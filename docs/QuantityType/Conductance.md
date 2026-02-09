# Conductance

Represents electrical conductance quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Conductance` class handles electrical conductance measurements. Conductance is the reciprocal of resistance.

For the complete list of conductance units, see [Supported Units: Conductance](../SupportedUnits.md#conductance).

---

## SI Unit Expansion

The siemens is defined as:

```
S = s³·A²·kg⁻¹·m⁻² = A/V = 1/Ω
```

---

## Common Prefixed Units

| Unit | Value | Typical Use |
|------|-------|-------------|
| pS | 10⁻¹² S | Ion channels |
| nS | 10⁻⁹ S | Membranes |
| μS | 10⁻⁶ S | Water quality |
| mS | 10⁻³ S | Conductivity meters |
| S | 1 S | Standard |
| kS | 10³ S | High conductivity |

---

## Usage Examples

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

## See Also

- **[Supported Units: Conductance](../SupportedUnits.md#conductance)** - Complete list
- **[Quantity](../Quantity.md)** - Base class documentation
- **[Resistance](Resistance.md)** - Inverse quantity (Ω = 1/S)
