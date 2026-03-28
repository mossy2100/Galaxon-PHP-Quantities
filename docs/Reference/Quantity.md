# Quantity

Base class for physical measurements with units.

**Namespace:** `Galaxon\Quantities`

---

## Overview

The `Quantity` class provides a framework for creating strongly typed measurement classes (Length, Mass, Time, etc.) with automatic unit conversion, arithmetic operations, and comparison capabilities.

Quantities are immutable value objects combining a numeric value with a unit. All arithmetic and transformation operations return new instances.

### Key Features

- Automatic validation of units and values
- Type-safe arithmetic operations (add, subtract, multiply, divide)
- Automatic unit conversion with prefix handling
- Comparison and equality testing with epsilon tolerance
- Flexible string formatting and parsing
- Parts-based formatting (e.g., "5h 30min 45s")

---

## Properties

### value

```php
public readonly float $value
```

The numeric value of the measurement in the specified unit.

### derivedUnit

```php
public readonly DerivedUnit $derivedUnit
```

The unit of the measurement as a DerivedUnit object.

### dimension

```php
public string $dimension { get; }
```

The dimension code of the quantity (e.g., 'L' for length, 'MLT-2' for force).

See [DimensionService](Services/DimensionService.md) for more details about how dimensions work.

### type

```php
public ?QuantityType $type { get; }
```

The quantity type metadata, or null if not registered.

---

## Constructor and Factory Method

### \_\_construct()

```php
public function __construct(float $value, null|string|UnitInterface $unit = null)
```

Create a new Quantity with the specified value and unit.

The `Quantity` constructor cannot be called directly — use a specific subclass constructor (e.g. `new Length(...)`) or the `Quantity::create()` factory method. This ensures the correct subclass is always used for each dimension. See [Creating Quantities](../WorkingWithQuantities/CreatingQuantities).

**Parameters:**
- `$value` (float) - The numeric value.
- `$unit` (null|string|UnitInterface) - The unit (symbol string, object, or null for dimensionless).

**Throws:**
- `LogicException` - If `new Quantity()` is called directly or the wrong subclass constructor is called for the unit's dimension.
- `DomainException` - If the value is non-finite (INF or NAN).
- [`UnknownUnitException`](Exceptions/UnknownUnitException.md) - If the unit string contains unknown units.
- `FormatException` - If the unit string cannot be parsed.

**Examples:**
```php
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;

$length = new Length(100, 'm');
$mass = new Mass(5.5, 'kg');
```

### create()

```php
public static function create(float $value, null|string|UnitInterface $unit): self
```

Create a Quantity of the appropriate type for the given unit.

Determines the quantity dimension from the unit, and refers to [QuantityTypeService](Services/QuantityTypeService.md) to find and instantiate the correct subclass. If no subclass is registered for the dimension, a generic `Quantity` object is created.

**Parameters:**
- `$value` (float) - The numeric value.
- `$unit` (null|string|UnitInterface) - The unit.

**Returns:**
- `Quantity` - A Quantity of the appropriate type.

**Throws:**
- `DomainException` - If the value is non-finite (INF or NAN).
- [`UnknownUnitException`](Exceptions/UnknownUnitException.md) - If the unit string contains unknown units.
- `FormatException` - If the unit string cannot be parsed.

**Examples:**
```php
use Galaxon\Quantities\Quantity;

// Creates a Length object.
$length = Quantity::create(100, 'm');

// Creates a Force object.
$force = Quantity::create(10, 'N');

// Creates a generic Quantity for unregistered dimensions.
$custom = Quantity::create(5, 'kg*m2/s3');
```

---

## Static Methods
### convert()

```php
public static function convert(
    float $value,
    string|UnitInterface $srcUnit,
    string|UnitInterface $destUnit
): float
```

Convert a value from a source unit to a destination unit.

A convenience method for converting a raw numeric value without creating Quantity objects.

**Parameters:**
- `$value` (float) - The numeric value to convert.
- `$srcUnit` (string|UnitInterface) - The source unit.
- `$destUnit` (string|UnitInterface) - The destination unit.

