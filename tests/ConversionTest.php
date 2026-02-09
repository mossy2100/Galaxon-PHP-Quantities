<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests;

use DomainException;
use Galaxon\Quantities\Conversion;
use Galaxon\Quantities\DerivedUnit;
use Galaxon\Quantities\FloatWithError;
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Conversion class.
 */
#[CoversClass(Conversion::class)]
class ConversionTest extends TestCase
{
    // region Setup

    public static function setUpBeforeClass(): void
    {
        // Load Imperial/US units for cross-system tests.
        UnitRegistry::loadSystem(System::Imperial);
        UnitRegistry::loadSystem(System::UsCustomary);
        UnitRegistry::loadSystem(System::Nautical);
    }

    // endregion

    // region Constructor tests

    /**
     * Test constructor with string unit terms.
     */
    public function testConstructorWithStrings(): void
    {
        $conv = new Conversion('m', 'ft', 3.28084);

        $this->assertSame('m', (string)$conv->srcUnit);
        $this->assertSame('ft', (string)$conv->destUnit);
        $this->assertEqualsWithDelta(3.28084, $conv->factor->value, 1e-10);
    }

    /**
     * Test constructor with UnitTerm objects.
     */
    public function testConstructorWithUnitTerms(): void
    {
        $srcUnitTerm = DerivedUnit::parse('m');
        $destUnitTerm = DerivedUnit::parse('ft');

        $conv = new Conversion($srcUnitTerm, $destUnitTerm, 3.28084);

        $this->assertSame($srcUnitTerm, $conv->srcUnit);
        $this->assertSame($destUnitTerm, $conv->destUnit);
    }

    /**
     * Test constructor with FloatWithError factor.
     */
    public function testConstructorWithFloatWithError(): void
    {
        $factor = new FloatWithError(3.28084, 0.00001);

        $conv = new Conversion('m', 'ft', $factor);

        $this->assertSame(3.28084, $conv->factor->value);
        $this->assertSame(0.00001, $conv->factor->absoluteError);
    }

    /**
     * Test constructor throws for zero factor.
     */
    public function testConstructorThrowsForZeroFactor(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Conversion factor must be positive.');

        new Conversion('m', 'ft', 0.0);
    }

    /**
     * Test constructor throws for FloatWithError zero factor.
     */
    public function testConstructorThrowsForZeroFloatWithErrorFactor(): void
    {
        $factor = new FloatWithError(0.0, 0.0);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Conversion factor must be positive.');

        new Conversion('m', 'ft', $factor);
    }

    /**
     * Test constructor throws for mismatched dimensions.
     */
    public function testConstructorThrowsForMismatchedDimensions(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("'m' (L) and 's' (T) have different dimensions");

        new Conversion('m', 's', 1.0);
    }

    /**
     * Test constructor throws for negative factor.
     */
    public function testConstructorThrowsForNegativeFactor(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Conversion factor must be positive.');

        new Conversion('m', 'ft', -3.28084);
    }

    /**
     * Test constructor with factor of 1.0 (identity-like conversion).
     */
    public function testConstructorWithFactorOne(): void
    {
        $conv = new Conversion('m', 'ft', 1.0);

        $this->assertSame(1.0, $conv->factor->value);
        $this->assertSame(0.0, $conv->factor->absoluteError);
    }

    // endregion

    // region invert() tests

    /**
     * Test invert swaps units.
     */
    public function testInvertSwapsUnits(): void
    {
        $conv = new Conversion('m', 'ft', 3.28084);

        $inverted = $conv->inv();

        $this->assertSame('ft', (string)$inverted->srcUnit);
        $this->assertSame('m', (string)$inverted->destUnit);
    }

    /**
     * Test invert inverts factor.
     */
    public function testInvertInvertsFactor(): void
    {
        $conv = new Conversion('m', 'ft', 3.28084);

        $inverted = $conv->inv();

        $this->assertEqualsWithDelta(1.0 / 3.28084, $inverted->factor->value, 1e-10);
    }

