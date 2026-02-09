# Velocity

Represents velocity/speed quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Velocity` class handles speed and velocity measurements. Most velocity units are derived from length/time ratios (m/s, km/h, mph), which are automatically supported. This class provides the knot as a named unit.

For the complete list of velocity units, see [Supported Units: Velocity](../SupportedUnits.md#velocity).

---

## Knot Definition

The knot is defined as one nautical mile per hour:

```
1 kn = 1 nmi/h = 1.852 km/h ≈ 1.15078 mph
```

---

## Compound Velocity Units

Velocity units are automatically supported through unit arithmetic:

```php
use Galaxon\Quantities\Quantity;

// Metres per second
$speed = new Quantity(10, 'm/s');

// Kilometres per hour
$car = new Quantity(100, 'km/h');
$inMs = $car->to('m/s');  // 27.778 m/s

// Miles per hour
$highway = new Quantity(70, 'mi/h');
$inKmh = $highway->to('km/h');  // 112.65 km/h
```

---

## Usage Examples

```php
use Galaxon\Quantities\QuantityType\Velocity;
use Galaxon\Quantities\Quantity;

// Nautical speed
$ship = new Velocity(20, 'kn');
$inKmh = $ship->to('km/h');  // 37.04 km/h
$inMph = $ship->to('mi/h');  // 23.02 mph

// Aircraft
$cruising = new Velocity(500, 'kn');
$inMs = $cruising->to('m/s');  // 257.22 m/s

// Compound units
$runner = new Quantity(10, 'm/s');
$inKmh = $runner->to('km/h');  // 36 km/h

// Speed of sound (at sea level)
$mach1 = new Quantity(343, 'm/s');
$inKn = $mach1->to('kn');  // 666.74 kn

// Speed of light
$c = new Quantity(299792458, 'm/s');
$inKmh = $c->to('km/h');  // 1.079×10⁹ km/h
```

---

## Common Speed Conversions

| Speed | m/s | km/h | mph | kn |
|-------|-----|------|-----|-----|
| Walking | 1.4 | 5 | 3.1 | 2.7 |
| Running | 6 | 21.6 | 13.4 | 11.7 |
| City driving | 13.9 | 50 | 31.1 | 27.0 |
| Highway | 31.3 | 112.7 | 70 | 60.8 |
| Sound | 343 | 1235 | 767 | 667 |

---

## See Also

- **[Supported Units: Velocity](../SupportedUnits.md#velocity)** - Complete list of velocity units
- **[Quantity](../Quantity.md)** - Base class documentation
- **[Length](Length.md)** - Related quantity
- **[Time](Time.md)** - Related quantity
- **[Acceleration](Acceleration.md)** - Related quantity (velocity per time)
