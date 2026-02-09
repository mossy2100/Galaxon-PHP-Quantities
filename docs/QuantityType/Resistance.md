# Resistance

Represents electrical resistance quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Resistance` class handles electrical resistance measurements.

For the complete list of resistance units, see [Supported Units: Resistance](../SupportedUnits.md#resistance).

---

## SI Unit Expansion

The ohm is defined as:

```
Ω = kg·m²·s⁻³·A⁻² = V/A
```

---

## Unit Symbol

The ohm uses:
- ASCII: `ohm`
- Unicode: `Ω` (Greek capital omega, U+03A9)

---

## Common Prefixed Units

| Unit | Value | Typical Use |
|------|-------|-------------|
| mΩ | 10⁻³ Ω | Power connections |
| Ω | 1 Ω | Standard resistors |
| kΩ | 10³ Ω | Common electronics |
| MΩ | 10⁶ Ω | Insulation, sensors |
| GΩ | 10⁹ Ω | High impedance |

---

## Usage Examples

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

## See Also

- **[Supported Units: Resistance](../SupportedUnits.md#resistance)** - Complete list
- **[Quantity](../Quantity.md)** - Base class documentation
- **[Voltage](Voltage.md)** - Related quantity (V = IR)
- **[ElectricCurrent](ElectricCurrent.md)** - Related quantity
- **[Conductance](Conductance.md)** - Inverse quantity (S = 1/Ω)
