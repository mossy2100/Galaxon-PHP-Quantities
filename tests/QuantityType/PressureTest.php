<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\QuantityType;

use Galaxon\Core\Traits\FloatAssertions;
use Galaxon\Quantities\QuantityType\Pressure;
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;
use Galaxon\Quantities\Tests\Traits\ArrayShapeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Pressure quantity type.
 */
#[CoversClass(Pressure::class)]
final class PressureTest extends TestCase
{
    use ArrayShapeTrait;
    use FloatAssertions;

    // region Setup

    public static function setUpBeforeClass(): void
    {
        // Load Scientific and US units.
        UnitRegistry::loadSystem(System::Scientific);
        UnitRegistry::loadSystem(System::UsCustomary);
        UnitRegistry::loadSystem(System::Imperial);
    }

    // endregion

    // region Overridden methods

    /**
     * Test getUnitDefinitions() returns valid unit definitions.
     */
    public function testGetUnitDefinitionsReturnsValidArray(): void
    {
        $units = Pressure::getUnitDefinitions();

        $this->assertValidUnitDefinitionsShape($units);
    }

    /**
     * Test getConversionDefinitions() returns valid conversion definitions.
     */
    public function testGetConversionDefinitionsReturnsValidArray(): void
    {
        $conversions = Pressure::getConversionDefinitions();

        $this->assertValidConversionDefinitionsShape($conversions);
    }

    // endregion

    // region Metric conversion tests

