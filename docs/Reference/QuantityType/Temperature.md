# Temperature

Represents temperature quantities with special handling for offset-based conversions.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Temperature` class handles the complexity of temperature conversions, which differ from other quantity types because Celsius and Fahrenheit are offset from absolute zero.

For the complete list of temperature units, see [Units: Temperature](../../Concepts/Units.md#temperature).

---

## Constants

| Constant             | Type  | Value    | Description                             |
|----------------------|-------|----------|-----------------------------------------|
| `CELSIUS_OFFSET`     | float | `273.15` | Offset to convert Celsius to Kelvin     |
| `FAHRENHEIT_OFFSET`  | float | `459.67` | Offset to convert Fahrenheit to Rankine |
| `RANKINE_PER_KELVIN` | float | `1.8`    | Factor to convert Kelvin to Rankine     |

---

## Temperature Scales

The package supports four temperature scales:

| Scale      | Symbol   | Zero Point             | Unit System |
|------------|----------|------------------------|-------------|
| Kelvin     | K        | Absolute zero          | SI          |
| Celsius    | °C, degC | 273.15 K below Kelvin  | SI          |
| Rankine    | °R, degR | Absolute zero          | Imperial/US |
| Fahrenheit | °F, degF | 459.67°R below Rankine | Imperial/US |

**Absolute scales** (Kelvin, Rankine) start at absolute zero.
**Offset scales** (Celsius, Fahrenheit) have arbitrary zero points.

---

## Methods

### convert()

```php
public static function convert(float $value, string|UnitInterface $srcUnit, string|UnitInterface $destUnit): float
```

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

- **Offsets only apply to absolute temperatures** — When converting derived units like `J/°C` to `J/K`, only the scale factor applies, not the offset. This is physically correct because such quantities represent rates of change, not absolute temperatures:

```php
// Absolute temperature — offset is applied.
$t = new Temperature(20, 'degC');
echo $t->to('K');  // 293.15 K (not 20 K)

// Rate of change — only scale factor, no offset.
$rate = Quantity::create(5, 'J/K');
echo $rate->to('J/degR');  // 2.7778 J/°R
```

- **Prefixed Kelvin is supported** — Units like `mK` (millikelvin) work correctly:

```php
$cmbr = new Temperature(2725, 'mK');
echo $cmbr->to('K');     // 2.725 K
echo $cmbr->to('degC');  // -270.425 °C
```

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

## See Also

- **[Units: Temperature](../../Concepts/Units.md#temperature)** — Complete list of temperature units.
- **[Quantity](../Quantity.md)** — Base class documentation.
