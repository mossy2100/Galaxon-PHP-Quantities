# Creating Quantities

You can create quantities using either the base `Quantity` class or a dedicated subclass. Both approaches give you full access to arithmetic, conversion, comparison, and formatting.

There are two ways to create new Quantity objects:
1. `new Something()`
2. `Quantity::create()`

---

## `new Something()`

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

This is the recommended method, but it's not a requirement. Your code may involve quantity types the package does not provide a classes, and it may not be simple or worthwhile to create new classes just for this purpose.

### Why subclasses are provided

Subclasses are provided when at least one of the following applies:

- **Specific units** — The quantity type has its own units (e.g. `Force` has `N` and `lbf`, `Pressure` has `Pa` and `atm`, etc.).
- **Added features** — The class offers methods beyond the base `Quantity` API. For example, `Angle` provides trigonometric methods, `Time` provides interoperability with `DateInterval`, etc.
- **Common use** — The quantity type is frequently used in calculations, so a dedicated class improves convenience even without custom units (e.g. `Acceleration`, `Density`).

---

## Using `Quantity::create()`

This factory method works with any valid unit expression, making it ideal for ad-hoc or uncommon measurements:

```php
use Galaxon\Quantities\Quantity;

$entropy = new Quantity(37.5, 'J/K');
$torque = new Quantity(120, 'N*m');
$viscosity = Quantity::create(1.002e-3, 'Pa*s');
```


The `Quantity::create()` factory method infers the quantity type from the unit and checks the `QuantityTypeService` for an appropriate registered subclass. For example, `Quantity::create(10, 'm')` returns a `Length` object. If no dedicated subclass is registered for the quantity type, then the result will be a `Quantity` object.

Note, however, it isn't permitted to call `new Quantity()` directly. This limitation is included to reduce code fragility, in case a dedicated class is added for a quantity type later.

---

### Other Derived Units

Most [SI derived units defined by field of application](https://en.wikipedia.org/wiki/SI_derived_unit#By_field_of_application) — such as viscosity, entropy, specific heat capacity, and surface tension — do not have dedicated classes. They can be created directly using the base `Quantity` class with compound unit expressions (e.g. `'Pa*s'`, `'J/K'`, `'J/(kg*K)'`).

If you need a dedicated class for one of these, see [Adding Quantity Types](AddingQuantityTypes.md).
