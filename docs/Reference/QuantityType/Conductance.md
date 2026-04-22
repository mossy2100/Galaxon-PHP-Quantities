# Conductance

Represents electrical conductance quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Conductance` class handles electrical conductance measurements. Conductance is the reciprocal of resistance.

---

## Unit definitions

| Name    | ASCII symbol | Prefixes   | Systems |
| ------- | ------------ | ---------- | ------- |
| siemens | `S`          | all metric | SI      |

---

## Conversion definitions

| From | To                  | Factor |
| ---- | ------------------- | ------ |
| `S`  | `kg-1*m-2*s3*A2`    | 1      |

---

## SI unit

The *siemens* is defined as:

```
S = s³·A²·kg⁻¹·m⁻² = A/V = 1/Ω
```

---

## Usage examples

```php
use Galaxon\Quantities\QuantityType\Conductance;

// Water quality
$tap = new Conductance(500, 'uS');
$distilled = new Conductance(1, 'uS');

// Convert to resistance
$g = new Conductance(1, 'mS');
// 1 mS = 1 kΩ resistance

// Ion channels
$channel = new Conductance(30, 'pS');
$inNS = $channel->to('nS');  // 0.03 nS
```

---

## See also

- **[Quantity](../Quantity.md)** - Base class documentation
- **[Resistance](Resistance.md)** - Inverse quantity (Ω = 1/S)
