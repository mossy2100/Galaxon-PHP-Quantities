<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Services;

use Galaxon\Quantities\Services\RegexService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for RegexService class.
 */
#[CoversClass(RegexService::class)]
final class RegexServiceTest extends TestCase
{
    // region isValidName tests

    /**
     * Test isValidName returns true for single word.
     */
    public function testIsValidNameReturnsTrueForSingleWord(): void
    {
        $this->assertTrue(RegexService::isValidUnitName('meter'));
        $this->assertTrue(RegexService::isValidUnitName('joule'));
        $this->assertTrue(RegexService::isValidUnitName('Hz'));
    }

    /**
     * Test isValidName returns true for two words.
     */
    public function testIsValidNameReturnsTrueForTwoWords(): void
    {
        $this->assertTrue(RegexService::isValidUnitName('nautical mile'));
        $this->assertTrue(RegexService::isValidUnitName('troy ounce'));
    }

    /**
     * Test isValidName returns true for three words.
     */
    public function testIsValidNameReturnsTrueForThreeWords(): void
    {
        $this->assertTrue(RegexService::isValidUnitName('US fluid ounce'));
    }

    /**
     * Test isValidName returns false for empty string.
     */
    public function testIsValidNameReturnsFalseForEmpty(): void
    {
        $this->assertFalse(RegexService::isValidUnitName(''));
    }

    /**
     * Test isValidName returns false for digits.
     */
    public function testIsValidNameReturnsFalseForDigits(): void
    {
        $this->assertFalse(RegexService::isValidUnitName('unit123'));
    }

    // endregion

    // region isValidAsciiSymbol tests

    /**
     * Test isValidAsciiSymbol returns true for valid symbols.
     */
    public function testIsValidAsciiSymbolReturnsTrueForValid(): void
    {
        $this->assertTrue(RegexService::isValidAsciiSymbol('m'));
        $this->assertTrue(RegexService::isValidAsciiSymbol('km'));
        $this->assertTrue(RegexService::isValidAsciiSymbol('Hz'));
        $this->assertTrue(RegexService::isValidAsciiSymbol('ohm'));
    }

    /**
     * Test isValidAsciiSymbol returns false for invalid symbols.
     */
    public function testIsValidAsciiSymbolReturnsFalseForInvalid(): void
    {
        $this->assertFalse(RegexService::isValidAsciiSymbol('m²'));
        $this->assertFalse(RegexService::isValidAsciiSymbol('123'));
        $this->assertFalse(RegexService::isValidAsciiSymbol('Ω'));
    }

    // endregion

    // region isValidUnicodeSymbol tests

    /**
     * Test isValidUnicodeSymbol returns true for valid symbols.
     */
    public function testIsValidUnicodeSymbolReturnsTrueForValid(): void
    {
        $this->assertTrue(RegexService::isValidUnicodeSymbol('m'));
        $this->assertTrue(RegexService::isValidUnicodeSymbol('Ω'));
        $this->assertTrue(RegexService::isValidUnicodeSymbol('°'));
        $this->assertTrue(RegexService::isValidUnicodeSymbol('μ'));
    }

    /**
     * Test isValidUnicodeSymbol returns false for invalid symbols.
     */
    public function testIsValidUnicodeSymbolReturnsFalseForInvalid(): void
    {
        $this->assertFalse(RegexService::isValidUnicodeSymbol('123'));
    }

    // endregion

    // region isValidAlternateSymbol tests

    /**
     * Test isValidAlternateSymbol returns true for valid symbols.
     */
    public function testIsValidAlternateSymbolReturnsTrueForValid(): void
    {
        // Single letters (case insensitive).
        $this->assertTrue(RegexService::isValidAlternateSymbol('l'));
        $this->assertTrue(RegexService::isValidAlternateSymbol('L'));
        $this->assertTrue(RegexService::isValidAlternateSymbol('z'));

        // Punctuation marks.
        $this->assertTrue(RegexService::isValidAlternateSymbol('%'));
        $this->assertTrue(RegexService::isValidAlternateSymbol('"'));
        $this->assertTrue(RegexService::isValidAlternateSymbol("'"));
        $this->assertTrue(RegexService::isValidAlternateSymbol('#'));
        $this->assertTrue(RegexService::isValidAlternateSymbol('!'));
        $this->assertTrue(RegexService::isValidAlternateSymbol('@'));
        $this->assertTrue(RegexService::isValidAlternateSymbol('?'));
        $this->assertTrue(RegexService::isValidAlternateSymbol('$'));
        $this->assertTrue(RegexService::isValidAlternateSymbol('`'));
    }

