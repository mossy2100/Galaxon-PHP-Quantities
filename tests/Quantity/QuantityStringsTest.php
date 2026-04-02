<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Quantity;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Quantities\Exceptions\DimensionMismatchException;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType\Angle;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\QuantityType\Temperature;
use Galaxon\Quantities\QuantityType\Time;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for parsing and formatting Quantity objects.
 */
#[CoversClass(Quantity::class)]
final class QuantityStringsTest extends TestCase
{
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

    // region Parse tests - dimension mismatch

    /**
     * Test that parsing a unit with the wrong dimension throws DimensionMismatchException.
     */
    public function testParseWrongDimensionThrowsException(): void
    {
        $this->expectException(DimensionMismatchException::class);

        Length::parse('123 kg');
    }

    /**
     * Test that parsing a compound unit with the wrong dimension throws DimensionMismatchException.
     */
    public function testParseCompoundUnitWrongDimensionThrowsException(): void
    {
        $this->expectException(DimensionMismatchException::class);

        Length::parse('10 kg*m/s2');
    }

    /**
     * Test that Quantity::parse() accepts any valid unit without dimension restriction.
     */
    public function testQuantityParseAcceptsAnyDimension(): void
    {
        $mass = Quantity::parse('123 kg');
        $length = Quantity::parse('456 m');

        $this->assertInstanceOf(Mass::class, $mass);
        $this->assertInstanceOf(Length::class, $length);
    }

    /**
     * Test that subclass parse() works when the dimension matches.
     */
    public function testParseCorrectDimensionSucceeds(): void
    {
        $length = Length::parse('100 km');
        $mass = Mass::parse('50 kg');

        $this->assertInstanceOf(Length::class, $length);
        $this->assertSame(100.0, $length->value);

        $this->assertInstanceOf(Mass::class, $mass);
        $this->assertSame(50.0, $mass->value);
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

        // Default specifier is 'g', which uses scientific notation for large values.
        $this->assertSame('1.23457×10⁶ m', $length->format());

        // Use 'f' specifier for full fixed-point value.
        $this->assertSame('1234567.89 m', $length->format('f'));
    }

    // endregion

    // region Format tests - precision control

    /**
     * Test formatting with fixed precision.
     */
    public function testFormatFixedPrecision(): void
    {
        $length = new Length(5.0, 'm');

        $this->assertSame('5.00 m', $length->format('f', 2));
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
     * Test formatting with null precision trims trailing zeros.
     */
    public function testFormatNullPrecisionTrimsZeros(): void
    {
        $length = new Length(5.0, 'm');

        $this->assertSame('5 m', $length->format());
    }

    // endregion

    // region Format tests - scientific notation

    /**
     * Test formatting with scientific notation.
     */
    public function testFormatScientificNotation(): void
    {
        $length = new Length(1500.0, 'm');
        $result = $length->format('e', 2, ascii: true);

        $this->assertSame('1.50e+3 m', $result);
    }

    /**
     * Test formatting with uppercase scientific notation.
     */
    public function testFormatScientificNotationUppercase(): void
    {
        $length = new Length(1500.0, 'm');
        $result = $length->format('E', 2, ascii: true);

        $this->assertSame('1.50E+3 m', $result);
    }

    /**
     * Test scientific notation uses ×10 with superscript exponent by default (ascii=false).
     */
    public function testFormatScientificNotationUnicode(): void
    {
        $length = new Length(1500.0, 'm');
        $result = $length->format('e', 2);

        $this->assertSame('1.50×10³ m', $result);
    }

    /**
     * Test uppercase scientific notation also uses ×10 with superscript when ascii=false.
     */
    public function testFormatScientificNotationUppercaseUnicode(): void
    {
        $length = new Length(1500.0, 'm');
        $result = $length->format('E', 2);

        $this->assertSame('1.50×10³ m', $result);
    }

    /**
     * Test scientific notation with negative exponent uses ×10 with superscript by default.
     */
    public function testFormatScientificNotationUnicodeNegativeExponent(): void
    {
        $length = new Length(0.0025, 'm');
        $result = $length->format('e', 2);

        $this->assertSame('2.50×10⁻³ m', $result);
    }

    /**
     * Test scientific notation with null precision trims zeros and uses ×10.
     */
    public function testFormatScientificNotationUnicodeTrimZeros(): void
    {
        $length = new Length(3000.0, 'm');
        $result = $length->format('e');

        $this->assertSame('3×10³ m', $result);
    }

    /**
     * Test scientific notation with explicit precision preserves trailing zeros.
     */
    public function testFormatScientificNotationPreservesZerosWithPrecision(): void
    {
        $length = new Length(3000.0, 'm');
        $result = $length->format('e', 4);

        $this->assertSame('3.0000×10³ m', $result);
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

        $this->assertSame('45 °', $angle->format(includeSpace: true));
    }

    /**
     * Test formatting without space.
     */
    public function testFormatWithoutSpace(): void
    {
        $length = new Length(100, 'm');

        $this->assertSame('100m', $length->format(includeSpace: false));
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
        $formatted = $parsed->format('f', 2, includeSpace: true);

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
        $this->expectExceptionMessage("Invalid format specifier: 'x'.");

        $length = new Length(10, 'm');
        $length->format('x');
    }

    /**
     * Test format() with negative precision throws exception.
     */
    public function testFormatNegativePrecisionThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid precision: -1.');

        $length = new Length(10, 'm');
        $length->format('f', -1);
    }