**Returns:**
- `float` - The converted value.

**Throws:**
- `FormatException` - If a unit string cannot be parsed.
- [`UnknownUnitException`](Exceptions/UnknownUnitException.md) - If a unit string contains unknown units.
- `LogicException` - If no conversion path exists between the units.

**Examples:**
```php
// Convert 100 degrees Fahrenheit to Celsius.
$celsius = Quantity::convert(100, 'degF', 'degC');

// Convert 5 miles to kilometres.
$km = Quantity::convert(5, 'mi', 'km');
```

### getUnitDefinitions()

```php
public static function getUnitDefinitions(): array
```

Get the unit definitions for this quantity type.

Returns an empty array in the base `Quantity` class. Overridden in subclasses (e.g. `Length`, `Mass`, `Force`) to define the units specific to that quantity type. Each entry specifies the unit's ASCII symbol, optional Unicode symbol, prefix group, and unit systems.

These definitions are loaded by `UnitService` when a unit system is initialised.

**Returns:**
- `array` - An associative array of unit definitions keyed by unit name.

### getConversionDefinitions()

```php
public static function getConversionDefinitions(): array
```

Get the conversion definitions for this quantity type.

Returns an empty array in the base `Quantity` class. Overridden in subclasses to define conversion factors between units. Each entry is a tuple of `[sourceUnit, destUnit, factor]`.

These definitions are loaded by `ConversionService` when a unit system is initialised.

**Returns:**
- `list<array{string, string, float}>` - A list of conversion definition tuples.

### getQuantityType()

```php
public static function getQuantityType(): ?QuantityType
```

Get the quantity type metadata for the calling class.

Returns the registered `QuantityType` for the calling subclass, or null if called on `Quantity` itself or an unregistered subclass.

**Returns:**
- `?QuantityType` - The quantity type, or null if not registered.

**Examples:**
```php
$type = Length::getQuantityType();
$type->name;       // 'length'
$type->dimension;  // 'L'

Quantity::getQuantityType();  // null
```

### getDimension()

```php
public static function getDimension(): ?string
```

Get the dimension code for the calling class.

Returns the dimension code (e.g. `'L'` for Length, `'M'` for Mass) if the calling class is a registered quantity type subclass. Returns null if called on `Quantity` itself or an unregistered subclass.

**Returns:**
- `?string` - The dimension code, or null if not registered.

**Examples:**
```php
Length::getDimension();       // 'L'
Mass::getDimension();         // 'M'
Temperature::getDimension();  // 'H'
Quantity::getDimension();     // null
```

---

## Conversion Methods

### to()

```php
public function to(string|UnitInterface $destUnit): self
```

Convert this Quantity to a different unit.

**Parameters:**
- `$destUnit` (string|UnitInterface) - The destination unit.

**Returns:**
- `Quantity` - A new Quantity in the specified unit.

**Throws:**
- [`UnknownUnitException`](Exceptions/UnknownUnitException.md) - If the destination unit is unknown.
- [`DimensionMismatchException`](Exceptions/DimensionMismatchException.md) - If the destination unit has a different dimension.
- `LogicException` - If no conversion path exists.

**Examples:**
```php
$meters = new Length(1000, 'm');
$km = $meters->to('km');        // 1 km
$feet = $meters->to('ft');      // 3280.84 ft
```

### toSi()

```php
public function toSi(): self
```

Convert this quantity to SI units with simplification. Base units are replaced by named units where possible (e.g., kg\*m/s2 becomes N).

To auto-prefix the result, chain with `autoPrefix()`.

**Returns:**
- `Quantity` - A new Quantity in SI units.

**Examples:**
```php
$force = new Force(1000, 'N');
$si = $force->toSi();  // 1000 N

$energy = Quantity::create(1, 'kW*h');
$si = $energy->toSi();  // 3600000 J
$si = $energy->toSi()->autoPrefix();  // 3.6 MJ
```