    /**
     * Test isValidAlternateSymbol returns false for invalid symbols.
     */
    public function testIsValidAlternateSymbolReturnsFalseForInvalid(): void
    {
        // Digits.
        $this->assertFalse(RegexService::isValidAlternateSymbol('1'));
        $this->assertFalse(RegexService::isValidAlternateSymbol('0'));

        // Multiple characters.
        $this->assertFalse(RegexService::isValidAlternateSymbol('ab'));
        $this->assertFalse(RegexService::isValidAlternateSymbol('%%'));

        // Unicode characters.
        $this->assertFalse(RegexService::isValidAlternateSymbol('ℓ'));
        $this->assertFalse(RegexService::isValidAlternateSymbol('Ω'));

        // Disallowed ASCII (brackets, math operators).
        $this->assertFalse(RegexService::isValidAlternateSymbol('('));
        $this->assertFalse(RegexService::isValidAlternateSymbol('+'));
        $this->assertFalse(RegexService::isValidAlternateSymbol('*'));
        $this->assertFalse(RegexService::isValidAlternateSymbol('-'));
        $this->assertFalse(RegexService::isValidAlternateSymbol('/'));
    }

    // endregion

    // region isValidUnitSymbol tests

    /**
     * Test isValidUnitSymbol returns true for ASCII word symbols.
     */
    public function testIsValidUnitSymbolReturnsTrueForAsciiWords(): void
    {
        $this->assertTrue(RegexService::isValidUnitSymbol('m'));
        $this->assertTrue(RegexService::isValidUnitSymbol('Hz'));
        $this->assertTrue(RegexService::isValidUnitSymbol('ohm'));
        $this->assertTrue(RegexService::isValidUnitSymbol('fluid ounce'));
    }

    /**
     * Test isValidUnitSymbol returns true for Unicode symbols.
     */
    public function testIsValidUnitSymbolReturnsTrueForUnicodeSymbols(): void
    {
        $this->assertTrue(RegexService::isValidUnitSymbol('Ω'));
        $this->assertTrue(RegexService::isValidUnitSymbol('°'));
        $this->assertTrue(RegexService::isValidUnitSymbol('μ'));
        $this->assertTrue(RegexService::isValidUnitSymbol('°C'));
    }

    /**
     * Test isValidUnitSymbol returns true for alternate symbols.
     */
    public function testIsValidUnitSymbolReturnsTrueForAlternateSymbols(): void
    {
        $this->assertTrue(RegexService::isValidUnitSymbol('%'));
        $this->assertTrue(RegexService::isValidUnitSymbol('"'));
        $this->assertTrue(RegexService::isValidUnitSymbol("'"));
        $this->assertTrue(RegexService::isValidUnitSymbol('#'));
    }

    /**
     * Test isValidUnitSymbol returns false for invalid symbols.
     */
    public function testIsValidUnitSymbolReturnsFalseForInvalid(): void
    {
        $this->assertFalse(RegexService::isValidUnitSymbol('123'));
        $this->assertFalse(RegexService::isValidUnitSymbol('m²'));
        $this->assertFalse(RegexService::isValidUnitSymbol(''));
        $this->assertFalse(RegexService::isValidUnitSymbol('('));
        $this->assertFalse(RegexService::isValidUnitSymbol('+'));
    }

    // endregion

    // region isValidUnicodeNonLetterChar tests

    /**
     * Test isValidUnicodeNonLetterChar returns true for valid symbols.
     */
    public function testIsValidUnicodeNonLetterCharReturnsTrueForValid(): void
    {
        $this->assertTrue(RegexService::isValidUnicodeSpecialChar('°'));
        $this->assertTrue(RegexService::isValidUnicodeSpecialChar('%'));
        $this->assertTrue(RegexService::isValidUnicodeSpecialChar('$'));
        $this->assertTrue(RegexService::isValidUnicodeSpecialChar('€'));
        $this->assertTrue(RegexService::isValidUnicodeSpecialChar('′'));
        $this->assertTrue(RegexService::isValidUnicodeSpecialChar('″'));
    }

