# DimensionMismatchException

Exception thrown when an operation requires matching dimensions but receives different ones.

**Namespace:** `Galaxon\Quantities\Exceptions`
**Extends:** `DomainException`

---

## Overview

`DimensionMismatchException` is thrown when attempting to convert, compare, or combine quantities whose dimensions are incompatible — for example, comparing a length with a mass, or creating a conversion between meters and seconds.

The exception message automatically includes quantity type names where available, making it easy to diagnose the issue. For example: `"Dimension mismatch: 'L' (length) and 'T' (time)."`.

Because it extends `DomainException`, existing code that catches `DomainException` will continue to work.

---

## Constructor

```php
public function __construct(
    public readonly ?string $dimension1,
    public readonly ?string $dimension2,
    string $message = '',
    int $code = 0,
    ?Throwable $previous = null,
)
```

**Parameters:**
- `$dimension1` (?string) — The first dimension code (e.g. `'L'`, `'M'`, `'LT-2'`), or null if unknown.
- `$dimension2` (?string) — The second dimension code, or null if unknown.
- `$message` (string) — Optional custom message. If empty, a default message is generated using `QuantityTypeService::getByDimension()` to resolve human-readable names. Null dimensions are rendered as `null` in the message.
- `$code` (int) — The exception code.
- `$previous` (?Throwable) — The previous throwable for exception chaining.

---

## Properties

### dimension1

```php
public readonly ?string $dimension1
```

The first dimension code, or null if unknown.

### dimension2

```php
public readonly ?string $dimension2
```

The second dimension code, or null if unknown.

---

## Where it is thrown

| Class | Method | Condition |
|-------|--------|-----------|
| `Quantity` | `preCompare()` | Comparing quantities with different dimensions. |
| `Conversion` | `__construct()` | Source and destination units have different dimensions. |
| `ConversionService` | `validateUnits()` | Units passed to conversion methods have different dimensions. |
| `Converter` | `validateUnit()` | Unit has the wrong dimension for this converter. |
| `Temperature` | `convert()` | A unit is not a temperature unit (dimension `'H'`). |

---

## Examples

```php
use Galaxon\Quantities\Exceptions\DimensionMismatchException;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;

try {
    $length = new Length(100, 'm');
    $mass = new Mass(50, 'kg');
    $length->add($mass);
} catch (DimensionMismatchException $e) {
    echo $e->dimension1; // 'L'
    echo $e->dimension2; // 'M'
    echo $e->getMessage(); // "Dimension mismatch: 'L' (length) and 'M' (mass)."
}
```

---

## See also

- **[UnknownUnitException](UnknownUnitException.md)** — Related exception for unknown unit symbols.
- **[Quantity](../Quantity.md)** — Base class where dimension checks occur during comparison and arithmetic.
- **[Conversion](../Internal/Conversion.md)** — Conversion class that validates dimension compatibility.
- **[Converter](../Internal/Converter.md)** — Converter class that validates units against its dimension.
