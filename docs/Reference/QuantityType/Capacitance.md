# Capacitance

Represents electrical capacitance quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Capacitance` class handles electrical capacitance measurements.

---

## Unit definitions

| Name  | ASCII symbol | Prefixes   | Systems |
| ----- | ------------ | ---------- | ------- |
| farad | `F`          | all metric | SI      |

---

## Conversion definitions

| From | To                | Factor |
| ---- | ----------------- | ------ |
| `F`  | `kg-1*m-2*s4*A2`    | 1      |

---

## SI unit

The *farad* is defined as:

```
F = kg⁻¹·m⁻²·s⁴·A² = C/V = s/Ω
```

---

## Usage examples

```php
use Galaxon\Quantities\QuantityType\Capacitance;

// Common capacitors
$decoupling = new Capacitance(100, 'nF');
$electrolytic = new Capacitance(1000, 'uF');

// Convert units
$inUF = $decoupling->to('uF');  // 0.1 μF
$inPF = $decoupling->to('pF');  // 100,000 pF

// Supercapacitors
$super = new Capacitance(1, 'F');
$inMF = $super->to('mF');  // 1000 mF
```

---

## See also

- **[Quantity](../Quantity.md)** - Base class documentation
- **[ElectricCharge](ElectricCharge.md)** - Related quantity (C = Q/V)
- **[Voltage](Voltage.md)** - Related quantity
