<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\QuantityType;

use Galaxon\Core\Traits\FloatAssertions;
use Galaxon\Quantities\QuantityType\Acceleration;
use Galaxon\Quantities\QuantityType\Area;
use Galaxon\Quantities\QuantityType\Density;
use Galaxon\Quantities\QuantityType\Force;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Pressure;
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Pressure quantity type.
 */
#[CoversClass(Pressure::class)]
final class PressureTest extends TestCase
{
    use FloatAssertions;

    // region Setup

    public static function setUpBeforeClass(): void
    {
        // Load Scientific and US units.
        UnitRegistry::loadSystem(System::Scientific);
        UnitRegistry::loadSystem(System::US);
        UnitRegistry::loadSystem(System::Imperial);
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
        $si = $pressure->toSi();

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
        $si = $pressure->toSi();

        $this->assertSame(101325.0, $si->value);
        $this->assertSame('kg/(m*s2)', $si->derivedUnit->asciiSymbol);
    }

    // endregion

    // region P = F/A tests (Pressure = Force / Area)

    /**
     * Test calculating pressure from force and area (SI units).
     */
    public function testPressureFromForceAndAreaSI(): void
    {
        $force = new Force(1000, 'N');
        $area = new Area(2, 'm2');
        $result = $force->div($area);

        // P = F/A = 1000 N / 2 m² = 500 Pa
        $this->assertInstanceOf(Pressure::class, $result);

        $pa = $result->to('Pa');
        $this->assertSame(500.0, $pa->value);
    }

    /**
     * Test calculating pressure from kilonewtons and square metres.
     */
    public function testPressureFromKilonewtonsAndSquareMetres(): void
    {
        $force = new Force(10, 'kN');
        $area = new Area(0.5, 'm2');
        $result = $force->div($area);

        // P = 10 kN / 0.5 m² = 20 kPa
        $kpa = $result->to('kPa');
        $this->assertSame(20.0, $kpa->value);
    }

    /**
     * Test calculating pressure from newtons and square centimetres.
     */
    public function testPressureFromNewtonsAndSquareCentimetres(): void
    {
        $force = new Force(100, 'N');
        $area = new Area(10, 'cm2');
        $result = $force->div($area);

        // P = 100 N / 10 cm² = 100 N / 0.001 m² = 100000 Pa = 100 kPa
        $kpa = $result->to('kPa');
        $this->assertSame(100.0, $kpa->value);
    }

    /**
     * Test calculating pressure in imperial units (lbf/in²).
     */
    public function testPressureFromPoundForceAndSquareInches(): void
    {
        $force = new Force(100, 'lbf');
        $area = new Area(10, 'in2');
        $result = $force->div($area);

        // P = 100 lbf / 10 in² = 10 lbf/in² = 10 psi
        $psi = $result->to('lbf/in2');
        $this->assertSame(10.0, $psi->value);
    }

    /**
     * Test hydraulic press calculation.
     *
     * A hydraulic press applies 5000 N over a piston area of 0.01 m².
     */
    public function testHydraulicPressPressure(): void
    {
        $force = new Force(5000, 'N');
        $area = new Area(0.01, 'm2');
        $result = $force->div($area);

        // P = 5000 N / 0.01 m² = 500000 Pa = 500 kPa
        $this->assertInstanceOf(Pressure::class, $result);
        $kpa = $result->to('kPa');
        $this->assertSame(500.0, $kpa->value);

        // Also verify in psi
        $psi = $result->to('lbf/in2');
        $this->assertApproxEqual(72.52, $psi->value, absTol: 1e-2);
    }

    /**
     * Test stiletto heel pressure calculation.
     *
     * A 60 kg person standing on one stiletto heel (area ≈ 1 cm²).
     */
    public function testStilettoHeelPressure(): void
    {
        // Weight = mass × gravity = 60 kg × 9.80665 m/s² ≈ 588.4 N
        $force = new Force(588.4, 'N');
        $area = new Area(1, 'cm2');
        $result = $force->div($area);

        // P = 588.4 N / 1 cm² = 588.4 N / 0.0001 m² = 5884000 Pa ≈ 5.88 MPa
        $mpa = $result->to('MPa');
        $this->assertApproxEqual(5.884, $mpa->value, absTol: 1e-3);
    }

    /**
     * Test car tire contact patch pressure.
     *
     * A 1500 kg car with 4 tires, each with contact patch of 150 cm².
     */
    public function testCarTireContactPatchPressure(): void
    {
        // Total weight = 1500 kg × 9.80665 m/s² ≈ 14710 N
        // Weight per tire = 14710 / 4 = 3677.5 N
        $force = new Force(3677.5, 'N');
        $area = new Area(150, 'cm2');
        $result = $force->div($area);

        // P = 3677.5 N / 150 cm² = 3677.5 N / 0.015 m² ≈ 245.2 kPa
        $kpa = $result->to('kPa');
        $this->assertApproxEqual(245.2, $kpa->value, absTol: 1e-1);

        // Typical tire pressure ≈ 35 psi
        $psi = $result->to('lbf/in2');
        $this->assertApproxEqual(35.6, $psi->value, absTol: 1e-1);
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
        // At 10 metres depth, pressure increases by ~1 atm
        // Total pressure at 10m ≈ 2 atm
        $pressure = new Pressure(2, 'atm');
        $kpa = $pressure->to('kPa');

        $this->assertSame(202.65, $kpa->value);
    }

    /**
     * Test free diver ear pressure calculation.
     *
     * Water pressure increases with depth: P = ρgh
     * where ρ = water density (1000 kg/m³), g = gravity, h = depth.
     *
     * At 30 metres (a common recreational free diving depth),
     * the water pressure alone is about 3 atm, plus 1 atm at surface = 4 atm total.
     */
    public function testFreeDiverEarPressure(): void
    {
        // Water density: ρ = 1000 kg/m³
        $density = new Density(1000, 'kg/m3');

        // Gravitational acceleration: g = 9.80665 m/s²
        $gravity = new Acceleration(9.80665, 'm/s2');

        // Depth: h = 30 m
        $depth = new Length(30, 'm');

        // Water pressure: P = ρgh
        $waterPressure = $density->mul($gravity)->mul($depth);

        // Convert to atmospheres
        $waterPressureAtm = $waterPressure->to('atm');

        // Water pressure at 30m ≈ 2.9 atm
        $this->assertApproxEqual(2.9, $waterPressureAtm->value, absTol: 1e-2);

        // Total pressure on ear = water pressure + atmospheric pressure
        $atmosphericPressure = new Pressure(1, 'atm');
        $totalPressure = $waterPressure->to('Pa')->add($atmosphericPressure);

        // Total ≈ 3.9 atm (about 4× surface pressure)
        $totalAtm = $totalPressure->to('atm');
        $this->assertApproxEqual(3.9, $totalAtm->value, absTol: 1e-2);

        // This is why free divers must equalize their ears frequently!
        // The pressure difference can cause barotrauma if not equalized.
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
