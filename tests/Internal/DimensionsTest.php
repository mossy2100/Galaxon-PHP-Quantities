<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Internal;

use DomainException;
use Galaxon\Quantities\Internal\Dimensions;
use Galaxon\Quantities\Internal\UnitTerm;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Dimensions class.
 */
#[CoversClass(Dimensions::class)]
final class DimensionsTest extends TestCase
{
    // region isValid() tests

    /**
     * Test isValid() returns true for single dimension code.
     */
    public function testIsValidSingleCode(): void
    {
        $this->assertTrue(Dimensions::isValid('L'));
        $this->assertTrue(Dimensions::isValid('M'));
        $this->assertTrue(Dimensions::isValid('T'));
    }

    /**
     * Test isValid() returns true for single dimension with exponent.
     */
    public function testIsValidSingleCodeWithExponent(): void
    {
        $this->assertTrue(Dimensions::isValid('L2'));
        $this->assertTrue(Dimensions::isValid('L3'));
        $this->assertTrue(Dimensions::isValid('T-1'));
        $this->assertTrue(Dimensions::isValid('T-2'));
    }

    /**
     * Test isValid() returns true for compound dimension codes.
     */
    public function testIsValidCompoundCode(): void
    {
        $this->assertTrue(Dimensions::isValid('ML'));
        $this->assertTrue(Dimensions::isValid('MLT'));
        $this->assertTrue(Dimensions::isValid('MLT-2'));
        $this->assertTrue(Dimensions::isValid('M2L2T-4'));
    }

    /**
     * Test isValid() returns true for all dimension codes.
     */
    public function testIsValidAllCodes(): void
    {
        foreach (array_keys(Dimensions::DIMENSION_CODES) as $code) {
            $this->assertTrue(Dimensions::isValid($code), "Code '$code' should be valid");
        }
    }

    /**
     * Test isValid() returns true for string '1' (dimensionless).
     */
    public function testIsValidStringOne(): void
    {
        $this->assertTrue(Dimensions::isValid('1'));
    }

    /**
     * Test isValid() returns false for invalid dimension letters.
     */
    public function testIsValidInvalidLetters(): void
    {
        $this->assertFalse(Dimensions::isValid('X'));
        $this->assertFalse(Dimensions::isValid('Z'));
        $this->assertFalse(Dimensions::isValid('B'));
    }

    /**
     * Test isValid() returns false for lowercase letters.
     */
    public function testIsValidLowercaseLetters(): void
    {
        $this->assertFalse(Dimensions::isValid('l'));
        $this->assertFalse(Dimensions::isValid('m'));
        $this->assertFalse(Dimensions::isValid('mlt'));
    }

    /**
     * Test isValid() returns false for invalid format.
     */
    public function testIsValidInvalidFormat(): void
    {
        $this->assertFalse(Dimensions::isValid('2L'));    // Exponent before letter
        $this->assertFalse(Dimensions::isValid('L*M'));   // Invalid character
        $this->assertFalse(Dimensions::isValid('L M'));   // Space
        $this->assertFalse(Dimensions::isValid('L-'));    // Minus without digit
    }

    /**
     * Test isValid() accepts dimension with exponent followed by another dimension.
     */
    public function testIsValidExponentFollowedByDimension(): void
    {
        // L2M is valid: it means L² × M
        $this->assertTrue(Dimensions::isValid('L2M'));
        $this->assertTrue(Dimensions::isValid('M2L2T-2'));
    }

    /**
     * Test isValid() returns false for multi-digit exponents.
     */
    public function testIsValidMultiDigitExponents(): void
    {
        $this->assertFalse(Dimensions::isValid('L10'));
        $this->assertFalse(Dimensions::isValid('L-10'));
    }

    // endregion

    // region decompose() tests

    /**
     * Test decompose() with single dimension code.
     */
    public function testDecomposeSingleCode(): void
    {
        $result = Dimensions::decompose('L');

        $this->assertSame([
            'L' => 1,
        ], $result);
    }

    /**
     * Test decompose() with single dimension code and exponent.
     */
    public function testDecomposeSingleCodeWithExponent(): void
    {
        $this->assertSame([
            'L' => 2,
        ], Dimensions::decompose('L2'));
        $this->assertSame([
            'L' => 3,
        ], Dimensions::decompose('L3'));
        $this->assertSame([
            'T' => -1,
        ], Dimensions::decompose('T-1'));
        $this->assertSame([
            'T' => -2,
        ], Dimensions::decompose('T-2'));
    }

