# QuantityPartsService

Service for decomposing quantities into parts and reassembling them.

**Namespace:** `Galaxon\Quantities\Services`

---

## Overview

The `QuantityPartsService` class maintains a configurable registry of default part unit symbols and result unit symbols for each quantity type. Built-in defaults are provided for length, time, angle, and mass.

To decompose, reassemble, parse, or format quantities as parts, use the corresponding methods on `Quantity` (`fromParts`, `toParts`, `parseParts`, `formatParts`) — see the [Quantity reference](../Quantity.md#parts-methods). Those methods read the configuration set up via this service.

### Built-in Defaults

| Quantity Type | Part Unit Symbols                     | Result Unit Symbol |
|---------------|---------------------------------------|--------------------|
| length        | `mi`, `yd`, `ft`, `in`               | `ft`               |
| time          | `y`, `mo`, `w`, `d`, `h`, `min`, `s` | `s`                |
| angle         | `deg`, `arcmin`, `arcsec`             | `deg`              |
| mass          | *(none)*                              | `lb`               |

All methods are static.

---

## Configuration Methods

### reset()

```php
public static function reset(): void
```

Reset the parts configurations to their defaults. Primarily intended for test isolation.

### getPartUnitSymbols()

```php
public static function getPartUnitSymbols(?QuantityType $quantityType): ?array
```

Get the default part unit symbols for a quantity type.

**Returns:** `?list<string>` — The part unit symbols, or `null` if none configured.

**Throws:** [`NullArgumentException`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Exceptions/NullArgumentException.md) if the quantity type is null.

### setPartUnitSymbols()

```php
public static function setPartUnitSymbols(
    ?QuantityType $quantityType,
    ?array $partUnitSymbols
): void
```

Set the default part unit symbols for a quantity type. Pass `null` to clear. Duplicates are removed and values are re-indexed.

**Throws:**
- [`NullArgumentException`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Exceptions/NullArgumentException.md) if the quantity type is null.
- `LengthException` if the array is empty.
- `InvalidArgumentException` if the array contains non-string values.
- [`FormatException`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Exceptions/FormatException.md) if any of the symbols are invalid.

### getResultUnitSymbol()

```php
public static function getResultUnitSymbol(?QuantityType $quantityType): ?string
```

Get the default result unit symbol for a quantity type.

**Returns:** `?string` — The result unit symbol, or `null` if none configured.

**Throws:** [`NullArgumentException`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Exceptions/NullArgumentException.md) if the quantity type is null.

### setResultUnitSymbol()

```php
public static function setResultUnitSymbol(
    ?QuantityType $quantityType,
    ?string $resultUnitSymbol
): void
```

Set the default result unit symbol for a quantity type. Pass `null` to clear.

**Throws:**
- [`NullArgumentException`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Exceptions/NullArgumentException.md) if the quantity type is null.
- `DomainException` if the value is an empty string.

---

## See Also

- **[Quantity](../Quantity.md)** — Core quantity value type
- **[UnitService](UnitService.md)** — Unit lookup and registration
