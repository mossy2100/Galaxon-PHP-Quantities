<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Quantity;

use DivisionByZeroError;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType\Area;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\QuantityType\Time;
use Galaxon\Quantities\QuantityType\Volume;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Quantity arithmetic methods (inv, mul, div).
 */
#[CoversClass(Quantity::class)]
#[CoversClass(Length::class)]
#[CoversClass(Mass::class)]
#[CoversClass(Time::class)]
#[CoversClass(Area::class)]
#[CoversClass(Volume::class)]
final class QuantityArithmeticTest extends TestCase
{
    // region inv() tests

    /**
     * Test basic inversion of a simple quantity.
     */
    public function testInvBasic(): void
    {
        $length = new Length(2, 'm');

        $result = $length->inv();

        $this->assertEqualsWithDelta(0.5, $result->value, 0.0001);
        $this->assertSame('m⁻¹', (string)$result->derivedUnit);
        $this->assertSame('L-1', $result->derivedUnit->dimension);
    }

    /**
     * Test inversion of quantity with value 1.
     */
    public function testInvOne(): void
    {
        $length = new Length(1, 'm');

        $result = $length->inv();

        $this->assertEqualsWithDelta(1.0, $result->value, 0.0001);
        $this->assertSame('m⁻¹', (string)$result->derivedUnit);
    }

    /**
     * Test inversion of negative value.
     */
    public function testInvNegativeValue(): void
    {
        $length = new Length(-4, 'm');

        $result = $length->inv();

        $this->assertEqualsWithDelta(-0.25, $result->value, 0.0001);
        $this->assertSame('m⁻¹', (string)$result->derivedUnit);
    }

    /**
     * Test inversion throws DivisionByZeroError for zero value.
     */
    public function testInvThrowsForZero(): void
    {
        $length = new Length(0, 'm');

        $this->expectException(DivisionByZeroError::class);

        $length->inv();
    }

    /**
     * Test inversion of compound unit (velocity to inverse velocity).
     */
    public function testInvCompoundUnit(): void
    {
        $length = new Length(10, 'm');
        $time = new Time(2, 's');
        $velocity = $length->div($time); // 5 m/s

        $result = $velocity->inv();

        $this->assertEqualsWithDelta(0.2, $result->value, 0.0001);
        // Inverted: s/m (or m⁻¹·s)
        $this->assertSame('L-1T', $result->derivedUnit->dimension);
    }

    /**
     * Test inversion of unit with exponent.
     */
    public function testInvUnitWithExponent(): void
    {
        $area = new Area(4, 'm2');

        $result = $area->inv();

        $this->assertEqualsWithDelta(0.25, $result->value, 0.0001);
        $this->assertSame('m⁻²', (string)$result->derivedUnit);
        $this->assertSame('L-2', $result->derivedUnit->dimension);
    }

    /**
     * Test double inversion returns to original dimension.
     */
    public function testInvDouble(): void
    {
        $length = new Length(4, 'm');

        $result = $length->inv()->inv();

        $this->assertEqualsWithDelta(4.0, $result->value, 0.0001);
        $this->assertSame('L', $result->derivedUnit->dimension);
    }

    // endregion

    // region mul() tests - scalar multiplication

    /**
     * Test multiplying by a scalar (dimensionless float).
     */
    public function testMulByScalar(): void
    {
        $length = new Length(10, 'm');

        $result = $length->mul(3.0);

        $this->assertInstanceOf(Length::class, $result);
        $this->assertEqualsWithDelta(30.0, $result->value, 0.0001);
        $this->assertSame('m', (string)$result->derivedUnit);
    }

    /**
     * Test multiplying by zero scalar.
     */
    public function testMulByZeroScalar(): void
    {
        $length = new Length(10, 'm');

        $result = $length->mul(0.0);

        $this->assertInstanceOf(Length::class, $result);
        $this->assertSame(0.0, $result->value);
        $this->assertSame('m', (string)$result->derivedUnit);
    }

    /**
     * Test multiplying by negative scalar.
     */
    public function testMulByNegativeScalar(): void
    {
        $length = new Length(10, 'm');

        $result = $length->mul(-2.0);

        $this->assertInstanceOf(Length::class, $result);
        $this->assertEqualsWithDelta(-20.0, $result->value, 0.0001);
    }

    /**
     * Test multiplying by fractional scalar.
     */
    public function testMulByFractionalScalar(): void
    {
        $length = new Length(10, 'm');

        $result = $length->mul(0.5);

        $this->assertInstanceOf(Length::class, $result);
        $this->assertEqualsWithDelta(5.0, $result->value, 0.0001);
    }

    // endregion

    // region mul() tests - quantity multiplication

    /**
     * Test multiplying two lengths produces an area.
     */
    public function testMulLengthsProducesArea(): void
    {
        $length1 = new Length(10, 'm');
        $length2 = new Length(1, 'ft');

        $result = $length1->mul($length2);

        $this->assertInstanceOf(Area::class, $result);
        $this->assertEquals(10, $result->value);
        $this->assertCount(2, $result->derivedUnit->unitTerms);
        $this->assertNotNull($result->derivedUnit->getUnitTermBySymbol('m'));
        $this->assertNotNull($result->derivedUnit->getUnitTermBySymbol('ft'));
    }

