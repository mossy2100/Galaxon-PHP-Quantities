# Creating Quantities

You can create quantities using either the base `Quantity` class or a dedicated subclass. Both approaches give you full access to arithmetic, conversion, comparison, and formatting.

There are two ways to create new Quantity objects:
1. `new QuantitySubclass()`
2. `Quantity::create()`

---

## `new QuantitySubclass()`

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

The package does not provide dedicated `Quantity` subclasses for many quantity types. However, quantities of these types can be created by calling `Quantity::create()` with derived unit expressions (e.g. `'Pa*s'`, `'J/K'`, `'J/(kg*K)'`).

The `Quantity::create()` factory method infers the dimension from the unit and checks the `QuantityTypeService` for an appropriate registered subclass. For example, `Quantity::create(10, 'm')` returns a `Length` object. If no dedicated subclass is registered for the quantity type, then the result will be a `Quantity` object.

If you need a dedicated class for a quantity type, see [Customization](Customization.md).

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

For example, if `new Quantity(42, 'J/K')` was used to create an entropy quantity, and an `Entropy` class is added to the application later, that call to `new Quantity()` would return a `Quantity` object rather than an `Entropy` object. Constructors can only return an instance of the class being instantiated; not a derived class or any other class. The `Quantity::create()` factory method addresses this problem.

---

## See Also

- **[Quantity](../Reference/Quantity.md)** — Full API reference for the base Quantity class.
- **[Units](../Concepts/Units.md)** — Complete list of built-in units by quantity type.
- **[Customization](Customization.md)** — Adding custom units, conversions, and quantity type classes.
- **[String Functions](StringFunctions.md)** — Parsing quantity strings with `parse()`.
