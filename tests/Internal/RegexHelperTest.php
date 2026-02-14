<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Internal;

use Galaxon\Quantities\Internal\RegexHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for RegexHelper class.
 */
#[CoversClass(RegexHelper::class)]
final class RegexHelperTest extends TestCase
{
    // region isValidName tests

    /**
     * Test isValidName returns true for single word.
     */
    public function testIsValidNameReturnsTrueForSingleWord(): void
    {
        $this->assertTrue(RegexHelper::isValidUnitName('meter'));
        $this->assertTrue(RegexHelper::isValidUnitName('joule'));
        $this->assertTrue(RegexHelper::isValidUnitName('Hz'));
    }

    /**
     * Test isValidName returns true for two words.
     */
    public function testIsValidNameReturnsTrueForTwoWords(): void
    {
        $this->assertTrue(RegexHelper::isValidUnitName('nautical mile'));
        $this->assertTrue(RegexHelper::isValidUnitName('troy ounce'));
    }

    /**
     * Test isValidName returns true for three words.
     */
    public function testIsValidNameReturnsTrueForThreeWords(): void
    {
        $this->assertTrue(RegexHelper::isValidUnitName('US fluid ounce'));
    }

    /**
     * Test isValidName returns false for empty string.
     */
    public function testIsValidNameReturnsFalseForEmpty(): void
    {
        $this->assertFalse(RegexHelper::isValidUnitName(''));
    }

    /**
     * Test isValidName returns false for digits.
     */
    public function testIsValidNameReturnsFalseForDigits(): void
    {
        $this->assertFalse(RegexHelper::isValidUnitName('unit123'));
    }

    /**
     * Test isValidName returns false for too many words.
     */
    public function testIsValidNameReturnsFalseForTooManyWords(): void
    {
        $this->assertFalse(RegexHelper::isValidUnitName('one two three four'));
    }

    // endregion

    // region isValidAsciiSymbol tests

    /**
     * Test isValidAsciiSymbol returns true for valid symbols.
     */
    public function testIsValidAsciiSymbolReturnsTrueForValid(): void
    {
        $this->assertTrue(RegexHelper::isValidAsciiSymbol('m'));
        $this->assertTrue(RegexHelper::isValidAsciiSymbol('km'));
        $this->assertTrue(RegexHelper::isValidAsciiSymbol('Hz'));
        $this->assertTrue(RegexHelper::isValidAsciiSymbol('ohm'));
    }

    /**
     * Test isValidAsciiSymbol returns false for invalid symbols.
     */
    public function testIsValidAsciiSymbolReturnsFalseForInvalid(): void
    {
        $this->assertFalse(RegexHelper::isValidAsciiSymbol('m²'));
        $this->assertFalse(RegexHelper::isValidAsciiSymbol('123'));
        $this->assertFalse(RegexHelper::isValidAsciiSymbol('Ω'));
    }

    // endregion

    // region isValidUnicodeSymbol tests

    /**
     * Test isValidUnicodeSymbol returns true for valid symbols.
     */
    public function testIsValidUnicodeSymbolReturnsTrueForValid(): void
    {
        $this->assertTrue(RegexHelper::isValidUnicodeSymbol('m'));
        $this->assertTrue(RegexHelper::isValidUnicodeSymbol('Ω'));
        $this->assertTrue(RegexHelper::isValidUnicodeSymbol('°'));
        $this->assertTrue(RegexHelper::isValidUnicodeSymbol('μ'));
    }

    /**
     * Test isValidUnicodeSymbol returns false for invalid symbols.
     */
    public function testIsValidUnicodeSymbolReturnsFalseForInvalid(): void
    {
        $this->assertFalse(RegexHelper::isValidUnicodeSymbol('123'));
    }

    // endregion

    // region isValidAlternateSymbol tests

