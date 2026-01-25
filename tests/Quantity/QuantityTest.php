<?php

declare(strict_types=1);

namespace Quantity;

use DomainException;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType\Area;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Time;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use stdClass;

/**
 * Test class for Quantity methods: formatting, comparison, and parts.
 *
 * Constructor, create(), and parse() tests are in QuantityCreateTest.
 * Conversion tests (to, toSi, convert) are in QuantityConvertTest.
 * Arithmetic tests are in QuantityArithmeticTest.
 */
#[CoversClass(Quantity::class)]
#[CoversClass(Area::class)]
#[CoversClass(Length::class)]
#[CoversClass(Time::class)]
final class QuantityTest extends TestCase
{
    // region Formatting tests (format, __toString)

    /**
     * Test format with fixed notation.
     */
    public function testFormatFixed(): void
    {
        $length = new Length(123.456, 'm');

        $this->assertSame('123.46 m', $length->format('f', 2));
    }

    /**
     * Test format with scientific notation.
     */
    public function testFormatScientific(): void
    {
        $length = new Length(1500, 'm');

        $this->assertSame('1.5e+3 m', $length->format('e', 1));
    }

    /**
     * Test format with general notation.
     */
    public function testFormatGeneral(): void
    {
        $length = new Length(1500, 'm');

        $this->assertSame('1500 m', $length->format('g', 4));
    }

    /**
     * Test format with trimZeros disabled.
     */
    public function testFormatNoTrimZeros(): void
    {
        $length = new Length(10, 'm');

        $this->assertSame('10.00 m', $length->format('f', 2, false));
    }

    /**
     * Test format with includeSpace enabled.
     */
    public function testFormatWithSpace(): void
    {
        $length = new Length(100, 'm');

        $this->assertSame('100 m', $length->format('f', 0, true, true));
    }

    /**
     * Test format throws for invalid specifier.
     */
    public function testFormatThrowsForInvalidSpecifier(): void
    {
        $length = new Length(100, 'm');

        $this->expectException(DomainException::class);

        $length->format('x');
    }

    /**
     * Test format throws for negative precision.
     */
    public function testFormatThrowsForNegativePrecision(): void
    {
        $length = new Length(100, 'm');

        $this->expectException(DomainException::class);

        $length->format('f', -1);
    }

    /**
     * Test __toString returns formatted string.
     */
    public function testToString(): void
    {
        $length = new Length(100, 'm');

        // __toString has a space between value and unit
        $this->assertSame('100 m', (string)$length);
    }

    /**
     * Test __toString with decimal value.
     */
    public function testToStringWithDecimal(): void
    {
        $length = new Length(123.456, 'km');

        $this->assertSame('123.456 km', (string)$length);
    }

    /**
     * Test __toString normalizes negative zero.
     */
    public function testToStringNormalizesNegativeZero(): void
    {
        $length = new Length(-0.0, 'm');

        $this->assertSame('0 m', (string)$length);
    }

    // endregion

    // region Comparison tests (compare, approxEqual)

    /**
     * Test compare returns -1 when less than.
     */
    public function testCompareLessThan(): void
    {
        $a = new Length(10, 'm');
        $b = new Length(20, 'm');

        $this->assertSame(-1, $a->compare($b));
    }

    /**
     * Test compare returns 0 when equal.
     */
    public function testCompareEqual(): void
    {
        $a = new Length(10, 'm');
        $b = new Length(10, 'm');

        $this->assertSame(0, $a->compare($b));
    }

    /**
     * Test compare returns 1 when greater than.
     */
    public function testCompareGreaterThan(): void
    {
        $a = new Length(20, 'm');
        $b = new Length(10, 'm');

        $this->assertSame(1, $a->compare($b));
    }

    /**
     * Test compare converts units automatically.
     */
    public function testCompareConvertsUnits(): void
    {
        $a = new Length(1, 'km');
        $b = new Length(1000, 'm');

        $this->assertSame(0, $a->compare($b));
    }

    /**
     * Test compare throws for different Quantity types.
     */
    public function testCompareThrowsForDifferentTypes(): void
    {
        $length = new Length(100, 'm');
        $area = new Area(100, 'm2');

        $this->expectException(InvalidArgumentException::class);

        $length->compare($area);
    }

    /**
     * Test compare throws for non-Quantity.
     */
    public function testCompareThrowsForNonQuantity(): void
    {
        $length = new Length(100, 'm');

        $this->expectException(InvalidArgumentException::class);

        $length->compare(100);
    }

    /**
     * Test approxEqual returns true for equal values.
     */
    public function testApproxEqualTrue(): void
    {
        $a = new Length(100, 'm');
        $b = new Length(100, 'm');

        $this->assertTrue($a->approxEqual($b));
    }

    /**
     * Test approxEqual returns true within tolerance.
     */
    public function testApproxEqualWithinTolerance(): void
    {
        $a = new Length(100, 'm');
        $b = new Length(100.0000001, 'm');

        $this->assertTrue($a->approxEqual($b));
    }

    /**
     * Test approxEqual returns false for different values.
     */
    public function testApproxEqualFalse(): void
    {
        $a = new Length(100, 'm');
        $b = new Length(200, 'm');

        $this->assertFalse($a->approxEqual($b));
    }

