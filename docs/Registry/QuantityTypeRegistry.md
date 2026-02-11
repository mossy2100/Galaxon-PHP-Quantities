# QuantityTypeRegistry

Registry of quantity types keyed by dimension code.

**Namespace:** `Galaxon\Quantities\Registry`

---

## Overview

The `QuantityTypeRegistry` provides mapping between:

- Dimension codes (e.g., 'L', 'M', 'T-2L2M')
- Quantity names (e.g., 'length', 'mass', 'energy')
- PHP classes (e.g., `Length::class`, `Mass::class`, `Energy::class`)

This allows `Quantity::create()` to instantiate the appropriate subclass based on dimensional analysis.

---

## Built-in Quantity Types

The registry includes all standard physical quantities:

| Name | Dimension | Class |
|------|-----------|-------|
| dimensionless | 1 | `Dimensionless` |
| length | L | `Length` |
| mass | M | `Mass` |
| time | T | `Time` |
| electric current | I | `ElectricCurrent` |
| temperature | H | `Temperature` |
| amount of substance | N | `AmountOfSubstance` |
| luminous intensity | J | `LuminousIntensity` |
| angle | A | `Angle` |
| area | L² | `Area` |
| volume | L³ | `Volume` |
| velocity | T⁻¹L | `Velocity` |
| frequency | T⁻¹ | `Frequency` |
| force | T⁻²LM | `Force` |
| energy | T⁻²L²M | `Energy` |
| power | T⁻³L²M | `Power` |
| *...and more* | | |

---

## Methods

### Lookup Methods

#### `static getByDimension(string $dimension): ?QuantityType`

Get a quantity type by its dimension code.

```php
$qtyType = QuantityTypeRegistry::getByDimension('L');
echo $qtyType->name;      // 'length'
echo $qtyType->dimension; // 'L'
echo $qtyType->class;     // 'Galaxon\Quantities\QuantityType\Length'
```

#### `static getByName(string $name): ?QuantityType`

Get a quantity type by its name (case-insensitive).

```php
$qtyType = QuantityTypeRegistry::getByName('energy');
echo $qtyType->dimension; // 'T-2L2M'
echo $qtyType->class;     // 'Galaxon\Quantities\QuantityType\Energy'
```

#### `static getByClass(string $class): ?QuantityType`

Get a quantity type by its PHP class.

```php
$qtyType = QuantityTypeRegistry::getByClass(Force::class);
echo $qtyType->name;       // 'force'
echo $qtyType->dimension;  // 'T-2LM'
```

#### `static getAll(): array`

Get all registered quantity types.

```php
$allTypes = QuantityTypeRegistry::getAll();
foreach ($allTypes as $name => $qtyType) {
    echo "$name: {$qtyType->dimension}\n";
}
```

#### `static getClasses(): array`

Get all registered quantity type classes.

```php
$classes = QuantityTypeRegistry::getClasses();
// ['Galaxon\Quantities\QuantityType\Length', ...]
```

### Modification Methods

#### `static add(string $name, string $dimension, ?string $class): void`

Register a new quantity type.

```php
QuantityTypeRegistry::add(
    'jerk',
    'T-3L',
    Jerk::class
);
```

#### `static setClass(string $name, string $class): void`

Set or update the class for an existing quantity type.

```php
// Use a custom Money class for currency
QuantityTypeRegistry::setClass('currency', Money::class);
```

#### `static reset(): void`

Reset the registry to its initial state.

```php
QuantityTypeRegistry::reset();
```

---

## How Quantity::create() Uses This

When performing arithmetic on quantities, the result's class is determined by dimensional analysis:

```php
$length = new Length(10, 'm');
$width = new Length(5, 'm');

// Length × Length = Area (dimension L × L = L²)
$area = $length->multiply($width);
// $area is an Area object because L² maps to Area::class
```

---

## Adding Custom Quantity Types

```php
use Galaxon\Quantities\Registry\QuantityTypeRegistry;
use Galaxon\Quantities\Quantity;

// Create a custom quantity class
class Jerk extends Quantity
{
    public static function getUnitDefinitions(): array
    {
        return [];  // Uses compound units only
    }
}

// Register it
QuantityTypeRegistry::add('jerk', 'T-3L', Jerk::class);

// Now Quantity::create() will return Jerk for dimension T-3L
$jerk = Quantity::create(1, 'm/s3');
// $jerk instanceof Jerk === true
```

---

## See Also

- **[QuantityType](../QuantityType.md)** - QuantityType class documentation
- **[Quantity](../Quantity.md)** - Base quantity class
- **[Dimensions](../Dimensions.md)** - Dimension code utilities
- **[UnitRegistry](UnitRegistry.md)** - Unit registry
