# Sink Consat

Historic real time travel data from Consat.

## Data

The data provided by Consat is two-fold: Planned journeys and actual,
executed journeys.  The planned journeys stored in
`consat_planned_journeys` has some basic data for each trip, like
date, line and trip number, start and end time of journey, company and
direction (in- or outbound).

The executed, ran journeys is more detailed with all calls done. This
data is stored in the table `consat_calls`. For every call there is
metrics for stop place, pax count, reference to its planned journey,
planned and actual arrival and departure times, distance to next stop,
delay, vehicle and validity of the call.

A final table `consat_call_details` consist of every position reported
by all vehicles on all trips, giving us a trace of all recorded
journeys.  Due to its massive size this only has data one month back.


## Source

Consat is a provider of real time system for public transport. They
provide actual and estimated arrival and departure times for all
planned journeys of a PTA.

## Usage

The `consat_calls` table is the most interesting table as this has pax
and delay data connected with stop places, which is essential when
investigating bottle necks and customer flow.

This gives answers to interesting questions like:
- What stop places are causes for delays
- How many pax is transported per line per day
- What lines are more prone to delays than other

