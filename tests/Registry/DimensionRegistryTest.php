<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Registry;

use DomainException;
use Galaxon\Quantities\Helpers\DimensionUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Dimensions class.
 */
#[CoversClass(DimensionUtils::class)]
final class DimensionRegistryTest extends TestCase
{
    // region isValid() tests

    /**
     * Test isValid() returns true for single dimension code.
     */
    public function testIsValidSingleCode(): void
    {
        $this->assertTrue(DimensionUtils::isValid('L'));
        $this->assertTrue(DimensionUtils::isValid('M'));
        $this->assertTrue(DimensionUtils::isValid('T'));
    }

    /**
     * Test isValid() returns true for single dimension with exponent.
     */
    public function testIsValidSingleCodeWithExponent(): void
    {
        $this->assertTrue(DimensionUtils::isValid('L2'));
        $this->assertTrue(DimensionUtils::isValid('L3'));
        $this->assertTrue(DimensionUtils::isValid('T-1'));
        $this->assertTrue(DimensionUtils::isValid('T-2'));
    }

    /**
     * Test isValid() returns true for compound dimension codes.
     */
    public function testIsValidCompoundCode(): void
    {
        $this->assertTrue(DimensionUtils::isValid('ML'));
        $this->assertTrue(DimensionUtils::isValid('MLT'));
        $this->assertTrue(DimensionUtils::isValid('MLT-2'));
        $this->assertTrue(DimensionUtils::isValid('M2L2T-4'));
    }

    /**
     * Test isValid() returns true for all dimension codes.
     */
    public function testIsValidAllCodes(): void
    {
        foreach (array_keys(DimensionUtils::DIMENSION_CODES) as $code) {
            $this->assertTrue(DimensionUtils::isValid($code), "Code '$code' should be valid");
        }
    }

    /**
     * Test isValid() returns true for empty string (dimensionless).
     */
    public function testIsValidEmptyString(): void
    {
        $this->assertTrue(DimensionUtils::isValid(''));
    }

    /**
     * Test isValid() returns false for invalid dimension letters.
     */
    public function testIsValidInvalidLetters(): void
    {
        $this->assertFalse(DimensionUtils::isValid('X'));
        $this->assertFalse(DimensionUtils::isValid('Z'));
        $this->assertFalse(DimensionUtils::isValid('B'));
    }

    /**
     * Test isValid() returns false for lowercase letters.
     */
    public function testIsValidLowercaseLetters(): void
    {
        $this->assertFalse(DimensionUtils::isValid('l'));
        $this->assertFalse(DimensionUtils::isValid('m'));
        $this->assertFalse(DimensionUtils::isValid('mlt'));
    }

    /**
     * Test isValid() returns false for invalid format.
     */
    public function testIsValidInvalidFormat(): void
    {
        $this->assertFalse(DimensionUtils::isValid('2L'));    // Exponent before letter
        $this->assertFalse(DimensionUtils::isValid('L*M'));   // Invalid character
        $this->assertFalse(DimensionUtils::isValid('L M'));   // Space
        $this->assertFalse(DimensionUtils::isValid('L-'));    // Minus without digit
    }

    /**
     * Test isValid() accepts dimension with exponent followed by another dimension.
     */
    public function testIsValidExponentFollowedByDimension(): void
    {
        // L2M is valid: it means L² × M
        $this->assertTrue(DimensionUtils::isValid('L2M'));
        $this->assertTrue(DimensionUtils::isValid('M2L2T-2'));
    }

    /**
     * Test isValid() returns false for multi-digit exponents.
     */
    public function testIsValidMultiDigitExponents(): void
    {
        $this->assertFalse(DimensionUtils::isValid('L10'));
        $this->assertFalse(DimensionUtils::isValid('L-10'));
    }

    // endregion

    // region explode() tests

    /**
     * Test explode() with single dimension code.
     */
    public function testExplodeSingleCode(): void
    {
        $result = DimensionUtils::explode('L');

        $this->assertSame([
            'L' => 1,
        ], $result);
    }

