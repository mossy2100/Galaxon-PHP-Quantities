# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.0] - 2026-02-13

Initial release.

### Added
- `Quantity` base class — immutable value objects combining a numeric value with a unit.
- 33 quantity type classes covering all 7 SI base quantities plus derived, supplementary, and non-SI types (e.g. Data, Dimensionless).
- Arithmetic operations: `add()`, `sub()`, `mul()`, `div()`, `pow()` with automatic dimensional analysis.
- Unit conversion via `to()`, `toSi()`, and `autoPrefix()`.
- Part decomposition via `toParts()`, `fromParts()`, and `formatParts()` (e.g. hours/minutes/seconds, degrees/arcminutes/arcseconds).
- `PhysicalConstant` class with named methods for SI defining constants and derived constants.
- `UnitRegistry` — static registry of units with lazy per-system loading.
- `ConversionRegistry` — static registry of conversion factors between units, organised by dimension.
- `PrefixRegistry` — SI and binary prefix definitions.
- `QuantityTypeRegistry` — registry of all quantity type metadata.
- Support for 7 measurement systems: SI, SI Accepted, Common, Imperial, US Customary, Nautical, and Astronomical.
- Parsing from strings via `Quantity::parse()` and subclass `parse()` methods.
- Formatting with `format()` supporting fixed, scientific, and general notation with Unicode ×10 superscript exponents.
- Derived unit representation via `DerivedUnit` (compound units like kg·m/s²).
- Unit expansion system linking named units to their base unit equivalents.
- Comprehensive documentation in `docs/` directory.

### Requirements
- PHP ^8.4
- galaxon/core ^1.0

### Development
- PSR-12 coding standards via Galaxon CodingStandard
- PHPStan level 9 static analysis
- PHPUnit with 100% code coverage
- Comprehensive test suite including real-world physics and engineering examples
