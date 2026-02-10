<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Quantity;

use DomainException;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType\Angle;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\QuantityType\Temperature;
use Galaxon\Quantities\QuantityType\Time;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for creating Quantity objects.
 */
#[CoversClass(Quantity::class)]
final class QuantityCreateTest extends TestCase
{
    // region Direct constructor tests

    /**
     * Test creating a Length with the constructor.
     */
    public function testCreateLengthWithConstructor(): void
    {
        $length = new Length(5, 'm');

        $this->assertInstanceOf(Length::class, $length);
        $this->assertSame(5.0, $length->value);
        $this->assertSame('m', $length->derivedUnit->asciiSymbol);
    }

    /**
     * Test creating a Mass with the constructor.
     */
    public function testCreateMassWithConstructor(): void
    {
        $mass = new Mass(10, 'kg');

        $this->assertInstanceOf(Mass::class, $mass);
        $this->assertSame(10.0, $mass->value);
        $this->assertSame('kg', $mass->derivedUnit->asciiSymbol);
    }

    /**
     * Test creating a Time with the constructor.
     */
    public function testCreateTimeWithConstructor(): void
    {
        $time = new Time(60, 's');

        $this->assertInstanceOf(Time::class, $time);
        $this->assertSame(60.0, $time->value);
        $this->assertSame('s', $time->derivedUnit->asciiSymbol);
    }

    /**
     * Test creating an Angle with the constructor.
     */
    public function testCreateAngleWithConstructor(): void
    {
        $angle = new Angle(90, 'deg');

        $this->assertInstanceOf(Angle::class, $angle);
        $this->assertSame(90.0, $angle->value);
        $this->assertSame('deg', $angle->derivedUnit->asciiSymbol);
    }

