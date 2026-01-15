<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Quantity;

use DomainException;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType\Area;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\QuantityType\Time;
use Galaxon\Quantities\QuantityType\Volume;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Quantity creation via constructor and create() methods.
 */
#[CoversClass(Quantity::class)]
#[CoversClass(Length::class)]
#[CoversClass(Mass::class)]
#[CoversClass(Time::class)]
#[CoversClass(Area::class)]
#[CoversClass(Volume::class)]
final class QuantityCreateTest extends TestCase
{
    // region Constructor tests - base Quantity class

    /**
     * Test creating a Quantity directly with a valid unit.
     */
    public function testQuantityConstructorWithValidUnit(): void
    {
        $qty = new Length(100, 'm');

        $this->assertSame(100.0, $qty->value);
        $this->assertSame('m', (string)$qty->derivedUnit);
    }

    /**
     * Test creating a Quantity with zero value.
     */
    public function testQuantityConstructorWithZero(): void
    {
        $qty = new Length(0, 'm');

        $this->assertSame(0.0, $qty->value);
    }

    /**
     * Test creating a Quantity with negative value.
     */
    public function testQuantityConstructorWithNegativeValue(): void
    {
        $qty = new Mass(-50, 'kg');

        $this->assertSame(-50.0, $qty->value);
    }

    /**
     * Test constructor throws DomainException for infinity.
     */
    public function testQuantityConstructorThrowsForInfinity(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('cannot be ±INF or NAN');

        new Quantity(INF, 'm');
    }

    /**
     * Test constructor throws DomainException for negative infinity.
     */
    public function testQuantityConstructorThrowsForNegativeInfinity(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('cannot be ±INF or NAN');

        new Quantity(-INF, 'm');
    }

    /**
     * Test constructor throws DomainException for NAN.
     */
    public function testQuantityConstructorThrowsForNan(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('cannot be ±INF or NAN');

        new Quantity(NAN, 'm');
    }

    /**
     * Test constructor throws DomainException for invalid unit.
     */
    public function testQuantityConstructorThrowsForInvalidUnit(): void
    {
        $this->expectException(DomainException::class);

        new Quantity(100, 'invalid');
    }

    // endregion

    // region Constructor tests - derived classes

    /**
     * Test creating a Length with a valid length unit.
     */
    public function testLengthConstructorWithValidUnit(): void
    {
        $length = new Length(100, 'm');

        $this->assertInstanceOf(Length::class, $length);
        $this->assertSame(100.0, $length->value);
        $this->assertSame('m', (string)$length->derivedUnit);
    }

    /**
     * Test creating a Length with a prefixed unit.
     */
    public function testLengthConstructorWithPrefixedUnit(): void
    {
        $length = new Length(5, 'km');

        $this->assertInstanceOf(Length::class, $length);
        $this->assertSame(5.0, $length->value);
        $this->assertSame('km', (string)$length->derivedUnit);
    }

    /**
     * Test creating a Mass with a valid mass unit.
     */
    public function testMassConstructorWithValidUnit(): void
    {
        $mass = new Mass(75, 'kg');

        $this->assertInstanceOf(Mass::class, $mass);
        $this->assertSame(75.0, $mass->value);
        $this->assertSame('kg', (string)$mass->derivedUnit);
    }

    /**
     * Test creating a Time with a valid time unit.
     */
    public function testTimeConstructorWithValidUnit(): void
    {
        $time = new Time(60, 's');

        $this->assertInstanceOf(Time::class, $time);
        $this->assertSame(60.0, $time->value);
        $this->assertSame('s', (string)$time->derivedUnit);
    }

    /**
     * Test creating an Area with a valid area unit.
     */
    public function testAreaConstructorWithValidUnit(): void
    {
        $area = new Area(100, 'm2');

        $this->assertInstanceOf(Area::class, $area);
        $this->assertSame(100.0, $area->value);
        $this->assertSame('m2', $area->derivedUnit->format(true));
    }

    /**
     * Test creating a Volume with a valid volume unit.
     */
    public function testVolumeConstructorWithValidUnit(): void
    {
        $volume = new Volume(1000, 'L');

        $this->assertInstanceOf(Volume::class, $volume);
        $this->assertSame(1000.0, $volume->value);
    }