### toSiBase()

```php
public function toSiBase(): self
```

Convert to SI base units without simplification or auto-prefixing.

Unlike `toSi()`, this returns purely SI base units (e.g., kg\*m/s2 instead of N). Useful for calculations or when you need the fundamental SI form.

**Returns:**
- `Quantity` - A new Quantity in SI base units.

**Examples:**
```php
$force = new Force(1, 'N');
$base = $force->toSiBase();  // 1 kg*m/s2

$energy = new Energy(1, 'J');
$base = $energy->toSiBase();  // 1 kg*m2/s2
```

### toEnglishBase()

```php
public function toEnglishBase(): self
```

Convert to English (Imperial/US customary) base units without simplification.

For dimensions that don't have a specific English base unit (e.g. time, electric current), the SI base unit is used. See [DimensionService](Services/DimensionService.md) for the mapping.

**Returns:**
- `Quantity` - A new Quantity in English base units.

**Examples:**
```php
$force = new Force(1, 'lbf');
$base = $force->toEnglishBase();  // lb*ft/s2

$length = new Length(1, 'mi');
$base = $length->toEnglishBase();  // 5280 ft

$speed = Quantity::create(1, 'kn');
$base = $speed->toEnglishBase();  // ~1.688 ft/s
```

### toBase()

```php
public function toBase(): self
```

Convert to SI or English base units, whichever is the better fit for the current unit.

Units like lbf, mi, and ac will convert to English base units (lb, ft, s), while units like km, N, and Hz will convert to SI base units (kg, m, s). Uses `DerivedUnit::siExpansionPreferred()` to determine the best fit.

**Returns:**
- `Quantity` - A new Quantity in the most appropriate base units.

**Examples:**
```php
$force = new Force(1, 'N');
$base = $force->toBase();  // 1 kg*m/s2

$force = new Force(1, 'lbf');
$base = $force->toBase();  // lb*ft/s2
```

---

## Unit Transformation Methods

### expand()

```php
public function expand(): self
```

Expand named units to base units (e.g., N -> kg*m/s2).

If no registered expansion exists, falls back to SI or English base units depending on whether the unit terms are SI or English.

**Returns:**
- `Quantity` - A new Quantity with expanded units.

**Examples:**
```php
$force = new Force(1, 'N');
$expanded = $force->expand();  // 1 kg*m/s2

$force = new Force(1, 'lbf');
$expanded = $force->expand();  // lb*ft/s2
```

### merge()

```php
public function merge(): self
```

Merge units with the same dimension (e.g., m\*ft -> m2).

The first unit encountered of a given dimension will be the one any others are converted to.

**Returns:**
- `Quantity` - A new Quantity with merged units.

### autoPrefix()

```php
public function autoPrefix(): self
```

Apply the best engineering prefix to the first unit term.

The best prefix is the one that produces the smallest value greater than or equal to 1. Only engineering prefixes (multiples of 1000) are considered.

**Returns:**
- `Quantity` - A new Quantity with optimal prefix.

**Examples:**
```php
$length = new Length(0.001, 'm');
$prefixed = $length->autoPrefix();  // 1 mm

$length = new Length(1000000, 'm');
$prefixed = $length->autoPrefix();  // 1000 km
```

### simplify()

```php
public function simplify(): self
```

Substitute base units for derived units where possible (e.g., kg\*m/s2 -> N).

To auto-prefix the result, chain with `autoPrefix()`.

**Returns:**
- `Quantity` - A new Quantity with simplified units.

---

## Value Transformation Methods

### withValue()

```php
public function withValue(float $value): self
```

Create a new Quantity with the same unit but a different value.

**Parameters:**
- `$value` (float) - The new numeric value.

**Returns:**
- `Quantity` - A new Quantity with the given value in the same unit.

**Examples:**
```php
$length = new Length(10, 'm');
$doubled = $length->withValue(20);  // 20 m
```

### abs()

```php
public function abs(): self
```

Get the absolute value of this measurement.

