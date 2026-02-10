<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests;

use DomainException;
use Galaxon\Core\Traits\FloatAssertions;
use Galaxon\Quantities\PhysicalConstant;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the PhysicalConstant class.
 */
#[CoversClass(PhysicalConstant::class)]
final class PhysicalConstantTest extends TestCase
{
    use FloatAssertions;

    // region SI defining constants

    /**
     * Test getting the speed of light.
     */
    public function testSpeedOfLight(): void
    {
        $c = PhysicalConstant::speedOfLight();

        $this->assertInstanceOf(Quantity::class, $c);
        $this->assertSame(299792458.0, $c->value);
        $this->assertSame('m/s', $c->derivedUnit->asciiSymbol);
    }

    /**
     * Test getting the Planck constant.
     */
    public function testPlanckConstant(): void
    {
        $h = PhysicalConstant::planck();

        $this->assertInstanceOf(Quantity::class, $h);
        $this->assertApproxEqual(6.62607015e-34, $h->value);
        $this->assertSame('J*s', $h->derivedUnit->asciiSymbol);
    }

    /**
     * Test getting the elementary charge.
     */
    public function testElementaryCharge(): void
    {
        $e = PhysicalConstant::elementaryCharge();

        $this->assertInstanceOf(Quantity::class, $e);
        $this->assertApproxEqual(1.602176634e-19, $e->value);
        $this->assertSame('C', $e->derivedUnit->asciiSymbol);
    }

    /**
     * Test getting the Boltzmann constant.
     */
    public function testBoltzmannConstant(): void
    {
        $k = PhysicalConstant::boltzmann();

        $this->assertInstanceOf(Quantity::class, $k);
        $this->assertApproxEqual(1.380649e-23, $k->value);
        $this->assertSame('J/K', $k->derivedUnit->asciiSymbol);
    }

    /**
     * Test getting the Avogadro constant.
     */
    public function testAvogadroConstant(): void
    {
        $na = PhysicalConstant::avogadro();

        $this->assertInstanceOf(Quantity::class, $na);
        $this->assertApproxEqual(6.02214076e23, $na->value);
        $this->assertSame('mol-1', $na->derivedUnit->asciiSymbol);
    }

    /**
     * Test getting the caesium frequency.
     */
    public function testCaesiumFrequency(): void
    {
        $deltaNuCs = PhysicalConstant::caesiumFrequency();

        $this->assertInstanceOf(Quantity::class, $deltaNuCs);
        $this->assertSame(9192631770.0, $deltaNuCs->value);
        $this->assertSame('Hz', $deltaNuCs->derivedUnit->asciiSymbol);
    }

