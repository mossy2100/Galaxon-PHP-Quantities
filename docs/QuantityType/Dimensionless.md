# Dimensionless

Represents dimensionless quantities (ratios, percentages, concentrations).

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Dimensionless` class handles quantities that have no physical dimension, such as ratios, percentages, and concentrations.

For the complete list of dimensionless units, see [Supported Units: Dimensionless](../SupportedUnits.md#dimensionless).

---

## Units

| Unit               | Symbol      | Value |
|--------------------|-------------|-------|
| scalar             | *(empty)*   | 1     |
| percentage         | %           | 0.01  |
| parts per thousand | ppt, ‰      | 0.001 |
| parts per million  | ppm         | 10⁻⁶  |
| parts per billion  | ppb         | 10⁻⁹  |

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

// Percentages
$rate = new Dimensionless(0.05, '');  // 5% as scalar
$asPercent = $rate->to('%');          // 5%

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

## See Also

- **[Supported Units: Dimensionless](../SupportedUnits.md#dimensionless)** - Complete list of dimensionless units
- **[Quantity](../Quantity.md)** - Base class documentation