**Returns:**
- `Quantity` - A new Quantity with non-negative value.

### round()

```php
public function round(int $precision = 0, RoundingMode $mode = RoundingMode::HalfAwayFromZero): self
```

Round the value to a given number of decimal places.

**Parameters:**
- `$precision` (int) - Number of decimal places. Default: `0`.
- `$mode` (RoundingMode) - The rounding mode. Default: `RoundingMode::HalfAwayFromZero`.

**Returns:**
- `Quantity` - A new Quantity with the rounded value in the same unit.

**Examples:**
```php
$length = new Length(1.5678, 'm');
$rounded = $length->round(2);  // 1.57 m
```

### floor()

```php
public function floor(): self
```

Round the value down to the nearest integer.

**Returns:**
- `Quantity` - A new Quantity with the value rounded down, in the same unit.

**Examples:**
```php
$length = new Length(1.9, 'm');
$floored = $length->floor();  // 1 m
```

### ceil()

```php
public function ceil(): self
```

Round the value up to the nearest integer.

**Returns:**
- `Quantity` - A new Quantity with the value rounded up, in the same unit.

**Examples:**
```php
$length = new Length(1.1, 'm');
$ceiled = $length->ceil();  // 2 m
```

---

## Arithmetic Methods

### neg()

```php
public function neg(): self
```

Negate this measurement.

**Returns:**
- `Quantity` - A new Quantity with negated value.

### add()

```php
public function add(self $other): self
```

Add another measurement to this one. The other Quantity must have the same dimension. The result is expressed in this measurement's unit.

**Parameters:**
- `$other` (self) - Another Quantity with the same dimension.

**Returns:**
- `Quantity` - Sum in this measurement's unit.

**Throws:**
- [`DimensionMismatchException`](Exceptions/DimensionMismatchException.md) - If the quantities have different dimensions.

**Examples:**
```php
$a = new Length(100, 'm');
$b = new Length(2, 'km');
$sum = $a->add($b);                    // 2100 m
$sum = $a->add(new Length(50, 'cm'));   // 100.5 m
```

### sub()

```php
public function sub(self $other): self
```

Subtract another measurement from this one. The other Quantity must have the same dimension. The result is expressed in this measurement's unit.

**Parameters:**
- `$other` (self) - Another Quantity with the same dimension.

**Returns:**
- `Quantity` - Difference in this measurement's unit.

**Throws:**
- [`DimensionMismatchException`](Exceptions/DimensionMismatchException.md) - If the quantities have different dimensions.

**Examples:**
```php
$a = new Length(2, 'km');
$b = new Length(500, 'm');
$diff = $a->sub($b);  // 1.5 km
```

### mul()

```php
public function mul(float|self|string|UnitInterface $other): self
```

Multiply this measurement by a scalar, another Quantity, or a unit.

When multiplying by a scalar, the unit is preserved. When multiplying by a Quantity or unit, the units are combined.

**Parameters:**
- `$other` (float|self|string|UnitInterface) - A scalar, Quantity, unit symbol, or UnitInterface.

**Returns:**
- `Quantity` - Product with combined units.

**Throws:**
- `DomainException` - If the result overflows to infinity.

**Examples:**
```php
$length = new Length(10, 'm');
$doubled = $length->mul(2);        // 20 m
$area = $length->mul($length);     // 100 m2
$ms = $length->mul('s');           // 10 m*s
```

### div()

```php
public function div(float|self|string|UnitInterface $other): self
```

Divide this measurement by a scalar, another Quantity, or a unit.

When dividing by a scalar, the unit is preserved. When dividing by a Quantity or unit, the units are combined with inverse exponents. Dividing by the same dimension cancels out, yielding a dimensionless result.

**Parameters:**
- `$other` (float|self|string|UnitInterface) - A scalar, Quantity, unit symbol, or UnitInterface.

**Returns:**
- `Quantity` - Quotient with adjusted units.

**Throws:**
- `DivisionByZeroError` - If dividing by zero.

### pow()

```php
public function pow(int $exponent): self
```

