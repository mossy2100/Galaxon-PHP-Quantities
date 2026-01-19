<?php

declare(strict_types=1);

namespace Registry;

use DomainException;
use Galaxon\Quantities\Registry\DimensionRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Dimensions class.
 */
#[CoversClass(DimensionRegistry::class)]
final class DimensionRegistryTest extends TestCase
{
    // region isValid() tests

    /**
     * Test isValid() returns true for single dimension code.
     */
    public function testIsValidSingleCode(): void
    {
        $this->assertTrue(DimensionRegistry::isValid('L'));
        $this->assertTrue(DimensionRegistry::isValid('M'));
        $this->assertTrue(DimensionRegistry::isValid('T'));
    }

    /**
     * Test isValid() returns true for single dimension with exponent.
     */
    public function testIsValidSingleCodeWithExponent(): void
    {
        $this->assertTrue(DimensionRegistry::isValid('L2'));
        $this->assertTrue(DimensionRegistry::isValid('L3'));
        $this->assertTrue(DimensionRegistry::isValid('T-1'));
        $this->assertTrue(DimensionRegistry::isValid('T-2'));
    }

    /**
     * Test isValid() returns true for compound dimension codes.
     */
    public function testIsValidCompoundCode(): void
    {
        $this->assertTrue(DimensionRegistry::isValid('ML'));
        $this->assertTrue(DimensionRegistry::isValid('MLT'));
        $this->assertTrue(DimensionRegistry::isValid('MLT-2'));
        $this->assertTrue(DimensionRegistry::isValid('M2L2T-4'));
    }

    /**
     * Test isValid() returns true for all dimension codes.
     */
    public function testIsValidAllCodes(): void
    {
        foreach (array_keys(DimensionRegistry::DIMENSION_CODES) as $code) {
            $this->assertTrue(DimensionRegistry::isValid($code), "Code '$code' should be valid");
        }
    }

    /**
     * Test isValid() returns true for empty string (dimensionless).
     */
    public function testIsValidEmptyString(): void
    {
        $this->assertTrue(DimensionRegistry::isValid(''));
    }

    /**
     * Test isValid() returns false for invalid dimension letters.
     */
    public function testIsValidInvalidLetters(): void
    {
        $this->assertFalse(DimensionRegistry::isValid('X'));
        $this->assertFalse(DimensionRegistry::isValid('Z'));
        $this->assertFalse(DimensionRegistry::isValid('B'));
    }

    /**
     * Test isValid() returns false for lowercase letters.
     */
    public function testIsValidLowercaseLetters(): void
    {
        $this->assertFalse(DimensionRegistry::isValid('l'));
        $this->assertFalse(DimensionRegistry::isValid('m'));
        $this->assertFalse(DimensionRegistry::isValid('mlt'));
    }

    /**
     * Test isValid() returns false for invalid format.
     */
    public function testIsValidInvalidFormat(): void
    {
        $this->assertFalse(DimensionRegistry::isValid('2L'));    // Exponent before letter
        $this->assertFalse(DimensionRegistry::isValid('L*M'));   // Invalid character
        $this->assertFalse(DimensionRegistry::isValid('L M'));   // Space
        $this->assertFalse(DimensionRegistry::isValid('L-'));    // Minus without digit
    }

    /**
     * Test isValid() accepts dimension with exponent followed by another dimension.
     */
    public function testIsValidExponentFollowedByDimension(): void
    {
        // L2M is valid: it means L² × M
        $this->assertTrue(DimensionRegistry::isValid('L2M'));
        $this->assertTrue(DimensionRegistry::isValid('M2L2T-2'));
    }

    /**
     * Test isValid() returns false for multi-digit exponents.
     */
    public function testIsValidMultiDigitExponents(): void
    {
        $this->assertFalse(DimensionRegistry::isValid('L10'));
        $this->assertFalse(DimensionRegistry::isValid('L-10'));
    }

    // endregion

    // region explode() tests

