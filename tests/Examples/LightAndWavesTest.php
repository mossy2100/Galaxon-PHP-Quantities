<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Examples;

use Galaxon\Core\Traits\FloatAssertions;
use Galaxon\Quantities\PhysicalConstant;
use Galaxon\Quantities\QuantityType\Energy;
use Galaxon\Quantities\QuantityType\Frequency;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\QuantityType\Velocity;
use PHPUnit\Framework\TestCase;

/**
 * Light and wave examples demonstrating photon energy, wavelength, and mass-energy equivalence.
 */
class LightAndWavesTest extends TestCase
{
    use FloatAssertions;

    /**
     * Photon energy: E = hf.
     *
     * Energy of a green light photon (f ≈ 5.49 × 10¹⁴ Hz, λ ≈ 546 nm).
     */
    public function testPhotonEnergy(): void
    {
        $h = PhysicalConstant::planck();
        $f = new Frequency(5.49e14, 'Hz');
        $energy = $h->mul($f);

        $this->assertInstanceOf(Energy::class, $energy);
        // 6.62607015 × 10⁻³⁴ × 5.49 × 10¹⁴ ≈ 3.638 × 10⁻¹⁹ J
        $this->assertApproxEqual(3.6397e-19, $energy->value, 1e-3);
    }

    /**
     * Wavelength from frequency: λ = c/f.
     *
     * Wavelength of an FM radio signal at 101.5 MHz.
     */
    public function testWavelengthFromFrequency(): void
    {
        $c = PhysicalConstant::speedOfLight();
        $f = new Frequency(101.5e6, 'Hz');
        $lambda = $c->div($f);

        $this->assertInstanceOf(Length::class, $lambda);
        // 299,792,458 / 101,500,000 ≈ 2.954 m
        $this->assertApproxEqual(2.9536, $lambda->value, 1e-3);
    }

    /**
     * Mass-energy equivalence: E = mc².
     *
     * Energy contained in 1 gram of matter.
     */
    public function testMassEnergyEquivalence(): void
    {
        $m = new Mass(0.001, 'kg');
        $c = PhysicalConstant::speedOfLight();
        $energy = $m->mul($c->pow(2));

        $this->assertInstanceOf(Energy::class, $energy);
        // 0.001 × (299,792,458)² ≈ 8.988 × 10¹³ J (≈ 21.5 kilotons of TNT)
        $this->assertApproxEqual(8.9876e13, $energy->value, 1e-4);
    }

    /**
     * De Broglie wavelength: λ = h/(mv).
     *
     * Wavelength of an electron travelling at 10⁶ m/s.
     */
    public function testDeBroglieWavelength(): void
    {
        $h = PhysicalConstant::planck();
        $me = PhysicalConstant::electronMass();
        $v = new Velocity(1e6, 'm/s');

        $lambda = $h->div($me->mul($v));

        $this->assertInstanceOf(Length::class, $lambda);
        // 6.626 × 10⁻³⁴ / (9.109 × 10⁻³¹ × 10⁶) ≈ 7.274 × 10⁻¹⁰ m (0.727 nm)
        $this->assertApproxEqual(7.274e-10, $lambda->value, 1e-3);
    }

    /**
     * Photon energy from wavelength: E = hc/λ.
     *
     * Energy of a green light photon (λ = 550 nm) in joules and electronvolts.
     */
    public function testPhotonEnergyFromWavelength(): void
    {
        $h = PhysicalConstant::planck();
        $c = PhysicalConstant::speedOfLight();
        $lambda = new Length(550, 'nm');

        $energy = $h->mul($c)->div($lambda);

        $this->assertInstanceOf(Energy::class, $energy);
        // hc/λ ≈ 3.61 × 10⁻¹⁹ J ≈ 2.25 eV
        $j = $energy->to('J');
        $this->assertApproxEqual(3.6126e-19, $j->value, 1e-4);
        $ev = $energy->to('eV');
        $this->assertApproxEqual(2.254, $ev->value, 1e-3);
    }
}
