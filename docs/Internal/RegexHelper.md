# RegexHelper

Centralised regex patterns and validation for unit symbols.

## Overview

The `RegexHelper` class consolidates all regular expression constants, pattern builders, and validation methods used across the Quantities package. It is the single source of truth for how unit symbols, prefix symbols, derived unit expressions, quantity strings, and DMS angle strings are validated and matched.

The class composes patterns from small, private constants (e.g. `RX_ASCII_WORD`, `RX_UNICODE_LETTER`) into larger patterns via static builder methods. Validation methods wrap these patterns with anchors and return booleans, optionally populating match arrays for downstream parsing.

### Key Features

- Composable regex constants for ASCII and Unicode unit symbols.
- Builder methods for unit term, derived unit, and compound patterns.
- Validation methods for names, symbols, prefixes, derived units, quantities, and DMS angles.
- No state -- all methods and constants are static.

## Regex Builder Methods

### unitRegex()

```php
public static function unitRegex(): string
```

Get the regex pattern for matching a unit symbol (excluding dimensionless). Matches ASCII words or a Unicode symbol, wrapped in a capture group.

**Returns:**
- `string` - The regex pattern (without delimiters or anchors).

### unitTermRegex()

```php
public static function unitTermRegex(): string
```

Get the regex pattern for matching a unit term. Matches an optional prefix followed by a unit symbol, optionally followed by an exponent in either ASCII digits or Unicode superscript characters.

**Returns:**
- `string` - The regex pattern (without delimiters or anchors).

### derivedUnitRegexForm1()

```php
public static function derivedUnitRegexForm1(): string
```

Get the regex pattern for form 1 of a derived unit: one or more unit terms separated by multiply and/or divide operators (e.g. `kg*m/s2`, `m-1*kg*s-3`).

**Returns:**
- `string` - The regex pattern (without delimiters or anchors).

### derivedUnitRegexForm2()

```php
public static function derivedUnitRegexForm2(): string
```

Get the regex pattern for form 2 of a derived unit: numerator terms divided by parenthesised denominator terms (e.g. `W/(sr*m2)`).

**Returns:**
- `string` - The regex pattern (without delimiters or anchors).

### derivedUnitRegex()

```php
public static function derivedUnitRegex(): string
```

Get the regex pattern for matching a derived unit in either form. Combines `derivedUnitRegexForm1()` and `derivedUnitRegexForm2()` as alternatives.

**Returns:**
- `string` - The regex pattern (without delimiters or anchors).

## Validation Methods

### isValidUnitName()

```php
public static function isValidUnitName(string $name): bool
```

Check if a string is a valid unit name: non-empty ASCII, up to 3 words, upper and lower-case allowed (e.g. `"meter"`, `"US fluid ounce"`).

**Parameters:**
- `$name` (string) - The string to check.

**Returns:**
- `bool` - True if the string is a valid unit name.

### isValidAsciiSymbol()

```php
public static function isValidAsciiSymbol(string $symbol): bool
```

Check if a string is a valid ASCII unit symbol: up to 3 ASCII words or a single special character (e.g. `"m"`, `"fluid ounce"`, `"%"`).

**Parameters:**
- `$symbol` (string) - The string to check.

**Returns:**
- `bool` - True if the string is a valid ASCII unit symbol.

### isValidUnicodeSymbol()

```php
public static function isValidUnicodeSymbol(string $symbol): bool
```

Check if a string is a valid Unicode unit symbol: a Unicode letter, special character, or temperature symbol (e.g. `"Ω"`, `"°C"`, `"‰"`).

**Parameters:**
- `$symbol` (string) - The string to check.

**Returns:**
- `bool` - True if the string is a valid Unicode unit symbol.

### isValidAlternateSymbol()

```php
public static function isValidAlternateSymbol(string $symbol): bool
```

Check if a string is a single ASCII character valid for use as an alternate unit symbol. Letters and special characters are allowed; digits, brackets, and mathematical operators are disallowed.

**Parameters:**
- `$symbol` (string) - The string to check.

**Returns:**
- `bool` - True if the string is a valid alternate symbol.

### isValidUnitSymbol()

```php
public static function isValidUnitSymbol(string $symbol): bool
```

Check if a string is a valid unit symbol. Accepts ASCII symbols, Unicode symbols, and alternate symbols.

**Parameters:**
- `$symbol` (string) - The string to check.

**Returns:**
- `bool` - True if the string is a valid unit symbol.

### isValidUnicodeSpecialChar()

```php
public static function isValidUnicodeSpecialChar(string $symbol): bool
```

Check if a string is a single Unicode special character valid for use as a unit symbol. Excludes letters, digits, spaces, non-printable characters, mathematical operators, brackets, and most punctuation.

**Parameters:**
- `$symbol` (string) - The string to check.

**Returns:**
- `bool` - True if the string is a valid Unicode special character.

### isValidUnitTerm()

```php
public static function isValidUnitTerm(string $symbol, ?array &$matches): bool
```

Check if a string is a valid unit term symbol (e.g. `"km2"`, `"s-1"`, `"m"`).

**Parameters:**
- `$symbol` (string) - The symbol to validate.
- `$matches` (?array) - Output array for match results.

**Returns:**
- `bool` - True if the symbol is a valid unit term.

### isValidDerivedUnitForm1()

