# Part Decomposition

Some quantities are naturally expressed as a combination of units — angles in degrees, arcminutes, and arcseconds; time in hours, minutes, and seconds; lengths in feet and inches. The part decomposition methods let you convert between a single-unit value and its multi-unit parts.

### Converting to Parts with `toParts()`

The `toParts()` method breaks a quantity into integer components for each unit, with only the smallest unit having a fractional value:

```php
use Galaxon\Quantities\QuantityType\Angle;
use Galaxon\Quantities\QuantityType\Time;
use Galaxon\Quantities\QuantityType\Length;

// Angle: 45° 30′ 15.12″
$angle = new Angle(45.504200, 'deg');
$parts = $angle->toParts();
// ['sign' => 1, 'deg' => 45, 'arcmin' => 30, 'arcsec' => 15.12]

// Time: 1h 1min 1s
$time = new Time(3661, 's');
$parts = $time->toParts(['h', 'min', 's']);
// ['sign' => 1, 'h' => 1, 'min' => 1, 's' => 1.0]

// Length: 5ft 8in
$height = new Length(68, 'in');
$parts = $height->toParts(['ft', 'in']);
// ['sign' => 1, 'ft' => 5, 'in' => 8.0]
```

The result always includes a `sign` key (`1` for positive/zero, `-1` for negative). The actual part values are always non-negative.

Use the `precision` parameter to round the smallest unit:

```php
$time = new Time(3661.789, 's');

$parts = $time->toParts(['h', 'min', 's'], precision: 1);
// ['sign' => 1, 'h' => 1, 'min' => 1, 's' => 1.8]

$parts = $time->toParts(['h', 'min', 's'], precision: 0);
// ['sign' => 1, 'h' => 1, 'min' => 1, 's' => 2.0]
```

If rounding the smallest unit causes it to overflow (e.g. 59.9 seconds rounds to 60), the larger parts are adjusted automatically.

### Creating from Parts with `fromParts()`

The `fromParts()` method creates a quantity from an array of unit-value pairs:

```php
// Angle from DMS
$angle = Angle::fromParts(['deg' => 45, 'arcmin' => 30, 'arcsec' => 15.12]);
echo $angle;  // 45.504200 deg  (in the default result unit)

// Time from components
$time = Time::fromParts(['h' => 2, 'min' => 30, 's' => 45]);
echo $time;  // 9045 s

// Negative value using sign
$time = Time::fromParts(['h' => 1, 'min' => 30, 'sign' => -1]);
echo $time;  // -5400 s
```

The result unit is determined by the class's default result unit symbol (e.g. `'s'` for Time, `'deg'` for Angle). You can override it:

```php
$time = Time::fromParts(['h' => 1, 'min' => 30], 'min');
echo $time;  // 90 min
```

### Formatting Parts with `formatParts()`

The `formatParts()` method produces a human-readable string:

```php
// Angle
$angle = new Angle(45.50833333, 'deg');
echo $angle->formatParts();                // 45° 30′ 30″
echo $angle->formatParts(precision: 1);   // 45° 30′ 30.0″

// Time
$time = new Time(90061, 's');
echo $time->formatParts();                         // 1d 1h 1min 1s
echo $time->formatParts(['h', 'min', 's']);        // 25h 1min 1s

// Length
$height = new Length(68, 'in');
echo $height->formatParts(['ft', 'in']);           // 5ft 8in
echo $height->formatParts(['ft', 'in'], precision: 0);  // 5ft 8in

// Zero components are omitted by default
$time = new Time(3600, 's');
echo $time->formatParts(['h', 'min', 's']);                      // 1h
echo $time->formatParts(['h', 'min', 's'], showZeros: true);    // 1h 0min 0s

// Negative values
$time = new Time(-3661, 's');
echo $time->formatParts(['h', 'min', 's']);  // -1h 1min 1s
```

### Default Part Units

Some quantity types have default part unit symbols and result unit symbols. You can read and customise these at runtime using convenience methods on the `Quantity` subclass:

```php
use Galaxon\Quantities\QuantityType\Time;
use Galaxon\Quantities\QuantityType\Angle;

// Check the defaults
Time::getPartUnitSymbols();   // ['y', 'mo', 'w', 'd', 'h', 'min', 's']
Angle::getPartUnitSymbols();  // ['deg', 'arcmin', 'arcsec']

// Customise for your application
Time::setPartUnitSymbols(['h', 'min', 's']);
Time::setResultUnitSymbol('min');
```

The Mass class provides convenience methods for setting up imperial or US customary parts:

```php
use Galaxon\Quantities\QuantityType\Mass;

Mass::setImperialParts();
// Sets: ['LT', 'st', 'lb', 'oz'] with result unit 'lb'

$weight = new Mass(157, 'lb');
echo $weight->formatParts();  // 11st 3lb
```
