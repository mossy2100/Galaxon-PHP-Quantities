
CLAUDE TODO:
1. This doesn't need to be overly long. Explain `equal`, `approxEqual`, `compare`, etc.
2. Explain the relationship to the `ApproxComparable` trait and link to the documentation for the main Traits page (https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Traits/Traits.md).



### Comparison and Approximate Equality

Compare measurements with exact or approximate equality:

```php
$a = new Length(1000, 'm');
$b = new Length(1, 'km');

// Exact comparison
$a->compare($b);        // 0 (equal)
$a->lessThan($b);       // false
$a->greaterThan($b);    // false

// Approximate comparison (handles floating-point precision)
$a->approxEqual($b);    // true

// Angles use radians for tolerance
$angle1 = new Angle(180, 'deg');
$angle2 = new Angle(M_PI, 'rad');
$angle1->approxEqual($angle2);  // true
```