```php
public static function isValidDerivedUnitForm1(string $symbol): bool
```

Check if a string matches form 1 of a derived unit: unit terms separated by multiply/divide operators.

**Parameters:**
- `$symbol` (string) - The symbol to validate.

**Returns:**
- `bool` - True if the symbol matches form 1.

### isValidDerivedUnitForm2()

```php
public static function isValidDerivedUnitForm2(string $symbol, ?array &$matches): bool
```

Check if a string matches form 2 of a derived unit: numerator terms divided by parenthesised denominator terms. Populates named groups `num` and `den` in the matches array.

**Parameters:**
- `$symbol` (string) - The symbol to validate.
- `$matches` (?array) - Output array for named match groups (`num` and `den`).

**Returns:**
- `bool` - True if the symbol matches form 2.

### isValidDerivedUnit()

```php
public static function isValidDerivedUnit(string $symbol): bool
```

Check if a string is a valid derived unit symbol in either form (e.g. `"kg*m/s2"`, `"W/(sr*m2)"`).

**Parameters:**
- `$symbol` (string) - The symbol to validate.

**Returns:**
- `bool` - True if the symbol is a valid derived unit.

### isValidQuantity()

```php
public static function isValidQuantity(string $qty, ?array &$matches): bool
```

Check if a string is a valid quantity representation (a number optionally followed by a derived unit symbol, e.g. `"9.81 m/s2"`, `"100"`, `"3.14159 rad"`).

**Parameters:**
- `$qty` (string) - The quantity string to validate.
- `$matches` (?array) - Output array for match results.

**Returns:**
- `bool` - True if the quantity string is valid.

### isValidDmsAngle()

```php
public static function isValidDmsAngle(string $value, ?array &$matches): bool
```

Check if a string is a valid DMS (degrees, arcminutes, arcseconds) angle representation. Matches formats like `"45°30'15.5\""`, `"90°"`, `"-12°34'56.78\""`. Only one sign character is allowed, at the start.

**Parameters:**
- `$value` (string) - The string to validate.
- `$matches` (?array) - Output array for named match groups (`sign`, `deg`, `min`, `sec`).

**Returns:**
- `bool` - True if the string is a valid DMS angle.

### isValidAsciiPrefix()

```php
public static function isValidAsciiPrefix(string $symbol): bool
```

Check if a string is a valid ASCII prefix symbol: 1-2 ASCII letters, upper or lower case (e.g. `"k"`, `"Ki"`, `"M"`).

**Parameters:**
- `$symbol` (string) - The string to check.

**Returns:**
- `bool` - True if the string is a valid ASCII prefix symbol.

### isValidUnicodePrefix()

```php
public static function isValidUnicodePrefix(string $symbol): bool
```

Check if a string is a valid Unicode prefix symbol: a single Unicode letter (e.g. `"µ"`).

**Parameters:**
- `$symbol` (string) - The string to check.

**Returns:**
- `bool` - True if the string is a valid Unicode prefix symbol.

### isValidPrefix()

```php
public static function isValidPrefix(string $symbol): bool
```

Check if a string is a valid prefix symbol. Accepts both ASCII and Unicode prefix symbols.

**Parameters:**
- `$symbol` (string) - The string to check.

**Returns:**
- `bool` - True if the string is a valid prefix symbol.

## Usage Examples

You shouldn't need to use these methods - they are primarily for internal use by the package to validate strings.

### Validating Unit Symbols

```php
use Galaxon\Quantities\Internal\RegexHelper;

RegexHelper::isValidAsciiSymbol('m');             // true
RegexHelper::isValidAsciiSymbol('fluid ounce');   // true
RegexHelper::isValidUnicodeSymbol('Ω');           // true
RegexHelper::isValidUnicodeSymbol('°C');          // true
RegexHelper::isValidUnitSymbol('%');              // true
```

### Validating Derived Units

```php
use Galaxon\Quantities\Internal\RegexHelper;

RegexHelper::isValidDerivedUnit('kg*m/s2');       // true
RegexHelper::isValidDerivedUnit('W/(sr*m2)');     // true
RegexHelper::isValidDerivedUnitForm1('m/s');      // true
RegexHelper::isValidDerivedUnitForm2('W/(sr*m2)', $m); // true, $m['num'] = 'W', $m['den'] = 'sr*m2'
```

### Parsing Quantities

```php
use Galaxon\Quantities\Internal\RegexHelper;

RegexHelper::isValidQuantity('9.81 m/s2', $matches); // true
// $matches[1] = '9.81', $matches[2] = 'm/s2'

RegexHelper::isValidQuantity('100', $matches);        // true (dimensionless)
```

### Validating DMS Angles

```php
use Galaxon\Quantities\Internal\RegexHelper;

RegexHelper::isValidDmsAngle('45°30\'15.5"', $matches); // true
// $matches['deg'] = '45', $matches['min'] = '30', $matches['sec'] = '15.5'

RegexHelper::isValidDmsAngle('-90°', $matches);          // true
// $matches['sign'] = '-', $matches['deg'] = '90'
```

## See Also

- **[Unit](Unit.md)** - Unit of measurement
- **[UnitTerm](UnitTerm.md)** - Unit with prefix and exponent
- **[DerivedUnit](DerivedUnit.md)** - Compound unit representation
- **[Prefix](Prefix.md)** - SI and binary prefixes
