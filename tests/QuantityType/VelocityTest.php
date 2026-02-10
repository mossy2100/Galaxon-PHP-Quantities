<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\QuantityType;

use Galaxon\Core\Traits\FloatAssertions;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Time;
use Galaxon\Quantities\QuantityType\Velocity;
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;
use Galaxon\Quantities\Tests\Traits\ArrayShapeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Velocity quantity type.
 */
#[CoversClass(Velocity::class)]
final class VelocityTest extends TestCase
{
    use ArrayShapeTrait;
    use FloatAssertions;

    // region Setup

    public static function setUpBeforeClass(): void
    {
        // Load Imperial/US and Nautical units.
        UnitRegistry::loadSystem(System::Imperial);
        UnitRegistry::loadSystem(System::UsCustomary);
        UnitRegistry::loadSystem(System::Nautical);
    }

    // endregion

    // region Overridden methods

    /**
     * Test getUnitDefinitions() returns valid unit definitions.
     */
    public function testGetUnitDefinitionsReturnsValidArray(): void
    {
        $units = Velocity::getUnitDefinitions();

        $this->assertValidUnitDefinitionsShape($units);
    }

    /**
     * Test getConversionDefinitions() returns empty array.
     */
    public function testGetConversionDefinitionsReturnsEmptyArray(): void
    {
        $conversions = Velocity::getConversionDefinitions();

        $this->assertEmpty($conversions);
    }

    // endregion

    // region Metric conversion tests

    /**
     * Test converting metres per second to kilometres per hour.
     */
    public function testConvertMetresPerSecondToKilometresPerHour(): void
    {
        $vel = new Velocity(1, 'm/s');
        $kmh = $vel->to('km/h');

        // 1 m/s = 3.6 km/h
        $this->assertInstanceOf(Velocity::class, $kmh);
        $this->assertSame(3.6, $kmh->value);
    }

    /**
     * Test converting kilometres per hour to metres per second.
     */
    public function testConvertKilometresPerHourToMetresPerSecond(): void
    {
        $vel = new Velocity(36, 'km/h');
        $ms = $vel->to('m/s');

        // 36 km/h = 10 m/s
        $this->assertSame(10.0, $ms->value);
    }

    // endregion

    // region Imperial conversion tests

    /**
     * Test converting miles per hour to feet per second.
     */
    public function testConvertMilesPerHourToFeetPerSecond(): void
    {
        $vel = new Velocity(60, 'mi/h');
        $fps = $vel->to('ft/s');

        // 60 mi/h = 60 × 5280 ft / 3600 s = 88 ft/s
        $this->assertSame(88.0, $fps->value);
    }

    /**
     * Test converting feet per second to miles per hour.
     */
    public function testConvertFeetPerSecondToMilesPerHour(): void
    {
        $vel = new Velocity(88, 'ft/s');
        $mph = $vel->to('mi/h');

        // 88 ft/s = 60 mi/h
        $this->assertApproxEqual(60.0, $mph->value);
    }

    // endregion

    // region Nautical conversion tests

    /**
     * Test converting knots to nautical miles per hour.
     */
    public function testConvertKnotsToNauticalMilesPerHour(): void
    {
        $vel = new Velocity(10, 'kn');
        $nmih = $vel->to('nmi/h');

        // 1 kn = 1 nmi/h by definition
        $this->assertSame(10.0, $nmih->value);
    }

    /**
     * Test converting knots to metres per second.
     */
    public function testConvertKnotsToMetresPerSecond(): void
    {
        $vel = new Velocity(1, 'kn');
        $ms = $vel->to('m/s');

        // 1 kn = 1 nmi/h = 1852 m / 3600 s ≈ 0.514444 m/s
        $this->assertApproxEqual(1852.0 / 3600.0, $ms->value);
    }

    /**
     * Test converting metres per second to knots.
     */
    public function testConvertMetresPerSecondToKnots(): void
    {
        $vel = new Velocity(1, 'm/s');
        $kn = $vel->to('kn');

        // 1 m/s = 3600/1852 kn ≈ 1.94384 kn
        $this->assertApproxEqual(3600.0 / 1852.0, $kn->value);
    }

    /**
     * Test converting knots to kilometres per hour.
     */
    public function testConvertKnotsToKilometresPerHour(): void
    {
        $vel = new Velocity(1, 'kn');
        $kmh = $vel->to('km/h');

        // 1 kn = 1.852 km/h
        $this->assertSame(1.852, $kmh->value);
    }

