# SolidAngle

Represents solid angle quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `SolidAngle` class handles solid angle measurements. A solid angle is the two-dimensional angle in three-dimensional space that an object subtends at a point.

For the complete list of solid angle units, see [Supported Units: Solid Angle](../SupportedUnits.md#solid-angle).

---

## SI Unit

The steradian (sr) is the SI unit of solid angle. A complete sphere subtends 4π steradians.

---

## Common Prefixed Units

| Unit | Value | Typical Use |
|------|-------|-------------|
| μsr | 10⁻⁶ sr | Astronomy, small apertures |
| msr | 10⁻³ sr | Optics |
| sr | 1 sr | Standard |

---

## Reference Values

| Shape | Solid Angle |
|-------|-------------|
| Full sphere | 4π sr ≈ 12.566 sr |
| Hemisphere | 2π sr ≈ 6.283 sr |
| 1° × 1° square | ~0.0003 sr |
| Sun/Moon (from Earth) | ~0.00006 sr |

---

## Usage Examples

```php
use Galaxon\Quantities\QuantityType\SolidAngle;

// Light cone
$cone = new SolidAngle(0.5, 'sr');

// Astronomical object
$galaxy = new SolidAngle(100, 'usr');
$inSr = $galaxy->to('sr');  // 0.0001 sr

// Full sphere
$sphere = new SolidAngle(4 * M_PI, 'sr');
```

---

## See Also

- **[Supported Units: Solid Angle](../SupportedUnits.md#solid-angle)** - Complete list
- **[Quantity](../Quantity.md)** - Base class documentation
- **[Angle](Angle.md)** - Related planar angle quantity
- **[LuminousFlux](LuminousFlux.md)** - Related quantity (lm = cd·sr)