    /**
     * Test isValidAlternateSymbol returns true for valid symbols.
     */
    public function testIsValidAlternateSymbolReturnsTrueForValid(): void
    {
        // Single letters (case insensitive).
        $this->assertTrue(RegexHelper::isValidAlternateSymbol('l'));
        $this->assertTrue(RegexHelper::isValidAlternateSymbol('L'));
        $this->assertTrue(RegexHelper::isValidAlternateSymbol('z'));

        // Punctuation marks.
        $this->assertTrue(RegexHelper::isValidAlternateSymbol('%'));
        $this->assertTrue(RegexHelper::isValidAlternateSymbol('"'));
        $this->assertTrue(RegexHelper::isValidAlternateSymbol("'"));
        $this->assertTrue(RegexHelper::isValidAlternateSymbol('#'));
        $this->assertTrue(RegexHelper::isValidAlternateSymbol('!'));
        $this->assertTrue(RegexHelper::isValidAlternateSymbol('@'));
        $this->assertTrue(RegexHelper::isValidAlternateSymbol('?'));
        $this->assertTrue(RegexHelper::isValidAlternateSymbol('$'));
        $this->assertTrue(RegexHelper::isValidAlternateSymbol('`'));
    }

    /**
     * Test isValidAlternateSymbol returns false for invalid symbols.
     */
    public function testIsValidAlternateSymbolReturnsFalseForInvalid(): void
    {
        // Digits.
        $this->assertFalse(RegexHelper::isValidAlternateSymbol('1'));
        $this->assertFalse(RegexHelper::isValidAlternateSymbol('0'));

        // Multiple characters.
        $this->assertFalse(RegexHelper::isValidAlternateSymbol('ab'));
        $this->assertFalse(RegexHelper::isValidAlternateSymbol('%%'));

        // Unicode characters.
        $this->assertFalse(RegexHelper::isValidAlternateSymbol('ℓ'));
        $this->assertFalse(RegexHelper::isValidAlternateSymbol('Ω'));

        // Disallowed ASCII (brackets, math operators).
        $this->assertFalse(RegexHelper::isValidAlternateSymbol('('));
        $this->assertFalse(RegexHelper::isValidAlternateSymbol('+'));
        $this->assertFalse(RegexHelper::isValidAlternateSymbol('*'));
        $this->assertFalse(RegexHelper::isValidAlternateSymbol('-'));
        $this->assertFalse(RegexHelper::isValidAlternateSymbol('/'));
    }

    // endregion

    // region isValidUnitSymbol tests

    /**
     * Test isValidUnitSymbol returns true for ASCII word symbols.
     */
    public function testIsValidUnitSymbolReturnsTrueForAsciiWords(): void
    {
        $this->assertTrue(RegexHelper::isValidUnitSymbol('m'));
        $this->assertTrue(RegexHelper::isValidUnitSymbol('Hz'));
        $this->assertTrue(RegexHelper::isValidUnitSymbol('ohm'));
        $this->assertTrue(RegexHelper::isValidUnitSymbol('fluid ounce'));
    }

    /**
     * Test isValidUnitSymbol returns true for Unicode symbols.
     */
    public function testIsValidUnitSymbolReturnsTrueForUnicodeSymbols(): void
    {
        $this->assertTrue(RegexHelper::isValidUnitSymbol('Ω'));
        $this->assertTrue(RegexHelper::isValidUnitSymbol('°'));
        $this->assertTrue(RegexHelper::isValidUnitSymbol('μ'));
        $this->assertTrue(RegexHelper::isValidUnitSymbol('°C'));
    }

    /**
     * Test isValidUnitSymbol returns true for alternate symbols.
     */
    public function testIsValidUnitSymbolReturnsTrueForAlternateSymbols(): void
    {
        $this->assertTrue(RegexHelper::isValidUnitSymbol('%'));
        $this->assertTrue(RegexHelper::isValidUnitSymbol('"'));
        $this->assertTrue(RegexHelper::isValidUnitSymbol("'"));
        $this->assertTrue(RegexHelper::isValidUnitSymbol('#'));
    }

    /**
     * Test isValidUnitSymbol returns false for invalid symbols.
     */
    public function testIsValidUnitSymbolReturnsFalseForInvalid(): void
    {
        $this->assertFalse(RegexHelper::isValidUnitSymbol('123'));
        $this->assertFalse(RegexHelper::isValidUnitSymbol('m²'));
        $this->assertFalse(RegexHelper::isValidUnitSymbol(''));
        $this->assertFalse(RegexHelper::isValidUnitSymbol('('));
        $this->assertFalse(RegexHelper::isValidUnitSymbol('+'));
    }

