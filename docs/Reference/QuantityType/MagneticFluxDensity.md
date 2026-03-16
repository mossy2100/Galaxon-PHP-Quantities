# MagneticFluxDensity

Represents magnetic flux density (magnetic field strength) quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `MagneticFluxDensity` class handles magnetic flux density measurements, also known as magnetic field strength or B-field.

For the complete list of magnetic flux density units, see [Supported Units: Magnetic Flux Density](SupportedUnits.md#magnetic-flux-density).

---

## SI Unit Expansion

The tesla is defined as:

```
T = kg·s⁻²·A⁻¹ = Wb/m² = V·s/m²
```

---

## Usage Examples

```php
use Galaxon\Quantities\QuantityType\MagneticFluxDensity;

// Earth's magnetic field
$earth = new MagneticFluxDensity(50, 'uT');
$inNT = $earth->to('nT');  // 50,000 nT

// MRI machine
$mri = new MagneticFluxDensity(3, 'T');
$inMT = $mri->to('mT');  // 3000 mT

// Neodymium magnet
$magnet = new MagneticFluxDensity(1.2, 'T');
```

---

## See Also

- **[Supported Units: Magnetic Flux Density](SupportedUnits.md#magnetic-flux-density)** - Complete list
- **[Quantity](../Quantity.md)** - Base class documentation
- **[MagneticFlux](MagneticFlux.md)** - Related quantity (Φ = B·A)