Raise this Quantity to an integer power.

**Parameters:**
- `$exponent` (int) - The exponent.

**Returns:**
- `Quantity` - Result with exponentiated units.

**Examples:**
```php
$length = new Length(10, 'm');
$area = $length->sqr();     // 100 m2
$volume = $length->pow(3);  // 1000 m3
```

### sqr()

```php
public function sqr(): self
```

Square this Quantity. Equivalent to `pow(2)`, but more efficient and readable.

**Returns:**
- `Quantity` - Result with squared value and units.

**Example:**
```php
$velocity = Quantity::create(3, 'm/s');
$result = $velocity->sqr();  // 9 m2/s2
```

### inv()

```php
public function inv(): self
```

Invert this quantity (1/x).

**Returns:**
- `Quantity` - A new Quantity with inverted value and unit.

**Throws:**
- `DivisionByZeroError` - If value is zero.

---

## Comparison Methods

### compare()

```php
public function compare(mixed $other): int
```

Compare two Quantities for ordering.

**Parameters:**
- `$other` (mixed) - The value to compare with.

**Returns:**
- `int` - -1 if less, 0 if equal, 1 if greater.

**Throws:**
- `IncomparableTypesException` - If other is not a Quantity.
- [`DimensionMismatchException`](Exceptions/DimensionMismatchException.md) - If quantities have different dimensions.

### approxEqual()

```php
public function approxEqual(
    mixed $other,
    float $relTol = Floats::DEFAULT_RELATIVE_TOLERANCE,
    float $absTol = Floats::DEFAULT_ABSOLUTE_TOLERANCE
): bool
```

Check if two Quantities are approximately equal within tolerances.

**Parameters:**
- `$other` (mixed) - The value to compare with.
- `$relTol` (float) - Relative tolerance.
- `$absTol` (float) - Absolute tolerance.

**Returns:**
- `bool` - True if approximately equal.

**Examples:**
```php
$a = new Length(1, 'km');
$b = new Length(1000.0001, 'm');
$a->approxEqual($b);  // true
```

---

## String Methods

### parse()

```php
public static function parse(string $input): self
```

Parse a string representation into a Quantity.

Supports single-value strings (e.g. "123.45 km") and multi-part strings (e.g. "5h 30min 45s"). Subclasses may override this to support additional formats.

When called from a subclass (e.g. `Length::parse()`), the parsed unit's dimension must match the subclass's dimension. When called as `Quantity::parse()`, any valid unit is accepted.

**Parameters:**
- `$input` (string) - The string to parse.

**Returns:**
- `Quantity` - The parsed Quantity.

**Throws:**
- `FormatException` - If the format is invalid.
- [`UnknownUnitException`](Exceptions/UnknownUnitException.md) - If units are unknown.
- [`DimensionMismatchException`](Exceptions/DimensionMismatchException.md) - If called on a subclass and the parsed unit has a different dimension.

**Examples:**
```php
$length = Length::parse('123.45 km');
$angle = Angle::parse('90deg');
$time = Time::parse('1.5e3 ms');
$duration = Time::parse('5h 30min 45s');

// Quantity::parse() accepts any unit.
$mass = Quantity::parse('50 kg');  // Mass object

// Subclass parse rejects wrong dimensions.
Length::parse('123 kg');  // DimensionMismatchException
```

### formatValue()

```php
public static function formatValue(
    float $value,
    string $specifier = 'g',
    ?int $precision = null,
    ?bool $trimZeros = null,
    bool $ascii = false
): string
```

Format a numeric value as a string. This is a static utility method used internally by `format()` but also available for standalone use.

**Parameters:**

- `$specifier` (string) - The format specifier.