    /**
     * Test explode() with single dimension code and exponent.
     */
    public function testExplodeSingleCodeWithExponent(): void
    {
        $this->assertSame([
            'L' => 2,
        ], DimensionUtils::explode('L2'));
        $this->assertSame([
            'L' => 3,
        ], DimensionUtils::explode('L3'));
        $this->assertSame([
            'T' => -1,
        ], DimensionUtils::explode('T-1'));
        $this->assertSame([
            'T' => -2,
        ], DimensionUtils::explode('T-2'));
    }

    /**
     * Test explode() with compound dimension code.
     */
    public function testExplodeCompoundCode(): void
    {
        $result = DimensionUtils::explode('MLT-2');

        $this->assertSame([
            'M' => 1,
            'L' => 1,
            'T' => -2,
        ], $result);
    }

    /**
     * Test explode() with complex dimension code (energy: M L2 T-2).
     */
    public function testExplodeComplexCode(): void
    {
        $result = DimensionUtils::explode('ML2T-2');

        $this->assertSame([
            'M' => 1,
            'L' => 2,
            'T' => -2,
        ], $result);
    }

    /**
     * Test explode() throws DomainException for invalid code.
     */
    public function testExplodeThrowsForInvalidCode(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid dimension code 'XYZ'");

        DimensionUtils::explode('XYZ');
    }

    /**
     * Test explode() returns empty array for empty string (dimensionless).
     */
    public function testExplodeReturnsEmptyArrayForEmptyString(): void
    {
        $result = DimensionUtils::explode('');

        $this->assertSame([], $result);
    }

    // endregion

    // region implode() tests

    /**
     * Test implode() with single dimension term.
     */
    public function testImplodeSingleTerm(): void
    {
        $result = DimensionUtils::implode([
            'L' => 1,
        ]);

        $this->assertSame('L', $result);
    }

    /**
     * Test implode() with single dimension term and exponent.
     */
    public function testImplodeSingleTermWithExponent(): void
    {
        $this->assertSame('L2', DimensionUtils::implode([
            'L' => 2,
        ]));
        $this->assertSame('L3', DimensionUtils::implode([
            'L' => 3,
        ]));
        $this->assertSame('T-1', DimensionUtils::implode([
            'T' => -1,
        ]));
        $this->assertSame('T-2', DimensionUtils::implode([
            'T' => -2,
        ]));
    }

    /**
     * Test implode() with multiple terms.
     */
    public function testImplodeMultipleTerms(): void
    {
        $result = DimensionUtils::implode([
            'M' => 1,
            'L' => 1,
            'T' => -2,
        ]);

        $this->assertSame('MLT-2', $result);
    }

    /**
     * Test implode() sorts terms into canonical order.
     */
    public function testImplodeSortsTerms(): void
    {
        // Input in wrong order (T before M before L)
        $result = DimensionUtils::implode([
            'T' => -2,
            'M' => 1,
            'L' => 1,
        ]);

        // Output should be in canonical order (M, L, T)
        $this->assertSame('MLT-2', $result);
    }

    /**
     * Test implode() with empty array returns empty string.
     */
    public function testImplodeEmptyArray(): void
    {
        $result = DimensionUtils::implode([]);

        $this->assertSame('', $result);
    }

