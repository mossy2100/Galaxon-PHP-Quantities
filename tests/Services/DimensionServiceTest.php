<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Services;

use DomainException;
use Galaxon\Quantities\Internal\DerivedUnit;
use Galaxon\Quantities\Internal\UnitTerm;
use Galaxon\Quantities\Services\DimensionService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for DimensionService class.
 */
#[CoversClass(DimensionService::class)]
final class DimensionServiceTest extends TestCase
{
    // region isValid() tests

    /**
     * Test isValid() returns true for single dimension code.
     */
    public function testIsValidSingleCode(): void
    {
        $this->assertTrue(DimensionService::isValid('L'));
        $this->assertTrue(DimensionService::isValid('M'));
        $this->assertTrue(DimensionService::isValid('T'));
    }

    /**
     * Test isValid() returns true for single dimension with exponent.
     */
    public function testIsValidSingleCodeWithExponent(): void
    {
        $this->assertTrue(DimensionService::isValid('L2'));
        $this->assertTrue(DimensionService::isValid('L3'));
        $this->assertTrue(DimensionService::isValid('T-1'));
        $this->assertTrue(DimensionService::isValid('T-2'));
    }

    /**
     * Test isValid() returns true for compound dimension codes.
     */
    public function testIsValidCompoundCode(): void
    {
        $this->assertTrue(DimensionService::isValid('ML'));
        $this->assertTrue(DimensionService::isValid('MLT'));
        $this->assertTrue(DimensionService::isValid('MLT-2'));
        $this->assertTrue(DimensionService::isValid('M2L2T-4'));
    }

    /**
     * Test isValid() returns true for all dimension codes.
     */
    public function testIsValidAllCodes(): void
    {
        foreach (array_keys(DimensionService::DIMENSION_CODES) as $code) {
            $this->assertTrue(DimensionService::isValid($code), "Code '$code' should be valid");
        }
    }

    /**
     * Test isValid() returns true for string '' (dimensionless).
     */
    public function testIsValidEmptyString(): void
    {
        $this->assertTrue(DimensionService::isValid(''));
    }

    /**
     * Test isValid() returns false for invalid dimension letters.
     */
    public function testIsValidInvalidLetters(): void
    {
        $this->assertFalse(DimensionService::isValid('X'));
        $this->assertFalse(DimensionService::isValid('Z'));
        $this->assertFalse(DimensionService::isValid('B'));
    }

    /**
     * Test isValid() returns false for lowercase letters.
     */
    public function testIsValidLowercaseLetters(): void
    {
        $this->assertFalse(DimensionService::isValid('l'));
        $this->assertFalse(DimensionService::isValid('m'));
        $this->assertFalse(DimensionService::isValid('mlt'));
    }

    /**
     * Test isValid() returns false for invalid format.
     */
    public function testIsValidInvalidFormat(): void
    {
        $this->assertFalse(DimensionService::isValid('2L'));    // Exponent before letter
        $this->assertFalse(DimensionService::isValid('L*M'));   // Invalid character
        $this->assertFalse(DimensionService::isValid('L M'));   // Space
        $this->assertFalse(DimensionService::isValid('L-'));    // Minus without digit
    }

    /**
     * Test isValid() accepts dimension with exponent followed by another dimension.
     */
    public function testIsValidExponentFollowedByDimension(): void
    {
        // L2M is valid: it means L² × M
        $this->assertTrue(DimensionService::isValid('L2M'));
        $this->assertTrue(DimensionService::isValid('M2L2T-2'));
    }

    /**
     * Test isValid() returns false for multi-digit exponents.
     */
    public function testIsValidMultiDigitExponents(): void
    {
        $this->assertFalse(DimensionService::isValid('L10'));
        $this->assertFalse(DimensionService::isValid('L-10'));
    }

    // endregion

    // region decompose() tests