| Specifier | Description                                                                    |
| --------- |--------------------------------------------------------------------------------|
| `'e'`     | Scientific notation with lowercase `e`.                                        |
| `'E'`     | Scientific notation with uppercase `E`.                                        |
| `'f'`     | Fixed-point notation (locale-aware).                                           |
| `'F'`     | Fixed-point notation (non-locale-aware, always uses `.` as decimal separator). |
| `'g'`     | Shortest of `e` or `f` (lower-case `e`, locale-aware). **Default.**            |
| `'G'`     | Shortest of `E` or `f` (upper-case `E`, locale-aware).                         |
| `'h'`     | Shortest of `e` or `F` (lower-case `e`, non-locale-aware).                     |
| `'H'`     | Shortest of `E` or `F` (upper-case `E`, non-locale-aware).                     |

- `$precision` (?int) - Number of decimal places for `e`/`E`/`f`/`F` or significant digits for `g`/`G`/`h`/`H`. `null` uses the `sprintf` default (usually 6).
- `$trimZeros` (?bool) - Controls trailing zero trimming:
  - `null` (default) — auto: trims when `$precision` is null, preserves when `$precision` is explicit.
  - `true` — always trim trailing zeros (and trailing decimal point).
  - `false` — never trim; preserve all digits.
- `$ascii` (bool) - If `true`, use ASCII `e` notation. If `false` (default), use `×10` with superscript exponents.

**Returns:**
- `string` - The formatted value string.

**Throws:**
- `DomainException` - If the specifier is invalid or precision is outside 0–17.

**Examples:**
```php
Quantity::formatValue(5.0);                       // "5"
Quantity::formatValue(5.0, 'f', 2);               // "5.00"
Quantity::formatValue(5.0, 'f', 2, true);         // "5"
Quantity::formatValue(1500.0, 'e', 2);            // "1.50×10³"
Quantity::formatValue(1500.0, 'e', 2, ascii: true); // "1.50e+3"
```

### format()

```php
public function format(
    string $specifier = 'g',
    ?int $precision = null,
    ?bool $trimZeros = null,
    ?bool $includeSpace = null,
    bool $ascii = false
): string
```

Format the measurement as a string with value and unit.

See `formatValue()` for details on the `$specifier`, `$precision`, `$trimZeros`, and `$ascii` parameters.

**Parameters:**
- `$specifier` (string) - The format specifier (see `formatValue()` for the table).
- `$precision` (?int) - Number of decimal places for `e`/`E`/`f`/`F` or significant digits for `g`/`G`/`h`/`H`. `null` uses the `sprintf` default (usually 6).
- `$trimZeros` (?bool) - Controls trailing zero trimming. `null` (default) trims when precision is null, preserves when explicit. `true` always trims, `false` never trims.
- `$includeSpace` (?bool) - Space between value and unit. `null` = auto (no space for symbol-only units like `°`). `true` = always. `false` = never.
- `$ascii` (bool) - If `true`, use ASCII symbols and `e` notation. If `false` (default), use Unicode symbols and superscript notation.

**Returns:**
- `string` - The formatted string.

**Throws:**
- `DomainException` - If the specifier is invalid or precision is outside 0–17.

**Examples:**
```php
$length = new Length(1500.0, 'm');
$length->format();                                // "1500 m"
$length->format('f', 2);                          // "1500.00 m"
$length->format('f', 2, trimZeros: true);         // "1500 m"
$length->format('e', 2);                          // "1.50×10³ m"
$length->format('e', 2, ascii: true);             // "1.50e+3 m"

$angle = new Angle(90, 'deg');
$angle->format();                                 // "90°"
$angle->format(includeSpace: true);               // "90 °"
```

### \_\_toString()

```php
public function __toString(): string
```

Convert to string using default formatting.

**Returns:**
- `string` - The measurement as a string.

---

## Parts Methods

Parts methods allow decomposing a quantity into multiple unit components (e.g. hours, minutes, seconds) and reconstructing from them.

### getDefaultPartUnitSymbols()

```php
public static function getDefaultPartUnitSymbols(): array
```

Get the default part unit symbols for output methods.

**Returns:**
- `list<string>` - The default unit symbols.

### setDefaultPartUnitSymbols()

```php
public static function setDefaultPartUnitSymbols(array $symbols): void
```

