<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Internal;

use Galaxon\Core\Integers;
use Galaxon\Core\Numbers;

/**
 * Centralised regex patterns and validation for unit symbols.
 *
 * Contains all regex constants, pattern builders, and validation methods used across Unit, UnitTerm,
 * DerivedUnit, and Prefix classes.
 */
final class RegexHelper
{
    // region Constants

    /**
     * Pattern for a single ASCII letter.
     */
    private const string RX_ASCII_LETTER = '[a-z]';

    /**
     * Pattern for a single ASCII word (one or more letters).
     */
    private const string RX_ASCII_WORD = self::RX_ASCII_LETTER . '+';

    /**
     * Pattern for one to three ASCII words separated by single spaces.
     */
    private const string RX_ASCII_WORDS = self::RX_ASCII_WORD . '(?: ' . self::RX_ASCII_WORD . '){0,2}';

    /**
     * Pattern for a single ASCII non-alphanumeric character valid for use as a unit symbol.
     */
    private const string RX_ASCII_SPECIAL_CHR = "[!-'?@`]";

    /**
     * Pattern for a valid ASCII unit symbol: either ASCII words or a single special character.
     */
    private const string RX_ASCII_SYMBOL = self::RX_ASCII_WORDS . '|' . self::RX_ASCII_SPECIAL_CHR;

    /**
     * Pattern for a single Unicode letter.
     */
    private const string RX_UNICODE_LETTER = '\p{L}';

    /**
     * Pattern for a single Unicode special character valid for use as a unit symbol.
     */
    private const string RX_UNICODE_SPECIAL_CHR = "[!-'?@`′″‰\p{So}\p{Sc}]";

    /**
     * Pattern for a temperature symbol (degree sign followed by a single letter).
     */
    private const string RX_TEMP_SYMBOL = '°[a-z]';

    /**
     * Pattern for a valid Unicode unit symbol: Unicode letter, special character, or temperature symbol.
     */
    private const string RX_UNICODE_SYMBOL =
        self::RX_UNICODE_LETTER . '|' . self::RX_UNICODE_SPECIAL_CHR . '|' . self::RX_TEMP_SYMBOL;

    /**
     * Pattern for a valid prefix symbol: 1-2 ASCII letters or 1 Unicode letter.
     */
    private const string RX_PREFIX = self::RX_ASCII_LETTER . '{1,2}|' . self::RX_UNICODE_LETTER;

    /**
     * Pattern for a valid unit symbol: ASCII words or Unicode letter or special character.
     */
    private const string RX_UNIT = self::RX_ASCII_WORDS . '|' . self::RX_UNICODE_SYMBOL;

    /**
     * Allowed multiplication operators.
     *     * = Asterisk
     *     . = Period (full stop) character.
     *     · = Middle dot (U+00B7) - used in typography, Catalan, etc.
     *     ⋅ = Dot operator (U+22C5) - mathematical multiplication symbol.
     */
    private const string RX_MUL_OPS = '*.\x{00B7}\x{22C5}';

    /**
     * Regular expression character class with multiplication operators only.
     */
    private const string RX_CLASS_MUL_OPS = '[' . self::RX_MUL_OPS . ']';

    /**
     * Regular expression character class with multiplication and division operators.
     */
    public const string RX_CLASS_MUL_DIV_OPS = '[' . self::RX_MUL_OPS . '\/]';

    /**
     * Pattern for a valid number (decimal or scientific notation).
     * Must start with a digit; disallows starting with a sign or dot.
     */
    private const string RX_NUM = '\d+(?:\.\d+)?(?:[eE][+-]?\d+)?';

    // endregion Constants

    // region Regex builder methods

    /**
     * Get the regular expression pattern for matching a unit symbol (excluding dimensionless).
     *
     * @return string The regex pattern (without delimiters or anchors).
     */
    public static function unitRegex(): string
    {
        return '(' . self::RX_ASCII_WORDS . '|' . self::RX_UNICODE_SYMBOL . ')';
    }

    /**
     * Get the regex pattern for matching a unit term.
     *
     * Matches one or more letters (the unit symbol) optionally followed by an exponent
     * in either ASCII digits or Unicode superscript characters.
     *
     * @return string The regex pattern (without delimiters or anchors).
     */
    public static function unitTermRegex(): string
    {
        $superscriptChars = Integers::SUPERSCRIPT_CHARACTERS;
        $superscriptMinus = $superscriptChars['-'];
        unset($superscriptChars['-']);
        $superscriptDigits = implode('', $superscriptChars);
        return '((?:' . self::RX_PREFIX . ')?(?:' . self::RX_UNIT . "))((-?\d)|($superscriptMinus?[$superscriptDigits]))?";
    }