    // endregion

    // region Cross-system conversion tests

    /**
     * Test converting kilometres per hour to miles per hour.
     */
    public function testConvertKilometresPerHourToMilesPerHour(): void
    {
        $vel = new Velocity(100, 'km/h');
        $mph = $vel->to('mi/h');

        // 1 km = 1000 m, 1 mi = 1609.344 m
        // 100 km/h = 100 × 1000 / 1609.344 mi/h ≈ 62.1371 mi/h
        $this->assertApproxEqual(100 * 1000 / 1609.344, $mph->value);
    }

    /**
     * Test converting miles per hour to kilometres per hour.
     */
    public function testConvertMilesPerHourToKilometresPerHour(): void
    {
        $vel = new Velocity(88, 'mi/h');
        $kmh = $vel->to('km/h');

        $this->assertApproxEqual(88 * 1609.344 / 1000, $kmh->value);
    }

    /**
     * Test converting miles per hour to knots.
     */
    public function testConvertMilesPerHourToKnots(): void
    {
        $vel = new Velocity(60, 'mi/h');
        $kn = $vel->to('kn');

        // 60 mi/h = 60 × 1609.344 / 1852 kn ≈ 52.138 kn
        $this->assertApproxEqual(60 * 1609.344 / 1852, $kn->value);
    }

    /**
     * Test converting knots to miles per hour.
     */
    public function testConvertKnotsToMilesPerHour(): void
    {
        $vel = new Velocity(50, 'kn');
        $mph = $vel->to('mi/h');

        // 50 kn = 50 × 1852 / 1609.344 mi/h ≈ 57.539 mi/h
        $this->assertApproxEqual(50 * 1852 / 1609.344, $mph->value);
    }

    // endregion

    // region Division tests (Length / Time = Velocity)

