# Change log

## [Unreleased]

### Added
- New DB table: consat_invalidated_journeys.
- Planned journey table has journey start and end time of journeys, in
  addition to journey direction (Inbound, Outbound)
- Documentation of sink (`SINK.md`).

### Changed
- Dropped table `consat_destinations`.
- All timestamp columns converted to datetime.
- Stop quay IDs are now converted to the NSR or Regtopp equivalent.

## [0.1.0] – 2024-04-18
### Added
- Implemented sink API for fetching and deleting raw data
- Implemented sink API for importing and deleleting of it.
- Added configurable age per imported csv.
