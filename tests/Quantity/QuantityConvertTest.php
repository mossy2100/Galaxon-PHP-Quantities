<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Quantity;

use DomainException;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType\Area;
use Galaxon\Quantities\QuantityType\Force;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\QuantityType\Pressure;
use Galaxon\Quantities\QuantityType\Time;
use Galaxon\Quantities\QuantityType\Velocity;
use Galaxon\Quantities\QuantityType\Volume;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Quantity conversion methods: to(), toSi(), and convert().
 */
#[CoversClass(Quantity::class)]
#[CoversClass(Length::class)]
#[CoversClass(Mass::class)]
#[CoversClass(Time::class)]
#[CoversClass(Area::class)]
#[CoversClass(Volume::class)]
#[CoversClass(Velocity::class)]
#[CoversClass(Force::class)]
#[CoversClass(Pressure::class)]
final class QuantityConvertTest extends TestCase
{
    // region to() basic tests

    /**
     * Test to() with same unit returns equivalent value.
     */
    public function testToSameUnit(): void
    {
        $length = new Length(100, 'm');

        $result = $length->to('m');

        $this->assertSame(100.0, $result->value);
        $this->assertSame('m', $result->derivedUnit->format(true));
    }

    /**
     * Test to() returns new instance.
     */
    public function testToReturnsNewInstance(): void
    {
        $length = new Length(100, 'm');

        $result = $length->to('km');

        $this->assertNotSame($length, $result);
    }

    /**
     * Test to() throws for invalid unit.
     */
    public function testToThrowsForInvalidUnit(): void
    {
        $length = new Length(100, 'm');

        $this->expectException(DomainException::class);

        $length->to('invalid');
    }

    /**
     * Test to() throws for incompatible dimension.
     */
    public function testToThrowsForIncompatibleDimension(): void
    {
        $length = new Length(100, 'm');

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('different quantity types');

        $length->to('kg');
    }

    // endregion

    // region to() SI prefix conversions

    /**
     * Test metres to kilometres.
     */
    public function testMetresToKilometres(): void
    {
        $length = new Length(1000, 'm');

        $result = $length->to('km');

        $this->assertEqualsWithDelta(1.0, $result->value, 1e-10);
        $this->assertSame('km', (string)$result->derivedUnit);
    }

    /**
     * Test kilometres to metres.
     */
    public function testKilometresToMetres(): void
    {
        $length = new Length(1, 'km');

        $result = $length->to('m');

        $this->assertEqualsWithDelta(1000.0, $result->value, 1e-10);
        $this->assertSame('m', (string)$result->derivedUnit);
    }

    /**
     * Test metres to centimetres.
     */
    public function testMetresToCentimetres(): void
    {
        $length = new Length(1, 'm');

        $result = $length->to('cm');

        $this->assertEqualsWithDelta(100.0, $result->value, 1e-10);
        $this->assertSame('cm', (string)$result->derivedUnit);
    }

    /**
     * Test metres to millimetres.
     */
    public function testMetresToMillimetres(): void
    {
        $length = new Length(1, 'm');

        $result = $length->to('mm');

        $this->assertEqualsWithDelta(1000.0, $result->value, 1e-10);
        $this->assertSame('mm', (string)$result->derivedUnit);
    }

    /**
     * Test kilograms to grams.
     */
    public function testKilogramsToGrams(): void
    {
        $mass = new Mass(1, 'kg');

        $result = $mass->to('g');

        $this->assertEqualsWithDelta(1000.0, $result->value, 1e-10);
        $this->assertSame('g', (string)$result->derivedUnit);
    }

    /**
     * Test grams to milligrams.
     */
    public function testGramsToMilligrams(): void
    {
        $mass = new Mass(1, 'g');

        $result = $mass->to('mg');

        $this->assertEqualsWithDelta(1000.0, $result->value, 1e-10);
        $this->assertSame('mg', (string)$result->derivedUnit);
    }

    /**
     * Test seconds to milliseconds.
     */
    public function testSecondsToMilliseconds(): void
    {
        $time = new Time(1, 's');

        $result = $time->to('ms');

        $this->assertEqualsWithDelta(1000.0, $result->value, 1e-10);
        $this->assertSame('ms', (string)$result->derivedUnit);
    }

    // endregion

    // region to() SI to US customary - Length

    /**
     * Test metres to feet.
     */
    public function testMetresToFeet(): void
    {
        $length = new Length(1, 'm');

        $result = $length->to('ft');

        // 1 m = 1/0.3048 ft ≈ 3.28084 ft
        $this->assertEqualsWithDelta(3.28084, $result->value, 0.00001);
        $this->assertSame('ft', (string)$result->derivedUnit);
    }

    /**
     * Test metres to inches.
     */
    public function testMetresToInches(): void
    {
        $length = new Length(1, 'm');

        $result = $length->to('in');

        // 1 m ≈ 39.3701 in
        $this->assertEqualsWithDelta(39.3701, $result->value, 0.0001);
        $this->assertSame('in', (string)$result->derivedUnit);
    }

    /**
     * Test kilometres to miles.
     */
    public function testKilometresToMiles(): void
    {
        $length = new Length(1, 'km');

        $result = $length->to('mi');

        // 1 km ≈ 0.621371 mi
        $this->assertEqualsWithDelta(0.621371, $result->value, 0.000001);
        $this->assertSame('mi', (string)$result->derivedUnit);
    }

    /**
     * Test metres to yards.
     */
    public function testMetresToYards(): void
    {
        $length = new Length(1, 'm');

        $result = $length->to('yd');

        // 1 m = 1/0.9144 yd ≈ 1.09361 yd
        $this->assertEqualsWithDelta(1.09361, $result->value, 0.00001);
        $this->assertSame('yd', (string)$result->derivedUnit);
    }

    // endregion

    // region to() US customary to SI - Length

    /**
     * Test feet to metres.
     */
    public function testFeetToMetres(): void
    {
        $length = new Length(1, 'ft');

        $result = $length->to('m');

        // 1 ft = 0.3048 m (exact)
        $this->assertEqualsWithDelta(0.3048, $result->value, 1e-10);
        $this->assertSame('m', (string)$result->derivedUnit);
    }

    /**
     * Test inches to centimetres.
     */
    public function testInchesToCentimetres(): void
    {
        $length = new Length(1, 'in');

        $result = $length->to('cm');

        // 1 in = 2.54 cm (exact)
        $this->assertEqualsWithDelta(2.54, $result->value, 1e-10);
        $this->assertSame('cm', (string)$result->derivedUnit);
    }

    /**
     * Test miles to kilometres.
     */
    public function testMilesToKilometres(): void
    {
        $length = new Length(1, 'mi');

        $result = $length->to('km');

        // 1 mi ≈ 1.60934 km
        $this->assertEqualsWithDelta(1.60934, $result->value, 0.00001);
        $this->assertSame('km', (string)$result->derivedUnit);
    }

    /**
     * Test yards to metres.
     */
    public function testYardsToMetres(): void
    {
        $length = new Length(1, 'yd');

        $result = $length->to('m');

        // 1 yd = 0.9144 m (exact)
        $this->assertEqualsWithDelta(0.9144, $result->value, 1e-10);
        $this->assertSame('m', (string)$result->derivedUnit);
    }

