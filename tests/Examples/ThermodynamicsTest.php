<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Examples;

use Galaxon\Core\Traits\FloatAssertions;
use Galaxon\Quantities\PhysicalConstant;
use Galaxon\Quantities\QuantityType\AmountOfSubstance;
use Galaxon\Quantities\QuantityType\Area;
use Galaxon\Quantities\QuantityType\Energy;
use Galaxon\Quantities\QuantityType\Power;
use Galaxon\Quantities\QuantityType\Pressure;
use Galaxon\Quantities\QuantityType\Temperature;
use Galaxon\Quantities\QuantityType\Volume;
use PHPUnit\Framework\TestCase;

/**
 * Thermodynamics examples demonstrating gas laws and thermal radiation calculations.
 */
class ThermodynamicsTest extends TestCase
{
    use FloatAssertions;

    /**
     * Ideal gas law: PV = nRT, solving for pressure.
     *
     * 1 mol of gas at 300 K in a 25 L (0.025 m³) container.
     */
    public function testIdealGasLaw(): void
    {
        $n = new AmountOfSubstance(1, 'mol');
        $gasConst = PhysicalConstant::molarGas();
        $temp = new Temperature(300, 'K');
        $volume = new Volume(0.025, 'm3');

        $pressure = $n->mul($gasConst)->mul($temp)->div($volume);

        $this->assertInstanceOf(Pressure::class, $pressure);
        // 1 × 8.314462618 × 300 / 0.025 = 99,773.55 Pa ≈ 1 atm
        $this->assertApproxEqual(99773.55142, $pressure->value, 1e-6);
    }

    /**
     * Stefan-Boltzmann law: P = σAT⁴.
     *
     * Total radiant power from 1 m² of the Sun's surface (T = 5778 K).
     */
    public function testStefanBoltzmannRadiation(): void
    {
        $sigma = PhysicalConstant::stefanBoltzmann();
        $area = new Area(1, 'm2');
        $temp = new Temperature(5778, 'K');

        $power = $sigma->mul($area)->mul($temp->pow(4));

        $this->assertInstanceOf(Power::class, $power);
        // σ × 1 × 5778⁴ ≈ 6.32 × 10⁷ W (63.2 MW per m²)
        $this->assertApproxEqual(6.319e7, $power->value, 1e-3);
    }

    /**
     * Average thermal energy of a particle: E = (3/2)kT.
     *
     * Thermal energy at room temperature (300 K).
     */
    public function testThermalEnergy(): void
    {
        $k = PhysicalConstant::boltzmann();
        $temp = new Temperature(300, 'K');

        $energy = $k->mul($temp)->mul(1.5);

        $this->assertInstanceOf(Energy::class, $energy);
        // 1.5 × 1.380649 × 10⁻²³ × 300 = 6.213 × 10⁻²¹ J
        $this->assertApproxEqual(6.21292e-21, $energy->value);
    }
}