    /**
     * Test isValidUnicodeNonLetterChar returns false for letters.
     */
    public function testIsValidUnicodeNonLetterCharReturnsFalseForLetters(): void
    {
        $this->assertFalse(RegexService::isValidUnicodeSpecialChar('m'));
        $this->assertFalse(RegexService::isValidUnicodeSpecialChar('abc'));
    }

    /**
     * Test isValidUnicodeNonLetterChar returns false for digits.
     */
    public function testIsValidUnicodeNonLetterCharReturnsFalseForDigits(): void
    {
        $this->assertFalse(RegexService::isValidUnicodeSpecialChar('1'));
        $this->assertFalse(RegexService::isValidUnicodeSpecialChar('123'));
    }

    /**
     * Test isValidUnicodeNonLetterChar returns false for multiple symbols.
     */
    public function testIsValidUnicodeNonLetterCharReturnsFalseForMultipleSymbols(): void
    {
        $this->assertFalse(RegexService::isValidUnicodeSpecialChar('°C'));
        $this->assertFalse(RegexService::isValidUnicodeSpecialChar('%%'));
    }

    // endregion

    // region unitRegex() tests

    /**
     * Test unitRegex matches valid unit symbols.
     */
    public function testUnitRegexMatchesValidSymbols(): void
    {
        $pattern = '/^' . RegexService::unitRegex() . '$/iu';

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
        $pattern = '/^' . RegexService::unitRegex() . '$/iu';

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
        $this->assertTrue(RegexService::isValidAsciiPrefix('k'));
        $this->assertTrue(RegexService::isValidAsciiPrefix('M'));
        $this->assertTrue(RegexService::isValidAsciiPrefix('Ki'));
        $this->assertTrue(RegexService::isValidAsciiPrefix('da'));
    }

    /**
     * Test isValidAsciiPrefixSymbol returns false for invalid symbols.
     */
    public function testIsValidAsciiPrefixSymbolReturnsFalseForInvalid(): void
    {
        $this->assertFalse(RegexService::isValidAsciiPrefix(''));
        $this->assertFalse(RegexService::isValidAsciiPrefix('abc'));
        $this->assertFalse(RegexService::isValidAsciiPrefix('1'));
        $this->assertFalse(RegexService::isValidAsciiPrefix('μ'));
    }

    /**
     * Test isValidUnicodePrefixSymbol returns true for valid symbols.
     */
    public function testIsValidUnicodePrefixSymbolReturnsTrueForValid(): void
    {
        $this->assertTrue(RegexService::isValidPrefix('μ'));
        $this->assertTrue(RegexService::isValidPrefix('k'));
        $this->assertTrue(RegexService::isValidPrefix('Ki'));
    }

    /**
     * Test isValidUnicodePrefixSymbol returns false for invalid symbols.
     */
    public function testIsValidUnicodePrefixSymbolReturnsFalseForInvalid(): void
    {
        $this->assertFalse(RegexService::isValidPrefix(''));
        $this->assertFalse(RegexService::isValidPrefix('abc'));
        $this->assertFalse(RegexService::isValidPrefix('1'));
        $this->assertFalse(RegexService::isValidPrefix('°'));
    }

    // endregion

    // region isValidUnitTerm tests

    /**
     * Test isValidUnitTerm returns true for a simple unit.
     */
    public function testIsValidUnitTermReturnsTrueForSimpleUnit(): void
    {
        $this->assertTrue(RegexService::isValidUnitTerm('m', $m));
        assert(isset($m[1]));
        $this->assertSame('m', $m[1]);
    }

    /**
     * Test isValidUnitTerm returns true for a prefixed unit.
     */
    public function testIsValidUnitTermReturnsTrueForPrefixedUnit(): void
    {
        $this->assertTrue(RegexService::isValidUnitTerm('km', $m));
        assert(isset($m[1]));
        $this->assertSame('km', $m[1]);
    }

    /**
     * Test isValidUnitTerm returns true for a unit with ASCII exponent.
     */
    public function testIsValidUnitTermReturnsTrueForUnitWithAsciiExponent(): void
    {
        $this->assertTrue(RegexService::isValidUnitTerm('m2', $m));
        assert(isset($m[1]) && isset($m[2]));
        $this->assertSame('m', $m[1]);
        $this->assertSame('2', $m[2]);
    }