    // endregion

    // region to() SI to US customary - Mass

    /**
     * Test kilograms to pounds.
     */
    public function testKilogramsToPounds(): void
    {
        $mass = new Mass(1, 'kg');

        $result = $mass->to('lb');

        // 1 kg ≈ 2.20462 lb
        $this->assertEqualsWithDelta(2.20462, $result->value, 0.00001);
        $this->assertSame('lb', (string)$result->derivedUnit);
    }

    /**
     * Test grams to ounces.
     */
    public function testGramsToOunces(): void
    {
        $mass = new Mass(100, 'g');

        $result = $mass->to('oz');

        // 100 g ≈ 3.5274 oz
        $this->assertEqualsWithDelta(3.5274, $result->value, 0.0001);
        $this->assertSame('oz', (string)$result->derivedUnit);
    }

    // endregion

    // region to() US customary to SI - Mass

    /**
     * Test pounds to kilograms.
     */
    public function testPoundsToKilograms(): void
    {
        $mass = new Mass(1, 'lb');

        $result = $mass->to('kg');

        // 1 lb = 0.45359237 kg (exact)
        $this->assertEqualsWithDelta(0.45359237, $result->value, 1e-8);
        $this->assertSame('kg', (string)$result->derivedUnit);
    }

    /**
     * Test ounces to grams.
     */
    public function testOuncesToGrams(): void
    {
        $mass = new Mass(1, 'oz');

        $result = $mass->to('g');

        // 1 oz ≈ 28.3495 g
        $this->assertEqualsWithDelta(28.3495, $result->value, 0.0001);
        $this->assertSame('g', (string)$result->derivedUnit);
    }

    // endregion

    // region to() Area conversions

    /**
     * Test square metres to square feet.
     */
    public function testSquareMetresToSquareFeet(): void
    {
        $area = new Area(1, 'm2');

        $result = $area->to('ft2');

        // 1 m² ≈ 10.7639 ft²
        $this->assertEqualsWithDelta(10.7639, $result->value, 0.0001);
        $this->assertSame('ft²', (string)$result->derivedUnit);
    }

    /**
     * Test square feet to square metres.
     */
    public function testSquareFeetToSquareMetres(): void
    {
        $area = new Area(1, 'ft2');

        $result = $area->to('m2');

        // 1 ft² = 0.092903 m²
        $this->assertEqualsWithDelta(0.092903, $result->value, 0.000001);
        $this->assertSame('m²', (string)$result->derivedUnit);
    }

    /**
     * Test hectares to acres.
     */
    public function testHectaresToAcres(): void
    {
        $area = new Area(1, 'ha');

        $result = $area->to('ac');

        // 1 ha ≈ 2.47105 ac
        $this->assertEqualsWithDelta(2.47105, $result->value, 0.00001);
        $this->assertSame('ac', (string)$result->derivedUnit);
    }

    // endregion

    // region to() Volume conversions

    /**
     * Test litres to gallons (US).
     */
    public function testLitresToGallons(): void
    {
        $volume = new Volume(1, 'L');

        $result = $volume->to('gal');

        // 1 L ≈ 0.264172 gal
        $this->assertEqualsWithDelta(0.264172, $result->value, 0.000001);
        $this->assertSame('gal', (string)$result->derivedUnit);
    }

    /**
     * Test gallons to litres.
     */
    public function testGallonsToLitres(): void
    {
        $volume = new Volume(1, 'gal');

        $result = $volume->to('L');

        // 1 gal ≈ 3.78541 L
        $this->assertEqualsWithDelta(3.78541, $result->value, 0.00001);
        $this->assertSame('L', (string)$result->derivedUnit);
    }

    /**
     * Test cubic metres to cubic feet.
     */
    public function testCubicMetresToCubicFeet(): void
    {
        $volume = new Volume(1, 'm3');

        $result = $volume->to('ft3');

        // 1 m³ ≈ 35.3147 ft³
        $this->assertEqualsWithDelta(35.3147, $result->value, 0.0001);
        $this->assertSame('ft³', (string)$result->derivedUnit);
    }

    // endregion

    // region to() Named unit conversions - Force

    /**
     * Test newtons to pound-force.
     */
    public function testNewtonsToPoundForce(): void
    {
        $force = new Force(1, 'N');

        $result = $force->to('lbf');

        // 1 N ≈ 0.224809 lbf
        $this->assertEqualsWithDelta(0.224809, $result->value, 0.000001);
        $this->assertSame('lbf', (string)$result->derivedUnit);
    }

    /**
     * Test pound-force to newtons.
     */
    public function testPoundForceToNewtons(): void
    {
        $force = new Force(1, 'lbf');

        $result = $force->to('N');

        // 1 lbf ≈ 4.44822 N
        $this->assertEqualsWithDelta(4.44822, $result->value, 0.00001);
        $this->assertSame('N', (string)$result->derivedUnit);
    }

    /**
     * Test kilonewtons to newtons.
     */
    public function testKilonewtonsToNewtons(): void
    {
        $force = new Force(1, 'kN');

        $result = $force->to('N');

        $this->assertEqualsWithDelta(1000.0, $result->value, 1e-10);
        $this->assertSame('N', (string)$result->derivedUnit);
    }

    /**
     * Test kilonewtons to pound-force.
     */
    public function testKilonewtonsToPoundForce(): void
    {
        $force = new Force(1, 'kN');

        $result = $force->to('lbf');

        // 1 kN ≈ 224.809 lbf
        $this->assertEqualsWithDelta(224.809, $result->value, 0.001);
        $this->assertSame('lbf', (string)$result->derivedUnit);
    }

    /**
     * Test pound-force to kilonewtons.
     */
    public function testPoundForceToKilonewtons(): void
    {
        $force = new Force(1000, 'lbf');

        $result = $force->to('kN');

        // 1000 lbf ≈ 4.44822 kN
        $this->assertEqualsWithDelta(4.44822, $result->value, 0.00001);
        $this->assertSame('kN', (string)$result->derivedUnit);
    }

    /**
     * Test newtons to pound-force with larger value.
     */
    public function testNewtonsToPoundForceLargeValue(): void
    {
        $force = new Force(1000, 'N');

        $result = $force->to('lbf');

        // 1000 N ≈ 224.809 lbf
        $this->assertEqualsWithDelta(224.809, $result->value, 0.001);
        $this->assertSame('lbf', (string)$result->derivedUnit);
    }

    /**
     * Test pound-force to newtons with larger value.
     */
    public function testPoundForceToNewtonsLargeValue(): void
    {
        $force = new Force(100, 'lbf');

        $result = $force->to('N');

        // 100 lbf ≈ 444.822 N
        $this->assertEqualsWithDelta(444.822, $result->value, 0.001);
        $this->assertSame('N', (string)$result->derivedUnit);
    }

