# Inductance

Represents electrical inductance quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Inductance` class handles electrical inductance measurements.

For the complete list of inductance units, see [Supported Units: Inductance](Units.md#inductance).

---

## SI Unit Expansion

The henry is defined as:

```
H = kg·m²·s⁻²·A⁻² = Wb/A = V·s/A
```

---

## Usage Examples

```php
use Galaxon\Quantities\QuantityType\Inductance;

// Common inductors
$rf = new Inductance(10, 'nH');
$power = new Inductance(100, 'uH');
$audio = new Inductance(1, 'mH');

// Convert units
$inUH = $audio->to('uH');  // 1000 μH
$inNH = $power->to('nH');  // 100,000 nH
```

---

## See Also

- **[Supported Units: Inductance](Units.md#inductance)** - Complete list
- **[Quantity](../Quantity.md)** - Base class documentation
- **[MagneticFlux](MagneticFlux.md)** - Related quantity (Φ = LI)
- **[ElectricCurrent](ElectricCurrent.md)** - Related quantity
