# DerivedUnit

Represents a compound unit composed of one or more unit terms.

## Overview

The `DerivedUnit` class represents compound units like 'kg\*m/s²' (newton) or 'J/(mol\*K)' (molar heat capacity). It maintains a collection of `UnitTerm` objects, each representing a unit with its prefix and exponent.

Key behaviors:
- Unit terms with the same base unit are automatically combined (e.g., km³ * km⁻¹ = km²)
- Supports parsing from strings in various formats
- Provides both ASCII and Unicode symbol representations
- Implements `UnitInterface` for consistent handling

### Key Features

- Automatic term combination for like units
- Multiple parsing formats (multiplication, division, parentheses)
- Dimension code calculation from component terms
- Immutable transformations (inv, pow, toSiBase)
- Equatable via the `Equatable` trait

## Properties

### unitTerms

```php
private(set) array $unitTerms
```

Array of unit terms the DerivedUnit comprises, keyed by the unit symbol without exponent. Unit terms with the same base are automatically combined.

### dimension

```php
private(set) string $dimension
```

The dimension code of the derived unit. Calculated from the component unit terms. Defaults to '1' (dimensionless) for empty units.

### asciiSymbol

```php
public string $asciiSymbol { get; }
```

The ASCII representation of the unit (e.g., 'kg*m/s2'). Uses asterisk for multiplication and digit exponents.

### unicodeSymbol

```php
public string $unicodeSymbol { get; }
```

The Unicode representation of the unit (e.g., 'kg⋅m⋅s⁻²'). Uses dot operator (⋅) for multiplication and superscript exponents.

### multiplier

```php
public float $multiplier { get; }
```

The combined multiplier from all unit term prefixes, accounting for exponents. For example, km²⋅ms⁻¹ would have multiplier 1000² × 0.001⁻¹ = 1e6 × 1000 = 1e9.

### firstUnitTerm

```php
public ?UnitTerm $firstUnitTerm { get; }
```

The first unit term in the derived unit, or null if empty. Useful for applying prefixes or checking unit properties.

## Constructor

### __construct()

```php
public function __construct(null|Unit|UnitTerm|array $unit = null)
```

Construct a new DerivedUnit instance.

**Parameters:**
- `$unit` (null|Unit|UnitTerm|array) - The unit, unit term, or array of unit terms, or null for empty

**Throws:**
- `DomainException` - If the provided unit is invalid

**Examples:**
```php
// Empty (dimensionless) unit
$dimensionless = new DerivedUnit();

// From a single unit term
$metres = new DerivedUnit(new UnitTerm('m'));

// From an array of unit terms
$velocity = new DerivedUnit([
    new UnitTerm('m'),
    new UnitTerm('s', null, -1)
]);
```

## Factory Methods

### toDerivedUnit()

```php
public static function toDerivedUnit(null|string|UnitInterface $value): self
```

Convert any unit representation to a DerivedUnit.

**Parameters:**
- `$value` (null|string|UnitInterface) - The value to convert

**Returns:**
- `self` - The equivalent DerivedUnit

**Throws:**
- `FormatException` - If a string cannot be parsed
- `DomainException` - If a string contains unknown units

**Examples:**
```php
$unit = DerivedUnit::toDerivedUnit('m/s');
$unit = DerivedUnit::toDerivedUnit($unitTerm);
$unit = DerivedUnit::toDerivedUnit(null); // Empty unit
```

### parse()

```php
public static function parse(string $symbol): self
```

Parse a string into a DerivedUnit.

**Parameters:**
- `$symbol` (string) - The unit symbol (e.g., 'm', 'kg\*m/s2', 'J/(mol\*K)')

**Returns:**
- `self` - The parsed DerivedUnit

**Throws:**
- `FormatException` - If the format is invalid
- `DomainException` - If any units are unknown
- `LogicException` - If there was a parsing error

**Behavior:**
- Supports multiplication: `kg*m`, `kg.m`, `kg*m`
- Supports division: `m/s`, `kg*m/s2`
- Supports parentheses in denominator: `J/(mol*K)`
- Empty string returns dimensionless unit

**Examples:**
```php
$force = DerivedUnit::parse('kg*m/s2');
$heatCapacity = DerivedUnit::parse('J/(mol*K)');
$frequency = DerivedUnit::parse('s-1');
```

## Inspection Methods

### isDimensionless()

```php
public function isDimensionless(): bool
```

Check if this derived unit is dimensionless (has no unit terms).

**Returns:**
- `bool` - True if dimensionless

### isSi()

```php
public function isSi(): bool
```

Check if all unit terms belong to the SI system.

**Returns:**
- `bool` - True if all units are SI units

### isBase()

```php
public function isBase(): bool
```

Check if all unit terms are base units (single-dimension units, not expandable).

**Returns:**
- `bool` - True if all units are base units. False if dimensionless.

### isSiBase()

```php
public function isSiBase(): bool
```

Check if all unit terms are SI base units (e.g., m, kg, s, A, K, cd, mol, rad, B, XAU).

**Returns:**
- `bool` - True if all units are SI base units. False if dimensionless.

### isExpandable()

```php
public function isExpandable(): bool
```

Check if any unit term is expandable (is a named derived unit like N, J, Pa).

**Returns:**
- `bool` - True if at least one term is expandable

### isMergeable()

```php
public function isMergeable(): bool
```

Check if any two unit terms share the same dimension and could be merged.

**Returns:**
- `bool` - True if at least two terms share a dimension

