# Capacitance

Represents electrical capacitance quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Capacitance` class handles electrical capacitance measurements.

For the complete list of capacitance units, see [Supported Units: Capacitance](../SupportedUnits.md#capacitance).

---

## SI Unit Expansion

The farad is defined as:

```
F = kg⁻¹·m⁻²·s⁴·A² = C/V = s/Ω
```

---

## Common Prefixed Units

| Unit | Value | Typical Use |
|------|-------|-------------|
| pF | 10⁻¹² F | RF circuits, small caps |
| nF | 10⁻⁹ F | Decoupling, timing |
| μF | 10⁻⁶ F | Electrolytic, power |
| mF | 10⁻³ F | Supercapacitors |
| F | 1 F | Large supercapacitors |

Note: One farad is a very large capacitance. Most capacitors are measured in pF, nF, or μF.

---

## Usage Examples

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

## See Also

- **[Supported Units: Capacitance](../SupportedUnits.md#capacitance)** - Complete list
- **[Quantity](../Quantity.md)** - Base class documentation
- **[ElectricCharge](ElectricCharge.md)** - Related quantity (C = Q/V)
- **[Voltage](Voltage.md)** - Related quantity
