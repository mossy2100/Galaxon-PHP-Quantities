# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.1.0] - 2026-05-11

### Added

- **`PhysicalConstant` public float constants** — All 20 constants now accessible as typed class constants (e.g. `PhysicalConstant::PLANCK`, `PhysicalConstant::SPEED_OF_LIGHT`). The internal data table and `Force::lbf` conversion now reference these instead of hardcoded literals.
- **`Temperature::toAbsoluteScale()`** — Public method returning `static`; always returns a new object even for Kelvin/Rankine inputs.
- **`CurrencyService::getUnits(bool $bypassCache)`** and **`getConversions(bool $bypassCache)`** — Cleaner replacements for the removed `refreshUnits()` / `refreshConversions()` methods.
- **`CurrencyService::deleteUnits()`**, **`deleteConversions()`**, **`resetDataDir()`** — Cache management methods.
- **`$si` parameter on `fromParts()` and `parseParts()`** — Allows the caller to choose SI or English base units for the result. Replaces the old `$resultUnitSymbol` parameter.
- **Prefixed and compound symbols in parts methods** — `fromParts()`, `toParts()`, and `parseParts()` now accept prefixed (`km`) and compound (`kW*h`) unit symbols as part keys.
- **`Unit::parse()` lookup cache** — Successful symbol lookups are cached so repeated parses do not re-scan unit definitions.

### Changed

- **`Quantity::parse()`** — Return type narrowed from `self` to `static`.
- **`Quantity::parse()` and `parseParts()`** — Now two distinct public methods; `parse()` tries a single quantity first and falls back to `parseParts()`.
- **`toParts()`** — Always emits every requested part unit, even when its value is zero.
- **`formatParts()`** — Zero-value fallback uses the smallest requested unit rather than a hardcoded default; sign extracted before the loop.
- **`CompoundUnit::addUnitTerm()` and `removeUnitTerm()`** — Made private; external callers can no longer mutate a `CompoundUnit` directly.
- **`CurrencyService::DEFAULT_DATA_DIR`** — Made private. Use `resetDataDir()` to restore the default data directory.
- **`DimensionService::getBaseUnitSymbol()`** — Renamed to `getBaseUnitTermSymbol()`.
- **`DimensionService::getBaseUnitTerm()` and `getBaseCompoundUnit()`** — `$si` parameter now defaults to `true`.
- **`UnitTerm` parsing** — Explicit exponents of 0 and 1 are now rejected at parse time.

### Fixed

- **`parseParts()`** — Last part was not added in the parse loop; a leading space appeared on the unit symbol when value and unit were whitespace-separated.
- **`CurrencyService::getXmlFilePath()`** — Was returning `CurrencyData.xml`; corrected to `CurrencyUnits.xml`.

### Removed

- **`Quantity::getResultUnitSymbol()`** and its overrides in `Angle`, `Length`, `Mass`, and `Time` — The result unit for `fromParts()` is now the first unit in the parts array.
- **`CurrencyService::loadUnitData()`**, **`loadConversionData()`**, **`refreshUnits()`**, **`refreshConversions()`** — Replaced by `getUnits()`, `getConversions()`, `deleteUnits()`, and `deleteConversions()`.
- **`PrefixService::reset()`**.

### Documentation

- Full formatting standardisation across all docs: unit names italic, symbols in backticks, dimension codes in backticks, prefix symbols italic.
- Unit definition and conversion definition tables added to all QuantityType reference docs.
- `CurrencyService.md` restructured; new methods documented.
- `Temperature.md` updated with `toAbsoluteScale()`.
- `CompoundUnit.md` construction section removed (mutators now private).
- `PhysicalConstant.md` quick reference updated with ASCII symbols and italic units; cross-link to the concepts guide added.

---

## [1.0.0] - 2026-04-09

### Removed

- **`QuantityPartsService`** — Class deleted entirely. All parts functionality (`fromParts`, `toParts`, `parseParts`, `formatParts`) and the supporting validators now live directly on `Quantity` itself in the Parts region. Subclasses provide built-in defaults by overriding `getPartUnitSymbols()` and `getResultUnitSymbol()`. No global state, no service class. The corresponding `QuantityPartsServiceTest`, `docs/Reference/Services/QuantityPartsService.md`, and the row in the README services table were also removed.
- **`Mass::setImperialParts()` / `setUsCustomaryParts()`** — Replaced with the public constants `Mass::IMP_PART_UNITS` and `Mass::US_PART_UNITS`. Callers now pass these inline via `partUnitSymbols`.

