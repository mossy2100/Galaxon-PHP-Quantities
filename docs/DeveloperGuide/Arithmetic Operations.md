# Arithmetic Operations

Quantities support addition, subtraction, multiplication, and division:

```php
$a = new Length(100, 'm');
$b = new Length(50, 'm');

$sum = $a->add($b);        // 150 m
$diff = $a->sub($b);       // 50 m
$scaled = $a->mul(2.0);    // 200 m
$halved = $a->div(2.0);    // 50 m
$abs = $diff->neg()->abs();  // 50 m

// Add with different units (auto-converted)
$total = $a->add(new Length(1, 'km'));  // 1100 m

// Convenience syntax
$total = $a->add(500, 'cm');  // 105 m
```

Multiplying or dividing quantities of different types produces the correct quantity type and units:

```php
use Galaxon\Quantities\PhysicalConstant;
use Galaxon\Quantities\QuantityType\Acceleration;
use Galaxon\Quantities\QuantityType\Frequency;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\QuantityType\Time;

// Force = Mass × Acceleration (F = m·a)
$mass = new Mass(10, 'kg');
$accel = new Acceleration(9.8, 'm/s2');
$force = $mass->mul($accel);  // 98 N (Force)

// Velocity = Length / Time (v = d/t)
$distance = new Length(100, 'km');
$time = new Time(2, 'h');
$speed = $distance->div($time);  // 50 km/h (Velocity)

// Wavelength from frequency (λ = c/f)
$c = PhysicalConstant::speedOfLight();
$f = new Frequency(5.4545e14, 'Hz');
$wavelength = $c->div($f);  // ~550 nm (Length)
```
