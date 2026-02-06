<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Examples;

use Galaxon\Core\Traits\FloatAssertions;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Time;
use Galaxon\Quantities\QuantityType\Velocity;
use Galaxon\Quantities\QuantityType\Volume;
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;
use PHPUnit\Framework\TestCase;

/**
 * Aviation examples demonstrating calculations with mixed Imperial, Nautical, and SI units.
 */
class AviationTest extends TestCase
{
    use FloatAssertions;

    // region Setup

    public static function setUpBeforeClass(): void
    {
        // Load Imperial/US units for cross-system tests.
        UnitRegistry::loadSystem(System::Imperial);
        UnitRegistry::loadSystem(System::US);
        UnitRegistry::loadSystem(System::Nautical);
    }

    // endregion

    /**
     * Ground speed: v = d/t.
     *
     * An aircraft covers 360 nautical miles in 1.5 hours.
     */
    public function testGroundSpeed(): void
    {
        $d = new Length(360, 'nmi');
        $t = new Time(1.5, 'h');
        $v = $d->div($t);

        $this->assertInstanceOf(Velocity::class, $v);
        // 360 nmi / 1.5 h = 240 kn = 240 × 1852/3600 m/s ≈ 123.467 m/s
        $vSi = $v->toSi();
        $this->assertApproxEqual(123.467, $vSi->value, 1e-3);
    }

    /**
     * Descent time: t = altitude / descent rate.
     *
     * Descending from 35,000 ft at 1,500 ft/min.
     */
    public function testDescentTime(): void
    {
        $altitude = new Length(35000, 'ft');
        $rate = new Velocity(1500, 'ft/min');
        $t = $altitude->div($rate);

        $this->assertInstanceOf(Time::class, $t);
        // 35,000 / 1,500 = 23.333 min = 1400 s
        $tSi = $t->toSi(true, false);
        $this->assertApproxEqual(1400.0, $tSi->value, 1e-3);
    }

    /**
     * Top of descent distance: d = ground speed × descent time.
     *
     * At 450 knots ground speed, descending from 35,000 ft at 1,500 ft/min.
     * Pilots use the "3:1 rule" as a rough approximation: 3 nm per 1,000 ft.
     */
    public function testTopOfDescentDistance(): void
    {
        // Calculate descent time.
        $altitude = new Length(35000, 'ft');
        $descentRate = new Velocity(1500, 'ft/min');
        $descentTime = $altitude->div($descentRate);

        // Calculate distance covered during descent.
        $groundSpeed = new Velocity(450, 'kn');
        $distance = $groundSpeed->mul($descentTime);

        $this->assertInstanceOf(Length::class, $distance);
        // 450 kn × 23.333 min → to SI: 231.5 m/s × 1400 s = 324,100 m
        $distanceSi = $distance->toSi(true, false);
        $this->assertApproxEqual(324100, $distanceSi->value, 1e-2);
    }

    /**
     * Fuel burn: total fuel = fuel flow × time.
     *
     * An aircraft burns 850 US gallons per hour on a 3.5 hour flight.
     */
    public function testFuelBurn(): void
    {
        $fuelFlow = new Volume(850, 'US gal')->div(new Time(1, 'h'));
        $flightTime = new Time(3.5, 'h');
        $totalFuel = $fuelFlow->mul($flightTime);

        $this->assertInstanceOf(Volume::class, $totalFuel);
        // 850 × 3.5 = 2975 US gal → to SI: 2975 × 0.003785 ≈ 11.26 m³
        $totalFuelSi = $totalFuel->toSi();
        $this->assertApproxEqual(11.26, $totalFuelSi->value, 1e-2);
    }
}