### Changed

- **`Quantity` parts methods** — `fromParts()` and `parseParts()` return types tightened from `Quantity` to `static`. `validateQuantityType()` now returns the resolved `QuantityType` (non-null) and is passed into the unit validators, eliminating duplicate lookups and PHPStan null-safety issues.
- **`composer.json`** — Bumped `galaxon/core` constraint to `^1.6` (required for `Floats::format()` and the Core v1.6.0 trait namespace reorganisation). `galaxon/coding-standard` switched from local path repo to `^1.0` from Packagist.
- **Trait namespace updates** — Updated `use` statements throughout the package to match Core v1.6.0's trait reorganisation (`Galaxon\Core\Traits\Comparison\*`, `Galaxon\Core\Traits\Asserts\*`).
- **`Quantity::isValidUnicodeSpecialChar()`** — Moved to `Internal\Unit::isValidUnicodeSpecialChar()` (public). `Quantity::format()` calls it directly.

### Fixed

- **`Quantity::to()`, `toDerived()`, `merge()`, `autoPrefix()`, `withValue()`, `fromParts()`** — Return types narrowed to `static` via `assert($result instanceof static)` so PHPStan level 9 can verify the dimension-preserving invariant. No runtime behaviour change.

### Documentation

- **Bulk docblock cleanup** — Replaced the verbose `@return array<string, array{...}>` annotation on every `getUnitDefinitions()` override across the 33 QuantityType subclasses with `@inheritDoc`, then removed `@inheritDoc` entirely (redundant alongside `#[Override]`). Restored minimal one-line docblocks on the 12 methods that were left bare (currency `getName()` overrides plus the parts hooks on `Length`/`Time`/`Angle`/`Mass`).
- **`Quantity.md`** — Removed "delegates to `QuantityPartsService`" paragraph; updated parts-method exception lists; added entries for the new `getPartUnitSymbols()` / `getResultUnitSymbol()` subclass hooks under Subclass Methods.
- **`UnknownUnitException.md`** — Table rows for `validatePartUnits()` / `validateResultUnit()` now point at `Quantity` instead of `QuantityPartsService`.

### Code quality

- **`composer quality` passes end-to-end**: PHPCS clean, PHPStan level 9 clean, PHPUnit all green, coverage threshold met. 100% test coverage maintained on the parts machinery after the move from `QuantityPartsService`.

### Earlier work shipped in this release

The entries below describe work done earlier in the 1.0 cycle. Some of them mention `QuantityPartsService`, which was later removed entirely (see the Removed section above) — they accurately record the intermediate state at the time the change was made.

#### Changed

