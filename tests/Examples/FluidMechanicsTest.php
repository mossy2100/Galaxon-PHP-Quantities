<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Examples;

use Galaxon\Core\Traits\FloatAssertions;
use Galaxon\Quantities\PhysicalConstant;
use Galaxon\Quantities\QuantityType\Area;
use Galaxon\Quantities\QuantityType\Density;
use Galaxon\Quantities\QuantityType\Force;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Pressure;
use Galaxon\Quantities\QuantityType\Volume;
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;
use PHPUnit\Framework\TestCase;

/**
 * Fluid mechanics examples demonstrating pressure and buoyancy calculations.
 */
class FluidMechanicsTest extends TestCase
{
    use FloatAssertions;

    public static function setUpBeforeClass(): void
    {
        // Load Imperial units for lbf and in².
        UnitRegistry::loadSystem(System::Imperial);
    }

    /**
     * Pressure from force and area: P = F/A.
     *
     * A 1000 N force distributed over 0.5 m².
     */
    public function testPressureFromForceAndArea(): void
    {
        $force = new Force(1000, 'N');
        $area = new Area(0.5, 'm2');
        $pressure = $force->div($area);

        $this->assertInstanceOf(Pressure::class, $pressure);
        $this->assertApproxEqual(2000.0, $pressure->value);
    }

    /**
     * Hydrostatic pressure: P = ρgh.
     *
     * Pressure at 10 metres depth in fresh water (ρ = 1000 kg/m³).
     */
    public function testHydrostaticPressure(): void
    {
        $rho = new Density(1000, 'kg/m3');
        $g = PhysicalConstant::earthGravity();
        $h = new Length(10, 'm');
        $pressure = $rho->mul($g)->mul($h);

        $this->assertInstanceOf(Pressure::class, $pressure);
        // 1000 × 9.80665 × 10 = 98,066.5 Pa
        $this->assertApproxEqual(98066.5, $pressure->value);
    }

    /**
     * Buoyancy force (Archimedes' principle): F = ρVg.
     *
     * A 10 litre (0.01 m³) object submerged in fresh water.
     */
    public function testBuoyancyForce(): void
    {
        $rho = new Density(1000, 'kg/m3');
        $volume = new Volume(0.01, 'm3');
        $g = PhysicalConstant::earthGravity();
        $force = $rho->mul($volume)->mul($g);

        $this->assertInstanceOf(Force::class, $force);
        // 1000 × 0.01 × 9.80665 = 98.0665 N
        $this->assertApproxEqual(98.0665, $force->value);
    }

    /**
     * Pressure in Imperial units: P = F/A.
     *
     * 100 lbf distributed over 10 in².
     */
    public function testPressureFromPoundForceAndSquareInches(): void
    {
        $force = new Force(100, 'lbf');
        $area = new Area(10, 'in2');
        $pressure = $force->div($area);

        $this->assertInstanceOf(Pressure::class, $pressure);
        // 100 lbf / 10 in² = 10 psi
        $psi = $pressure->to('lbf/in2');
        $this->assertSame(10.0, $psi->value);
    }
}