    /**
     * Test round-trip conversion N -> lbf -> N preserves value.
     */
    public function testForceRoundTripNewtonsPoundForce(): void
    {
        $original = new Force(123.456, 'N');

        $result = $original->to('lbf')->to('N');

        $this->assertEqualsWithDelta(123.456, $result->value, 1e-6);
    }

    /**
     * Test round-trip conversion lbf -> N -> lbf preserves value.
     */
    public function testForceRoundTripPoundForceNewtons(): void
    {
        $original = new Force(50.5, 'lbf');

        $result = $original->to('N')->to('lbf');

        $this->assertEqualsWithDelta(50.5, $result->value, 1e-6);
    }

    // endregion

    // region to() Named unit conversions - Pressure

    /**
     * Test pascals to bar.
     */
    public function testPascalsToBar(): void
    {
        $pressure = new Pressure(100000, 'Pa');

        $result = $pressure->to('bar');

        $this->assertEqualsWithDelta(1.0, $result->value, 1e-10);
        $this->assertSame('bar', (string)$result->derivedUnit);
    }

    /**
     * Test bar to pascals.
     */
    public function testBarToPascals(): void
    {
        $pressure = new Pressure(1, 'bar');

        $result = $pressure->to('Pa');

        $this->assertEqualsWithDelta(100000.0, $result->value, 1e-10);
        $this->assertSame('Pa', (string)$result->derivedUnit);
    }

    /**
     * Test pascals to atmospheres.
     */
    public function testPascalsToAtmospheres(): void
    {
        $pressure = new Pressure(101325, 'Pa');

        $result = $pressure->to('atm');

        $this->assertEqualsWithDelta(1.0, $result->value, 1e-10);
        $this->assertSame('atm', (string)$result->derivedUnit);
    }

    /**
     * Test kilopascals to bar.
     */
    public function testKilopascalsToBar(): void
    {
        $pressure = new Pressure(100, 'kPa');

        $result = $pressure->to('bar');

        $this->assertEqualsWithDelta(1.0, $result->value, 1e-10);
        $this->assertSame('bar', (string)$result->derivedUnit);
    }

    /**
     * Test pascals to PSI (lbf/in²).
     */
    public function testPascalsToPsi(): void
    {
        $pressure = new Pressure(6894.76, 'Pa');

        $result = $pressure->to('lbf/in2');

        // 6894.76 Pa ≈ 1 PSI
        $this->assertEqualsWithDelta(1.0, $result->value, 0.001);
    }

    /**
     * Test PSI (lbf/in²) to pascals.
     */
    public function testPsiToPascals(): void
    {
        $pressure = Quantity::create(1, 'lbf/in2');

        $result = $pressure->to('Pa');

        // 1 PSI ≈ 6894.76 Pa
        $this->assertEqualsWithDelta(6894.76, $result->value, 0.01);
    }

    /**
     * Test kilopascals to PSI.
     */
    public function testKilopascalsToPsi(): void
    {
        $pressure = new Pressure(100, 'kPa');

        $result = $pressure->to('lbf/in2');

        // 100 kPa ≈ 14.5038 PSI
        $this->assertEqualsWithDelta(14.5038, $result->value, 0.0001);
    }

    /**
     * Test PSI to kilopascals.
     */
    public function testPsiToKilopascals(): void
    {
        $pressure = Quantity::create(14.5038, 'lbf/in2');

        $result = $pressure->to('kPa');

        // 14.5038 PSI ≈ 100 kPa
        $this->assertEqualsWithDelta(100.0, $result->value, 0.01);
    }

    /**
     * Test bar to PSI.
     */
    public function testBarToPsi(): void
    {
        $pressure = new Pressure(1, 'bar');

        $result = $pressure->to('lbf/in2');

        // 1 bar ≈ 14.5038 PSI
        $this->assertEqualsWithDelta(14.5038, $result->value, 0.0001);
    }

    /**
     * Test PSI to bar.
     */
    public function testPsiToBar(): void
    {
        $pressure = Quantity::create(14.5038, 'lbf/in2');

        $result = $pressure->to('bar');

        // 14.5038 PSI ≈ 1 bar
        $this->assertEqualsWithDelta(1.0, $result->value, 0.001);
    }

    /**
     * Test atmosphere to PSI.
     */
    public function testAtmosphereToPsi(): void
    {
        $pressure = new Pressure(1, 'atm');

        $result = $pressure->to('lbf/in2');

        // 1 atm ≈ 14.696 PSI
        $this->assertEqualsWithDelta(14.696, $result->value, 0.001);
    }

    /**
     * Test PSI to atmosphere.
     */
    public function testPsiToAtmosphere(): void
    {
        $pressure = Quantity::create(14.696, 'lbf/in2');

        $result = $pressure->to('atm');

        // 14.696 PSI ≈ 1 atm
        $this->assertEqualsWithDelta(1.0, $result->value, 0.001);
    }

    /**
     * Test round-trip conversion Pa -> PSI -> Pa preserves value.
     */
    public function testPressureRoundTripPascalsPsi(): void
    {
        $original = new Pressure(50000, 'Pa');

        $result = $original->to('lbf/in2')->to('Pa');

        $this->assertEqualsWithDelta(50000.0, $result->value, 0.1);
    }

    /**
     * Test round-trip conversion PSI -> Pa -> PSI preserves value.
     */
    public function testPressureRoundTripPsiPascals(): void
    {
        $original = Quantity::create(30, 'lbf/in2');

        $result = $original->to('Pa')->to('lbf/in2');

        $this->assertEqualsWithDelta(30.0, $result->value, 0.001);
    }

    /**
     * Test typical tire pressure conversion (32 PSI to kPa).
     */
    public function testTirePressurePsiToKpa(): void
    {
        $pressure = Quantity::create(32, 'lbf/in2');

        $result = $pressure->to('kPa');

        // 32 PSI ≈ 220.6 kPa
        $this->assertEqualsWithDelta(220.6, $result->value, 0.1);
    }

    /**
     * Test typical tire pressure conversion (220 kPa to PSI).
     */
    public function testTirePressureKpaToPsi(): void
    {
        $pressure = new Pressure(220, 'kPa');

        $result = $pressure->to('lbf/in2');

        // 220 kPa ≈ 31.9 PSI
        $this->assertEqualsWithDelta(31.9, $result->value, 0.1);
    }

    // endregion

    // region to() Complex/Compound unit conversions - Velocity

    /**
     * Test metres per second to kilometres per hour.
     */
    public function testMetresPerSecondToKilometresPerHour(): void
    {
        $velocity = Quantity::create(1, 'm/s');

        $result = $velocity->to('km/h');

        // 1 m/s = 3.6 km/h
        $this->assertEqualsWithDelta(3.6, $result->value, 1e-10);
    }

    /**
     * Test kilometres per hour to metres per second.
     */
    public function testKilometresPerHourToMetresPerSecond(): void
    {
        $velocity = Quantity::create(36, 'km/h');

        $result = $velocity->to('m/s');

        // 36 km/h = 10 m/s
        $this->assertEqualsWithDelta(10.0, $result->value, 1e-10);
    }