    /**
     * Test explode() with single dimension code.
     */
    public function testExplodeSingleCode(): void
    {
        $result = DimensionRegistry::explode('L');

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
        ], DimensionRegistry::explode('L2'));
        $this->assertSame([
            'L' => 3,
        ], DimensionRegistry::explode('L3'));
        $this->assertSame([
            'T' => -1,
        ], DimensionRegistry::explode('T-1'));
        $this->assertSame([
            'T' => -2,
        ], DimensionRegistry::explode('T-2'));
    }

    /**
     * Test explode() with compound dimension code.
     */
    public function testExplodeCompoundCode(): void
    {
        $result = DimensionRegistry::explode('MLT-2');

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
        $result = DimensionRegistry::explode('ML2T-2');

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

        DimensionRegistry::explode('XYZ');
    }

    /**
     * Test explode() returns empty array for empty string (dimensionless).
     */
    public function testExplodeReturnsEmptyArrayForEmptyString(): void
    {
        $result = DimensionRegistry::explode('');

        $this->assertSame([], $result);
    }

    // endregion

    // region implode() tests

    /**
     * Test implode() with single dimension term.
     */
    public function testImplodeSingleTerm(): void
    {
        $result = DimensionRegistry::implode([
            'L' => 1,
        ]);

        $this->assertSame('L', $result);
    }

    /**
     * Test implode() with single dimension term and exponent.
     */
    public function testImplodeSingleTermWithExponent(): void
    {
        $this->assertSame('L2', DimensionRegistry::implode([
            'L' => 2,
        ]));
        $this->assertSame('L3', DimensionRegistry::implode([
            'L' => 3,
        ]));
        $this->assertSame('T-1', DimensionRegistry::implode([
            'T' => -1,
        ]));
        $this->assertSame('T-2', DimensionRegistry::implode([
            'T' => -2,
        ]));
    }

    /**
     * Test implode() with multiple terms.
     */
    public function testImplodeMultipleTerms(): void
    {
        $result = DimensionRegistry::implode([
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
        $result = DimensionRegistry::implode([
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
        $result = DimensionRegistry::implode([]);

        $this->assertSame('', $result);
    }

    /**
     * Test implode() omits exponent of 1.
     */
    public function testImplodeOmitsExponentOne(): void
    {
        $result = DimensionRegistry::implode([
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
        $this->assertSame('MLT-2', DimensionRegistry::normalize('MLT-2'));
    }

    /**
     * Test normalize() reorders terms.
     */
    public function testNormalizeReordersTerms(): void
    {
        // T-2 L M should become M L T-2
        $this->assertSame('MLT-2', DimensionRegistry::normalize('T-2LM'));
    }

    /**
     * Test normalize() with single code.
     */
    public function testNormalizeSingleCode(): void
    {
        $this->assertSame('L', DimensionRegistry::normalize('L'));
        $this->assertSame('L2', DimensionRegistry::normalize('L2'));
    }

    /**
     * Test normalize() throws for invalid code.
     */
    public function testNormalizeThrowsForInvalidCode(): void
    {
        $this->expectException(DomainException::class);

        DimensionRegistry::normalize('invalid');
    }

    // endregion

    // region applyExponent() tests

    /**
     * Test applyExponent() with exponent of 1 returns unchanged.
     */
    public function testApplyExponentOne(): void
    {
        $this->assertSame('L', DimensionRegistry::applyExponent('L', 1));
        $this->assertSame('MLT-2', DimensionRegistry::applyExponent('MLT-2', 1));
    }

    /**
     * Test applyExponent() squares a simple dimension.
     */
    public function testApplyExponentSquare(): void
    {
        $this->assertSame('L2', DimensionRegistry::applyExponent('L', 2));
    }

    /**
     * Test applyExponent() cubes a simple dimension.
     */
    public function testApplyExponentCube(): void
    {
        $this->assertSame('L3', DimensionRegistry::applyExponent('L', 3));
    }

    /**
     * Test applyExponent() with negative exponent.
     */
    public function testApplyExponentNegative(): void
    {
        $this->assertSame('T-2', DimensionRegistry::applyExponent('T-1', 2));
    }

    /**
     * Test applyExponent() with compound dimension (force squared).
     */
    public function testApplyExponentCompound(): void
    {
        // Force (MLT-2) squared = M2L2T-4
        $this->assertSame('M2L2T-4', DimensionRegistry::applyExponent('MLT-2', 2));
    }

    /**
     * Test applyExponent() with zero exponent.
     */
    public function testApplyExponentZero(): void
    {
        // L^0 = dimensionless (all exponents become 0)
        $result = DimensionRegistry::applyExponent('L', 0);

        $this->assertSame('L0', $result);
    }

    /**
     * Test applyExponent() with negative multiplier (inverse).
     */
    public function testApplyExponentInverse(): void
    {
        // Inverse of velocity (LT-1) = L-1T
        $this->assertSame('L-1T', DimensionRegistry::applyExponent('LT-1', -1));
    }

    /**
     * Test applyExponent() throws for invalid dimension.
     */
    public function testApplyExponentThrowsForInvalidDimension(): void
    {
        $this->expectException(DomainException::class);

        DimensionRegistry::applyExponent('invalid', 2);
    }

    // endregion

    // region letterToInt() tests

    /**
     * Test letterToInt() returns correct index for each dimension code.
     */
    public function testLetterToIntValidCodes(): void
    {
        $codes = array_keys(DimensionRegistry::DIMENSION_CODES);
        foreach ($codes as $index => $code) {
            $this->assertSame($index, DimensionRegistry::letterToInt($code), "Code '$code' should have index $index");
        }
    }

    /**
     * Test letterToInt() returns null for invalid code.
     */
    public function testLetterToIntInvalidCode(): void
    {
        $this->assertNull(DimensionRegistry::letterToInt('X'));
        $this->assertNull(DimensionRegistry::letterToInt('Z'));
        $this->assertNull(DimensionRegistry::letterToInt('B'));
    }

    /**
     * Test letterToInt() returns null for lowercase.
     */
    public function testLetterToIntLowercase(): void
    {
        $this->assertNull(DimensionRegistry::letterToInt('l'));
        $this->assertNull(DimensionRegistry::letterToInt('m'));
    }

    /**
     * Test letterToInt() returns null for empty string.
     */
    public function testLetterToIntEmptyString(): void
    {
        $this->assertNull(DimensionRegistry::letterToInt(''));
    }

    /**
     * Test letterToInt() returns null for multi-character string.
     */
    public function testLetterToIntMultiCharacter(): void
    {
        $this->assertNull(DimensionRegistry::letterToInt('ML'));
        $this->assertNull(DimensionRegistry::letterToInt('L2'));
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
            $normalized = DimensionRegistry::normalize($code);
            $exploded = DimensionRegistry::explode($normalized);
            $imploded = DimensionRegistry::implode($exploded);

            $this->assertSame($normalized, $imploded, "Round-trip failed for '$code'");
        }
    }

    // endregion
}