    // endregion

    // region Constructor tests - dimension mismatch exceptions

    /**
     * Test Length constructor throws LogicException for mass unit.
     */
    public function testLengthConstructorThrowsForMassUnit(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('expected dimension');

        new Length(100, 'kg');
    }

    /**
     * Test Length constructor throws LogicException for time unit.
     */
    public function testLengthConstructorThrowsForTimeUnit(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('expected dimension');

        new Length(60, 's');
    }

    /**
     * Test Mass constructor throws LogicException for length unit.
     */
    public function testMassConstructorThrowsForLengthUnit(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('expected dimension');

        new Mass(100, 'm');
    }

    /**
     * Test Time constructor throws LogicException for length unit.
     */
    public function testTimeConstructorThrowsForLengthUnit(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('expected dimension');

        new Time(100, 'km');
    }

    /**
     * Test Area constructor throws LogicException for length unit.
     */
    public function testAreaConstructorThrowsForLengthUnit(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('expected dimension');

        new Area(100, 'm');
    }

    /**
     * Test Volume constructor throws LogicException for area unit.
     */
    public function testVolumeConstructorThrowsForAreaUnit(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('expected dimension');

        new Volume(100, 'm2');
    }

    // endregion

    // region create() tests - base Quantity class

    /**
     * Test Quantity::create() returns Length for length unit.
     */
    public function testQuantityCreateReturnsLengthForLengthUnit(): void
    {
        $qty = Quantity::create(100, 'm');

        $this->assertInstanceOf(Length::class, $qty);
        $this->assertSame(100.0, $qty->value);
    }

    /**
     * Test Quantity::create() returns Mass for mass unit.
     */
    public function testQuantityCreateReturnsMassForMassUnit(): void
    {
        $qty = Quantity::create(75, 'kg');

        $this->assertInstanceOf(Mass::class, $qty);
        $this->assertSame(75.0, $qty->value);
    }

    /**
     * Test Quantity::create() returns Time for time unit.
     */
    public function testQuantityCreateReturnsTimeForTimeUnit(): void
    {
        $qty = Quantity::create(60, 's');

        $this->assertInstanceOf(Time::class, $qty);
        $this->assertSame(60.0, $qty->value);
    }

    /**
     * Test Quantity::create() returns Area for area unit.
     */
    public function testQuantityCreateReturnsAreaForAreaUnit(): void
    {
        $qty = Quantity::create(100, 'ha');

        $this->assertInstanceOf(Area::class, $qty);
        $this->assertSame(100.0, $qty->value);
    }

    /**
     * Test Quantity::create() returns Volume for volume unit.
     */
    public function testQuantityCreateReturnsVolumeForVolumeUnit(): void
    {
        $qty = Quantity::create(1000, 'L');

        $this->assertInstanceOf(Volume::class, $qty);
        $this->assertSame(1000.0, $qty->value);
    }

    /**
     * Test Quantity::create() returns base Quantity for unregistered dimension.
     */
    public function testQuantityCreateReturnsBaseQuantityForUnregisteredDimension(): void
    {
        // Electric current (A) has no registered class
        $qty = Quantity::create(10, 'A');

        $this->assertInstanceOf(Quantity::class, $qty);
        // Should not be a subclass
        $this->assertSame(Quantity::class, $qty::class);
    }

    // endregion

    // region create() tests - derived classes

    /**
     * Test Length::create() returns Length instance.
     */
    public function testLengthCreateReturnsLengthInstance(): void
    {
        $length = Length::create(100, 'm');

        $this->assertInstanceOf(Length::class, $length);
        $this->assertSame(100.0, $length->value);
    }

    /**
     * Test Length::create() works with prefixed units.
     */
    public function testLengthCreateWithPrefixedUnit(): void
    {
        $length = Length::create(5, 'km');

        $this->assertInstanceOf(Length::class, $length);
        $this->assertSame(5.0, $length->value);
        $this->assertSame('km', (string)$length->derivedUnit);
    }