    /**
     * Test metres per second to knots.
     */
    public function testMetresPerSecondToKnots(): void
    {
        $velocity = Quantity::create(1, 'm/s');

        $result = $velocity->to('kn');

        // 1 m/s ≈ 1.94384 knots
        $this->assertEqualsWithDelta(1.94384, $result->value, 0.00001);
    }

    /**
     * Test knots to metres per second.
     */
    public function testKnotsToMetresPerSecond(): void
    {
        $velocity = new Velocity(1, 'kn');

        $result = $velocity->to('m/s');

        // 1 knot = 1852/3600 m/s ≈ 0.514444 m/s
        $this->assertEqualsWithDelta(0.514444, $result->value, 0.000001);
    }

    /**
     * Test miles per hour to kilometres per hour.
     */
    public function testMilesPerHourToKilometresPerHour(): void
    {
        $velocity = Quantity::create(60, 'mi/h');

        $result = $velocity->to('km/h');

        // 60 mph ≈ 96.5606 km/h
        $this->assertEqualsWithDelta(96.5606, $result->value, 0.0001);
    }

    /**
     * Test feet per second to metres per second.
     */
    public function testFeetPerSecondToMetresPerSecond(): void
    {
        $velocity = Quantity::create(1, 'ft/s');

        $result = $velocity->to('m/s');

        // 1 ft/s = 0.3048 m/s
        $this->assertEqualsWithDelta(0.3048, $result->value, 1e-10);
    }

    // endregion

    // region toSi() tests

    /**
     * Test toSi converts length to metres.
     */
    public function testToSiConvertsLengthToMetres(): void
    {
        $length = new Length(1, 'km');

        $result = $length->toSi();

        $this->assertEqualsWithDelta(1000.0, $result->value, 1e-10);
        $this->assertSame('m', (string)$result->derivedUnit);
    }

    /**
     * Test toSi converts mass to kilograms.
     */
    public function testToSiConvertsMassToKilograms(): void
    {
        $mass = new Mass(1000, 'g');

        $result = $mass->toSi();

        $this->assertEqualsWithDelta(1.0, $result->value, 1e-10);
        $this->assertSame('kg', (string)$result->derivedUnit);
    }

    /**
     * Test toSi converts time to seconds.
     */
    public function testToSiConvertsTimeToSeconds(): void
    {
        $time = new Time(1, 'min');

        $result = $time->toSi();

        $this->assertEqualsWithDelta(60.0, $result->value, 1e-10);
        $this->assertSame('s', (string)$result->derivedUnit);
    }

    /**
     * Test toSi converts US customary length to metres.
     */
    public function testToSiConvertsFeetToMetres(): void
    {
        $length = new Length(1, 'ft');

        $result = $length->toSi();

        $this->assertEqualsWithDelta(0.3048, $result->value, 1e-10);
        $this->assertSame('m', (string)$result->derivedUnit);
    }

    /**
     * Test toSi converts compound velocity to m/s.
     */
    public function testToSiConvertsVelocityToMetresPerSecond(): void
    {
        $velocity = Quantity::create(1, 'km/h');

        $result = $velocity->toSi();

        // 1 km/h = 1000/3600 m/s ≈ 0.277778 m/s
        $this->assertEqualsWithDelta(0.277778, $result->value, 0.000001);
        $this->assertSame('m⋅s⁻¹', (string)$result->derivedUnit);
    }

    /**
     * Test toSi converts force to kg*m/s².
     */
    public function testToSiConvertsNewtonToBaseUnits(): void
    {
        $force = new Force(1, 'N');

        $result = $force->toSi();

        $this->assertEqualsWithDelta(1.0, $result->value, 1e-10);
        $this->assertSame('kg⋅m⋅s⁻²', (string)$result->derivedUnit);
    }

    // endregion

    // region convert() static method tests

    /**
     * Test static convert with simple units.
     */
    public function testConvertStaticSimpleUnits(): void
    {
        $result = Quantity::convert(1, 'km', 'm');

        $this->assertEqualsWithDelta(1000.0, $result, 1e-10);
    }

    /**
     * Test static convert throws for incompatible units.
     */
    public function testConvertStaticThrowsForIncompatibleUnits(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('different quantity types');

        Quantity::convert(1, 'm', 'kg');
    }

    /**
     * Test static convert with compound units.
     */
    public function testConvertStaticCompoundUnits(): void
    {
        $result = Quantity::convert(1, 'm/s', 'km/h');

        $this->assertEqualsWithDelta(3.6, $result, 1e-10);
    }

    // endregion

    // region simplify() tests - without autoprefixing

    /**
     * Test simplify converts kg⋅m⋅s⁻² to newtons.
     */
    public function testSimplifyForceToNewtons(): void
    {
        $force = Quantity::create(10, 'kg*m*s-2');

        $result = $force->simplify(false);

        $this->assertEqualsWithDelta(10.0, $result->value, 1e-10);
        $this->assertSame('N', (string)$result->derivedUnit);
    }

    /**
     * Test simplify converts kg⋅m²⋅s⁻² to joules.
     */
    public function testSimplifyEnergyToJoules(): void
    {
        $energy = Quantity::create(100, 'kg*m2*s-2');

        $result = $energy->simplify(false);

        $this->assertEqualsWithDelta(100.0, $result->value, 1e-10);
        $this->assertSame('J', (string)$result->derivedUnit);
    }

    /**
     * Test simplify converts kg⋅m⁻¹⋅s⁻² to pascals.
     */
    public function testSimplifyPressureToPascals(): void
    {
        $pressure = Quantity::create(101325, 'kg*m-1*s-2');

        $result = $pressure->simplify(false);

        $this->assertEqualsWithDelta(101325.0, $result->value, 1e-10);
        $this->assertSame('Pa', (string)$result->derivedUnit);
    }

    /**
     * Test simplify converts kg⋅m²⋅s⁻³ to watts.
     */
    public function testSimplifyPowerToWatts(): void
    {
        $power = Quantity::create(1000, 'kg*m2*s-3');

        $result = $power->simplify(false);

        $this->assertEqualsWithDelta(1000.0, $result->value, 1e-10);
        $this->assertSame('W', (string)$result->derivedUnit);
    }

    /**
     * Test simplify converts s⁻¹ to hertz.
     */
    public function testSimplifyFrequencyToHertz(): void
    {
        $frequency = Quantity::create(50, 's-1');

        $result = $frequency->simplify(false);

        $this->assertEqualsWithDelta(50.0, $result->value, 1e-10);
        $this->assertSame('Hz', (string)$result->derivedUnit);
    }

    /**
     * Test simplify converts ms⁻¹ to kHz.
     */
    public function testSimplifyFrequencyToKilohertz(): void
    {
        $frequency = Quantity::create(50, 'ms-1');

        $result = $frequency->simplify(false);

        $this->assertEqualsWithDelta(50.0, $result->value, 1e-10);
        $this->assertSame('kHz', (string)$result->derivedUnit);
    }

