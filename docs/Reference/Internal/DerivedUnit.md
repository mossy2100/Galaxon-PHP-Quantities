# DerivedUnit

Represents a compound unit composed of one or more unit terms.

---

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
- Immutable transformations (inv, mul, pow, toSiBase, toEnglishBase)
- Equatable via the `Equatable` trait

---

## Properties

### unitTerms

```php
private(set) array $unitTerms
```

Array of unit terms the DerivedUnit comprises, keyed by the unit symbol without exponent. Unit terms with the same base are automatically combined.

Type: `array<string, UnitTerm>`

### dimension

```php
private(set) string $dimension
```

The dimension code of the derived unit. Calculated from the component unit terms. Defaults to `''` (empty string) for dimensionless units (i.e. scalars).

### expansion

```php
private(set) ?Quantity $expansion
```

The cached expansion quantity from `tryExpand()`, or `null` if not yet computed or no expansion exists.

### asciiSymbol

```php
public string $asciiSymbol { get; }
```

The ASCII representation of the unit (e.g., 'kg\*m/s2'). Uses asterisk for multiplication and digit exponents.

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

The first unit term in the derived unit, or `null` if empty.

### quantityType

```php
public ?QuantityType $quantityType { get; }
```

The quantity type this derived unit is for (e.g., the `QuantityType` for length), or `null` if the dimension has no registered quantity type. Resolved via `QuantityTypeService::getByDimension()`.

---

## Constructor

### \_\_construct()

```php
public function __construct(null|Unit|UnitTerm|array $unit = null)
```

Construct a new DerivedUnit instance.

**Parameters:**
- `$unit` (null|Unit|UnitTerm|list\<Unit|UnitTerm\>) - The unit, unit term, or array of unit terms, or `null` for empty.

**Throws:**
- `DomainException` - If the provided unit is invalid.

**Examples:**
```php
// Empty (dimensionless) unit
$dimensionless = new DerivedUnit();

// From a single unit term
$meters = new DerivedUnit(new UnitTerm('m'));

// From an array of unit terms
$velocity = new DerivedUnit([
    new UnitTerm('m'),
    new UnitTerm('s', null, -1)
]);
```

---

## Factory Methods

### toDerivedUnit()

```php
public static function toDerivedUnit(null|string|UnitInterface $value): self
```

Convert any unit representation to a DerivedUnit. Returns the same instance if already a DerivedUnit.

**Parameters:**
- `$value` (null|string|UnitInterface) - The value to convert.

**Returns:** `DerivedUnit`

**Throws:**
- `FormatException` - If a string cannot be parsed.
- [`UnknownUnitException`](../Exceptions/UnknownUnitException.md) - If a string contains unknown units.
- `DomainException` - If a string contains a zero exponent.

### parse()

```php
public static function parse(string $symbol): self
```

Parse a string into a DerivedUnit.

**Parameters:**
- `$symbol` (string) - The unit symbol (e.g., `'m'`, `'kg*m/s2'`, `'J/(mol*K)'`).

**Returns:** `DerivedUnit`

**Throws:**
- `FormatException` - If the format is invalid.
- [`UnknownUnitException`](../Exceptions/UnknownUnitException.md) - If any units are unknown.
- `DomainException` - If an exponent is zero.

**Behavior:**
- Supports multiplication: `kg⋅m`, `kg.m`, `kg*m`
- Supports division: `m/s`, `kg*m/s2`
- Supports parentheses in denominator: `J/(mol*K)`
- Empty string returns dimensionless unit

**Examples:**
```php
$force = DerivedUnit::parse('kg*m/s2');
$heatCapacity = DerivedUnit::parse('J/(mol*K)');
$frequency = DerivedUnit::parse('s-1');
```

---

## Inspection Methods

### isDimensionless()

```php
public function isDimensionless(): bool
```

Check if this derived unit is dimensionless (has no unit terms).

### isSi()

```php
public function isSi(): bool
```

Check if all unit terms belong to the SI system. Dimensionless units are not considered SI.

### isBase()

```php
public function isBase(): bool
```

Check if all unit terms are base units (single-dimension units, not expandable). Also true if dimensionless (empty unit terms).

### isSiBase()

```php
public function isSiBase(): bool
```

Check if this derived unit is equivalent to its SI base form. Compares the unit with the result of `toSiBase()`. Also true if dimensionless.

### isMergeable()

```php
public function isMergeable(): bool
```

Check if any two unit terms share the same unit dimension and could be merged. For example, a derived unit containing both `'m'` and `'ft'` returns true since both have dimension `'L'`.

### siPreferred()

```php
public function siPreferred(): bool
```

Determine whether SI or English base units are preferred for expansion/simplification.

Returns `true` if the unit contains no unambiguously English units, or if it contains at least one unambiguously SI unit. Units like `s`, `mol`, `A`, `cd`, `B`, and `XAU` are considered ambiguous (used with both systems) and are excluded from the count.

### hasPrefixes()

```php
public function hasPrefixes(): bool
```

Check if any unit term has a prefix.

### includesUnit()

```php
public function includesUnit(Unit $unit): bool
```

Check if the DerivedUnit includes the given unit in any of its terms.

**Parameters:**
- `$unit` (Unit) - The unit to check.

---

## Comparison Methods

### equal()

```php
public function equal(mixed $other): bool
```

Check if this derived unit equals another. Compares by ASCII symbol.

**Parameters:**
- `$other` (mixed) - The value to compare.

**Returns:** `bool` - True if both are `DerivedUnit` instances with the same ASCII symbol.

---

## Manipulation Methods

### addUnitTerm()

```php
public function addUnitTerm(UnitTerm $newUnitTerm): void
```

