# UnitTerm

Represents a decomposed unit symbol with prefix and exponent.

## Overview

The `UnitTerm` class represents a unit symbol like 'km2' decomposed into its components:
- **unit**: The base Unit object (e.g., metre)
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

The exponent (e.g., 2 for m2, -1 for s-1). Must be between -9 and 9, and cannot be 0.

### asciiSymbol

```php
public string $asciiSymbol { get; }
```

The full unit symbol with prefix and exponent in ASCII format (e.g., 'km2', 'ms-1').

### unicodeSymbol

```php
public string $unicodeSymbol { get; }
```

The full unit symbol with superscript exponent in Unicode format (e.g., 'km²', 'ms⁻¹').

### unprefixedAsciiSymbol

```php
public string $unprefixedAsciiSymbol { get; }
```

The unit symbol with exponent but without prefix (e.g., 'm2', 's-1').

### unexponentiatedAsciiSymbol

```php
public string $unexponentiatedAsciiSymbol { get; }
```

The unit symbol with prefix but without exponent (e.g., 'km', 'ms').

### prefixMultiplier

```php
public float $prefixMultiplier { get; }
```

The prefix multiplier (e.g., 1000 for kilo). Returns 1.0 if no prefix.

### multiplier

```php
public float $multiplier { get; }
```

The prefix multiplier raised to the exponent (e.g., 10002 = 1e6 for km2).

### dimension

```php
public string $dimension { get; }
```

The dimension code with exponent applied (e.g., 'L2' for m2).

## Constructor

### __construct()

```php
public function __construct(
    string|Unit $unit = '',
    null|string|Prefix $prefix = null,
    int $exponent = 1
)
```

Create a new UnitTerm instance.

**Parameters:**
- `$unit` (string|Unit) - The unit or its symbol (default: empty string for dimensionless)
- `$prefix` (null|string|Prefix) - The prefix symbol or object (default: null)
- `$exponent` (int) - The exponent (default: 1)

**Throws:**
- `DomainException` - If the unit is unknown, prefix is invalid for the unit, or exponent is invalid

**Examples:**
```php
// Simple unit
$metre = new UnitTerm('m');

// Prefixed unit
$kilometre = new UnitTerm('m', 'k');

// With exponent
$squareKilometre = new UnitTerm('m', 'k', 2);

// Inverse
$perSecond = new UnitTerm('s', null, -1);
```

## Factory Methods

### getBySymbol()

```php
public static function getBySymbol(string $symbol): ?self
```

Look up a unit or prefixed unit by its symbol. Symbol uniqueness is enforced by `UnitRegistry`, so at most one match is possible.

**Parameters:**
- `$symbol` (string) - The prefixed unit symbol to search for

**Returns:**
- `?self` - The matching UnitTerm, or null if not found

**Examples:**
```php
$km = UnitTerm::getBySymbol('km');
// UnitTerm(metre, kilo)

$m = UnitTerm::getBySymbol('m');
// UnitTerm(metre)

$unknown = UnitTerm::getBySymbol('xyz');
// null
```

### toUnitTerm()

```php
public static function toUnitTerm(string|Unit|self $value): self
```

Convert any unit representation to a UnitTerm.

**Parameters:**
- `$value` (string|Unit|self) - The value to convert

**Returns:**
- `self` - The equivalent UnitTerm

**Throws:**
- `DomainException` - If a string cannot be parsed

### parse()

```php
public static function parse(string $symbol): self
```

Parse a string into a UnitTerm.

**Parameters:**
- `$symbol` (string) - The unit symbol (e.g., 'm2', 'km', 's-1')

**Returns:**
- `self` - The parsed UnitTerm

**Throws:**
- `FormatException` - If the format is invalid
- `DomainException` - If the unit is unknown or exponent is zero

**Behavior:**
- Accepts ASCII exponents (m2, s-1)
- Accepts Unicode superscript exponents (m2, s-1)
- Recognises all registered units and prefixes

**Examples:**
```php
$m2 = UnitTerm::parse('m2');
$km = UnitTerm::parse('km');
$perSecond = UnitTerm::parse('s-1');
$perSecondUnicode = UnitTerm::parse('s-1');
```

## Inspection Methods

### isSi()

```php
public function isSi(): bool
```

Check if this unit term's base unit belongs to the SI system.

**Returns:**
- `bool` - True if the base unit is an SI unit

### isBase()

```php
public function isBase(): bool
```

