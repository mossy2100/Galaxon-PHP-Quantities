# Resistance

Represents electrical resistance quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Resistance` class handles electrical resistance measurements.

---

## Unit definitions

| Name | ASCII symbol | Unicode symbol | Prefixes   | Systems |
| ---- | ------------ | -------------- | ---------- | ------- |
| ohm  | `ohm`        | `Ω`            | all metric | SI      |

---

## Conversion definitions

| From  | To                  | Factor |
| ----- | ------------------- | ------ |
| `ohm` | `kg*m2*s-3*A-2`     | 1      |

---

## SI unit

The *ohm* is defined as:

```
Ω = kg·m²·s⁻³·A⁻² = V/A
```

---

## Unit symbol

The ohm uses:
- ASCII: `ohm`
- Unicode: `Ω` (Greek capital omega, U+03A9)

---

## Usage examples

```php
use Galaxon\Quantities\QuantityType\Resistance;

// Standard resistors
$r1 = new Resistance(470, 'ohm');
$r2 = new Resistance(4.7, 'kohm');
$inOhm = $r2->to('ohm');  // 4700 Ω

// Unicode symbol
$r3 = new Resistance(10, 'kΩ');

// High impedance
$input = new Resistance(10, 'Mohm');
$inKohm = $input->to('kohm');  // 10,000 kΩ
```

---

## See also

- **[Quantity](../Quantity.md)** - Base class documentation
- **[Voltage](Voltage.md)** - Related quantity (V = IR)
- **[ElectricCurrent](ElectricCurrent.md)** - Related quantity
- **[Conductance](Conductance.md)** - Inverse quantity (S = 1/Ω)
