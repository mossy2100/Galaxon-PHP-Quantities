<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Examples;

use Galaxon\Core\Traits\FloatAssertions;
use Galaxon\Quantities\PhysicalConstant;
use Galaxon\Quantities\QuantityType\Acceleration;
use Galaxon\Quantities\QuantityType\Energy;
use Galaxon\Quantities\QuantityType\Force;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\QuantityType\Power;
use Galaxon\Quantities\QuantityType\Time;
use PHPUnit\Framework\TestCase;

/**
 * Newtonian mechanics examples demonstrating force, work, and power calculations.
 */
class NewtonianMechanicsTest extends TestCase
{
    use FloatAssertions;

    /**
     * Newton's second law: F = ma.
     *
     * A 10 kg object accelerated at 5 m/s².
     */
    public function testNewtonSecondLaw(): void
    {
        $m = new Mass(10, 'kg');
        $a = new Acceleration(5, 'm/s2');
        $force = $m->mul($a);

        $this->assertInstanceOf(Force::class, $force);
        $this->assertApproxEqual(50.0, $force->value);
    }

    /**
     * Weight from mass: W = mg.
     *
     * Weight of a 75 kg person under standard gravity.
     */
    public function testWeight(): void
    {
        $m = new Mass(75, 'kg');
        $g = PhysicalConstant::earthGravity();
        $weight = $m->mul($g);

        $this->assertInstanceOf(Force::class, $weight);
        // 75 × 9.80665 = 735.49875 N
        $this->assertApproxEqual(735.49875, $weight->value);
    }

    /**
     * Newton's law of gravitation: F = GMm/r².
     *
     * Gravitational force between Earth and the Moon.
     */
    public function testGravitationalForce(): void
    {
        $gravConst = PhysicalConstant::gravitational();
        $earthMass = new Mass(5.972e24, 'kg');
        $moonMass = new Mass(7.342e22, 'kg');
        $distance = new Length(3.844e8, 'm');

        $force = $gravConst->mul($earthMass)->mul($moonMass)->div($distance->pow(2));

        $this->assertInstanceOf(Force::class, $force);
        // ≈ 1.98 × 10²⁰ N
        $this->assertApproxEqual(1.9799e20, $force->value, 1e-3);
    }

    /**
     * Work done by a force: W = Fd.
     *
     * Pushing a box with 100 N of force over 5 metres.
     */
    public function testWorkDoneByForce(): void
    {
        $force = new Force(100, 'N');
        $d = new Length(5, 'm');
        $work = $force->mul($d);

        $this->assertInstanceOf(Energy::class, $work);
        $this->assertApproxEqual(500.0, $work->value);
    }

    /**
     * Power from work and time: P = W/t.
     *
     * Doing 500 J of work in 10 seconds.
     */
    public function testPowerFromWork(): void
    {
        $work = new Energy(500, 'J');
        $t = new Time(10, 's');
        $power = $work->div($t);

        $this->assertInstanceOf(Power::class, $power);
        $this->assertApproxEqual(50.0, $power->value);
    }

    /**
     * Work with prefixed force units: W = Fd.
     *
     * A 1 kN force applied over 100 metres.
     */
    public function testWorkWithPrefixedForce(): void
    {
        $force = new Force(1, 'kN');
        $d = new Length(100, 'm');
        $work = $force->mul($d);

        $this->assertInstanceOf(Energy::class, $work);
        // 1 kN × 100 m = 100 kJ
        $kj = $work->to('kJ');
        $this->assertApproxEqual(100.0, $kj->value);
    }

    /**
     * Work converted to calories: W = Fd.
     *
     * A 100 N force over 41.84 m produces exactly 1 kcal of energy.
     */
    public function testWorkConvertedToCalories(): void
    {
        $force = new Force(100, 'N');
        $d = new Length(41.84, 'm');
        $work = $force->mul($d);

        $this->assertInstanceOf(Energy::class, $work);
        // 100 N × 41.84 m = 4184 J = 1 kcal
        $kcal = $work->to('kcal');
        $this->assertApproxEqual(1.0, $kcal->value);
    }
}