    /**
     * Test Mass::create() returns Mass instance.
     */
    public function testMassCreateReturnsMassInstance(): void
    {
        $mass = Mass::create(75, 'kg');

        $this->assertInstanceOf(Mass::class, $mass);
        $this->assertSame(75.0, $mass->value);
    }

    /**
     * Test Time::create() returns Time instance.
     */
    public function testTimeCreateReturnsTimeInstance(): void
    {
        $time = Time::create(3600, 's');

        $this->assertInstanceOf(Time::class, $time);
        $this->assertSame(3600.0, $time->value);
    }

    // endregion

    // region Edge cases

    /**
     * Test negative zero is normalized to positive zero.
     */
    public function testNegativeZeroIsNormalized(): void
    {
        $qty = new Length(-0.0, 'm');

        $this->assertSame(0.0, $qty->value);
        $this->assertStringNotContainsString('-', (string)$qty->value);
    }

    /**
     * Test very small values are preserved.
     */
    public function testVerySmallValuesArePreserved(): void
    {
        $qty = new Length(1e-300, 'm');

        $this->assertSame(1e-300, $qty->value);
    }

    /**
     * Test very large values are preserved.
     */
    public function testVeryLargeValuesArePreserved(): void
    {
        $qty = new Length(1e300, 'm');

        $this->assertSame(1e300, $qty->value);
    }

    /**
     * Test calling new Quantity() directly with a registered dimension throws LogicException.
     */
    public function testQuantityConstructorThrowsForRegisteredDimension(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('call `new');

        new Quantity(100, 'm');
    }

    /**
     * Test creating a dimensionless Quantity with null unit.
     */
    public function testQuantityConstructorWithNullUnit(): void
    {
        $qty = new Quantity(42.0, null);

        $this->assertSame(42.0, $qty->value);
        $this->assertSame('', (string)$qty->derivedUnit);
    }

    // endregion

    // region parse() tests - basic parsing

    /**
     * Test parsing a simple value with unit separated by space.
     */
    public function testParseSimpleValueWithSpace(): void
    {
        $qty = Quantity::parse('123.45 km');

        $this->assertInstanceOf(Length::class, $qty);
        $this->assertSame(123.45, $qty->value);
        $this->assertSame('km', (string)$qty->derivedUnit);
    }

    /**
     * Test parsing a value with unit without space.
     */
    public function testParseValueWithoutSpace(): void
    {
        $qty = Quantity::parse('90m');

        $this->assertInstanceOf(Length::class, $qty);
        $this->assertSame(90.0, $qty->value);
        $this->assertSame('m', (string)$qty->derivedUnit);
    }

    /**
     * Test parsing with scientific notation.
     */
    public function testParseScientificNotation(): void
    {
        $qty = Quantity::parse('1.5e3 ms');

        $this->assertInstanceOf(Time::class, $qty);
        $this->assertSame(1500.0, $qty->value);
        $this->assertSame('ms', (string)$qty->derivedUnit);
    }

    /**
     * Test parsing with uppercase scientific notation.
     */
    public function testParseScientificNotationUppercase(): void
    {
        $qty = Quantity::parse('2.5E-2 kg');

        $this->assertInstanceOf(Mass::class, $qty);
        $this->assertSame(0.025, $qty->value);
        $this->assertSame('kg', (string)$qty->derivedUnit);
    }

    /**
     * Test parsing negative value.
     */
    public function testParseNegativeValue(): void
    {
        $qty = Quantity::parse('-50 m');

        $this->assertInstanceOf(Length::class, $qty);
        $this->assertSame(-50.0, $qty->value);
    }

    /**
     * Test parsing positive value with explicit plus sign throws exception.
     *
     * Note: The parser does not accept explicit plus signs.
     */
    public function testParsePositiveValueWithPlusSignThrows(): void
    {
        $this->expectException(DomainException::class);

        Quantity::parse('+100 m');
    }

    /**
     * Test parsing zero.
     */
    public function testParseZero(): void
    {
        $qty = Quantity::parse('0 m');

        $this->assertInstanceOf(Length::class, $qty);
        $this->assertSame(0.0, $qty->value);
    }

