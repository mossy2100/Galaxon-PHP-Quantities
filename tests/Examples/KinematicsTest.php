<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Examples;

use Galaxon\Core\Traits\FloatAssertions;
use Galaxon\Quantities\PhysicalConstant;
use Galaxon\Quantities\QuantityType\Acceleration;
use Galaxon\Quantities\QuantityType\Energy;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\QuantityType\Time;
use Galaxon\Quantities\QuantityType\Velocity;
use PHPUnit\Framework\TestCase;

/**
 * Kinematics examples demonstrating motion equations using the Quantities package.
 */
class KinematicsTest extends TestCase
{
    use FloatAssertions;

    /**
     * Velocity from distance and time: v = d/t.
     *
     * Usain Bolt's 100 m world record: 9.58 seconds.
     */
    public function testVelocityFromDistanceAndTime(): void
    {
        $d = new Length(100, 'm');
        $t = new Time(9.58, 's');
        $v = $d->div($t);

        $this->assertInstanceOf(Velocity::class, $v);
        $this->assertApproxEqual(10.438413361169, $v->value);
    }

    /**
     * Acceleration from velocity change and time: a = Δv/Δt.
     *
     * Car accelerating from 0 to 30 m/s in 10 seconds.
     */
    public function testAcceleration(): void
    {
        $deltaV = new Velocity(30, 'm/s');
        $deltaT = new Time(10, 's');
        $a = $deltaV->div($deltaT);

        $this->assertInstanceOf(Acceleration::class, $a);
        $this->assertApproxEqual(3.0, $a->value);
    }

    /**
     * Distance under constant acceleration: d = ½at².
     *
     * Object accelerating at 2 m/s² for 10 seconds.
     */
    public function testDistanceUnderConstantAcceleration(): void
    {
        $a = new Acceleration(2, 'm/s2');
        $t = new Time(10, 's');
        $d = $a->mul($t->pow(2))->mul(0.5);

        $this->assertInstanceOf(Length::class, $d);
        $this->assertApproxEqual(100.0, $d->value);
    }

    /**
     * Free fall distance: d = ½gt².
     *
     * Object falling for 3 seconds under standard gravity.
     */
    public function testFreeFallDistance(): void
    {
        $g = PhysicalConstant::earthGravity();
        $t = new Time(3, 's');
        $d = $g->mul($t->pow(2))->mul(0.5);

        $this->assertInstanceOf(Length::class, $d);
        // ½ × 9.80665 × 9 = 44.12993 m
        $this->assertApproxEqual(44.129925, $d->value);
    }

    /**
     * Kinetic energy: E = ½mv².
     *
     * A 1000 kg car travelling at 30 m/s (~108 km/h).
     */
    public function testKineticEnergy(): void
    {
        $m = new Mass(1000, 'kg');
        $v = new Velocity(30, 'm/s');
        $energy = $m->mul($v->pow(2))->mul(0.5);

        $this->assertInstanceOf(Energy::class, $energy);
        // ½ × 1000 × 900 = 450,000 J = 450 kJ
        $this->assertApproxEqual(450000.0, $energy->value);
    }
}