- **DerivedUnit → CompoundUnit** — `Internal\DerivedUnit` renamed to `Internal\CompoundUnit`. The term *compound unit* now means "a unit formed by multiplying/dividing unit terms" (e.g. `kg·m/s²`), while *derived unit* means "a named unit that substitutes for a compound unit" (e.g. `N`, `lbf`, `Hz`).
- **Quantity::simplify() → Quantity::toDerived()** — Method renamed to match the new terminology. Substitutes named derived units (like `N`) for equivalent compound base-unit forms (like `kg·m/s²`).
- **Quantity (parts methods)** — `fromParts()`, `toParts()`, `parseParts()`, and `formatParts()` now accept optional inline overrides (`$resultUnitSymbol` for `fromParts`/`parseParts`; `$partUnitSymbols` for `toParts`/`formatParts`) so the configured defaults can be bypassed for a single call without mutating service state.
- **QuantityPartsService (parts methods)** — Same new optional parameters added; the four methods are now marked `@internal` (call the wrappers on `Quantity`).
- **Floats::format()** — Default precision for `g`/`G`/`h`/`H` is now 7 significant digits (was sprintf's default of 6) so that `g` is genuinely "the shorter of `e` and `f` at matching precision". `e`/`E`/`f`/`F` still default to 6. Format string is now always explicit `%.Nspec`.
- **UnitSystem** — Moved from `Galaxon\Quantities` to `Galaxon\Quantities\Internal` namespace.
- **README** — Reordered Concepts section so ideas build logically: Terminology → Dimensions → QuantityTypes → Prefixes → Units → PhysicalConstants. Removed `SystemsOfUnits.md` (information available in the Reference doc; all systems now load by default).
- **PhysicalConstants.md (Concepts)** — Added "Usual symbol" column with proper Unicode/`<sub>` notation; switched exponents to Unicode (×10ⁿ); removed redundant Method column; normalised heading hierarchy.
- **PartDecomposition.md** — Rewrote `toParts()`/`formatParts()`/`fromParts()` examples to match the actual API. Non-default part units are now configured via `QuantityPartsService::setPartUnitSymbols()`.
- **WorkingWithQuantities docs** — Added missing `## Overview` headings, fixed broken heading hierarchies, split stacked `use` statements, added missing imports in code snippets, normalised scientific notation in CalculationExamples to `× 10ⁿ`.
- **Terminology.md** — Completed truncated intro sentence.
- **QuantityPartsService** — Uses `NullArgumentException` (from Core) instead of `DomainException` when the quantity type is null.
- **QuantityPartsService** — Config methods (`getPartUnitSymbols`, `setPartUnitSymbols`, `getResultUnitSymbol`, `setResultUnitSymbol`) removed from `Quantity`; use `QuantityPartsService` directly.
- **Mass** — `setImperialParts()` and `setUsCustomaryParts()` call `QuantityPartsService` directly.
- **DimensionService** — Renamed `applyExponent()` to `pow()` for consistency.
- **DimensionMismatchException** — `$dimension1` and `$dimension2` properties are now nullable (`?string`). Null dimensions render as `null` in the default message.
- **Quantity::formatValue()** — Moved to `Floats::format()` in Core. `Quantity::format()` now delegates to it.
- **Quantity::neg()** — Now returns `static` instead of `self` (dimension preserved).
- **Quantity::abs()** — Returns `static` (unchanged behaviour, was already `static`).
- **Quantity::merge()** — Always returns a new object (previously returned `$this` when not mergeable).
- **Quantity::compare()** — Fixed `@throws` PHPDoc: `InvalidArgumentException` → `DimensionMismatchException`.
- **Quantity::approxEqual()** — Catches `DimensionMismatchException` explicitly (was catching `DomainException`).
- **Quantity::inv()** and **Quantity::div()** — Use `Numbers::isZero()` instead of strict comparison for zero checks.
- **Quantity::create()** — Removed redundant finite-value check (the constructor validates).
- **PhysicalConstant::getAll()** — Fixed `@return` type annotation (`list<Quantity>` → `array<string, Quantity>`).
- **PhysicalConstant** — Renamed region `Accessor methods` → `Lookup methods` to match convention.
- **Quantity** — Split `Subclass methods` region into `Subclass methods` (for overridable methods) and `Lookup methods` (for `getQuantityType()` and `getDimension()`).
- **Quantity** — `convert()` moved into `Unit conversion methods` region; `Utility methods` region removed.

#### Added

- **Quantity::new()** — Private helper that encapsulates the `$allowConstruct` flag hack for constructing generic Quantity objects.
- **Quantity::withValue()** — Now uses `new static()` directly for subclasses (skipping the dimension→class lookup), and the `new()` helper for base Quantity instances.
- **DimensionMismatchException** — Constructor now accepts null dimensions.

#### Fixed

- **Documentation** — Rewrote PrefixService.md, QuantityTypeService.md, QuantityPartsService.md, DimensionService.md, Quantity.md, PhysicalConstant.md, DimensionMismatchException.md with correct exception types, section names, signatures, return types, and missing/removed methods.
- **PrefixService** — Fixed PHPDoc typo listing 16 as a valid group code.
- **QuantityTypeService** — Fixed class PHPDoc description.
- **Quantity::pow()** — Fixed misleading example (was using `sqr()` instead of `pow(n)`).
- **Quantity::sqr()** — Removed dubious "more efficient" claim from PHPDoc.
- **Quantity::create()** — Fixed critical missing `return` statement in fallback path for unregistered dimensions.
- **QuantityPartsService::formatParts()** — Was ignoring the new `$partUnitSymbols` argument when delegating to `toParts()`; now forwards it correctly.
- **QuantityPartsService::parseParts()** — Was ignoring the new `$resultUnitSymbol` argument when delegating to `fromParts()`; now forwards it correctly.
- **Documentation (Concepts + WorkingWithQuantities)** — Reviewed all pages for terminology consistency, code-example correctness, heading hierarchies, and stale API references. All inline code examples verified by execution.

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
- Derived unit representation via `DerivedUnit` (units like kg·m/s²).
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
