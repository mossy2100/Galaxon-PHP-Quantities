<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\QuantityType;

use Galaxon\Core\Traits\FloatAssertions;
use Galaxon\Quantities\QuantityType\Area;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Volume;
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;
use Galaxon\Quantities\Tests\Traits\ArrayShapeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Volume quantity type.
 */
#[CoversClass(Volume::class)]
final class VolumeTest extends TestCase
{
    use ArrayShapeTrait;
    use FloatAssertions;

    // region Setup

    public static function setUpBeforeClass(): void
    {
        // Load Imperial/US units.
        UnitRegistry::loadSystem(System::Imperial);
        UnitRegistry::loadSystem(System::UsCustomary);
    }

    // endregion

    // region Overridden methods

    /**
     * Test getUnitDefinitions() returns valid unit definitions.
     */
    public function testGetUnitDefinitionsReturnsValidArray(): void
    {
        $units = Volume::getUnitDefinitions();

        $this->assertValidUnitDefinitionsShape($units);
    }

    /**
     * Test getConversionDefinitions() returns valid conversion definitions.
     */
    public function testGetConversionDefinitionsReturnsValidArray(): void
    {
        $conversions = Volume::getConversionDefinitions();

        $this->assertValidConversionDefinitionsShape($conversions);
    }

    // endregion

    // region Metric conversion tests