    /**
     * Test simplify converts kg⋅m²⋅s⁻³⋅A⁻¹ to volts.
     */
    public function testSimplifyVoltageToVolts(): void
    {
        $voltage = Quantity::create(12, 'kg*m2*s-3*A-1');

        $result = $voltage->simplify(false);

        $this->assertEqualsWithDelta(12.0, $result->value, 1e-10);
        $this->assertSame('V', (string)$result->derivedUnit);
    }

    /**
     * Test simplify converts s⋅A to coulombs.
     */
    public function testSimplifyChargeToCoulombs(): void
    {
        $charge = Quantity::create(5, 's*A');

        $result = $charge->simplify(false);

        $this->assertEqualsWithDelta(5.0, $result->value, 1e-10);
        $this->assertSame('C', (string)$result->derivedUnit);
    }

    /**
     * Test simplify leaves simple units unchanged.
     */
    public function testSimplifyLeavesSimpleUnitsUnchanged(): void
    {
        $length = new Length(100, 'm');

        $result = $length->simplify(false);

        $this->assertEqualsWithDelta(100.0, $result->value, 1e-10);
        $this->assertSame('m', (string)$result->derivedUnit);
    }

    /**
     * Test simplify leaves already-simplified units unchanged.
     */
    public function testSimplifyLeavesNamedUnitsUnchanged(): void
    {
        $force = new Force(50, 'N');

        $result = $force->simplify(false);

        $this->assertEqualsWithDelta(50.0, $result->value, 1e-10);
        $this->assertSame('N', (string)$result->derivedUnit);
    }

    /**
     * Test simplify preserves value correctly.
     */
    public function testSimplifyPreservesValue(): void
    {
        $force = Quantity::create(123.456, 'kg*m*s-2');

        $result = $force->simplify(false);

        $this->assertEqualsWithDelta(123.456, $result->value, 1e-10);
    }

    /**
     * Test simplify with zero value.
     */
    public function testSimplifyWithZeroValue(): void
    {
        $force = Quantity::create(0, 'kg*m*s-2');

        $result = $force->simplify(false);

        $this->assertSame(0.0, $result->value);
        $this->assertSame('N', (string)$result->derivedUnit);
    }

    /**
     * Test simplify with negative value.
     */
    public function testSimplifyWithNegativeValue(): void
    {
        $force = Quantity::create(-50, 'kg*m*s-2');

        $result = $force->simplify(false);

        $this->assertEqualsWithDelta(-50.0, $result->value, 1e-10);
        $this->assertSame('N', (string)$result->derivedUnit);
    }

    /**
     * Test simplify returns new instance.
     */
    public function testSimplifyReturnsNewInstance(): void
    {
        $force = Quantity::create(10, 'kg*m*s-2');

        $result = $force->simplify(false);

        $this->assertNotSame($force, $result);
    }

    /**
     * Test simplify does not autoprefix when disabled.
     */
    public function testSimplifyDoesNotAutoPrefixWhenDisabled(): void
    {
        // 1000 N should stay as 1000 N, not become 1 kN
        $force = Quantity::create(1000, 'kg*m*s-2');

        $result = $force->simplify(false);

        $this->assertEqualsWithDelta(1000.0, $result->value, 1e-10);
        $this->assertSame('N', (string)$result->derivedUnit);
    }

    /**
     * Test simplify with compound velocity (m/s) - should not simplify to named unit.
     */
    public function testSimplifyVelocityStaysCompound(): void
    {
        $velocity = Quantity::create(10, 'm/s');

        $result = $velocity->simplify(false);

        // m/s has no SI named unit, should stay as m⋅s⁻¹
        $this->assertEqualsWithDelta(10.0, $result->value, 1e-10);
        $this->assertSame('m⋅s⁻¹', (string)$result->derivedUnit);
    }

    /**
     * Test simplify with acceleration (m/s²) - should not simplify to named unit.
     */
    public function testSimplifyAccelerationStaysCompound(): void
    {
        $acceleration = Quantity::create(9.8, 'm/s2');

        $result = $acceleration->simplify(false);

        // m/s² has no SI named unit, should stay as m⋅s⁻²
        $this->assertEqualsWithDelta(9.8, $result->value, 1e-10);
        $this->assertSame('m⋅s⁻²', (string)$result->derivedUnit);
    }

    // endregion

    // region autoPrefix() tests

    /**
     * Test autoPrefix converts 1000 m to 1 km.
     */
    public function testAutoPrefixMetresToKilometres(): void
    {
        $length = new Length(1000, 'm');

        $result = $length->autoPrefix();

        $this->assertEqualsWithDelta(1.0, $result->value, 1e-10);
        $this->assertSame('km', (string)$result->derivedUnit);
    }

    /**
     * Test autoPrefix converts 0.001 m to 1 mm.
     */
    public function testAutoPrefixMetresToMillimetres(): void
    {
        $length = new Length(0.001, 'm');

        $result = $length->autoPrefix();

        $this->assertEqualsWithDelta(1.0, $result->value, 1e-10);
        $this->assertSame('mm', (string)$result->derivedUnit);
    }

    /**
     * Test autoPrefix converts 0.000001 m to 1 μm.
     */
    public function testAutoPrefixMetresToMicrometres(): void
    {
        $length = new Length(0.000001, 'm');

        $result = $length->autoPrefix();

        $this->assertEqualsWithDelta(1.0, $result->value, 1e-10);
        $this->assertSame('μm', (string)$result->derivedUnit);
    }

    /**
     * Test autoPrefix converts 1000000 m to 1 Mm.
     */
    public function testAutoPrefixMetresToMegametres(): void
    {
        $length = new Length(1000000, 'm');

        $result = $length->autoPrefix();

        $this->assertEqualsWithDelta(1.0, $result->value, 1e-10);
        $this->assertSame('Mm', (string)$result->derivedUnit);
    }

    /**
     * Test autoPrefix leaves value in good range unchanged.
     */
    public function testAutoPrefixLeavesGoodRangeUnchanged(): void
    {
        $length = new Length(5, 'm');

        $result = $length->autoPrefix();

        $this->assertEqualsWithDelta(5.0, $result->value, 1e-10);
        $this->assertSame('m', (string)$result->derivedUnit);
    }

    /**
     * Test autoPrefix with value between 1 and 1000.
     */
    public function testAutoPrefixValueBetween1And1000(): void
    {
        $length = new Length(500, 'm');

        $result = $length->autoPrefix();

        // With engineering prefixes, 500 m stays as 500 m (no c, d, da, h prefixes)
        $this->assertEqualsWithDelta(500.0, $result->value, 1e-10);
        $this->assertSame('m', (string)$result->derivedUnit);
    }

    /**
     * Test autoPrefix converts 1500 m to 1.5 km.
     */
    public function testAutoPrefixPartialKilometres(): void
    {
        $length = new Length(1500, 'm');

        $result = $length->autoPrefix();

        $this->assertEqualsWithDelta(1.5, $result->value, 1e-10);
        $this->assertSame('km', (string)$result->derivedUnit);
    }

    /**
     * Test autoPrefix with negative value.
     */
    public function testAutoPrefixNegativeValue(): void
    {
        $length = new Length(-1000, 'm');

        $result = $length->autoPrefix();

        $this->assertEqualsWithDelta(-1.0, $result->value, 1e-10);
        $this->assertSame('km', (string)$result->derivedUnit);
    }