    /**
     * Test implode() omits exponent of 1.
     */
    public function testImplodeOmitsExponentOne(): void
    {
        $result = DimensionUtils::implode([
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
        $this->assertSame('MLT-2', DimensionUtils::normalize('MLT-2'));
    }

    /**
     * Test normalize() reorders terms.
     */
    public function testNormalizeReordersTerms(): void
    {
        // T-2 L M should become M L T-2
        $this->assertSame('MLT-2', DimensionUtils::normalize('T-2LM'));
    }

    /**
     * Test normalize() with single code.
     */
    public function testNormalizeSingleCode(): void
    {
        $this->assertSame('L', DimensionUtils::normalize('L'));
        $this->assertSame('L2', DimensionUtils::normalize('L2'));
    }

    /**
     * Test normalize() throws for invalid code.
     */
    public function testNormalizeThrowsForInvalidCode(): void
    {
        $this->expectException(DomainException::class);

        DimensionUtils::normalize('invalid');
    }

    // endregion

    // region applyExponent() tests

    /**
     * Test applyExponent() with exponent of 1 returns unchanged.
     */
    public function testApplyExponentOne(): void
    {
        $this->assertSame('L', DimensionUtils::applyExponent('L', 1));
        $this->assertSame('MLT-2', DimensionUtils::applyExponent('MLT-2', 1));
    }

    /**
     * Test applyExponent() squares a simple dimension.
     */
    public function testApplyExponentSquare(): void
    {
        $this->assertSame('L2', DimensionUtils::applyExponent('L', 2));
    }

    /**
     * Test applyExponent() cubes a simple dimension.
     */
    public function testApplyExponentCube(): void
    {
        $this->assertSame('L3', DimensionUtils::applyExponent('L', 3));
    }

    /**
     * Test applyExponent() with negative exponent.
     */
    public function testApplyExponentNegative(): void
    {
        $this->assertSame('T-2', DimensionUtils::applyExponent('T-1', 2));
    }

    /**
     * Test applyExponent() with compound dimension (force squared).
     */
    public function testApplyExponentCompound(): void
    {
        // Force (MLT-2) squared = M2L2T-4
        $this->assertSame('M2L2T-4', DimensionUtils::applyExponent('MLT-2', 2));
    }

    /**
     * Test applyExponent() with zero exponent.
     */
    public function testApplyExponentZero(): void
    {
        // L^0 = dimensionless (all exponents become 0)
        $result = DimensionUtils::applyExponent('L', 0);

        $this->assertSame('L0', $result);
    }

    /**
     * Test applyExponent() with negative multiplier (inverse).
     */
    public function testApplyExponentInverse(): void
    {
        // Inverse of velocity (LT-1) = L-1T
        $this->assertSame('L-1T', DimensionUtils::applyExponent('LT-1', -1));
    }

    /**
     * Test applyExponent() throws for invalid dimension.
     */
    public function testApplyExponentThrowsForInvalidDimension(): void
    {
        $this->expectException(DomainException::class);

        DimensionUtils::applyExponent('invalid', 2);
    }

    // endregion

    // region letterToInt() tests

    /**
     * Test letterToInt() returns correct index for each dimension code.
     */
    public function testLetterToIntValidCodes(): void
    {
        $codes = array_keys(DimensionUtils::DIMENSION_CODES);
        foreach ($codes as $index => $code) {
            $this->assertSame($index, DimensionUtils::letterToInt($code), "Code '$code' should have index $index");
        }
    }

    /**
     * Test letterToInt() returns null for invalid code.
     */
    public function testLetterToIntInvalidCode(): void
    {
        $this->assertNull(DimensionUtils::letterToInt('X'));
        $this->assertNull(DimensionUtils::letterToInt('Z'));
        $this->assertNull(DimensionUtils::letterToInt('B'));
    }

    /**
     * Test letterToInt() returns null for lowercase.
     */
    public function testLetterToIntLowercase(): void
    {
        $this->assertNull(DimensionUtils::letterToInt('l'));
        $this->assertNull(DimensionUtils::letterToInt('m'));
    }

    /**
     * Test letterToInt() returns null for empty string.
     */
    public function testLetterToIntEmptyString(): void
    {
        $this->assertNull(DimensionUtils::letterToInt(''));
    }

    /**
     * Test letterToInt() returns null for multi-character string.
     */
    public function testLetterToIntMultiCharacter(): void
    {
        $this->assertNull(DimensionUtils::letterToInt('ML'));
        $this->assertNull(DimensionUtils::letterToInt('L2'));
    }

    // endregion

    // region Round-trip tests

    /**
     * Test explode() and implode() are inverse operations.
     */
    public function testExplodeImplodeRoundTrip(): void
    {
        $codes = ['L', 'L2', 'T-1', 'MLT-2', 'M2L2T-4', 'MLIT-2'];

        foreach ($codes as $code) {
            $normalized = DimensionUtils::normalize($code);
            $exploded = DimensionUtils::explode($normalized);
            $imploded = DimensionUtils::implode($exploded);

            $this->assertSame($normalized, $imploded, "Round-trip failed for '$code'");
        }
    }

    // endregion
}
