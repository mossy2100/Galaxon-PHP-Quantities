# Temperature

The `Temperature` class extends `Quantity` with special handling for offset-based temperature scales (Celsius and Fahrenheit).

---

## Why Temperature is Special

Most unit conversions are simple scale factors (e.g. 1 km = 1000 m). Temperature is different because Celsius and Fahrenheit are offset from absolute zero:

- 0 °C = 273.15 K
- 0 °F = 459.67 °R

This means converting 20 °C to Kelvin is not just multiplication — it requires adding an offset. The `Temperature` class overrides the `convert()` method to handle this correctly.

---

## Basic Conversions

```php
use Galaxon\Quantities\QuantityType\Temperature;

$boiling = new Temperature(100, 'degC');
echo $boiling->to('K');     // 373.15 K
echo $boiling->to('degF');  // 212 °F
echo $boiling->to('degR');  // 671.67 °R

$body = new Temperature(98.6, 'degF');
echo $body->to('degC');     // 37 °C

$absolute = new Temperature(0, 'K');
echo $absolute->to('degC');  // -273.15 °C
echo $absolute->to('degF');  // -459.67 °F
```

---

## Temperature Differences vs Absolute Temperatures

The offset only applies when converting absolute temperatures — quantities with dimension `H` and no other unit terms. For derived units containing temperature (e.g. J/K, W/°C), only the scale factor is applied, not the offset. This is physically correct because such quantities represent rates of change, not absolute temperatures:

```php
use Galaxon\Quantities\Quantity;

// Absolute temperature — offset is applied.
$t = new Temperature(20, 'degC');
echo $t->to('K');  // 293.15 K (not 20 K)

// Rate of change — only scale factor, no offset.
$rate = Quantity::create(5, 'J/K');
echo $rate->to('J/degR');  // 2.7778 J/°R
```

---

## Prefixed Kelvin

Kelvin supports SI prefixes (millikelvin, microkelvin, etc.), and the conversion handles these correctly:

```php
$cmbr = new Temperature(2725, 'mK');
echo $cmbr->to('K');     // 2.725 K
echo $cmbr->to('degC');  // -270.425 °C
```

---

## Conversion Constants

The `Temperature` class exposes its conversion constants for reference:

| Constant             | Value   | Meaning                          |
| -------------------- | ------- | -------------------------------- |
| `CELSIUS_OFFSET`     | 273.15  | Kelvin value of 0 °C             |
| `FAHRENHEIT_OFFSET`  | 459.67  | Rankine value of 0 °F            |
| `RANKINE_PER_KELVIN` | 1.8     | Scale factor between K and °R    |

---

## See Also

- [Temperature reference](../Reference/QuantityType/Temperature.md)
- [Supported Units](SupportedUnits.md) — Complete unit reference.