Check if this unit term's unit is a base unit (single-dimension, not expandable).

**Returns:**
- `bool` - True if the unit is a base unit

### isSiBase()

```php
public function isSiBase(): bool
```

Check if this unit term is an SI base unit (with or without exponent). Returns true for kg, m, s, A, K, cd, mol, rad, B, XAU and any of these with exponents (e.g., m2, s-1). Returns false for prefixed units like km or g.

**Returns:**
- `bool` - True if the unit is an SI base unit

### isExpandable()

```php
public function isExpandable(): bool
```

Check if this unit term's unit is expandable into base units (e.g., N, J, Pa).

**Returns:**
- `bool` - True if the unit term is expandable

## Transformation Methods

### inv()

```php
public function inv(): self
```

Return a new UnitTerm with the exponent negated.

**Returns:**
- `self` - A new instance with inverted exponent

**Examples:**
```php
$perSecond = new UnitTerm('s', null, -1);
$second = $perSecond->inv();
echo $second->exponent; // 1
```

### withExponent()

```php
public function withExponent(int $exp): self
```

Return a new UnitTerm with a different exponent.

**Parameters:**
- `$exp` (int) - The new exponent

**Returns:**
- `self` - A new instance with the specified exponent

**Throws:**
- `DomainException` - If exponent is 0 or outside -9 to 9

### removeExponent()

```php
public function removeExponent(): self
```

Return a new UnitTerm with exponent set to 1.

**Returns:**
- `self` - A new instance with exponent 1

### pow()

```php
public function pow(int $exponent): self
```

Return a new UnitTerm with the exponent multiplied.

**Parameters:**
- `$exponent` (int) - The exponent to raise to

**Returns:**
- `self` - A new instance with multiplied exponent

**Examples:**
```php
$m = new UnitTerm('m');
$m2 = $m->pow(2);
$m6 = $m2->pow(3);
echo $m6->exponent; // 6
```

### removePrefix()

```php
public function removePrefix(): self
```

Return a new UnitTerm with the prefix removed.

**Returns:**
- `self` - A new instance without prefix

**Examples:**
```php
$km = new UnitTerm('m', 'k');
$m = $km->removePrefix();
echo $m->asciiSymbol; // 'm'
```

## String Methods

### regex()

```php
public static function regex(): string
```

Get the regex pattern for matching a unit term.

**Returns:**
- `string` - The regex pattern (without delimiters or anchors)

### format()

```php
public function format(bool $ascii = false): string
```

Format the unit term as a string.

**Parameters:**
- `$ascii` (bool) - If true, return ASCII format; if false (default), return Unicode format

**Returns:**
- `string` - The formatted unit term symbol

**Examples:**
```php
$term = new UnitTerm('m', 'k', 2);
$term->format(true);  // 'km2'
$term->format(false); // 'km²'
```

### __toString()

```php
public function __toString(): string
```

Convert to string using Unicode format.

**Returns:**
- `string` - The Unicode representation

## Comparison Methods

### equal()

```php
public function equal(mixed $other): bool
```

Check if this UnitTerm equals another.

**Parameters:**
- `$other` (mixed) - The value to compare

**Returns:**
- `bool` - True if unit, prefix, and exponent all match

## Usage Examples

### Building Compound Units

```php
use Galaxon\Quantities\Internal\DerivedUnit;use Galaxon\Quantities\Internal\UnitTerm;

// Build velocity: m/s
$metre = new UnitTerm('m');
$perSecond = new UnitTerm('s', null, -1);

$velocity = new DerivedUnit([$metre, $perSecond]);
echo $velocity->asciiSymbol; // 'm/s'

// Build acceleration: m/s2
$perSecondSquared = new UnitTerm('s', null, -2);
$acceleration = new DerivedUnit([$metre, $perSecondSquared]);
echo $acceleration->asciiSymbol; // 'm/s2'
```

### Working with Prefixes

```php
use Galaxon\Quantities\Internal\UnitTerm;

$km2 = new UnitTerm('m', 'k', 2);

echo $km2->prefix->name;      // 'kilo'
echo $km2->prefixMultiplier;  // 1000
echo $km2->multiplier;        // 1000000 (10002)
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

## See Also

- **[Unit](Unit.md)** - The base unit representation
- **[DerivedUnit](DerivedUnit.md)** - Compound unit using UnitTerms
- **[Prefix](Prefix.md)** - SI and binary prefixes
- **[UnitInterface](UnitInterface.md)** - Interface for all unit types
