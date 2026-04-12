# Dimensionless

Represents dimensionless quantities (ratios, percentages, concentrations).

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Dimensionless` class handles quantities that have no physical dimension, such as ratios, percentages, and concentrations.

---

## Unit definitions

| Name               | ASCII symbol | Unicode symbol | Systems |
| ------------------ | ------------ | -------------- | ------- |
| scalar             | *(empty)*    |                | Common  |
| percentage         | `%`          |                | Common  |
| parts per thousand | `ppt`        | `‰`            | Common  |
| parts per million  | `ppm`        |                | Common  |
| parts per billion  | `ppb`        |                | Common  |

---

## Conversion definitions

| From  | To    | Factor |
| ----- | ----- | ------ |
| *(scalar)* | *%*   | 100    |
| *%*   | *ppt* | 10     |
| *ppt* | *ppm* | 1000   |
| *ppm* | *ppb* | 1000   |

---

## Conversion Chain

```
1 (scalar)
  └── × 100 → % (percentage)
              └── × 10 → ‰ (parts per thousand)
                          └── × 1000 → ppm (parts per million)
                                        └── × 1000 → ppb (parts per billion)
```

---

## Usage Examples

```php
use Galaxon\Quantities\QuantityType\Dimensionless;
use Galaxon\Quantities\QuantityType\Time;

// Discount
$discount = new Dimensionless(25, '%');
$asScalar = $discount->to('');        // 0.25

// Concentrations
$fluoride = new Dimensionless(1, 'ppm');
$inPpb = $fluoride->to('ppb');        // 1000 ppb
$inPercent = $fluoride->to('%');      // 0.0001%

// Scientific measurements
$co2 = new Dimensionless(420, 'ppm');
$inPercent = $co2->to('%');           // 0.042%

// Alcohol content
$beer = new Dimensionless(5, '%');
$wine = new Dimensionless(13, '%');

// Probability
$chance = new Dimensionless(0.75, '');
$asPercent = $chance->to('%');        // 75%

// Interest rate
$annualRate = new Dimensionless(5.25, '%');
$asDecimal = $annualRate->to('');     // 0.0525

// Work capacity (hours per week as a ratio)
$hoursPerWeek = (new Time(40, 'h'))->div(new Time(1, 'w'));
$utilisation = $hoursPerWeek->to('%');  // 23.809523...%
```

---

## Arithmetic with Dimensionless Quantities

```php
// Percentage calculations
$price = 100;
$taxRate = new Dimensionless(8.5, '%');
$tax = $price * $taxRate->to('')->value;  // 8.5

// Combining percentages
$discount = new Dimensionless(20, '%');
$additional = new Dimensionless(10, '%');
$total = $discount->add($additional);     // 30%
```

---

## Physical Constants

The following physical constants have this quantity type. See [`PhysicalConstant`](../PhysicalConstant.md) for the full list.

- **`PhysicalConstant::fineStructure()`** (α) — Fine-structure constant, 7.2973525693 × 10⁻³.

---

## See Also

- **[Units: Dimensionless](../../Concepts/Units.md#dimensionless)** - Complete list of dimensionless units
- **[Quantity](../Quantity.md)** - Base class documentation
