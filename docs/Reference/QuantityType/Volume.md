# Volume

Represents volume quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Volume` class handles volume measurements including liters and Imperial/US Customary liquid measures.

---

## Unit definitions

| Name                 | ASCII symbol | Prefixes   | Systems             |
| -------------------- | ------------ | ---------- | ------------------- |
| liter                | `L`          | all metric | SI Accepted, Metric |
| metric cup           | `cup`        |            | Metric              |
| metric tablespoon    | `tbsp`       |            | Metric              |
| metric teaspoon      | `tsp`        |            | Metric              |
| imperial gallon      | `imp gal`    |            | Imperial            |
| imperial quart       | `imp qt`     |            | Imperial            |
| imperial pint        | `imp pt`     |            | Imperial            |
| imperial fluid ounce | `imp fl oz`  |            | Imperial            |
| imperial tablespoon  | `imp tbsp`   |            | Imperial            |
| imperial teaspoon    | `imp tsp`    |            | Imperial            |
| US gallon            | `US gal`     |            | US Customary        |
| US quart             | `US qt`      |            | US Customary        |
| US pint              | `US pt`      |            | US Customary        |
| US cup               | `US cup`     |            | US Customary        |
| US fluid ounce       | `US fl oz`   |            | US Customary        |
| US tablespoon        | `US tbsp`    |            | US Customary        |
| US teaspoon          | `US tsp`     |            | US Customary        |

**Note:** Cubic units like m³, cm³, ft³, etc. are automatically supported through unit arithmetic.

---

## Conversion definitions

| From          | To            | Factor  |
| ------------- | ------------- | ------- |
| `m3`          | `L`           | 1000    |
| `cup`         | `mL`          | 250     |
| `tbsp`        | `mL`          | 15      |
| `tsp`         | `mL`          | 5       |
| `US gal`      | `in3`         | 231     |
| `US gal`      | `US qt`       | 4       |
| `US qt`       | `US pt`       | 2       |
| `US pt`       | `US cup`      | 2       |
| `US cup`      | `US fl oz`    | 8       |
| `US fl oz`    | `US tbsp`     | 2       |
| `US tbsp`     | `US tsp`      | 3       |
| `imp gal`     | `L`           | 4.54609 |
| `imp gal`     | `imp qt`      | 4       |
| `imp qt`      | `imp pt`      | 2       |
| `imp pt`      | `imp fl oz`   | 20      |
| `imp fl oz`   | `imp tbsp`    | 2       |
| `imp tbsp`    | `imp tsp`     | 4       |

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

$impPint = new Volume(1, 'imp pt');
$usPint = new Volume(1, 'US pt');

echo $impPint->to('mL');  // 568.26 mL
echo $usPint->to('mL');   // 473.18 mL
```

---

## Metric culinary units

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

## Cubic units

Cubic length units are automatically supported through unit arithmetic:

```php
// Cubic meters
$tank = new Volume(1000, 'L');
$inCubicMeters = $tank->to('m3');  // 1 m³

// Cubic centimeters (same as mL)
$cc = new Volume(500, 'cm3');
$inMl = $cc->to('mL');  // 500 mL

// Cubic inches
$engine = new Volume(350, 'in3');
$inLitres = $engine->to('L');  // 5.735 L
```

---

## Parts

Volume has no built-in part unit list because the choice between Imperial and US Customary units depends on context. The `Volume` class exposes both common lists as constants — pass either to a parts method via `partUnitSymbols`:

```php
use Galaxon\Quantities\QuantityType\Volume;

Volume::IMP_PART_UNITS;  // ['imp gal', 'imp qt', 'imp pt', 'imp fl oz']
Volume::US_PART_UNITS;   // ['US gal', 'US qt', 'US pt', 'US cup', 'US fl oz']

$beer = new Volume(5.5, 'imp pt');
echo $beer->formatParts(partUnitSymbols: Volume::IMP_PART_UNITS);
// 2 imp qt 1 imp pt 10 imp fl oz

$milk = new Volume(1.5, 'US gal');
echo $milk->formatParts(partUnitSymbols: Volume::US_PART_UNITS);
// 1 US gal 2 US qt
```

---

## Usage examples

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

## See also

- **[Quantity](../Quantity.md)** - Base class documentation
- **[Length](Length.md)** - Related quantity (cubic length = volume)
- **[Area](Area.md)** - Related quantity
