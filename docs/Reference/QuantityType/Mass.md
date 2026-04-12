# Mass

Represents mass quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Mass` class handles mass measurements across SI, Imperial, and US Customary systems.

The SI base unit for mass is the *kilogram* (`kg`), not the *gram*. This is the only SI base unit with a prefix. The *gram* is defined with all metric prefixes, so `kg`, `mg`, `μg`, etc. are all available.

---

## Unit definitions

| Name       | ASCII symbol | Prefixes   | Systems                |
| ---------- | ------------ | ---------- | ---------------------- |
| gram       | `g`          | all metric | SI                     |
| tonne      | `t`          |            | SI Accepted            |
| dalton     | `Da`         |            | SI Accepted            |
| grain      | `gr`         |            | Imperial, US Customary |
| ounce      | `oz`         |            | Imperial, US Customary |
| troy ounce | `oz t`       |            | Imperial, US Customary |
| pound      | `lb`         |            | Imperial, US Customary |
| stone      | `st`         |            | Imperial               |
| short ton  | `tn`         |            | US Customary           |
| long ton   | `LT`         |            | Imperial               |

**Note:** The SI base unit for mass is the *kilogram* (`kg`), not the *gram*.

---

## Conversion definitions

| From   | To   | Factor            |
| ------ | ---- | ----------------- |
| `t`    | `kg` | 1000              |
| `Da`   | `kg` | 1.66053906892e-27 |
| `lb`   | `kg` | 0.45359237        |
| `LT`   | `lb` | 2240              |
| `tn`   | `lb` | 2000              |
| `st`   | `lb` | 14                |
| `lb`   | `oz` | 16                |
| `lb`   | `gr` | 7000              |
| `oz t` | `gr` | 480               |

The [International Yard and Pound Agreement](https://en.wikipedia.org/wiki/International_yard_and_pound) (1959) defines the exact metric equivalent for the pound.

---

## Parts

Mass has no built-in part unit list because the choice between Imperial and US Customary units depends on context. The `Mass` class exposes both common lists as constants — pass either to a parts method via `partUnitSymbols`:

```php
use Galaxon\Quantities\QuantityType\Mass;

Mass::IMP_PART_UNITS;  // ['LT', 'st', 'lb', 'oz']
Mass::US_PART_UNITS;   // ['tn', 'lb', 'oz', 'gr']

$weight = new Mass(157, 'lb');
echo $weight->formatParts(partUnitSymbols: Mass::IMP_PART_UNITS);  // 11st 3lb

$produce = new Mass(52, 'oz');
echo $produce->formatParts(partUnitSymbols: Mass::US_PART_UNITS);  // 3lb 4oz
```

The default result unit for `Mass::fromParts()` and `Mass::parseParts()` is `lb`.

---

## Usage examples

```php
use Galaxon\Quantities\QuantityType\Mass;

// Create masses in different units
$kg = new Mass(75, 'kg');
$lb = new Mass(165, 'lb');
$g = new Mass(500, 'g');

// Convert between systems
$inPounds = $kg->to('lb');   // 165.347 lb
$inKg = $lb->to('kg');       // 74.842 kg

// Imperial/US units
$stones = new Mass(12, 'st');
$inLb = $stones->to('lb');   // 168 lb

// Scientific units
$atom = new Mass(12, 'Da');  // Carbon-12 atom
$metric = new Mass(1000, 't');  // 1000 tonnes

// Small masses
$mg = new Mass(500, 'mg');
$grains = new Mass(77, 'gr');  // ~5 grams
```

---

## Physical constants

The following physical constants have this quantity type. See [`PhysicalConstant`](../PhysicalConstant.md) for the full list.

- **`PhysicalConstant::electronMass()`** (mₑ) — Electron mass, 9.1093837015 × 10⁻³¹ kg.
- **`PhysicalConstant::protonMass()`** (mₚ) — Proton mass, 1.67262192369 × 10⁻²⁷ kg.
- **`PhysicalConstant::neutronMass()`** (mₙ) — Neutron mass, 1.67492749804 × 10⁻²⁷ kg.

---

## See also

- **[Quantity](../Quantity.md)** — Base class documentation.
- **[Force](Force.md)** — Related quantity (M·L·T⁻²).
- **[Density](Density.md)** — Related quantity (M·L⁻³).
- **[Part Decomposition](../../WorkingWithQuantities/PartDecomposition.md)** — General parts formatting and parsing.
