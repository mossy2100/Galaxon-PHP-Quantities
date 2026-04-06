# UnitTerm

Represents a decomposed unit symbol with prefix and exponent.

---

## Overview

The `UnitTerm` class represents a unit symbol like 'km²' decomposed into its components:
- **unit**: The base Unit object (e.g., meter)
- **prefix**: The SI/binary prefix (e.g., kilo)
- **exponent**: The power (e.g., 2)

This decomposition allows for:
- Prefix arithmetic in conversions
- Exponent manipulation for compound units
- Dimension calculation with exponents applied

The class implements `UnitInterface` and uses the `Equatable` trait for value-based equality.

### Key Features

- Automatic symbol lookup and validation
- Both ASCII and Unicode symbol formatting
- Prefix and exponent manipulation methods
- Immutable transformations
- Dimension code with exponent applied

---

## Properties

### unit

```php
public readonly Unit $unit
```

The base unit without prefix or exponent.

### prefix

```php
public readonly ?Prefix $prefix
```

The SI/binary prefix, or null if none.

### exponent

```php
public readonly int $exponent
```

The exponent (e.g., 2 for *m²*, -1 for *s⁻¹*). Must be between -9 and 9, and cannot be 0. Defaults to 1.

### asciiSymbol

```php
public string $asciiSymbol { get; }
```

The full unit symbol with prefix and exponent in ASCII format (e.g., `'km2'`, `'ms-1'`).

### unicodeSymbol

```php
public string $unicodeSymbol { get; }
```

The full unit symbol with superscript exponent in Unicode format (e.g., `'km²'`, `'ms⁻¹'`).

### unprefixedAsciiSymbol

```php
public string $unprefixedAsciiSymbol { get; }
```

The unit symbol with exponent but without prefix (e.g., `'m2'`, `'s-1'`).

### unexponentiatedAsciiSymbol

```php
public string $unexponentiatedAsciiSymbol { get; }
```

The unit symbol with prefix but without exponent (e.g., `'km'`, `'ms'`).

### prefixMultiplier

```php
public float $prefixMultiplier { get; }
```

The prefix multiplier (e.g., 1000 for kilo). Returns 1.0 if no prefix.

### multiplier

```php
public float $multiplier { get; }
```

The prefix multiplier raised to the exponent (e.g., 1000² = 1e6 for km²).

### dimension

```php
public string $dimension { get; }
```

The dimension code with exponent applied (e.g., `'L2'` for m²). Computed via `DimensionService::pow()`.

### quantityType

```php
public ?QuantityType $quantityType { get; }
```

The quantity type this unit term is for (e.g., the `QuantityType` for length), or `null` if the dimension has no registered quantity type.

---

## Constructor

### \_\_construct()

```php
public function __construct(
    string|Unit $unit = '',
    null|string|Prefix $prefix = null,
    int $exponent = 1
)
```

Create a new UnitTerm instance.

**Parameters:**
- `$unit` (string|Unit) - The unit or its symbol (default: empty string for dimensionless).
- `$prefix` (null|string|Prefix) - The prefix symbol or object (default: `null` for none).
- `$exponent` (int) - The exponent (default: `1`).

**Throws:**
- [`UnknownUnitException`](../Exceptions/UnknownUnitException.md) - If the unit symbol is not recognized.
- `DomainException` - If the prefix is unknown or invalid for the unit, or the exponent is zero or outside the range -9 to 9.

**Examples:**
```php
// Simple unit
$meter = new UnitTerm('m');

// Prefixed unit
$kilometer = new UnitTerm('m', 'k');

// With exponent
$squareKilometer = new UnitTerm('m', 'k', 2);

// Inverse
$perSecond = new UnitTerm('s', null, -1);
```

---

## Factory Methods

### toUnitTerm()

```php
public static function toUnitTerm(string|Unit|self $value): self
```

Convert any unit representation to a UnitTerm. Returns the same instance if already a UnitTerm.

**Parameters:**
- `$value` (string|Unit|self) - The value to convert.

**Returns:** `UnitTerm`

**Throws:**
- [`FormatException`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Exceptions/FormatException.md) - If a string has an invalid format.
- [`UnknownUnitException`](../Exceptions/UnknownUnitException.md) - If a string or Unit symbol is not recognized.
- `DomainException` - If the exponent or prefix is invalid.

### parse()

```php
public static function parse(string $symbol): self
```

Parse a string into a UnitTerm.

**Parameters:**
- `$symbol` (string) - The unit symbol (e.g., `'m2'`, `'km'`, `'s-1'`).

**Returns:** `UnitTerm`

**Throws:**
- [`FormatException`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Exceptions/FormatException.md) - If the format is invalid.
- [`UnknownUnitException`](../Exceptions/UnknownUnitException.md) - If the unit symbol is not recognized.
- `DomainException` - If the exponent is zero.

