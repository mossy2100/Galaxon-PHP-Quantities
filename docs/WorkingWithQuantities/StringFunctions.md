# String Functions

---

## Parsing strings

Use `parse()` to create a Quantity from a string. Whitespace between the value and unit is allowed:

```php
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Angle;
use Galaxon\Quantities\QuantityType\Time;
use Galaxon\Quantities\QuantityType\Mass;

// Simple quantities
$length = Length::parse('42.195 km');   // Length(42.195, 'km')
$mass = Mass::parse('70kg');            // Mass(70, 'kg')

// Scientific notation
$wavelength = Length::parse('5.5e-7 m');  // Length(5.5e-7, 'm')

// Angles — single value or parts notation
$angle = Angle::parse('45.5 deg');                // Angle(45.5, 'deg')
$angle = Angle::parse('45deg 30arcmin 0arcsec');  // Angle(45.5, 'deg')

// Time — single value or parts notation
$time = Time::parse('3661 s');              // Time(3661, 's')
$time = Time::parse('1h 1min 1s');          // Time(3661, 's')
$time = Time::parse('-1h 30min 45s');       // Time(-5445, 's')
```

When `parse()` encounters a multi-part string (e.g. `"1h 30min 45s"`), it automatically delegates to `parseParts()`, which reconstructs the quantity from its component parts. In this case, spaces between values and units are *not* allowed, because spaces are used to separate parts.

---

## Unit Syntax

When writing unit strings — whether for `Quantity::create()`, `parse()`, or any method that
accepts a unit — the following syntax rules apply.

### Simple Units

A simple unit is a prefix (optional) followed by a unit symbol, e.g. `km`, `mg`, `Hz`, `degC`.

### Derived Units

Derived units combine multiple unit terms using multiplication and division operators.

**Multiplication operators** — the parser accepts four characters:

| Character | Name | Unicode |
|-----------|------|---------|
| `*` | Asterisk | U+002A |
| `.` | Period | U+002E |
| `⋅` | Dot operator | U+22C5 |
| `·` | Middle dot | U+00B7 |

When formatting output, `*` is used in ASCII mode and `⋅` (dot operator) in Unicode mode. On macOS, the middle dot `·` can be typed with **Option+Shift+9**, but as a general rule it's expected that `*` will be used in code.

**Division operator** — only the forward slash `/` is supported.

Examples:

```php
// These are all equivalent
$force = Quantity::create(10, 'kg*m/s2');
$force = Quantity::create(10, 'kg.m/s2');
$force = Quantity::create(10, 'kg⋅m/s2');
$force = Quantity::create(10, 'kg·m/s2');
```

### Exponents

Exponents are written directly after the unit symbol — do not use an exponentiation operator
such as `^` or `**`. Valid exponents are in the range -9..9, excluding 0. The parser accepts both plain digits and Unicode superscript characters:

```php
// Plain digits
$energy = Quantity::create(100, 'kg*m2/s2');
$accel = Quantity::create(9.8, 'm/s2');
$inv = Quantity::create(5000, 's-1');

// Unicode superscripts (also accepted)
$energy = Quantity::create(100, 'kg⋅m²/s²');
$inv = Quantity::create(5000, 's⁻¹');
```

### Bracket Form

When the denominator contains multiple unit terms, it's acceptable to use parentheses to group them:

```php
// Without brackets — s2 and A are separate denominator terms
$quantity = Quantity::create(1, 'kg*m2/s2/A');

// With brackets — equivalent, but clearer
$quantity = Quantity::create(1, 'kg*m2/(s2*A)');

// Useful for readability
$heatCapacity = Quantity::create(1, 'J/(mol*K)');
```

Parentheses are only permitted around the denominator, in the form `<numerator>/(<denominator>)`.

---

## Formatting output

#### Default Formatting with `echo`

Using a Quantity in a string context calls `__toString()`, which uses default formatting:

```php
$length = new Length(1500, 'm');
echo $length;  // 1500 m

$angle = new Angle(45.5, 'deg');
echo $angle;  // 45.5°

$resistance = Quantity::create(4700, 'ohm');
echo $resistance;  // 4700 Ω
```

#### The `format()` Method

For more control, use `format()` with a specifier, precision, and ASCII mode:

```php
$length = new Length(1234.5678, 'm');

// Significant figures (default)
echo $length->format();           // 1234.57 m
echo $length->format('g', 4);    // 1235 m

// Fixed-point notation
echo $length->format('f');        // 1234.5678 m
echo $length->format('f', 2);    // 1234.57 m

// Scientific notation
echo $length->format('e', 3);    // 1.235×10³ m
```

**Specifiers:**

| Specifier             | Description | Precision means            |
| --------------------- | ----------- | -------------------------- |
| `f` / `F`             | Fixed-point | Decimal places             |
| `e` / `E`             | Scientific  | Decimal places in mantissa |
| `g` / `G` / `h` / `H` | Shortest    | Significant figures        |

When `$precision` is `null` (the default), trailing zeros are automatically trimmed. When an explicit precision is given, digits are preserved. This behavior can be overridden with the `$trimZeros` parameter (`true` = always trim, `false` = never trim, `null` = auto).

See: [formatValue()](Quantity.md#formatValue())

### ASCII vs. Unicode

By default, quantities are formatted with Unicode characters. Pass `ascii: true` for plain ASCII output. The differences appear in four areas:

**1. Prefix symbols** — e.g. micro:

```php
$capacitance = Quantity::create(4.7, 'uF');
echo $capacitance->format();               // 4.7 μF
echo $capacitance->format(ascii: true);    // 4.7 uF
```

**2. Unit symbols** — e.g. ohm, degree:

```php
$resistance = Quantity::create(100, 'ohm');
echo $resistance->format();               // 100 Ω
echo $resistance->format(ascii: true);    // 100 ohm

$angle = new Angle(90, 'deg');
echo $angle->format();               // 90°
echo $angle->format(ascii: true);    // 90 deg
```

**3. Derived unit notation** — multiplication dot vs. asterisk:

```php
$energy = Quantity::create(100, 'kg*m2/s2');
echo $energy->format();               // 100 kg·m²/s²
echo $energy->format(ascii: true);    // 100 kg*m2/s2
```

**4. Scientific notation** — Unicode superscripts vs. `e` notation:

```php
$distance = new Length(1.496e11, 'm');
echo $distance->format('e', 3);               // 1.496×10¹¹ m
echo $distance->format('e', 3, ascii: true);  // 1.496e+11 m
```

### Space between value and unit

By default, `format()` automatically determines whether to place a space between the value and unit. Single non-letter symbols like `°`, `%`, or `″` have no space; all other units get a space:

```php
$angle = new Angle(45, 'deg');
echo $angle;  // 45°  (no space)

$length = new Length(100, 'm');
echo $length;  // 100 m  (space)
```

You can override this with the `$includeSpace` parameter:

```php
echo $angle->format(includeSpace: true);   // 45 °
echo $length->format(includeSpace: false); // 100m
```

---

## See Also

- **[Quantity](../Reference/Quantity.md)** — Full reference for `format()`, `formatValue()`, `parse()`, and `__toString()`.
- **[Part Decomposition](PartDecomposition.md)** — Multi-part formatting and parsing (e.g. `"5h 30min 45s"`).
- **[Money](../Reference/QuantityType/Money.md)** — Currency-specific formatting with locale support.