    /**
     * Test invert is reversible.
     */
    public function testInvertIsReversible(): void
    {
        $conv = new Conversion('m', 'ft', 3.28084);

        $doubleInverted = $conv->inv()->inv();

        $this->assertSame('m', (string)$doubleInverted->srcUnit);
        $this->assertSame('ft', (string)$doubleInverted->destUnit);
        $this->assertEqualsWithDelta(3.28084, $doubleInverted->factor->value, 1e-10);
    }

    /**
     * Test invert propagates error correctly.
     */
    public function testInvertPropagatesError(): void
    {
        $factor = new FloatWithError(2.0, 0.1);
        $conv = new Conversion('m', 'ft', $factor);

        $inverted = $conv->inv();

        // Relative error should be preserved: 0.1/2 = 0.05 = 5%
        // Inverted value is 0.5, so absolute error ≈ 0.5 * 0.05 = 0.025
        $this->assertEqualsWithDelta(0.5, $inverted->factor->value, 1e-10);
        $this->assertGreaterThan(0.0, $inverted->factor->absoluteError);
    }

    /**
     * Test invert with factor of 1.0.
     */
    public function testInvertWithFactorOne(): void
    {
        $conv = new Conversion('m', 'ft', 1.0);

        $inverted = $conv->inv();

        $this->assertSame(1.0, $inverted->factor->value);
    }

    // endregion

    // region combineSequential() tests

    /**
     * Test combineSequential chains units correctly.
     */
    public function testCombineSequentialChainsUnits(): void
    {
        $conv1 = new Conversion('m', 'ft', 3.28084);
        $conv2 = new Conversion('ft', 'in', 12.0);

        $combined = $conv1->combineSequential($conv2);

        $this->assertSame('m', (string)$combined->srcUnit);
        $this->assertSame('in', (string)$combined->destUnit);
    }

    /**
     * Test combineSequential multiplies factors.
     */
    public function testCombineSequentialMultipliesFactors(): void
    {
        $conv1 = new Conversion('m', 'ft', 3.28084);
        $conv2 = new Conversion('ft', 'in', 12.0);

        $combined = $conv1->combineSequential($conv2);

        // m -> in = 3.28084 * 12 = 39.37008
        $this->assertEqualsWithDelta(3.28084 * 12.0, $combined->factor->value, 1e-10);
    }

    /**
     * Test combineSequential propagates error.
     */
    public function testCombineSequentialPropagatesError(): void
    {
        $factor1 = new FloatWithError(2.0, 0.1);
        $factor2 = new FloatWithError(3.0, 0.2);
        $conv1 = new Conversion('m', 'ft', $factor1);
        $conv2 = new Conversion('ft', 'in', $factor2);

        $combined = $conv1->combineSequential($conv2);

        $this->assertEqualsWithDelta(6.0, $combined->factor->value, 1e-10);
        // Error should be greater than either individual error
        $this->assertGreaterThan(0.1, $combined->factor->absoluteError);
    }

    /**
     * Test combineSequential with factor of 1.0.
     */
    public function testCombineSequentialWithFactorOne(): void
    {
        $conv1 = new Conversion('m', 'ft', 1.0);
        $conv2 = new Conversion('ft', 'in', 12.0);

        $combined = $conv1->combineSequential($conv2);

        $this->assertEqualsWithDelta(12.0, $combined->factor->value, 1e-10);
    }

    // endregion

    // region combineConvergent() tests

    /**
     * Test combineConvergent chains units correctly.
     */
    public function testCombineConvergentChainsUnits(): void
    {
        $conv1 = new Conversion('m', 'ft', 3.28084);  // m -> ft
        $conv2 = new Conversion('in', 'ft', 1.0 / 12.0); // in -> ft

        $combined = $conv1->combineConvergent($conv2);

        $this->assertSame('m', (string)$combined->srcUnit);
        $this->assertSame('in', (string)$combined->destUnit);
    }

