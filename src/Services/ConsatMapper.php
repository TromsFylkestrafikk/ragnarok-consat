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

    public function mapPlannedJourneys($csvFile)
    {
        $mapper = $this->createMapper($csvFile, 'consat_planned_journeys', ['id']);
        $mapper->column('OperatingCalendarDay', 'date')->required()->format([static::class, 'dateFormatter']);
        $mapper->column('Id', 'id')->required();
        $mapper->column('ExternalId', 'journey_id')->required();
        $mapper->column('JourneyName', 'trip');
        $mapper->column('BelongsToCompanyId', 'company');
        $mapper->column('BelongsToLineId', 'line_id');
        $mapper->column('BelongsToLineId', 'line')->format(fn ($lineId) => $lineId ? (int) substr($lineId, -3) : $lineId);
        return $mapper;
    }

    public function mapCalls($csvFile)
    {
        $mapper = $this->createMapper($csvFile, 'consat_calls', ['date', 'id']);
        $mapper->column('OperatingCalendarDay', 'date')->required()->format([static::class, 'dateFormatter']);
        $mapper->column('Id', 'id')->required();
        $mapper->column('UsesPlannedJourneyId', 'planned_journey_id')->required();
        $mapper->column('SequenceInJourney', 'sequence');
        $mapper->column('UsesStopPointId', 'stop_point_id');
        $mapper->column('StopDurationSeconds', 'stop_duration');
        $mapper->column('PlannedArrivalTime', 'planned_arrival');
        $mapper->column('PlannedDepartureTime', 'planned_departure');
        $mapper->column('ActualArrivalTime', 'actual_arrival');
        $mapper->column('ActualDepartureTime', 'actual_departure');
        $mapper->column('MeasuredDistanceToNextPointInJourney', 'distance_next_point');
        $mapper->column('VehicleIdentity', 'vehicle');
        $mapper->column('DelayOnDepartureSeconds', 'delay');
        $mapper->column('IsValid', 'valid')->format(fn ($valid) => (bool) $valid);
        $mapper->preInsertRecord(function ($csvRec, &$dbRec) {
            $dbRec['nsr_quay_id'] = $this->stopMap[$csvRec['UsesStopPointId']]['id'];
            $dbRec['stop_name'] = $this->stopMap[$csvRec['UsesStopPointId']]['name'];
        });
        return $mapper;
    }

    public function mapCallDetails($csvFile)
    {
        $mapper = $this->createMapper($csvFile, 'consat_call_details', ['date', 'id']);
        $mapper->column('TimeStamp', 'timestamp')->required();
        $mapper->column('OperatingCalendarDay', 'date')->required()->format([static::class, 'dateFormatter']);
        $mapper->column('StartsAtCallId', 'call_id')->required();
        $mapper->column('EventType', 'event_type');
        $mapper->column('ReportedDistance', 'distance');
        $mapper->column('ReportedLatitude', 'latitude');
        $mapper->column('ReportedLongitude', 'longitude');
        return $mapper;
    }

    public function mapPassengerCount($csvFile)
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

    public function mapStopPoint($csvFile)
    {
        $mapper = $this->createMapper($csvFile, 'consat_stops', ['date', 'id']);
        $mapper->column('Id', 'id')->required();
        $mapper->column('OperatingCalendarDay', 'date')->required()->format([static::class, 'dateFormatter']);
        $mapper->column('ExternalId', 'external_id')->required();
        $mapper->column('Name', 'name')->required();
        $mapper->column('Latitude', 'latitude');
        $mapper->column('Longitude', 'longitude');
        $mapper->preInsertRecord(function ($record) {
            $this->stopMap[$record['Id']] = [
                'id' => $record['ExternalId'],
                'name' => $record['Name'],
            ];
        });
        return $mapper;
    }

    public function mapDestination($csvFile)
    {
        $mapper = $this->createMapper($csvFile, 'consat_destinations', ['date', 'id']);
        $mapper->column('Id', 'id')->required();
        $mapper->column('ExternalId', 'external_id');
        $mapper->column('OperatingCalendarDay', 'date')->required()->format([static::class, 'dateFormatter']);
        $mapper->column('DestinationNameShort', 'destination')->required();
        $mapper->preInsertRecord(function ($csvRec, &$dbRec) {
            if (!empty($csvRec['DestinationNameLong'])) {
                $dbRec['destination'] = $csvRec['DestinationNameLong'];
            }
        });
        return $mapper;
    }

    /**
     * @param string $csvFile
     * @param string $destTable
     * @param array|null $keyCols
     *
     * @return CsvToTable
     */
    protected function createMapper($csvFile, $destTable, $keyCols = null)
    {
        $mapper = new CsvToTable($this->csvDisk->path($csvFile), $destTable, $keyCols);
        return $mapper->prepareCsvReader(function (Reader $csv) {
            $csv->setDelimiter(';');
        })->offset(2)->nullValues(['NULL', 'null']);
    }
}