    /**
     * Test parsing dimensionless quantity (number only).
     */
    public function testParseDimensionless(): void
    {
        $qty = Quantity::parse('3.14159');

        $this->assertInstanceOf(Quantity::class, $qty);
        $this->assertSame(3.14159, $qty->value);
        $this->assertSame('', (string)$qty->derivedUnit);
    }

    /**
     * Test parsing with multiple spaces between value and unit.
     */
    public function testParseWithMultipleSpaces(): void
    {
        $qty = Quantity::parse('100   m');

        $this->assertInstanceOf(Length::class, $qty);
        $this->assertSame(100.0, $qty->value);
        $this->assertSame('m', (string)$qty->derivedUnit);
    }

    /**
     * Test parsing with leading and trailing whitespace.
     */
    public function testParseWithLeadingAndTrailingWhitespace(): void
    {
        $qty = Quantity::parse('  50 kg  ');

        $this->assertInstanceOf(Mass::class, $qty);
        $this->assertSame(50.0, $qty->value);
    }

    // endregion

    // region parse() tests - complex units

    /**
     * Test parsing compound unit with asterisk multiplication.
     */
    public function testParseCompoundUnitWithAsterisk(): void
    {
        $qty = Quantity::parse('9.8 kg*m/s2');

        $this->assertSame(9.8, $qty->value);
        // Units are sorted by dimension code order.
        $this->assertSame('kg·m·s⁻²', (string)$qty->derivedUnit);
    }

    /**
     * Test parsing compound unit with middle dot (·) multiplication.
     */
    public function testParseCompoundUnitWithMiddleDot(): void
    {
        $qty = Quantity::parse('9.8 kg·m/s2');

        $this->assertSame(9.8, $qty->value);
        // Units are sorted by dimension code order.
        $this->assertSame('kg·m·s⁻²', (string)$qty->derivedUnit);
    }

    /**
     * Test parsing compound unit with period (.) multiplication.
     */
    public function testParseCompoundUnitWithPeriod(): void
    {
        $qty = Quantity::parse('9.8 kg.m/s2');

        $this->assertSame(9.8, $qty->value);
        // Units are sorted by dimension code order.
        $this->assertSame('kg·m·s⁻²', (string)$qty->derivedUnit);
    }

    /**
     * Test parsing compound unit with superscript exponents.
     */
    public function testParseCompoundUnitWithSuperscriptExponents(): void
    {
        $qty = Quantity::parse('9.8 kg·m·s⁻²');

        $this->assertSame(9.8, $qty->value);
        // Units are sorted by dimension code order.
        $this->assertSame('kg·m·s⁻²', (string)$qty->derivedUnit);
    }

    /**
     * Test parsing compound unit with mixed notation.
     */
    public function testParseCompoundUnitMixedNotation(): void
    {
        $qty = Quantity::parse('5 kg*m²/s²');

        $this->assertSame(5.0, $qty->value);
        // Both m² and s² should be parsed correctly
        $this->assertStringContainsString('kg', (string)$qty->derivedUnit);
        $this->assertStringContainsString('m', (string)$qty->derivedUnit);
        $this->assertStringContainsString('s', (string)$qty->derivedUnit);
    }

    /**
     * Test parsing velocity unit (m/s).
     */
    public function testParseVelocityUnit(): void
    {
        $qty = Quantity::parse('299792458 m/s');

        $this->assertSame(299792458.0, $qty->value);
        $this->assertSame('m·s⁻¹', (string)$qty->derivedUnit);
    }

    /**
     * Test parsing acceleration unit (m/s2).
     */
    public function testParseAccelerationUnit(): void
    {
        $qty = Quantity::parse('9.80665 m/s2');

        $this->assertSame(9.80665, $qty->value);
        $this->assertSame('m·s⁻²', (string)$qty->derivedUnit);
    }

    /**
     * Test parsing area unit with superscript.
     */
    public function testParseAreaUnitWithSuperscript(): void
    {
        $qty = Quantity::parse('100 m²');

        $this->assertInstanceOf(Area::class, $qty);
        $this->assertSame(100.0, $qty->value);
    }

    /**
     * Test parsing area unit with ASCII exponent.
     */
    public function testParseAreaUnitWithAsciiExponent(): void
    {
        $qty = Quantity::parse('100 m2');

        $this->assertInstanceOf(Area::class, $qty);
        $this->assertSame(100.0, $qty->value);
    }

