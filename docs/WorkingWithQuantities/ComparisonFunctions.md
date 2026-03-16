# Comparison Functions

Quantity objects implement the [`Comparable`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Traits/Comparable.md) and [`ApproxComparable`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Traits/ApproxComparable.md) traits from the Core package, providing both exact and approximate comparison methods.

---

## Exact Comparison

The `compare()` method returns `-1`, `0`, or `1`, like PHP's spaceship operator. Units are automatically converted before comparing:

```php
$a = new Length(1000, 'm');
$b = new Length(1, 'km');

$a->compare($b);           // 0 (equal)
$a->equal($b);             // true
$a->lessThan($b);          // false
$a->greaterThan($b);       // false
$a->lessThanOrEqual($b);   // true
$a->greaterThanOrEqual($b);  // true
```

Note that `compare()` and `equal()` test for *exact* floating-point equality. Due to the nature of floating-point arithmetic, converted values may not be exactly equal even when they represent the same physical quantity.

---

## Approximate Comparison

The `approxEqual()` method handles floating-point precision by allowing a small tolerance:

```php
$a = new Length(1000, 'm');
$b = new Length(1, 'km');
$a->approxEqual($b);  // true

// Custom tolerances
$a->approxEqual($b, relTol: 1e-9, absTol: 1e-12);

// Works across unit systems
$angle1 = new Angle(180, 'deg');
$angle2 = new Angle(M_PI, 'rad');
$angle1->approxEqual($angle2);  // true
```

The `approxCompare()` method combines approximate equality with ordering — it returns `0` when two values are within tolerance, and `-1` or `1` otherwise:

```php
$a = new Mass(1, 'kg');
$b = new Mass(1000, 'g');
$a->approxCompare($b);  // 0
```

---

## Incompatible Quantities

Comparing quantities with different dimensions throws an exception with `compare()`, and returns `false` with `approxEqual()`:

```php
$length = new Length(100, 'm');
$time = new Time(100, 's');

$length->compare($time);      // throws DimensionMismatchException
$length->approxEqual($time);  // false (no exception)
```

---

## See Also

- **[Comparable](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Traits/Comparable.md)** — Core trait providing `compare()`, `equal()`, `lessThan()`, `greaterThan()`, and related methods.
- **[ApproxComparable](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Traits/ApproxComparable.md)** — Core trait providing `approxEqual()` and `approxCompare()` with tolerance support.
- **[Quantity](../Reference/Quantity.md)** — Full reference for comparison method signatures and exceptions.
