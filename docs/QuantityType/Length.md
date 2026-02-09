# Length

Represents length/distance quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Length` class handles distance measurements across multiple systems including SI, Imperial, US Customary, Astronomical, Nautical, and Typographical units.

For the complete list of length units, see [Supported Units: Length](../SupportedUnits.md#length).

---

## Parts Methods

The `Length` class supports decomposition into miles, yards, feet, and inches (Imperial/US):

```php
$length = new Length(5.5, 'ft');
$parts = $length->toParts();
// ['mi' => 0, 'yd' => 1, 'ft' => 2, 'in' => 6, 'sign' => 1]

// Create from parts
$length = Length::fromParts([
    'ft' => 5,
    'in' => 6,
    'sign' => 1
]);
```

---

## Key Conversions

| From | To | Factor |
|------|-----|--------|
| yard | metre | 0.9144 (exact) |
| foot | metre | 0.3048 (exact) |
| inch | millimetre | 25.4 (exact) |
| mile | yard | 1760 |
| nautical mile | metre | 1852 |
| astronomical unit | metre | 149,597,870,700 |
| light year | metre | 9,460,730,472,580,800 |

The International Yard and Pound Agreement (1959) defines the exact metric equivalents for US Customary and Imperial length units.

---

## Usage Examples

```php
use Galaxon\Quantities\QuantityType\Length;

// Create lengths in different units
$metres = new Length(100, 'm');
$feet = new Length(6, 'ft');
$miles = new Length(26.2, 'mi');

// Convert between systems
$inFeet = $metres->to('ft');     // 328.084 ft
$inMetres = $feet->to('m');      // 1.8288 m

// Metric prefixes
$km = new Length(5, 'km');
$mm = new Length(1500, 'mm');

// Astronomical distances
$earthSun = new Length(1, 'au');
$proxima = new Length(4.2465, 'ly');

// Typography (CSS)
$pixels = new Length(96, 'px');
$inches = $pixels->to('in');  // 1 in (96 px/in)

// Nautical
$voyage = new Length(100, 'nmi');
$inKm = $voyage->to('km');  // 185.2 km
```

---

## See Also

- **[Supported Units: Length](../SupportedUnits.md#length)** - Complete list of length units
- **[Quantity](../Quantity.md)** - Base class documentation
- **[Area](Area.md)** - Related quantity (L²)
- **[Volume](Volume.md)** - Related quantity (L³)