    /**
     * Test isValidUnitTerm returns true for a unit with negative ASCII exponent.
     */
    public function testIsValidUnitTermReturnsTrueForNegativeAsciiExponent(): void
    {
        $this->assertTrue(RegexService::isValidUnitTerm('s-2', $m));
        assert(isset($m[1]) && isset($m[2]));
        $this->assertSame('s', $m[1]);
        $this->assertSame('-2', $m[2]);
    }

    /**
     * Test isValidUnitTerm returns true for a prefixed unit with exponent.
     */
    public function testIsValidUnitTermReturnsTrueForPrefixedUnitWithExponent(): void
    {
        $this->assertTrue(RegexService::isValidUnitTerm('km2', $m));
        assert(isset($m[1]) && isset($m[2]));
        $this->assertSame('km', $m[1]);
        $this->assertSame('2', $m[2]);
    }

    /**
     * Test isValidUnitTerm returns true for a unit with superscript exponent.
     */
    public function testIsValidUnitTermReturnsTrueForSuperscriptExponent(): void
    {
        $this->assertTrue(RegexService::isValidUnitTerm('m²', $m));
        assert(isset($m[1]));
        $this->assertSame('m', $m[1]);
    }

    /**
     * Test isValidUnitTerm returns true for a unit with negative superscript exponent.
     */
    public function testIsValidUnitTermReturnsTrueForNegativeSuperscriptExponent(): void
    {
        $this->assertTrue(RegexService::isValidUnitTerm('s⁻²', $m));
        assert(isset($m[1]));
        $this->assertSame('s', $m[1]);
    }

    /**
     * Test isValidUnitTerm returns true for a multi-letter unit.
     */
    public function testIsValidUnitTermReturnsTrueForMultiLetterUnit(): void
    {
        $this->assertTrue(RegexService::isValidUnitTerm('Hz', $m));
        assert(isset($m[1]));
        $this->assertSame('Hz', $m[1]);
    }

    /**
     * Test isValidUnitTerm returns true for a Unicode unit symbol.
     */
    public function testIsValidUnitTermReturnsTrueForUnicodeSymbol(): void
    {
        $this->assertTrue(RegexService::isValidUnitTerm('Ω', $m));
        assert(isset($m[1]));
        $this->assertSame('Ω', $m[1]);
    }

    /**
     * Test isValidUnitTerm returns true for a Unicode prefix with unit.
     */
    public function testIsValidUnitTermReturnsTrueForUnicodePrefixWithUnit(): void
    {
        $this->assertTrue(RegexService::isValidUnitTerm('μm', $m));
        assert(isset($m[1]));
        $this->assertSame('μm', $m[1]);
    }

    /**
     * Test isValidUnitTerm returns false for digits only.
     */
    public function testIsValidUnitTermReturnsFalseForDigitsOnly(): void
    {
        $this->assertFalse(RegexService::isValidUnitTerm('123', $m));
    }

    /**
     * Test isValidUnitTerm returns false for an empty string.
     */
    public function testIsValidUnitTermReturnsFalseForEmptyString(): void
    {
        $this->assertFalse(RegexService::isValidUnitTerm('', $m));
    }

    /**
     * Test isValidUnitTerm returns false for a compound unit.
     */
    public function testIsValidUnitTermReturnsFalseForCompoundUnit(): void
    {
        $this->assertFalse(RegexService::isValidUnitTerm('kg*m', $m));
    }

    // endregion

    // region isValidDerivedUnitForm1 tests

    /**
     * Test isValidDerivedUnitForm1 returns true for a single unit term.
     */
    public function testIsValidDerivedUnitForm1ReturnsTrueForSingleUnit(): void
    {
        $this->assertTrue(RegexService::isValidDerivedUnitForm1('m'));
        $this->assertTrue(RegexService::isValidDerivedUnitForm1('Hz'));
        $this->assertTrue(RegexService::isValidDerivedUnitForm1('km2'));
    }