    /**
     * Test decompose() with single dimension code.
     */
    public function testDecomposeSingleCode(): void
    {
        $result = DimensionService::decompose('L');

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
        ], DimensionService::decompose('L2'));
        $this->assertSame([
            'L' => 3,
        ], DimensionService::decompose('L3'));
        $this->assertSame([
            'T' => -1,
        ], DimensionService::decompose('T-1'));
        $this->assertSame([
            'T' => -2,
        ], DimensionService::decompose('T-2'));
    }

    /**
     * Test decompose() with compound dimension code.
     */
    public function testDecomposeCompoundCode(): void
    {
        $result = DimensionService::decompose('MLT-2');

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
        $result = DimensionService::decompose('ML2T-2');

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

        DimensionService::decompose('XYZ');
    }

    /**
     * Test decompose() returns empty array for string '' (dimensionless).
     */
    public function testDecomposeReturnsEmptyArrayForEmptyString(): void
    {
        $result = DimensionService::decompose('');

        $this->assertSame([], $result);
    }

    // endregion

    // region compose() tests

    /**
     * Test compose() with single dimension term.
     */
    public function testComposeSingleTerm(): void
    {
        $result = DimensionService::compose([
            'L' => 1,
        ]);

        $this->assertSame('L', $result);
    }

    /**
     * Test compose() with single dimension term and exponent.
     */
    public function testComposeSingleTermWithExponent(): void
    {
        $this->assertSame('L2', DimensionService::compose([
            'L' => 2,
        ]));
        $this->assertSame('L3', DimensionService::compose([
            'L' => 3,
        ]));
        $this->assertSame('T-1', DimensionService::compose([
            'T' => -1,
        ]));
        $this->assertSame('T-2', DimensionService::compose([
            'T' => -2,
        ]));
    }

    /**
     * Test compose() with multiple terms.
     */
    public function testComposeMultipleTerms(): void
    {
        $result = DimensionService::compose([
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
        $result = DimensionService::compose([
            'T' => -2,
            'M' => 1,
            'L' => 1,
        ]);

        // Output should be in canonical order (M, L, T)
        $this->assertSame('MLT-2', $result);
    }

    /**
     * Test compose() with empty array returns empty string.
     */
    public function testComposeEmptyArray(): void
    {
        $result = DimensionService::compose([]);

        $this->assertSame('', $result);
    }

    /**
     * Test compose() omits exponent of 1.
     */
    public function testComposeOmitsExponentOne(): void
    {
        $result = DimensionService::compose([
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
        $this->assertSame('MLT-2', DimensionService::normalize('MLT-2'));
    }

    /**
     * Test normalize() reorders terms.
     */
    public function testNormalizeReordersTerms(): void
    {
        // T-2 L M should become M L T-2
        $this->assertSame('MLT-2', DimensionService::normalize('T-2LM'));
    }

    /**
     * Test normalize() with single code.
     */
    public function testNormalizeSingleCode(): void
    {
        $this->assertSame('L', DimensionService::normalize('L'));
        $this->assertSame('L2', DimensionService::normalize('L2'));
    }

    /**
     * Test normalize() throws for invalid code.
     */
    public function testNormalizeThrowsForInvalidCode(): void
    {
        $this->expectException(DomainException::class);

        DimensionService::normalize('invalid');
    }

    // endregion

    // region applyExponent() tests

    /**
     * Test applyExponent() with exponent of 1 returns unchanged.
     */
    public function testApplyExponentOne(): void
    {
        $this->assertSame('L', DimensionService::applyExponent('L', 1));
        $this->assertSame('MLT-2', DimensionService::applyExponent('MLT-2', 1));
    }

    /**
     * Test applyExponent() squares a simple dimension.
     */
    public function testApplyExponentSquare(): void
    {
        $this->assertSame('L2', DimensionService::applyExponent('L', 2));
    }

    /**
     * Test applyExponent() cubes a simple dimension.
     */
    public function testApplyExponentCube(): void
    {
        $this->assertSame('L3', DimensionService::applyExponent('L', 3));
    }

    /**
     * Test applyExponent() with negative exponent.
     */
    public function testApplyExponentNegative(): void
    {
        $this->assertSame('T-2', DimensionService::applyExponent('T-1', 2));
    }

    /**
     * Test applyExponent() with compound dimension (force squared).
     */
    public function testApplyExponentCompound(): void
    {
        // Force (MLT-2) squared = M2L2T-4
        $this->assertSame('M2L2T-4', DimensionService::applyExponent('MLT-2', 2));
    }

    /**
     * Test applyExponent() with zero exponent.
     */
    public function testApplyExponentZero(): void
    {
        // L^0 = dimensionless (all exponents become 0)
        $result = DimensionService::applyExponent('L', 0);

        $this->assertSame('L0', $result);
    }

    /**
     * Test applyExponent() with negative multiplier (inverse).
     */
    public function testApplyExponentInverse(): void
    {
        // Inverse of velocity (LT-1) = L-1T
        $this->assertSame('L-1T', DimensionService::applyExponent('LT-1', -1));
    }

    /**
     * Test applyExponent() throws for invalid dimension.
     */
    public function testApplyExponentThrowsForInvalidDimension(): void
    {
        $this->expectException(DomainException::class);

        DimensionService::applyExponent('invalid', 2);
    }

    // endregion

    // region letterToInt() tests

    /**
     * Test letterToInt() returns correct index for each dimension code.
     */
    public function testLetterToIntValidCodes(): void
    {
        $codes = array_keys(DimensionService::DIMENSION_CODES);
        foreach ($codes as $index => $code) {
            $this->assertSame($index, DimensionService::letterToInt($code), "Code '$code' should have index $index");
        }
    }

    /**
     * Test letterToInt() throws for invalid code.
     */
    public function testLetterToIntThrowsForInvalidCode(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid dimension code letter 'X'");

        DimensionService::letterToInt('X');
    }

    /**
     * Test letterToInt() throws for lowercase.
     */
    public function testLetterToIntThrowsForLowercase(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid dimension code letter 'l'");

        DimensionService::letterToInt('l');
    }

    /**
     * Test letterToInt() throws for empty string.
     */
    public function testLetterToIntThrowsForEmptyString(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid dimension code letter ''");

        DimensionService::letterToInt('');
    }

    /**
     * Test letterToInt() throws for multi-character string.
     */
    public function testLetterToIntThrowsForMultiCharacter(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid dimension code letter 'ML'");

        DimensionService::letterToInt('ML');
    }

    // endregion

    // region getBaseUnitSymbol() tests

    /**
     * Test getBaseUnitSymbol() returns correct SI symbol for each dimension code.
     */
    public function testGetBaseUnitSymbolReturnsCorrectSymbols(): void
    {
        $this->assertSame('kg', DimensionService::getBaseUnitSymbol('M', true));
        $this->assertSame('m', DimensionService::getBaseUnitSymbol('L', true));
        $this->assertSame('s', DimensionService::getBaseUnitSymbol('T', true));
        $this->assertSame('A', DimensionService::getBaseUnitSymbol('I', true));
        $this->assertSame('K', DimensionService::getBaseUnitSymbol('H', true));
        $this->assertSame('mol', DimensionService::getBaseUnitSymbol('N', true));
        $this->assertSame('cd', DimensionService::getBaseUnitSymbol('J', true));
        $this->assertSame('rad', DimensionService::getBaseUnitSymbol('A', true));
        $this->assertSame('B', DimensionService::getBaseUnitSymbol('D', true));
        $this->assertSame('XAU', DimensionService::getBaseUnitSymbol('C', true));
    }

    /**
     * Test getBaseUnitSymbol() throws for invalid dimension code.
     */
    public function testGetBaseUnitSymbolThrowsForInvalidCode(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid dimension code letter: 'X'");

        DimensionService::getBaseUnitSymbol('X', true);
    }

    /**
     * Test getBaseUnitSymbol() throws for multi-character string.
     */
    public function testGetBaseUnitSymbolThrowsForMultiCharacter(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid dimension code letter: 'ML'");

        DimensionService::getBaseUnitSymbol('ML', true);
    }

    /**
     * Test getBaseUnitSymbol() throws for empty string.
     */
    public function testGetBaseUnitSymbolThrowsForEmptyString(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid dimension code letter: ''");

        DimensionService::getBaseUnitSymbol('', true);
    }

    /**
     * Test getBaseUnitSymbol() throws for lowercase letter.
     */
    public function testGetBaseUnitSymbolThrowsForLowercase(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid dimension code letter: 'm'");

        DimensionService::getBaseUnitSymbol('m', true);
    }

    /**
     * Test getBaseUnitSymbol() returns English base unit symbols for dimensions that have them.
     */
    public function testGetBaseUnitSymbolReturnsEnglishSymbols(): void
    {
        $this->assertSame('lb', DimensionService::getBaseUnitSymbol('M', false));
        $this->assertSame('ft', DimensionService::getBaseUnitSymbol('L', false));
        $this->assertSame('deg', DimensionService::getBaseUnitSymbol('A', false));
        $this->assertSame('degR', DimensionService::getBaseUnitSymbol('H', false));
    }

    /**
     * Test getBaseUnitSymbol() falls back to SI for dimensions without English base units.
     */
    public function testGetBaseUnitSymbolEnglishFallsBackToSi(): void
    {
        $this->assertSame('s', DimensionService::getBaseUnitSymbol('T', false));
        $this->assertSame('A', DimensionService::getBaseUnitSymbol('I', false));
        $this->assertSame('mol', DimensionService::getBaseUnitSymbol('N', false));
        $this->assertSame('cd', DimensionService::getBaseUnitSymbol('J', false));
        $this->assertSame('B', DimensionService::getBaseUnitSymbol('D', false));
        $this->assertSame('XAU', DimensionService::getBaseUnitSymbol('C', false));
    }

    // endregion

    // region getBaseUnitTerm() tests

    /**
     * Test getBaseUnitTerm() returns correct SI UnitTerm for each dimension code.
     */
    public function testGetBaseUnitTermReturnsCorrectUnitTerms(): void
    {
        $massUnit = DimensionService::getBaseUnitTerm('M', true);
        $this->assertInstanceOf(UnitTerm::class, $massUnit);
        $this->assertSame('kg', $massUnit->asciiSymbol);

        $lengthUnit = DimensionService::getBaseUnitTerm('L', true);
        $this->assertInstanceOf(UnitTerm::class, $lengthUnit);
        $this->assertSame('m', $lengthUnit->asciiSymbol);

        $timeUnit = DimensionService::getBaseUnitTerm('T', true);
        $this->assertInstanceOf(UnitTerm::class, $timeUnit);
        $this->assertSame('s', $timeUnit->asciiSymbol);
    }

    /**
     * Test getBaseUnitTerm() returns UnitTerm with correct unit reference.
     */
    public function testGetBaseUnitTermHasCorrectUnit(): void
    {
        $lengthUnit = DimensionService::getBaseUnitTerm('L', true);

        $this->assertSame('meter', $lengthUnit->unit->name);
        $this->assertSame('L', $lengthUnit->unit->dimension);
    }

    /**
     * Test getBaseUnitTerm() throws for invalid dimension code.
     */
    public function testGetBaseUnitTermThrowsForInvalidCode(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid dimension code letter: 'X'");

        DimensionService::getBaseUnitTerm('X', true);
    }

    /**
     * Test getBaseUnitTerm() throws for multi-character string.
     */
    public function testGetBaseUnitTermThrowsForMultiCharacter(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid dimension code letter: 'ML'");

        DimensionService::getBaseUnitTerm('ML', true);
    }

    /**
     * Test getBaseUnitTerm() returns correct English UnitTerms.
     */
    public function testGetBaseUnitTermReturnsEnglishUnitTerms(): void
    {
        $massUnit = DimensionService::getBaseUnitTerm('M', false);
        $this->assertInstanceOf(UnitTerm::class, $massUnit);
        $this->assertSame('lb', $massUnit->asciiSymbol);

        $lengthUnit = DimensionService::getBaseUnitTerm('L', false);
        $this->assertInstanceOf(UnitTerm::class, $lengthUnit);
        $this->assertSame('ft', $lengthUnit->asciiSymbol);

        $tempUnit = DimensionService::getBaseUnitTerm('H', false);
        $this->assertInstanceOf(UnitTerm::class, $tempUnit);
        $this->assertSame('degR', $tempUnit->asciiSymbol);
    }

    /**
     * Test getBaseUnitTerm() falls back to SI for dimensions without English base units.
     */
    public function testGetBaseUnitTermEnglishFallsBackToSi(): void
    {
        $timeUnit = DimensionService::getBaseUnitTerm('T', false);
        $this->assertInstanceOf(UnitTerm::class, $timeUnit);
        $this->assertSame('s', $timeUnit->asciiSymbol);
    }

    // endregion

    // region getBaseDerivedUnit() tests

    /**
     * Test getBaseDerivedUnit() returns a DerivedUnit for a single dimension.
     */
    public function testGetBaseDerivedUnitReturnsDerivedUnitForSingleDimension(): void
    {
        $result = DimensionService::getBaseDerivedUnit('L', true);

        $this->assertInstanceOf(DerivedUnit::class, $result);
        $this->assertSame('m', $result->asciiSymbol);
    }

    /**
     * Test getBaseDerivedUnit() returns correct SI unit for force dimension (MLT-2).
     */
    public function testGetBaseDerivedUnitReturnsCorrectSiUnitForForce(): void
    {
        $result = DimensionService::getBaseDerivedUnit('MLT-2', true);

        $this->assertSame('kg*m/s2', $result->asciiSymbol);
    }

    /**
     * Test getBaseDerivedUnit() returns correct SI unit for energy dimension (ML2T-2).
     */
    public function testGetBaseDerivedUnitReturnsCorrectSiUnitForEnergy(): void
    {
        $result = DimensionService::getBaseDerivedUnit('ML2T-2', true);

        $this->assertSame('kg*m2/s2', $result->asciiSymbol);
    }

    /**
     * Test getBaseDerivedUnit() returns correct SI unit for velocity dimension (LT-1).
     */
    public function testGetBaseDerivedUnitReturnsCorrectSiUnitForVelocity(): void
    {
        $result = DimensionService::getBaseDerivedUnit('LT-1', true);

        $this->assertSame('m/s', $result->asciiSymbol);
    }

    /**
     * Test getBaseDerivedUnit() returns correct SI unit for area dimension (L2).
     */
    public function testGetBaseDerivedUnitReturnsCorrectSiUnitForArea(): void
    {
        $result = DimensionService::getBaseDerivedUnit('L2', true);

        $this->assertSame('m2', $result->asciiSymbol);
    }

    /**
     * Test getBaseDerivedUnit() returns dimensionless DerivedUnit for ''.
     */
    public function testGetBaseDerivedUnitReturnsDimensionlessForEmptyString(): void
    {
        $result = DimensionService::getBaseDerivedUnit('', true);

        $this->assertInstanceOf(DerivedUnit::class, $result);
        $this->assertSame('', $result->asciiSymbol);
    }

    /**
     * Test getBaseDerivedUnit() throws for invalid dimension code.
     */
    public function testGetBaseDerivedUnitThrowsForInvalidCode(): void
    {
        $this->expectException(DomainException::class);

        DimensionService::getBaseDerivedUnit('XYZ', true);
    }

    /**
     * Test getBaseDerivedUnit() returns correct English unit for force dimension (MLT-2).
     */
    public function testGetBaseDerivedUnitReturnsCorrectEnglishUnitForForce(): void
    {
        $result = DimensionService::getBaseDerivedUnit('MLT-2', false);

        $this->assertSame('lb*ft/s2', $result->asciiSymbol);
    }

    /**
     * Test getBaseDerivedUnit() returns correct English unit for velocity dimension (LT-1).
     */
    public function testGetBaseDerivedUnitReturnsCorrectEnglishUnitForVelocity(): void
    {
        $result = DimensionService::getBaseDerivedUnit('LT-1', false);

        $this->assertSame('ft/s', $result->asciiSymbol);
    }

    /**
     * Test getBaseDerivedUnit() returns correct English unit for a single dimension.
     */
    public function testGetBaseDerivedUnitReturnsEnglishUnitForSingleDimension(): void
    {
        $result = DimensionService::getBaseDerivedUnit('L', false);

        $this->assertInstanceOf(DerivedUnit::class, $result);
        $this->assertSame('ft', $result->asciiSymbol);
    }

    /**
     * Test getBaseDerivedUnit() falls back to SI for dimensions without English base units.
     */
    public function testGetBaseDerivedUnitEnglishFallsBackToSiForTime(): void
    {
        // Time has no English base unit, so it should fall back to 's'.
        $result = DimensionService::getBaseDerivedUnit('T', false);

        $this->assertSame('s', $result->asciiSymbol);
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
            $normalized = DimensionService::normalize($code);
            $decomposed = DimensionService::decompose($normalized);
            $composed = DimensionService::compose($decomposed);

            $this->assertSame($normalized, $composed, "Round-trip failed for '$code'");
        }
    }

    // endregion
}