    /**
     * Test dividing metres by seconds.
     */
    public function testDivMetresBySeconds(): void
    {
        $length = new Length(100, 'm');
        $time = new Time(10, 's');
        $result = $length->div($time);

        // 100 m / 10 s = 10 m/s
        $this->assertInstanceOf(Velocity::class, $result);
        $this->assertSame(10.0, $result->value);
        $this->assertSame('m/s', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test dividing kilometres by hours.
     */
    public function testDivKilometresByHours(): void
    {
        $length = new Length(120, 'km');
        $time = new Time(2, 'h');
        $result = $length->div($time);

        // 120 km / 2 h = 60 km/h
        $this->assertInstanceOf(Velocity::class, $result);
        $this->assertSame(60.0, $result->value);
        $this->assertSame('km/h', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test dividing miles by hours.
     */
    public function testDivMilesByHours(): void
    {
        $length = new Length(150, 'mi');
        $time = new Time(2.5, 'h');
        $result = $length->div($time);

        // 150 mi / 2.5 h = 60 mi/h
        $this->assertInstanceOf(Velocity::class, $result);
        $this->assertSame(60.0, $result->value);
        $this->assertSame('mi/h', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test dividing nautical miles by hours produces knots-compatible velocity.
     */
    public function testDivNauticalMilesByHours(): void
    {
        $length = new Length(100, 'nmi');
        $time = new Time(2, 'h');
        $result = $length->div($time);

        // 100 nmi / 2 h = 50 nmi/h = 50 kn
        $this->assertInstanceOf(Velocity::class, $result);
        $this->assertSame(50.0, $result->value);

        // Convert to knots
        $kn = $result->to('kn');
        $this->assertSame(50.0, $kn->value);
    }

    // endregion

    // region Multiplication tests (Velocity × Time = Length)

    /**
     * Test multiplying metres per second by seconds.
     */
    public function testMulMetresPerSecondBySeconds(): void
    {
        $vel = new Velocity(10, 'm/s');
        $time = new Time(5, 's');
        $result = $vel->mul($time);

        // 10 m/s × 5 s = 50 m
        $this->assertInstanceOf(Length::class, $result);
        $this->assertSame(50.0, $result->value);
        $this->assertSame('m', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test multiplying kilometres per hour by hours.
     */
    public function testMulKilometresPerHourByHours(): void
    {
        $vel = new Velocity(60, 'km/h');
        $time = new Time(2.5, 'h');
        $result = $vel->mul($time);

        // 60 km/h × 2.5 h = 150 km
        $this->assertInstanceOf(Length::class, $result);
        $this->assertSame(150.0, $result->value);
        $this->assertSame('km', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test multiplying knots by hours.
     */
    public function testMulKnotsByHours(): void
    {
        $vel = new Velocity(20, 'kn');
        $time = new Time(3, 'h');
        $result = $vel->mul($time);

        // 20 kn × 3 h = 60 kn·h
        $this->assertSame(60.0, $result->value);

        // Convert to nautical miles to verify
        $nmi = $result->to('nmi');
        $this->assertInstanceOf(Length::class, $nmi);
        $this->assertSame(60.0, $nmi->value);
    }

    // endregion

    // region Addition tests

    /**
     * Test adding velocities in same units.
     */
    public function testAddSameUnits(): void
    {
        $a = new Velocity(50, 'km/h');
        $b = new Velocity(30, 'km/h');
        $result = $a->add($b);

        $this->assertInstanceOf(Velocity::class, $result);
        $this->assertSame(80.0, $result->value);
        $this->assertSame('km/h', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding metres per second to kilometres per hour.
     */
    public function testAddMetresPerSecondToKilometresPerHour(): void
    {
        $a = new Velocity(100, 'km/h');
        $b = new Velocity(10, 'm/s');
        $result = $a->add($b);

        // 100 km/h + 10 m/s = 100 km/h + 36 km/h = 136 km/h
        $this->assertInstanceOf(Velocity::class, $result);
        $this->assertSame(136.0, $result->value);
        $this->assertSame('km/h', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding knots to miles per hour.
     */
    public function testAddKnotsToMilesPerHour(): void
    {
        $a = new Velocity(60, 'mi/h');
        $b = new Velocity(10, 'kn');
        $result = $a->add($b);

        // 60 mi/h + 10 kn = 60 mi/h + 10 × 1852/1609.344 mi/h ≈ 71.508 mi/h
        $this->assertInstanceOf(Velocity::class, $result);
        $this->assertApproxEqual(60 + 10 * 1852 / 1609.344, $result->value);
        $this->assertSame('mi/h', $result->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Parse tests

    /**
     * Test parsing metres per second.
     */
    public function testParseMetresPerSecond(): void
    {
        $vel = Velocity::parse('25 m/s');

        $this->assertInstanceOf(Velocity::class, $vel);
        $this->assertSame(25.0, $vel->value);
        $this->assertSame('m/s', $vel->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing kilometres per hour.
     */
    public function testParseKilometresPerHour(): void
    {
        $vel = Velocity::parse('120 km/h');

        $this->assertSame(120.0, $vel->value);
        $this->assertSame('km/h', $vel->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing knots.
     */
    public function testParseKnots(): void
    {
        $vel = Velocity::parse('15 kn');

        $this->assertSame(15.0, $vel->value);
        $this->assertSame('kn', $vel->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing miles per hour.
     */
    public function testParseMilesPerHour(): void
    {
        $vel = Velocity::parse('65 mi/h');

        $this->assertSame(65.0, $vel->value);
        $this->assertSame('mi/h', $vel->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Static convert method tests

    /**
     * Test static convert method.
     */
    public function testStaticConvertMethod(): void
    {
        $value = Velocity::convert(1, 'm/s', 'km/h');

        $this->assertSame(3.6, $value);
    }

    /**
     * Test static convert cross-system.
     */
    public function testStaticConvertCrossSystem(): void
    {
        $value = Velocity::convert(1, 'kn', 'km/h');

        $this->assertSame(1.852, $value);
    }

    // endregion

    // region Practical examples

    /**
     * Test speed of sound conversion.
     */
    public function testSpeedOfSound(): void
    {
        // Speed of sound at sea level ≈ 343 m/s
        $speedOfSound = new Velocity(343, 'm/s');

        $kmh = $speedOfSound->to('km/h');
        $mph = $speedOfSound->to('mi/h');
        $kn = $speedOfSound->to('kn');

        $this->assertApproxEqual(1234.8, $kmh->value);
        $this->assertApproxEqual(343 * 3600 / 1609.344, $mph->value);
        $this->assertApproxEqual(343 * 3600 / 1852, $kn->value);
    }

    /**
     * Test speed limit conversion (practical example).
     */
    public function testSpeedLimitConversion(): void
    {
        // 100 km/h speed limit
        $limit = new Velocity(100, 'km/h');
        $mph = $limit->to('mi/h');

        // ≈ 62.14 mph
        $this->assertApproxEqual(100 * 1000 / 1609.344, $mph->value);
    }

    // endregion
}
