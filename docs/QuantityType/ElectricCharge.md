# ElectricCharge

Represents electric charge quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `ElectricCharge` class handles electric charge measurements.

For the complete list of electric charge units, see [Supported Units: Electric Charge](../SupportedUnits.md#electric-charge).

---

## SI Unit Expansion

The coulomb is defined as:

```
C = A·s
```

One coulomb is the charge transported by a current of one ampere in one second.

---

## Common Prefixed Units

| Unit | Value | Typical Use |
|------|-------|-------------|
| pC | 10⁻¹² C | Static electricity |
| nC | 10⁻⁹ C | Capacitors |
| μC | 10⁻⁶ C | Small capacitors |
| mC | 10⁻³ C | Batteries |
| C | 1 C | Reference |
| kC | 10³ C | Industrial |

---

## Usage Examples

```php
use Galaxon\Quantities\QuantityType\ElectricCharge;

// Battery capacity
$battery = new ElectricCharge(5000, 'mAh');  // Using compound unit
$capacitor = new ElectricCharge(100, 'uC');

// Elementary charge
$electron = new ElectricCharge(1.602e-19, 'C');

// Convert units
$charge = new ElectricCharge(1, 'C');
$inMC = $charge->to('mC');  // 1000 mC
```

---

## See Also

- **[Supported Units: Electric Charge](../SupportedUnits.md#electric-charge)** - Complete list
- **[Quantity](../Quantity.md)** - Base class documentation
- **[ElectricCurrent](ElectricCurrent.md)** - Related quantity (I = Q/t)
- **[Capacitance](Capacitance.md)** - Related quantity (C = Q/V)