    /**
     * Test creating a Temperature with the constructor.
     */
    public function testCreateTemperatureWithConstructor(): void
    {
        $temp = new Temperature(25, 'degC');

        $this->assertInstanceOf(Temperature::class, $temp);
        $this->assertSame(25.0, $temp->value);
        $this->assertSame('degC', $temp->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Static create() method tests

    /**
     * Test creating a Length with the static create() method.
     */
    public function testCreateLengthWithStaticMethod(): void
    {
        $length = Length::create(100, 'km');

        $this->assertInstanceOf(Length::class, $length);
        $this->assertSame(100.0, $length->value);
        $this->assertSame('km', $length->derivedUnit->asciiSymbol);
    }

    /**
     * Test that Quantity::create() returns the correct subclass.
     */
    public function testGenericCreateReturnsCorrectSubclass(): void
    {
        $length = Quantity::create(5, 'm');
        $mass = Quantity::create(10, 'kg');
        $time = Quantity::create(60, 's');

        $this->assertInstanceOf(Length::class, $length);
        $this->assertInstanceOf(Mass::class, $mass);
        $this->assertInstanceOf(Time::class, $time);
    }

    // endregion

    // region Prefixed unit tests

    /**
     * Test creating with metric prefixes.
     */
    public function testCreateWithMetricPrefixes(): void
    {
        $km = new Length(1, 'km');
        $mm = new Length(1000, 'mm');
        $mg = new Mass(500, 'mg');
        $ns = new Time(100, 'ns');

        $this->assertSame(1.0, $km->value);
        $this->assertSame('km', $km->derivedUnit->asciiSymbol);

        $this->assertSame(1000.0, $mm->value);
        $this->assertSame('mm', $mm->derivedUnit->asciiSymbol);

        $this->assertSame(500.0, $mg->value);
        $this->assertSame('mg', $mg->derivedUnit->asciiSymbol);

        $this->assertSame(100.0, $ns->value);
        $this->assertSame('ns', $ns->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Negative and zero value tests

    /**
     * Test creating with zero value.
     */
    public function testCreateWithZeroValue(): void
    {
        $length = new Length(0, 'm');

        $this->assertSame(0.0, $length->value);
    }

    /**
     * Test creating with negative value.
     */
    public function testCreateWithNegativeValue(): void
    {
        $temp = new Temperature(-40, 'degC');

        $this->assertSame(-40.0, $temp->value);
    }

    /**
     * Test that -0.0 is normalized to 0.0.
     */
    public function testNegativeZeroNormalized(): void
    {
        $length = new Length(-0.0, 'm');

        $this->assertSame(0.0, $length->value);
        $this->assertStringNotContainsString('-', (string)$length->value);
    }

    // endregion

    // region Error handling tests

    /**
     * Test that INF throws an exception.
     */
    public function testInfinityThrowsException(): void
    {
        $this->expectException(DomainException::class);

        new Length(INF, 'm');
    }

    /**
     * Test that -INF throws an exception.
     */
    public function testNegativeInfinityThrowsException(): void
    {
        $this->expectException(DomainException::class);

        new Length(-INF, 'm');
    }

    /**
     * Test that NAN throws an exception.
     */
    public function testNanThrowsException(): void
    {
        $this->expectException(DomainException::class);

        new Length(NAN, 'm');
    }

    /**
     * Test that an unknown unit throws an exception.
     */
    public function testUnknownUnitThrowsException(): void
    {
        $this->expectException(DomainException::class);

        new Length(5, 'xyz');
    }

    /**
     * Test that Quantity::create() with INF throws an exception.
     */
    public function testCreateWithInfinityThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Value cannot be ±INF or NAN.');

        Quantity::create(INF, 'm');
    }

    /**
     * Test that Quantity::create() with -INF throws an exception.
     */
    public function testCreateWithNegativeInfinityThrowsException(): void
    {
        $this->expectException(DomainException::class);

        Quantity::create(-INF, 'm');
    }

    /**
     * Test that Quantity::create() with NAN throws an exception.
     */
    public function testCreateWithNanThrowsException(): void
    {
        $this->expectException(DomainException::class);

        Quantity::create(NAN, 'm');
    }

    /**
     * Test that calling the wrong constructor throws an exception.
     */
    public function testWrongConstructorThrowsException(): void
    {
        $this->expectException(LogicException::class);

        // Trying to create a Length by calling the Mass constructor
        new Mass(5, 'm');
    }

    // endregion

    // region Unicode symbol tests

    /**
     * Test creating with Unicode symbols.
     */
    public function testCreateWithUnicodeSymbols(): void
    {
        // Degree symbol for angles
        $angle = new Angle(45, '°');
        $this->assertSame(45.0, $angle->value);

        // Micro symbol for prefixes
        $length = new Length(100, 'μm');
        $this->assertSame(100.0, $length->value);
        $this->assertSame('μm', $length->derivedUnit->unicodeSymbol);
    }

    // endregion

    // region fromParts() tests

    /**
     * Test Quantity::fromParts() with mass units returns Mass instance.
     */
    public function testFromPartsReturnsMassInstance(): void
    {
        $qty = Quantity::fromParts([
            'kg' => 1,
            'g'  => 500,
        ], 'g');

        $this->assertInstanceOf(Mass::class, $qty);
        $this->assertSame(1500.0, $qty->value);
        $this->assertSame('g', $qty->derivedUnit->asciiSymbol);
    }

    /**
     * Test Quantity::fromParts() with time units returns Time instance.
     */
    public function testFromPartsReturnsTimeInstance(): void
    {
        $qty = Quantity::fromParts([
            'h'   => 1,
            'min' => 30,
            's'   => 45,
        ], 's');

        $this->assertInstanceOf(Time::class, $qty);
        // 1 h = 3600 s, 30 min = 1800 s, 45 s = 45 s
        // Total = 5445 s
        $this->assertSame(5445.0, $qty->value);
        $this->assertSame('s', $qty->derivedUnit->asciiSymbol);
    }

    /**
     * Test Quantity::fromParts() with length units returns Length instance.
     */
    public function testFromPartsReturnsLengthInstance(): void
    {
        $qty = Quantity::fromParts([
            'km' => 1,
            'm'  => 500,
        ], 'm');

        $this->assertInstanceOf(Length::class, $qty);
        $this->assertSame(1500.0, $qty->value);
        $this->assertSame('m', $qty->derivedUnit->asciiSymbol);
    }

    /**
     * Test Quantity::fromParts() with sign.
     */
    public function testFromPartsWithSign(): void
    {
        $qty = Quantity::fromParts([
            'm'    => 100,
            'cm'   => 50,
            'sign' => -1,
        ], 'm');

        $this->assertInstanceOf(Length::class, $qty);
        $this->assertSame(-100.5, $qty->value);
    }

    // endregion

    // region Base class method tests

    /**
     * Test getUnitDefinitions() returns empty array by default.
     */
    public function testGetUnitDefinitionsReturnsEmptyArray(): void
    {
        $result = Quantity::getUnitDefinitions();

        $this->assertSame([], $result);
    }

    /**
     * Test getConversionDefinitions() returns empty array by default.
     */
    public function testGetConversionDefinitionsReturnsEmptyArray(): void
    {
        $result = Quantity::getConversionDefinitions();

        $this->assertSame([], $result);
    }

    /**
     * Test getPartsConfig() returns default config.
     */
    public function testGetPartsConfigReturnsDefaultConfig(): void
    {
        $result = Quantity::getPartsConfig();

        $this->assertSame([
            'from' => null,
            'to'   => [],
        ], $result);
    }

    // endregion

    // region isSi() tests

    /**
     * Test isSi returns true for SI unit.
     */
    public function testIsSiReturnsTrueForSiUnit(): void
    {
        $length = new Length(1, 'm');

        $this->assertTrue($length->isSi());
    }

    /**
     * Test isSi returns true for prefixed SI unit.
     */
    public function testIsSiReturnsTrueForPrefixedSiUnit(): void
    {
        $length = new Length(1, 'km');

        $this->assertTrue($length->isSi());
    }

    /**
     * Test isSi returns true for compound SI unit.
     */
    public function testIsSiReturnsTrueForCompoundSiUnit(): void
    {
        $qty = Quantity::create(1, 'kg*m/s2');

        $this->assertTrue($qty->isSi());
    }

    /**
     * Test isSi returns false for non-SI unit.
     */
    public function testIsSiReturnsFalseForNonSiUnit(): void
    {
        $time = new Time(1, 'min');

        $this->assertFalse($time->isSi());
    }

    // endregion
}
