# Illuminance

Represents illuminance quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Illuminance` class handles illuminance measurements, which describe how much light falls on a surface.

For the complete list of illuminance units, see [Supported Units: Illuminance](Units.md#illuminance).

---

## SI Unit Expansion

The lux is defined as:

```
lx = cd·sr·m⁻² = lm/m²
```

One lux equals one lumen per square meter.

---

## Usage Examples

```php
use Galaxon\Quantities\QuantityType\Illuminance;

// Office lighting
$office = new Illuminance(500, 'lx');

// Photography
$studio = new Illuminance(1000, 'lx');

// Convert units
$inKlx = $studio->to('klx');  // 1 klx

// Low light
$night = new Illuminance(1, 'lx');
$inMlx = $night->to('mlx');  // 1000 mlx
```

---

## See Also

- **[Supported Units: Illuminance](Units.md#illuminance)** - Complete list
- **[Quantity](../Quantity.md)** - Base class documentation
- **[LuminousFlux](LuminousFlux.md)** - Related quantity (lx = lm/m²)
- **[LuminousIntensity](LuminousIntensity.md)** - Related quantity