    /**
     * Test parsing volume unit with superscript.
     */
    public function testParseVolumeUnitWithSuperscript(): void
    {
        $qty = Quantity::parse('5 m³');

        $this->assertInstanceOf(Volume::class, $qty);
        $this->assertSame(5.0, $qty->value);
    }

    /**
     * Test parsing volume unit with ASCII exponent.
     */
    public function testParseVolumeUnitWithAsciiExponent(): void
    {
        $qty = Quantity::parse('5 m3');

        $this->assertInstanceOf(Volume::class, $qty);
        $this->assertSame(5.0, $qty->value);
    }

    /**
     * Test parsing unit with negative superscript exponent.
     */
    public function testParseUnitWithNegativeSuperscriptExponent(): void
    {
        $qty = Quantity::parse('1000 s⁻¹');

        $this->assertSame(1000.0, $qty->value);
        $this->assertSame('s⁻¹', (string)$qty->derivedUnit);
    }

    /**
     * Test parsing prefixed compound unit.
     */
    public function testParsePrefixedCompoundUnit(): void
    {
        $qty = Quantity::parse('50 km/h');

        $this->assertSame(50.0, $qty->value);
        $this->assertStringContainsString('km', (string)$qty->derivedUnit);
    }

    // endregion

    // region parse() tests - exceptions

    /**
     * Test parse throws DomainException for empty string.
     */
    public function testParseThrowsForEmptyString(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('does not represent a valid quantity');

        Quantity::parse('');
    }

    /**
     * Test parse throws DomainException for whitespace-only string.
     */
    public function testParseThrowsForWhitespaceOnly(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('does not represent a valid quantity');

        Quantity::parse('   ');
    }

    /**
     * Test parse throws DomainException for invalid format (unit only).
     */
    public function testParseThrowsForUnitOnly(): void
    {
        $this->expectException(DomainException::class);

        Quantity::parse('kg');
    }

    /**
     * Test parse throws DomainException for invalid unit.
     */
    public function testParseThrowsForInvalidUnit(): void
    {
        $this->expectException(DomainException::class);

        Quantity::parse('100 xyz');
    }

    /**
     * Test parse throws DomainException for gibberish.
     */
    public function testParseThrowsForGibberish(): void
    {
        $this->expectException(DomainException::class);

        Quantity::parse('not a quantity');
    }

    // endregion

    // region parse() tests - called from subclass

    /**
     * Test Length::parse() returns Length instance.
     */
    public function testLengthParseReturnsLength(): void
    {
        $length = Length::parse('100 m');

        $this->assertInstanceOf(Length::class, $length);
        $this->assertSame(100.0, $length->value);
    }

    /**
     * Test Mass::parse() returns Mass instance.
     */
    public function testMassParseReturnsMass(): void
    {
        $mass = Mass::parse('75 kg');

        $this->assertInstanceOf(Mass::class, $mass);
        $this->assertSame(75.0, $mass->value);
    }

    /**
     * Test Time::parse() returns Time instance.
     */
    public function testTimeParseReturnsTime(): void
    {
        $time = Time::parse('3600 s');

        $this->assertInstanceOf(Time::class, $time);
        $this->assertSame(3600.0, $time->value);
    }

    // endregion

    // region create() tests - additional coverage

    /**
     * Test create() throws DomainException for infinity.
     */
    public function testCreateThrowsForInfinity(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('cannot be ±INF or NAN');

        Quantity::create(INF, 'm');
    }

    /**
     * Test create() throws DomainException for NAN.
     */
    public function testCreateThrowsForNan(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('cannot be ±INF or NAN');

        Quantity::create(NAN, 'm');
    }

    /**
     * Test create() with null unit returns dimensionless Quantity.
     */
    public function testCreateWithNullUnit(): void
    {
        $qty = Quantity::create(42.0, null);

        $this->assertInstanceOf(Quantity::class, $qty);
        $this->assertSame(42.0, $qty->value);
        $this->assertSame('', (string)$qty->derivedUnit);
    }

    // endregion
}