    /**
     * Test multiplying lengths with different SI prefixes.
     */
    public function testMulLengthsWithDifferentPrefixes(): void
    {
        $length1 = new Length(1, 'km');
        $length2 = new Length(1, 'mm');

        $result = $length1->mul($length2);

        $this->assertInstanceOf(Area::class, $result);
        $this->assertEquals(1, $result->value);
        $this->assertCount(2, $result->derivedUnit->unitTerms);
        $this->assertNotNull($result->derivedUnit->getUnitTermBySymbol('km'));
        $this->assertNotNull($result->derivedUnit->getUnitTermBySymbol('mm'));
    }

    /**
     * Test multiplying three lengths produces a volume.
     */
    public function testMulThreeLengthsProducesVolume(): void
    {
        $length1 = new Length(2, 'm');
        $length2 = new Length(3, 'm');
        $length3 = new Length(4, 'm');

        $result = $length1->mul($length2)->mul($length3);

        $this->assertInstanceOf(Volume::class, $result);
        $this->assertEqualsWithDelta(24.0, $result->value, 0.0001);
        $this->assertSame('m3', $result->derivedUnit->format(true));
    }

    /**
     * Test multiplying same unit collapses exponents.
     */
    public function testMulSameUnitCollapsesExponents(): void
    {
        $length1 = new Length(3, 'm');
        $length2 = new Length(4, 'm');

        $result = $length1->mul($length2);

        $this->assertInstanceOf(Area::class, $result);
        $this->assertEqualsWithDelta(12.0, $result->value, 0.0001);
        // Should collapse to m² not m*m
        $this->assertCount(1, $result->derivedUnit->unitTerms);
        $this->assertSame('m²', (string)$result->derivedUnit);
    }

    /**
     * Test multiplying produces dimensionless when exponents cancel.
     */
    public function testMulProducesDimensionless(): void
    {
        $length = new Length(6, 'm');
        $invLength = $length->inv(); // 1/6 m⁻¹

        $result = $length->mul($invLength);

        $this->assertInstanceOf(Quantity::class, $result);
        $this->assertEqualsWithDelta(1.0, $result->value, 0.0001);
        $this->assertTrue($result->isDimensionless());
        $this->assertSame('', (string)$result->derivedUnit);
    }

    /**
     * Test multiplying length by time produces compound unit.
     */
    public function testMulLengthByTimeProducesCompound(): void
    {
        $length = new Length(10, 'm');
        $time = new Time(5, 's');

        $result = $length->mul($time);

        $this->assertInstanceOf(Quantity::class, $result);
        $this->assertEqualsWithDelta(50.0, $result->value, 0.0001);
        $this->assertSame('LT', $result->derivedUnit->dimension);
    }

    /**
     * Test multiplying mass by length by time produces compound unit.
     */
    public function testMulMassLengthTime(): void
    {
        $mass = new Mass(2, 'kg');
        $length = new Length(3, 'm');
        $time = new Time(4, 's');

        $result = $mass->mul($length)->mul($time);

        $this->assertEqualsWithDelta(24.0, $result->value, 0.0001);
        $this->assertSame('MLT', $result->derivedUnit->dimension);
    }

    /**
     * Test multiplying with value and unit parameters.
     */
    public function testMulWithValueAndUnit(): void
    {
        $length = new Length(10, 'm');

        $result = $length->mul(5.0, 's');

        $this->assertEqualsWithDelta(50.0, $result->value, 0.0001);
        $this->assertSame('LT', $result->derivedUnit->dimension);
    }

    // endregion

    // region div() tests - scalar division

    /**
     * Test dividing by a scalar.
     */
    public function testDivByScalar(): void
    {
        $length = new Length(10, 'm');

        $result = $length->div(2.0);

        $this->assertInstanceOf(Length::class, $result);
        $this->assertEqualsWithDelta(5.0, $result->value, 0.0001);
        $this->assertSame('m', (string)$result->derivedUnit);
    }

    /**
     * Test dividing by negative scalar.
     */
    public function testDivByNegativeScalar(): void
    {
        $length = new Length(10, 'm');

        $result = $length->div(-2.0);

        $this->assertInstanceOf(Length::class, $result);
        $this->assertEqualsWithDelta(-5.0, $result->value, 0.0001);
    }

    /**
     * Test dividing by fractional scalar.
     */
    public function testDivByFractionalScalar(): void
    {
        $length = new Length(10, 'm');

        $result = $length->div(0.5);

        $this->assertInstanceOf(Length::class, $result);
        $this->assertEqualsWithDelta(20.0, $result->value, 0.0001);
    }

