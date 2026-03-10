# Arithmetic Operations

Quantities support a full set of arithmetic operations. All operations return new Quantity objects — the original is never modified.

```php
use Galaxon\Quantities\QuantityType\Length;

$a = new Length(100, 'm');
$b = new Length(50, 'm');

$sum = $a->add($b);        // 150 m
$diff = $a->sub($b);       // 50 m
$scaled = $a->mul(2.0);    // 200 m
$halved = $a->div(2.0);    // 50 m
$neg = $a->neg();           // -100 m
$abs = $neg->abs();         // 100 m
```

---

## add() and sub()

Add or subtract quantities of the same dimension. The result uses the unit of the first operand.

**Call styles:**
- `add($quantity)` — add another Quantity object.
- `add($value, $unit)` — convenience form; creates a Quantity from a value and unit string or object.

```php
$a = new Length(100, 'm');

// Add a Quantity object.
$sum = $a->add(new Length(2, 'km'));  // 2100 m

// Convenience form with value and unit.
$sum = $a->add(500, 'cm');  // 105 m

// Subtraction works the same way.
$diff = $a->sub(new Length(30, 'm'));  // 70 m
$diff = $a->sub(2, 'km');             // -1900 m
```

Units are automatically converted before the operation. An exception is thrown if the dimensions don't match:

```php
$a->add(new Mass(5, 'kg'));  // DomainException: incompatible dimensions
```

---

## mul()

Multiply a quantity by a scalar, a unit, or another quantity.

**Call styles:**
- `mul($scalar)` — scale the value without changing the unit.
- `mul($unit)` — multiply by a unit (symbol or object).
- `mul($quantity)` — multiply by another Quantity.
- `mul($value, $unit)` — multiply by a quantity defined by a value with a unit.

```php
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\QuantityType\Time;

$length = new Length(10, 'm');

// By scalar — scales the value, keeps the unit.
$doubled = $length->mul(2.0);  // 20 m

// By unit — adds the unit to the result.
$area = $length->mul('m');  // 10 m2

// By Quantity — multiplies values and combines units.
$width = new Length(5, 'm');
$area = $length->mul($width);  // 50 m2

// By value and unit.
$volume = $area->mul(3, 'm');  // 150 m3
```

### Same-dimension multiplication and merge()

When multiplying quantities that share a dimension but have different units, `mul()` preserves both unit terms as-is. Call `merge()` to combine compatible units:

```php
$a = new Length(2, 'm');
$b = new Length(3, 'ft');

$product = $a->mul($b);       // 6 m*ft
$merged = $product->merge();  // 1.8288 m2
```

---

## div()

Divide a quantity by a scalar, a unit, or another quantity. Supports the same call styles as `mul()`.

**Call styles:**
- `div($scalar)` — scale the value without changing the unit.
- `div($unit)` — divide by a unit (symbol or object).
- `div($quantity)` — divide by another Quantity.
- `div($value, $unit)` — divide by a quantity defined by a value with a unit.

```php
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Time;

$distance = new Length(100, 'km');

// By scalar.
$half = $distance->div(2.0);  // 50 km

// By unit.
$perSecond = $distance->div('s');  // 100 km/s

// By Quantity — produces a derived quantity type.
$time = new Time(2, 'h');
$speed = $distance->div($time);  // 50 km/h (Velocity)

// By value and unit.
$speed = $distance->div(4, 'h');  // 25 km/h
```

Division by zero throws a `DivisionByZeroError`:

```php
$distance->div(0.0);  // DivisionByZeroError
```

---

## inv()

Invert a quantity (1/x). Both the value and unit are inverted.

```php
use Galaxon\Quantities\QuantityType\Time;

$time = new Time(4, 's');
$inv = $time->inv();  // 0.25 s-1 (Frequency)
```

Throws `DivisionByZeroError` if the value is zero.

---

## neg() and abs()

`neg()` negates the value. `abs()` returns the absolute value. Neither changes the unit.

```php
use Galaxon\Quantities\QuantityType\Temperature;

$temp = new Temperature(-10, 'degC');
$pos = $temp->abs();  // 10 °C
$neg = $pos->neg();   // -10 °C
```

---

## pow()

Raise a quantity to an integer exponent. Both the value and unit are exponentiated.

```php
use Galaxon\Quantities\QuantityType\Length;

$side = new Length(3, 'm');
$area = $side->pow(2);    // 9 m2 (Area)
$volume = $side->pow(3);  // 27 m3 (Volume)
```

Negative exponents invert the unit:

```php
use Galaxon\Quantities\QuantityType\Time;

$time = new Time(2, 's');
$invSquared = $time->pow(-2);  // 0.25 s-2
```

---

## Result types

The result type of a `mul()`, `div()`, `inv()`, or `pow()` operation is determined automatically. If a built-in quantity type matches the resulting unit, the correct subclass is returned:

```php
use Galaxon\Quantities\PhysicalConstant;
use Galaxon\Quantities\QuantityType\Acceleration;
use Galaxon\Quantities\QuantityType\Frequency;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\QuantityType\Time;

// Force = Mass x Acceleration (F = ma)
$mass = new Mass(10, 'kg');
$accel = new Acceleration(9.8, 'm/s2');
$force = $mass->mul($accel);  // 98 kg*m/s2 (Force object)

// Velocity = Length / Time (v = d/t)
$distance = new Length(100, 'km');
$time = new Time(2, 'h');
$speed = $distance->div($time);  // 50 km/h (Velocity object)

// Wavelength from frequency (lambda = c/f)
$c = PhysicalConstant::speedOfLight();
$f = new Frequency(5.4545e14, 'Hz');
$wavelength = $c->div($f);  // ~550 nm (Length object)
```

Some common relationships:

- **Power x Time = Energy**: 1 kW x 1 h = 1 kWh
- **Force x Distance = Energy**: 1 N x 1 m = 1 J
- **Length x Length = Area**: 10 m x 5 m = 50 m2
- **Area x Length = Volume**: 50 m2 x 2 m = 100 m3

If no built-in quantity type matches the resulting dimension, a base `Quantity` object is returned.