    /**
     * Test getting the luminous efficacy.
     */
    public function testLuminousEfficacy(): void
    {
        $kcd = PhysicalConstant::luminousEfficacy();

        $this->assertInstanceOf(Quantity::class, $kcd);
        $this->assertSame(683.0, $kcd->value);
        $this->assertSame('lm/W', $kcd->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Universal constants

    /**
     * Test getting the gravitational constant.
     */
    public function testGravitationalConstant(): void
    {
        $gravConst = PhysicalConstant::gravitational();

        $this->assertInstanceOf(Quantity::class, $gravConst);
        $this->assertApproxEqual(6.67430e-11, $gravConst->value);
        $this->assertSame('m3/(kg*s2)', $gravConst->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Electromagnetic constants

    /**
     * Test getting the vacuum permittivity.
     */
    public function testVacuumPermittivity(): void
    {
        $epsilon0 = PhysicalConstant::vacuumPermittivity();

        $this->assertInstanceOf(Quantity::class, $epsilon0);
        $this->assertApproxEqual(8.8541878128e-12, $epsilon0->value);
        $this->assertSame('F/m', $epsilon0->derivedUnit->asciiSymbol);
    }

    /**
     * Test getting the vacuum permeability.
     */
    public function testVacuumPermeability(): void
    {
        $mu0 = PhysicalConstant::vacuumPermeability();

        $this->assertInstanceOf(Quantity::class, $mu0);
        $this->assertApproxEqual(1.25663706212e-6, $mu0->value);
        $this->assertSame('H/m', $mu0->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Atomic and nuclear constants

    /**
     * Test getting the electron mass.
     */
    public function testElectronMass(): void
    {
        $me = PhysicalConstant::electronMass();

        $this->assertInstanceOf(Quantity::class, $me);
        $this->assertApproxEqual(9.1093837015e-31, $me->value);
        $this->assertSame('kg', $me->derivedUnit->asciiSymbol);
    }

    /**
     * Test getting the proton mass.
     */
    public function testProtonMass(): void
    {
        $mp = PhysicalConstant::protonMass();

        $this->assertInstanceOf(Quantity::class, $mp);
        $this->assertApproxEqual(1.67262192369e-27, $mp->value);
        $this->assertSame('kg', $mp->derivedUnit->asciiSymbol);
    }

    /**
     * Test getting the neutron mass.
     */
    public function testNeutronMass(): void
    {
        $mn = PhysicalConstant::neutronMass();

        $this->assertInstanceOf(Quantity::class, $mn);
        $this->assertApproxEqual(1.67492749804e-27, $mn->value);
        $this->assertSame('kg', $mn->derivedUnit->asciiSymbol);
    }

    /**
     * Test getting the fine-structure constant (dimensionless).
     */
    public function testFineStructureConstant(): void
    {
        $alpha = PhysicalConstant::fineStructure();

        $this->assertInstanceOf(Quantity::class, $alpha);
        $this->assertApproxEqual(7.2973525693e-3, $alpha->value);
        $this->assertSame('', $alpha->derivedUnit->asciiSymbol);
    }

    /**
     * Test getting the Rydberg constant.
     */
    public function testRydbergConstant(): void
    {
        $rinf = PhysicalConstant::rydberg();

        $this->assertInstanceOf(Quantity::class, $rinf);
        $this->assertApproxEqual(10973731.568160, $rinf->value);
        $this->assertSame('m-1', $rinf->derivedUnit->asciiSymbol);
    }

    /**
     * Test getting the Bohr radius.
     */
    public function testBohrRadius(): void
    {
        $a0 = PhysicalConstant::bohrRadius();

        $this->assertInstanceOf(Quantity::class, $a0);
        $this->assertApproxEqual(5.29177210903e-11, $a0->value);
        $this->assertSame('m', $a0->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Thermodynamic constants

    /**
     * Test getting the molar gas constant.
     */
    public function testMolarGasConstant(): void
    {
        $gasConst = PhysicalConstant::molarGas();

        $this->assertInstanceOf(Quantity::class, $gasConst);
        $this->assertApproxEqual(8.314462618, $gasConst->value);
        $this->assertSame('J/(mol*K)', $gasConst->derivedUnit->asciiSymbol);
    }

    /**
     * Test getting the Stefan-Boltzmann constant.
     */
    public function testStefanBoltzmannConstant(): void
    {
        $sigma = PhysicalConstant::stefanBoltzmann();

        $this->assertInstanceOf(Quantity::class, $sigma);
        $this->assertApproxEqual(5.670374419e-8, $sigma->value);
        $this->assertSame('W/(m2*K4)', $sigma->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Other constants

    /**
     * Test getting the standard gravity.
     */
    public function testStandardGravity(): void
    {
        $g = PhysicalConstant::earthGravity();

        $this->assertInstanceOf(Quantity::class, $g);
        $this->assertSame(9.80665, $g->value);
        $this->assertSame('m/s2', $g->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Caching tests

    /**
     * Test that repeated calls return the same cached instance.
     */
    public function testReturnsCachedInstance(): void
    {
        $c1 = PhysicalConstant::speedOfLight();
        $c2 = PhysicalConstant::speedOfLight();

        $this->assertSame($c1, $c2);
    }

    /**
     * Test that different constants return different instances.
     */
    public function testDifferentConstantsAreDifferent(): void
    {
        $gravConst = PhysicalConstant::gravitational();
        $g = PhysicalConstant::earthGravity();

        $this->assertNotSame($gravConst, $g);
        $this->assertApproxEqual(6.67430e-11, $gravConst->value);
        $this->assertSame(9.80665, $g->value);
    }

    /**
     * Test that get() returns the same cached instance as the static method.
     */
    public function testGetSharesCacheWithStaticMethods(): void
    {
        $c1 = PhysicalConstant::speedOfLight();
        $c2 = PhysicalConstant::get('c');

        $this->assertSame($c1, $c2);
    }

    // endregion

    // region get() method tests

    /**
     * Test get() with speed of light symbol.
     */
    public function testGetSpeedOfLight(): void
    {
        $c = PhysicalConstant::get('c');

        $this->assertInstanceOf(Quantity::class, $c);
        $this->assertSame(299792458.0, $c->value);
        $this->assertSame('m/s', $c->derivedUnit->asciiSymbol);
    }

    /**
     * Test get() with gravitational constant symbol.
     */
    public function testGetGravitationalConstant(): void
    {
        $gravConst = PhysicalConstant::get('G');

        $this->assertInstanceOf(Quantity::class, $gravConst);
        $this->assertApproxEqual(6.67430e-11, $gravConst->value);
    }

    /**
     * Test get() with standard gravity symbol.
     */
    public function testGetStandardGravity(): void
    {
        $g = PhysicalConstant::get('g');

        $this->assertInstanceOf(Quantity::class, $g);
        $this->assertSame(9.80665, $g->value);
    }

    /**
     * Test get() is case-sensitive (G vs g).
     */
    public function testGetIsCaseSensitive(): void
    {
        $gravConst = PhysicalConstant::get('G');
        $g = PhysicalConstant::get('g');

        $this->assertNotSame($gravConst, $g);
        $this->assertApproxEqual(6.67430e-11, $gravConst->value);
        $this->assertSame(9.80665, $g->value);
    }

    /**
     * Test get() throws for unknown symbol.
     */
    public function testGetThrowsForUnknownSymbol(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Unknown constant symbol: 'xyz'.");

        PhysicalConstant::get('xyz');
    }

    // endregion

    // region Practical usage tests

    /**
     * Test using constants in calculations (E = mc²).
     */
    public function testEnergyMassEquivalence(): void
    {
        $c = PhysicalConstant::speedOfLight();
        $mass = new Mass(1, 'kg');

        // E = mc²
        $energy = $mass->mul($c)->mul($c);

        // 1 kg × (299792458 m/s)² = 8.98755e16 J
        $joules = $energy->to('J');
        $this->assertApproxEqual(8.98755178736818e16, $joules->value);
    }

    /**
     * Test using gravitational constant in calculation.
     */
    public function testGravitationalForceCalculation(): void
    {
        $gravConst = PhysicalConstant::gravitational();

        // Gravitational force between two 1 kg masses 1 m apart.
        // F = G * m1 * m2 / r²
        $m1 = new Mass(1, 'kg');
        $m2 = new Mass(1, 'kg');
        $r = new Length(1, 'm');

        $force = $gravConst->mul($m1)->mul($m2)->div($r->pow(2));
        $newtons = $force->to('N');

        // F = 6.67430e-11 N
        $this->assertApproxEqual(6.67430e-11, $newtons->value);
    }

    // endregion
}
