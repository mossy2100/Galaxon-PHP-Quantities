# AmountOfSubstance

Represents amount of substance quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `AmountOfSubstance` class handles measurements of the amount of substance. The mole is one of the seven SI base units.

For the complete list of amount of substance units, see [Supported Units: Amount of Substance](../SupportedUnits.md#amount-of-substance).

---

## SI Base Unit

The mole (mol) is defined by fixing the value of the Avogadro constant:

```
Nₐ = 6.02214076 × 10²³ mol⁻¹
```

One mole contains exactly 6.02214076 × 10²³ elementary entities.

---

## Common Prefixed Units

| Unit | Value | Typical Use |
|------|-------|-------------|
| pmol | 10⁻¹² mol | Biochemistry |
| nmol | 10⁻⁹ mol | Molecular biology |
| μmol | 10⁻⁶ mol | Clinical chemistry |
| mmol | 10⁻³ mol | Blood chemistry |
| mol | 1 mol | General chemistry |
| kmol | 10³ mol | Industrial chemistry |

---

## Usage Examples

```php
use Galaxon\Quantities\QuantityType\AmountOfSubstance;

// Chemistry
$sample = new AmountOfSubstance(0.5, 'mol');
$inMmol = $sample->to('mmol');  // 500 mmol

// Biochemistry
$enzyme = new AmountOfSubstance(100, 'nmol');
$inUmol = $enzyme->to('umol');  // 0.1 μmol

// Blood glucose (typical: 4-6 mmol/L)
$glucose = new AmountOfSubstance(5, 'mmol');
```

---

## See Also

- **[Supported Units: Amount of Substance](../SupportedUnits.md#amount-of-substance)** - Complete list
- **[Quantity](../Quantity.md)** - Base class documentation
- **[CatalyticActivity](CatalyticActivity.md)** - Related quantity (mol/s)
