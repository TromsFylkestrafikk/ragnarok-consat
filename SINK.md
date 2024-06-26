# Sink Consat

Historic real time travel data from Consat.

## Data

This sink has historic real time information for each operating day,
generated in the early hours the next day. The imported data consist
of:

- All planned journeys: `consat_planned_journeys`. This has
  information about line, company, start and stop time and direction
  of the journey.
- All executed calls with pax data and stop name: `consat_calls`. This
  is probably the most useful table to get familiar with as it
  contains all delays, passenger count data and stop names.
- All positions reported by all vehicles on all journeys:
  `consat_call_details`. Due to its massive size this data is stored
  for a month only.

## Source

Consat is a provider of real time system for public transport. They
provide actual and estimated arrival and departure times for all
planned journeys of a PTA.

## Usage

The most useful import data from Consat is within the `consat_calls`
table.  It contains timestamps for both planned and actual departure
and arrivals, passenger count and quay ID. It references the planned
journey and has to be joined with both this and the date column.

All joins within this source have to be joined with both the foreign
key and the `date` column, which all tables has. e.g:
```sql
select j.line, c.stop_name
from consat_calls c join consat_planned_journeys j on c.date = j.date and c.planned_journey_id = j.id
where c.date = '2024-06-24'
```

