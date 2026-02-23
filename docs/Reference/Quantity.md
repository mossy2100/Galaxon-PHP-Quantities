# Quantity

Base class for physical measurements with units.

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

## Constructor

### __construct()

```php
public function __construct(float $value, null|string|UnitInterface $unit = null)
```

Create a new Quantity with the specified value and unit.

The `Quantity` constructor cannot be called directly — use a specific subclass constructor (e.g. `new Length(...)`) or the `Quantity::create()` factory method. This ensures the correct subclass is always used for each dimension.

**Parameters:**
- `$value` (float) - The numeric value.
- `$unit` (null|string|UnitInterface) - The unit (symbol string, object, or null for dimensionless).

**Throws:**
- `LogicException` - If `new Quantity()` is called directly or the wrong subclass constructor is called for the unit's dimension.
- `DomainException` - If the value is non-finite (INF or NAN) or if the unit string contains unknown units.
- `FormatException` - If the unit string cannot be parsed.

**Examples:**
```php
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;

$length = new Length(100, 'm');
$mass = new Mass(5.5, 'kg');
```

## Factory Methods

### create()

```php
public static function create(float $value, null|string|UnitInterface $unit): self
```

Create a Quantity of the appropriate type for the given unit.

Uses the dimension class registry to instantiate the correct subclass. If no subclass is registered for the dimension, a generic Quantity is created.

**Parameters:**
- `$value` (float) - The numeric value.
- `$unit` (null|string|UnitInterface) - The unit.

**Returns:**
- `Quantity` - A Quantity of the appropriate type.

**Throws:**
- `DomainException` - If the value is non-finite (INF or NAN) or the unit string contains unknown units.
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
- `DomainException` - If a unit string contains unknown units.
- `LogicException` - If no conversion path exists between the units.

**Examples:**
```php
// Convert 100 degrees Fahrenheit to Celsius.
$celsius = Quantity::convert(100, 'degF', 'degC');

// Convert 5 miles to kilometres.
$km = Quantity::convert(5, 'mi', 'km');
```

## Transformation Methods

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
- `DomainException` - If the destination unit is invalid.
- `LogicException` - If no conversion path exists.

**Examples:**
```php
$meters = new Length(1000, 'm');
$km = $meters->to('km');        // 1 km
$feet = $meters->to('ft');      // 3280.84 ft
```

### toSi()

```php
public function toSi(bool $simplify = true, bool $autoPrefix = true): self
```

Convert this quantity to SI units.

**Parameters:**
- `$simplify` (bool) - If true, use derived units where possible (e.g., N instead of kg\*m/s2).
- `$autoPrefix` (bool) - If true, apply the best engineering prefix.

**Returns:**
- `Quantity` - A new Quantity in SI units.

**Examples:**
```php
$force = new Force(1000, 'N');
$si = $force->toSi();  // 1 kN (with auto-prefix)

$energy = Quantity::create(1, 'kW*h');
$si = $energy->toSi();  // 3.6 MJ
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

### withValue()

```php
public function withValue(float $value): self
```

Create a new Quantity with the same unit but a different value.

Returns the same instance if the value is unchanged.

**Parameters:**
- `$value` (float) - The new numeric value.

**Returns:**
- `Quantity` - A new Quantity with the given value in the same unit.

**Examples:**
```php
$length = new Length(10, 'm');
$doubled = $length->withValue(20);  // 20 m
```

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

Merge units with the same dimension (e.g., m*ft -> m2).

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
public function simplify(bool $autoPrefix = true): self
```

Substitute base units for derived units where possible (e.g., kg*m/s2 -> N).

**Parameters:**
- `$autoPrefix` (bool) - If true, apply the best prefix.

**Returns:**
- `Quantity` - A new Quantity with simplified units.

## Arithmetic Methods

### abs()

```php
public function abs(): self
```

Get the absolute value of this measurement.

**Returns:**
- `Quantity` - A new Quantity with non-negative value.

### neg()

```php
public function neg(): self
```

Negate this measurement.

**Returns:**
- `Quantity` - A new Quantity with negated value.

### add()

```php
public function add(self|float $otherOrValue, null|string|UnitInterface $otherUnit = null): self
```

Add another measurement to this one.

**Parameters:**
- `$otherOrValue` (self|float) - Another Quantity or numeric value.
- `$otherUnit` (null|string|UnitInterface) - Unit if numeric value provided.

**Returns:**
- `Quantity` - Sum in this measurement's unit.

**Examples:**
```php
$a = new Length(100, 'm');
$b = new Length(2, 'km');
$sum = $a->add($b);          // 2100 m
$sum = $a->add(50, 'cm');    // 100.5 m
```

### sub()

