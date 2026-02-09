# Quantity

Abstract base class for physical measurements with units.

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
- Lazy initialization of converters per dimension

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

**Parameters:**
- `$value` (float) - The numeric value
- `$unit` (null|string|UnitInterface) - The unit (symbol string, object, or null for dimensionless)

**Throws:**
- `DomainException` - If the value is non-finite (INF or NAN)
- `FormatException` - If the unit string cannot be parsed
- `DomainException` - If the unit string contains unknown units
- `LogicException` - If calling the wrong constructor for the unit's dimension

**Examples:**
```php
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;

$length = new Length(100, 'm');
$mass = new Mass(5.5, 'kg');
$area = new Area(1000, 'm2');
```

## Factory Methods

### create()

```php
public static function create(float $value, null|string|UnitInterface $unit): self
```

Create a Quantity of the appropriate type for the given unit.

Uses the dimension class registry to instantiate the correct subclass.

**Parameters:**
- `$value` (float) - The numeric value
- `$unit` (null|string|UnitInterface) - The unit

**Returns:**
- `self` - A Quantity of the appropriate type

**Examples:**
```php
use Galaxon\Quantities\Quantity;

// Creates a Length object
$length = Quantity::create(100, 'm');

// Creates a Force object
$force = Quantity::create(10, 'N');

// Creates a generic Quantity for unregistered dimensions
$custom = Quantity::create(5, 'kg*m2/s3');
```

## Transformation Methods

### to()

```php
public function to(string|UnitInterface $destUnit): self
```

Convert this Quantity to a different unit.

**Parameters:**
- `$destUnit` (string|UnitInterface) - The destination unit

**Returns:**
- `self` - A new Quantity in the specified unit

**Throws:**
- `DomainException` - If the destination unit is invalid
- `LogicException` - If no conversion path exists

**Examples:**
```php
$metres = new Length(1000, 'm');
$km = $metres->to('km');        // 1 km
$feet = $metres->to('ft');      // 3280.84 ft
```

### toSi()

```php
public function toSi(bool $simplify = true, bool $autoPrefix = true): self
```

Convert this quantity to SI units.

**Parameters:**
- `$simplify` (bool) - If true, use derived units where possible (e.g., N instead of kg*m/s2)
- `$autoPrefix` (bool) - If true, apply the best engineering prefix

**Returns:**
- `self` - A new Quantity in SI units

**Examples:**
```php
$force = new Force(1000, 'N');
$si = $force->toSi();  // 1 kN (with auto-prefix)

$energy = Quantity::create(1, 'kWh');
$si = $energy->toSi();  // 3.6 MJ
```

### toSiBase()

```php
public function toSiBase(): self
```

Convert to SI base units without simplification or auto-prefixing.

**Returns:**
- `self` - A new Quantity in SI base units

**Examples:**
```php
$force = new Force(1, 'N');
$base = $force->toSiBase();  // 1 kg*m/s2
```

### expand()

```php
public function expand(): self
```

Expand named units to base units (e.g., N -> kg*m/s2).

**Returns:**
- `self` - A new Quantity with expanded units

### merge()

```php
public function merge(): self
```

Merge units with the same dimension (e.g., m*ft -> m2).

**Returns:**
- `self` - A new Quantity with merged units

### autoPrefix()

```php
public function autoPrefix(): self
```

Apply the best engineering prefix to the first unit term.

**Returns:**
- `self` - A new Quantity with optimal prefix

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
- `$autoPrefix` (bool) - If true, apply the best prefix

**Returns:**
- `self` - A new Quantity with simplified units

## Arithmetic Methods

### abs()

```php
public function abs(): self
```

Get the absolute value of this measurement.

**Returns:**
- `self` - A new Quantity with non-negative value

### neg()

```php
public function neg(): self
```

Negate this measurement.

**Returns:**
- `self` - A new Quantity with negated value

### add()

```php
public function add(self|float $otherOrValue, null|string|UnitInterface $otherUnit = null): self
```

Add another measurement to this one.

**Parameters:**
- `$otherOrValue` (self|float) - Another Quantity or numeric value
- `$otherUnit` (null|string|UnitInterface) - Unit if numeric value provided

**Returns:**
- `self` - Sum in this measurement's unit

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
- `$otherOrValue` (self|float) - Another Quantity or numeric value
- `$otherUnit` (null|string|UnitInterface) - Unit if numeric value provided

**Returns:**
- `self` - Difference in this measurement's unit

### mul()

```php
public function mul(float|self $otherOrValue, null|string|UnitInterface $otherUnit = null): self
```

Multiply this measurement by a scalar or another Quantity.

**Parameters:**
- `$otherOrValue` (float|self) - Scalar or another Quantity
- `$otherUnit` (null|string|UnitInterface) - Unit if numeric value provided

**Returns:**
- `self` - Product with combined units (merged if same dimension)

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
- `$otherOrValue` (float|self) - Divisor scalar or Quantity
- `$otherUnit` (null|string|UnitInterface) - Unit if numeric value provided

**Returns:**
- `self` - Quotient with adjusted units

**Throws:**
- `DivisionByZeroError` - If dividing by zero

### pow()

```php
public function pow(int $exponent): self
```

Raise this Quantity to an integer power.

**Parameters:**
- `$exponent` (int) - The exponent