    /**
     * Test dividing by zero throws DivisionByZeroError.
     */
    public function testDivByZeroScalarThrows(): void
    {
        $length = new Length(10, 'm');

        $this->expectException(DivisionByZeroError::class);

        $length->div(0.0);
    }

    // endregion

    // region div() tests - quantity division

    /**
     * Test dividing area by length produces length.
     */
    public function testDivAreaByLengthProducesLength(): void
    {
        $area = new Area(100, 'm2');
        $length = new Length(10, 'm');

        $result = $area->div($length);

        $this->assertInstanceOf(Length::class, $result);
        $this->assertEqualsWithDelta(10.0, $result->value, 0.0001);
        $this->assertSame('m', (string)$result->derivedUnit);
    }

    /**
     * Test dividing length by time produces velocity (compound unit).
     */
    public function testDivLengthByTimeProducesVelocity(): void
    {
        $length = new Length(100, 'm');
        $time = new Time(10, 's');

        $result = $length->div($time);

        $this->assertInstanceOf(Quantity::class, $result);
        $this->assertEqualsWithDelta(10.0, $result->value, 0.0001);
        $this->assertSame('m·s⁻¹', (string)$result->derivedUnit);
    }

    /**
     * Test dividing by same unit produces dimensionless.
     */
    public function testDivSameUnitProducesDimensionless(): void
    {
        $length1 = new Length(10, 'm');
        $length2 = new Length(2, 'm');

        $result = $length1->div($length2);

        $this->assertInstanceOf(Quantity::class, $result);
        $this->assertEqualsWithDelta(5.0, $result->value, 0.0001);
        $this->assertTrue($result->isDimensionless());
        $this->assertSame('', (string)$result->derivedUnit);
    }

    /**
     * Test dividing volume by area produces length.
     */
    public function testDivVolumeByAreaProducesLength(): void
    {
        $volume = new Volume(1000, 'm3');
        $area = new Area(100, 'm2');

        $result = $volume->div($area);

        $this->assertInstanceOf(Length::class, $result);
        $this->assertEqualsWithDelta(10.0, $result->value, 0.0001);
        $this->assertSame('m', (string)$result->derivedUnit);
    }

    /**
     * Test dividing volume by length produces area.
     */
    public function testDivVolumeByLengthProducesArea(): void
    {
        $volume = new Volume(1000, 'm3');
        $length = new Length(10, 'm');

        $result = $volume->div($length);

        $this->assertInstanceOf(Area::class, $result);
        $this->assertEqualsWithDelta(100.0, $result->value, 0.0001);
        $this->assertSame('m²', (string)$result->derivedUnit);
    }

    /**
     * Test dividing by zero quantity throws DivisionByZeroError.
     */
    public function testDivByZeroQuantityThrows(): void
    {
        $length1 = new Length(10, 'm');
        $length2 = new Length(0, 'm');

        $this->expectException(DivisionByZeroError::class);

        $length1->div($length2);
    }

    /**
     * Test dividing with value and unit parameters.
     */
    public function testDivWithValueAndUnit(): void
    {
        $length = new Length(100, 'm');

        $result = $length->div(10.0, 's');

        $this->assertEqualsWithDelta(10.0, $result->value, 0.0001);
        $this->assertSame('LT-1', $result->derivedUnit->dimension);
    }

    /**
     * Test chained division.
     */
    public function testDivChained(): void
    {
        $length = new Length(100, 'm');
        $time1 = new Time(2, 's');
        $time2 = new Time(5, 's');

        // 100 m / 2 s / 5 s = 10 m/s²
        $result = $length->div($time1)->div($time2);

        $this->assertEqualsWithDelta(10.0, $result->value, 0.0001);
        $this->assertSame('LT-2', $result->derivedUnit->dimension);
    }

    // endregion

    // region Mixed operations tests

    /**
     * Test combining mul and div operations.
     */
    public function testMulAndDivCombined(): void
    {
        $mass = new Mass(10, 'kg');
        $length = new Length(5, 'm');
        $time = new Time(2, 's');

        // Force = mass × acceleration = kg × m / s²
        $result = $mass->mul($length)->div($time)->div($time);

        $this->assertEqualsWithDelta(12.5, $result->value, 0.0001);
        $this->assertSame('MLT-2', $result->derivedUnit->dimension);
    }

    /**
     * Test that order of operations matters for value but not dimension.
     */
    public function testOrderOfOperations(): void
    {
        $a = new Length(10, 'm');
        $b = new Length(2, 'm');
        $c = new Length(5, 'm');

        // (a × b) / c vs a × (b / c) - same dimension, different value
        $result1 = $a->mul($b)->div($c);
        $result2 = $a->mul($b->div($c));

        $this->assertSame($result1->derivedUnit->dimension, $result2->derivedUnit->dimension);
        $this->assertEqualsWithDelta(4.0, $result1->value, 0.0001);  // (10 × 2) / 5 = 4
        $this->assertEqualsWithDelta(4.0, $result2->value, 0.0001);  // 10 × (2 / 5) = 4
    }

    // endregion
}
