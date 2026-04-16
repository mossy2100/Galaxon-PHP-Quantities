# UnitInterface

Interface for unit representations in the Quantities package.

---

## Overview

`UnitInterface` defines the contract that all unit representations must follow, including simple units (`Unit`), prefixed units with exponents (`UnitTerm`), and compound units (`CompoundUnit`). This interface ensures consistent behavior for parsing, formatting, and accessing unit symbols across all unit types.

Extends `Stringable`, so all implementing classes can be cast to string.

The interface defines:
- `$asciiSymbol` — ASCII representation of the unit symbol.
- `$unicodeSymbol` — Unicode representation with proper superscripts and non-letters.
- `$dimension` — The dimensional code (e.g., `'L'` for length, `'M'` for mass).
- `parse()` — Static method to create an instance from a string.
- `format()` — Instance method to format the unit as a string.

---

## Properties

### asciiSymbol

```php
public string $asciiSymbol { get; }
```

The ASCII representation of the unit symbol. Uses only ASCII characters, with exponents shown as digits (e.g., `'m2'` for square meters, `'kg*m/s2'` for newtons).

### unicodeSymbol

```php
public string $unicodeSymbol { get; }
```

The Unicode representation of the unit symbol. May include non-ASCII characters like superscript digits (e.g., `'m²'`), Greek letters (e.g., `'Ω'` for ohm), and the middle dot operator (e.g., `'kg⋅m/s²'`).

### dimension

```php
public string $dimension { get; }
```

The dimensional code representing the physical dimension of the unit. Uses standard dimension symbols: L (length), M (mass), T (time), I (electric current), N (amount of substance), J (luminous intensity), and some non-standard ones: H (temperature), A (angle), D (data), and C (currency).

Compound dimensions include exponents (e.g., `'L2'` for area, `'MLT-2'` for force). Dimensionless units have an empty string `''`.

---

## Factory methods

### parse()

```php
public static function parse(string $symbol): self
```

Parse a string representation of a unit into an instance.

**Parameters:**
- `$symbol` (string) - The unit symbol to parse (e.g., `'m'`, `'km2'`, `'kg*m/s2'`).

**Returns:** `UnitInterface` - A new instance of the implementing class.

**Throws:**
- [`FormatException`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Exceptions/FormatException.md) - If the symbol format is invalid.
- [`UnknownUnitException`](../Exceptions/UnknownUnitException.md) - If the symbol contains unknown units.

---

## Conversion methods

### format()

```php
public function format(bool $ascii = false): string
```

Format the unit as a string.

**Parameters:**
- `$ascii` (bool) - If `true`, return ASCII format; if `false` (default), return Unicode format.

**Returns:** `string` - The formatted unit symbol.

**Contract:**
- When `$ascii` is `false`, returns the Unicode representation with superscripts.
- When `$ascii` is `true`, returns the ASCII representation with digit exponents.

*`__toString()` is inherited from `Stringable` and delegates to `format()`.*

---

## Implementing classes

- **[Unit](Unit.md)** — Represents a single unit of measurement.
- **[UnitTerm](UnitTerm.md)** — Represents a unit with optional prefix and exponent.
- **[CompoundUnit](CompoundUnit.md)** — Represents a compound unit with zero or more unit terms.
