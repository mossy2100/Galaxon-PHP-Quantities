# QuantityTypeService

Registry of quantity types keyed by dimension code.

**Namespace:** `Galaxon\Quantities\Services`

---

## Overview

The `QuantityTypeService` provides mapping between:

- Dimension codes (e.g. `'L'`, `'M'`, `'T-2L2M'`)
- Quantity names (e.g. `'length'`, `'mass'`, `'energy'`)
- PHP classes (e.g. `Length::class`, `Mass::class`, `Energy::class`)

This allows `Quantity::create()` to instantiate the appropriate subclass based on dimensional analysis. For example, when multiplying `Length * Length`, `create()` returns an `Area` object because dimension `L2` maps to `Area::class`.

All methods are static. The class uses lazy initialization to build the registry on first access.

---

## Constants

### `DEFAULT_QUANTITY_TYPES`

Built-in quantity type definitions keyed by name. Each entry contains a dimension code and PHP class. The registry includes all standard physical quantities:

| Name | Dimension | Class |
|---|---|---|
| dimensionless | *(empty)* | `Dimensionless` |
| length | L | `Length` |
| mass | M | `Mass` |
| time | T | `Time` |
| electric current | I | `ElectricCurrent` |
| temperature | H | `Temperature` |
| amount of substance | N | `AmountOfSubstance` |
| luminous intensity | J | `LuminousIntensity` |
| angle | A | `Angle` |
| data | D | `Data` |
| money | C | `Money` |
| solid angle | A2 | `SolidAngle` |
| area | L2 | `Area` |
| volume | L3 | `Volume` |
| velocity | T-1L | `Velocity` |
| acceleration | T-2L | `Acceleration` |
| density | L-3M | `Density` |
| frequency | T-1 | `Frequency` |
| force | T-2LM | `Force` |
| pressure | T-2L-1M | `Pressure` |
| energy | T-2L2M | `Energy` |
| power | T-3L2M | `Power` |
| electric charge | TI | `ElectricCharge` |
| voltage | T-3L2MI-1 | `Voltage` |
| capacitance | T4L-2M-1I2 | `Capacitance` |
| resistance | T-3L2MI-2 | `Resistance` |
| conductance | T3L-2M-1I2 | `Conductance` |
| magnetic flux | T-2L2MI-1 | `MagneticFlux` |
| magnetic flux density | T-2MI-1 | `MagneticFluxDensity` |
| inductance | T-2L2MI-2 | `Inductance` |
| luminous flux | JA2 | `LuminousFlux` |
| illuminance | L-2JA2 | `Illuminance` |
| absorbed dose | T-2L2 | `RadiationDose` |
| catalytic activity | T-1N | `CatalyticActivity` |

---

## Lookup Methods

### getAll()

```php
public static function getAll(): array
```

Get all registered quantity types.

**Returns:** `array<string, QuantityType>` — Keyed by quantity type name.

```php
$allTypes = QuantityTypeService::getAll();
foreach ($allTypes as $name => $qtyType) {
    echo "$name: {$qtyType->dimension}\n";
}
```

### getByDimension()

```php
public static function getByDimension(string $dimension): ?QuantityType
```

Get the quantity type matching a given dimension code. The dimension code is normalized before lookup.

**Returns:** `?QuantityType`

**Throws:** [`FormatException`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Exceptions/FormatException.md) if the dimension code is invalid.

```php
$qtyType = QuantityTypeService::getByDimension('L');
echo $qtyType->name;      // 'length'
echo $qtyType->dimension;  // 'L'
echo $qtyType->class;      // 'Galaxon\Quantities\QuantityType\Length'

// Normalizes dimension codes.
$qtyType = QuantityTypeService::getByDimension('LT-1');
echo $qtyType->name;  // 'velocity'

// Returns null for unregistered dimensions.
$qtyType = QuantityTypeService::getByDimension('L4');  // null
```

### getByName()

```php
public static function getByName(string $name): ?QuantityType
```

Get the quantity type matching a given name. Case-insensitive.

**Returns:** `?QuantityType`

```php
$qtyType = QuantityTypeService::getByName('energy');
echo $qtyType->dimension;  // 'T-2L2M'

$qtyType = QuantityTypeService::getByName('Electric Current');  // case-insensitive
echo $qtyType->dimension;  // 'I'

QuantityTypeService::getByName('nonexistent');  // null
```

### getByClass()

```php
public static function getByClass(string $class): ?QuantityType
```

Get the quantity type matching a given PHP class.

**Returns:** `?QuantityType`

```php
$qtyType = QuantityTypeService::getByClass(Force::class);
echo $qtyType->name;       // 'force'
echo $qtyType->dimension;  // 'T-2LM'

QuantityTypeService::getByClass(Quantity::class);  // null (base class not registered)
```

### getClasses()

```php
public static function getClasses(): array
```

Get all registered quantity type classes.

**Returns:** `list<class-string<Quantity>>`

```php
$classes = QuantityTypeService::getClasses();
// [Dimensionless::class, Length::class, Mass::class, ...]
```

---

## Registry Methods

### add()

```php
public static function add(string $name, string $dimension, string $class): void
```

Register a new quantity type. The name, dimension, and class must all be unique.

**Throws:**
- [`FormatException`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Exceptions/FormatException.md) if the dimension code is invalid.
- `LogicException` if the name, dimension, or class is already registered.

```php
QuantityTypeService::add('jerk', 'T-3L', Jerk::class);

// Now Quantity::create() will return Jerk for dimension T-3L.
$jerk = Quantity::create(1, 'm/s3');
// $jerk instanceof Jerk === true
```

See **[Customization](../../WorkingWithQuantities/Customization.md)** for full details on adding custom quantity types.

### setClass()

```php
public static function setClass(string $name, string $class): void
```

Set or update the class for an existing quantity type.

**Throws:** `DomainException` if the quantity type name is not registered.

```php
QuantityTypeService::setClass('money', CustomMoney::class);
```

### remove()

```php
public static function remove(string $name): void
```

Remove a quantity type from the registry. Does nothing if the name doesn't exist or the registry is not initialized.

```php
QuantityTypeService::remove('jerk');
```

### removeAll()

```php
public static function removeAll(): void
```

Remove all quantity types from the registry. Unlike `reset()`, the next access will NOT trigger re-initialization.

### reset()

```php
public static function reset(): void
```

Reset the registry to its initial state, forcing re-initialization from the built-in definitions on next access. Useful for test isolation.

---

## See Also

- **[QuantityType](../Internal/QuantityType.md)** - QuantityType class documentation
- **[Quantity](../Quantity.md)** - Base quantity class
- **[DimensionService](DimensionService.md)** - Dimension code utilities
- **[UnitService](UnitService.md)** - Unit registry
- **[Customization](../../WorkingWithQuantities/Customization.md)** - Adding custom quantity types and units