Set the default part unit symbols for output methods.

**Parameters:**
- `$symbols` (list\<string\>) - The unit symbols from largest to smallest.

**Throws:**
- [`UnknownUnitException`](Exceptions/UnknownUnitException.md) - If the array contains unknown unit symbols.
- `DomainException` - If the array is empty.
- `InvalidArgumentException` - If the array contains non-string items.

### getDefaultResultUnitSymbol()

```php
public static function getDefaultResultUnitSymbol(): string
```

Get the default result unit symbol for input methods.

**Returns:**
- `string` - The default result unit symbol.

### setDefaultResultUnitSymbol()

```php
public static function setDefaultResultUnitSymbol(string $symbol): void
```

Set the default result unit symbol for input methods.

**Parameters:**
- `$symbol` (string) - The unit symbol.

**Throws:**
- [`UnknownUnitException`](Exceptions/UnknownUnitException.md) - If the unit is unknown.

### fromParts()

```php
public static function fromParts(array $parts, ?string $resultUnitSymbol = null): static
```

Create a Quantity from component parts.

**Parameters:**
- `$parts` (array) - Array of unit symbol => value pairs, optionally with 'sign' key.
- `$resultUnitSymbol` (?string) - Result unit, or null for class default.

**Returns:**
- `Quantity` - The combined Quantity.

**Examples:**
```php
$angle = Angle::fromParts(['deg' => 45, 'arcmin' => 30, 'arcsec' => 15]);
$duration = Time::fromParts(['h' => 2, 'min' => 30, 's' => 45]);
```

### toParts()

```php
public function toParts(?array $partUnitSymbols = null, ?int $precision = null): array
```

Convert to component parts.

**Parameters:**
- `$partUnitSymbols` (?array) - Array of unit symbols from largest to smallest, or null for class default.
- `$precision` (?int) - Decimal places for smallest unit, or null for no rounding.

**Returns:**
- `array` - Array with 'sign' key and unit symbol => value pairs.

### parseParts()

```php
public static function parseParts(string $input, ?string $resultUnitSymbol = null): static
```

Parse a multi-part string into a Quantity.

Parses strings like "4y 5mo 6d 12h 34min 56.789s" where each part is a value immediately followed by a unit symbol, separated by whitespace.

**Parameters:**
- `$input` (string) - The string to parse.
- `$resultUnitSymbol` (?string) - The unit to use for the resulting Quantity, or null for the class default.

**Returns:**
- `Quantity` - A new Quantity representing the sum of the parts.

**Throws:**
- `FormatException` - If the input string is invalid.
- `DomainException` - If no result unit symbol is provided and no default is set.

**Examples:**
```php
$time = Time::parseParts('5h 30min 45s', 's');  // 19845 s
$angle = Angle::parseParts("12deg 34arcmin 56.789arcsec");
```

### formatParts()

```php
public function formatParts(
    ?array $partUnitSymbols = null,
    ?int $precision = null,
    bool $showZeros = false,
    bool $ascii = false
): string
```

Format as component parts.

Only the smallest unit may have a decimal point. Larger units will be integers. Zero-value components are omitted by default unless `$showZeros` is true.

**Parameters:**
- `$partUnitSymbols` (?array) - Array of unit symbols from largest to smallest, or null for class default.
- `$precision` (?int) - Decimal places for smallest unit, or null for no rounding.
- `$showZeros` (bool) - Include zero-value components.
- `$ascii` (bool) - Use ASCII symbols only.

**Returns:**
- `string` - Formatted string like "5h 30min 45s".

---

## See Also

- **[DerivedUnit](Internal/DerivedUnit.md)** - Unit representation used by Quantity.
- **[Converter](Internal/Converter.md)** - Handles unit conversions.
- **[DimensionService](Services/DimensionService.md)** - Dimension codes and base unit mappings.
- **[QuantityType](Internal/QuantityType.md)** - Quantity type metadata.
- **[PhysicalConstant](PhysicalConstant.md)** - Physical constants as Quantities.
