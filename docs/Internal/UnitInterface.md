# UnitInterface

Interface for unit representations in the Quantities package.

## Overview

`UnitInterface` defines the contract that all unit representations must follow, including simple units (`Unit`), prefixed units with exponents (`UnitTerm`), and compound units (`DerivedUnit`). This interface ensures consistent behavior for parsing, formatting, and accessing unit symbols across all unit types.

The interface defines:
- `$asciiSymbol` - ASCII representation of the unit symbol
- `$unicodeSymbol` - Unicode representation with proper superscripts and special characters
- `$dimension` - The dimensional code (e.g., 'L' for length, 'M' for mass)
- `parse()` - Static method to create an instance from a string
- `format()` - Instance method to format the unit as a string

## Properties

### asciiSymbol

```php
public string $asciiSymbol { get; }
```

The ASCII representation of the unit symbol. Uses only ASCII characters, with exponents shown as digits (e.g., 'm2' for square meters, 'kg*m/s2' for newtons).

This format is suitable for code, file names, and systems that don't support Unicode.

### unicodeSymbol

```php
public string $unicodeSymbol { get; }
```

The Unicode representation of the unit symbol. May include special characters like superscript digits (e.g., 'm²'), 
Greek letters (e.g., 'Ω' for ohm), and the middle dot operator (e.g., 'kg⋅m/s²').

This format is preferred for display to users.

### dimension

```php
public string $dimension { get; }
```

The dimensional code representing the physical dimension of the unit. Uses standard dimension symbols: L (length), 
M (mass), T (time), I (electric current), N (amount of substance), J (luminous intensity), and some non-standard ones:
H (temperature), A (angle), D (data), and C (money).

Compound dimensions include exponents (e.g., 'L2' for area, 'MLT-2' for force). Dimensionless units have dimension '1'.

## Methods

### parse()

```php
public static function parse(string $symbol): self
```

Parse a string representation of a unit into an instance.

**Parameters:**
- `$symbol` (string) - The unit symbol to parse (e.g., 'm', 'km2', 'kg*m/s2')

**Returns:**
- `self` - A new instance of the implementing class

**Contract:**
- Must accept both ASCII and Unicode symbol formats
- Must throw `FormatException` if the symbol format is invalid
- Must throw `DomainException` if the symbol represents an unknown unit

### format()

```php
public function format(bool $ascii = false): string
```

Format the unit as a string.

**Parameters:**
- `$ascii` (bool) - If true, return ASCII format; if false (default), return Unicode format

**Returns:**
- `string` - The formatted unit symbol

**Contract:**
- When `$ascii` is false, should return the Unicode representation with superscripts
- When `$ascii` is true, should return the ASCII representation with digit exponents
- The returned string should be parseable by `parse()`

## Implementing Classes

- `Galaxon\Quantities\Unit` - Represents a single unit of measurement
- `Galaxon\Quantities\UnitTerm` - Represents a unit with optional prefix and exponent
- `Galaxon\Quantities\DerivedUnit` - Represents a compound unit with 0 or more unit terms

## See Also

- **[Unit](Unit.md)** - Simple unit implementation
- **[UnitTerm](UnitTerm.md)** - Unit with prefix and exponent
- **[DerivedUnit](DerivedUnit.md)** - Compound unit implementation
