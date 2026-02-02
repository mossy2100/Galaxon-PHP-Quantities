<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\QuantityType;

use DomainException;
use Galaxon\Core\Traits\FloatAssertions;
use Galaxon\Quantities\Helpers\UnitRegistry;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType\Temperature;
use Galaxon\Quantities\System;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Temperature quantity type.
 */
#[CoversClass(Temperature::class)]
final class TemperatureTest extends TestCase
{
    use FloatAssertions;

    // region Setup

    public static function setUpBeforeClass(): void
    {
        // Load Imperial/US units for Fahrenheit and Rankine.
        UnitRegistry::loadSystem(System::Imperial);
        UnitRegistry::loadSystem(System::US);
    }

    // endregion

    // region Constants tests

    /**
     * Test that the Celsius offset constant is correct.
     */
    public function testCelsiusOffsetConstant(): void
    {
        $this->assertSame(273.15, Temperature::CELSIUS_OFFSET);
    }

    /**
     * Test that the Fahrenheit offset constant is correct.
     */
    public function testFahrenheitOffsetConstant(): void
    {
        $this->assertSame(459.67, Temperature::FAHRENHEIT_OFFSET);
    }

    /**
     * Test that the Rankine per Kelvin constant is correct.
     */
    public function testRankinePerKelvinConstant(): void
    {
        $this->assertSame(1.8, Temperature::RANKINE_PER_KELVIN);
    }

    // endregion

    // region Celsius to other units tests

