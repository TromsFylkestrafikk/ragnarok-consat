# Change log

## [Unreleased]

### Added
- Planned journey table has journey start and end time of journeys, in
  addition to journey direction (Inbound, Outbound)

### Changed
- NSR quay info and pax data are now merged into the Calls table.
  Passenger count table is dropped.
- All timestamp columns converted to datetime.

## [0.1.0] – 2024-04-18
### Added
- Implemented sink API for fetching and deleting raw data
- Implemented sink API for importing and deleleting of it.
- Added configurable age per imported csv.
