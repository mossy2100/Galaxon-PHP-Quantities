# Acceleration

Represents acceleration quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Acceleration` class handles acceleration measurements. This class has no dedicated unit definitions as acceleration is expressed using compound units (m/s², ft/s², etc.).

---

## Compound Units

Acceleration units are automatically supported through unit arithmetic:

```php
use Galaxon\Quantities\Quantity;

// Metres per second squared
$gravity = new Quantity(9.80665, 'm/s2');

// Feet per second squared
$inFtS2 = $gravity->to('ft/s2');  // 32.174 ft/s²

// Kilometres per hour per second
$car = new Quantity(3.6, 'km/(h*s)');  // Equivalent to 1 m/s²
```

---

## Standard Gravity

The standard acceleration due to gravity (g₀) is defined exactly as:

```
g₀ = 9.80665 m/s²
```

This value is used to define the pound-force and other gravitational units.

---

## Usage Examples

```php
use Galaxon\Quantities\Quantity;

// Earth's gravity
$g = new Quantity(9.80665, 'm/s2');

// Vehicle acceleration (0-100 km/h in 8 seconds)
$car = new Quantity(100 / 8, 'km/(h*s)');
$inMs2 = $car->to('m/s2');  // 3.472 m/s²
$inG = $car->value / 9.80665;  // 0.354 g

// Centrifuge
$centrifuge = new Quantity(10000 * 9.80665, 'm/s2');  // 10,000 g
```

---

## See Also

- **[Quantity](../Quantity.md)** - Base class documentation
- **[Velocity](Velocity.md)** - Related quantity (a = Δv/t)
- **[Force](Force.md)** - Related quantity (F = ma)
- **[Time](Time.md)** - Related quantity
