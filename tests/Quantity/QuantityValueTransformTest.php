<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Quantity;

use DomainException;
use Galaxon\Core\Traits\FloatAssertions;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Temperature;
use Galaxon\Quantities\Services\UnitService;
use Galaxon\Quantities\UnitSystem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RoundingMode;

/**
 * Tests for transformation operations on Quantity objects.
 */
#[CoversClass(Quantity::class)]
final class QuantityValueTransformTest extends TestCase
{
    use FloatAssertions;

    // region Setup

    public static function setUpBeforeClass(): void
    {
        // Load Imperial/US units for cross-system tests.
        UnitService::loadSystem(UnitSystem::Imperial);
        UnitService::loadSystem(UnitSystem::UsCustomary);
    }

    // endregion

    // region withValue() tests

    /**
     * Test withValue() preserves unit.
     */
    public function testWithValuePreservesUnit(): void
    {
        $length = new Length(10, 'km');
        $newLength = $length->withValue(20);

        $this->assertSame(20.0, $newLength->value);
        $this->assertSame('km', $newLength->derivedUnit->asciiSymbol);
    }

    /**
     * Test withValue() returns a new instance when the value is unchanged.
     */
    public function testWithValueSameValueReturnsNewInstance(): void
    {
        $length = new Length(10, 'm');
        $length2 = $length->withValue(10);

        $this->assertNotSame($length, $length2);
    }

    /**
     * Test withValue() with zero.
     */
    public function testWithValueZero(): void
    {
        $length = new Length(10, 'm');
        $zero = $length->withValue(0);

        $this->assertSame(0.0, $zero->value);
        $this->assertSame('m', $zero->derivedUnit->asciiSymbol);
    }

    /**
     * Test withValue() with negative value.
     */
    public function testWithValueNegative(): void
    {
        $length = new Length(10, 'm');
        $neg = $length->withValue(-5);

        $this->assertSame(-5.0, $neg->value);
        $this->assertSame('m', $neg->derivedUnit->asciiSymbol);
    }

    /**
     * Test withValue() with non-finite value throws DomainException.
     */
    public function testWithValueInfinityThrowsDomainException(): void
    {
        $this->expectException(DomainException::class);

        $length = new Length(10, 'm');
        $length->withValue(INF);
    }

    /**
     * Test withValue() with NAN throws DomainException.
     */
    public function testWithValueNanThrowsDomainException(): void
    {
        $this->expectException(DomainException::class);

        $length = new Length(10, 'm');
        $length->withValue(NAN);
    }

    // endregion

    // region abs() tests

    /**
     * Test abs() on positive value.
     */
    public function testAbsPositive(): void
    {
        $length = new Length(5, 'm');
        $abs = $length->abs();

        $this->assertSame(5.0, $abs->value);
    }

    /**
     * Test abs() on negative value.
     */
    public function testAbsNegative(): void
    {
        $temp = new Temperature(-10, 'degC');
        $abs = $temp->abs();

        $this->assertSame(10.0, $abs->value);
    }

    /**
     * Test abs() on zero.
     */
    public function testAbsZero(): void
    {
        $length = new Length(0, 'm');
        $abs = $length->abs();

        $this->assertSame(0.0, $abs->value);
    }

    // endregion

    // region round() tests

    /**
     * Test round() with default precision (0 decimal places).
     */
    public function testRoundDefaultPrecision(): void
    {
        $length = new Length(1.567, 'm');
        $rounded = $length->round();

        $this->assertSame(2.0, $rounded->value);
        $this->assertSame('m', $rounded->derivedUnit->asciiSymbol);
    }

    /**
     * Test round() with specific precision.
     */
    public function testRoundWithPrecision(): void
    {
        $length = new Length(1.567, 'm');

        $this->assertSame(1.6, $length->round(1)->value);
        $this->assertSame(1.57, $length->round(2)->value);
        $this->assertSame(1.567, $length->round(3)->value);
    }

    /**
     * Test round() rounds half away from zero by default.
     */
    public function testRoundHalfAwayFromZero(): void
    {
        $pos = new Length(2.5, 'm');
        $neg = new Length(-2.5, 'm');

        $this->assertSame(3.0, $pos->round()->value);
        $this->assertSame(-3.0, $neg->round()->value);
    }

    /**
     * Test round() with explicit rounding mode.
     */
    public function testRoundWithMode(): void
    {
        $length = new Length(2.5, 'm');
        $rounded = $length->round(0, RoundingMode::HalfEven);

        $this->assertSame(2.0, $rounded->value);
    }

    /**
     * Test round() on already-rounded value.
     */
    public function testRoundAlreadyRounded(): void
    {
        $length = new Length(3.0, 'm');
        $rounded = $length->round();

        $this->assertSame(3.0, $rounded->value);
    }

    /**
     * Test round() with negative precision.
     */
    public function testRoundNegativePrecision(): void
    {
        $length = new Length(1567.0, 'm');

        $this->assertSame(1570.0, $length->round(-1)->value);
        $this->assertSame(1600.0, $length->round(-2)->value);
    }

    // endregion

    // region floor() tests

    /**
     * Test floor() on positive value.
     */
    public function testFloorPositive(): void
    {
        $length = new Length(1.9, 'm');
        $floored = $length->floor();

        $this->assertSame(1.0, $floored->value);
        $this->assertSame('m', $floored->derivedUnit->asciiSymbol);
    }

    /**
     * Test floor() on negative value.
     */
    public function testFloorNegative(): void
    {
        $temp = new Temperature(-1.1, 'degC');
        $floored = $temp->floor();

        $this->assertSame(-2.0, $floored->value);
    }

    /**
     * Test floor() on integer value.
     */
    public function testFloorAlreadyInteger(): void
    {
        $length = new Length(3.0, 'm');
        $floored = $length->floor();

        $this->assertSame(3.0, $floored->value);
    }

    // endregion

    // region ceil() tests

    /**
     * Test ceil() on positive value.
     */
    public function testCeilPositive(): void
    {
        $length = new Length(1.1, 'm');
        $ceiled = $length->ceil();

        $this->assertSame(2.0, $ceiled->value);
        $this->assertSame('m', $ceiled->derivedUnit->asciiSymbol);
    }

    /**
     * Test ceil() on negative value.
     */
    public function testCeilNegative(): void
    {
        $temp = new Temperature(-1.9, 'degC');
        $ceiled = $temp->ceil();

        $this->assertSame(-1.0, $ceiled->value);
    }

    /**
     * Test ceil() on integer value.
     */
    public function testCeilAlreadyInteger(): void
    {
        $length = new Length(3.0, 'm');
        $ceiled = $length->ceil();

        $this->assertSame(3.0, $ceiled->value);
    }

    // endregion
}