**Behavior:**
- Accepts ASCII exponents (`m2`, `s-1`)
- Accepts Unicode superscript exponents (`m²`, `s⁻¹`)
- Recognises all registered units and prefixes
- Empty string returns dimensionless unit

---

## Inspection Methods

### isSi()

```php
public function isSi(): bool
```

Check if this unit term's unit belongs to the SI system.

### isBase()

```php
public function isBase(): bool
```

Check if this unit term's unit is a base unit (single-dimension, not expandable).

---

## Comparison Methods

### equal()

```php
public function equal(mixed $other): bool
```

Check if this UnitTerm equals another. Compares by ASCII symbol.

**Parameters:**
- `$other` (mixed) - The value to compare.

**Returns:** `bool` - True if both are `UnitTerm` instances with the same ASCII symbol.

---

## Unary Arithmetic Methods

### inv()

```php
public function inv(): self
```

Return a new UnitTerm with the exponent negated.

**Returns:** `UnitTerm` - A new instance with inverted exponent (e.g., m² → m⁻²).

**Examples:**
```php
$perSecond = new UnitTerm('s', null, -1);
$second = $perSecond->inv();
echo $second->exponent; // 1
```

---

## Power Methods

### pow()

```php
public function pow(int $exponent): self
```

Return a new UnitTerm with the exponent multiplied by the given value.

**Parameters:**
- `$exponent` (int) - The exponent to raise to.

**Returns:** `UnitTerm` - A new instance with multiplied exponent (e.g., m² with exp=3 → m⁶).

**Examples:**
```php
$m = new UnitTerm('m');
$m2 = $m->pow(2);
$m6 = $m2->pow(3);
echo $m6->exponent; // 6
```

---

## Transformation Methods

### withExponent()

```php
public function withExponent(int $exp): self
```

Return a new UnitTerm with the same unit and prefix as the calling object, but with the given exponent.

**Parameters:**
- `$exp` (int) - The new exponent.

**Returns:** `UnitTerm`

**Throws:**
- `DomainException` - If the exponent is 0 or outside -9 to 9.

### removeExponent()

```php
public function removeExponent(): self
```

Return a new UnitTerm with exponent set to 1.

### removePrefix()

```php
public function removePrefix(): self
```

Return a new UnitTerm with the prefix removed.

**Examples:**
```php
$km = new UnitTerm('m', 'k');
$m = $km->removePrefix();
echo $m->asciiSymbol; // 'm'
```

---

## Conversion Methods

### format()

```php
public function format(bool $ascii = false): string
```

Format the unit term as a string.

**Parameters:**
- `$ascii` (bool) - If `true`, return ASCII format; if `false` (default), return Unicode format.

**Returns:** `string` - The formatted unit term symbol.

**Behavior:**
- ASCII: uses digit exponents and ASCII symbols (e.g., `'km2'`).
- Unicode: uses superscript exponents and Unicode symbols (e.g., `'km²'`).

### \_\_toString()

```php
public function __toString(): string
```

Convert to string using Unicode format.

---

## Usage Examples

### Building Compound Units

```php
use Galaxon\Quantities\Internal\DerivedUnit;
use Galaxon\Quantities\Internal\UnitTerm;

// Build velocity: m/s
$meter = new UnitTerm('m');
$perSecond = new UnitTerm('s', null, -1);

$velocity = new DerivedUnit([$meter, $perSecond]);
echo $velocity->asciiSymbol; // 'm/s'

// Build acceleration: m/s2
$perSecondSquared = new UnitTerm('s', null, -2);
$acceleration = new DerivedUnit([$meter, $perSecondSquared]);
echo $acceleration->asciiSymbol; // 'm/s2'
```

### Working with Prefixes

```php
use Galaxon\Quantities\Internal\UnitTerm;

$km2 = new UnitTerm('m', 'k', 2);

echo $km2;                    // 'km²'
echo $km2->asciiSymbol;       // 'km2'
echo $km2->prefix->name;      // 'kilo'
echo $km2->prefixMultiplier;  // 1000
echo $km2->multiplier;        // 1000000 (1000²)
echo $km2->dimension;         // 'L2'

// Remove prefix for conversion
$m2 = $km2->removePrefix();
echo $m2->asciiSymbol; // 'm2'
```

### Parsing and Validation

```php
use Galaxon\Quantities\Internal\UnitTerm;

// Parse with various formats
$term1 = UnitTerm::parse('km2');
$term2 = UnitTerm::parse('ms-1');
$term3 = UnitTerm::parse('degC');

// Check dimensions
echo $term1->dimension; // 'L2'
echo $term2->dimension; // 'T-1'
```

---

## See Also

- **[Unit](Unit.md)** - The base unit representation.
- **[DerivedUnit](DerivedUnit.md)** - Compound unit using UnitTerms.
- **[Prefix](Prefix.md)** - SI and binary prefixes.
- **[UnitInterface](UnitInterface.md)** - Interface for all unit types.
