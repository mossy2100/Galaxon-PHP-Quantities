# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed

- **QuantityPartsService** — Uses `NullArgumentException` (from Core) instead of `DomainException` when the quantity type is null.
- **QuantityPartsService** — Config methods (`getPartUnitSymbols`, `setPartUnitSymbols`, `getResultUnitSymbol`, `setResultUnitSymbol`) removed from `Quantity`; use `QuantityPartsService` directly.
- **Mass** — `setImperialParts()` and `setUsCustomaryParts()` call `QuantityPartsService` directly.
- **DimensionService** — Renamed `applyExponent()` to `pow()` for consistency.

### Fixed

- **Documentation** — Rewrote PrefixService.md, QuantityTypeService.md, QuantityPartsService.md, DimensionService.md with correct exception types, section names, missing methods, and return types.
- **PrefixService** — Fixed PHPDoc typo listing 16 as a valid group code.
- **QuantityTypeService** — Fixed class PHPDoc description.

---

## [0.2.0] - 2026-02-14

### Added
- `RegexService` class centralising all regex constants, pattern builders, and validation methods used across the package.
- `UnitService::ON_DUPLICATE_THROW`, `ON_DUPLICATE_SKIP`, and `ON_DUPLICATE_REPLACE` constants for controlling duplicate unit behaviour.
- `System::Custom` enum case for user-defined units.
- Unit constructor now validates name format, prefix group range, expansion unit symbol, expansion value, and system values.
- `DimensionService::letterToInt()` now throws `DomainException` instead of returning `null` for invalid codes.
- Documentation for `RegexService` class.

### Changed
- `UnitService::add()` now accepts a `Unit` object instead of individual parameters, with an `$onDuplicateAction` parameter.
- `Unit` constructor parameter order changed: `$dimension` and `$systems` moved before `$prefixGroup`.
- `Unit` constructor defaults: `$systems` defaults to `[System::Custom]` instead of `[]`.
- Moved all regex constants and validation methods from `Unit`, `UnitTerm`, `DerivedUnit`, and `Prefix` into `RegexService`.
- Replaced inline regex in `Prefix` constructor with `RegexService::isValidAsciiPrefix()` and `isValidUnicodePrefix()`.
- Replaced inline regex in `Angle::parse()` with `RegexService::isValidDmsAngle()`.
- Replaced `DerivedUnit::regex()` and `DerivedUnit::parse()` inline patterns with `RegexService` methods.
- Replaced `UnitTerm::regex()` with `RegexService::unitTermRegex()` and `isValidUnitTerm()`.
- Replaced `Quantity::parse()` inline regex with `RegexService::isValidQuantity()`.
- Renamed `Unit::isValidNonLetterSymbol()` to `RegexService::isValidUnicodeSpecialChar()`.
- Renamed `System::Astronomical` to `System::Scientific`.
- Renamed `metre` to `meter`, `litre` to `liter` in unit definitions.
- `Unit::$symbols` property hook now uses empty array `[]` instead of `null` for uninitialised state.
- Updated documentation for `Unit`, `UnitTerm`, `DimensionService`, and `UnitService`.

### Removed
- `Unit::regex()`, `Unit::isValidAsciiSymbol()`, `Unit::isValidUnicodeSymbol()`, `Unit::isValidNonLetterSymbol()` (moved to `RegexService`).
- `Unit::RX_MUL_OPS_ONLY` and `Unit::RX_MUL_OPS_PLUS_DIV` public constants (replaced by `RegexService::RX_CLASS_MUL_DIV_OPS`).
- `UnitTerm::regex()` (moved to `RegexService::unitTermRegex()`).
- `DerivedUnit::regex()` (moved to `RegexService::derivedUnitRegex()`).
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
- `UnitService` — static registry of units with lazy per-system loading.
- `ConversionService` — static registry of conversion factors between units, organised by dimension.
- `PrefixService` — SI and binary prefix definitions.
- `QuantityTypeService` — registry of all quantity type metadata.
- Support for several systems of units: SI, SI Accepted, Common, Imperial, US Customary, Scientific, Nautical, and Css.
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