    /**
     * Get the regex pattern for form 1 of a derived unit: one or more unit terms separated by multiply and /or divide
     * operators.
     *
     * Example: kg*m/s2 or m−1*kg*s−3
     *
     * This will fail for an empty string (dimensionless).
     *
     * @return string The regex pattern (without delimiters or anchors).
     */
    public static function derivedUnitRegexForm1(): string
    {
        $rxUnitTerm = self::unitTermRegex();
        return "$rxUnitTerm(?:" . self::RX_CLASS_MUL_DIV_OPS . "$rxUnitTerm)*";
    }

    /**
     * Get the regex pattern for form 2 of a derived unit: numerator terms / (denominator terms).
     *
     * Example: W/(sr*m2)
     *
     * This will fail for an empty string (dimensionless).
     *
     * @return string The regex pattern (without delimiters or anchors).
     */
    public static function derivedUnitRegexForm2(): string
    {
        $rxUnitTerm = self::unitTermRegex();
        $mulTerms = "$rxUnitTerm(?:" . self::RX_CLASS_MUL_OPS . "$rxUnitTerm)*";
        return "(?:$mulTerms)\/\\((?:$mulTerms)\\)";
    }

    /**
     * Get the regex pattern for matching a derived unit (either form).
     *
     * @return string The regex pattern (without delimiters or anchors).
     */
    public static function derivedUnitRegex(): string
    {
        return '(?:' . self::derivedUnitRegexForm1() . ')|(?:' . self::derivedUnitRegexForm2() . ')';
    }

    // endregion Regex builder methods

    // region Validation methods

    /**
     * Check if a string is a valid unit name (non-empty ASCII, up to 3 words, upper and lower-case ok).
     *
     * @param string $name The string to check.
     * @return bool True if the string is a valid unit name.
     */
    public static function isValidUnitName(string $name): bool
    {
        return (bool)preg_match('/^' . self::RX_ASCII_WORDS . '$/i', $name);
    }

    /**
     * Check if a string is a valid ASCII unit symbol.
     *
     * This can include up to 3 ASCII words or a single special character.
     *
     * @param string $symbol The string to check.
     * @return bool True if the string is a valid ASCII unit symbol.
     */
    public static function isValidAsciiSymbol(string $symbol): bool
    {
        return (bool)preg_match('/^(' . self::RX_ASCII_SYMBOL . ')$/i', $symbol);
    }

    /**
     * Check if a string is a valid Unicode unit symbol.
     *
     * This can include a Unicode letter or special character, or a temperature unit symbol (e.g. '°C').
     *
     * @param string $symbol The string to check.
     * @return bool True if the string is a valid Unicode unit symbol.
     */
    public static function isValidUnicodeSymbol(string $symbol): bool
    {
        return (bool)preg_match('/^(' . self::RX_UNICODE_SYMBOL . ')$/iu', $symbol);
    }

    /**
     * Check if a string is a single ASCII character valid for use as an alternate unit symbol.
     *
     * Letters and special characters are allowed; non-printable characters, digits, brackets, and mathematical
     * operators are disallowed.
     *
     * @return bool True if the string contains only a single ASCII character valid for use as a unit symbol.
     */
    public static function isValidAlternateSymbol(string $symbol): bool
    {
        return (bool)preg_match('/^(' . self::RX_ASCII_LETTER . '|' . self::RX_ASCII_SPECIAL_CHR . ')$/i', $symbol);
    }

    /**
     * Check if a string is a valid unit symbol.
     *
     * @param string $symbol The string to check.
     * @return bool True if the string is a valid unit symbol.
     */
    public static function isValidUnitSymbol(string $symbol): bool
    {
        return self::isValidAsciiSymbol($symbol) ||
            self::isValidUnicodeSymbol($symbol) ||
            self::isValidAlternateSymbol($symbol);
    }

    /**
     * Check if a string is a Unicode special character.
     *
     * This excludes letters, digits, spaces, non-printable characters, mathematical operators, brackets, and most
     * punctuation.
     *
     * @param string $symbol The string to check.
     * @return bool True if the string contains only a single special Unicode character.
     */
    public static function isValidUnicodeSpecialChar(string $symbol): bool
    {
        return (bool)preg_match('/^' . self::RX_UNICODE_SPECIAL_CHR . '$/iu', $symbol);
    }

