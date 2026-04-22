# LuminousIntensity

Represents luminous intensity quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `LuminousIntensity` class handles luminous intensity measurements. The *candela* is one of the seven SI base units.

---

## Unit definitions

| Name    | ASCII symbol | Prefixes   | Systems |
| ------- | ------------ | ---------- | ------- |
| candela | `cd`         | all metric | SI      |

---

## SI unit

The *candela* (`cd`) is defined by fixing the luminous efficacy of monochromatic radiation at frequency 540 × 10¹² Hz:

```
Kcd = 683 lm/W
```

---

## Usage examples

```php
use Galaxon\Quantities\QuantityType\LuminousIntensity;

// LEDs
$led = new LuminousIntensity(100, 'mcd');
$inCd = $led->to('cd');  // 0.1 cd

// Standard candle (approximately)
$candle = new LuminousIntensity(1, 'cd');

// Spotlight
$spot = new LuminousIntensity(5, 'kcd');
$inCd = $spot->to('cd');  // 5000 cd
```

---

## See also

- **[Quantity](../Quantity.md)** - Base class documentation
- **[LuminousFlux](LuminousFlux.md)** - Related quantity (cd·sr)
- **[Illuminance](Illuminance.md)** - Related quantity