    /**
     * Test format() with precision > 17 throws exception.
     */
    public function testFormatPrecisionTooHighThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid precision: 18.');

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

    // region formatValue() tests

    /**
     * Test formatValue() with default parameters trims trailing zeros.
     */
    public function testFormatValueDefaultTrimsZeros(): void
    {
        $this->assertSame('5', Quantity::formatValue(5.0));
    }

    /**
     * Test formatValue() with explicit precision preserves trailing zeros.
     */
    public function testFormatValueExplicitPrecisionPreservesZeros(): void
    {
        $this->assertSame('5.00', Quantity::formatValue(5.0, 'f', 2));
    }

    /**
     * Test formatValue() with explicit precision and trimZeros true forces trimming.
     */
    public function testFormatValueExplicitPrecisionWithTrimZerosTrue(): void
    {
        $this->assertSame('5', Quantity::formatValue(5.0, 'f', 2, true));
    }

    /**
     * Test formatValue() with null precision and trimZeros false preserves zeros.
     */
    public function testFormatValueNullPrecisionWithTrimZerosFalse(): void
    {
        $this->assertSame('5.000000', Quantity::formatValue(5.0, 'f', null, false));
    }

    /**
     * Test formatValue() normalizes negative zero.
     */
    public function testFormatValueNormalizesNegativeZero(): void
    {
        $this->assertSame('0', Quantity::formatValue(-0.0));
    }

    /**
     * Test formatValue() with scientific notation and explicit precision preserves zeros.
     */
    public function testFormatValueScientificPrecisionPreservesZeros(): void
    {
        $this->assertSame('3.0000e+3', Quantity::formatValue(3000.0, 'e', 4, ascii: true));
    }

    /**
     * Test formatValue() with scientific notation and null precision trims zeros.
     */
    public function testFormatValueScientificNullPrecisionTrimsZeros(): void
    {
        $this->assertSame('3e+3', Quantity::formatValue(3000.0, 'e', ascii: true));
    }

    /**
     * Test formatValue() with scientific notation and ASCII output.
     */
    public function testFormatValueScientificAscii(): void
    {
        $this->assertSame('1.50e+3', Quantity::formatValue(1500.0, 'e', 2, ascii: true));
    }

    /**
     * Test formatValue() with invalid specifier throws exception.
     */
    public function testFormatValueInvalidSpecifierThrowsException(): void
    {
        $this->expectException(DomainException::class);

        Quantity::formatValue(1.0, 'x');
    }

    /**
     * Test formatValue() with invalid precision throws exception.
     */
    public function testFormatValueInvalidPrecisionThrowsException(): void
    {
        $this->expectException(DomainException::class);

        Quantity::formatValue(1.0, 'f', -1);
    }

    // endregion

    // region format() trimZeros tests

    /**
     * Test format() with default trimZeros trims when precision is null.
     */
    public function testFormatAutoTrimWithNullPrecision(): void
    {
        $length = new Length(5.0, 'm');

        $this->assertSame('5 m', $length->format());
    }

    /**
     * Test format() with default trimZeros preserves digits when precision is explicit.
     */
    public function testFormatAutoPreservesWithExplicitPrecision(): void
    {
        $length = new Length(5.0, 'm');

        $this->assertSame('5.00 m', $length->format('f', 2));
    }

    /**
     * Test format() with trimZeros true forces trimming despite explicit precision.
     */
    public function testFormatTrimZerosTrueOverridesPrecision(): void
    {
        $length = new Length(5.0, 'm');

        $this->assertSame('5 m', $length->format('f', 2, trimZeros: true));
    }

    /**
     * Test format() with trimZeros false preserves zeros despite null precision.
     */
    public function testFormatTrimZerosFalseOverridesNullPrecision(): void
    {
        $length = new Length(5.0, 'm');

        $this->assertSame('5.000000 m', $length->format('f', null, trimZeros: false));
    }

    /**
     * Test format() with g specifier and explicit precision.
     */
    public function testFormatGSpecifierExplicitPrecision(): void
    {
        $length = new Length(1234.56, 'm');

        // 'g' with precision 6 = 6 significant digits.
        $this->assertSame('1234.56 m', $length->format('g', 6));
    }

    /**
     * Test format() with h specifier (non-locale-aware shortest).
     */
    public function testFormatHSpecifier(): void
    {
        $length = new Length(1234.5, 'm');

        $this->assertSame('1234.5 m', $length->format('h'));
    }

    /**
     * Test that trimming does not strip significant zeros from integer values.
     */
    public function testFormatTrimDoesNotStripIntegerZeros(): void
    {
        // g specifier on 1500.0 produces "1500" (no decimal point).
        // Trimming must not strip the trailing zeros.
        $length = new Length(1500.0, 'm');

        $this->assertSame('1500 m', $length->format('g'));
    }

    /**
     * Test that trimming strips decimal trailing zeros but not integer zeros.
     */
    public function testFormatValueTrimStripsDecimalButNotIntegerZeros(): void
    {
        // f specifier with precision 2 on 1500.0 produces "1500.00".
        // With trimming, the ".00" is removed but "1500" is preserved.
        $this->assertSame('1500', Quantity::formatValue(1500.0, 'f', 2, true));
    }

    // endregion
}
