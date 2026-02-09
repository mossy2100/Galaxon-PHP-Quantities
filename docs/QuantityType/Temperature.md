# Temperature

Represents temperature quantities with special handling for offset-based conversions.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Temperature` class handles the complexity of temperature conversions, which differ from other quantity types because Celsius and Fahrenheit are offset from absolute zero.

For the complete list of temperature units, see [Supported Units: Temperature](../SupportedUnits.md#temperature).

---

## Constants

| Constant | Type | Value | Description |
|----------|------|-------|-------------|
| `CELSIUS_OFFSET` | float | `273.15` | Offset to convert Celsius to Kelvin |
| `FAHRENHEIT_OFFSET` | float | `459.67` | Offset to convert Fahrenheit to Rankine |
| `RANKINE_PER_KELVIN` | float | `1.8` | Factor to convert Kelvin to Rankine |

---

## Temperature Scales

The package supports four temperature scales:

| Scale | Symbol | Zero Point | System |
|-------|--------|------------|--------|
| Kelvin | K | Absolute zero | SI |
| Celsius | °C, degC | 273.15 K below Kelvin | SI |
| Rankine | °R, degR | Absolute zero | Imperial/US |
| Fahrenheit | °F, degF | 459.67 °R below Rankine | Imperial/US |

**Absolute scales** (Kelvin, Rankine) start at absolute zero.
**Offset scales** (Celsius, Fahrenheit) have arbitrary zero points.

---

## Methods

### `static convert(float $value, string|UnitInterface $srcUnit, string|UnitInterface $destUnit): float`

Convert temperature between units, correctly handling offset-based scales.

This method overrides the parent `convert()` because temperature conversions require adding/subtracting offsets, not just multiplying by a factor.

```php
// Freezing point of water
Temperature::convert(0, 'degC', 'K');      // 273.15
Temperature::convert(0, 'degC', 'degF');   // 32
Temperature::convert(32, 'degF', 'degC');  // 0

// Boiling point of water
Temperature::convert(100, 'degC', 'degF'); // 212
Temperature::convert(100, 'degC', 'K');    // 373.15

// Absolute zero
Temperature::convert(0, 'K', 'degC');      // -273.15
Temperature::convert(0, 'K', 'degR');      // 0
Temperature::convert(0, 'degR', 'degF');   // -459.67
```

---

## Conversion Logic

The conversion process:

1. **Convert to absolute scale** - Apply offset if starting from Celsius or Fahrenheit
2. **Convert between systems** - Multiply/divide by 1.8 if crossing SI/Imperial boundary
3. **Convert to destination** - Apply offset if ending in Celsius or Fahrenheit

### Important Notes

- **Offsets only apply to absolute temperatures** - When converting derived units like `J/°C` to `J/K`, only the factor (1.0) applies, not the offset. This is correct because such quantities represent rates of change, not absolute temperatures.
- **Prefixed Kelvin is supported** - Units like `mK` (millikelvin) work correctly.

---

## Usage Examples

```php
use Galaxon\Quantities\QuantityType\Temperature;

// Create temperatures
$bodyTemp = new Temperature(98.6, 'degF');
$roomTemp = new Temperature(20, 'degC');
$absolute = new Temperature(0, 'K');

// Convert between scales
$bodyTempC = $bodyTemp->to('degC');  // 37°C
$roomTempK = $roomTemp->to('K');     // 293.15 K

// Arithmetic works correctly
$diff = $bodyTemp->subtract($roomTemp);
echo $diff->to('degC')->value;  // 17 (temperature difference)

// Compare temperatures
$hot = new Temperature(100, 'degC');
$boiling = new Temperature(212, 'degF');
$hot->approxEqual($boiling);  // true

// Direct conversion without creating objects
$kelvin = Temperature::convert(25, 'degC', 'K');  // 298.15
```

---

## Common Reference Points

| Description | Celsius | Fahrenheit | Kelvin |
|-------------|---------|------------|--------|
| Absolute zero | -273.15 | -459.67 | 0 |
| Water freezes | 0 | 32 | 273.15 |
| Room temperature | 20-22 | 68-72 | 293-295 |
| Human body | 37 | 98.6 | 310.15 |
| Water boils | 100 | 212 | 373.15 |

---

## See Also

- **[Supported Units: Temperature](../SupportedUnits.md#temperature)** - Complete list of temperature units
- **[Quantity](../Quantity.md)** - Base class documentation
