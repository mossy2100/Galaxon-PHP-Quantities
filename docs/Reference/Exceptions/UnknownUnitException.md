# UnknownUnitException

Exception thrown when a unit symbol cannot be resolved to a known unit.

**Namespace:** `Galaxon\Quantities\Exceptions`
**Extends:** `DomainException`

---

## Overview

`UnknownUnitException` is thrown when a string is provided as a unit symbol but no matching unit is found in the unit registry. This may indicate a typo, an unsupported unit, or that the required system of units has not been loaded via `UnitService::loadSystem()`.

Because it extends `DomainException`, existing code that catches `DomainException` will continue to work. The specific exception type allows callers to distinguish unknown-unit errors from other domain errors.

---

## Constructor

```php
public function __construct(
    public readonly string $unit,
    string $message = '',
    int $code = 0,
    ?Throwable $previous = null,
)
```

**Parameters:**
- `$unit` (string) — The unrecognised unit symbol.
- `$message` (string) — Optional custom message. If empty, a default message is generated: `"Unknown unit: '{$unit}'."`.
- `$code` (int) — The exception code.
- `$previous` (?Throwable) — The previous throwable for exception chaining.

---

## Properties

### unit

```php
public readonly string $unit
```

The unit symbol that could not be resolved.

---

## Where It Is Thrown

| Class | Method | Condition |
|-------|--------|-----------|
| `Unit` | `parse()` | Symbol not found in the unit registry. |
| `UnitTerm` | `__construct()` | Unit symbol string not found in the unit registry. |
| `UnitTerm` | `parse()` | Prefixed symbol not found in the unit registry. |
| `QuantityPartsService` | `validatePartUnitSymbols()` | Part unit symbol not found in the unit registry. |

---

## Examples

```php
use Galaxon\Quantities\Exceptions\UnknownUnitException;
use Galaxon\Quantities\QuantityType\Length;

try {
    $length = new Length(5, 'xyz');
} catch (UnknownUnitException $e) {
    echo $e->unit;     // 'xyz'
    echo $e->getMessage(); // "Unknown unit: 'xyz'."
}
```

---

## See Also

- **[DimensionMismatchException](DimensionMismatchException.md)** — Related exception for dimension mismatches.
- **[Unit](../Internal/Unit.md)** — Unit class with `parse()` method.
- **[UnitTerm](../Internal/UnitTerm.md)** — UnitTerm class with `parse()` method.
- **[UnitService](../Services/UnitService.md)** — Unit registry and system loading.