    /**
     * Test isValidDerivedUnitForm1 returns true for multiply operator.
     */
    public function testIsValidDerivedUnitForm1ReturnsTrueForMultiply(): void
    {
        $this->assertTrue(RegexService::isValidDerivedUnitForm1('kg*m'));
        $this->assertTrue(RegexService::isValidDerivedUnitForm1('kg.m'));
    }

    /**
     * Test isValidDerivedUnitForm1 returns true for Unicode multiply operators.
     */
    public function testIsValidDerivedUnitForm1ReturnsTrueForUnicodeMultiplyOps(): void
    {
        // Dot operator (U+22C5).
        $this->assertTrue(RegexService::isValidDerivedUnitForm1("kg\u{22C5}m"));
        // Middle dot (U+00B7).
        $this->assertTrue(RegexService::isValidDerivedUnitForm1("kg\u{00B7}m"));
    }

    /**
     * Test isValidDerivedUnitForm1 returns true for divide operator.
     */
    public function testIsValidDerivedUnitForm1ReturnsTrueForDivide(): void
    {
        $this->assertTrue(RegexService::isValidDerivedUnitForm1('m/s'));
        $this->assertTrue(RegexService::isValidDerivedUnitForm1('m/s2'));
    }

    /**
     * Test isValidDerivedUnitForm1 returns true for mixed multiply and divide.
     */
    public function testIsValidDerivedUnitForm1ReturnsTrueForMixedOps(): void
    {
        $this->assertTrue(RegexService::isValidDerivedUnitForm1('kg*m/s2'));
        $this->assertTrue(RegexService::isValidDerivedUnitForm1('kg*m*s-2'));
    }

    /**
     * Test isValidDerivedUnitForm1 returns true for multiple terms.
     */
    public function testIsValidDerivedUnitForm1ReturnsTrueForMultipleTerms(): void
    {
        $this->assertTrue(RegexService::isValidDerivedUnitForm1('kg*m2/s3'));
        $this->assertTrue(RegexService::isValidDerivedUnitForm1('kg*m/s2/A'));
    }

    /**
     * Test isValidDerivedUnitForm1 returns true for prefixed units.
     */
    public function testIsValidDerivedUnitForm1ReturnsTrueForPrefixedUnits(): void
    {
        $this->assertTrue(RegexService::isValidDerivedUnitForm1('kN*m'));
        $this->assertTrue(RegexService::isValidDerivedUnitForm1('km/ms'));
    }

    /**
     * Test isValidDerivedUnitForm1 returns false for empty string.
     */
    public function testIsValidDerivedUnitForm1ReturnsFalseForEmptyString(): void
    {
        $this->assertFalse(RegexService::isValidDerivedUnitForm1(''));
    }

    /**
     * Test isValidDerivedUnitForm1 returns false for digits only.
     */
    public function testIsValidDerivedUnitForm1ReturnsFalseForDigitsOnly(): void
    {
        $this->assertFalse(RegexService::isValidDerivedUnitForm1('123'));
    }

    /**
     * Test isValidDerivedUnitForm1 returns false for form 2 syntax.
     */
    public function testIsValidDerivedUnitForm1ReturnsFalseForForm2Syntax(): void
    {
        $this->assertFalse(RegexService::isValidDerivedUnitForm1('W/(sr*m2)'));
    }

    // endregion

    // region isValidDerivedUnitForm2 tests

    /**
     * Test isValidDerivedUnitForm2 returns true for simple numerator and denominator.
     */
    public function testIsValidDerivedUnitForm2ReturnsTrueForSimple(): void
    {
        $this->assertTrue(RegexService::isValidDerivedUnitForm2('m/(s)', $m));
        assert(isset($m['num']) && isset($m['den']));
        $this->assertSame('m', $m['num']);
        $this->assertSame('s', $m['den']);
    }

    /**
     * Test isValidDerivedUnitForm2 returns true for compound denominator.
     */
    public function testIsValidDerivedUnitForm2ReturnsTrueForCompoundDenominator(): void
    {
        $this->assertTrue(RegexService::isValidDerivedUnitForm2('W/(sr*m2)', $m));
        assert(isset($m['num']) && isset($m['den']));
        $this->assertSame('W', $m['num']);
        $this->assertSame('sr*m2', $m['den']);
    }

