CLAUDE TODO:
1. Show examples and explanation of fromParts(), toParts(), and formatParts(), with Angle, Time, and Length units. No need to show any parseParts() examples - this is called from parse().

# Part Decomposition

Break measurements into component parts:

```php
use Galaxon\Quantities\QuantityType\Angle;
use Galaxon\Quantities\QuantityType\Time;

// Angle to degrees, arcminutes, arcseconds
$angle = new Angle(45.5042, 'deg');
$parts = $angle->toParts(smallestUnitSymbol: 'arcsec', precision: 2);
// ['sign' => 1, 'deg' => 45, 'arcmin' => 30, 'arcsec' => 15.12]

echo $angle->formatParts(smallestUnitSymbol: 'arcsec', precision: 1);
// "45° 30′ 15.1″"

// Create from parts
$angle = Angle::fromParts(['deg' => 45, 'arcmin' => 30, 'arcsec' => 15.12]);

// Time to years, months, days, hours, minutes, seconds
$duration = new Time(90061, 's');
echo $duration->formatParts(smallestUnitSymbol: 's', precision: 0);
// "1d 1h 1min 1s"

// Convert to DateInterval
$interval = $duration->toDateInterval();