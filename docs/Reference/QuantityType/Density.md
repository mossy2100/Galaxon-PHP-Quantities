# Density

Represents density quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Density` class handles density measurements. This class has no dedicated unit definitions as density is expressed using compound units (kg/m³, g/cm³, lb/ft³, etc.).

---

## Compound Units

Density units are automatically supported through unit arithmetic:

```php
use Galaxon\Quantities\Quantity;

// SI units
$water = new Quantity(1000, 'kg/m3');
$inGCm3 = $water->to('g/cm3');  // 1 g/cm³

// Imperial units
$inLbFt3 = $water->to('lb/ft3');  // 62.428 lb/ft³
```

---

## Common Densities

| Material | Density |
|----------|---------|
| Air (STP) | 1.225 kg/m³ |
| Water | 1000 kg/m³ = 1 g/cm³ |
| Aluminum | 2700 kg/m³ |
| Iron | 7874 kg/m³ |
| Gold | 19300 kg/m³ |

---

## Usage Examples

```php
use Galaxon\Quantities\Quantity;

// Water density
$water = new Quantity(1, 'g/cm3');
$inKgM3 = $water->to('kg/m3');  // 1000 kg/m³

// Metal density
$steel = new Quantity(7850, 'kg/m3');
$inGCm3 = $steel->to('g/cm3');  // 7.85 g/cm³

// Imperial units
$concrete = new Quantity(150, 'lb/ft3');
$inKgM3 = $concrete->to('kg/m3');  // 2403 kg/m³

// Relative density (specific gravity)
$material = new Quantity(2.5, 'g/cm3');
$relativeToWater = $material->value / 1.0;  // 2.5
```

---

## See Also

- **[Quantity](../Quantity.md)** - Base class documentation
- **[Mass](Mass.md)** - Related quantity
- **[Volume](Volume.md)** - Related quantity (ρ = m/V)
