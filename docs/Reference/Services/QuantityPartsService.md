# QuantityPartsService

Service for decomposing quantities into parts and reassembling them.

**Namespace:** `Galaxon\Quantities\Services`

---

## Overview

The `QuantityPartsService` class handles operations like converting `1.5 hours` into `1h 30min 0s` and vice versa. It decomposes a quantity into a set of named unit parts (from largest to smallest), and can reassemble parts back into a single quantity. It also provides parsing and formatting of multi-unit strings such as `"4y 5mo 6d 12h 34min 56.789s"` and `"12deg 34arcmin 56.789arcsec"`.

The class maintains a configurable registry of default part unit symbols and result unit symbols for each quantity type. Built-in defaults are provided for length, time, angle, and mass.

All methods are accessed indirectly via `Quantity` delegation methods.

### Built-in Defaults

| Quantity Type | Part Unit Symbols                     | Result Unit Symbol |
|---------------|---------------------------------------|--------------------|
| length        | `mi`, `yd`, `ft`, `in`               | `ft`               |
| time          | `y`, `mo`, `w`, `d`, `h`, `min`, `s` | `s`                |
| angle         | `deg`, `arcmin`, `arcsec`             | `deg`              |
| mass          | *(none)*                              | `lb`               |

### Key Features

- Decompose a quantity into parts with configurable unit sets.
- Reassemble parts into a single quantity.
- Parse multi-unit strings into quantities.
- Format quantities as multi-unit strings with configurable precision and zero display.
- Configurable defaults per quantity type, with reset capability for test isolation.
- All methods are static.

---

## Configuration Methods

### reset()

```php
public static function reset(): void
```

Reset the parts configurations to their defaults. Primarily intended for test isolation.

---

### getPartUnitSymbols()

```php
public static function getPartUnitSymbols(?QuantityType $quantityType): ?array
```

Get the default part unit symbols for a quantity type.

**Parameters:**
- `$quantityType` (?QuantityType) - The quantity type.

**Returns:**
- `?list<string>` - The part unit symbols, or `null` if none configured.

**Throws:**
- `DomainException` - If the quantity type is unregistered.

---

### setPartUnitSymbols()

```php
public static function setPartUnitSymbols(
    ?QuantityType $quantityType,
    ?array $partUnitSymbols
): void
```

Set the default part unit symbols for a quantity type. Duplicates are removed and values are re-indexed.

**Parameters:**
- `$quantityType` (?QuantityType) - The quantity type.
- `$partUnitSymbols` (`?list<string>`) - The part unit symbols, or `null` to clear.

**Throws:**
- `DomainException` - If the quantity type is unregistered or the array is empty.
- `InvalidArgumentException` - If the array contains non-string values.

---

### getResultUnitSymbol()

```php
public static function getResultUnitSymbol(?QuantityType $quantityType): ?string
```

Get the default result unit symbol for a quantity type.

**Parameters:**
- `$quantityType` (?QuantityType) - The quantity type.

**Returns:**
- `?string` - The result unit symbol, or `null` if none configured.

**Throws:**
- `DomainException` - If the quantity type is unregistered.

---

### setResultUnitSymbol()

```php
public static function setResultUnitSymbol(
    ?QuantityType $quantityType,
    ?string $resultUnitSymbol
): void
```

Set the default result unit symbol for a quantity type.

**Parameters:**
- `$quantityType` (?QuantityType) - The quantity type.
- `$resultUnitSymbol` (`?string`) - The result unit symbol, or `null` to clear.

**Throws:**
- `DomainException` - If the quantity type is unregistered or the value is an empty string.

---

## Part Operations

### fromParts()

```php
public static function fromParts(
    ?QuantityType $quantityType,
    array $parts
): Quantity
```

Create a new `Quantity` as the sum of measurements in different units. The result is expressed in the configured result unit for the given quantity type.

The `$parts` array is keyed by unit symbol with numeric values. An optional `'sign'` key may be included with a value of `1` (non-negative) or `-1` (negative). If omitted, the sign defaults to `1`.

**Parameters:**
- `$quantityType` (?QuantityType) - The quantity type.
- `$parts` (`array<string, int|float>`) - The parts, keyed by unit symbol.

**Returns:**
- `Quantity` - A new Quantity representing the sum of the parts.

**Throws:**
- `DomainException` - If the quantity type is unregistered, the result unit symbol or sign is invalid, or no result unit is configured.
- `InvalidArgumentException` - If any unit symbols are not strings, or any values are not numbers.

---

### toParts()

```php
public static function toParts(
    Quantity $quantity,
    ?int $precision = null
): array
```

Decompose a quantity into parts, from the largest to the smallest configured unit. All part values are integers except for the smallest unit, which may have a fractional component. A `'sign'` key is included with a value of `1` (positive or zero) or `-1` (negative).

If `$precision` is provided, the smallest unit value is rounded to that many decimal places. Rounding up is propagated correctly through larger units when necessary.

**Parameters:**
- `$quantity` (Quantity) - The quantity to decompose.
- `$precision` (`?int`) - The number of decimal places for the smallest unit, or `null` for no rounding.

**Returns:**
- `array<string, int|float>` - Array of parts keyed by unit symbol, plus a `'sign'` key.

**Throws:**
- `DomainException` - If the quantity type is unregistered, part unit symbols are not configured, or precision is negative.
- `InvalidArgumentException` - If any of the unit symbols are not strings.

---

### parseParts()

```php
public static function parseParts(
    ?QuantityType $quantityType,
    string $input
): Quantity
```

Parse a multi-unit string into a `Quantity`. Each part in the string must have no space between the value and unit symbol. Parts are separated by whitespace. Only the first part may be negative.

**Parameters:**
- `$quantityType` (?QuantityType) - The quantity type.
- `$input` (string) - The string to parse (e.g. `"4y 5mo 6d 12h 34min 56.789s"`).

**Returns:**
- `Quantity` - A new Quantity representing the sum of the parsed parts.

**Throws:**
- `DomainException` - If the quantity type is unregistered or no result unit is configured.
- `FormatException` - If the input string is empty or invalid.
- `UnexpectedValueException` - If there is an unexpected error during parsing.
- `InvalidArgumentException` - If any of the unit symbols are not strings.

---

### formatParts()

```php
public static function formatParts(
    Quantity $quantity,
    ?int $precision = null,
    bool $showZeros = false,
    bool $ascii = false
): string
```

Format a quantity as a multi-unit parts string. Only the smallest unit may include a decimal point; larger units are integers.

If `$showZeros` is `false`, parts with zero values are omitted. However, if the entire quantity is zero, the smallest unit is always shown (e.g. `"0s"`, `"0ft"`).

**Parameters:**
- `$quantity` (Quantity) - The quantity to format.
- `$precision` (`?int`) - The number of decimal places for the smallest unit, or `null` for no rounding.
- `$showZeros` (bool) - If `true`, include all parts even when zero. Default: `false`.
- `$ascii` (bool) - If `true`, use ASCII characters only. Default: `false`.

**Returns:**
- `string` - The formatted string (e.g. `"1h 30min 0s"`, `"12deg 34arcmin 56.789arcsec"`).

**Throws:**
- `DomainException` - If the quantity type is unregistered, part unit symbols are not configured, or precision is negative.
- `InvalidArgumentException` - If any of the unit symbols are not strings.

---

## See Also

- **[Quantity](../Quantity.md)** - Core quantity value type
- **[RegexService](RegexService.md)** - Regex patterns and validation
- **[UnitService](UnitService.md)** - Unit lookup and registration