    /**
     * Test isValidDerivedUnitForm2 returns true for compound numerator and denominator.
     */
    public function testIsValidDerivedUnitForm2ReturnsTrueForCompoundBoth(): void
    {
        $this->assertTrue(RegexService::isValidDerivedUnitForm2('kg*m/(s2*A)', $m));
        assert(isset($m['num']) && isset($m['den']));
        $this->assertSame('kg*m', $m['num']);
        $this->assertSame('s2*A', $m['den']);
    }

    /**
     * Test isValidDerivedUnitForm2 returns true with dot multiply operator.
     */
    public function testIsValidDerivedUnitForm2ReturnsTrueWithDotOperator(): void
    {
        $this->assertTrue(RegexService::isValidDerivedUnitForm2('kg.m/(s2.A)', $m));
    }

    /**
     * Test isValidDerivedUnitForm2 returns true for real-world unit: J/(mol*K).
     */
    public function testIsValidDerivedUnitForm2ReturnsTrueForJPerMolK(): void
    {
        $this->assertTrue(RegexService::isValidDerivedUnitForm2('J/(mol*K)', $m));
        assert(isset($m['num']) && isset($m['den']));
        $this->assertSame('J', $m['num']);
        $this->assertSame('mol*K', $m['den']);
    }

    /**
     * Test isValidDerivedUnitForm2 returns false for empty string.
     */
    public function testIsValidDerivedUnitForm2ReturnsFalseForEmptyString(): void
    {
        $this->assertFalse(RegexService::isValidDerivedUnitForm2('', $m));
    }

    /**
     * Test isValidDerivedUnitForm2 returns false for form 1 syntax.
     */
    public function testIsValidDerivedUnitForm2ReturnsFalseForForm1Syntax(): void
    {
        $this->assertFalse(RegexService::isValidDerivedUnitForm2('kg*m/s2', $m));
    }

    /**
     * Test isValidDerivedUnitForm2 returns false for missing parentheses.
     */
    public function testIsValidDerivedUnitForm2ReturnsFalseForMissingParentheses(): void
    {
        $this->assertFalse(RegexService::isValidDerivedUnitForm2('W/sr*m2', $m));
    }

    /**
     * Test isValidDerivedUnitForm2 returns false when denominator contains division.
     */
    public function testIsValidDerivedUnitForm2ReturnsFalseForDivisionInDenominator(): void
    {
        $this->assertFalse(RegexService::isValidDerivedUnitForm2('W/(sr/m2)', $m));
    }

    // endregion

    // region isValidDerivedUnit tests

    /**
     * Test isValidDerivedUnit returns true for form 1 units.
     */
    public function testIsValidDerivedUnitReturnsTrueForForm1(): void
    {
        $this->assertTrue(RegexService::isValidDerivedUnit('m'));
        $this->assertTrue(RegexService::isValidDerivedUnit('kg*m/s2'));
        $this->assertTrue(RegexService::isValidDerivedUnit('m/s'));
        $this->assertTrue(RegexService::isValidDerivedUnit('kg*m*s-2'));
    }

    /**
     * Test isValidDerivedUnit returns true for form 2 units.
     */
    public function testIsValidDerivedUnitReturnsTrueForForm2(): void
    {
        $this->assertTrue(RegexService::isValidDerivedUnit('W/(sr*m2)'));
        $this->assertTrue(RegexService::isValidDerivedUnit('kg*m/(s2*A)'));
        $this->assertTrue(RegexService::isValidDerivedUnit('J/(mol*K)'));
    }

    /**
     * Test isValidDerivedUnit returns true for Unicode operators and symbols.
     */
    public function testIsValidDerivedUnitReturnsTrueForUnicodeOperatorsAndSymbols(): void
    {
        // Dot operator.
        $this->assertTrue(RegexService::isValidDerivedUnit("kg\u{22C5}m/s2"));
        // Unicode unit symbol.
        $this->assertTrue(RegexService::isValidDerivedUnit('Ω'));
    }

    /**
     * Test isValidDerivedUnit returns true for units with superscript exponents.
     */
    public function testIsValidDerivedUnitReturnsTrueForSuperscriptExponents(): void
    {
        $this->assertTrue(RegexService::isValidDerivedUnit('m²'));
        $this->assertTrue(RegexService::isValidDerivedUnit('kg*m/s²'));
    }