    /**
     * Test combineConvergent divides factors.
     */
    public function testCombineConvergentDividesFactors(): void
    {
        $conv1 = new Conversion('m', 'ft', 3.28084);
        $conv2 = new Conversion('in', 'ft', 1.0 / 12.0);

        $combined = $conv1->combineConvergent($conv2);

        // m -> in = 3.28084 / (1/12) = 3.28084 * 12 = 39.37008
        $this->assertEqualsWithDelta(3.28084 * 12.0, $combined->factor->value, 1e-10);
    }

    /**
     * Test combineConvergent propagates error.
     */
    public function testCombineConvergentPropagatesError(): void
    {
        $factor1 = new FloatWithError(6.0, 0.1);
        $factor2 = new FloatWithError(2.0, 0.05);
        $conv1 = new Conversion('m', 'ft', $factor1);
        $conv2 = new Conversion('in', 'ft', $factor2);

        $combined = $conv1->combineConvergent($conv2);

        $this->assertEqualsWithDelta(3.0, $combined->factor->value, 1e-10);
        $this->assertGreaterThan(0.0, $combined->factor->absoluteError);
    }

    // endregion

    // region combineDivergent() tests

    /**
     * Test combineDivergent chains units correctly.
     */
    public function testCombineDivergentChainsUnits(): void
    {
        $conv1 = new Conversion('ft', 'm', 0.3048);   // ft -> m
        $conv2 = new Conversion('ft', 'in', 12.0);    // ft -> in

        $combined = $conv1->combineDivergent($conv2);

        $this->assertSame('m', (string)$combined->srcUnit);
        $this->assertSame('in', (string)$combined->destUnit);
    }

    /**
     * Test combineDivergent calculates factor correctly.
     */
    public function testCombineDivergentCalculatesFactor(): void
    {
        $conv1 = new Conversion('ft', 'm', 0.3048);
        $conv2 = new Conversion('ft', 'in', 12.0);

        $combined = $conv1->combineDivergent($conv2);

        // m -> in = 12 / 0.3048 = 39.3700...
        $this->assertEqualsWithDelta(12.0 / 0.3048, $combined->factor->value, 1e-10);
    }

    /**
     * Test combineDivergent propagates error.
     */
    public function testCombineDivergentPropagatesError(): void
    {
        $factor1 = new FloatWithError(2.0, 0.1);
        $factor2 = new FloatWithError(6.0, 0.2);
        $conv1 = new Conversion('ft', 'm', $factor1);
        $conv2 = new Conversion('ft', 'in', $factor2);

        $combined = $conv1->combineDivergent($conv2);

        $this->assertEqualsWithDelta(3.0, $combined->factor->value, 1e-10);
        $this->assertGreaterThan(0.0, $combined->factor->absoluteError);
    }

    // endregion

    // region combineOpposite() tests

    /**
     * Test combineOpposite chains units correctly.
     */
    public function testCombineOppositeChainsUnits(): void
    {
        $conv1 = new Conversion('ft', 'm', 0.3048);   // ft -> m
        $conv2 = new Conversion('in', 'ft', 1.0 / 12.0); // in -> ft

        $combined = $conv1->combineOpposite($conv2);

        $this->assertSame('m', (string)$combined->srcUnit);
        $this->assertSame('in', (string)$combined->destUnit);
    }

    /**
     * Test combineOpposite calculates factor correctly.
     */
    public function testCombineOppositeCalculatesFactor(): void
    {
        $conv1 = new Conversion('ft', 'm', 0.3048);
        $conv2 = new Conversion('in', 'ft', 1.0 / 12.0);

        $combined = $conv1->combineOpposite($conv2);

        // m -> in = 1 / (0.3048 * (1/12)) = 12 / 0.3048 = 39.3700...
        $this->assertEqualsWithDelta(1.0 / (0.3048 * (1.0 / 12.0)), $combined->factor->value, 1e-10);
    }

