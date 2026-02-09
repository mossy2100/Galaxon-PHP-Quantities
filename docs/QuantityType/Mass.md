# Mass

Represents mass quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Mass` class handles mass measurements across SI, Imperial, and US Customary systems.

For the complete list of mass units, see [Supported Units: Mass](../SupportedUnits.md#mass).

---

## SI Base Unit Note

The SI base unit for mass is the **kilogram** (kg), not the gram. This is the only SI base unit with a prefix. When working with SI prefixes:

```php
// These are equivalent
$mass1 = new Mass(1, 'kg');
$mass2 = new Mass(1000, 'g');

// Milligram, microgram, etc.
$mg = new Mass(500, 'mg');
$ug = new Mass(100, 'ug');  // or 'μg'
```

---

## Key Conversions

| From | To | Factor |
|------|-----|--------|
| pound | kilogram | 0.45359237 (exact) |
| grain | milligram | 64.79891 |
| tonne | kilogram | 1000 |
| dalton | kilogram | 1.66053906892×10⁻²⁷ |
| pound | ounce | 16 |
| stone | pound | 14 |
| short ton | pound | 2000 |
| long ton | pound | 2240 |

The International Yard and Pound Agreement (1959) defines the exact metric equivalent for the pound.

---

## Usage Examples

```php
use Galaxon\Quantities\QuantityType\Mass;

// Create masses in different units
$kg = new Mass(75, 'kg');
$lb = new Mass(165, 'lb');
$g = new Mass(500, 'g');

// Convert between systems
$inPounds = $kg->to('lb');   // 165.347 lb
$inKg = $lb->to('kg');       // 74.842 kg

// Imperial/US units
$stones = new Mass(12, 'st');
$inLb = $stones->to('lb');   // 168 lb

// Scientific units
$atom = new Mass(12, 'Da');  // Carbon-12 atom
$metric = new Mass(1000, 't');  // 1000 tonnes

// Small masses
$mg = new Mass(500, 'mg');
$grains = new Mass(77, 'gr');  // ~5 grams
```

---

## See Also

- **[Supported Units: Mass](../SupportedUnits.md#mass)** - Complete list of mass units
- **[Quantity](../Quantity.md)** - Base class documentation
- **[Force](Force.md)** - Related quantity (M·L·T⁻²)
- **[Density](Density.md)** - Related quantity (M·L⁻³)
