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

## SI unit and dimension

The *steradian* (`sr`) is the SI unit of solid angle. A complete sphere subtends 4π *steradians*.

In the SI system, the steradian, like the radian, is considered dimensionless and therefore normally has a code of `1`. For the purpose of this package, however, it was preferable to have a dedicated `Angle` class, which necessitated a dedicated dimension code, which was allocated the letter `A`.

By extension, the dimension code for the steradian became `A2`. Some other quantity types were also affected; for example, the dimension code for *luminous flux*, normally `J`, is `JA2` within this package. This is arguably an improvement, since it differentiates *luminous intensity* (`J`) from *luminous flux*, which are related but different quantity types.

Similarly, the dimension code for *illuminance*, normally `L-2J`, is `L-2JA2` within this package. 

See [Dimensions and Base Units](../../Concepts/DimensionsAndBaseUnits.md) for more information.

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
