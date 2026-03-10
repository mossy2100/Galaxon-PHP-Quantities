# QuantityTypeService

Registry of quantity types keyed by dimension code.

**Namespace:** `Galaxon\Quantities\Services`

## Overview

The `QuantityTypeService` provides mapping between:

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

## Lookup Methods

### getByDimension()

```php
public static function getByDimension(string $dimension): ?QuantityType
```

Get a quantity type by its dimension code.

```php
$qtyType = QuantityTypeService::getByDimension('L');
echo $qtyType->name;      // 'length'
echo $qtyType->dimension; // 'L'
echo $qtyType->class;     // 'Galaxon\Quantities\QuantityType\Length'
```

### getByName()

```php
public static function getByName(string $name): ?QuantityType
```

Get a quantity type by its name (case-insensitive).

```php
$qtyType = QuantityTypeService::getByName('energy');
echo $qtyType->dimension; // 'T-2L2M'
echo $qtyType->class;     // 'Galaxon\Quantities\QuantityType\Energy'
```

### getByClass()

```php
public static function getByClass(string $class): ?QuantityType
```

Get a quantity type by its PHP class.

```php
$qtyType = QuantityTypeService::getByClass(Force::class);
echo $qtyType->name;       // 'force'
echo $qtyType->dimension;  // 'T-2LM'
```

### getAll()

```php
public static function getAll(): array
```

Get all registered quantity types.

```php
$allTypes = QuantityTypeService::getAll();
foreach ($allTypes as $name => $qtyType) {
    echo "$name: {$qtyType->dimension}\n";
}
```

### getClasses()

```php
public static function getClasses(): array
```

Get all registered quantity type classes.

```php
$classes = QuantityTypeService::getClasses();
// ['Galaxon\Quantities\QuantityType\Length', ...]
```

---

## Modification Methods

### add()

```php
public static function add(string $name, string $dimension, ?string $class): void
```

Register a new quantity type.

```php
QuantityTypeService::add(
    'jerk',
    'T-3L',
    Jerk::class
);
```

### setClass()

```php
public static function setClass(string $name, string $class): void
```

Set or update the class for an existing quantity type.

```php
// Use a custom Currency class for currency
QuantityTypeService::setClass('currency', Currency::class);
```

### reset()

```php
public static function reset(): void
```

Reset the registry to its initial state.

```php
QuantityTypeService::reset();
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
use Galaxon\Quantities\Services\QuantityTypeService;
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
QuantityTypeService::add('jerk', 'T-3L', Jerk::class);

// Now Quantity::create() will return Jerk for dimension T-3L
$jerk = Quantity::create(1, 'm/s3');
// $jerk instanceof Jerk === true
```

---

## See Also

- **[QuantityType](../Internal/QuantityType.md)** - QuantityType class documentation
- **[Quantity](../Quantity.md)** - Base quantity class
- **[DimensionService](../Services/DimensionService.md)** - Dimension code utilities
- **[UnitService](UnitService.md)** - Unit registry