**Returns:**
- `self` - Result with exponentiated units

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
- `self` - A new Quantity with inverted value and unit

**Throws:**
- `DivisionByZeroError` - If value is zero

## Comparison Methods

### compare()

```php
public function compare(mixed $other): int
```

Compare two Quantities for ordering.

**Parameters:**
- `$other` (mixed) - The value to compare with

**Returns:**
- `int` - -1 if less, 0 if equal, 1 if greater

**Throws:**
- `IncomparableTypesException` - If other is not a Quantity
- `DomainException` - If quantities have different dimensions

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
- `$other` (mixed) - The value to compare with
- `$relTol` (float) - Relative tolerance
- `$absTol` (float) - Absolute tolerance

**Returns:**
- `bool` - True if approximately equal

**Examples:**
```php
$a = new Length(1, 'km');
$b = new Length(1000.0001, 'm');
$a->approxEqual($b);  // true
```

## String Methods

### parse()

```php
public static function parse(string $value): self
```

Parse a string representation into a Quantity.

**Parameters:**
- `$value` (string) - The string to parse (e.g., "123.45 km")

**Returns:**
- `self` - The parsed Quantity

**Throws:**
- `FormatException` - If the format is invalid
- `DomainException` - If units are unknown

**Examples:**
```php
$length = Length::parse("123.45 km");
$angle = Angle::parse("90deg");
$time = Time::parse("1.5e3 ms");
```

### format()

```php
public function format(
    string $specifier = 'f',
    ?int $precision = null,
    bool $trimZeros = true,
    ?bool $includeSpace = null,
    bool $ascii = false
): string
```

Format the measurement as a string.

**Parameters:**
- `$specifier` (string) - Format type: 'f' (fixed), 'e' (scientific), 'g' (shortest)
- `$precision` (?int) - Number of digits
- `$trimZeros` (bool) - Remove trailing zeros
- `$includeSpace` (?bool) - Space between value and unit (null = auto)
- `$ascii` (bool) - Use ASCII symbols only

**Returns:**
- `string` - The formatted string

**Examples:**
```php
$angle = new Angle(90, 'deg');
$angle->format('f', 2);           // "90.00 deg"
$angle->format('f', 2, true, false); // "90deg"
```

### __toString()

```php
public function __toString(): string
```

Convert to string using default formatting.

**Returns:**
- `string` - The measurement as a string

## Parts Methods

### fromParts()

```php
public static function fromParts(array $parts, ?string $resultUnitSymbol = null): self
```

Create a Quantity from component parts.

**Parameters:**
- `$parts` (array) - Array of unit symbol => value pairs, optionally with 'sign' key
- `$resultUnitSymbol` (?string) - Result unit, or null for class default

**Returns:**
- `self` - The combined Quantity

**Examples:**
```php
$angle = Angle::fromParts(['deg' => 45, 'arcmin' => 30, 'arcsec' => 15]);
$duration = Duration::fromParts(['h' => 2, 'min' => 30, 's' => 45]);
```

### toParts()

```php
public function toParts(
    ?string $largestUnitSymbol = null,
    ?string $smallestUnitSymbol = null,
    ?int $precision = null
): array
```

Convert to component parts.

**Parameters:**
- `$largestUnitSymbol` (?string) - Largest unit to include
- `$smallestUnitSymbol` (?string) - Smallest unit to include
- `$precision` (?int) - Decimal places for smallest unit

**Returns:**
- `array` - Array with 'sign' and unit symbol => value pairs

### formatParts()

```php
public function formatParts(
    ?string $largestUnitSymbol = null,
    ?string $smallestUnitSymbol = null,
    ?int $precision = null,
    bool $showZeros = false,
    bool $ascii = false
): string
```

Format as component parts.

**Parameters:**
- `$largestUnitSymbol` (?string) - Largest unit to include
- `$smallestUnitSymbol` (?string) - Smallest unit to include
- `$precision` (?int) - Decimal places for smallest unit
- `$showZeros` (bool) - Include zero-value components
- `$ascii` (bool) - Use ASCII symbols only

**Returns:**
- `string` - Formatted string like "5h 30min 45s"

## Usage Examples

### Unit Conversion

```php
use Galaxon\Quantities\QuantityType\Length;

$marathon = new Length(42.195, 'km');
echo $marathon->to('mi');  // ~26.2 miles
echo $marathon->to('m');   // 42195 m
```

### Arithmetic

```php
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Time;

$distance = new Length(100, 'm');
$time = new Time(10, 's');

$speed = $distance->div($time);
echo $speed->toSi();  // 10 m/s

$area = $distance->mul($distance);
echo $area;  // 10000 m2
```

### Comparison

```php
use Galaxon\Quantities\QuantityType\Length;

$a = new Length(1, 'km');
$b = new Length(1000, 'm');

if ($a->approxEqual($b)) {
    echo "Equal!";
}

$lengths = [$a, new Length(500, 'm'), $b];
usort($lengths, fn($x, $y) => $x->compare($y));
```

## See Also

- **[DerivedUnit](DerivedUnit.md)** - Unit representation used by Quantity
- **[Converter](Converter.md)** - Handles unit conversions
- **[QuantityType](QuantityType.md)** - Quantity type metadata
- **[PhysicalConstant](PhysicalConstant.md)** - Physical constants as Quantities