    /**
     * Test autoPrefix with zero value.
     */
    public function testAutoPrefixZeroValue(): void
    {
        $length = new Length(0, 'm');

        $result = $length->autoPrefix();

        $this->assertSame(0.0, $result->value);
    }

    /**
     * Test autoPrefix leaves non-SI units unchanged.
     */
    public function testAutoPrefixLeavesNonSiUnchanged(): void
    {
        $length = new Length(5280, 'ft');

        $result = $length->autoPrefix();

        // Non-SI, should return unchanged
        $this->assertEqualsWithDelta(5280.0, $result->value, 1e-10);
        $this->assertSame('ft', (string)$result->derivedUnit);
    }

    /**
     * Test autoPrefix with already prefixed unit.
     */
    public function testAutoPrefixAlreadyPrefixedUnit(): void
    {
        $length = new Length(1000, 'km');

        $result = $length->autoPrefix();

        // 1000 km = 1 Mm
        $this->assertEqualsWithDelta(1.0, $result->value, 1e-10);
        $this->assertSame('Mm', (string)$result->derivedUnit);
    }

    /**
     * Test autoPrefix converts 1000 g to 1 kg.
     */
    public function testAutoPrefixGramsToKilograms(): void
    {
        $mass = new Mass(1000, 'g');

        $result = $mass->autoPrefix();

        $this->assertEqualsWithDelta(1.0, $result->value, 1e-10);
        $this->assertSame('kg', (string)$result->derivedUnit);
    }

    /**
     * Test autoPrefix converts 0.001 g to 1 mg.
     */
    public function testAutoPrefixGramsToMilligrams(): void
    {
        $mass = new Mass(0.001, 'g');

        $result = $mass->autoPrefix();

        $this->assertEqualsWithDelta(1.0, $result->value, 1e-10);
        $this->assertSame('mg', (string)$result->derivedUnit);
    }

    /**
     * Test autoPrefix with compound unit (velocity).
     */
    public function testAutoPrefixCompoundVelocity(): void
    {
        $velocity = Quantity::create(1000, 'm/s');

        $result = $velocity->autoPrefix();

        // Should prefix the first unit term (m → km)
        $this->assertEqualsWithDelta(1.0, $result->value, 1e-10);
        $this->assertSame('km⋅s⁻¹', (string)$result->derivedUnit);
    }

    /**
     * Test autoPrefix with small compound unit.
     */
    public function testAutoPrefixSmallCompoundVelocity(): void
    {
        $velocity = Quantity::create(0.001, 'm/s');

        $result = $velocity->autoPrefix();

        // Should prefix the first unit term (m → mm)
        $this->assertEqualsWithDelta(1.0, $result->value, 1e-10);
        $this->assertSame('mm⋅s⁻¹', (string)$result->derivedUnit);
    }

    /**
     * Test autoPrefix with force in newtons.
     */
    public function testAutoPrefixNewtonsToKilonewtons(): void
    {
        $force = new Force(1000, 'N');

        $result = $force->autoPrefix();

        $this->assertEqualsWithDelta(1.0, $result->value, 1e-10);
        $this->assertSame('kN', (string)$result->derivedUnit);
    }

    /**
     * Test autoPrefix with pressure in pascals.
     */
    public function testAutoPrefixPascalsToKilopascals(): void
    {
        $pressure = new Pressure(1000, 'Pa');

        $result = $pressure->autoPrefix();

        $this->assertEqualsWithDelta(1.0, $result->value, 1e-10);
        $this->assertSame('kPa', (string)$result->derivedUnit);
    }

    /**
     * Test autoPrefix with very large value.
     */
    public function testAutoPrefixVeryLargeValue(): void
    {
        $length = new Length(1e12, 'm');

        $result = $length->autoPrefix();

        // 1e12 m = 1 Tm (tera)
        $this->assertEqualsWithDelta(1.0, $result->value, 1e-10);
        $this->assertSame('Tm', (string)$result->derivedUnit);
    }

    /**
     * Test autoPrefix with very small value.
     */
    public function testAutoPrefixVerySmallValue(): void
    {
        $length = new Length(1e-12, 'm');

        $result = $length->autoPrefix();

        // 1e-12 m = 1 pm (pico)
        $this->assertEqualsWithDelta(1.0, $result->value, 1e-10);
        $this->assertSame('pm', (string)$result->derivedUnit);
    }

    /**
     * Test autoPrefix returns new instance.
     */
    public function testAutoPrefixReturnsNewInstance(): void
    {
        $length = new Length(1000, 'm');

        $result = $length->autoPrefix();

        $this->assertNotSame($length, $result);
    }

    /**
     * Test autoPrefix preserves dimension.
     */
    public function testAutoPrefixPreservesDimension(): void
    {
        $length = new Length(1000, 'm');

        $result = $length->autoPrefix();

        $this->assertSame('L', $result->derivedUnit->dimension);
    }

    // endregion

    // region reduce() tests

    /**
     * Test reduce combines m/m² to m⁻¹.
     */
    public function testReduceMetresDividedBySquareMetres(): void
    {
        $qty = Quantity::create(10, 'm/m2');

        $result = $qty->reduce();

        $this->assertEqualsWithDelta(10.0, $result->value, 1e-10);
        $this->assertSame('m⁻¹', (string)$result->derivedUnit);
    }

    /**
     * Test reduce combines m²/m to m.
     */
    public function testReduceSquareMetresDividedByMetres(): void
    {
        $qty = Quantity::create(20, 'm2/m');

        $result = $qty->reduce();

        $this->assertEqualsWithDelta(20.0, $result->value, 1e-10);
        $this->assertSame('m', (string)$result->derivedUnit);
    }

    /**
     * Test reduce converts ft/m to single unit (dimensionless after conversion).
     */
    public function testReduceFeetDividedByMetres(): void
    {
        // 1 ft/m = 0.3048 (dimensionless ratio)
        $qty = Quantity::create(1, 'ft/m');

        $result = $qty->reduce();

        // Should convert ft to m, giving m/m = dimensionless
        $this->assertEqualsWithDelta(0.3048, $result->value, 1e-10);
        $this->assertTrue($result->isDimensionless());
    }

    /**
     * Test reduce converts m/ft to single unit (dimensionless after conversion).
     */
    public function testReduceMetresDividedByFeet(): void
    {
        // 1 m/ft = 1/0.3048 ≈ 3.28084
        $qty = Quantity::create(1, 'm/ft');

        $result = $qty->reduce();

        $this->assertEqualsWithDelta(3.28084, $result->value, 0.00001);
        $this->assertTrue($result->isDimensionless());
    }

    /**
     * Test reduce converts m⋅ft to single area unit.
     */
    public function testReduceMetresTimesFeet(): void
    {
        // 1 m⋅ft should become m² (converting ft to m first)
        $qty = Quantity::create(1, 'm*ft');

        $result = $qty->reduce();

        // 1 m × 0.3048 m = 0.3048 m²
        $this->assertEqualsWithDelta(0.3048, $result->value, 1e-10);
        $this->assertSame('m²', (string)$result->derivedUnit);
    }

