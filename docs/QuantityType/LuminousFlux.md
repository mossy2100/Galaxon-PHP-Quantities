# LuminousFlux

Represents luminous flux quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `LuminousFlux` class handles luminous flux measurements, commonly used to rate light bulb brightness.

For the complete list of luminous flux units, see [Supported Units: Luminous Flux](../SupportedUnits.md#luminous-flux).

---

## SI Unit Expansion

The lumen is defined as:

```
lm = cd·sr
```

One lumen is the luminous flux emitted by a source of one candela intensity over a solid angle of one steradian.

---

## Common Values

| Light Source | Luminous Flux |
|--------------|---------------|
| Candle | ~12 lm |
| 40W incandescent | ~450 lm |
| 60W incandescent | ~800 lm |
| 100W incandescent | ~1600 lm |
| LED equivalent | Same lumens, less watts |

---

## Usage Examples

```php
use Galaxon\Quantities\QuantityType\LuminousFlux;

// Light bulbs
$bulb = new LuminousFlux(800, 'lm');  // "60W equivalent"

// LED flashlight
$flashlight = new LuminousFlux(1000, 'lm');

// Convert units
$inKlm = $flashlight->to('klm');  // 1 klm
```

---

## See Also

- **[Supported Units: Luminous Flux](../SupportedUnits.md#luminous-flux)** - Complete list
- **[Quantity](../Quantity.md)** - Base class documentation
- **[LuminousIntensity](LuminousIntensity.md)** - Related quantity
- **[Illuminance](Illuminance.md)** - Related quantity (lm/m²)
- **[SolidAngle](SolidAngle.md)** - Related quantity