    /**
     * Test decompose() with compound dimension code.
     */
    public function testDecomposeCompoundCode(): void
    {
        $result = Dimensions::decompose('MLT-2');

        $this->assertSame([
            'M' => 1,
            'L' => 1,
            'T' => -2,
        ], $result);
    }

    /**
     * Test decompose() with complex dimension code (energy: M L2 T-2).
     */
    public function testDecomposeComplexCode(): void
    {
        $result = Dimensions::decompose('ML2T-2');

        $this->assertSame([
            'M' => 1,
            'L' => 2,
            'T' => -2,
        ], $result);
    }

    /**
     * Test decompose() throws DomainException for invalid code.
     */
    public function testDecomposeThrowsForInvalidCode(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid dimension code 'XYZ'");

        Dimensions::decompose('XYZ');
    }

    /**
     * Test decompose() returns empty array for string '1' (dimensionless).
     */
    public function testDecomposeReturnsEmptyArrayForEmptyString(): void
    {
        $result = Dimensions::decompose('1');

        $this->assertSame([], $result);
    }

    // endregion

    // region compose() tests

    /**
     * Test compose() with single dimension term.
     */
    public function testComposeSingleTerm(): void
    {
        $result = Dimensions::compose([
            'L' => 1,
        ]);

        $this->assertSame('L', $result);
    }

    /**
     * Test compose() with single dimension term and exponent.
     */
    public function testComposeSingleTermWithExponent(): void
    {
        $this->assertSame('L2', Dimensions::compose([
            'L' => 2,
        ]));
        $this->assertSame('L3', Dimensions::compose([
            'L' => 3,
        ]));
        $this->assertSame('T-1', Dimensions::compose([
            'T' => -1,
        ]));
        $this->assertSame('T-2', Dimensions::compose([
            'T' => -2,
        ]));
    }

    /**
     * Test compose() with multiple terms.
     */
    public function testComposeMultipleTerms(): void
    {
        $result = Dimensions::compose([
            'M' => 1,
            'L' => 1,
            'T' => -2,
        ]);

        $this->assertSame('MLT-2', $result);
    }

    /**
     * Test compose() sorts terms into canonical order.
     */
    public function testComposeSortsTerms(): void
    {
        // Input in wrong order (T before M before L)
        $result = Dimensions::compose([
            'T' => -2,
            'M' => 1,
            'L' => 1,
        ]);

        // Output should be in canonical order (M, L, T)
        $this->assertSame('MLT-2', $result);
    }

    /**
     * Test compose() with empty array returns string '1'.
     */
    public function testComposeEmptyArray(): void
    {
        $result = Dimensions::compose([]);

        $this->assertSame('1', $result);
    }

    /**
     * Test compose() omits exponent of 1.
     */
    public function testComposeOmitsExponentOne(): void
    {
        $result = Dimensions::compose([
            'M' => 1,
            'L' => 2,
        ]);

        $this->assertSame('ML2', $result);
        $this->assertStringNotContainsString('M1', $result);
    }

    // endregion

    // region normalize() tests

    /**
     * Test normalize() with already normalized code.
     */
    public function testNormalizeAlreadyNormalized(): void
    {
        $this->assertSame('MLT-2', Dimensions::normalize('MLT-2'));
    }

    /**
     * Test normalize() reorders terms.
     */
    public function testNormalizeReordersTerms(): void
    {
        // T-2 L M should become M L T-2
        $this->assertSame('MLT-2', Dimensions::normalize('T-2LM'));
    }

    /**
     * Test normalize() with single code.
     */
    public function testNormalizeSingleCode(): void
    {
        $this->assertSame('L', Dimensions::normalize('L'));
        $this->assertSame('L2', Dimensions::normalize('L2'));
    }

    /**
     * Test normalize() throws for invalid code.
     */
    public function testNormalizeThrowsForInvalidCode(): void
    {
        $this->expectException(DomainException::class);

        Dimensions::normalize('invalid');
    }

    // endregion

    // region applyExponent() tests