    /**
     * Test reduce converts dL/L to dimensionless ratio.
     */
    public function testReduceDecilitresDividedByLitres(): void
    {
        // 1 dL/L = 0.1
        $qty = Quantity::create(1, 'dL/L');

        $result = $qty->reduce();

        $this->assertEqualsWithDelta(0.1, $result->value, 1e-10);
        $this->assertTrue($result->isDimensionless());
    }

    /**
     * Test reduce converts km/m to dimensionless ratio.
     */
    public function testReduceKilometresDividedByMetres(): void
    {
        // 1 km/m = 1000
        $qty = Quantity::create(1, 'km/m');

        $result = $qty->reduce();

        $this->assertEqualsWithDelta(1000.0, $result->value, 1e-10);
        $this->assertTrue($result->isDimensionless());
    }

    /**
     * Test reduce converts m/km to dimensionless ratio.
     */
    public function testReduceMetresDividedByKilometres(): void
    {
        // 1 m/km = 0.001
        $qty = Quantity::create(1, 'm/km');

        $result = $qty->reduce();

        $this->assertEqualsWithDelta(0.001, $result->value, 1e-10);
        $this->assertTrue($result->isDimensionless());
    }

    /**
     * Test reduce converts cm²/m² to dimensionless ratio.
     */
    public function testReduceSquareCentimetresDividedBySquareMetres(): void
    {
        // 1 cm²/m² = 0.0001
        $qty = Quantity::create(1, 'cm2/m2');

        $result = $qty->reduce();

        $this->assertEqualsWithDelta(0.0001, $result->value, 1e-10);
        $this->assertTrue($result->isDimensionless());
    }

    /**
     * Test reduce with kg/g gives dimensionless ratio.
     */
    public function testReduceKilogramsDividedByGrams(): void
    {
        // 1 kg/g = 1000
        $qty = Quantity::create(1, 'kg/g');

        $result = $qty->reduce();

        $this->assertEqualsWithDelta(1000.0, $result->value, 1e-10);
        $this->assertTrue($result->isDimensionless());
    }

    /**
     * Test reduce leaves already-reduced units unchanged.
     */
    public function testReduceLeavesSimpleUnitsUnchanged(): void
    {
        $length = new Length(100, 'm');

        $result = $length->reduce();

        $this->assertEqualsWithDelta(100.0, $result->value, 1e-10);
        $this->assertSame('m', (string)$result->derivedUnit);
    }

    /**
     * Test reduce with compound unit m/s (different dimensions - no reduction).
     */
    public function testReduceVelocityUnchanged(): void
    {
        $velocity = Quantity::create(10, 'm/s');

        $result = $velocity->reduce();

        // Different dimensions, should stay as m⋅s⁻¹
        $this->assertEqualsWithDelta(10.0, $result->value, 1e-10);
        $this->assertSame('m⋅s⁻¹', (string)$result->derivedUnit);
    }

    /**
     * Test reduce preserves value with mixed length units.
     */
    public function testReducePreservesValueWithMixedUnits(): void
    {
        // 10 ft × 2 m should give area in single unit
        $area = Quantity::create(1, 'ft*m');

        $result = $area->reduce();

        // 1 ft × 1 m = 3.2808398950131235 m × 1 m = 3.2808398950131235 m²
        $this->assertEqualsWithDelta(3.2808398950131235, $result->value, 1e-10);
        $this->assertSame('ft²', (string)$result->derivedUnit);
    }

    /**
     * Test reduce with in/ft gives dimensionless.
     */
    public function testReduceInchesDividedByFeet(): void
    {
        // 1 in/ft = 1/12
        $qty = Quantity::create(1, 'in/ft');

        $result = $qty->reduce();

        $this->assertEqualsWithDelta(1.0 / 12.0, $result->value, 1e-10);
        $this->assertTrue($result->isDimensionless());
    }

    /**
     * Test reduce with ft/in gives dimensionless.
     */
    public function testReduceFeetDividedByInches(): void
    {
        // 1 ft/in = 12
        $qty = Quantity::create(1, 'ft/in');

        $result = $qty->reduce();

        $this->assertEqualsWithDelta(12.0, $result->value, 1e-10);
        $this->assertTrue($result->isDimensionless());
    }

    /**
     * Test reduce with mi/yd gives dimensionless.
     */
    public function testReduceMilesDividedByYards(): void
    {
        // 1 mi/yd = 1760
        $qty = Quantity::create(1, 'mi/yd');

        $result = $qty->reduce();

        $this->assertEqualsWithDelta(1760.0, $result->value, 1e-10);
        $this->assertTrue($result->isDimensionless());
    }

    /**
     * Test reduce returns new instance.
     */
    public function testReduceReturnsNewInstance(): void
    {
        $qty = Quantity::create(1, 'm/km');

        $result = $qty->reduce();

        $this->assertNotSame($qty, $result);
    }

    /**
     * Test reduce with zero value.
     */
    public function testReduceWithZeroValue(): void
    {
        $qty = Quantity::create(0, 'ft/m');

        $result = $qty->reduce();

        $this->assertSame(0.0, $result->value);
        $this->assertTrue($result->isDimensionless());
    }

    /**
     * Test reduce with negative value.
     */
    public function testReduceWithNegativeValue(): void
    {
        $qty = Quantity::create(-5, 'km/m');

        $result = $qty->reduce();

        $this->assertEqualsWithDelta(-5000.0, $result->value, 1e-10);
        $this->assertTrue($result->isDimensionless());
    }

    // endregion

    // region expand() tests

    /**
     * Test expand converts newtons to kg⋅m⋅s⁻².
     */
    public function testExpandNewtonsToBaseUnits(): void
    {
        $force = new Force(10, 'N');

        $result = $force->expand();

        $this->assertEqualsWithDelta(10.0, $result->value, 1e-10);
        $this->assertSame('kg⋅m⋅s⁻²', (string)$result->derivedUnit);
    }

    /**
     * Test expand converts joules to kg⋅m²⋅s⁻².
     */
    public function testExpandJoulesToBaseUnits(): void
    {
        $energy = Quantity::create(100, 'J');

        $result = $energy->expand();

        $this->assertEqualsWithDelta(100.0, $result->value, 1e-10);
        $this->assertSame('kg⋅m²⋅s⁻²', (string)$result->derivedUnit);
    }

    /**
     * Test expand converts pascals to kg⋅m⁻¹⋅s⁻².
     */
    public function testExpandPascalsToBaseUnits(): void
    {
        $pressure = new Pressure(101325, 'Pa');

        $result = $pressure->expand();

        $this->assertEqualsWithDelta(101325.0, $result->value, 1e-10);
        $this->assertSame('kg⋅m⁻¹⋅s⁻²', (string)$result->derivedUnit);
    }

    /**
     * Test expand converts watts to kg⋅m²⋅s⁻³.
     */
    public function testExpandWattsToBaseUnits(): void
    {
        $power = Quantity::create(1000, 'W');

        $result = $power->expand();

        $this->assertEqualsWithDelta(1000.0, $result->value, 1e-10);
        $this->assertSame('kg⋅m²⋅s⁻³', (string)$result->derivedUnit);
    }