    // endregion

    // region isValidUnicodeNonLetterChar tests

    /**
     * Test isValidUnicodeNonLetterChar returns true for valid symbols.
     */
    public function testIsValidUnicodeNonLetterCharReturnsTrueForValid(): void
    {
        $this->assertTrue(RegexHelper::isValidUnicodeSpecialChar('°'));
        $this->assertTrue(RegexHelper::isValidUnicodeSpecialChar('%'));
        $this->assertTrue(RegexHelper::isValidUnicodeSpecialChar('$'));
        $this->assertTrue(RegexHelper::isValidUnicodeSpecialChar('€'));
        $this->assertTrue(RegexHelper::isValidUnicodeSpecialChar('′'));
        $this->assertTrue(RegexHelper::isValidUnicodeSpecialChar('″'));
    }

    /**
     * Test isValidUnicodeNonLetterChar returns false for letters.
     */
    public function testIsValidUnicodeNonLetterCharReturnsFalseForLetters(): void
    {
        $this->assertFalse(RegexHelper::isValidUnicodeSpecialChar('m'));
        $this->assertFalse(RegexHelper::isValidUnicodeSpecialChar('abc'));
    }

    /**
     * Test isValidUnicodeNonLetterChar returns false for digits.
     */
    public function testIsValidUnicodeNonLetterCharReturnsFalseForDigits(): void
    {
        $this->assertFalse(RegexHelper::isValidUnicodeSpecialChar('1'));
        $this->assertFalse(RegexHelper::isValidUnicodeSpecialChar('123'));
    }

    /**
     * Test isValidUnicodeNonLetterChar returns false for multiple symbols.
     */
    public function testIsValidUnicodeNonLetterCharReturnsFalseForMultipleSymbols(): void
    {
        $this->assertFalse(RegexHelper::isValidUnicodeSpecialChar('°C'));
        $this->assertFalse(RegexHelper::isValidUnicodeSpecialChar('%%'));
    }

    // endregion

    // region unitRegex() tests

    /**
     * Test unitRegex matches valid unit symbols.
     */
    public function testUnitRegexMatchesValidSymbols(): void
    {
        $pattern = '/^' . RegexHelper::unitRegex() . '$/iu';

        $this->assertSame(1, preg_match($pattern, 'm'));
        $this->assertSame(1, preg_match($pattern, 'km'));
        $this->assertSame(1, preg_match($pattern, 'Hz'));
        $this->assertSame(1, preg_match($pattern, 'ohm'));
        $this->assertSame(1, preg_match($pattern, 'Ω'));
    }

    /**
     * Test unitRegex does not match invalid symbols.
     */
    public function testUnitRegexDoesNotMatchInvalidSymbols(): void
    {
        $pattern = '/^' . RegexHelper::unitRegex() . '$/iu';

        $this->assertSame(0, preg_match($pattern, '123'));
        $this->assertSame(0, preg_match($pattern, 'm²'));
    }

    // endregion

    // region Prefix validation tests

    /**
     * Test isValidAsciiPrefixSymbol returns true for valid symbols.
     */
    public function testIsValidAsciiPrefixSymbolReturnsTrueForValid(): void
    {
        $this->assertTrue(RegexHelper::isValidAsciiPrefix('k'));
        $this->assertTrue(RegexHelper::isValidAsciiPrefix('M'));
        $this->assertTrue(RegexHelper::isValidAsciiPrefix('Ki'));
        $this->assertTrue(RegexHelper::isValidAsciiPrefix('da'));
    }

    /**
     * Test isValidAsciiPrefixSymbol returns false for invalid symbols.
     */
    public function testIsValidAsciiPrefixSymbolReturnsFalseForInvalid(): void
    {
        $this->assertFalse(RegexHelper::isValidAsciiPrefix(''));
        $this->assertFalse(RegexHelper::isValidAsciiPrefix('abc'));
        $this->assertFalse(RegexHelper::isValidAsciiPrefix('1'));
        $this->assertFalse(RegexHelper::isValidAsciiPrefix('μ'));
    }