    /**
     * Test isValidDerivedUnit returns false for empty string.
     */
    public function testIsValidDerivedUnitReturnsFalseForEmptyString(): void
    {
        $this->assertFalse(RegexService::isValidDerivedUnit(''));
    }

    /**
     * Test isValidDerivedUnit returns false for digits only.
     */
    public function testIsValidDerivedUnitReturnsFalseForDigitsOnly(): void
    {
        $this->assertFalse(RegexService::isValidDerivedUnit('123'));
    }

    /**
     * Test isValidDerivedUnit returns false for a quantity (number with unit).
     */
    public function testIsValidDerivedUnitReturnsFalseForQuantity(): void
    {
        $this->assertFalse(RegexService::isValidDerivedUnit('9.81 m/s2'));
    }

    // endregion

    // region isValidQuantity tests

    /**
     * Test isValidQuantity returns true for value with unit.
     */
    public function testIsValidQuantityReturnsTrueForValueWithUnit(): void
    {
        $this->assertTrue(RegexService::isValidQuantity('123.45 km', $m));
        assert(isset($m[1]) && isset($m[2]));
        $this->assertSame('123.45', $m[1]);
        $this->assertSame('km', $m[2]);
    }

    /**
     * Test isValidQuantity returns true for value without space before unit.
     */
    public function testIsValidQuantityReturnsTrueWithoutSpace(): void
    {
        $this->assertTrue(RegexService::isValidQuantity('90deg', $m));
        assert(isset($m[1]) && isset($m[2]));
        $this->assertSame('90', $m[1]);
        $this->assertSame('deg', $m[2]);
    }

    /**
     * Test isValidQuantity returns true for scientific notation.
     */
    public function testIsValidQuantityReturnsTrueForScientificNotation(): void
    {
        $this->assertTrue(RegexService::isValidQuantity('1.5e3 ms', $m));
        assert(isset($m[1]));
        $this->assertSame('1.5e3', $m[1]);
    }

    /**
     * Test isValidQuantity returns true for negative value.
     */
    public function testIsValidQuantityReturnsTrueForNegativeValue(): void
    {
        $this->assertTrue(RegexService::isValidQuantity('-9.81 m/s2', $m));
        assert(isset($m[1]));
        $this->assertSame('-9.81', $m[1]);
    }

    /**
     * Test isValidQuantity returns true for dimensionless value.
     */
    public function testIsValidQuantityReturnsTrueForDimensionless(): void
    {
        $this->assertTrue(RegexService::isValidQuantity('42', $m));
        assert(isset($m[1]));
        $this->assertSame('42', $m[1]);
        $this->assertFalse(isset($m[2]) && $m[2] !== '');
    }

    /**
     * Test isValidQuantity returns true for compound unit with parentheses.
     */
    public function testIsValidQuantityReturnsTrueForCompoundUnit(): void
    {
        $this->assertTrue(RegexService::isValidQuantity('8.314 J/(mol*K)', $m));
        assert(isset($m[1]));
        $this->assertSame('8.314', $m[1]);
    }

    /**
     * Test isValidQuantity returns false for empty string.
     */
    public function testIsValidQuantityReturnsFalseForEmpty(): void
    {
        $this->assertFalse(RegexService::isValidQuantity('', $m));
    }

    /**
     * Test isValidQuantity returns false for non-numeric input.
     */
    public function testIsValidQuantityReturnsFalseForNonNumeric(): void
    {
        $this->assertFalse(RegexService::isValidQuantity('abc', $m));
        $this->assertFalse(RegexService::isValidQuantity('km', $m));
    }

    // endregion

    // region isValidDmsAngle tests

    /**
     * Test isValidDmsAngle returns true for degrees only.
     */
    public function testIsValidDmsAngleReturnsTrueForDegreesOnly(): void
    {
        $this->assertTrue(RegexService::isValidDmsAngle('45°', $m));
        assert(isset($m['deg']));
        $this->assertSame('45', $m['deg']);
    }

    /**
     * Test isValidDmsAngle returns true for degrees and arcminutes.
     */
    public function testIsValidDmsAngleReturnsTrueForDegreesAndMinutes(): void
    {
        $this->assertTrue(RegexService::isValidDmsAngle("45°30'", $m));
        assert(isset($m['deg']) && isset($m['min']));
        $this->assertSame('45', $m['deg']);
        $this->assertSame('30', $m['min']);
    }

