# ElectricCurrent

Represents electric current quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `ElectricCurrent` class handles electric current measurements. The ampere is one of the seven SI base units.

For the complete list of electric current units, see [Supported Units: Electric Current](Units.md#electric-current).

---

## SI Base Unit

The ampere (A) is an SI base unit, defined by fixing the value of the elementary charge *e*.

---

## Usage Examples

```php
use Galaxon\Quantities\QuantityType\ElectricCurrent;

// Household appliances
$kettle = new ElectricCurrent(10, 'A');
$inMA = $kettle->to('mA');  // 10,000 mA

// Electronics
$led = new ElectricCurrent(20, 'mA');
$inA = $led->to('A');  // 0.02 A

// Microelectronics
$sensor = new ElectricCurrent(100, 'uA');
$inNA = $sensor->to('nA');  // 100,000 nA
```

---

## See Also

- **[Supported Units: Electric Current](Units.md#electric-current)** - Complete list
- **[Quantity](../Quantity.md)** - Base class documentation
- **[Voltage](Voltage.md)** - Related quantity
- **[Resistance](Resistance.md)** - Related quantity (Ohm's law: V = IR)
- **[ElectricCharge](ElectricCharge.md)** - Related quantity (Q = It)