Add a unit term, combining exponents with any existing term of the same unit.

**Parameters:**
- `$newUnitTerm` (UnitTerm) - The unit term to add.

**Behavior:**
- If a term with the same base exists, exponents are added.
- If the resulting exponent is zero, the term is removed.
- Terms are automatically sorted into canonical order.

### removeUnitTerm()

```php
public function removeUnitTerm(UnitTerm $unitTermToRemove): void
```

Remove a unit term.

**Parameters:**
- `$unitTermToRemove` (UnitTerm) - The unit term to remove.

---

## Unary Arithmetic Methods

### inv()

```php
public function inv(): self
```

Return a new DerivedUnit with all exponents negated.

**Returns:** `DerivedUnit` - A new instance with inverted exponents.

**Examples:**
```php
$velocity = DerivedUnit::parse('m/s');
$invVelocity = $velocity->inv(); // s/m
```

---

## Binary Arithmetic Methods

### mul()

```php
public function mul(self $other): self
```

Multiply this DerivedUnit by another, combining unit terms. Same-unit exponents are added (e.g., m⋅m² = m³), and terms that cancel to zero are removed.

**Parameters:**
- `$other` (DerivedUnit) - The DerivedUnit to multiply by.

**Returns:** `DerivedUnit` - A new instance representing the product.

**Examples:**
```php
$length = DerivedUnit::parse('m');
$time = DerivedUnit::parse('s');
$velocity = $length->mul($time->inv()); // m/s
```

---

## Power Methods

### pow()

```php
public function pow(int $exponent): self
```

Return a new DerivedUnit raised to a power. Each unit term's exponent is multiplied by the given value.

**Parameters:**
- `$exponent` (int) - The power to raise to.

**Returns:** `DerivedUnit` - A new instance with multiplied exponents.

**Examples:**
```php
$length = DerivedUnit::parse('m');
$area = $length->pow(2);  // m2
$volume = $length->pow(3); // m3
```

---

## Transformation Methods

### toSiBase()

```php
public function toSiBase(): self
```

Convert the DerivedUnit to its SI base unit equivalent. This includes the special units designated as SI base for this system: rad, B, and XAU.

**Returns:** `DerivedUnit`

**Throws:**
- `DomainException` - If any dimension codes are invalid.
- `LogicException` - If any dimension codes lack SI base units.

**Examples:**
```php
$force = DerivedUnit::parse('N');
$base = $force->toSiBase(); // kg⋅m⋅s⁻²
```

### toEnglishBase()

```php
public function toEnglishBase(): self
```

Convert the DerivedUnit to its English base unit equivalent. For dimensions without an English base unit (e.g., time), falls back to the SI base unit.

**Returns:** `DerivedUnit`

**Throws:**
- `DomainException` - If any dimension codes are invalid.
- `LogicException` - If any dimension codes lack a base unit.

**Examples:**
```php
$force = DerivedUnit::parse('lbf');
$base = $force->toEnglishBase(); // lb⋅ft⋅s⁻²
```

### removePrefixes()

```php
public function removePrefixes(): self
```

Return a new DerivedUnit with all prefixes removed from all unit terms.

**Returns:** `DerivedUnit`

**Examples:**
```php
$prefixed = DerivedUnit::parse('km/ms');
$unprefixed = $prefixed->removePrefixes(); // m/s
```

### merge()

```php
public function merge(): Quantity
```

Merge unit terms that share the same unit dimension (e.g., `'m'` and `'ft'`). The first unit encountered of a given dimension is kept; subsequent units of the same dimension are converted to it.

**Returns:** `Quantity` - A new Quantity with the merged derived unit and the conversion factor as the value.

**Examples:**
```php
$mixed = DerivedUnit::parse('m*ft');
$merged = $mixed->merge();
echo $merged->derivedUnit; // m²
echo $merged->value;       // 0.3048
```

---

## Conversion Methods

### format()

```php
public function format(bool $ascii = false): string
```

Format the derived unit as a string.

**Parameters:**
- `$ascii` (bool) - If `true`, return ASCII format; if `false` (default), return Unicode format.

**Returns:** `string` - The formatted unit symbol.

**Behavior:**
- ASCII uses `'*'` for multiplication and digit exponents.
- Unicode uses dot operator (`⋅`) for multiplication and superscript exponents.
- Negative exponents in denominator are shown as positive after `'/'`.
- Multiple denominator terms use parentheses: `J/(mol*K)`.

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
use Galaxon\Quantities\Internal\DerivedUnit;

// Parse various formats
$unit1 = DerivedUnit::parse('kg*m/s2');
$unit2 = DerivedUnit::parse('J/(mol*K)');
$unit3 = DerivedUnit::parse('m2');

// Check properties
if ($unit1->isSi()) {
    echo "Unit is SI compatible";
}
```

### Arithmetic with Units

```php
use Galaxon\Quantities\Internal\DerivedUnit;

$length = DerivedUnit::parse('m');
$time = DerivedUnit::parse('s');

// Calculate velocity unit: m/s
$velocity = $length->mul($time->inv());
echo $velocity->asciiSymbol; // 'm/s'

// Square it for area
$area = $length->pow(2);
echo $area->asciiSymbol; // 'm2'
```

---

## See Also

- **[Unit](Unit.md)** - Simple unit representation.
- **[UnitTerm](UnitTerm.md)** - Unit with prefix and exponent.
- **[UnitInterface](UnitInterface.md)** - Interface implemented by all unit types.
- **[Quantity](../Quantity.md)** - Uses DerivedUnit for unit representation.
- **[Converter](Converter.md)** - Uses DerivedUnit for conversion path discovery.
