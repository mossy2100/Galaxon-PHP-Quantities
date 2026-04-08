# QuantityPartsService

Internal service that powers the parts methods on `Quantity` (`fromParts`, `toParts`, `parseParts`, `formatParts`).

**Namespace:** `Galaxon\Quantities\Services`

---

## Overview

`QuantityPartsService` decomposes quantities into multi-unit parts and reassembles them — operations like turning `1.5 hours` into `"1h 30min 0s"` and back.

You generally shouldn't call its methods directly. The four parts methods are marked `@internal` and are exposed via wrapper methods on [`Quantity`](../Quantity.md#parts-methods) (and through to its subclasses such as `Time`, `Angle`, `Length`, `Mass`).

Built-in defaults are baked into a private constant; the service has no mutable state. To use a different set of part units or a different result unit for a single call, pass `$partUnitSymbols` or `$resultUnitSymbol` directly to the parts method — see [Part Decomposition](../../WorkingWithQuantities/PartDecomposition.md) for examples.

### Built-in Defaults

| Quantity Type | Part Unit Symbols                    | Result Unit Symbol |
|---------------|--------------------------------------|--------------------|
| length        | `mi`, `yd`, `ft`, `in`               | `ft`               |
| time          | `y`, `mo`, `w`, `d`, `h`, `min`, `s` | `s`                |
| angle         | `deg`, `arcmin`, `arcsec`            | `deg`              |
| mass          | *(none)*                             | `lb`               |

For mass, the `Mass` class also exposes ready-to-use part-unit lists as constants:

```php
Mass::IMP_PART_UNITS;  // ['LT', 'st', 'lb', 'oz']
Mass::US_PART_UNITS;   // ['tn', 'lb', 'oz', 'gr']
```

Pass either constant inline as the `$partUnitSymbols` argument:

```php
$weight = new Mass(157, 'lb');
echo $weight->formatParts(partUnitSymbols: Mass::IMP_PART_UNITS);  // "11st 3lb"
```

All methods on `QuantityPartsService` are static.

---

## See Also

- **[Quantity (Parts Methods)](../Quantity.md#parts-methods)** — The public API for parts decomposition and reassembly.
- **[Part Decomposition](../../WorkingWithQuantities/PartDecomposition.md)** — Usage guide with examples.
- **[Mass](../QuantityType/Mass.md)** — Includes the `IMP_PART_UNITS` and `US_PART_UNITS` convenience constants.
- **[UnitService](UnitService.md)** — Unit lookup and registration.
