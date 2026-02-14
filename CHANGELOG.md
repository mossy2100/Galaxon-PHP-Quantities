# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.2.0] - 2026-02-14

### Added
- `RegexHelper` class centralising all regex constants, pattern builders, and validation methods used across the package.
- `UnitRegistry::ON_DUPLICATE_THROW`, `ON_DUPLICATE_SKIP`, and `ON_DUPLICATE_REPLACE` constants for controlling duplicate unit behaviour.
- `System::Custom` enum case for user-defined units.
- Unit constructor now validates name format, prefix group range, expansion unit symbol, expansion value, and system values.
- `Dimensions::letterToInt()` now throws `DomainException` instead of returning `null` for invalid codes.
- Documentation for `RegexHelper` class.

### Changed
- `UnitRegistry::add()` now accepts a `Unit` object instead of individual parameters, with an `$onDuplicateAction` parameter.
- `Unit` constructor parameter order changed: `$dimension` and `$systems` moved before `$prefixGroup`.
- `Unit` constructor defaults: `$systems` defaults to `[System::Custom]` instead of `[]`.
- Moved all regex constants and validation methods from `Unit`, `UnitTerm`, `DerivedUnit`, and `Prefix` into `RegexHelper`.
- Replaced inline regex in `Prefix` constructor with `RegexHelper::isValidAsciiPrefix()` and `isValidUnicodePrefix()`.
- Replaced inline regex in `Angle::parse()` with `RegexHelper::isValidDmsAngle()`.
- Replaced `DerivedUnit::regex()` and `DerivedUnit::parse()` inline patterns with `RegexHelper` methods.
- Replaced `UnitTerm::regex()` with `RegexHelper::unitTermRegex()` and `isValidUnitTerm()`.
- Replaced `Quantity::parse()` inline regex with `RegexHelper::isValidQuantity()`.
- Renamed `Unit::isValidNonLetterSymbol()` to `RegexHelper::isValidUnicodeSpecialChar()`.
- Renamed `System::Astronomical` to `System::Scientific`.
- Renamed `metre` to `meter`, `litre` to `liter` in unit definitions.
- `Unit::$symbols` property hook now uses empty array `[]` instead of `null` for uninitialised state.
- Updated documentation for `Unit`, `UnitTerm`, `Dimensions`, and `UnitRegistry`.

### Removed
- `Unit::regex()`, `Unit::isValidAsciiSymbol()`, `Unit::isValidUnicodeSymbol()`, `Unit::isValidNonLetterSymbol()` (moved to `RegexHelper`).
- `Unit::RX_MUL_OPS_ONLY` and `Unit::RX_MUL_OPS_PLUS_DIV` public constants (replaced by `RegexHelper::RX_CLASS_MUL_DIV_OPS`).
- `UnitTerm::regex()` (moved to `RegexHelper::unitTermRegex()`).
- `DerivedUnit::regex()` (moved to `RegexHelper::derivedUnitRegex()`).
- `Unit::acceptsPrefixes()` method.

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
- Support for several systems of units: SI, SI Accepted, Common, Imperial, US Customary, Scientific, Nautical, and Typographical.
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