**Examples:**
```php
$mixed = DerivedUnit::parse('m*ft');
$mixed->isMergeable(); // true (both are length)

$velocity = DerivedUnit::parse('m/s');
$velocity->isMergeable(); // false (different dimensions)
```

### hasPrefixes()

```php
public function hasPrefixes(): bool
```

Check if any unit term has a prefix.

**Returns:**
- `bool` - True if at least one term has a prefix

## Transformation Methods

### inv()

```php
public function inv(): self
```

Return a new DerivedUnit with all exponents negated.

**Returns:**
- `self` - A new instance with inverted exponents

**Examples:**
```php
$velocity = DerivedUnit::parse('m/s');
$invVelocity = $velocity->inv(); // s/m
```

### pow()

```php
public function pow(int $exponent): self
```

Return a new DerivedUnit raised to a power.

**Parameters:**
- `$exponent` (int) - The power to raise to

**Returns:**
- `self` - A new instance with multiplied exponents

**Examples:**
```php
$length = DerivedUnit::parse('m');
$area = $length->pow(2);  // m2
$volume = $length->pow(3); // m3
```

### toSiBase()

```php
public function toSiBase(): self
```

Convert the DerivedUnit to its SI base unit equivalent. This includes the special units designated as SI base for this system: rad, B, and XAU.

**Returns:**
- `self` - A new DerivedUnit with SI base units

**Throws:**
- `DomainException` - If any dimension codes are invalid
- `LogicException` - If any dimension codes lack SI base units

**Examples:**
```php
$force = DerivedUnit::parse('N');
$base = $force->toSiBase(); // kg⋅m⋅s⁻²
```

### removePrefixes()

```php
public function removePrefixes(): self
```

Return a new DerivedUnit with all prefixes removed.

**Returns:**
- `self` - A new instance without prefixes

**Examples:**
```php
$prefixed = DerivedUnit::parse('km/ms');
$unprefixed = $prefixed->removePrefixes(); // m/s
```

## Manipulation Methods

### addUnitTerm()

```php
public function addUnitTerm(UnitTerm $newUnitTerm): void
```

Add a unit term, combining exponents with any existing term of the same unit.

**Parameters:**
- `$newUnitTerm` (UnitTerm) - The unit term to add

**Behavior:**
- If a term with the same base exists, exponents are added
- If the resulting exponent is zero, the term is removed
- Terms are automatically sorted into canonical order

### removeUnitTerm()

```php
public function removeUnitTerm(UnitTerm $unitTermToRemove): void
```

Remove a unit term.

**Parameters:**
- `$unitTermToRemove` (UnitTerm) - The unit term to remove

### sortUnitTerms()

```php
public function sortUnitTerms(): void
```

Sort the unit terms into canonical order. Called automatically by `addUnitTerm()`.

## Comparison Methods

### equal()

```php
public function equal(mixed $other): bool
```

Check if this derived unit equals another.

**Parameters:**
- `$other` (mixed) - The value to compare

**Returns:**
- `bool` - True if both have the same unit terms with same exponents

## String Methods

### format()

```php
public function format(bool $ascii = false): string
```

Format the derived unit as a string.

**Parameters:**
- `$ascii` (bool) - If true, return ASCII format; if false (default), return Unicode format

**Returns:**
- `string` - The formatted unit symbol

**Behavior:**
- ASCII uses '*' for multiplication and digit exponents
- Unicode uses dot operator (⋅) for multiplication and superscript exponents
- Negative exponents in denominator are shown as positive after '/'
- Multiple denominator terms use parentheses: `J/(mol*K)`

### __toString()

```php
public function __toString(): string
```

Convert to string using Unicode format.

**Returns:**
- `string` - The Unicode representation

## Usage Examples

### Building Compound Units

```php
use Galaxon\Quantities\DerivedUnit;
use Galaxon\Quantities\UnitTerm;

// Build Newton: kg*m/s2
$newton = new DerivedUnit([
    new UnitTerm('kg'),
    new UnitTerm('m'),
    new UnitTerm('s', null, -2)
]);

echo $newton->asciiSymbol;   // 'kg*m/s2'
echo $newton->unicodeSymbol; // 'kg⋅m⋅s⁻²'
echo $newton->dimension;     // 'T-2LM'
```

### Parsing and Validation

```php
use Galaxon\Quantities\DerivedUnit;

// Parse various formats
$unit1 = DerivedUnit::parse('kg*m/s2');
$unit2 = DerivedUnit::parse('J/(mol*K)');
$unit3 = DerivedUnit::parse('m2');

// Check properties
if ($unit1->isSi()) {
    echo "Unit is SI compatible";
}

if ($unit2->isExpandable()) {
    echo "Unit can be expanded to base units";
}
```

### Arithmetic with Units

```php
use Galaxon\Quantities\DerivedUnit;

$length = DerivedUnit::parse('m');
$time = DerivedUnit::parse('s');

// Calculate velocity unit: m * s-1
$velocity = new DerivedUnit();
foreach ($length->unitTerms as $term) {
    $velocity->addUnitTerm($term);
}
$velocity->addUnitTerm($time->firstUnitTerm->inv());

echo $velocity->asciiSymbol; // 'm/s'
```

## See Also

- **[Unit](Unit.md)** - Simple unit representation
- **[UnitTerm](UnitTerm.md)** - Unit with prefix and exponent
- **[UnitInterface](UnitInterface.md)** - Interface implemented by all unit types
- **[Quantity](Quantity.md)** - Uses DerivedUnit for unit representation