    /**
     * Test isValidDmsAngle returns true for full DMS with ASCII quotes.
     */
    public function testIsValidDmsAngleReturnsTrueForFullDmsAscii(): void
    {
        $this->assertTrue(RegexService::isValidDmsAngle('45°30\'15.5"', $m));
        assert(isset($m['deg']) && isset($m['min']) && isset($m['sec']));
        $this->assertSame('45', $m['deg']);
        $this->assertSame('30', $m['min']);
        $this->assertSame('15.5', $m['sec']);
    }

    /**
     * Test isValidDmsAngle returns true for full DMS with Unicode symbols.
     */
    public function testIsValidDmsAngleReturnsTrueForFullDmsUnicode(): void
    {
        $this->assertTrue(RegexService::isValidDmsAngle('12°34′56.78″', $m));
        assert(isset($m['deg']) && isset($m['min']) && isset($m['sec']));
        $this->assertSame('12', $m['deg']);
        $this->assertSame('34', $m['min']);
        $this->assertSame('56.78', $m['sec']);
    }

    /**
     * Test isValidDmsAngle returns true for negative angle.
     */
    public function testIsValidDmsAngleReturnsTrueForNegative(): void
    {
        $this->assertTrue(RegexService::isValidDmsAngle('-90°', $m));
        assert(isset($m['sign']) && isset($m['deg']));
        $this->assertSame('-', $m['sign']);
        $this->assertSame('90', $m['deg']);
    }

    /**
     * Test isValidDmsAngle returns true for positive sign.
     */
    public function testIsValidDmsAngleReturnsTrueForPositiveSign(): void
    {
        $this->assertTrue(RegexService::isValidDmsAngle('+45°', $m));
        assert(isset($m['sign']));
        $this->assertSame('+', $m['sign']);
    }

    /**
     * Test isValidDmsAngle returns true for arcminutes only.
     */
    public function testIsValidDmsAngleReturnsTrueForMinutesOnly(): void
    {
        $this->assertTrue(RegexService::isValidDmsAngle("30'", $m));
        assert(isset($m['min']));
        $this->assertSame('30', $m['min']);
    }

    /**
     * Test isValidDmsAngle returns true for arcseconds only.
     */
    public function testIsValidDmsAngleReturnsTrueForSecondsOnly(): void
    {
        $this->assertTrue(RegexService::isValidDmsAngle('15.5"', $m));
        assert(isset($m['sec']));
        $this->assertSame('15.5', $m['sec']);
    }

    /**
     * Test isValidDmsAngle returns true for decimal degrees.
     */
    public function testIsValidDmsAngleReturnsTrueForDecimalDegrees(): void
    {
        $this->assertTrue(RegexService::isValidDmsAngle('45.5°', $m));
        assert(isset($m['deg']));
        $this->assertSame('45.5', $m['deg']);
    }

    /**
     * Test isValidDmsAngle returns true for scientific notation in seconds.
     */
    public function testIsValidDmsAngleReturnsTrueForScientificNotation(): void
    {
        $this->assertTrue(RegexService::isValidDmsAngle('1.5e2"', $m));
        assert(isset($m['sec']));
        $this->assertSame('1.5e2', $m['sec']);
    }

    /**
     * Test isValidDmsAngle returns true with spaces between components.
     */
    public function testIsValidDmsAngleReturnsTrueWithSpaces(): void
    {
        $this->assertTrue(RegexService::isValidDmsAngle("45° 30' 15\"", $m));
        assert(isset($m['deg']) && isset($m['min']) && isset($m['sec']));
        $this->assertSame('45', $m['deg']);
        $this->assertSame('30', $m['min']);
        $this->assertSame('15', $m['sec']);
    }

    /**
     * Test isValidDmsAngle returns false for plain number without DMS symbols.
     */
    public function testIsValidDmsAngleReturnsFalseForPlainNumber(): void
    {
        $this->assertFalse(RegexService::isValidDmsAngle('45', $m));
    }

    /**
     * Test isValidDmsAngle returns false for invalid format.
     */
    public function testIsValidDmsAngleReturnsFalseForInvalidFormat(): void
    {
        $this->assertFalse(RegexService::isValidDmsAngle('abc°', $m));
    }

    // endregion
}
