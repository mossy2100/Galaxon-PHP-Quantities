# LuminousIntensity

Represents luminous intensity quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `LuminousIntensity` class handles luminous intensity measurements. The candela is one of the seven SI base units.

For the complete list of luminous intensity units, see [Supported Units: Luminous Intensity](../SupportedUnits.md#luminous-intensity).

---

## SI Base Unit

The candela (cd) is defined by fixing the luminous efficacy of monochromatic radiation at frequency 540 × 10¹² Hz:

```
Kcd = 683 lm/W
```

---

## Common Prefixed Units

| Unit | Value | Typical Use |
|------|-------|-------------|
| mcd | 10⁻³ cd | LEDs, indicator lights |
| cd | 1 cd | Reference, candles |
| kcd | 10³ cd | Spotlights, projectors |

---

## Usage Examples

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

## See Also

- **[Supported Units: Luminous Intensity](../SupportedUnits.md#luminous-intensity)** - Complete list
- **[Quantity](../Quantity.md)** - Base class documentation
- **[LuminousFlux](LuminousFlux.md)** - Related quantity (cd·sr)
- **[Illuminance](Illuminance.md)** - Related quantity
