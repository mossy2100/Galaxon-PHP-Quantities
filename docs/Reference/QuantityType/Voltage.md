# Voltage

Represents voltage/electric potential quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Voltage` class handles voltage (electric potential difference) measurements.

---

## Unit definitions

| Name | ASCII symbol | Prefixes   | Systems |
| ---- | ------------ | ---------- | ------- |
| volt | `V`          | all metric | SI      |

---

## Conversion definitions

| From | To                  | Factor |
| ---- | ------------------- | ------ |
| `V`  | `kg*m2*s-3*A-1`     | 1      |

---

## SI unit

The *volt* is defined as:

```
V = kg·m²·s⁻³·A⁻¹ = W/A = J/C
```

---

## Usage examples

```php
use Galaxon\Quantities\QuantityType\Voltage;

// Batteries
$aa = new Voltage(1.5, 'V');
$car = new Voltage(12, 'V');

// Electronics
$signal = new Voltage(3.3, 'V');
$inMV = $signal->to('mV');  // 3300 mV

// Power distribution
$transmission = new Voltage(400, 'kV');
$inV = $transmission->to('V');  // 400,000 V

// Bioelectricity
$ecg = new Voltage(1, 'mV');
$inUV = $ecg->to('uV');  // 1000 μV
```

---

## See also

- **[Quantity](../Quantity.md)** - Base class documentation
- **[ElectricCurrent](ElectricCurrent.md)** - Related quantity (Ohm's law)
- **[Resistance](Resistance.md)** - Related quantity (V = IR)
- **[Power](Power.md)** - Related quantity (P = VI)
