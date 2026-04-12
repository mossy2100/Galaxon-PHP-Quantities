# MagneticFlux

Represents magnetic flux quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `MagneticFlux` class handles magnetic flux measurements.

---

## Unit definitions

| Name  | ASCII symbol | Prefixes   | Systems |
| ----- | ------------ | ---------- | ------- |
| weber | `Wb`         | all metric | SI      |

---

## Conversion definitions

| From | To                   | Factor |
| ---- | -------------------- | ------ |
| *Wb* | *kg\*m2\*s-2\*A-1*   | 1      |

---

## SI Unit Expansion

The *weber* is defined as:

```
Wb = kg·m²·s⁻²·A⁻¹ = V·s = T·m²
```

---

## Usage Examples

```php
use Galaxon\Quantities\QuantityType\MagneticFlux;

// Magnetic components
$sensor = new MagneticFlux(100, 'uWb');
$transformer = new MagneticFlux(10, 'mWb');

// Convert units
$inMWb = $sensor->to('mWb');  // 0.1 mWb
$inWb = $transformer->to('Wb');  // 0.01 Wb
```

---

## See Also

- **[Units: Magnetic Flux](../../Concepts/Units.md#magnetic-flux)** - Complete list
- **[Quantity](../Quantity.md)** - Base class documentation
- **[MagneticFluxDensity](MagneticFluxDensity.md)** - Related quantity (B = Φ/A)
- **[Inductance](Inductance.md)** - Related quantity (Φ = LI)