    /**
     * Test combineOpposite propagates error.
     */
    public function testCombineOppositePropagatesError(): void
    {
        $factor1 = new FloatWithError(2.0, 0.1);
        $factor2 = new FloatWithError(4.0, 0.2);
        $conv1 = new Conversion('ft', 'm', $factor1);
        $conv2 = new Conversion('in', 'ft', $factor2);

        $combined = $conv1->combineOpposite($conv2);

        // 1 / (2 * 4) = 0.125
        $this->assertEqualsWithDelta(0.125, $combined->factor->value, 1e-10);
        $this->assertGreaterThan(0.0, $combined->factor->absoluteError);
    }

    // endregion

    // region removePrefixes() tests

    /**
     * Test removePrefixes removes prefixes from both units.
     */
    public function testRemovePrefixesRemovesBothPrefixes(): void
    {
        $conv = new Conversion('km', 'cm', 100000.0);

        $unprefixed = $conv->removePrefixes();

        $this->assertSame('m', (string)$unprefixed->srcUnit);
        $this->assertSame('m', (string)$unprefixed->destUnit);
        // m -> m = 100000 * (1/1000) * 0.01 = 1
        $this->assertEqualsWithDelta(1.0, $unprefixed->factor->value, 1e-10);
    }

    /**
     * Test removePrefixes with no existing prefixes is a no-op.
     */
    public function testRemovePrefixesNoOp(): void
    {
        $conv = new Conversion('m', 'ft', 3.28084);

        $unprefixed = $conv->removePrefixes();

        $this->assertSame('m', (string)$unprefixed->srcUnit);
        $this->assertSame('ft', (string)$unprefixed->destUnit);
        $this->assertEqualsWithDelta(3.28084, $unprefixed->factor->value, 1e-10);
    }

    /**
     * Test removePrefixes removes only source prefix.
     */
    public function testRemovePrefixesRemovesSrcPrefixOnly(): void
    {
        $conv = new Conversion('km', 'ft', 3280.84);

        $unprefixed = $conv->removePrefixes();

        $this->assertSame('m', (string)$unprefixed->srcUnit);
        $this->assertSame('ft', (string)$unprefixed->destUnit);
        // m -> ft = 3280.84 / 1000 = 3.28084
        $this->assertEqualsWithDelta(3.28084, $unprefixed->factor->value, 1e-10);
    }

    // endregion

    // region Exponent unit tests

    /**
     * Test constructor with squared units (L2 dimension).
     */
    public function testConstructorWithSquaredUnits(): void
    {
        // m² → ft² (1 m² ≈ 10.7639 ft²)
        $conv = new Conversion('m2', 'ft2', 10.7639);

        $this->assertSame('m²', (string)$conv->srcUnit);
        $this->assertSame('ft²', (string)$conv->destUnit);
        $this->assertEqualsWithDelta(10.7639, $conv->factor->value, 1e-10);
    }

    /**
     * Test removePrefixes with squared unit.
     */
    public function testRemovePrefixesWithSquaredUnit(): void
    {
        // km² → cm² with factor 1e10
        $conv = new Conversion('km2', 'cm2', 1e10);

        $unprefixed = $conv->removePrefixes();

        $this->assertSame('m²', (string)$unprefixed->srcUnit);
        $this->assertSame('m²', (string)$unprefixed->destUnit);
        // m² → m² = 1e10 * (1e-6) * (1e-4) = 1e10 * 1e-10 = 1
        $this->assertEqualsWithDelta(1.0, $unprefixed->factor->value, 1e-10);
    }

    // endregion

    // region __toString() tests

    /**
     * Test toString format.
     */
    public function testToStringFormat(): void
    {
        $conv = new Conversion('m', 'ft', 3.28084);

        $str = (string)$conv;

        $this->assertStringContainsString('ft', $str);
        $this->assertStringContainsString('m', $str);
        $this->assertStringContainsString('3.28084', $str);
    }

    // endregion
}
