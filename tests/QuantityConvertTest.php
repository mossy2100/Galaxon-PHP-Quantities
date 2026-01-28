<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests;

use Galaxon\Core\Floats;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType\Angle;
use Galaxon\Quantities\QuantityType\Area;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\QuantityType\Temperature;
use Galaxon\Quantities\QuantityType\Time;
use Galaxon\Quantities\QuantityType\Volume;
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for converting Quantity objects between units.
 */
#[CoversClass(Quantity::class)]
final class QuantityConvertTest extends TestCase
{
    // region Setup

    public static function setUpBeforeClass(): void
    {
        // Load Imperial/US units once for cross-system conversion tests.
        UnitRegistry::loadSystem(System::Imperial);
        UnitRegistry::loadSystem(System::US);
    }

    // endregion

    // region Basic conversion tests (same system)

    /**
     * Test converting metres to kilometres.
     */
    public function testConvertMetresToKilometres(): void
    {
        $length = new Length(1000, 'm');
        $km = $length->to('km');

        $this->assertInstanceOf(Length::class, $km);
        $this->assertSame(1.0, $km->value);
        $this->assertSame('km', $km->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting kilometres to metres.
     */
    public function testConvertKilometresToMetres(): void
    {
        $length = new Length(1, 'km');
        $m = $length->to('m');

        $this->assertSame(1000.0, $m->value);
        $this->assertSame('m', $m->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting grams to kilograms.
     */
    public function testConvertGramsToKilograms(): void
    {
        $mass = new Mass(1000, 'g');
        $kg = $mass->to('kg');

        $this->assertSame(1.0, $kg->value);
    }

    /**
     * Test converting seconds to minutes.
     */
    public function testConvertSecondsToMinutes(): void
    {
        $time = new Time(120, 's');
        $min = $time->to('min');

        $this->assertSame(2.0, $min->value);
    }

    /**
     * Test converting minutes to hours.
     */
    public function testConvertMinutesToHours(): void
    {
        $time = new Time(90, 'min');
        $h = $time->to('h');

        $this->assertSame(1.5, $h->value);
    }

    /**
     * Test converting degrees to radians.
     */
    public function testConvertDegreesToRadians(): void
    {
        $angle = new Angle(180, 'deg');
        $rad = $angle->to('rad');

        $this->assertTrue(Floats::approxEqual(M_PI, $rad->value));
    }

    /**
     * Test converting radians to degrees.
     */
    public function testConvertRadiansToDegrees(): void
    {
        $angle = new Angle(M_PI / 2, 'rad');
        $deg = $angle->to('deg');

        $this->assertTrue(Floats::approxEqual(90.0, $deg->value));
    }

    // endregion

    // region Cross-system conversion tests (metric to imperial/US)

    /**
     * Test converting metres to feet.
     */
    public function testConvertMetresToFeet(): void
    {
        $length = new Length(1, 'm');
        $ft = $length->to('ft');

        // 1 metre = 3.280839895... feet
        $this->assertTrue(Floats::approxEqual(1 / 0.3048, $ft->value));
    }

    /**
     * Test converting feet to metres.
     */
    public function testConvertFeetToMetres(): void
    {
        $length = new Length(1, 'ft');
        $m = $length->to('m');

        // 1 foot = 0.3048 metres (exactly)
        $this->assertSame(0.3048, $m->value);
    }

    /**
     * Test converting inches to centimetres.
     */
    public function testConvertInchesToCentimetres(): void
    {
        $length = new Length(1, 'in');
        $cm = $length->to('cm');

        // 1 inch = 2.54 cm (exactly)
        $this->assertSame(2.54, $cm->value);
    }

    /**
     * Test converting miles to kilometres.
     */
    public function testConvertMilesToKilometres(): void
    {
        $length = new Length(1, 'mi');
        $km = $length->to('km');

        // 1 mile = 1.609344 km (exactly)
        $this->assertSame(1.609344, $km->value);
    }

    /**
     * Test converting kilograms to pounds.
     */
    public function testConvertKilogramsToPounds(): void
    {
        $mass = new Mass(1, 'kg');
        $lb = $mass->to('lb');

        // 1 kg = 2.20462262... pounds
        $this->assertTrue(Floats::approxEqual(1 / 0.45359237, $lb->value));
    }

    /**
     * Test converting pounds to kilograms.
     */
    public function testConvertPoundsToKilograms(): void
    {
        $mass = new Mass(1, 'lb');
        $kg = $mass->to('kg');

        // 1 pound = 0.45359237 kg (exactly)
        $this->assertSame(0.45359237, $kg->value);
    }

    // endregion

    // region Static convert method tests

    /**
     * Test static convert method on Length.
     */
    public function testStaticConvertMethod(): void
    {
        $value = Length::convert(1000, 'm', 'km');

        $this->assertSame(1.0, $value);
    }

    /**
     * Test static convert method on Quantity.
     */
    public function testGenericStaticConvertMethod(): void
    {
        $value = Quantity::convert(60, 'min', 'h');

        $this->assertSame(1.0, $value);
    }

    // endregion

    // region Chained conversion tests

    /**
     * Test chained conversions.
     */
    public function testChainedConversions(): void
    {
        $length = new Length(1, 'km');

        // km -> m -> cm -> mm
        $mm = $length->to('m')->to('cm')->to('mm');

        $this->assertSame(1000000.0, $mm->value);
    }

    // endregion

    // region Converting to same unit tests

    /**
     * Test converting to the same unit returns equivalent value.
     */
    public function testConvertToSameUnit(): void
    {
        $length = new Length(5, 'm');
        $same = $length->to('m');

        $this->assertSame(5.0, $same->value);
        $this->assertSame('m', $same->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Temperature conversion tests

    /**
     * Test converting Celsius to Kelvin.
     */
    public function testConvertCelsiusToKelvin(): void
    {
        $temp = new Temperature(0, 'degC');
        $k = $temp->to('K');

        $this->assertSame(273.15, $k->value);
    }

    /**
     * Test converting Kelvin to Celsius.
     */
    public function testConvertKelvinToCelsius(): void
    {
        $temp = new Temperature(273.15, 'K');
        $c = $temp->to('degC');

        $this->assertSame(0.0, $c->value);
    }

    /**
     * Test converting Fahrenheit to Celsius.
     */
    public function testConvertFahrenheitToCelsius(): void
    {
        $temp = new Temperature(32, 'degF');
        $c = $temp->to('degC');

        $this->assertTrue(Floats::approxEqual(0.0, $c->value));
    }

    /**
     * Test converting Celsius to Fahrenheit.
     */
    public function testConvertCelsiusToFahrenheit(): void
    {
        $temp = new Temperature(100, 'degC');
        $f = $temp->to('degF');

        $this->assertTrue(Floats::approxEqual(212.0, $f->value));
    }

    /**
     * Test temperature where Celsius equals Fahrenheit.
     */
    public function testTemperatureWhereCelsiusEqualsFahrenheit(): void
    {
        // -40 is where C and F are equal
        $c = new Temperature(-40, 'degC');
        $f = $c->to('degF');

        $this->assertTrue(Floats::approxEqual(-40.0, $f->value));
    }

    // endregion

    // region Zero value conversion tests

    /**
     * Test converting zero values.
     */
    public function testConvertZeroValue(): void
    {
        $length = new Length(0, 'm');
        $km = $length->to('km');

        $this->assertSame(0.0, $km->value);
    }

    // endregion

    // region Negative value conversion tests

    /**
     * Test converting negative values.
     */
    public function testConvertNegativeValue(): void
    {
        $temp = new Temperature(-10, 'degC');
        $k = $temp->to('K');

        $this->assertSame(263.15, $k->value);
    }

    // endregion
}