    /**
     * Test applyExponent() with exponent of 1 returns unchanged.
     */
    public function testApplyExponentOne(): void
    {
        $this->assertSame('L', Dimensions::applyExponent('L', 1));
        $this->assertSame('MLT-2', Dimensions::applyExponent('MLT-2', 1));
    }

    /**
     * Test applyExponent() squares a simple dimension.
     */
    public function testApplyExponentSquare(): void
    {
        $this->assertSame('L2', Dimensions::applyExponent('L', 2));
    }

    /**
     * Test applyExponent() cubes a simple dimension.
     */
    public function testApplyExponentCube(): void
    {
        $this->assertSame('L3', Dimensions::applyExponent('L', 3));
    }

    /**
     * Test applyExponent() with negative exponent.
     */
    public function testApplyExponentNegative(): void
    {
        $this->assertSame('T-2', Dimensions::applyExponent('T-1', 2));
    }

    /**
     * Test applyExponent() with compound dimension (force squared).
     */
    public function testApplyExponentCompound(): void
    {
        // Force (MLT-2) squared = M2L2T-4
        $this->assertSame('M2L2T-4', Dimensions::applyExponent('MLT-2', 2));
    }

    /**
     * Test applyExponent() with zero exponent.
     */
    public function testApplyExponentZero(): void
    {
        // L^0 = dimensionless (all exponents become 0)
        $result = Dimensions::applyExponent('L', 0);

        $this->assertSame('L0', $result);
    }

    /**
     * Test applyExponent() with negative multiplier (inverse).
     */
    public function testApplyExponentInverse(): void
    {
        // Inverse of velocity (LT-1) = L-1T
        $this->assertSame('L-1T', Dimensions::applyExponent('LT-1', -1));
    }

    /**
     * Test applyExponent() throws for invalid dimension.
     */
    public function testApplyExponentThrowsForInvalidDimension(): void
    {
        $this->expectException(DomainException::class);

        Dimensions::applyExponent('invalid', 2);
    }

    // endregion

    // region letterToInt() tests

    /**
     * Test letterToInt() returns correct index for each dimension code.
     */
    public function testLetterToIntValidCodes(): void
    {
        $codes = array_keys(Dimensions::DIMENSION_CODES);
        foreach ($codes as $index => $code) {
            $this->assertSame($index, Dimensions::letterToInt($code), "Code '$code' should have index $index");
        }
    }

    /**
     * Test letterToInt() throws for invalid code.
     */
    public function testLetterToIntThrowsForInvalidCode(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid dimension code letter 'X'");

        Dimensions::letterToInt('X');
    }

    /**
     * Test letterToInt() throws for lowercase.
     */
    public function testLetterToIntThrowsForLowercase(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid dimension code letter 'l'");

        Dimensions::letterToInt('l');
    }

    /**
     * Test letterToInt() throws for empty string.
     */
    public function testLetterToIntThrowsForEmptyString(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid dimension code letter ''");

        Dimensions::letterToInt('');
    }

    /**
     * Test letterToInt() throws for multi-character string.
     */
    public function testLetterToIntThrowsForMultiCharacter(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid dimension code letter 'ML'");

        Dimensions::letterToInt('ML');
    }

    // endregion

    // region getSiUnitTermSymbol() tests

    /**
     * Test getSiUnitTermSymbol() returns correct symbol for each dimension code.
     */
    public function testGetSiBaseUnitSymbolReturnsCorrectSymbols(): void
    {
        $this->assertSame('kg', Dimensions::getSiBaseUnitSymbol('M'));
        $this->assertSame('m', Dimensions::getSiBaseUnitSymbol('L'));
        $this->assertSame('s', Dimensions::getSiBaseUnitSymbol('T'));
        $this->assertSame('A', Dimensions::getSiBaseUnitSymbol('I'));
        $this->assertSame('K', Dimensions::getSiBaseUnitSymbol('H'));
        $this->assertSame('mol', Dimensions::getSiBaseUnitSymbol('N'));
        $this->assertSame('cd', Dimensions::getSiBaseUnitSymbol('J'));
        $this->assertSame('rad', Dimensions::getSiBaseUnitSymbol('A'));
        $this->assertSame('B', Dimensions::getSiBaseUnitSymbol('D'));
        $this->assertSame('XAU', Dimensions::getSiBaseUnitSymbol('C'));
    }

