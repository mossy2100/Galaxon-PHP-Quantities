<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Quantity;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType\Angle;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\QuantityType\Temperature;
use Galaxon\Quantities\QuantityType\Time;
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for parsing and formatting Quantity objects.
 */
#[CoversClass(Quantity::class)]
final class QuantityStringsTest extends TestCase
{
    // region Setup

    public static function setUpBeforeClass(): void
    {
        // Load Imperial/US units for cross-system tests.
        UnitRegistry::loadSystem(System::Imperial);
        UnitRegistry::loadSystem(System::UsCustomary);
    }

    // endregion

    // region Parse tests - basic

    /**
     * Test parsing a simple length with space between value and unit.
     */
    public function testParseBasicLengthWithSpace(): void
    {
        $length = Length::parse('123.45 km');

        $this->assertInstanceOf(Length::class, $length);
        $this->assertSame(123.45, $length->value);
        $this->assertSame('km', $length->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing a length without space between value and unit.
     */
    public function testParseLengthWithoutSpace(): void
    {
        $length = Length::parse('100m');

        $this->assertInstanceOf(Length::class, $length);
        $this->assertSame(100.0, $length->value);
        $this->assertSame('m', $length->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing an angle with degree symbol.
     */
    public function testParseAngleWithDegreeSymbol(): void
    {
        $angle = Angle::parse('90deg');

        $this->assertInstanceOf(Angle::class, $angle);
        $this->assertSame(90.0, $angle->value);
        $this->assertSame('deg', $angle->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing mass in kilograms.
     */
    public function testParseMassKilograms(): void
    {
        $mass = Mass::parse('75 kg');

        $this->assertInstanceOf(Mass::class, $mass);
        $this->assertSame(75.0, $mass->value);
        $this->assertSame('kg', $mass->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing time with prefixed unit.
     */
    public function testParseTimeWithPrefix(): void
    {
        $time = Time::parse('500 ms');

        $this->assertInstanceOf(Time::class, $time);
        $this->assertSame(500.0, $time->value);
        $this->assertSame('ms', $time->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Parse tests - scientific notation

    /**
     * Test parsing with scientific notation (lowercase e).
     */
    public function testParseScientificNotationLowercase(): void
    {
        $length = Length::parse('1.5e3 m');

        $this->assertSame(1500.0, $length->value);
    }

    /**
     * Test parsing with scientific notation (uppercase E).
     */
    public function testParseScientificNotationUppercase(): void
    {
        $length = Length::parse('2.5E-3 km');

        $this->assertSame(0.0025, $length->value);
    }

    // endregion

    // region Parse tests - negative values

    /**
     * Test parsing negative values.
     */
    public function testParseNegativeValue(): void
    {
        $temp = Temperature::parse('-40 degC');

        $this->assertSame(-40.0, $temp->value);
        $this->assertSame('degC', $temp->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Parse tests - generic Quantity::parse()

    /**
     * Test that Quantity::parse() returns the correct subclass.
     */
    public function testGenericParseReturnsCorrectSubclass(): void
    {
        $length = Quantity::parse('10 m');
        $mass = Quantity::parse('5 kg');
        $time = Quantity::parse('30 s');

        $this->assertInstanceOf(Length::class, $length);
        $this->assertInstanceOf(Mass::class, $mass);
        $this->assertInstanceOf(Time::class, $time);
    }

    // endregion

    // region Parse tests - error handling

    /**
     * Test parsing empty string throws exception.
     */
    public function testParseEmptyStringThrowsException(): void
    {
        $this->expectException(FormatException::class);

        Length::parse('');
    }

    /**
     * Test parsing whitespace-only string throws exception.
     */
    public function testParseWhitespaceOnlyThrowsException(): void
    {
        $this->expectException(FormatException::class);

        Length::parse('   ');
    }

    /**
     * Test parsing invalid format throws exception.
     */
    public function testParseInvalidFormatThrowsException(): void
    {
        $this->expectException(FormatException::class);

        Length::parse('not a valid quantity');
    }

    // endregion

    // region Format tests - basic

    /**
     * Test default formatting (Unicode, fixed point, trim zeros).
     */
    public function testFormatDefault(): void
    {
        $length = new Length(5.0, 'm');

        $this->assertSame('5 m', $length->format());
    }

    /**
     * Test formatting with decimal places.
     */
    public function testFormatWithDecimalPlaces(): void
    {
        $length = new Length(1.5, 'm');

        $this->assertSame('1.5 m', $length->format());
    }

    /**
     * Test formatting large values.
     */
    public function testFormatLargeValue(): void
    {
        $length = new Length(1234567.89, 'm');

        $this->assertSame('1234567.89 m', $length->format());
    }

    // endregion

    // region Format tests - precision control

    /**
     * Test formatting with fixed precision.
     */
    public function testFormatFixedPrecision(): void
    {
        $length = new Length(5.0, 'm');

        $this->assertSame('5.00 m', $length->format('f', 2, false));
    }

    /**
     * Test formatting with zero precision.
     */
    public function testFormatZeroPrecision(): void
    {
        $length = new Length(5.7, 'm');

        $this->assertSame('6 m', $length->format('f', 0));
    }

    /**
     * Test formatting with trim zeros enabled.
     */
    public function testFormatWithTrimZeros(): void
    {
        $length = new Length(5.0, 'm');

        $this->assertSame('5 m', $length->format('f', 2, true));
    }

    // endregion

    // region Format tests - scientific notation

    /**
     * Test formatting with scientific notation.
     */
    public function testFormatScientificNotation(): void
    {
        $length = new Length(1500.0, 'm');
        $result = $length->format('e', 2, false);

        $this->assertSame('1.50e+3 m', $result);
    }

    /**
     * Test formatting with uppercase scientific notation.
     */
    public function testFormatScientificNotationUppercase(): void
    {
        $length = new Length(1500.0, 'm');
        $result = $length->format('E', 2, false);

        $this->assertSame('1.50E+3 m', $result);
    }

    // endregion

    // region Format tests - ASCII vs Unicode

    /**
     * Test ASCII formatting.
     */
    public function testFormatAscii(): void
    {
        $temp = new Temperature(25, 'degC');

        $this->assertSame('25 degC', $temp->format(ascii: true));
    }

    /**
     * Test Unicode formatting.
     */
    public function testFormatUnicode(): void
    {
        $temp = new Temperature(25, 'degC');

        $this->assertSame('25 °C', $temp->format());
    }

    /**
     * Test formatting angle with degree symbol.
     */
    public function testFormatAngleDegreeSymbol(): void
    {
        $angle = new Angle(45, 'deg');

        // Unicode format - no space before degree symbol
        $this->assertSame('45°', $angle->format());
    }

    // endregion

    // region Format tests - space control

    /**
     * Test formatting with explicit space.
     */
    public function testFormatWithExplicitSpace(): void
    {
        $angle = new Angle(45, 'deg');

        $this->assertSame('45 °', $angle->format('f', null, true, true));
    }

    /**
     * Test formatting without space.
     */
    public function testFormatWithoutSpace(): void
    {
        $length = new Length(100, 'm');

        $this->assertSame('100m', $length->format('f', null, true, false));
    }

    // endregion

    // region __toString tests

    /**
     * Test __toString uses default format.
     */
    public function testToString(): void
    {
        $length = new Length(5.5, 'm');

        $this->assertSame('5.5 m', (string)$length);
    }

    /**
     * Test __toString with prefixed unit.
     */
    public function testToStringWithPrefix(): void
    {
        $length = new Length(2.5, 'km');

        $this->assertSame('2.5 km', (string)$length);
    }

    /**
     * Test __toString with negative value.
     */
    public function testToStringNegative(): void
    {
        $temp = new Temperature(-10, 'degC');

        $this->assertSame('-10 °C', (string)$temp);
    }

    /**
     * Test __toString with zero value.
     */
    public function testToStringZero(): void
    {
        $length = new Length(0, 'm');

        $this->assertSame('0 m', (string)$length);
    }

    // endregion

    // region Round-trip tests

    /**
     * Test parse-format round trip.
     */
    public function testRoundTripParseFormat(): void
    {
        $original = '123.45 km';
        $parsed = Length::parse($original);
        $formatted = $parsed->format('f', 2, false, true, true);

        $this->assertSame($original, $formatted);
    }

    // endregion

    // region Format error handling tests

    /**
     * Test format() with invalid specifier throws exception.
     */
    public function testFormatInvalidSpecifierThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("The specifier must be 'e', 'E', 'f', 'F', 'g', or 'G'.");

        $length = new Length(10, 'm');
        $length->format('x');
    }

    /**
     * Test format() with negative precision throws exception.
     */
    public function testFormatNegativePrecisionThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('The precision must be null or an integer between 0 and 17.');

        $length = new Length(10, 'm');
        $length->format('f', -1);
    }

    /**
     * Test format() with precision > 17 throws exception.
     */
    public function testFormatPrecisionTooHighThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('The precision must be null or an integer between 0 and 17.');

        $length = new Length(10, 'm');
        $length->format('f', 18);
    }

    // endregion

    // region Dimensionless format tests

    /**
     * Test formatting a dimensionless quantity.
     */
    public function testFormatDimensionless(): void
    {
        $qty = Quantity::create(42.5, '');

        $this->assertSame('42.5', $qty->format());
    }

    /**
     * Test __toString on dimensionless quantity.
     */
    public function testToStringDimensionless(): void
    {
        $qty = Quantity::create(3.14159, '');

        $this->assertSame('3.14159', (string)$qty);
    }

    // endregion
}
