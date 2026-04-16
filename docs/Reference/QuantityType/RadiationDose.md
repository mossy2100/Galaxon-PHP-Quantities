# RadiationDose

Represents radiation dose quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `RadiationDose` class handles radiation dose measurements using the *gray* (absorbed dose) and *sievert* (equivalent dose).

---

## Unit definitions

| Name    | ASCII symbol | Prefixes   | Systems |
| ------- | ------------ | ---------- | ------- |
| gray    | `Gy`         | all metric | SI      |
| sievert | `Sv`         | all metric | SI      |

**Note:** *Gray* measures absorbed dose; *sievert* measures equivalent dose. Both have dimension L²·T⁻².

---

## Conversion definitions

| From | To       | Factor |
| ---- | -------- | ------ |
| `Gy` | `m2*s-2`  | 1      |
| `Sv` | `m2*s-2`  | 1      |

---

## Gray vs sievert

Both units have the same dimension (L²·T⁻² = m²/s² = J/kg) but measure different aspects of radiation:

| Unit         | Measures        | Description                    |
|--------------|-----------------|--------------------------------|
| gray (Gy)    | Absorbed dose   | Energy deposited per unit mass |
| sievert (Sv) | Equivalent dose | Biological effect of radiation |

The *sievert* accounts for the relative biological effectiveness (RBE) of different radiation types:

```
Equivalent dose (Sv) = Absorbed dose (Gy) × Radiation weighting factor
```

---

## SI unit

Both units expand to the same base unit expression:

```
Gy = m²·s⁻² = J/kg
Sv = m²·s⁻² = J/kg
```

---

## Usage examples

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

## See also

- **[Quantity](../Quantity.md)** - Base class documentation
- **[Frequency](Frequency.md)** - Contains becquerel (radioactivity)
