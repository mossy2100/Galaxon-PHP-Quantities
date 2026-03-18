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
$inv = $a->inv();           // 0.01 m-1
```

For full method signatures and parameter details, see the [Quantity reference](../Reference/Quantity.md#arithmetic-methods).

---

## add() and sub()

These accept a single Quantity argument with the same dimension. The result uses the unit of the calling object, and units are automatically converted:

```php
$a = new Length(100, 'm');
$sum = $a->add(new Length(2, 'km'));     // 2100 m
$diff = $a->sub(new Length(50, 'cm'));   // 99.5 m

// Works across unit systems.
$miles = new Length(1, 'mi');
$sum = $miles->add(new Length(1, 'km')); // ~1.621 mi
```

A `DimensionMismatchException` is thrown if the dimensions don't match:

```php
$a->add(new Mass(5, 'kg'));  // DimensionMismatchException
```

---

## mul() and div()

These accept a scalar, a Quantity, or a unit (as a string or `UnitInterface` object). The behaviour depends on what you pass:

- **Scalar** — scales the value without changing the unit.
- **Quantity** — multiplies/divides values and combines units.
- **Unit** — multiplies/divides units.

```php
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Time;

$length = new Length(10, 'm');

// By scalar.
$doubled = $length->mul(2.0);  // 20 m
$halved = $length->div(2.0);   // 5 m

// By Quantity.
$width = new Length(5, 'm');
$area = $length->mul($width);  // 50 m2

$distance = new Length(100, 'km');
$time = new Time(2, 'h');
$speed = $distance->div($time);  // 50 km/h

// By unit string.
$areaUnit = $length->mul('m');      // 10 m2
$perSecond = $length->div('s');     // 10 m/s
```

Division by zero throws a `DivisionByZeroError`.

### Same-dimension multiplication and merge()

When multiplying quantities that share a dimension but use different units, `mul()` preserves both unit terms as-is. Call `merge()` afterwards if you want to consolidate them:

```php
$a = new Length(2, 'm');
$b = new Length(3, 'ft');

$product = $a->mul($b);       // 6 m*ft
$merged = $product->merge();  // ~1.829 m2
```

This applies whenever `mul()` or `div()` produces a result with multiple terms of the same dimension.

---

## inv()

Invert a quantity (1/x). Both the value and unit are inverted:

```php
use Galaxon\Quantities\QuantityType\Time;

$time = new Time(4, 's');
$inv = $time->inv();  // 0.25 s-1 (Frequency)
```

Throws `DivisionByZeroError` if the value is zero.

---

## neg() and abs()

`neg()` negates the value. `abs()` returns the absolute value. Neither changes the unit:

```php
use Galaxon\Quantities\QuantityType\Temperature;

$temp = new Temperature(-10, 'degC');
$pos = $temp->abs();  // 10 °C
$neg = $pos->neg();   // -10 °C
```

---

## pow()

Raise a quantity to an integer exponent. Both the value and unit are exponentiated:

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

## Chaining operations

Since every operation returns a new Quantity, you can chain calls:

```php
use Galaxon\Quantities\QuantityType\Length;

$length = new Length(10, 'm');
$result = $length->mul(2)->add(new Length(5, 'm'))->sub(new Length(3, 'm'));
// (10 * 2) + 5 - 3 = 22 m
```

---

## Result types

The result type of `add()`, `sub()`, `neg()`, or `abs()` is always the same type as the calling object — the dimension cannot change.

The result type of `mul()`, `div()`, `inv()`, or `pow()` is determined automatically. If the result quantity's dimension matches a registered quantity type, the correct subclass is returned. Otherwise, a base `Quantity` object is returned:

```php
use Galaxon\Quantities\PhysicalConstant;
use Galaxon\Quantities\QuantityType\Acceleration;
use Galaxon\Quantities\QuantityType\Frequency;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\QuantityType\Time;

// Force = Mass × Acceleration (F = ma)
$mass = new Mass(10, 'kg');
$accel = new Acceleration(9.8, 'm/s2');
$force = $mass->mul($accel);  // 98 kg*m/s2 (Force object)

// Velocity = Length / Time (v = d/t)
$distance = new Length(100, 'km');
$time = new Time(2, 'h');
$speed = $distance->div($time);  // 50 km/h (Velocity object)

// Wavelength from frequency (λ = c/f)
$c = PhysicalConstant::speedOfLight();
$f = new Frequency(5.4545e14, 'Hz');
$wavelength = $c->div($f);  // ~550 nm (Length object)

// Entropy = Energy / Temperature
$energy = new Energy(500, 'J');
$temp = new Temperature(300, 'K');
// No registered subclass, returns a base Quantity object.
$entropy = $energy->div($temp);  // ~1.667 J/K
```

---

## See Also

- **[Quantity](../Reference/Quantity.md#arithmetic-methods)** — Full method signatures and parameter details.
- **[Calculation Examples](CalculationExamples.md)** — Real-world physics and engineering calculations.
- **[Unit Conversion](UnitConversion.md)** — Converting quantities between units.
- **[PhysicalConstant](../Reference/PhysicalConstant.md)** — Built-in physical constants for scientific calculations.
