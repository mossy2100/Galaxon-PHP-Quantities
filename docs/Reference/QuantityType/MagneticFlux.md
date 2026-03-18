# MagneticFlux

Represents magnetic flux quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `MagneticFlux` class handles magnetic flux measurements.

For the complete list of magnetic flux units, see [Supported Units: Magnetic Flux](Units.md#magnetic-flux).

---

## SI Unit Expansion

The weber is defined as:

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

- **[Supported Units: Magnetic Flux](Units.md#magnetic-flux)** - Complete list
- **[Quantity](../Quantity.md)** - Base class documentation
- **[MagneticFluxDensity](MagneticFluxDensity.md)** - Related quantity (B = Φ/A)
- **[Inductance](Inductance.md)** - Related quantity (Φ = LI)