```php
public function sub(self|float $otherOrValue, null|string|UnitInterface $otherUnit = null): self
```

Subtract another measurement from this one.

**Parameters:**
- `$otherOrValue` (self|float) - Another Quantity or numeric value.
- `$otherUnit` (null|string|UnitInterface) - Unit if numeric value provided.

**Returns:**
- `Quantity` - Difference in this measurement's unit.

### mul()

```php
public function mul(float|self $otherOrValue, null|string|UnitInterface $otherUnit = null): self
```

Multiply this measurement by a scalar or another Quantity.

**Parameters:**
- `$otherOrValue` (float|self) - Scalar or another Quantity.
- `$otherUnit` (null|string|UnitInterface) - Unit if numeric value provided.

**Returns:**
- `Quantity` - Product with combined units (merged if same dimension).

**Examples:**
```php
$length = new Length(10, 'm');
$doubled = $length->mul(2);        // 20 m
$area = $length->mul($length);     // 100 m2
```

### div()

```php
public function div(float|self $otherOrValue, null|string|UnitInterface $otherUnit = null): self
```

Divide this measurement by a scalar or another Quantity.

**Parameters:**
- `$otherOrValue` (float|self) - Divisor scalar or Quantity.
- `$otherUnit` (null|string|UnitInterface) - Unit if numeric value provided.

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
$area = $length->pow(2);    // 100 m2
$volume = $length->pow(3);  // 1000 m3
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
- `DomainException` - If quantities have different dimensions.

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

## String Methods

### parse()

```php
public static function parse(string $input): self
```

Parse a string representation into a Quantity.

Supports single-value strings (e.g. "123.45 km") and multi-part strings (e.g. "5h 30min 45s"). Subclasses may override this to support additional formats (e.g. Angle supports DMS notation).

**Parameters:**
- `$input` (string) - The string to parse.

**Returns:**
- `Quantity` - The parsed Quantity.

**Throws:**
- `FormatException` - If the format is invalid.
- `DomainException` - If units are unknown.

**Examples:**
```php
$length = Length::parse('123.45 km');
$angle = Angle::parse('90deg');
$time = Time::parse('1.5e3 ms');
$duration = Time::parse('5h 30min 45s');
```

### format()

```php
public function format(
    string $specifier = 'f',
    ?int $precision = null,
    ?bool $includeSpace = null,
    bool $ascii = false
): string
```

Format the measurement as a string.

When `$precision` is null, trailing zeros are automatically trimmed. When an explicit precision is given, all digits are preserved (e.g. `format('f', 2)` on 5.0 gives `"5.00 m"`).

When `$ascii` is false (default) and scientific notation is used, the exponent is rendered as `x10` with superscript digits (e.g. `1.50x10^3`) instead of `e+3`.

**Parameters:**
- `$specifier` (string) - Format type: `'f'`/`'F'` (fixed), `'e'`/`'E'` (scientific), `'g'`/`'G'` (shortest), `'h'`/`'H'` (shortest, non-locale-aware). Uppercase variants use uppercase `E` in scientific notation. `'F'`, `'h'`, and `'H'` are non-locale-aware (always use `.` as decimal separator). See [sprintf()](https://www.php.net/manual/en/function.sprintf.php) for details.
- `$precision` (?int) - Number of decimal digits. Null uses sprintf default and trims trailing zeros.
- `$includeSpace` (?bool) - Space between value and unit. Null = auto (no space for symbol-only units like deg).
- `$ascii` (bool) - If true, use ASCII symbols and `e` notation. If false (default), use Unicode symbols and superscript notation.

**Returns:**
- `string` - The formatted string.

**Examples:**
```php
$length = new Length(1500.0, 'm');
$length->format('f', 2);              // "1500.00 m"
$length->format('e', 2);              // "1.50x10^3 m"
$length->format('e', 2, ascii: true); // "1.50e+3 m"

$angle = new Angle(90, 'deg');
$angle->format();                     // "90deg"
$angle->format('f', 2, false);        // "90.00deg"
```

### \_\_toString()

```php
public function __toString(): string
```

Convert to string using default formatting.

**Returns:**
- `string` - The measurement as a string.

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
- `DomainException` - If the array is empty or contains unknown unit symbols.
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
- `DomainException` - If the unit is unknown.

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

## See Also

- **[DerivedUnit](Internal/DerivedUnit.md)** - Unit representation used by Quantity.
- **[Converter](Internal/Converter.md)** - Handles unit conversions.
- **[DimensionService](Services/DimensionService.md)** - Dimension codes and base unit mappings.
- **[QuantityType](Internal/QuantityType.md)** - Quantity type metadata.
- **[PhysicalConstant](PhysicalConstant.md)** - Physical constants as Quantities.
