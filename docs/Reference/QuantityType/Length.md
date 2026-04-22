# Length

Represents length/distance quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Length` class handles distance measurements across multiple systems including SI, Imperial, US Customary, Scientific, Nautical, and Css units.

---

## Unit definitions

| Name              | ASCII symbol | Prefixes     | Systems                 |
| ----------------- | ------------ | ------------ | ----------------------- |
| meter             | `m`          | all metric   | SI                      |
| astronomical unit | `au`         |              | SI Accepted, Scientific |
| light year        | `ly`         |              | Scientific              |
| parsec            | `pc`²        | large metric | Scientific              |
| pixel             | `px`         |              | CSS                     |
| point             | `pt`¹        |              | CSS                     |
| pica              | `p`²         |              | CSS                     |
| inch              | `in`         |              | Imperial, US Customary  |
| foot              | `ft`         |              | Imperial, US Customary  |
| yard              | `yd`         |              | Imperial, US Customary  |
| mile              | `mi`         |              | Imperial, US Customary  |
| fathom            | `ftm`        |              | Nautical                |
| nautical mile     | `nmi`        |              | Nautical                |

**Note 1:** The abbreviation `pt`, for *point*, can also mean *pint*. Unit uniqueness is maintained because the unit symbols for pints within the package are `imp pt` and `US pt`.

**Note 2:** While CSS uses `pc` for *picas*, the package instead uses `p` for several reasons:
1. `pc` is also the symbol for *parsec*, and there's no obvious alternative.
2. Lower-case `p` is used for *picas* in design software like Adobe InDesign and QuarkXPress, so there is an established precedent.
3. *Picas* are not often used in CSS, so it shouldn't be a huge problem.

---

## Conversion definitions

| From   | To    | Factor             |
| ------ | ----- | ------------------ |
| `yd`   | `m`   | 0.9144             |
| `ft`   | `m`   | 0.3048             |
| `in`   | `mm`  | 25.4               |
| `in`   | `px`  | 96                 |
| `in`   | `pt`  | 72                 |
| `in`   | `p`   | 6                  |
| `ft`   | `in`  | 12                 |
| `yd`   | `ft`  | 3                  |
| `mi`   | `yd`  | 1760               |
| `au`   | `m`   | 149597870700       |
| `ly`   | `m`   | 9460730472580800   |
| `pc`   | `au`  | 648000 / π         |
| `ftm`  | `yd`  | 2                  |
| `nmi`  | `m`   | 1852               |

The [International Yard and Pound Agreement](https://en.wikipedia.org/wiki/International_yard_and_pound) (1959) defines the exact metric equivalents for US Customary and Imperial length units.

---

## Parts

The `Length` class supports decomposition into *miles*, *yards*, *feet*, and *inches* (Imperial/US) by default. The default result unit for `Length::fromParts()` and `Length::parseParts()` is `ft`.

```php
$length = new Length(5.5, 'ft');
$parts = $length->toParts();
// ['sign' => 1, 'mi' => 0, 'yd' => 1, 'ft' => 2, 'in' => 6.0]

echo $length->formatParts();
// 1yd 2ft 6in

// Create from parts.
$length = Length::fromParts([
    'ft' => 5,
    'in' => 6,
]);
echo $length;  // 5.5 ft
```

To use a different set of part units, pass `partUnitSymbols` to the parts method. For example, to decompose into feet and inches only:

```php
$height = new Length(68, 'in');
echo $height->formatParts(partUnitSymbols: ['ft', 'in']);
// 5ft 8in
```

---

## Usage examples

```php
use Galaxon\Quantities\QuantityType\Length;

// Create lengths in different units
$meters = new Length(100, 'm');
$feet = new Length(6, 'ft');
$miles = new Length(26.2, 'mi');

// Convert between systems
$inFeet = $meters->to('ft');     // 328.084 ft
$inMeters = $feet->to('m');      // 1.8288 m

// Metric prefixes
$km = new Length(5, 'km');
$mm = new Length(1500, 'mm');

// Astronomical distances
$earthSun = new Length(1, 'au');
$proxima = new Length(4.2465, 'ly');

// CSS
$pixels = new Length(96, 'px');
$inches = $pixels->to('in');  // 1 in (96 px/in)

// Nautical
$voyage = new Length(100, 'nmi');
$inKm = $voyage->to('km');  // 185.2 km
```

---

## Physical constants

The following physical constants have this quantity type. See [`PhysicalConstant`](../PhysicalConstant.md) for the full list.

- **`PhysicalConstant::bohrRadius()`** (a₀) — Bohr radius, 5.29177210903 × 10⁻¹¹ m.

---

## See also

- **[Quantity](../Quantity.md)** - Base class documentation
- **[Area](Area.md)** - Related quantity (L²)
- **[Volume](Volume.md)** - Related quantity (L³)
- **[Part Decomposition](../../WorkingWithQuantities/PartDecomposition.md)** — General parts formatting and parsing.