    /**
     * Test converting Celsius to Kelvin at freezing point.
     */
    public function testConvertCelsiusToKelvinFreezing(): void
    {
        $temp = new Temperature(0, 'degC');
        $k = $temp->to('K');

        $this->assertInstanceOf(Temperature::class, $k);
        $this->assertSame(273.15, $k->value);
        $this->assertSame('K', $k->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting Celsius to Kelvin at boiling point.
     */
    public function testConvertCelsiusToKelvinBoiling(): void
    {
        $temp = new Temperature(100, 'degC');
        $k = $temp->to('K');

        $this->assertSame(373.15, $k->value);
    }

    /**
     * Test converting Celsius to Fahrenheit at freezing point.
     */
    public function testConvertCelsiusToFahrenheitFreezing(): void
    {
        $temp = new Temperature(0, 'degC');
        $f = $temp->to('degF');

        $this->assertApproxEqual(32.0, $f->value);
    }

    /**
     * Test converting Celsius to Fahrenheit at boiling point.
     */
    public function testConvertCelsiusToFahrenheitBoiling(): void
    {
        $temp = new Temperature(100, 'degC');
        $f = $temp->to('degF');

        $this->assertApproxEqual(212.0, $f->value);
    }

    /**
     * Test converting Celsius to Rankine.
     */
    public function testConvertCelsiusToRankine(): void
    {
        $temp = new Temperature(0, 'degC');
        $r = $temp->to('degR');

        // 0°C = 273.15 K = 273.15 * 1.8 °R = 491.67 °R
        $this->assertApproxEqual(491.67, $r->value);
    }

    // endregion

    // region Kelvin to other units tests

    /**
     * Test converting Kelvin to Celsius.
     */
    public function testConvertKelvinToCelsius(): void
    {
        $temp = new Temperature(273.15, 'K');
        $c = $temp->to('degC');

        $this->assertSame(0.0, $c->value);
        $this->assertSame('degC', $c->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting Kelvin to Fahrenheit.
     */
    public function testConvertKelvinToFahrenheit(): void
    {
        $temp = new Temperature(273.15, 'K');
        $f = $temp->to('degF');

        $this->assertApproxEqual(32.0, $f->value);
    }

    /**
     * Test converting Kelvin to Rankine.
     */
    public function testConvertKelvinToRankine(): void
    {
        $temp = new Temperature(100, 'K');
        $r = $temp->to('degR');

        // 100 K = 180 °R
        $this->assertSame(180.0, $r->value);
    }

    // endregion

    // region Fahrenheit to other units tests

    /**
     * Test converting Fahrenheit to Celsius at freezing point.
     */
    public function testConvertFahrenheitToCelsiusFreezing(): void
    {
        $temp = new Temperature(32, 'degF');
        $c = $temp->to('degC');

        $this->assertApproxEqual(0.0, $c->value);
    }

    /**
     * Test converting Fahrenheit to Celsius at boiling point.
     */
    public function testConvertFahrenheitToCelsiusBoiling(): void
    {
        $temp = new Temperature(212, 'degF');
        $c = $temp->to('degC');

        $this->assertApproxEqual(100.0, $c->value);
    }

    /**
     * Test converting Fahrenheit to Kelvin.
     */
    public function testConvertFahrenheitToKelvin(): void
    {
        $temp = new Temperature(32, 'degF');
        $k = $temp->to('K');

        $this->assertApproxEqual(273.15, $k->value);
    }

    /**
     * Test converting Fahrenheit to Rankine.
     */
    public function testConvertFahrenheitToRankine(): void
    {
        $temp = new Temperature(0, 'degF');
        $r = $temp->to('degR');

        $this->assertSame(459.67, $r->value);
    }

    // endregion

    // region Rankine to other units tests

    /**
     * Test converting Rankine to Kelvin.
     */
    public function testConvertRankineToKelvin(): void
    {
        $temp = new Temperature(180, 'degR');
        $k = $temp->to('K');

        // 180 °R = 100 K
        $this->assertSame(100.0, $k->value);
    }

    /**
     * Test converting Rankine to Celsius.
     */
    public function testConvertRankineToCelsius(): void
    {
        $temp = new Temperature(491.67, 'degR');
        $c = $temp->to('degC');

        $this->assertApproxEqual(0.0, $c->value);
    }

    /**
     * Test converting Rankine to Fahrenheit.
     */
    public function testConvertRankineToFahrenheit(): void
    {
        $temp = new Temperature(459.67, 'degR');
        $f = $temp->to('degF');

        $this->assertApproxEqual(0.0, $f->value);
    }

    // endregion

    // region Special cases tests

    /**
     * Test temperature where Celsius equals Fahrenheit (-40).
     */
    public function testCelsiusEqualsFahrenheit(): void
    {
        $c = new Temperature(-40, 'degC');
        $f = $c->to('degF');

        $this->assertApproxEqual(-40.0, $f->value);
    }

    /**
     * Test absolute zero in Kelvin.
     */
    public function testAbsoluteZeroKelvin(): void
    {
        $temp = new Temperature(0, 'K');
        $c = $temp->to('degC');
        $f = $temp->to('degF');
        $r = $temp->to('degR');

        $this->assertSame(-273.15, $c->value);
        $this->assertApproxEqual(-459.67, $f->value);
        $this->assertSame(0.0, $r->value);
    }

    /**
     * Test absolute zero in Rankine.
     */
    public function testAbsoluteZeroRankine(): void
    {
        $temp = new Temperature(0, 'degR');
        $k = $temp->to('K');
        $c = $temp->to('degC');
        $f = $temp->to('degF');

        $this->assertSame(0.0, $k->value);
        $this->assertSame(-273.15, $c->value);
        $this->assertApproxEqual(-459.67, $f->value);
    }

    /**
     * Test converting to same unit returns same value.
     */
    public function testConvertToSameUnit(): void
    {
        $temp = new Temperature(25, 'degC');
        $same = $temp->to('degC');

        $this->assertSame(25.0, $same->value);
    }

    /**
     * Test negative Celsius to Kelvin.
     */
    public function testNegativeCelsiusToKelvin(): void
    {
        $temp = new Temperature(-10, 'degC');
        $k = $temp->to('K');

        $this->assertSame(263.15, $k->value);
    }

    // endregion

    // region Static convert method tests

    /**
     * Test static convert method Celsius to Kelvin.
     */
    public function testStaticConvertCelsiusToKelvin(): void
    {
        $value = Temperature::convert(100, 'degC', 'K');

        $this->assertSame(373.15, $value);
    }

    /**
     * Test static convert method Fahrenheit to Celsius.
     */
    public function testStaticConvertFahrenheitToCelsius(): void
    {
        $value = Temperature::convert(212, 'degF', 'degC');

        $this->assertApproxEqual(100.0, $value);
    }

    /**
     * Test static convert with same units.
     */
    public function testStaticConvertSameUnit(): void
    {
        $value = Temperature::convert(25, 'degC', 'degC');

        $this->assertSame(25.0, $value);
    }

    // endregion

    // region Error handling tests

    /**
     * Test convert throws for invalid source unit.
     */
    public function testConvertThrowsForInvalidSourceUnit(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid temperature unit: 'm'");

        Temperature::convert(100, 'm', 'K');
    }

    /**
     * Test convert throws for invalid destination unit.
     */
    public function testConvertThrowsForInvalidDestinationUnit(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid temperature unit: 'm'");

        Temperature::convert(100, 'K', 'm');
    }

    // endregion

    // region Parse tests

    /**
     * Test parsing Celsius temperature.
     */
    public function testParseCelsius(): void
    {
        $temp = Temperature::parse('25 degC');

        $this->assertInstanceOf(Temperature::class, $temp);
        $this->assertSame(25.0, $temp->value);
        $this->assertSame('degC', $temp->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing Kelvin temperature.
     */
    public function testParseKelvin(): void
    {
        $temp = Temperature::parse('300 K');

        $this->assertSame(300.0, $temp->value);
        $this->assertSame('K', $temp->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing Fahrenheit temperature.
     */
    public function testParseFahrenheit(): void
    {
        $temp = Temperature::parse('98.6 degF');

        $this->assertSame(98.6, $temp->value);
        $this->assertSame('degF', $temp->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing negative temperature.
     */
    public function testParseNegative(): void
    {
        $temp = Temperature::parse('-40 degC');

        $this->assertSame(-40.0, $temp->value);
    }

    /**
     * Test parsing with prefixed Kelvin (millikelvin).
     */
    public function testParsePrefixedKelvin(): void
    {
        $temp = Temperature::parse('100 mK');

        $this->assertSame(100.0, $temp->value);
        $this->assertSame('mK', $temp->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Prefixed Kelvin tests

    /**
     * Test converting millikelvin to Kelvin.
     */
    public function testConvertMillikelvinToKelvin(): void
    {
        $temp = new Temperature(1000, 'mK');
        $k = $temp->to('K');

        $this->assertSame(1.0, $k->value);
    }

    /**
     * Test converting Kelvin to millikelvin.
     */
    public function testConvertKelvinToMillikelvin(): void
    {
        $temp = new Temperature(1, 'K');
        $mk = $temp->to('mK');

        $this->assertSame(1000.0, $mk->value);
    }

    /**
     * Test converting millikelvin to Celsius.
     */
    public function testConvertMillikelvinToCelsius(): void
    {
        $temp = new Temperature(273150, 'mK');
        $c = $temp->to('degC');

        $this->assertApproxEqual(0.0, $c->value, absTol: 1e-9);
    }

    /**
     * Test converting Celsius to millikelvin.
     */
    public function testConvertCelsiusToMillikelvin(): void
    {
        $temp = new Temperature(0, 'degC');
        $mk = $temp->to('mK');

        $this->assertSame(273150.0, $mk->value);
    }

    /**
     * Test converting microkelvin to Kelvin.
     */
    public function testConvertMicrokelvinToKelvin(): void
    {
        $temp = new Temperature(1000000, 'μK');
        $k = $temp->to('K');

        $this->assertSame(1.0, $k->value);
    }

    // endregion

    // region Round-trip tests

    /**
     * Test Celsius to Fahrenheit to Celsius round-trip.
     */
    public function testCelsiusFahrenheitRoundTrip(): void
    {
        $original = new Temperature(37, 'degC');
        $roundTrip = $original->to('degF')->to('degC');

        $this->assertApproxEqual(37.0, $roundTrip->value);
    }

    /**
     * Test all four units round-trip.
     */
    public function testAllUnitsRoundTrip(): void
    {
        $original = new Temperature(300, 'K');
        $roundTrip = $original->to('degC')->to('degF')->to('degR')->to('K');

        $this->assertApproxEqual(300.0, $roundTrip->value);
    }

    // endregion

    // region Derived unit tests

    /**
     * Test that temperature offset is not applied to derived units.
     *
     * Entropy (J/°C) converted to SI (J/K) should have the same value,
     * because the offset only applies to absolute temperatures, not rates of change.
     */
    public function testEntropyToSiNoOffset(): void
    {
        $entropy = Quantity::create(100, 'J/degC');
        $si = $entropy->toSi();

        // Value should be unchanged - no offset applied.
        $this->assertSame(100.0, $si->value);
        // Unit should be base SI (kg·m²·s⁻²·K⁻¹).
        $this->assertSame('kg*m2/(s2*K)', $si->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting entropy from J/°C to J/K directly.
     */
    public function testEntropyJoulePerCelsiusToJoulePerKelvin(): void
    {
        $entropy = Quantity::create(100, 'J/degC');
        $converted = $entropy->to('J/K');

        // Value should be unchanged - no offset applied.
        $this->assertSame(100.0, $converted->value);
        // Unit should be J/K (normalized form).
        $this->assertSame('J/K', $converted->derivedUnit->asciiSymbol);
    }

    // endregion
}
