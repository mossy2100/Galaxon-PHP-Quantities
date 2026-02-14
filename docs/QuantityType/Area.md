# Area

Represents area quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Area` class handles area measurements. Most area units are derived from length units squared (m², ft², etc.), which are automatically supported. This class provides additional named area units.

For the complete list of area units, see [Supported Units: Area](../SupportedUnits.md#area).

---

## Key Conversions

| From | To | Factor |
|------|-----|--------|
| hectare | m² | 10,000 |
| acre | yd² | 4,840 |

---

## Square Units

Square length units are automatically supported through unit arithmetic:

```php
use Galaxon\Quantities\Quantity;

// Square meters
$room = new Quantity(25, 'm2');
$inSqFt = $room->to('ft2');  // 269.098 ft²

// Square kilometers
$country = new Quantity(7692024, 'km2');  // Australia

// Square inches
$screen = new Quantity(15.6 * 9, 'in2');
```

---

## Usage Examples

```php
use Galaxon\Quantities\QuantityType\Area;
use Galaxon\Quantities\Quantity;

// Named area units
$farm = new Area(100, 'ha');
$inAcres = $farm->to('ac');  // 247.105 ac

$lot = new Area(0.5, 'ac');
$inHa = $lot->to('ha');  // 0.2023 ha

// Square length units
$floor = new Quantity(150, 'm2');
$inSqFt = $floor->to('ft2');  // 1614.59 ft²

// Convert between area systems
$hectares = new Area(1, 'ha');
$inSqM = $hectares->to('m2');    // 10,000 m²
$inSqKm = $hectares->to('km2');  // 0.01 km²
```

---

## See Also

- **[Supported Units: Area](../SupportedUnits.md#area)** - Complete list of area units
- **[Quantity](../Quantity.md)** - Base class documentation
- **[Length](Length.md)** - Related quantity (squared length = area)
- **[Volume](Volume.md)** - Related quantity (area × length = volume)