    /**
     * Test converting pascals to kilopascals.
     */
    public function testConvertPascalsToKilopascals(): void
    {
        $pressure = new Pressure(1000, 'Pa');
        $kpa = $pressure->to('kPa');

        $this->assertInstanceOf(Pressure::class, $kpa);
        $this->assertSame(1.0, $kpa->value);
        $this->assertSame('kPa', $kpa->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting kilopascals to pascals.
     */
    public function testConvertKilopascalsToPascals(): void
    {
        $pressure = new Pressure(101.325, 'kPa');
        $pa = $pressure->to('Pa');

        $this->assertSame(101325.0, $pa->value);
    }

    /**
     * Test converting pascals to hectopascals.
     */
    public function testConvertPascalsToHectopascals(): void
    {
        $pressure = new Pressure(1013.25, 'Pa');
        $hpa = $pressure->to('hPa');

        $this->assertSame(10.1325, $hpa->value);
    }

    /**
     * Test converting hectopascals to pascals.
     */
    public function testConvertHectopascalsToPascals(): void
    {
        $pressure = new Pressure(1013.25, 'hPa');
        $pa = $pressure->to('Pa');

        $this->assertSame(101325.0, $pa->value);
    }

    /**
     * Test converting pascals to megapascals.
     */
    public function testConvertPascalsToMegapascals(): void
    {
        $pressure = new Pressure(1000000, 'Pa');
        $mpa = $pressure->to('MPa');

        $this->assertSame(1.0, $mpa->value);
    }

    /**
     * Test converting megapascals to kilopascals.
     */
    public function testConvertMegapascalsToKilopascals(): void
    {
        $pressure = new Pressure(1, 'MPa');
        $kpa = $pressure->to('kPa');

        $this->assertSame(1000.0, $kpa->value);
    }

    // endregion

    // region Atmosphere conversion tests

    /**
     * Test converting atmospheres to pascals.
     */
    public function testConvertAtmospheresToPascals(): void
    {
        $pressure = new Pressure(1, 'atm');
        $pa = $pressure->to('Pa');

        $this->assertSame(101325.0, $pa->value);
    }

    /**
     * Test converting pascals to atmospheres.
     */
    public function testConvertPascalsToAtmospheres(): void
    {
        $pressure = new Pressure(101325, 'Pa');
        $atm = $pressure->to('atm');

        $this->assertSame(1.0, $atm->value);
    }

    /**
     * Test converting atmospheres to kilopascals.
     */
    public function testConvertAtmospheresToKilopascals(): void
    {
        $pressure = new Pressure(1, 'atm');
        $kpa = $pressure->to('kPa');

        $this->assertSame(101.325, $kpa->value);
    }

    /**
     * Test converting kilopascals to atmospheres.
     */
    public function testConvertKilopascalsToAtmospheres(): void
    {
        $pressure = new Pressure(202.65, 'kPa');
        $atm = $pressure->to('atm');

        $this->assertSame(2.0, $atm->value);
    }

    // endregion

    // region Mercury column conversion tests

    /**
     * Test converting mmHg to pascals.
     */
    public function testConvertMmHgToPascals(): void
    {
        $pressure = new Pressure(1, 'mmHg');
        $pa = $pressure->to('Pa');

        $this->assertApproxEqual(133.322387415, $pa->value);
    }

    /**
     * Test converting pascals to mmHg.
     */
    public function testConvertPascalsToMmHg(): void
    {
        $pressure = new Pressure(133.322387415, 'Pa');
        $mmhg = $pressure->to('mmHg');

        $this->assertApproxEqual(1.0, $mmhg->value);
    }

    /**
     * Test converting mmHg to atmospheres.
     */
    public function testConvertMmHgToAtmospheres(): void
    {
        $pressure = new Pressure(760, 'mmHg');
        $atm = $pressure->to('atm');

        // 760 mmHg ≈ 1 atm (standard atmosphere)
        $this->assertApproxEqual(1.0, $atm->value, absTol: 1e-4);
    }

    /**
     * Test converting atmospheres to mmHg.
     */
    public function testConvertAtmospheresToMmHg(): void
    {
        $pressure = new Pressure(1, 'atm');
        $mmhg = $pressure->to('mmHg');

        // 1 atm ≈ 760 mmHg
        $this->assertApproxEqual(760.0, $mmhg->value, absTol: 1e-2);
    }

    /**
     * Test converting inHg to mmHg.
     */
    public function testConvertInHgToMmHg(): void
    {
        $pressure = new Pressure(1, 'inHg');
        $mmhg = $pressure->to('mmHg');

        $this->assertSame(25.4, $mmhg->value);
    }

    /**
     * Test converting mmHg to inHg.
     */
    public function testConvertMmHgToInHg(): void
    {
        $pressure = new Pressure(25.4, 'mmHg');
        $inhg = $pressure->to('inHg');

        $this->assertApproxEqual(1.0, $inhg->value);
    }

    /**
     * Test converting inHg to pascals.
     */
    public function testConvertInHgToPascals(): void
    {
        $pressure = new Pressure(1, 'inHg');
        $pa = $pressure->to('Pa');

        // 1 inHg = 25.4 mmHg × 133.322387415 Pa/mmHg
        $this->assertApproxEqual(25.4 * 133.322387415, $pa->value);
    }

    /**
     * Test converting inHg to kilopascals.
     */
    public function testConvertInHgToKilopascals(): void
    {
        $pressure = new Pressure(29.92, 'inHg');
        $kpa = $pressure->to('kPa');

        // 29.92 inHg ≈ 101.325 kPa (standard atmosphere)
        $this->assertApproxEqual(101.325, $kpa->value, absTol: 1e-2);
    }

    // endregion

    // region Pounds per square inch (psi) conversion tests

    /**
     * Test converting lbf/in² to pascals.
     */
    public function testConvertPsiToPascals(): void
    {
        $pressure = new Pressure(1, 'lbf/in2');
        $pa = $pressure->to('Pa');

        // 1 psi = 1 lbf/in² ≈ 6894.76 Pa
        // 1 lbf = 4.4482216152605 N
        // 1 in² = 0.00064516 m²
        // 1 psi = 4.4482216152605 / 0.00064516 ≈ 6894.76 Pa
        $expected = 4.4482216152605 / 0.00064516;
        $this->assertApproxEqual($expected, $pa->value);
    }

    /**
     * Test converting pascals to lbf/in².
     */
    public function testConvertPascalsToPsi(): void
    {
        $pressure = new Pressure(6894.76, 'Pa');
        $psi = $pressure->to('lbf/in2');

        // 6894.76 Pa ≈ 1 psi
        $this->assertApproxEqual(1.0, $psi->value, absTol: 1e-4);
    }

    /**
     * Test converting atmospheres to lbf/in².
     */
    public function testConvertAtmospheresToPsi(): void
    {
        $pressure = new Pressure(1, 'atm');
        $psi = $pressure->to('lbf/in2');

        // 1 atm ≈ 14.696 psi
        $this->assertApproxEqual(14.696, $psi->value, absTol: 1e-3);
    }

    /**
     * Test converting lbf/in² to atmospheres.
     */
    public function testConvertPsiToAtmospheres(): void
    {
        $pressure = new Pressure(14.696, 'lbf/in2');
        $atm = $pressure->to('atm');

        // 14.696 psi ≈ 1 atm
        $this->assertApproxEqual(1.0, $atm->value, absTol: 1e-3);
    }

    /**
     * Test converting kilopascals to lbf/in².
     */
    public function testConvertKilopascalsToPsi(): void
    {
        $pressure = new Pressure(100, 'kPa');
        $psi = $pressure->to('lbf/in2');

        // 100 kPa ≈ 14.504 psi
        $this->assertApproxEqual(14.504, $psi->value, absTol: 1e-3);
    }

    /**
     * Test converting lbf/in² to kilopascals.
     */
    public function testConvertPsiToKilopascals(): void
    {
        $pressure = new Pressure(30, 'lbf/in2');
        $kpa = $pressure->to('kPa');

        // 30 psi ≈ 206.84 kPa
        $this->assertApproxEqual(206.84, $kpa->value, absTol: 1e-2);
    }

    // endregion

    // region SI base unit conversion tests

    /**
     * Test converting pascals to base SI units.
     */
    public function testConvertPascalsToBaseSI(): void
    {
        $pressure = new Pressure(1000, 'Pa');
        $si = $pressure->toSiBase();

        // Pa = kg·m⁻¹·s⁻²
        $this->assertSame(1000.0, $si->value);
        $this->assertSame('kg/(m*s2)', $si->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting atmospheres to base SI units.
     */
    public function testConvertAtmospheresToBaseSI(): void
    {
        $pressure = new Pressure(1, 'atm');
        $si = $pressure->toSiBase();

        $this->assertSame(101325.0, $si->value);
        $this->assertSame('kg/(m*s2)', $si->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Addition tests

    /**
     * Test adding pascals to pascals.
     */
    public function testAddPascalsToPascals(): void
    {
        $a = new Pressure(1000, 'Pa');
        $b = new Pressure(500, 'Pa');
        $result = $a->add($b);

        $this->assertInstanceOf(Pressure::class, $result);
        $this->assertSame(1500.0, $result->value);
        $this->assertSame('Pa', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding kilopascals to pascals.
     */
    public function testAddKilopascalsToPascals(): void
    {
        $a = new Pressure(1000, 'Pa');
        $b = new Pressure(1, 'kPa');
        $result = $a->add($b);

        // 1000 Pa + 1 kPa = 1000 Pa + 1000 Pa = 2000 Pa
        $this->assertSame(2000.0, $result->value);
        $this->assertSame('Pa', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding atmospheres to kilopascals.
     */
    public function testAddAtmospheresToKilopascals(): void
    {
        $a = new Pressure(100, 'kPa');
        $b = new Pressure(1, 'atm');
        $result = $a->add($b);

        // 100 kPa + 1 atm = 100 kPa + 101.325 kPa = 201.325 kPa
        $this->assertSame(201.325, $result->value);
        $this->assertSame('kPa', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding mmHg to atmospheres.
     */
    public function testAddMmHgToAtmospheres(): void
    {
        $a = new Pressure(1, 'atm');
        $b = new Pressure(760, 'mmHg');
        $result = $a->add($b);

        // 1 atm + 760 mmHg ≈ 1 atm + 1 atm = 2 atm
        $this->assertApproxEqual(2.0, $result->value, absTol: 1e-4);
        $this->assertSame('atm', $result->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Parse tests

    /**
     * Test parsing pascals.
     */
    public function testParsePascals(): void
    {
        $pressure = Pressure::parse('101325 Pa');

        $this->assertInstanceOf(Pressure::class, $pressure);
        $this->assertSame(101325.0, $pressure->value);
        $this->assertSame('Pa', $pressure->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing kilopascals.
     */
    public function testParseKilopascals(): void
    {
        $pressure = Pressure::parse('101.325 kPa');

        $this->assertSame(101.325, $pressure->value);
        $this->assertSame('kPa', $pressure->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing atmospheres.
     */
    public function testParseAtmospheres(): void
    {
        $pressure = Pressure::parse('2 atm');

        $this->assertSame(2.0, $pressure->value);
        $this->assertSame('atm', $pressure->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing mmHg.
     */
    public function testParseMmHg(): void
    {
        $pressure = Pressure::parse('760 mmHg');

        $this->assertSame(760.0, $pressure->value);
        $this->assertSame('mmHg', $pressure->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing inHg.
     */
    public function testParseInHg(): void
    {
        $pressure = Pressure::parse('29.92 inHg');

        $this->assertSame(29.92, $pressure->value);
        $this->assertSame('inHg', $pressure->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Static convert method tests

    /**
     * Test static convert method.
     */
    public function testStaticConvertMethod(): void
    {
        $value = Pressure::convert(1, 'atm', 'Pa');

        $this->assertSame(101325.0, $value);
    }

    /**
     * Test static convert mmHg to kPa.
     */
    public function testStaticConvertMmHgToKpa(): void
    {
        $value = Pressure::convert(760, 'mmHg', 'kPa');

        // 760 mmHg ≈ 101.325 kPa
        $this->assertApproxEqual(101.325, $value, absTol: 1e-2);
    }

    // endregion

    // region Practical examples

    /**
     * Test standard atmospheric pressure.
     */
    public function testStandardAtmosphericPressure(): void
    {
        $pressure = new Pressure(1, 'atm');

        $this->assertSame(101325.0, $pressure->to('Pa')->value);
        $this->assertSame(101.325, $pressure->to('kPa')->value);
        $this->assertApproxEqual(1013.25, $pressure->to('hPa')->value);
        $this->assertApproxEqual(760.0, $pressure->to('mmHg')->value, absTol: 1e-2);
    }

    /**
     * Test blood pressure reading (systolic).
     */
    public function testBloodPressureSystolic(): void
    {
        // Normal systolic blood pressure ≈ 120 mmHg
        $pressure = new Pressure(120, 'mmHg');
        $kpa = $pressure->to('kPa');

        // 120 mmHg ≈ 16 kPa
        $this->assertApproxEqual(16.0, $kpa->value, absTol: 1e-2);
    }

    /**
     * Test tire pressure conversion.
     */
    public function testTirePressureConversion(): void
    {
        // Typical car tire pressure ≈ 220 kPa
        $pressure = new Pressure(220, 'kPa');

        $atm = $pressure->to('atm');
        // ≈ 2.17 atm
        $this->assertApproxEqual(220 / 101.325, $atm->value);
    }

    /**
     * Test weather barometer reading.
     */
    public function testWeatherBarometerReading(): void
    {
        // Typical barometric pressure: 29.92 inHg (standard atmosphere)
        $pressure = new Pressure(29.92, 'inHg');

        $hpa = $pressure->to('hPa');
        // ≈ 1013.25 hPa
        $this->assertApproxEqual(1013.25, $hpa->value, absTol: 1);
    }

    /**
     * Test scuba diving depth pressure.
     */
    public function testScubaDivingPressure(): void
    {
        // At 10 meters depth, pressure increases by ~1 atm
        // Total pressure at 10m ≈ 2 atm
        $pressure = new Pressure(2, 'atm');
        $kpa = $pressure->to('kPa');

        $this->assertSame(202.65, $kpa->value);
    }

    /**
     * Test zero pressure conversion.
     */
    public function testZeroPressureConversion(): void
    {
        $pressure = new Pressure(0, 'Pa');
        $atm = $pressure->to('atm');

        $this->assertSame(0.0, $atm->value);
    }

    // endregion

    // region Expansion tests

    /**
     * Test expanding pascals to base units.
     */
    public function testExpandPascals(): void
    {
        $pressure = new Pressure(1000, 'Pa');
        $expanded = $pressure->expand();

        // Pa expands to kg·m⁻¹·s⁻²
        $this->assertSame(1000.0, $expanded->value);
        $this->assertSame('kg/(m*s2)', $expanded->derivedUnit->asciiSymbol);
    }

    // endregion
}