    /**
     * Check if a string is a valid unit term symbol.
     *
     * @param string $symbol The symbol to validate.
     * @param ?array<array-key, string> $matches Output array for match results.
     * @return bool True if the symbol is a valid unit term.
     */
    public static function isValidUnitTerm(string $symbol, ?array &$matches): bool
    {
        return (bool)preg_match('/^' . self::unitTermRegex() . '$/iu', $symbol, $matches);
    }

    /**
     * Check if a string is a valid derived unit symbol (either form).
     *
     * @param string $symbol The symbol to validate.
     * @return bool True if the symbol is a valid derived unit.
     */
    public static function isValidDerivedUnit(string $symbol): bool
    {
        return (bool)preg_match('/^' . self::derivedUnitRegex() . '$/iu', $symbol);
    }

    /**
     * Check if a string matches form 1 of a derived unit: unit terms separated by multiply/divide operators.
     *
     * @param string $symbol The symbol to validate.
     * @return bool True if the symbol matches form 1.
     */
    public static function isValidDerivedUnitForm1(string $symbol): bool
    {
        return (bool)preg_match('/^' . self::derivedUnitRegexForm1() . '$/iu', $symbol);
    }

    /**
     * Check if a string matches form 2 of a derived unit: numerator terms / (denominator terms).
     *
     * @param string $symbol The symbol to validate.
     * @param ?array<array-key, string> $matches Output array for match results (named groups 'num' and 'den').
     * @return bool True if the symbol matches form 2.
     */
    public static function isValidDerivedUnitForm2(string $symbol, ?array &$matches): bool
    {
        // Wrap the mul-only sub-patterns with named groups for extraction.
        $rxUnitTerm = self::unitTermRegex();
        $mulTerms = "$rxUnitTerm(?:" . self::RX_CLASS_MUL_OPS . "$rxUnitTerm)*";
        return (bool)preg_match("/^(?<num>$mulTerms)\/\((?<den>$mulTerms)\)$/iu", $symbol, $matches);
    }

    /**
     * Check if a string is a valid quantity representation.
     *
     * @param string $qty The quantity string to validate.
     * @param ?array<array-key, string> $matches Output array for match results.
     * @return bool True if the quantity string is valid.
     */
    public static function isValidQuantity(string $qty, ?array &$matches): bool
    {
        $rxNum = Numbers::REGEX;
        $rxDerivedUnit = self::derivedUnitRegex();
        return (bool)preg_match("/^($rxNum)\s*($rxDerivedUnit)?$/iu", $qty, $matches);
    }

    /**
     * Check if a string is a valid DMS (degrees, arcminutes, arcseconds) angle representation.
     *
     * Matches formats like "45°30'15.5\"", "90°", "-12°34′56.78″".
     * Only one plus or minus sign is allowed, and it must be at the start of the string.
     *
     * @param string $value The string to validate.
     * @param ?array<array-key, string> $matches Output array for named match groups ('sign', 'deg', 'min', 'sec').
     * @return bool True if the string is a valid DMS angle.
     */
    public static function isValidDmsAngle(string $value, ?array &$matches): bool
    {
        $pattern = '/^(?:(?<sign>[-+]?)\s*)?'
                   . "(?:(?<deg>" . self::RX_NUM . ")°\s*)?"
                   . "(?:(?<min>" . self::RX_NUM . ")[′']\s*)?"
                   . "(?:(?<sec>" . self::RX_NUM . ")[″\"])?$/u";
        return (bool)preg_match($pattern, $value, $matches);
    }

    /**
     * Check if a string is a valid ASCII prefix symbol (1-2 ASCII letters, upper or lower case, e.g. 'm', 'Ki').
     *
     * @param string $symbol The string to check.
     * @return bool True if the string is a valid ASCII prefix symbol.
     */
    public static function isValidAsciiPrefix(string $symbol): bool
    {
        return (bool)preg_match('/^[a-z]{1,2}$/i', $symbol);
    }

    /**
     * Check if a string is a valid Unicode prefix symbol (1 Unicode letter, upper or lower case, e.g. 'µ').
     *
     * @param string $symbol The string to check.
     * @return bool True if the string is a valid Unicode prefix symbol.
     */
    public static function isValidUnicodePrefix(string $symbol): bool
    {
        return (bool)preg_match('/^\p{L}$/iu', $symbol);
    }

    /**
     * Check if a string is a valid prefix symbol.
     *
     * @param string $symbol The string to check.
     * @return bool True if the string is a valid Unicode prefix symbol.
     */
    public static function isValidPrefix(string $symbol): bool
    {
        return self::isValidAsciiPrefix($symbol) || self::isValidUnicodePrefix($symbol);
    }

    // endregion Prefix validation methods
}
