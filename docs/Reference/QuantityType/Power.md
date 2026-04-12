# Power

Represents power quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Power` class handles power measurements.

---

## Unit definitions

| Name | ASCII symbol | Prefixes   | Systems |
| ---- | ------------ | ---------- | ------- |
| watt | `W`          | all metric | SI      |

---

## Conversion definitions

| From | To              | Factor |
| ---- | --------------- | ------ |
| `W`  | `kg*m2*s-3`     | 1      |

---

## SI Unit Expansion

The *watt* is defined as:

```
W = kg·m²·s⁻³ = J/s = V·A
```

---

## Usage examples

```php
use Galaxon\Quantities\QuantityType\Power;

// Household
$bulb = new Power(60, 'W');
$kettle = new Power(2, 'kW');

// Convert units
$inW = $kettle->to('W');  // 2000 W
$inMW = $kettle->to('MW');  // 0.002 MW

// Electronics
$laser = new Power(5, 'mW');
$inUW = $laser->to('uW');  // 5000 μW

// Power generation
$plant = new Power(1000, 'MW');
$inGW = $plant->to('GW');  // 1 GW
```

---

## Energy calculation

Power × Time = Energy:

```php
$power = new Power(100, 'W');
$hours = 5;
$energy = $power->value * $hours;  // 500 Wh = 0.5 kWh
```

---

## See also

- **[Quantity](../Quantity.md)** - Base class documentation
- **[Energy](Energy.md)** - Related quantity (E = P·t)
- **[Voltage](Voltage.md)** - Related quantity (P = V·I)
- **[ElectricCurrent](ElectricCurrent.md)** - Related quantity
