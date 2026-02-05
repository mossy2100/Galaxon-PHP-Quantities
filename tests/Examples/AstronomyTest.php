<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Examples;

use Galaxon\Core\Traits\FloatAssertions;
use Galaxon\Quantities\PhysicalConstant;
use Galaxon\Quantities\QuantityType\Acceleration;
use Galaxon\Quantities\QuantityType\Force;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\QuantityType\Time;
use PHPUnit\Framework\TestCase;

/**
 * Astronomy examples demonstrating gravitational and light-travel calculations.
 */
class AstronomyTest extends TestCase
{
    use FloatAssertions;

    /**
     * Light travel time: t = d/c.
     *
     * How long sunlight takes to reach Earth (1 AU ≈ 149,597,870,700 m).
     */
    public function testLightTravelTimeSunToEarth(): void
    {
        $d = new Length(149597870700, 'm');
        $c = PhysicalConstant::get('c');
        $t = $d->div($c);

        $this->assertInstanceOf(Time::class, $t);
        // 149,597,870,700 / 299,792,458 ≈ 499.0 s ≈ 8.317 minutes
        $this->assertApproxEqual(499.005, $t->value, 1e-3);
    }

    /**
     * Gravitational force: F = GMm/r².
     *
     * Force between the Sun and Earth.
     */
    public function testGravitationalForceSunEarth(): void
    {
        $gravConst = PhysicalConstant::get('G');
        $sunMass = new Mass(1.989e30, 'kg');
        $earthMass = new Mass(5.972e24, 'kg');
        $distance = new Length(1.496e11, 'm');

        $force = $gravConst->mul($sunMass)->mul($earthMass)->div($distance->pow(2));

        $this->assertInstanceOf(Force::class, $force);
        // ≈ 3.54 × 10²² N
        $this->assertApproxEqual(3.541e22, $force->value, 1e-2);
        $this->assertSame('kg*m/s2', $force->derivedUnit->asciiSymbol);
    }

    /**
     * Surface gravity: g = GM/r².
     *
     * Surface gravity of Mars (M = 6.417 × 10²³ kg, r = 3,389.5 km).
     */
    public function testSurfaceGravityOfMars(): void
    {
        $gravConst = PhysicalConstant::get('G');
        $marsMass = new Mass(6.417e23, 'kg');
        $marsRadius = new Length(3.3895e6, 'm');

        $g = $gravConst->mul($marsMass)->div($marsRadius->pow(2));

        $this->assertInstanceOf(Acceleration::class, $g);
        // ≈ 3.73 m/s² (about 38% of Earth's gravity)
        $this->assertApproxEqual(3.727, $g->value, 1e-2);
    }
}
