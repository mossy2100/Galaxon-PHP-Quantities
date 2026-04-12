# Creating Quantities

You can create quantities using either the base `Quantity` class or a dedicated subclass. Both approaches give you full access to arithmetic, conversion, comparison, and formatting.

There are three ways to create new Quantity objects:
1. `new QuantitySubclass()`
2. `Quantity::create()`
3. `Quantity::parse()` or `QuantitySubclass::parse()`

Both constructors and `create()` accept a unit string — see [Unit Syntax](StringFunctions.md#unit-syntax) for the rules on writing unit expressions.

---

## Using `new QuantitySubclass()`

Use this method when a class exists for the relevant quantity type.

Dedicated subclasses exist for quantity types that have their own units, add extra features, or are commonly used. Using them gives you type-hinting, `instanceof` checks, and IDE autocompletion:

```php
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Angle;

$distance = new Length(42.195, 'km');
$heading = new Angle(127.5, 'deg');

// Angle adds trigonometry methods.
$sine = $heading->sin();
```

This is the recommended method, but it's not a requirement. Your code may involve quantity types the package does not provide classes for, and it may not be necessary or worthwhile to create new classes just for this purpose.

In theory, `Quantity::create()` (below) could be used in all cases, but `new QuantitySubclass()` makes for more readable code that is also more comprehensible by static analysis tools such as phpstan.

---

## Using `Quantity::create()`

This factory method works with any valid unit expression, making it ideal for ad-hoc or uncommon measurements.

```php
use Galaxon\Quantities\Quantity;

$entropy = Quantity::create(37.5, 'J/K');
$torque = Quantity::create(120, 'N*m');
$viscosity = Quantity::create(1.002e-3, 'Pa*s');
```

The package does not provide dedicated `Quantity` subclasses for many quantity types. However, quantities of these types can be created by calling `Quantity::create()` with compound unit expressions (e.g. `'Pa*s'`, `'J/K'`, `'J/(kg*K)'`).

The `Quantity::create()` factory method infers the dimension from the unit and checks the `QuantityTypeService` for an appropriate registered subclass. For example, `Quantity::create(10, 'm')` returns a `Length` object. If no dedicated subclass is registered for the quantity type, then the result will be a `Quantity` object.

If you need a dedicated class for a quantity type, see [Customization](Customization.md).

---

## Using `parse()`

Use `parse()` to create a quantity from a string containing both a value and a unit:

```php
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Time;

// Parse with a subclass for type safety
$distance = Length::parse('42.195 km');
$wavelength = Length::parse('5.5e-7 m');

// Parse with the base class (infers the quantity type)
$force = Quantity::parse('9.8 kg*m/s2');

// Multi-part strings are supported for Angle and Time
$duration = Time::parse('1h 30min 45s');
```

When called on a subclass, `parse()` validates that the parsed unit matches the expected dimension. For full details on parsing, including unit syntax, multi-part strings and formatting output, see [String Functions](StringFunctions.md).

---

## Questions
### Why subclasses are provided for only some quantity types

There are a great many quantity types used by science, engineering, finance, and other aspects of human civilization, and surely new ones are being invented all the time. Refer to this [list of physical quantities on Wikipedia](https://en.wikipedia.org/wiki/List_of_physical_quantities).

So, it isn't practical to include classes for all of them. However, some are commonly used in application code. By providing dedicated classes for quantity types such as `Angle`, `Time` and `Money`, special features applicable only to those quantity types can be contained within these subclasses.

Thus, subclasses are provided when at least one of the following applies:

- **Specific units** — The quantity type has dedicated units (e.g. `Force` has `N` and `lbf`, `Pressure` has `Pa` and `atm`, etc.).
- **Added features** — The class offers methods beyond the base `Quantity` API. For example, `Angle` provides trigonometric methods, `Time` provides interoperability with `DateInterval`, etc.
- **Common use** — The quantity type appears frequently in everyday calculations, so a dedicated class improves convenience even without custom units (e.g. `Acceleration`, `Density`). These could have been omitted, but they serve as useful examples of how to add your own custom quantity type classes.

### Why `new Quantity()` is disallowed

It isn't permitted to call `new Quantity()` directly. This limitation is included to reduce code fragility, in case a dedicated class is added for a quantity type later, which should have been instantiated instead.

For example, if `new Quantity(42, 'J/K')` was used to create an entropy quantity, and an `Entropy` class is added to the application later, that call to `new Quantity()` would return a `Quantity` object rather than an `Entropy` object, since constructors can only return an instance of the class being instantiated, not a derived class or any other class. The `Quantity::create()` factory method addresses this problem. Just make sure, if you add a dedicated class for a quantity type, you register it via `QuantityTypeService::add()`.

---

## See also

- **[Quantity](../Reference/Quantity.md)** — Full API reference for the base Quantity class.
- **[Units](../Concepts/Units.md)** — Complete list of built-in units by quantity type.
- **[Customization](Customization.md)** — Adding custom units, conversions, and quantity type classes.
- **[String Functions](StringFunctions.md)** — Parsing quantity strings with `parse()`.
