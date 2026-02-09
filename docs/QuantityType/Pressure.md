# Pressure

Represents pressure quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Pressure` class handles pressure measurements across SI and common scientific/engineering units.

For the complete list of pressure units, see [Supported Units: Pressure](../SupportedUnits.md#pressure).

---

## SI Unit Expansion

The pascal is defined in terms of SI base units:

```
Pa = kg·m⁻¹·s⁻² = N/m²
```

---

## Key Conversions

| From | To | Factor |
|------|-----|--------|
| atm | Pa | 101,325 (exact) |
| mmHg | Pa | 133.322387415 |
| inHg | mmHg | 25.4 |
| bar | Pa | 100,000 |
| kPa | Pa | 1,000 |

---

## Common Pressure Units

| Unit | Value | Typical Use |
|------|-------|-------------|
| Pa | 1 Pa | SI base |
| kPa | 1,000 Pa | Tire pressure, weather |
| MPa | 10⁶ Pa | Material strength |
| GPa | 10⁹ Pa | Diamond anvil cells |
| bar | 100,000 Pa | Industrial |
| atm | 101,325 Pa | Reference |
| mmHg | 133.322 Pa | Blood pressure |
| inHg | 3,386.39 Pa | US weather |

---

## Usage Examples

```php
use Galaxon\Quantities\QuantityType\Pressure;

// Atmospheric pressure
$seaLevel = new Pressure(1, 'atm');
$inKPa = $seaLevel->to('kPa');    // 101.325 kPa
$inMmHg = $seaLevel->to('mmHg');  // 760 mmHg

// Blood pressure
$systolic = new Pressure(120, 'mmHg');
$inKPa = $systolic->to('kPa');    // 16.0 kPa

// Tire pressure
$tire = new Pressure(32, 'psi');  // Using compound units
$inKPa = $tire->to('kPa');        // 220.6 kPa

// Weather (barometric)
$high = new Pressure(30.2, 'inHg');
$inMbar = $high->to('mbar');      // 1022.68 mbar

// Industrial
$hydraulic = new Pressure(20, 'MPa');
$inBar = $hydraulic->to('bar');   // 200 bar
```

---

## Pressure and Altitude

Atmospheric pressure decreases with altitude. At sea level:
- 1 atm = 101.325 kPa = 760 mmHg = 29.92 inHg

---

## See Also

- **[Supported Units: Pressure](../SupportedUnits.md#pressure)** - Complete list of pressure units
- **[Quantity](../Quantity.md)** - Base class documentation
- **[Force](Force.md)** - Related quantity (pressure = force/area)
