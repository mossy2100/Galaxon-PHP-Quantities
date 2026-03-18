# ElectricCharge

Represents electric charge quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `ElectricCharge` class handles electric charge measurements.

For the complete list of electric charge units, see [Units: Electric Charge](../../Concepts/Units.md#electric-charge).

---

## SI Unit Expansion

The coulomb is defined as:

```
C = A·s
```

One coulomb is the charge transported by a current of one ampere in one second.

---

## Usage Examples

```php
use Galaxon\Quantities\QuantityType\ElectricCharge;

// Battery capacity
$battery = new ElectricCharge(5000, 'mA*h');
$capacitor = new ElectricCharge(100, 'uC');

// Elementary charge
$electron = new ElectricCharge(1.602176634e-19, 'C');
echo $electron->autoPrefix();  // 160.217663 zC

// Convert units
$charge = new ElectricCharge(1, 'C');
$inMC = $charge->to('mC');  // 1000 mC
```

---

## Physical Constants

The following physical constants have this quantity type. See [`PhysicalConstant`](../PhysicalConstant.md) for the full list.

- **`PhysicalConstant::elementaryCharge()`** (e) — Elementary charge, 1.602176634 × 10⁻¹⁹ C.

---

## See Also

- **[Units: Electric Charge](../../Concepts/Units.md#electric-charge)** - Complete list
- **[Quantity](../Quantity.md)** - Base class documentation
- **[ElectricCurrent](ElectricCurrent.md)** - Related quantity (I = Q/t)
- **[Capacitance](Capacitance.md)** - Related quantity (C = Q/V)
