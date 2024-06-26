# Change log

## [Unreleased]

### Added
- Planned journey table has journey start and end time of journeys, in
  addition to journey direction (Inbound, Outbound)

### Changed
- Stop quay info and pax data are now merged into the `consat_calls`
  table.
- Dropped tables `consat_destinations`, `consat_stops`, and
  `consat_passenger_count`, as these are merged or superfluous.
- All timestamp columns converted to datetime.

## [0.1.0] – 2024-04-18
### Added
- Implemented sink API for fetching and deleting raw data
- Implemented sink API for importing and deleleting of it.
- Added configurable age per imported csv.
