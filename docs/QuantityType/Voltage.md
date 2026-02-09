# Voltage

Represents voltage/electric potential quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Voltage` class handles voltage (electric potential difference) measurements.

For the complete list of voltage units, see [Supported Units: Voltage](../SupportedUnits.md#voltage).

---

## SI Unit Expansion

The volt is defined as:

```
V = kg·m²·s⁻³·A⁻¹ = W/A = J/C
```

---

## Common Prefixed Units

| Unit | Value | Typical Use |
|------|-------|-------------|
| μV | 10⁻⁶ V | Bioelectric signals |
| mV | 10⁻³ V | Electronics, sensors |
| V | 1 V | Batteries, electronics |
| kV | 10³ V | Power distribution |
| MV | 10⁶ V | High voltage research |

---

## Usage Examples

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

## See Also

- **[Supported Units: Voltage](../SupportedUnits.md#voltage)** - Complete list
- **[Quantity](../Quantity.md)** - Base class documentation
- **[ElectricCurrent](ElectricCurrent.md)** - Related quantity (Ohm's law)
- **[Resistance](Resistance.md)** - Related quantity (V = IR)
- **[Power](Power.md)** - Related quantity (P = VI)