    /**
     * Test isValidUnicodePrefixSymbol returns true for valid symbols.
     */
    public function testIsValidUnicodePrefixSymbolReturnsTrueForValid(): void
    {
        $this->assertTrue(RegexHelper::isValidPrefix('μ'));
        $this->assertTrue(RegexHelper::isValidPrefix('k'));
        $this->assertTrue(RegexHelper::isValidPrefix('Ki'));
    }

    /**
     * Test isValidUnicodePrefixSymbol returns false for invalid symbols.
     */
    public function testIsValidUnicodePrefixSymbolReturnsFalseForInvalid(): void
    {
        $this->assertFalse(RegexHelper::isValidPrefix(''));
        $this->assertFalse(RegexHelper::isValidPrefix('abc'));
        $this->assertFalse(RegexHelper::isValidPrefix('1'));
        $this->assertFalse(RegexHelper::isValidPrefix('°'));
    }

    // endregion

    // region isValidQuantity tests

    /**
     * Test isValidQuantity returns true for value with unit.
     */
    public function testIsValidQuantityReturnsTrueForValueWithUnit(): void
    {
        $this->assertTrue(RegexHelper::isValidQuantity('123.45 km', $m));
        $this->assertSame('123.45', $m[1]);
        $this->assertSame('km', $m[2]);
    }

    /**
     * Test isValidQuantity returns true for value without space before unit.
     */
    public function testIsValidQuantityReturnsTrueWithoutSpace(): void
    {
        $this->assertTrue(RegexHelper::isValidQuantity('90deg', $m));
        $this->assertSame('90', $m[1]);
        $this->assertSame('deg', $m[2]);
    }

    /**
     * Test isValidQuantity returns true for scientific notation.
     */
    public function testIsValidQuantityReturnsTrueForScientificNotation(): void
    {
        $this->assertTrue(RegexHelper::isValidQuantity('1.5e3 ms', $m));
        $this->assertSame('1.5e3', $m[1]);
    }

    /**
     * Test isValidQuantity returns true for negative value.
     */
    public function testIsValidQuantityReturnsTrueForNegativeValue(): void
    {
        $this->assertTrue(RegexHelper::isValidQuantity('-9.81 m/s2', $m));
        $this->assertSame('-9.81', $m[1]);
    }

    /**
     * Test isValidQuantity returns true for dimensionless value.
     */
    public function testIsValidQuantityReturnsTrueForDimensionless(): void
    {
        $this->assertTrue(RegexHelper::isValidQuantity('42', $m));
        $this->assertSame('42', $m[1]);
        $this->assertFalse(isset($m[2]) && $m[2] !== '');
    }

    /**
     * Test isValidQuantity returns true for compound unit with parentheses.
     */
    public function testIsValidQuantityReturnsTrueForCompoundUnit(): void
    {
        $this->assertTrue(RegexHelper::isValidQuantity('8.314 J/(mol*K)', $m));
        $this->assertSame('8.314', $m[1]);
    }

    /**
     * Test isValidQuantity returns false for empty string.
     */
    public function testIsValidQuantityReturnsFalseForEmpty(): void
    {
        $this->assertFalse(RegexHelper::isValidQuantity('', $m));
    }

    /**
     * Test isValidQuantity returns false for non-numeric input.
     */
    public function testIsValidQuantityReturnsFalseForNonNumeric(): void
    {
        $this->assertFalse(RegexHelper::isValidQuantity('abc', $m));
        $this->assertFalse(RegexHelper::isValidQuantity('km', $m));
    }

    // endregion

    // region isValidDmsAngle tests

    /**
     * Test isValidDmsAngle returns true for degrees only.
     */
    public function testIsValidDmsAngleReturnsTrueForDegreesOnly(): void
    {
        $this->assertTrue(RegexHelper::isValidDmsAngle('45°', $m));
        $this->assertSame('45', $m['deg']);
    }

