# Volume

Represents volume quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Volume` class handles volume measurements including liters and Imperial/US Customary liquid measures.

For the complete list of volume units, see [Units: Volume](../../Concepts/Units.md#volume).

---

## Imperial vs US Customary

Several volume units share the same name but represent different amounts in the imperial and US customary systems. These are disambiguated with system prefixes (`imp` or `US`):

| Unit         | Imperial (`imp`)   | US Customary (`US`) |
| ------------ | ------------------ | ------------------- |
| gallon       | 4546.09 mL         | 3785.41 mL          |
| quart        | 1136.52 mL         | 946.35 mL           |
| pint         | 568.26 mL          | 473.18 mL           |
| fluid ounce  | 28.41 mL           | 29.57 mL            |
| cup          | —                  | 236.59 mL           |
| tablespoon   | 14.21 mL           | 14.79 mL            |
| teaspoon     | 3.55 mL            | 4.93 mL             |

Always use the system prefix to avoid ambiguity:

```php
use Galaxon\Quantities\QuantityType\Volume;
use Galaxon\Quantities\Services\UnitService;
use Galaxon\Quantities\UnitSystem;

$impPint = new Volume(1, 'imp pt');
$usPint = new Volume(1, 'US pt');

echo $impPint->to('mL');  // 568.26 mL
echo $usPint->to('mL');   // 473.18 mL
```

---

## Key Conversions

| From            | To             | Factor  |
| --------------- | -------------- | ------- |
| m³              | L              | 1000    |
| metric cup      | mL             | 250     |
| metric tbsp     | mL             | 15      |
| metric tsp      | mL             | 5       |
| imperial gallon | L              | 4.54609 |
| imperial gallon | imperial quart | 4       |
| imperial quart  | imperial pint  | 2       |
| imperial pint   | imperial fl oz | 20      |
| imperial fl oz  | imperial tbsp  | 2       |
| imperial tbsp   | imperial tsp   | 4       |
| US gallon       | in³            | 231     |
| US gallon       | US quart       | 4       |
| US quart        | US pint        | 2       |
| US pint         | US fl oz       | 16      |
| US pint         | US cup         | 2       |
| US cup          | US fl oz       | 8       |
| US fl oz        | US tbsp        | 2       |
| US tbsp         | US tsp         | 3       |

---

## Metric Culinary Units

The metric cup (250 mL), tablespoon (15 mL), and teaspoon (5 mL) are available in the `Metric` unit system. These use unprefixed symbols (`cup`, `tbsp`, `tsp`) since metric is the international standard for these units.

```php
$recipe = new Volume(2, 'cup');
echo $recipe->to('mL');  // 500 mL

$oil = new Volume(3, 'tbsp');
echo $oil->to('mL');  // 45 mL

$salt = new Volume(1, 'tsp');
echo $salt->to('mL');  // 5 mL
```

---

## Cubic Units

Cubic length units are automatically supported through unit arithmetic:

```php
// Cubic meters
$tank = new Volume(1000, 'L');
$inCubicMeters = $tank->to('m3');  // 1 m³

// Cubic centimeters (same as mL)
$cc = new Quantity(500, 'cm3');
$inMl = $cc->to('mL');  // 500 mL

// Cubic inches
$engine = new Quantity(350, 'in3');
$inLitres = $engine->to('L');  // 5.735 L
```

---

## Usage Examples

```php
use Galaxon\Quantities\QuantityType\Volume;

// Metric volumes
$liters = new Volume(2, 'L');
$ml = new Volume(500, 'mL');

// US volumes
$usGal = new Volume(5, 'US gal');
$usPint = new Volume(1, 'US pt');

// Imperial volumes
$impGal = new Volume(5, 'imp gal');
$impPint = new Volume(1, 'imp pt');

// Convert between systems
$usInLitres = $usGal->to('L');     // 18.927 L
$impInLitres = $impGal->to('L');   // 22.730 L

// Fuel economy example
$tank = new Volume(50, 'L');
$inUsGal = $tank->to('US gal');    // 13.209 US gal
$inImpGal = $tank->to('imp gal');  // 10.998 imp gal
```

---

## See Also

- **[Units: Volume](../../Concepts/Units.md#volume)** - Complete list of volume units
- **[Quantity](../Quantity.md)** - Base class documentation
- **[Length](Length.md)** - Related quantity (cubic length = volume)
- **[Area](Area.md)** - Related quantity
