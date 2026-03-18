# Pressure

Represents pressure quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Pressure` class handles pressure measurements across SI, scientific, and US customary units.

For the complete list of pressure units, see [Supported Units: Pressure](Units.md#pressure).

---

## SI Unit Expansion

The pascal is defined in terms of SI base units:

```
Pa = kg·m⁻¹·s⁻² = N/m²
```

---

## Units

| Unit | Symbol | System | Prefix Support |
|------|--------|--------|----------------|
| pascal | Pa | SI | Metric (kPa, MPa, GPa, etc.) |
| atmosphere | atm | Scientific | No |
| mmHg | mmHg | Scientific | No |
| inHg | inHg | US Customary | No |

---

## Key Conversions

| From | To | Factor |
|------|-----|--------|
| atm | Pa | 101,325 (exact) |
| mmHg | Pa | 133.322387415 |
| inHg | mmHg | 25.4 |

---

## Usage Examples

```php
use Galaxon\Quantities\QuantityType\Pressure;

// Atmospheric pressure.
$seaLevel = new Pressure(1, 'atm');
$inKPa = $seaLevel->to('kPa');    // 101.325 kPa
$inMmHg = $seaLevel->to('mmHg');  // 760 mmHg

// Blood pressure.
$systolic = new Pressure(120, 'mmHg');
$inKPa = $systolic->to('kPa');    // 16.0 kPa

// Weather (barometric).
$high = new Pressure(30.2, 'inHg');
$inKPa = $high->to('kPa');        // 102.269 kPa

// Industrial.
$hydraulic = new Pressure(20, 'MPa');
$inAtm = $hydraulic->to('atm');   // 197.385 atm
```

---

## See Also

- **[Supported Units: Pressure](Units.md#pressure)** — Complete list of pressure units.
- **[Quantity](../Quantity.md)** — Base class documentation.
- **[Force](Force.md)** — Related quantity (pressure = force/area).