    /**
     * Test converting cubic meters to liters.
     */
    public function testConvertCubicMetersToLitres(): void
    {
        $vol = new Volume(1, 'm3');
        $l = $vol->to('L');

        $this->assertInstanceOf(Volume::class, $l);
        $this->assertSame(1000.0, $l->value);
        $this->assertSame('L', $l->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting liters to cubic meters.
     */
    public function testConvertLitresToCubicMeters(): void
    {
        $vol = new Volume(1000, 'L');
        $m3 = $vol->to('m3');

        $this->assertSame(1.0, $m3->value);
    }

    /**
     * Test converting liters to milliliters.
     */
    public function testConvertLitresToMilliliters(): void
    {
        $vol = new Volume(1, 'L');
        $ml = $vol->to('mL');

        $this->assertSame(1000.0, $ml->value);
    }

    /**
     * Test converting milliliters to liters.
     */
    public function testConvertMillilitersToLitres(): void
    {
        $vol = new Volume(500, 'mL');
        $l = $vol->to('L');

        $this->assertSame(0.5, $l->value);
    }

    /**
     * Test converting cubic centimeters to milliliters.
     */
    public function testConvertCubicCentimetersToMilliliters(): void
    {
        $vol = new Volume(1, 'cm3');
        $ml = $vol->to('mL');

        // 1 cm³ = 1 mL
        $this->assertApproxEqual(1.0, $ml->value);
    }

    /**
     * Test converting liters to cubic centimeters.
     */
    public function testConvertLitresToCubicCentimeters(): void
    {
        $vol = new Volume(1, 'L');
        $cm3 = $vol->to('cm3');

        // 1 L = 1000 cm³
        $this->assertApproxEqual(1000.0, $cm3->value);
    }

    // endregion

    // region US customary conversion tests

    /**
     * Test converting US gallons to US quarts.
     */
    public function testConvertUSGallonsToUSQuarts(): void
    {
        $vol = new Volume(1, 'US gal');
        $qt = $vol->to('US qt');

        $this->assertSame(4.0, $qt->value);
        $this->assertSame('US qt', $qt->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting US quarts to US pints.
     */
    public function testConvertUSQuartsToUSPints(): void
    {
        $vol = new Volume(1, 'US qt');
        $pt = $vol->to('US pt');

        $this->assertSame(2.0, $pt->value);
    }

    /**
     * Test converting US pints to US fluid ounces.
     */
    public function testConvertUSPintsToUSFluidOunces(): void
    {
        $vol = new Volume(1, 'US pt');
        $floz = $vol->to('US fl oz');

        $this->assertSame(16.0, $floz->value);
    }

    /**
     * Test converting US gallons to US fluid ounces.
     */
    public function testConvertUSGallonsToUSFluidOunces(): void
    {
        $vol = new Volume(1, 'US gal');
        $floz = $vol->to('US fl oz');

        // 1 US gal = 4 qt × 2 pt × 16 fl oz = 128 fl oz
        $this->assertSame(128.0, $floz->value);
    }

    /**
     * Test converting US gallons to cubic inches.
     */
    public function testConvertUSGallonsToCubicInches(): void
    {
        $vol = new Volume(1, 'US gal');
        $in3 = $vol->to('in3');

        $this->assertSame(231.0, $in3->value);
    }

    // endregion

    // region Imperial conversion tests

    /**
     * Test converting imperial gallons to imperial quarts.
     */
    public function testConvertImperialGallonsToImperialQuarts(): void
    {
        $vol = new Volume(1, 'imp gal');
        $qt = $vol->to('imp qt');

        $this->assertSame(4.0, $qt->value);
        $this->assertSame('imp qt', $qt->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting imperial quarts to imperial pints.
     */
    public function testConvertImperialQuartsToImperialPints(): void
    {
        $vol = new Volume(1, 'imp qt');
        $pt = $vol->to('imp pt');

        $this->assertSame(2.0, $pt->value);
    }

    /**
     * Test converting imperial pints to imperial fluid ounces.
     */
    public function testConvertImperialPintsToImperialFluidOunces(): void
    {
        $vol = new Volume(1, 'imp pt');
        $floz = $vol->to('imp fl oz');

        // Imperial pint = 20 fl oz (not 16 like US)
        $this->assertSame(20.0, $floz->value);
    }

    /**
     * Test converting imperial gallons to imperial fluid ounces.
     */
    public function testConvertImperialGallonsToImperialFluidOunces(): void
    {
        $vol = new Volume(1, 'imp gal');
        $floz = $vol->to('imp fl oz');

        // 1 imp gal = 4 qt × 2 pt × 20 fl oz = 160 fl oz
        $this->assertSame(160.0, $floz->value);
    }

    /**
     * Test converting imperial gallons to liters.
     */
    public function testConvertImperialGallonsToLitres(): void
    {
        $vol = new Volume(1, 'imp gal');
        $l = $vol->to('L');

        $this->assertSame(4.54609, $l->value);
    }

    // endregion

    // region Cross-system conversion tests

    /**
     * Test converting liters to US gallons.
     */
    public function testConvertLitresToUSGallons(): void
    {
        $vol = new Volume(1, 'L');
        $gal = $vol->to('US gal');

        // 1 L = 1000 mL = 1000 cm³
        // 1 US gal = 231 in³ = 231 × 16.387064 mL = 3785.411784 mL
        // 1 L = 1000/3785.411784 US gal ≈ 0.264172 US gal
        $this->assertApproxEqual(1000 / 3785.411784, $gal->value);
    }

    /**
     * Test converting US gallons to liters.
     */
    public function testConvertUSGallonsToLitres(): void
    {
        $vol = new Volume(1, 'US gal');
        $l = $vol->to('L');

        // 1 US gal = 231 in³ = 231 × 16.387064 mL = 3785.411784 mL = 3.785411784 L
        $this->assertApproxEqual(3.785411784, $l->value);
    }

    /**
     * Test converting US gallons to imperial gallons.
     */
    public function testConvertUSGallonsToImperialGallons(): void
    {
        $vol = new Volume(1, 'US gal');
        $impGal = $vol->to('imp gal');

        // 1 US gal = 3.785411784 L
        // 1 imp gal = 4.54609 L
        // 1 US gal = 3.785411784 / 4.54609 imp gal ≈ 0.832674 imp gal
        $this->assertApproxEqual(3.785411784 / 4.54609, $impGal->value);
    }

    /**
     * Test converting imperial gallons to US gallons.
     */
    public function testConvertImperialGallonsToUSGallons(): void
    {
        $vol = new Volume(1, 'imp gal');
        $usGal = $vol->to('US gal');

        // 1 imp gal = 4.54609 L = 4.54609 / 3.785411784 US gal ≈ 1.20095 US gal
        $this->assertApproxEqual(4.54609 / 3.785411784, $usGal->value);
    }

    /**
     * Test converting US fluid ounces to imperial fluid ounces.
     */
    public function testConvertUSFluidOuncesToImperialFluidOunces(): void
    {
        $vol = new Volume(1, 'US fl oz');
        $impFloz = $vol->to('imp fl oz');

        // 1 US fl oz = 3785.411784 / 128 mL = 29.5735... mL
        // 1 imp fl oz = 4546.09 / 160 mL = 28.4130625 mL
        // 1 US fl oz = 29.5735... / 28.4130625 imp fl oz ≈ 1.04084 imp fl oz
        $usFlozInMl = 3785.411784 / 128;
        $impFlozInMl = 4546.09 / 160;
        $this->assertApproxEqual($usFlozInMl / $impFlozInMl, $impFloz->value);
    }

    // endregion

    // region Multiplication tests (Area × Length = Volume)

    /**
     * Test multiplying square meters by meters.
     */
    public function testMulSquareMetersByMeters(): void
    {
        $area = new Area(10, 'm2');
        $length = new Length(5, 'm');
        $result = $area->mul($length);

        // 10 m² × 5 m = 50 m³
        $this->assertInstanceOf(Volume::class, $result);
        $this->assertSame(50.0, $result->value);
        $this->assertSame('m³', $result->derivedUnit->unicodeSymbol);
    }

    /**
     * Test multiplying square feet by feet.
     */
    public function testMulSquareFeetByFeet(): void
    {
        $area = new Area(100, 'ft2');
        $length = new Length(10, 'ft');
        $result = $area->mul($length);

        // 100 ft² × 10 ft = 1000 ft³
        $this->assertInstanceOf(Volume::class, $result);
        $this->assertSame(1000.0, $result->value);
        $this->assertSame('ft³', $result->derivedUnit->unicodeSymbol);
    }

    /**
     * Test multiplying square centimeters by centimeters.
     */
    public function testMulSquareCentimetersByCentimeters(): void
    {
        $area = new Area(100, 'cm2');
        $length = new Length(10, 'cm');
        $result = $area->mul($length);

        // 100 cm² × 10 cm = 1000 cm³ = 1 L
        $this->assertInstanceOf(Volume::class, $result);
        $this->assertSame(1000.0, $result->value);
        $this->assertSame('cm³', $result->derivedUnit->unicodeSymbol);

        // Verify conversion to liters
        $l = $result->to('L');
        $this->assertApproxEqual(1.0, $l->value);
    }

    /**
     * Test multiplying square meters by kilometers (mixed metric).
     */
    public function testMulSquareMetersByKilometers(): void
    {
        $area = new Area(1000000, 'm2');
        $length = new Length(1, 'km');
        $result = $area->mul($length);

        // 1000000 m² × 1 km = 1000000 m² × 1000 m = 1,000,000,000 m³
        $this->assertInstanceOf(Volume::class, $result);
        $this->assertSame(1000000000.0, $result->value);
        $this->assertSame('m³', $result->derivedUnit->unicodeSymbol);
    }

    /**
     * Test multiplying Length × Length × Length = Volume.
     */
    public function testMulLengthByLengthByLength(): void
    {
        $a = new Length(2, 'm');
        $b = new Length(3, 'm');
        $c = new Length(4, 'm');
        $result = $a->mul($b)->mul($c);

        // 2 m × 3 m × 4 m = 24 m³
        $this->assertInstanceOf(Volume::class, $result);
        $this->assertSame(24.0, $result->value);
        $this->assertSame('m³', $result->derivedUnit->unicodeSymbol);
    }

    // endregion

    // region Addition tests

    /**
     * Test adding liters to liters.
     */
    public function testAddLitresToLitres(): void
    {
        $a = new Volume(500, 'mL');
        $b = new Volume(750, 'mL');
        $result = $a->add($b);

        $this->assertInstanceOf(Volume::class, $result);
        $this->assertSame(1250.0, $result->value);
        $this->assertSame('mL', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding milliliters to liters.
     */
    public function testAddMillilitersToLitres(): void
    {
        $a = new Volume(1, 'L');
        $b = new Volume(500, 'mL');
        $result = $a->add($b);

        $this->assertInstanceOf(Volume::class, $result);
        $this->assertSame(1.5, $result->value);
        $this->assertSame('L', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding US pints to US gallons.
     */
    public function testAddUSPintsToUSGallons(): void
    {
        $a = new Volume(1, 'US gal');
        $b = new Volume(4, 'US pt');
        $result = $a->add($b);

        // 1 US gal + 4 US pt = 1 US gal + 0.5 US gal = 1.5 US gal
        $this->assertInstanceOf(Volume::class, $result);
        $this->assertSame(1.5, $result->value);
        $this->assertSame('US gal', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding imperial pints to imperial gallons.
     */
    public function testAddImperialPintsToImperialGallons(): void
    {
        $a = new Volume(1, 'imp gal');
        $b = new Volume(4, 'imp pt');
        $result = $a->add($b);

        // 1 imp gal + 4 imp pt = 1 imp gal + 0.5 imp gal = 1.5 imp gal
        $this->assertInstanceOf(Volume::class, $result);
        $this->assertSame(1.5, $result->value);
        $this->assertSame('imp gal', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding liters to US gallons (cross-system).
     */
    public function testAddLitresToUSGallons(): void
    {
        $a = new Volume(1, 'US gal');
        $b = new Volume(1, 'L');
        $result = $a->add($b);

        // 1 US gal + 1 L = 1 US gal + 0.264172... US gal ≈ 1.264172 US gal
        $this->assertInstanceOf(Volume::class, $result);
        $this->assertApproxEqual(1 + 1000 / 3785.411784, $result->value);
        $this->assertSame('US gal', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding US gallons to imperial gallons (cross-system).
     */
    public function testAddUSGallonsToImperialGallons(): void
    {
        $a = new Volume(1, 'imp gal');
        $b = new Volume(1, 'US gal');
        $result = $a->add($b);

        // 1 imp gal + 1 US gal = 1 imp gal + 0.832674... imp gal ≈ 1.832674 imp gal
        $this->assertInstanceOf(Volume::class, $result);
        $this->assertApproxEqual(1 + 3.785411784 / 4.54609, $result->value);
        $this->assertSame('imp gal', $result->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Parse tests

    /**
     * Test parsing liters.
     */
    public function testParseLitres(): void
    {
        $vol = Volume::parse('2.5 L');

        $this->assertInstanceOf(Volume::class, $vol);
        $this->assertSame(2.5, $vol->value);
        $this->assertSame('L', $vol->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing milliliters.
     */
    public function testParseMilliliters(): void
    {
        $vol = Volume::parse('500 mL');

        $this->assertSame(500.0, $vol->value);
        $this->assertSame('mL', $vol->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing cubic meters.
     */
    public function testParseCubicMeters(): void
    {
        $vol = Volume::parse('10 m3');

        $this->assertSame(10.0, $vol->value);
        $this->assertSame('m³', $vol->derivedUnit->unicodeSymbol);
    }

    /**
     * Test parsing US gallons.
     */
    public function testParseUSGallons(): void
    {
        $vol = Volume::parse('5 US gal');

        $this->assertSame(5.0, $vol->value);
        $this->assertSame('US gal', $vol->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Static convert method tests

    /**
     * Test static convert method.
     */
    public function testStaticConvertMethod(): void
    {
        $value = Volume::convert(1, 'L', 'mL');

        $this->assertSame(1000.0, $value);
    }

    /**
     * Test static convert cross-system.
     */
    public function testStaticConvertCrossSystem(): void
    {
        $value = Volume::convert(1, 'imp gal', 'L');

        $this->assertSame(4.54609, $value);
    }

    // endregion
}
