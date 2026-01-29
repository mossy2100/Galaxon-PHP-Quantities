<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests;

use Galaxon\Core\Exceptions\IncomparableTypesException;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\QuantityType\Temperature;
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for comparison operations on Quantity objects.
 */
#[CoversClass(Quantity::class)]
final class QuantityCompareTest extends TestCase
{
    // region Setup

    public static function setUpBeforeClass(): void
    {
        // Load Imperial/US units for cross-system tests.
        UnitRegistry::loadSystem(System::Imperial);
        UnitRegistry::loadSystem(System::US);
    }

    // endregion

    // region compare() tests - same units

    /**
     * Test compare() returns 0 for equal values.
     */
    public function testCompareEqual(): void
    {
        $a = new Length(100, 'm');
        $b = new Length(100, 'm');

        $this->assertSame(0, $a->compare($b));
    }

    /**
     * Test compare() returns -1 when first is less.
     */
    public function testCompareLessThan(): void
    {
        $a = new Length(50, 'm');
        $b = new Length(100, 'm');

        $this->assertSame(-1, $a->compare($b));
    }

    /**
     * Test compare() returns 1 when first is greater.
     */
    public function testCompareGreaterThan(): void
    {
        $a = new Length(100, 'm');
        $b = new Length(50, 'm');

        $this->assertSame(1, $a->compare($b));
    }

    // endregion

    // region compare() tests - different units

    /**
     * Test compare() with different units (same dimension).
     */
    public function testCompareDifferentUnits(): void
    {
        $km = new Length(1, 'km');
        $m = new Length(1000, 'm');

        $this->assertSame(0, $km->compare($m));
    }

    /**
     * Test compare() with metres and feet.
     */
    public function testCompareMetresAndFeet(): void
    {
        $m = new Length(1, 'm');
        $ft = new Length(3, 'ft');

        // 1 m = 3.28... ft, so 1 m > 3 ft
        $this->assertSame(1, $m->compare($ft));
    }

    /**
     * Test compare() with different dimensions throws exception.
     */
    public function testCompareDifferentDimensionsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $length = new Length(100, 'm');
        $mass = new Mass(100, 'kg');
        $length->compare($mass);
    }

    /**
     * Test compare() with non-Quantity throws exception.
     */
    public function testCompareNonQuantityThrowsException(): void
    {
        $this->expectException(IncomparableTypesException::class);

        $length = new Length(100, 'm');
        $length->compare(100);
    }

    // endregion

    // region approxEqual() tests

    /**
     * Test approxEqual() for exactly equal values.
     */
    public function testApproxEqualExact(): void
    {
        $a = new Length(100, 'm');
        $b = new Length(100, 'm');

        $this->assertTrue($a->approxEqual($b));
    }

    /**
     * Test approxEqual() for values within tolerance.
     */
    public function testApproxEqualWithinTolerance(): void
    {
        $a = new Length(100, 'm');
        $b = new Length(100.0000001, 'm');

        $this->assertTrue($a->approxEqual($b));
    }

    /**
     * Test approxEqual() for values outside tolerance.
     */
    public function testApproxEqualOutsideTolerance(): void
    {
        $a = new Length(100, 'm');
        $b = new Length(101, 'm');

        $this->assertFalse($a->approxEqual($b));
    }

    /**
     * Test approxEqual() with different units.
     */
    public function testApproxEqualDifferentUnits(): void
    {
        $km = new Length(1, 'km');
        $m = new Length(1000, 'm');

        $this->assertTrue($km->approxEqual($m));
    }

    /**
     * Test approxEqual() with cross-system units.
     */
    public function testApproxEqualCrossSystem(): void
    {
        $inch = new Length(1, 'in');
        $cm = new Length(2.54, 'cm');

        $this->assertTrue($inch->approxEqual($cm));
    }

    /**
     * Test approxEqual() with custom tolerance.
     */
    public function testApproxEqualCustomTolerance(): void
    {
        $a = new Length(100, 'm');
        $b = new Length(105, 'm');

        // Not equal with default tolerance
        $this->assertFalse($a->approxEqual($b));

        // Equal with 10% relative tolerance
        $this->assertTrue($a->approxEqual($b, 0.1));
    }

    /**
     * Test approxEqual() returns false for different dimensions.
     */
    public function testApproxEqualDifferentDimensions(): void
    {
        $length = new Length(100, 'm');
        $mass = new Mass(100, 'kg');

        $this->assertFalse($length->approxEqual($mass));
    }

    /**
     * Test approxEqual() returns false for non-Quantity.
     */
    public function testApproxEqualNonQuantity(): void
    {
        $length = new Length(100, 'm');

        $this->assertFalse($length->approxEqual(100));
    }

    // endregion

    // region Comparison with zero

    /**
     * Test comparing zero values.
     */
    public function testCompareZeroValues(): void
    {
        $a = new Length(0, 'm');
        $b = new Length(0, 'km');

        $this->assertSame(0, $a->compare($b));
    }

    /**
     * Test comparing with zero.
     */
    public function testCompareWithZero(): void
    {
        $pos = new Length(1, 'm');
        $zero = new Length(0, 'm');
        $neg = new Temperature(-10, 'degC');

        $this->assertSame(1, $pos->compare($zero));
        $this->assertSame(-1, $zero->compare($pos));
    }

    // endregion

    // region Comparison with negative values

    /**
     * Test comparing negative values.
     */
    public function testCompareNegativeValues(): void
    {
        $a = new Temperature(-10, 'degC');
        $b = new Temperature(-20, 'degC');

        $this->assertSame(1, $a->compare($b));
        $this->assertSame(-1, $b->compare($a));
    }

    // endregion
}
