# Power

Represents power quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Power` class handles power measurements.

For the complete list of power units, see [Supported Units: Power](../SupportedUnits.md#power).

---

## SI Unit Expansion

The watt is defined as:

```
W = kg·m²·s⁻³ = J/s = V·A
```

---

## Common Prefixed Units

| Unit | Value | Typical Use |
|------|-------|-------------|
| μW | 10⁻⁶ W | Sensors, low-power electronics |
| mW | 10⁻³ W | LEDs, RF signals |
| W | 1 W | Light bulbs, small appliances |
| kW | 10³ W | Appliances, vehicles |
| MW | 10⁶ W | Power plants |
| GW | 10⁹ W | National grids |
| TW | 10¹² W | Global consumption |

---

## Usage Examples

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

## Energy Calculation

Power × Time = Energy:

```php
$power = new Power(100, 'W');
$hours = 5;
$energy = $power->value * $hours;  // 500 Wh = 0.5 kWh
```

---

## See Also

- **[Supported Units: Power](../SupportedUnits.md#power)** - Complete list
- **[Quantity](../Quantity.md)** - Base class documentation
- **[Energy](Energy.md)** - Related quantity (E = P·t)
- **[Voltage](Voltage.md)** - Related quantity (P = V·I)
- **[ElectricCurrent](ElectricCurrent.md)** - Related quantity
