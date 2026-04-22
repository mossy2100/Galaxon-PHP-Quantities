# CatalyticActivity

Represents catalytic activity quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `CatalyticActivity` class handles catalytic activity measurements, used in enzymology and biochemistry.

---

## Unit definitions

| Name  | ASCII symbol | Prefixes   | Systems |
| ----- | ------------ | ---------- | ------- |
| katal | `kat`        | all metric | SI      |

---

## Conversion definitions

| From  | To          | Factor |
| ----- | ----------- | ------ |
| `kat` | `mol*s-1`   | 1      |

---

## SI unit

The *katal* is defined as:

```
kat = mol·s⁻¹
```

One *katal* is the catalytic activity that converts one mole of substrate per second.

---

## Usage examples

```php
use Galaxon\Quantities\QuantityType\CatalyticActivity;

// Enzyme assay
$activity = new CatalyticActivity(50, 'nkat');
$inPkat = $activity->to('pkat');  // 50,000 pkat

// Clinical measurement
$serum = new CatalyticActivity(1, 'ukat');
$inNkat = $serum->to('nkat');  // 1000 nkat
```

---

## See also

- **[Quantity](../Quantity.md)** - Base class documentation
- **[AmountOfSubstance](AmountOfSubstance.md)** - Related quantity
- **[Time](Time.md)** - Related quantity
