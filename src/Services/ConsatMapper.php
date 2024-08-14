<?php

namespace Ragnarok\Consat\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Carbon;
use League\Csv\Reader;
use Ragnarok\Sink\Services\CsvToTable;

/**
 * Map Csv files to CsvToTable processor objects
 */
class ConsatMapper
{
    /**
     * @var Filesystem
     */
    protected $csvDisk;

    /**
     * Don't import or create mapper for these files.
     *
     * @var string[]
     */
    protected $exceptCsvs = [];

    /**
     * In-memory passenger count map
     */
    protected $pax = [];

    /**
     * Mapping: Consat stop => NSR quay.
     *
     * @var array
     */
    protected $stopMap = [];

    /**
     * @param Filesystem $disk Laravel disk/filesystem the csv files is found
     */
    public function __construct(Filesystem $disk)
    {
        $this->csvDisk = $disk;
    }

    public function except(string $csvFile): ConsatMapper
    {
        $this->exceptCsvs[$csvFile] = $csvFile;
        return $this;
    }

    /**
     * Get a CsvToTable mapper instance suitable for given csv file.
     *
     * @param string $csvFile
     *
     * @return CsvToTable|null
     */
    public function getMapper($csvFile)
    {
        $filename = last(explode('/', $csvFile));
        if (!empty($this->exceptCsvs[$filename])) {
            return null;
        }
        $methodName = 'map' . explode('.', $filename)[0];
        if (!method_exists(self::class, $methodName)) {
            return null;
        }
        return call_user_func([$this, $methodName], $csvFile);
    }

    public static function dateFormatter($date)
    {
        return (new Carbon($date))->format('Y-m-d');
    }

    public function mapPlannedJourneys($csvFile): CsvToTable
    {
        $mapper = $this->createMapper($csvFile, 'consat_planned_journeys', ['id']);
        $mapper->column('OperatingCalendarDay', 'date')->required()->format([static::class, 'dateFormatter']);
        $mapper->column('Id', 'id')->required();
        $mapper->column('ExternalId', 'journey_id')->required();
        $mapper->column('JourneyName', 'trip');
        $mapper->column('BelongsToCompanyId', 'company');
        $mapper->column('BelongsToLineId', 'line_id');
        $mapper->column('BelongsToLineId', 'line')->format(fn ($lineId) => $lineId ? (int) substr($lineId, -3) : $lineId);
        $mapper->column('JourneyStartTime', 'journey_start')->format(fn ($input) => new Carbon($input));
        $mapper->column('JourneyEndTime', 'journey_end')->format(fn ($input) => new Carbon($input));
        $mapper->column('DirectionCode', 'direction');
        return $mapper;
    }

    public function mapInvalidatedJourneys($csvFile): CsvToTable
    {
        $mapper = $this->createMapper($csvFile, 'consat_invalidated_journeys', ['date', 'planned_journey_id']);
        $mapper->column('OperatingCalendarDay', 'date')->required()->format([static::class, 'dateFormatter']);
        $mapper->column('UsesPlannedJourneyId', 'planned_journey_id')->required();
        $mapper->column('ValidityStart', 'validity_start')->format(fn ($input) => new Carbon($input));
        $mapper->column('ValidityEnd', 'validity_end')->format(fn ($input) => new Carbon($input));
        $mapper->column('Creator', 'creator');
        $mapper->column('Description', 'description');
        return $mapper;
    }

    public function mapCalls($csvFile): CsvToTable
    {
        $mapper = $this->createMapper($csvFile, 'consat_calls', ['date', 'id']);
        $mapper->column('OperatingCalendarDay', 'date')->required()->format([static::class, 'dateFormatter']);
        $mapper->column('Id', 'id')->required();
        $mapper->column('UsesPlannedJourneyId', 'planned_journey_id')->required();
        $mapper->column('SequenceInJourney', 'sequence');
        $mapper->column('StopDurationSeconds', 'stop_duration');
        $mapper->column('PlannedArrivalTime', 'planned_arrival');
        $mapper->column('PlannedDepartureTime', 'planned_departure');
        $mapper->column('ActualArrivalTime', 'actual_arrival');
        $mapper->column('ActualDepartureTime', 'actual_departure');
        $mapper->column('MeasuredDistanceToNextPointInJourney', 'distance_next_point');
        $mapper->column('VehicleIdentity', 'vehicle');
        $mapper->column('DelayOnDepartureSeconds', 'delay');
        $mapper->column('IsValid', 'valid')->format(fn ($valid) => (bool) $valid);
        return $mapper->preInsertRecord(function ($csvRec, &$dbRec) {
            $stop_key =  $dbRec['date'] . '-' . $csvRec['UsesStopPointId'];
            $dbRec['stop_quay_id'] = $this->stopMap[$stop_key]['id'];
        });
    }

    public function mapCallDetails($csvFile): CsvToTable
    {
        $mapper = $this->createMapper($csvFile, 'consat_call_details', ['date', 'id']);
        $mapper->column('TimeStamp', 'timestamp')->required();
        $mapper->column('OperatingCalendarDay', 'date')->required()->format([static::class, 'dateFormatter']);
        $mapper->column('StartsAtCallId', 'call_id')->required();
        $mapper->column('ReportedDistance', 'distance');
        $mapper->column('ReportedLatitude', 'latitude');
        $mapper->column('ReportedLongitude', 'longitude');
        return $mapper;
    }

    public function mapPassengerCount($csvFile): CsvToTable
    {
        $mapper = $this->createMapper($csvFile, 'consat_passenger_count', ['id']);
        $mapper->column('Id', 'id')->required();
        $mapper->column('OperatingCalendarDay', 'date')->required()->format([static::class, 'dateFormatter']);
        $mapper->column('TimeStamp', 'timestamp')->required();
        $mapper->column('HappensAtCallId', 'call_id')->required();
        $mapper->column('PassengersOnboard', 'on_board');
        $mapper->column('totalIn', 'in');
        $mapper->column('totalOut', 'out');
        $mapper->column('PassengersFromLastJourney', 'from_last_journey');
        $mapper->column('IsValid', 'valid')->format(fn ($valid) => (bool) $valid);
        return $mapper;
    }

    public function mapStopPoint($csvFile): CsvToTable
    {
        $mapper = $this->createMapper($csvFile, 'consat_stops', ['date', 'id']);
        $mapper->column('ExternalId', 'id')->required();
        $mapper->column('OperatingCalendarDay', 'date')->required()->format([static::class, 'dateFormatter']);
        $mapper->column('Name', 'stop_name')->required();
        $mapper->column('Latitude', 'latitude');
        $mapper->column('Longitude', 'longitude');
        // Hash/cache stop points. This is used by self::mapCalls() to add NSR
        // quays (and stop names) directly instead of the internal (regtopp)
        // stop point IDs.
        return $mapper->preInsertRecord(function ($record, $dbRec) {
            $key = $dbRec['date'] . '-' . $record['Id'];
            $this->stopMap[$key] = $dbRec;
        });
    }

    protected function createMapper(string $csvFile, string $destTable, array|null $keyCols = null): CsvToTable
    {
        $mapper = new CsvToTable($this->csvDisk->path($csvFile), $destTable, $keyCols);
        return $mapper
            ->prepareCsvReader(function (Reader $csv) {
                $csv->setDelimiter(';');
            })
            ->offset(2)
            ->nullValues(['NULL', 'null']);
    }
}