    /**
     * Test isValidDmsAngle returns true for degrees and arcminutes.
     */
    public function testIsValidDmsAngleReturnsTrueForDegreesAndMinutes(): void
    {
        $this->assertTrue(RegexHelper::isValidDmsAngle("45°30'", $m));
        $this->assertSame('45', $m['deg']);
        $this->assertSame('30', $m['min']);
    }

    /**
     * Test isValidDmsAngle returns true for full DMS with ASCII quotes.
     */
    public function testIsValidDmsAngleReturnsTrueForFullDmsAscii(): void
    {
        $this->assertTrue(RegexHelper::isValidDmsAngle('45°30\'15.5"', $m));
        $this->assertSame('45', $m['deg']);
        $this->assertSame('30', $m['min']);
        $this->assertSame('15.5', $m['sec']);
    }

    /**
     * Test isValidDmsAngle returns true for full DMS with Unicode symbols.
     */
    public function testIsValidDmsAngleReturnsTrueForFullDmsUnicode(): void
    {
        $this->assertTrue(RegexHelper::isValidDmsAngle('12°34′56.78″', $m));
        $this->assertSame('12', $m['deg']);
        $this->assertSame('34', $m['min']);
        $this->assertSame('56.78', $m['sec']);
    }

    /**
     * Test isValidDmsAngle returns true for negative angle.
     */
    public function testIsValidDmsAngleReturnsTrueForNegative(): void
    {
        $this->assertTrue(RegexHelper::isValidDmsAngle('-90°', $m));
        $this->assertSame('-', $m['sign']);
        $this->assertSame('90', $m['deg']);
    }

    /**
     * Test isValidDmsAngle returns true for positive sign.
     */
    public function testIsValidDmsAngleReturnsTrueForPositiveSign(): void
    {
        $this->assertTrue(RegexHelper::isValidDmsAngle('+45°', $m));
        $this->assertSame('+', $m['sign']);
    }

    /**
     * Test isValidDmsAngle returns true for arcminutes only.
     */
    public function testIsValidDmsAngleReturnsTrueForMinutesOnly(): void
    {
        $this->assertTrue(RegexHelper::isValidDmsAngle("30'", $m));
        $this->assertSame('30', $m['min']);
    }

    /**
     * Test isValidDmsAngle returns true for arcseconds only.
     */
    public function testIsValidDmsAngleReturnsTrueForSecondsOnly(): void
    {
        $this->assertTrue(RegexHelper::isValidDmsAngle('15.5"', $m));
        $this->assertSame('15.5', $m['sec']);
    }

    /**
     * Test isValidDmsAngle returns true for decimal degrees.
     */
    public function testIsValidDmsAngleReturnsTrueForDecimalDegrees(): void
    {
        $this->assertTrue(RegexHelper::isValidDmsAngle('45.5°', $m));
        $this->assertSame('45.5', $m['deg']);
    }

    /**
     * Test isValidDmsAngle returns true for scientific notation in seconds.
     */
    public function testIsValidDmsAngleReturnsTrueForScientificNotation(): void
    {
        $this->assertTrue(RegexHelper::isValidDmsAngle('1.5e2"', $m));
        $this->assertSame('1.5e2', $m['sec']);
    }

    /**
     * Test isValidDmsAngle returns true with spaces between components.
     */
    public function testIsValidDmsAngleReturnsTrueWithSpaces(): void
    {
        $this->assertTrue(RegexHelper::isValidDmsAngle("45° 30' 15\"", $m));
        $this->assertSame('45', $m['deg']);
        $this->assertSame('30', $m['min']);
        $this->assertSame('15', $m['sec']);
    }

    /**
     * Test isValidDmsAngle returns false for plain number without DMS symbols.
     */
    public function testIsValidDmsAngleReturnsFalseForPlainNumber(): void
    {
        $this->assertFalse(RegexHelper::isValidDmsAngle('45', $m));
    }

    /**
     * Test isValidDmsAngle returns false for invalid format.
     */
    public function testIsValidDmsAngleReturnsFalseForInvalidFormat(): void
    {
        $this->assertFalse(RegexHelper::isValidDmsAngle('abc°', $m));
    }

    // endregion
}
