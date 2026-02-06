<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\QuantityType;

use Galaxon\Core\Traits\FloatAssertions;
use Galaxon\Quantities\QuantityType\Energy;
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Energy quantity type.
 */
#[CoversClass(Energy::class)]
final class EnergyTest extends TestCase
{
    use FloatAssertions;

    // region Setup

    public static function setUpBeforeClass(): void
    {
        // Load US units for BTU.
        UnitRegistry::loadSystem(System::US);
    }

    // endregion

    // region Joule conversion tests

    /**
     * Test converting joules to kilojoules.
     */
    public function testConvertJoulesToKilojoules(): void
    {
        $energy = new Energy(1000, 'J');
        $kj = $energy->to('kJ');

        $this->assertInstanceOf(Energy::class, $kj);
        $this->assertSame(1.0, $kj->value);
        $this->assertSame('kJ', $kj->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting kilojoules to joules.
     */
    public function testConvertKilojoulesToJoules(): void
    {
        $energy = new Energy(5, 'kJ');
        $j = $energy->to('J');

        $this->assertSame(5000.0, $j->value);
    }

    /**
     * Test converting joules to megajoules.
     */
    public function testConvertJoulesToMegajoules(): void
    {
        $energy = new Energy(1000000, 'J');
        $mj = $energy->to('MJ');

        $this->assertSame(1.0, $mj->value);
    }

    /**
     * Test converting joules to millijoules.
     */
    public function testConvertJoulesToMillijoules(): void
    {
        $energy = new Energy(1, 'J');
        $mj = $energy->to('mJ');

        $this->assertSame(1000.0, $mj->value);
    }

    /**
     * Test converting gigajoules to joules.
     */
    public function testConvertGigajoulesToJoules(): void
    {
        $energy = new Energy(1, 'GJ');
        $j = $energy->to('J');

        $this->assertSame(1000000000.0, $j->value);
    }

    // endregion

    // region Calorie conversion tests

    /**
     * Test converting calories to joules.
     */
    public function testConvertCaloriesToJoules(): void
    {
        $energy = new Energy(1, 'cal');
        $j = $energy->to('J');

        $this->assertSame(4.184, $j->value);
    }

    /**
     * Test converting joules to calories.
     */
    public function testConvertJoulesToCalories(): void
    {
        $energy = new Energy(4.184, 'J');
        $cal = $energy->to('cal');

        $this->assertApproxEqual(1.0, $cal->value);
    }

    /**
     * Test converting kilocalories to joules.
     */
    public function testConvertKilocaloriesToJoules(): void
    {
        $energy = new Energy(1, 'kcal');
        $j = $energy->to('J');

        // 1 kcal = 4184 J
        $this->assertSame(4184.0, $j->value);
    }

    /**
     * Test converting kilocalories to kilojoules.
     */
    public function testConvertKilocaloriesToKilojoules(): void
    {
        $energy = new Energy(1, 'kcal');
        $kj = $energy->to('kJ');

        // 1 kcal = 4.184 kJ
        $this->assertSame(4.184, $kj->value);
    }

    /**
     * Test converting kilojoules to kilocalories.
     */
    public function testConvertKilojoulesToKilocalories(): void
    {
        $energy = new Energy(4.184, 'kJ');
        $kcal = $energy->to('kcal');

        $this->assertApproxEqual(1.0, $kcal->value);
    }

    // endregion

    // region BTU conversion tests

    /**
     * Test converting BTU to joules.
     */
    public function testConvertBtuToJoules(): void
    {
        $energy = new Energy(1, 'Btu');
        $j = $energy->to('J');

        $this->assertSame(1055.05585262, $j->value);
    }

    /**
     * Test converting joules to BTU.
     */
    public function testConvertJoulesToBtu(): void
    {
        $energy = new Energy(1055.05585262, 'J');
        $btu = $energy->to('Btu');

        $this->assertApproxEqual(1.0, $btu->value);
    }

    /**
     * Test converting BTU to kilojoules.
     */
    public function testConvertBtuToKilojoules(): void
    {
        $energy = new Energy(1, 'Btu');
        $kj = $energy->to('kJ');

        // 1 Btu = 1.05505585262 kJ
        $this->assertApproxEqual(1.05505585262, $kj->value);
    }

    /**
     * Test converting BTU to kilocalories.
     */
    public function testConvertBtuToKilocalories(): void
    {
        $energy = new Energy(1, 'Btu');
        $kcal = $energy->to('kcal');

        // 1 Btu = 1055.05585262 J / 4184 J/kcal ≈ 0.252164 kcal
        $this->assertApproxEqual(1055.05585262 / 4184, $kcal->value);
    }

    /**
     * Test converting kilocalories to BTU.
     */
    public function testConvertKilocaloriesToBtu(): void
    {
        $energy = new Energy(1, 'kcal');
        $btu = $energy->to('Btu');

        // 1 kcal = 4184 J / 1055.05585262 J/Btu ≈ 3.96567 Btu
        $this->assertApproxEqual(4184 / 1055.05585262, $btu->value);
    }

    // endregion

    // region Electronvolt conversion tests

    /**
     * Test converting electronvolts to joules.
     */
    public function testConvertElectronvoltsToJoules(): void
    {
        $energy = new Energy(1, 'eV');
        $j = $energy->to('J');

        $this->assertSame(1.602176634e-19, $j->value);
    }

    /**
     * Test converting joules to electronvolts.
     */
    public function testConvertJoulesToElectronvolts(): void
    {
        $energy = new Energy(1.602176634e-19, 'J');
        $ev = $energy->to('eV');

        $this->assertApproxEqual(1.0, $ev->value);
    }

    /**
     * Test converting kiloelectronvolts to electronvolts.
     */
    public function testConvertKiloelectronvoltsToElectronvolts(): void
    {
        $energy = new Energy(1, 'keV');
        $ev = $energy->to('eV');

        $this->assertSame(1000.0, $ev->value);
    }

    /**
     * Test converting megaelectronvolts to joules.
     */
    public function testConvertMegaelectronvoltsToJoules(): void
    {
        $energy = new Energy(1, 'MeV');
        $j = $energy->to('J');

        // 1 MeV = 1e6 × 1.602176634e-19 J = 1.602176634e-13 J
        $this->assertApproxEqual(1.602176634e-13, $j->value);
    }

    /**
     * Test converting gigaelectronvolts to megaelectronvolts.
     */
    public function testConvertGigaelectronvoltsToMegaelectronvolts(): void
    {
        $energy = new Energy(1, 'GeV');
        $mev = $energy->to('MeV');

        $this->assertSame(1000.0, $mev->value);
    }

    // endregion

    // region SI base unit conversion tests

    /**
     * Test converting joules to base SI units.
     */
    public function testConvertJoulesToBaseSI(): void
    {
        $energy = new Energy(10, 'J');
        $si = $energy->toSiBase();

        // 10 J = 10 kg·m²·s⁻²
        $this->assertSame(10.0, $si->value);
        $this->assertSame('kg*m2/s2', $si->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting kilocalories to base SI units.
     */
    public function testConvertKilocaloriesToBaseSI(): void
    {
        $energy = new Energy(1, 'kcal');
        $si = $energy->toSiBase();

        // 1 kcal = 4184 kg·m²·s⁻²
        $this->assertSame(4184.0, $si->value);
        $this->assertSame('kg*m2/s2', $si->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Addition tests

    /**
     * Test adding joules to joules.
     */
    public function testAddJoulesToJoules(): void
    {
        $a = new Energy(100, 'J');
        $b = new Energy(50, 'J');
        $result = $a->add($b);

        $this->assertInstanceOf(Energy::class, $result);
        $this->assertSame(150.0, $result->value);
        $this->assertSame('J', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding kilojoules to joules.
     */
    public function testAddKilojoulesToJoules(): void
    {
        $a = new Energy(1000, 'J');
        $b = new Energy(1, 'kJ');
        $result = $a->add($b);

        // 1000 J + 1 kJ = 1000 J + 1000 J = 2000 J
        $this->assertInstanceOf(Energy::class, $result);
        $this->assertSame(2000.0, $result->value);
        $this->assertSame('J', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding calories to joules.
     */
    public function testAddCaloriesToJoules(): void
    {
        $a = new Energy(100, 'J');
        $b = new Energy(10, 'cal');
        $result = $a->add($b);

        // 100 J + 10 cal = 100 J + 41.84 J = 141.84 J
        $this->assertInstanceOf(Energy::class, $result);
        $this->assertApproxEqual(141.84, $result->value);
        $this->assertSame('J', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding kilocalories to kilojoules.
     */
    public function testAddKilocaloriesToKilojoules(): void
    {
        $a = new Energy(10, 'kJ');
        $b = new Energy(1, 'kcal');
        $result = $a->add($b);

        // 10 kJ + 1 kcal = 10 kJ + 4.184 kJ = 14.184 kJ
        $this->assertInstanceOf(Energy::class, $result);
        $this->assertApproxEqual(14.184, $result->value);
        $this->assertSame('kJ', $result->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Parse tests

    /**
     * Test parsing joules.
     */
    public function testParseJoules(): void
    {
        $energy = Energy::parse('500 J');

        $this->assertInstanceOf(Energy::class, $energy);
        $this->assertSame(500.0, $energy->value);
        $this->assertSame('J', $energy->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing kilojoules.
     */
    public function testParseKilojoules(): void
    {
        $energy = Energy::parse('2.5 kJ');

        $this->assertSame(2.5, $energy->value);
        $this->assertSame('kJ', $energy->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing kilocalories.
     */
    public function testParseKilocalories(): void
    {
        $energy = Energy::parse('250 kcal');

        $this->assertSame(250.0, $energy->value);
        $this->assertSame('kcal', $energy->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing electronvolts.
     */
    public function testParseElectronvolts(): void
    {
        $energy = Energy::parse('1.5 MeV');

        $this->assertSame(1.5, $energy->value);
        $this->assertSame('MeV', $energy->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing BTU.
     */
    public function testParseBtu(): void
    {
        $energy = Energy::parse('100000 Btu');

        $this->assertSame(100000.0, $energy->value);
        $this->assertSame('Btu', $energy->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Static convert method tests

    /**
     * Test static convert method.
     */
    public function testStaticConvertMethod(): void
    {
        $value = Energy::convert(1000, 'J', 'kJ');

        $this->assertSame(1.0, $value);
    }

    /**
     * Test static convert calories to joules.
     */
    public function testStaticConvertCaloriesToJoules(): void
    {
        $value = Energy::convert(1, 'cal', 'J');

        $this->assertSame(4.184, $value);
    }

    // endregion

    // endregion

    // region Practical examples

    /**
     * Test food energy conversion (nutrition label): 200 kcal to kJ.
     */
    public function testFoodEnergyConversion(): void
    {
        $energy = new Energy(200, 'kcal');
        $kj = $energy->to('kJ');

        // 200 kcal = 836.8 kJ
        $this->assertApproxEqual(836.8, $kj->value);
    }

    /**
     * Test converting megajoules to kilocalories.
     */
    public function testConvertMegajoulesToKilocalories(): void
    {
        $energy = new Energy(1, 'MJ');
        $kcal = $energy->to('kcal');

        // 1 MJ = 1,000,000 / 4184 kcal ≈ 239.006 kcal
        $this->assertApproxEqual(1000000 / 4184, $kcal->value);
    }

    /**
     * Test zero energy conversion.
     */
    public function testZeroEnergyConversion(): void
    {
        $energy = new Energy(0, 'J');
        $cal = $energy->to('cal');

        $this->assertSame(0.0, $cal->value);
    }

    /**
     * Test natural gas therm conversion (1 therm = 100,000 Btu).
     */
    public function testNaturalGasThermConversion(): void
    {
        $energy = new Energy(100000, 'Btu');
        $mj = $energy->to('MJ');

        // 100,000 Btu = 105.505585262 MJ
        $this->assertApproxEqual(105.505585262, $mj->value);
    }

    // endregion
}