    /**
     * Test getSiUnitTermSymbol() throws for invalid dimension code.
     */
    public function testGetSiBaseUnitSymbolThrowsForInvalidCode(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid dimension code: 'X'");

        Dimensions::getSiBaseUnitSymbol('X');
    }

    /**
     * Test getSiUnitTermSymbol() throws for multi-character string.
     */
    public function testGetSiBaseUnitSymbolThrowsForMultiCharacter(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid dimension code: 'ML'");

        Dimensions::getSiBaseUnitSymbol('ML');
    }

    /**
     * Test getSiUnitTermSymbol() throws for empty string.
     */
    public function testGetSiBaseUnitSymbolThrowsForEmptyString(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid dimension code: ''");

        Dimensions::getSiBaseUnitSymbol('');
    }

    /**
     * Test getSiUnitTermSymbol() throws for lowercase letter.
     */
    public function testGetSiBaseUnitSymbolThrowsForLowercase(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid dimension code: 'm'");

        Dimensions::getSiBaseUnitSymbol('m');
    }

    // endregion

    // region getSiUnitTerm() tests

    /**
     * Test getSiUnitTerm() returns correct UnitTerm for each dimension code.
     */
    public function testGetSiUnitTermReturnsCorrectUnitTerms(): void
    {
        $massUnit = Dimensions::getSiBaseUnitTerm('M');
        $this->assertInstanceOf(UnitTerm::class, $massUnit);
        $this->assertSame('kg', $massUnit->asciiSymbol);

        $lengthUnit = Dimensions::getSiBaseUnitTerm('L');
        $this->assertInstanceOf(UnitTerm::class, $lengthUnit);
        $this->assertSame('m', $lengthUnit->asciiSymbol);

        $timeUnit = Dimensions::getSiBaseUnitTerm('T');
        $this->assertInstanceOf(UnitTerm::class, $timeUnit);
        $this->assertSame('s', $timeUnit->asciiSymbol);
    }

    /**
     * Test getSiUnitTerm() returns UnitTerm with correct unit reference.
     */
    public function testGetSiUnitTermHasCorrectUnit(): void
    {
        $lengthUnit = Dimensions::getSiBaseUnitTerm('L');

        $this->assertSame('meter', $lengthUnit->unit->name);
        $this->assertSame('L', $lengthUnit->unit->dimension);
    }

    /**
     * Test getSiUnitTerm() throws for invalid dimension code.
     */
    public function testGetSiUnitTermThrowsForInvalidCode(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid dimension code: 'X'");

        Dimensions::getSiBaseUnitTerm('X');
    }

    /**
     * Test getSiUnitTerm() throws for multi-character string.
     */
    public function testGetSiUnitTermThrowsForMultiCharacter(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid dimension code: 'ML'");

        Dimensions::getSiBaseUnitTerm('ML');
    }

    // endregion

    // region getSiBaseUnitSymbols() tests

    /**
     * Test getSiBaseUnitSymbols() returns all expected SI base unit symbols.
     */
    public function testGetSiBaseUnitSymbolsContainsExpectedSymbols(): void
    {
        $symbols = Dimensions::getSiBaseUnitSymbols();

        $this->assertContains('kg', $symbols);
        $this->assertContains('m', $symbols);
        $this->assertContains('s', $symbols);
        $this->assertContains('A', $symbols);
        $this->assertContains('mol', $symbols);
        $this->assertContains('K', $symbols);
        $this->assertContains('cd', $symbols);
    }

    /**
     * Test getSiBaseUnitSymbols() returns correct count matching DIMENSION_CODES.
     */
    public function testGetSiBaseUnitSymbolsCountMatchesDimensionCodes(): void
    {
        $symbols = Dimensions::getSiBaseUnitSymbols();

        $this->assertCount(count(Dimensions::DIMENSION_CODES), $symbols);
    }

    // endregion

    // region Round-trip tests

    /**
     * Test decompose() and compose() are inverse operations.
     *
     */
    public function testDecomposeComposeRoundTrip(): void
    {
        $codes = ['L', 'L2', 'T-1', 'MLT-2', 'M2L2T-4', 'MLIT-2'];

        foreach ($codes as $code) {
            $normalized = Dimensions::normalize($code);
            $decomposed = Dimensions::decompose($normalized);
            $composed = Dimensions::compose($decomposed);

            $this->assertSame($normalized, $composed, "Round-trip failed for '$code'");
        }
    }

    // endregion
}