    /**
     * Test approxEqual converts units automatically.
     */
    public function testApproxEqualConvertsUnits(): void
    {
        $a = new Length(1, 'km');
        $b = new Length(1000, 'm');

        $this->assertTrue($a->approxEqual($b));
    }

    /**
     * Test approxEqual returns false for different Quantity types.
     */
    public function testApproxEqualDifferentTypes(): void
    {
        $length = new Length(100, 'm');
        $area = new Area(100, 'm2');

        $this->assertFalse($length->approxEqual($area));
    }

    /**
     * Test approxEqual returns false for non-Quantity.
     */
    public function testApproxEqualNonQuantity(): void
    {
        $length = new Length(100, 'm');

        $this->assertFalse($length->approxEqual(100));
        $this->assertFalse($length->approxEqual('100m'));
        $this->assertFalse($length->approxEqual(new stdClass()));
    }

    // endregion

    // region Parts methods tests

    /**
     * Test getPartUnits returns empty array in base implementation.
     */
    public function testGetPartUnitsReturnsEmptyByDefault(): void
    {
        // Length doesn't override getPartUnits(), so it returns empty array.
        $this->assertSame([], Length::getPartUnits());
    }

    /**
     * Test validateSmallestUnit throws for invalid unit.
     */
    public function testValidateSmallestUnitThrowsForInvalidUnit(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Invalid part unit');

        // Use reflection to call protected method on a class with part units defined.
        $method = new ReflectionMethod(Time::class, 'validateSmallestUnit');
        $method->invoke(null, 'invalid');
    }

    /**
     * Test validatePrecision accepts null.
     */
    public function testValidatePrecisionAcceptsNull(): void
    {
        $this->expectNotToPerformAssertions();

        $method = new ReflectionMethod(Length::class, 'validatePrecision');
        $method->invoke(null, null);
    }

    /**
     * Test validatePrecision accepts zero.
     */
    public function testValidatePrecisionAcceptsZero(): void
    {
        $this->expectNotToPerformAssertions();

        $method = new ReflectionMethod(Length::class, 'validatePrecision');
        $method->invoke(null, 0);
    }

    /**
     * Test validatePrecision accepts positive integer.
     */
    public function testValidatePrecisionAcceptsPositive(): void
    {
        $this->expectNotToPerformAssertions();

        $method = new ReflectionMethod(Length::class, 'validatePrecision');
        $method->invoke(null, 5);
    }

    /**
     * Test validatePrecision throws for negative value.
     */
    public function testValidatePrecisionThrowsForNegative(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid precision');

        $method = new ReflectionMethod(Length::class, 'validatePrecision');
        $method->invoke(null, -1);
    }

    /**
     * Test validateAndTransformPartUnits throws when getPartUnits returns empty.
     */
    public function testValidateAndTransformPartUnitsThrowsWhenEmpty(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('must define the part units');

        $method = new ReflectionMethod(Length::class, 'validateAndTransformPartUnits');
        $method->invoke(null);
    }

    /**
     * Test fromPartsArray throws when getPartUnits returns empty.
     */
    public function testFromPartsArrayThrowsWhenPartUnitsEmpty(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('must define the part units');

        Length::fromPartsArray([
            'm' => 100,
        ]);
    }

    /**
     * Test toParts throws when getPartUnits returns empty.
     */
    public function testToPartsThrowsWhenPartUnitsEmpty(): void
    {
        $length = new Length(100, 'm');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('must define the part units');

        $length->toPartsArray('m');
    }

    /**
     * Test validateAndTransformPartUnits throws when a part unit is invalid.
     */
    public function testValidateAndTransformPartUnitsThrowsForInvalidPartUnit(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("Invalid part unit: 'invalid'");

        Badness::fromPartsArray([
            'foo' => 10,
        ]);
    }

    /**
     * Test validateAndTransformPartUnits throws when a symbol is empty.
     */
    public function testValidateAndTransformPartUnitsThrowsForEmptySymbol(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Unit symbols must be non-empty strings');

        EmptySymbol::fromPartsArray([
            'foo' => 10,
        ]);
    }

    // endregion
}

class Coolness extends Quantity
{
    /** @return array<string, int> */
    public static function getUnitDefinitions(): array
    {
        return [];
    }

    /** @return array<array{0: string, 1: string, 2: int|float, 3?: int|float}> */
    public static function getConversionDefinitions(): array
    {
        return [];
    }
}

class Badness extends Quantity
{
    /** @return array<string, int> */
    public static function getUnitDefinitions(): array
    {
        return [
            'foo' => 0,
            'bar' => 0,
        ];
    }

    /** @return array<array{0: string, 1: string, 2: int|float, 3?: int|float}> */
    public static function getConversionDefinitions(): array
    {
        return [
            ['foo', 'bar', 10],
        ];
    }

    /** @return array<int|string, string> */
    public static function getPartUnits(): array
    {
        return ['foo', 'invalid'];
    }
}

/**
 * Test class with an empty symbol in getPartUnits().
 */
class EmptySymbol extends Quantity
{
    /** @return array<string, int> */
    public static function getUnitDefinitions(): array
    {
        return [
            'foo' => 0,
        ];
    }

    /** @return array<array{0: string, 1: string, 2: int|float, 3?: int|float}> */
    public static function getConversionDefinitions(): array
    {
        return [];
    }

    /** @return array<int|string, string> */
    public static function getPartUnits(): array
    {
        return [
            'foo' => '',
        ];
    }
}
