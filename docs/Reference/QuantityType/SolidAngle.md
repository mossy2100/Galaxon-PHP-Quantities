# SolidAngle

Represents solid angle quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `SolidAngle` class handles solid angle measurements. A solid angle is the two-dimensional angle in three-dimensional space that an object subtends at a point.

---

## Unit definitions

| Name      | ASCII symbol | Prefixes     | Systems |
| --------- | ------------ | ------------ | ------- |
| steradian | `sr`         | small metric | SI      |

---

## Conversion definitions

| From | To     | Factor |
| ---- | ------ | ------ |
| `sr` | `rad2` | 1      |

---

## SI Unit

The *steradian* (`sr`) is the SI unit of solid angle. A complete sphere subtends 4π *steradians*.

---

## Usage examples

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

## See also

- **[Quantity](../Quantity.md)** - Base class documentation
- **[Angle](Angle.md)** - Related planar angle quantity
- **[LuminousFlux](LuminousFlux.md)** - Related quantity (lm = cd·sr)
