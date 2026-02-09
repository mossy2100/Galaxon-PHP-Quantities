# Energy

Represents energy quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Energy` class handles energy measurements across SI, scientific, and common units.

For the complete list of energy units, see [Supported Units: Energy](../SupportedUnits.md#energy).

---

## SI Unit Expansion

The joule is defined in terms of SI base units:

```
J = kg·m²·s⁻²
```

This allows automatic conversion with compound units:

```php
$energy = new Energy(1, 'J');
$inBaseUnits = $energy->to('kg*m2*s-2');  // 1
```

---

## Key Conversions

| From | To | Factor |
|------|-----|--------|
| eV | J | 1.602176634×10⁻¹⁹ |
| cal | J | 4.184 (exact, thermochemical) |
| Btu | J | 1055.05585262 |

---

## Common Prefixed Units

| Unit | Value |
|------|-------|
| kJ | 1,000 J |
| MJ | 1,000,000 J |
| kWh | 3,600,000 J |
| kcal | 4,184 J |
| keV | 1.602×10⁻¹⁶ J |
| MeV | 1.602×10⁻¹³ J |

---

## Usage Examples

```php
use Galaxon\Quantities\QuantityType\Energy;

// SI units
$work = new Energy(500, 'J');
$inKJ = $work->to('kJ');  // 0.5 kJ

// Electricity
$usage = new Energy(1, 'kWh');
$inMJ = $usage->to('MJ');  // 3.6 MJ

// Food energy (calories)
$food = new Energy(2000, 'kcal');
$inKJ = $food->to('kJ');   // 8368 kJ
$inMJ = $food->to('MJ');   // 8.368 MJ

// Physics (electronvolts)
$photon = new Energy(2.5, 'eV');
$inJ = $photon->to('J');   // 4.005×10⁻¹⁹ J

// Heating (BTU)
$heater = new Energy(10000, 'Btu');
$inKWh = $heater->to('kWh');  // 2.931 kWh

// Nuclear physics
$binding = new Energy(8.8, 'MeV');  // Per nucleon
```

---

## Energy-Related Quantities

Energy is related to other quantities:

- **Power × Time = Energy**: 1 kW × 1 h = 1 kWh
- **Force × Distance = Energy**: 1 N × 1 m = 1 J
- **Mass × c² = Energy**: E = mc²

---

## See Also

- **[Supported Units: Energy](../SupportedUnits.md#energy)** - Complete list of energy units
- **[Quantity](../Quantity.md)** - Base class documentation
- **[Power](Power.md)** - Related quantity (energy per time)
- **[Force](Force.md)** - Related quantity (energy per distance)
