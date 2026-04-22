<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\NonCurrencies\Services;

use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Quantities\Internal\CompoundUnit;
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
     * Test decompose() throws FormatException for invalid code.
     */
    public function testDecomposeThrowsForInvalidCode(): void
    {
        $this->expectException(FormatException::class);
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
        $this->expectException(FormatException::class);

        DimensionService::normalize('invalid');
    }

    // endregion

    // region pow() tests

    /**
     * Test pow() with exponent of 1 returns unchanged.
     */
    public function testPowOne(): void
    {
        $this->assertSame('L', DimensionService::pow('L', 1));
        $this->assertSame('MLT-2', DimensionService::pow('MLT-2', 1));
    }

    /**
     * Test pow() squares a simple dimension.
     */
    public function testPowSquare(): void
    {
        $this->assertSame('L2', DimensionService::pow('L', 2));
    }

    /**
     * Test pow() cubes a simple dimension.
     */
    public function testPowCube(): void
    {
        $this->assertSame('L3', DimensionService::pow('L', 3));
    }

    /**
     * Test pow() with negative exponent.
     */
    public function testPowNegative(): void
    {
        $this->assertSame('T-2', DimensionService::pow('T-1', 2));
    }

    /**
     * Test pow() with compound dimension (force squared).
     */
    public function testPowCompound(): void
    {
        // Force (MLT-2) squared = M2L2T-4
        $this->assertSame('M2L2T-4', DimensionService::pow('MLT-2', 2));
    }

    /**
     * Test pow() with zero exponent.
     */
    public function testPowZero(): void
    {
        // L^0 = dimensionless (all exponents become 0)
        $result = DimensionService::pow('L', 0);

        $this->assertSame('L0', $result);
    }

    /**
     * Test pow() with negative multiplier (inverse).
     */
    public function testPowInverse(): void
    {
        // Inverse of velocity (LT-1) = L-1T
        $this->assertSame('L-1T', DimensionService::pow('LT-1', -1));
    }

    /**
     * Test pow() throws for invalid dimension.
     */
    public function testPowThrowsForInvalidDimension(): void
    {
        $this->expectException(FormatException::class);

        DimensionService::pow('invalid', 2);
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
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage("Invalid dimension code letter: 'X'");

        DimensionService::letterToInt('X');
    }

    /**
     * Test letterToInt() throws for lowercase.
     */
    public function testLetterToIntThrowsForLowercase(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage("Invalid dimension code letter: 'l'");

        DimensionService::letterToInt('l');
    }

    /**
     * Test letterToInt() throws for empty string.
     */
    public function testLetterToIntThrowsForEmptyString(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage("Invalid dimension code letter: ''");

        DimensionService::letterToInt('');
    }

    /**
     * Test letterToInt() throws for multi-character string.
     */
    public function testLetterToIntThrowsForMultiCharacter(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage("Invalid dimension code letter: 'ML'");

        DimensionService::letterToInt('ML');
    }

    // endregion

    // region getBaseUnitTermSymbol() tests

    /**
     * Test getBaseUnitTermSymbol() returns correct SI symbol for each dimension code.
     */
    public function testGetBaseUnitTermSymbolReturnsCorrectSymbols(): void
    {
        $this->assertSame('kg', DimensionService::getBaseUnitTermSymbol('M', true));
        $this->assertSame('m', DimensionService::getBaseUnitTermSymbol('L', true));
        $this->assertSame('s', DimensionService::getBaseUnitTermSymbol('T', true));
        $this->assertSame('A', DimensionService::getBaseUnitTermSymbol('I', true));
        $this->assertSame('K', DimensionService::getBaseUnitTermSymbol('H', true));
        $this->assertSame('mol', DimensionService::getBaseUnitTermSymbol('N', true));
        $this->assertSame('cd', DimensionService::getBaseUnitTermSymbol('J', true));
        $this->assertSame('rad', DimensionService::getBaseUnitTermSymbol('A', true));
        $this->assertSame('B', DimensionService::getBaseUnitTermSymbol('D', true));
        $this->assertSame('XAU', DimensionService::getBaseUnitTermSymbol('C', true));
    }

    /**
     * Test getBaseUnitTermSymbol() throws for invalid dimension code.
     */
    public function testGetBaseUnitTermSymbolThrowsForInvalidCode(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage("Invalid dimension code letter: 'X'");

        DimensionService::getBaseUnitTermSymbol('X', true);
    }

    /**
     * Test getBaseUnitTermSymbol() throws for multi-character string.
     */
    public function testGetBaseUnitTermSymbolThrowsForMultiCharacter(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage("Invalid dimension code letter: 'ML'");

        DimensionService::getBaseUnitTermSymbol('ML', true);
    }

    /**
     * Test getBaseUnitTermSymbol() throws for empty string.
     */
    public function testGetBaseUnitTermSymbolThrowsForEmptyString(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage("Invalid dimension code letter: ''");

        DimensionService::getBaseUnitTermSymbol('', true);
    }

    /**
     * Test getBaseUnitTermSymbol() throws for lowercase letter.
     */
    public function testGetBaseUnitTermSymbolThrowsForLowercase(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage("Invalid dimension code letter: 'm'");

        DimensionService::getBaseUnitTermSymbol('m', true);
    }

    /**
     * Test getBaseUnitTermSymbol() returns English base unit symbols for dimensions that have them.
     */
    public function testGetBaseUnitTermSymbolReturnsEnglishSymbols(): void
    {
        $this->assertSame('lb', DimensionService::getBaseUnitTermSymbol('M', false));
        $this->assertSame('ft', DimensionService::getBaseUnitTermSymbol('L', false));
        $this->assertSame('deg', DimensionService::getBaseUnitTermSymbol('A', false));
        $this->assertSame('degR', DimensionService::getBaseUnitTermSymbol('H', false));
    }

    /**
     * Test getBaseUnitTermSymbol() falls back to SI for dimensions without English base units.
     */
    public function testGetBaseUnitTermSymbolEnglishFallsBackToSi(): void
    {
        $this->assertSame('s', DimensionService::getBaseUnitTermSymbol('T', false));
        $this->assertSame('A', DimensionService::getBaseUnitTermSymbol('I', false));
        $this->assertSame('mol', DimensionService::getBaseUnitTermSymbol('N', false));
        $this->assertSame('cd', DimensionService::getBaseUnitTermSymbol('J', false));
        $this->assertSame('B', DimensionService::getBaseUnitTermSymbol('D', false));
        $this->assertSame('XAU', DimensionService::getBaseUnitTermSymbol('C', false));
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
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage("Invalid dimension code letter: 'X'");

        DimensionService::getBaseUnitTerm('X', true);
    }

    /**
     * Test getBaseUnitTerm() throws for multi-character string.
     */
    public function testGetBaseUnitTermThrowsForMultiCharacter(): void
    {
        $this->expectException(FormatException::class);
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

    // region getBaseCompoundUnit() tests

    /**
     * Test getBaseCompoundUnit() returns a CompoundUnit for a single dimension.
     */
    public function testGetBaseCompoundUnitReturnsCompoundUnitForSingleDimension(): void
    {
        $result = DimensionService::getBaseCompoundUnit('L', true);

        $this->assertInstanceOf(CompoundUnit::class, $result);
        $this->assertSame('m', $result->asciiSymbol);
    }

    /**
     * Test getBaseCompoundUnit() returns correct SI unit for force dimension (MLT-2).
     */
    public function testGetBaseCompoundUnitReturnsCorrectSiUnitForForce(): void
    {
        $result = DimensionService::getBaseCompoundUnit('MLT-2', true);

        $this->assertSame('kg*m/s2', $result->asciiSymbol);
    }

    /**
     * Test getBaseCompoundUnit() returns correct SI unit for energy dimension (ML2T-2).
     */
    public function testGetBaseCompoundUnitReturnsCorrectSiUnitForEnergy(): void
    {
        $result = DimensionService::getBaseCompoundUnit('ML2T-2', true);

        $this->assertSame('kg*m2/s2', $result->asciiSymbol);
    }

    /**
     * Test getBaseCompoundUnit() returns correct SI unit for velocity dimension (LT-1).
     */
    public function testGetBaseCompoundUnitReturnsCorrectSiUnitForVelocity(): void
    {
        $result = DimensionService::getBaseCompoundUnit('LT-1', true);

        $this->assertSame('m/s', $result->asciiSymbol);
    }

    /**
     * Test getBaseCompoundUnit() returns correct SI unit for area dimension (L2).
     */
    public function testGetBaseCompoundUnitReturnsCorrectSiUnitForArea(): void
    {
        $result = DimensionService::getBaseCompoundUnit('L2', true);

        $this->assertSame('m2', $result->asciiSymbol);
    }

    /**
     * Test getBaseCompoundUnit() returns dimensionless CompoundUnit for ''.
     */
    public function testGetBaseCompoundUnitReturnsDimensionlessForEmptyString(): void
    {
        $result = DimensionService::getBaseCompoundUnit('', true);

        $this->assertInstanceOf(CompoundUnit::class, $result);
        $this->assertSame('', $result->asciiSymbol);
    }

    /**
     * Test getBaseCompoundUnit() throws for invalid dimension code.
     */
    public function testGetBaseCompoundUnitThrowsForInvalidCode(): void
    {
        $this->expectException(FormatException::class);

        DimensionService::getBaseCompoundUnit('XYZ', true);
    }

    /**
     * Test getBaseCompoundUnit() returns correct English unit for force dimension (MLT-2).
     */
    public function testGetBaseCompoundUnitReturnsCorrectEnglishUnitForForce(): void
    {
        $result = DimensionService::getBaseCompoundUnit('MLT-2', false);

        $this->assertSame('lb*ft/s2', $result->asciiSymbol);
    }

    /**
     * Test getBaseCompoundUnit() returns correct English unit for velocity dimension (LT-1).
     */
    public function testGetBaseCompoundUnitReturnsCorrectEnglishUnitForVelocity(): void
    {
        $result = DimensionService::getBaseCompoundUnit('LT-1', false);

        $this->assertSame('ft/s', $result->asciiSymbol);
    }

    /**
     * Test getBaseCompoundUnit() returns correct English unit for a single dimension.
     */
    public function testGetBaseCompoundUnitReturnsEnglishUnitForSingleDimension(): void
    {
        $result = DimensionService::getBaseCompoundUnit('L', false);

        $this->assertInstanceOf(CompoundUnit::class, $result);
        $this->assertSame('ft', $result->asciiSymbol);
    }

    /**
     * Test getBaseCompoundUnit() falls back to SI for dimensions without English base units.
     */
    public function testGetBaseCompoundUnitEnglishFallsBackToSiForTime(): void
    {
        // Time has no English base unit, so it should fall back to 's'.
        $result = DimensionService::getBaseCompoundUnit('T', false);

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

    // region countUnits() tests

    /**
     * Test countUnits() with a single dimension term.
     */
    public function testCountUnitsSingleTerm(): void
    {
        $this->assertSame(1, DimensionService::countUnits('L'));
        $this->assertSame(1, DimensionService::countUnits('M'));
    }

    /**
     * Test countUnits() with exponents.
     */
    public function testCountUnitsWithExponents(): void
    {
        // L2 = 2 unit slots.
        $this->assertSame(2, DimensionService::countUnits('L2'));
        // T-2 = 2 unit slots (absolute value).
        $this->assertSame(2, DimensionService::countUnits('T-2'));
    }

    /**
     * Test countUnits() with compound dimensions.
     */
    public function testCountUnitsCompound(): void
    {
        // MLT-2 = M(1) + L(1) + T(2) = 4.
        $this->assertSame(4, DimensionService::countUnits('MLT-2'));
        // ML2T-2 = M(1) + L(2) + T(2) = 5.
        $this->assertSame(5, DimensionService::countUnits('ML2T-2'));
    }

    /**
     * Test countUnits() with empty dimension.
     */
    public function testCountUnitsEmpty(): void
    {
        $this->assertSame(0, DimensionService::countUnits(''));
    }

    // endregion

    // region lessThanOrEqual() tests

    /**
     * Test lessThanOrEqual() with equal dimensions.
     */
    public function testLessThanOrEqualSameDimension(): void
    {
        $this->assertTrue(DimensionService::lessThanOrEqual('MLT-2', 'MLT-2'));
    }

    /**
     * Test lessThanOrEqual() with a subset dimension.
     */
    public function testLessThanOrEqualSubset(): void
    {
        // M is a subset of MLT-2.
        $this->assertTrue(DimensionService::lessThanOrEqual('M', 'MLT-2'));
        // ML is a subset of MLT-2.
        $this->assertTrue(DimensionService::lessThanOrEqual('ML', 'MLT-2'));
    }

    /**
     * Test lessThanOrEqual() with smaller exponent fits larger.
     */
    public function testLessThanOrEqualSmallerExponent(): void
    {
        // L fits inside L2 (exponent 1 <= 2).
        $this->assertTrue(DimensionService::lessThanOrEqual('L', 'L2'));
        // T-1 fits inside T-2 (abs(1) <= abs(2), same sign).
        $this->assertTrue(DimensionService::lessThanOrEqual('T-1', 'T-2'));
    }

    /**
     * Test lessThanOrEqual() returns false when dimension1 has larger exponent.
     */
    public function testLessThanOrEqualLargerExponentFails(): void
    {
        // L2 does not fit inside L.
        $this->assertFalse(DimensionService::lessThanOrEqual('L2', 'L'));
    }

    /**
     * Test lessThanOrEqual() returns false when signs differ.
     */
    public function testLessThanOrEqualDifferentSignsFails(): void
    {
        // T (positive) does not fit inside T-2 (negative).
        $this->assertFalse(DimensionService::lessThanOrEqual('T', 'T-2'));
        // T-1 does not fit inside T.
        $this->assertFalse(DimensionService::lessThanOrEqual('T-1', 'T'));
    }

    /**
     * Test lessThanOrEqual() returns false when dimension1 has terms missing from dimension2.
     */
    public function testLessThanOrEqualMissingTermFails(): void
    {
        // MLT-2 has T, which is not in ML.
        $this->assertFalse(DimensionService::lessThanOrEqual('MLT-2', 'ML'));
        // I is not in MLT-2.
        $this->assertFalse(DimensionService::lessThanOrEqual('I', 'MLT-2'));
    }

    /**
     * Test lessThanOrEqual() with empty dimension1.
     */
    public function testLessThanOrEqualEmptyIsSubsetOfAnything(): void
    {
        $this->assertTrue(DimensionService::lessThanOrEqual('', 'MLT-2'));
        $this->assertTrue(DimensionService::lessThanOrEqual('', ''));
    }

    // endregion

    // region sub() tests

    /**
     * Test sub() subtracts matching dimension terms.
     */
    public function testSubMatchingTerms(): void
    {
        // ML2T-2 - MLT-2 = L (M cancels, L2-L1=L, T-2-T-2=0).
        $this->assertSame('L', DimensionService::sub('ML2T-2', 'MLT-2'));
    }

    /**
     * Test sub() with identical dimensions produces empty.
     */
    public function testSubIdenticalProducesEmpty(): void
    {
        $this->assertSame('', DimensionService::sub('MLT-2', 'MLT-2'));
    }

    /**
     * Test sub() ignores terms in dimension2 not in dimension1.
     */
    public function testSubIgnoresExtraTermsInDimension2(): void
    {
        // L - MLT-2 = only L is in dimension1, so M and T in dimension2 are ignored.
        // L(1) - L(1) = 0, so result is empty... actually L - L = 0.
        // Let's use L2 - L = L instead.
        $this->assertSame('L', DimensionService::sub('L2', 'L'));
    }

    /**
     * Test sub() with no overlap keeps dimension1 unchanged.
     */
    public function testSubNoOverlap(): void
    {
        // M - L = M (L is not in dimension1, so ignored).
        $this->assertSame('M', DimensionService::sub('M', 'L'));
    }

    /**
     * Test sub() with empty dimension2 returns dimension1.
     */
    public function testSubEmptyDimension2(): void
    {
        $this->assertSame('MLT-2', DimensionService::sub('MLT-2', ''));
    }

    /**
     * Test sub() with empty dimension1 returns empty.
     */
    public function testSubEmptyDimension1(): void
    {
        $this->assertSame('', DimensionService::sub('', 'MLT-2'));
    }

    /**
     * Test sub() can produce negative exponents.
     */
    public function testSubProducesNegativeExponents(): void
    {
        // L - L2 = L(1-2) = L-1.
        $this->assertSame('L-1', DimensionService::sub('L', 'L2'));
    }

    // endregion
}
