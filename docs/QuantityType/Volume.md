# Volume

Represents volume quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Volume` class handles volume measurements including liters and Imperial/US Customary liquid measures.

For the complete list of volume units, see [Supported Units: Volume](../SupportedUnits.md#volume).

---

## US vs Imperial

US Customary and Imperial volume units share names but have different sizes:

| Unit | US Customary | Imperial |
|------|--------------|----------|
| Fluid ounce | 29.5735 mL | 28.4131 mL |
| Pint | 473.176 mL | 568.261 mL |
| Quart | 946.353 mL | 1136.52 mL |
| Gallon | 3.78541 L | 4.54609 L |

Use the appropriate prefix (`US` or `imp`) to specify which system:

```php
$usGal = new Volume(1, 'US gal');
$impGal = new Volume(1, 'imp gal');

$usGal->to('L');   // 3.78541 L
$impGal->to('L');  // 4.54609 L
```

---

## Key Conversions

| From | To | Factor |
|------|-----|--------|
| m³ | L | 1000 |
| US gallon | in³ | 231 (exact) |
| US gallon | US quart | 4 |
| US quart | US pint | 2 |
| US pint | US fl oz | 16 |
| Imperial gallon | L | 4.54609 (exact) |
| Imperial gallon | imp quart | 4 |
| Imperial quart | imp pint | 2 |
| Imperial pint | imp fl oz | 20 |

---

## Cubic Units

Cubic length units are automatically supported through unit arithmetic:

```php
// Cubic meters
$tank = new Volume(1000, 'L');
$inCubicMeters = $tank->to('m3');  // 1 m³

// Cubic centimeters (same as mL)
$cc = new Quantity(500, 'cm3');
$inMl = $cc->to('mL');  // 500 mL

// Cubic inches
$engine = new Quantity(350, 'in3');
$inLitres = $engine->to('L');  // 5.735 L
```

---

## Usage Examples

```php
use Galaxon\Quantities\QuantityType\Volume;

// Metric volumes
$liters = new Volume(2, 'L');
$ml = new Volume(500, 'mL');

// US volumes
$usGal = new Volume(5, 'US gal');
$usPint = new Volume(1, 'US pt');

// Imperial volumes
$impGal = new Volume(5, 'imp gal');
$impPint = new Volume(1, 'imp pt');

// Convert between systems
$usInLitres = $usGal->to('L');     // 18.927 L
$impInLitres = $impGal->to('L');   // 22.730 L

// Fuel economy example
$tank = new Volume(50, 'L');
$inUsGal = $tank->to('US gal');    // 13.209 US gal
$inImpGal = $tank->to('imp gal');  // 10.998 imp gal
```

---

## See Also

- **[Supported Units: Volume](../SupportedUnits.md#volume)** - Complete list of volume units
- **[Quantity](../Quantity.md)** - Base class documentation
- **[Length](Length.md)** - Related quantity (cubic length = volume)
- **[Area](Area.md)** - Related quantity
