# Force

Represents force quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Force` class handles force measurements in SI and Imperial/US Customary systems.

For the complete list of force units, see [Supported Units: Force](../SupportedUnits.md#force).

---

## SI Unit Expansion

The newton is defined in terms of SI base units:

```
N = kg·m·s⁻²
```

---

## Pound Force

The pound force (lbf) is defined using standard gravity (g₀ = 9.80665 m/s²):

```
1 lbf = 1 lb × g₀ = 0.45359237 kg × 9.80665 m/s² ≈ 4.44822 N
```

The expansion uses Imperial base units:

```
lbf = lb·ft·s⁻² × (g₀ / 0.3048)
```

---

## Key Conversions

| From | To | Factor |
|------|-----|--------|
| lbf | N | 4.44822162 |
| kN | N | 1000 |

---

## Usage Examples

```php
use Galaxon\Quantities\QuantityType\Force;

// SI units
$push = new Force(100, 'N');
$inKN = $push->to('kN');  // 0.1 kN

// Imperial units
$tension = new Force(500, 'lbf');
$inN = $tension->to('N');  // 2224.11 N

// Engineering
$load = new Force(50, 'kN');
$inLbf = $load->to('lbf');  // 11,240.45 lbf

// Weight vs Force
// Weight of 1 kg mass under standard gravity:
$weight = new Force(9.80665, 'N');
$inLbf = $weight->to('lbf');  // 2.205 lbf

// Thrust
$rocket = new Force(7.6, 'MN');
$inKN = $rocket->to('kN');  // 7600 kN
```

---

## Force-Related Quantities

Force is related to other quantities:

- **Force = Mass × Acceleration**: F = ma
- **Force × Distance = Energy**: Work = F × d
- **Force / Area = Pressure**: P = F / A

---

## See Also

- **[Supported Units: Force](../SupportedUnits.md#force)** - Complete list of force units
- **[Quantity](../Quantity.md)** - Base class documentation
- **[Pressure](Pressure.md)** - Related quantity (force per area)
- **[Energy](Energy.md)** - Related quantity (force × distance)
- **[Acceleration](Acceleration.md)** - Related quantity
