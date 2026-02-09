# RadiationDose

Represents radiation dose quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `RadiationDose` class handles radiation dose measurements using the gray (absorbed dose) and sievert (equivalent dose).

For the complete list of radiation dose units, see [Supported Units: Radiation Dose](../SupportedUnits.md#radiation-dose).

---

## Gray vs Sievert

Both units have the same dimension (L²·T⁻² = m²/s² = J/kg) but measure different aspects of radiation:

| Unit | Measures | Description |
|------|----------|-------------|
| Gray (Gy) | Absorbed dose | Energy deposited per unit mass |
| Sievert (Sv) | Equivalent dose | Biological effect of radiation |

The sievert accounts for the relative biological effectiveness (RBE) of different radiation types:

```
Equivalent dose (Sv) = Absorbed dose (Gy) × Radiation weighting factor
```

---

## SI Unit Expansion

Both units expand to the same base unit expression:

```
Gy = m²·s⁻² = J/kg
Sv = m²·s⁻² = J/kg
```

---

## Common Prefixed Units

| Unit | Value | Typical Use |
|------|-------|-------------|
| μGy | 10⁻⁶ Gy | Diagnostic imaging |
| mGy | 10⁻³ Gy | Medical procedures |
| Gy | 1 Gy | Radiation therapy |
| μSv | 10⁻⁶ Sv | Background radiation |
| mSv | 10⁻³ Sv | Occupational limits |
| Sv | 1 Sv | High dose (dangerous) |

---

## Usage Examples

```php
use Galaxon\Quantities\QuantityType\RadiationDose;

// Medical imaging
$xray = new RadiationDose(0.1, 'mGy');
$inUGy = $xray->to('uGy');  // 100 μGy

// Background radiation
$annual = new RadiationDose(2.4, 'mSv');  // Average annual dose
$inUSv = $annual->to('uSv');  // 2400 μSv

// Occupational limits
$limit = new RadiationDose(20, 'mSv');  // Annual limit for workers
$inSv = $limit->to('Sv');  // 0.02 Sv

// Radiation therapy
$treatment = new RadiationDose(2, 'Gy');  // Per fraction
$inMGy = $treatment->to('mGy');  // 2000 mGy
```

---

## Typical Dose Values

| Exposure | Approximate Dose |
|----------|-----------------|
| Chest X-ray | 0.1 mGy |
| CT scan | 10-20 mGy |
| Annual background | 2-3 mSv |
| Occupational limit | 20 mSv/year |
| Acute radiation sickness | > 1 Sv |

---

## See Also

- **[Supported Units: Radiation Dose](../SupportedUnits.md#radiation-dose)** - Complete list of radiation dose units
- **[Quantity](../Quantity.md)** - Base class documentation
- **[Frequency](Frequency.md)** - Contains becquerel (radioactivity)
