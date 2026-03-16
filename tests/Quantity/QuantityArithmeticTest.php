<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Quantity;

use DivisionByZeroError;
use DomainException;
use Galaxon\Core\Traits\FloatAssertions;
use Galaxon\Quantities\Exceptions\DimensionMismatchException;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType\Force;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\QuantityType\Temperature;
use Galaxon\Quantities\QuantityType\Time;
use Galaxon\Quantities\Services\UnitService;
use Galaxon\Quantities\UnitSystem;
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
        UnitService::loadSystem(UnitSystem::Imperial);
        UnitService::loadSystem(UnitSystem::UsCustomary);
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
     * Test add() with cross-system unit conversion.
     */
    public function testAddWithConversion(): void
    {
        $a = new Length(1, 'mi');
        $b = new Length(1, 'km');
        $sum = $a->add($b);

        // 1 mi + 1 km ≈ 1 mi + 0.621371 mi ≈ 1.621371 mi
        $this->assertApproxEqual(1.6213711922, $sum->value);
        $this->assertSame('mi', $sum->derivedUnit->asciiSymbol);
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

    /**
     * Test add() throws DimensionMismatchException when adding incompatible dimensions.
     */
    public function testAddIncompatibleDimensionsThrows(): void
    {
        $this->expectException(DimensionMismatchException::class);

        $length = new Length(10, 'm');
        $time = new Time(5, 's');
        $length->add($time);
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
     * Test sub() with cross-system unit conversion.
     */
    public function testSubWithConversion(): void
    {
        $a = new Length(1, 'mi');
        $b = new Length(1, 'km');
        $diff = $a->sub($b);

        // 1 mi - 1 km ≈ 1 mi - 0.621371 mi ≈ 0.378629 mi
        $this->assertApproxEqual(0.3786288078, $diff->value);
        $this->assertSame('mi', $diff->derivedUnit->asciiSymbol);
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

    /**
     * Test sub() throws DimensionMismatchException when subtracting incompatible dimensions.
     */
    public function testSubIncompatibleDimensionsThrows(): void
    {
        $this->expectException(DimensionMismatchException::class);

        $mass = new Mass(50, 'kg');
        $length = new Length(25, 'm');
        $mass->sub($length);
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
     * Test mul() by zero.
     */
    public function testMulByZero(): void
    {
        $length = new Length(10, 'm');
        $result = $length->mul(0);

        $this->assertSame(0.0, $result->value);
    }

    /**
     * Test mul() by negative scalar.
     */
    public function testMulByNegativeScalar(): void
    {
        $length = new Length(10, 'm');
        $result = $length->mul(-3);

        $this->assertSame(-30.0, $result->value);
        $this->assertSame('m', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test mul() with a string unit creates compound unit with value 1.
     */
    public function testMulByStringUnit(): void
    {
        $length = new Length(10, 'm');
        $result = $length->mul('s');

        $this->assertSame(10.0, $result->value);
        $this->assertSame('m*s', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test mul() that overflows to infinity throws DomainException.
     */
    public function testMulOverflowThrowsDomainException(): void
    {
        $this->expectException(DomainException::class);

        $big = new Length(1e308, 'm');
        $big->mul(1e308);
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

    /**
     * Test div() by negative scalar.
     */
    public function testDivByNegativeScalar(): void
    {
        $length = new Length(10, 'm');
        $result = $length->div(-2);

        $this->assertSame(-5.0, $result->value);
        $this->assertSame('m', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test div() with a string unit creates inverse compound unit.
     */
    public function testDivByStringUnit(): void
    {
        $length = new Length(10, 'm');
        $result = $length->div('s');

        $this->assertSame(10.0, $result->value);
        $this->assertSame('m/s', $result->derivedUnit->asciiSymbol);
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

    /**
     * Test inv() on a negative value.
     */
    public function testInvNegative(): void
    {
        $time = new Time(-4, 's');
        $result = $time->inv();

        $this->assertSame(-0.25, $result->value);
        $this->assertSame('s-1', $result->derivedUnit->asciiSymbol);
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
     * Test pow() with exponent 1 returns same value and unit.
     */
    public function testPowOne(): void
    {
        $length = new Length(5, 'km');
        $result = $length->pow(1);

        $this->assertSame(5.0, $result->value);
        $this->assertSame('km', $result->derivedUnit->asciiSymbol);
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
     * Test pow() with a prefixed unit.
     */
    public function testPowWithPrefixedUnit(): void
    {
        $length = new Length(3, 'km');
        $area = $length->pow(2);

        $this->assertSame(9.0, $area->value);
        $this->assertSame('km2', $area->derivedUnit->asciiSymbol);
    }

    /**
     * Test pow() with a compound unit.
     */
    public function testPowWithCompoundUnit(): void
    {
        $speed = Quantity::create(2, 'm/s');
        $result = $speed->pow(2);

        $this->assertSame(4.0, $result->value);
        $this->assertSame('m2/s2', $result->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Chained operations tests

    /**
     * Test chaining arithmetic operations.
     */
    public function testChainedOperations(): void
    {
        $length = new Length(10, 'm');
        $result = $length->mul(2)->addnew Length(5, 'm')->sub(new Length(3, 'm'));

        $this->assertSame(22.0, $result->value);
    }

    // endregion

    // region Cross-system arithmetic tests

    /**
     * Test adding meters and feet.
     */
    public function testAddMetersAndFeet(): void
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
