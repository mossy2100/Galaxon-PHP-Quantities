<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Quantity;

use DivisionByZeroError;
use DomainException;
use Galaxon\Core\Traits\FloatAssertions;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType\Force;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\QuantityType\Temperature;
use Galaxon\Quantities\QuantityType\Time;
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for arithmetic operations on Quantity objects.
 */
#[CoversClass(Quantity::class)]
final class QuantityArithmeticTest extends TestCase
{
    use FloatAssertions;

    // region Setup

    public static function setUpBeforeClass(): void
    {
        // Load Imperial/US units for cross-system tests.
        UnitRegistry::loadSystem(System::Imperial);
        UnitRegistry::loadSystem(System::UsCustomary);
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

    // region neg() tests

    /**
     * Test neg() on positive value.
     */
    public function testNegPositive(): void
    {
        $length = new Length(5, 'm');
        $neg = $length->neg();

        $this->assertSame(-5.0, $neg->value);
    }

    /**
     * Test neg() on negative value.
     */
    public function testNegNegative(): void
    {
        $temp = new Temperature(-10, 'degC');
        $neg = $temp->neg();

        $this->assertSame(10.0, $neg->value);
    }

    /**
     * Test neg() on zero.
     */
    public function testNegZero(): void
    {
        $length = new Length(0, 'm');
        $neg = $length->neg();

        $this->assertSame(0.0, $neg->value);
    }

    // endregion

    // region add() tests

    /**
     * Test add() with same units.
     */
    public function testAddSameUnits(): void
    {
        $a = new Length(100, 'm');
        $b = new Length(50, 'm');
        $sum = $a->add($b);

        $this->assertSame(150.0, $sum->value);
        $this->assertSame('m', $sum->derivedUnit->asciiSymbol);
    }

    /**
     * Test add() with different units (same dimension).
     */
    public function testAddDifferentUnits(): void
    {
        $a = new Length(100, 'm');
        $b = new Length(2, 'km');
        $sum = $a->add($b);

        $this->assertSame(2100.0, $sum->value);
        $this->assertSame('m', $sum->derivedUnit->asciiSymbol);
    }

    /**
     * Test add() with value and unit.
     */
    public function testAddValueAndUnit(): void
    {
        $a = new Length(100, 'm');
        $sum = $a->add(50, 'cm');

        $this->assertSame(100.5, $sum->value);
    }

    /**
     * Test add() preserves the unit of the first operand.
     */
    public function testAddPreservesFirstUnit(): void
    {
        $km = new Length(1, 'km');
        $m = new Length(500, 'm');
        $sum = $km->add($m);

        $this->assertSame(1.5, $sum->value);
        $this->assertSame('km', $sum->derivedUnit->asciiSymbol);
    }

    // endregion

    // region sub() tests

    /**
     * Test sub() with same units.
     */
    public function testSubSameUnits(): void
    {
        $a = new Length(100, 'm');
        $b = new Length(30, 'm');
        $diff = $a->sub($b);

        $this->assertSame(70.0, $diff->value);
    }

    /**
     * Test sub() with different units.
     */
    public function testSubDifferentUnits(): void
    {
        $a = new Length(2, 'km');
        $b = new Length(500, 'm');
        $diff = $a->sub($b);

        $this->assertSame(1.5, $diff->value);
        $this->assertSame('km', $diff->derivedUnit->asciiSymbol);
    }

    /**
     * Test sub() with value and unit.
     */
    public function testSubValueAndUnit(): void
    {
        $a = new Length(100, 'm');
        $diff = $a->sub(50, 'cm');

        $this->assertSame(99.5, $diff->value);
    }

    /**
     * Test sub() resulting in negative value.
     */
    public function testSubNegativeResult(): void
    {
        $a = new Length(100, 'm');
        $b = new Length(200, 'm');
        $diff = $a->sub($b);

        $this->assertSame(-100.0, $diff->value);
    }

    // endregion

    // region mul() tests

    /**
     * Test mul() with scalar.
     */
    public function testMulByScalar(): void
    {
        $length = new Length(10, 'm');
        $result = $length->mul(3);

        $this->assertSame(30.0, $result->value);
        $this->assertSame('m', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test mul() with another Quantity creates compound unit.
     */
    public function testMulByQuantity(): void
    {
        $length = new Length(10, 'm');
        $width = new Length(5, 'm');
        $area = $length->mul($width);

        $this->assertSame(50.0, $area->value);
        $this->assertSame('m2', $area->derivedUnit->asciiSymbol);
    }

    /**
     * Test mul() with different unit types.
     */
    public function testMulDifferentTypes(): void
    {
        $force = new Force(10, 'N');
        $distance = new Length(5, 'm');
        $energy = $force->mul($distance);

        $this->assertSame(50.0, $energy->value);
        $this->assertSame('N*m', $energy->derivedUnit->asciiSymbol);
    }

    /**
     * Test mul() with value and unit.
     */
    public function testMulValueAndUnit(): void
    {
        $length = new Length(10, 'm');
        $result = $length->mul(5, 'm');

        $this->assertSame(50.0, $result->value);
        $this->assertSame('m2', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test mul() by zero.
     */
    public function testMulByZero(): void
    {
        $length = new Length(10, 'm');
        $result = $length->mul(0);

        $this->assertSame(0.0, $result->value);
    }

    // endregion

    // region div() tests

    /**
     * Test div() by scalar.
     */
    public function testDivByScalar(): void
    {
        $length = new Length(10, 'm');
        $result = $length->div(2);

        $this->assertSame(5.0, $result->value);
        $this->assertSame('m', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test div() by another Quantity.
     */
    public function testDivByQuantity(): void
    {
        $distance = new Length(100, 'm');
        $time = new Time(10, 's');
        $velocity = $distance->div($time);

        $this->assertSame(10.0, $velocity->value);
        $this->assertSame('m/s', $velocity->derivedUnit->asciiSymbol);
    }

    /**
     * Test div() by same unit cancels out.
     */
    public function testDivBySameUnitCancels(): void
    {
        $a = new Length(100, 'm');
        $b = new Length(50, 'm');
        $ratio = $a->div($b);

        $this->assertSame(2.0, $ratio->value);
        $this->assertTrue($ratio->derivedUnit->isDimensionless());
    }

    /**
     * Test div() by zero throws exception.
     */
    public function testDivByZeroScalarThrowsException(): void
    {
        $this->expectException(DivisionByZeroError::class);

        $length = new Length(10, 'm');
        $length->div(0);
    }

    /**
     * Test div() by zero Quantity throws exception.
     */
    public function testDivByZeroQuantityThrowsException(): void
    {
        $this->expectException(DivisionByZeroError::class);

        $a = new Length(10, 'm');
        $b = new Length(0, 'm');
        $a->div($b);
    }

    // endregion

    // region inv() tests

    /**
     * Test inv() basic case.
     */
    public function testInv(): void
    {
        $time = new Time(2, 's');
        $frequency = $time->inv();

        $this->assertSame(0.5, $frequency->value);
        $this->assertSame('s-1', $frequency->derivedUnit->asciiSymbol);
    }

    /**
     * Test inv() on zero throws exception.
     */
    public function testInvZeroThrowsException(): void
    {
        $this->expectException(DivisionByZeroError::class);

        $time = new Time(0, 's');
        $time->inv();
    }

    // endregion

    // region pow() tests

    /**
     * Test pow() with positive exponent.
     */
    public function testPowPositive(): void
    {
        $length = new Length(3, 'm');
        $volume = $length->pow(3);

        $this->assertSame(27.0, $volume->value);
        $this->assertSame('m3', $volume->derivedUnit->asciiSymbol);
    }

    /**
     * Test pow() with negative exponent.
     */
    public function testPowNegative(): void
    {
        $length = new Length(2, 'm');
        $result = $length->pow(-2);

        $this->assertSame(0.25, $result->value);
        $this->assertSame('m-2', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test pow() with zero exponent throws exception.
     */
    public function testPowZeroThrowsException(): void
    {
        $this->expectException(DomainException::class);

        $length = new Length(5, 'm');
        $length->pow(0);
    }

    /**
     * Test pow(2) produces square unit.
     */
    public function testPowSquare(): void
    {
        $length = new Length(4, 'm');
        $area = $length->pow(2);

        $this->assertSame(16.0, $area->value);
        $this->assertSame('m2', $area->derivedUnit->asciiSymbol);
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

    // endregion

    // region Chained operations tests

    /**
     * Test chaining arithmetic operations.
     */
    public function testChainedOperations(): void
    {
        $length = new Length(10, 'm');
        $result = $length->mul(2)->add(5, 'm')->sub(3, 'm');

        $this->assertSame(22.0, $result->value);
    }

    // endregion

    // region Cross-system arithmetic tests

    /**
     * Test adding metres and feet.
     */
    public function testAddMetresAndFeet(): void
    {
        $m = new Length(1, 'm');
        $ft = new Length(1, 'ft');
        $sum = $m->add($ft);

        // 1 m + 1 ft = 1 m + 0.3048 m = 1.3048 m
        $this->assertApproxEqual(1.3048, $sum->value);
        $this->assertSame('m', $sum->derivedUnit->asciiSymbol);
    }

    /**
     * Test subtracting kilograms and pounds.
     */
    public function testSubKilogramsAndPounds(): void
    {
        $kg = new Mass(1, 'kg');
        $lb = new Mass(1, 'lb');
        $diff = $kg->sub($lb);

        // 1 kg - 1 lb = 1 kg - 0.45359237 kg = 0.54640763 kg
        $this->assertApproxEqual(1 - 0.45359237, $diff->value);
    }

    // endregion
}