    /**
     * Test expand converts hertz to s⁻¹.
     */
    public function testExpandHertzToBaseUnits(): void
    {
        $frequency = Quantity::create(50, 'Hz');

        $result = $frequency->expand();

        $this->assertEqualsWithDelta(50.0, $result->value, 1e-10);
        $this->assertSame('s⁻¹', (string)$result->derivedUnit);
    }

    /**
     * Test expand converts volts to kg⋅m²⋅s⁻³⋅A⁻¹.
     */
    public function testExpandVoltsToBaseUnits(): void
    {
        $voltage = Quantity::create(12, 'V');

        $result = $voltage->expand();

        $this->assertEqualsWithDelta(12.0, $result->value, 1e-10);
        $this->assertSame('kg⋅m²⋅s⁻³⋅A⁻¹', (string)$result->derivedUnit);
    }

    /**
     * Test expand converts coulombs to s⋅A.
     */
    public function testExpandCoulombsToBaseUnits(): void
    {
        $charge = Quantity::create(5, 'C');

        $result = $charge->expand();

        $this->assertEqualsWithDelta(5.0, $result->value, 1e-10);
        $this->assertSame('s⋅A', (string)$result->derivedUnit);
    }

    /**
     * Test expand converts ohms to kg⋅m²⋅s⁻³⋅A⁻².
     */
    public function testExpandOhmsToBaseUnits(): void
    {
        $resistance = Quantity::create(100, 'ohm');

        $result = $resistance->expand();

        $this->assertEqualsWithDelta(100.0, $result->value, 1e-10);
        $this->assertSame('kg⋅m²⋅s⁻³⋅A⁻²', (string)$result->derivedUnit);
    }

    /**
     * Test expand converts kilonewtons to kg⋅m⋅s⁻² with correct value.
     */
    public function testExpandKilonewtonsToBaseUnits(): void
    {
        $force = new Force(1, 'kN');

        $result = $force->expand();

        // 1 kN = 1000 kg⋅m⋅s⁻²
        $this->assertEqualsWithDelta(1000.0, $result->value, 1e-10);
        $this->assertSame('kg⋅m⋅s⁻²', (string)$result->derivedUnit);
    }

    /**
     * Test expand converts megapascals to kg⋅m⁻¹⋅s⁻² with correct value.
     */
    public function testExpandMegapascalsToBaseUnits(): void
    {
        $pressure = new Pressure(1, 'MPa');

        $result = $pressure->expand();

        // 1 MPa = 1,000,000 kg⋅m⁻¹⋅s⁻²
        $this->assertEqualsWithDelta(1e6, $result->value, 1e-4);
        $this->assertSame('kg⋅m⁻¹⋅s⁻²', (string)$result->derivedUnit);
    }

    /**
     * Test expand converts pound-force to base units with expansion value.
     */
    public function testExpandPoundForceToBaseUnits(): void
    {
        $force = new Force(1, 'lbf');

        $result = $force->expand();

        // lbf has an expansion value (9.80665 / 0.3048)
        // 1 lbf = (9.80665 / 0.3048) lb⋅ft⋅s⁻²
        $expectedValue = 9.80665 / 0.3048;
        $this->assertEqualsWithDelta($expectedValue, $result->value, 1e-6);
    }

    /**
     * Test expand with squared named unit.
     */
    public function testExpandSquaredNewtons(): void
    {
        // N² = (kg⋅m⋅s⁻²)² = kg²⋅m²⋅s⁻⁴
        $qty = Quantity::create(4, 'N2');

        $result = $qty->expand();

        $this->assertEqualsWithDelta(4.0, $result->value, 1e-10);
        $this->assertSame('kg²⋅m²⋅s⁻⁴', (string)$result->derivedUnit);
    }

    /**
     * Test expand with inverse named unit.
     */
    public function testExpandInverseNewtons(): void
    {
        // N⁻¹ = (kg⋅m⋅s⁻²)⁻¹ = kg⁻¹⋅m⁻¹⋅s²
        $qty = Quantity::create(2, 'N-1');

        $result = $qty->expand();

        $this->assertEqualsWithDelta(2.0, $result->value, 1e-10);
        $this->assertSame('kg⁻¹⋅m⁻¹⋅s²', (string)$result->derivedUnit);
    }

    /**
     * Test expand preserves value.
     */
    public function testExpandPreservesValue(): void
    {
        $force = new Force(123.456, 'N');

        $result = $force->expand();

        $this->assertEqualsWithDelta(123.456, $result->value, 1e-10);
    }

    /**
     * Test expand with zero value.
     */
    public function testExpandWithZeroValue(): void
    {
        $force = new Force(0, 'N');

        $result = $force->expand();

        $this->assertSame(0.0, $result->value);
        $this->assertSame('kg⋅m⋅s⁻²', (string)$result->derivedUnit);
    }

    /**
     * Test expand with negative value.
     */
    public function testExpandWithNegativeValue(): void
    {
        $force = new Force(-50, 'N');

        $result = $force->expand();

        $this->assertEqualsWithDelta(-50.0, $result->value, 1e-10);
        $this->assertSame('kg⋅m⋅s⁻²', (string)$result->derivedUnit);
    }

    /**
     * Test expand and simplify are inverse operations.
     */
    public function testExpandAndSimplifyAreInverse(): void
    {
        $original = new Force(100, 'N');

        $expanded = $original->expand();
        $simplified = $expanded->simplify(false);

        $this->assertEqualsWithDelta(100.0, $simplified->value, 1e-10);
        $this->assertSame('N', (string)$simplified->derivedUnit);
    }

    /**
     * Test expand returns new instance for expandable units.
     */
    public function testExpandReturnsNewInstanceForExpandableUnits(): void
    {
        $force = new Force(10, 'N');

        $result = $force->expand();

        $this->assertNotSame($force, $result);
    }

    // endregion

    // region Edge cases

    /**
     * Test converting zero value.
     */
    public function testConvertZeroValue(): void
    {
        $length = new Length(0, 'm');

        $result = $length->to('km');

        $this->assertSame(0.0, $result->value);
    }

    /**
     * Test converting negative value.
     */
    public function testConvertNegativeValue(): void
    {
        $length = new Length(-100, 'm');

        $result = $length->to('km');

        $this->assertEqualsWithDelta(-0.1, $result->value, 1e-10);
    }

    /**
     * Test converting very small value.
     */
    public function testConvertVerySmallValue(): void
    {
        $length = new Length(1e-10, 'm');

        $result = $length->to('nm');

        // 1e-10 m = 0.1 nm
        $this->assertEqualsWithDelta(0.1, $result->value, 1e-15);
    }

    /**
     * Test converting very large value.
     */
    public function testConvertVeryLargeValue(): void
    {
        $length = new Length(1e15, 'm');

        $result = $length->to('km');

        $this->assertEqualsWithDelta(1e12, $result->value, 1e5);
    }

    // endregion
}
