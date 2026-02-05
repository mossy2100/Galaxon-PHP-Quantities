<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests;

use DomainException;
use Galaxon\Core\Traits\FloatAssertions;
use Galaxon\Quantities\PhysicalConstant;
use Galaxon\Quantities\Quantity;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the PhysicalConstant class.
 */
#[CoversClass(PhysicalConstant::class)]
final class PhysicalConstantTest extends TestCase
{
    use FloatAssertions;

    // region get() tests

    /**
     * Test getting the speed of light by symbol.
     */
    public function testGetSpeedOfLight(): void
    {
        $c = PhysicalConstant::get('c');

        $this->assertInstanceOf(Quantity::class, $c);
        $this->assertSame(299792458.0, $c->value);
        $this->assertSame('m/s', $c->derivedUnit->asciiSymbol);
    }

    /**
     * Test getting the gravitational constant by symbol.
     */
    public function testGetGravitationalConstant(): void
    {
        $gravConst = PhysicalConstant::get('G');

        $this->assertInstanceOf(Quantity::class, $gravConst);
        $this->assertApproxEqual(6.67430e-11, $gravConst->value);
        $this->assertSame('m3/(kg*s2)', $gravConst->derivedUnit->asciiSymbol);
    }

    /**
     * Test getting Planck constant by symbol.
     */
    public function testGetPlanckConstant(): void
    {
        $h = PhysicalConstant::get('h');

        $this->assertInstanceOf(Quantity::class, $h);
        $this->assertApproxEqual(6.62607015e-34, $h->value);
        $this->assertSame('J*s', $h->derivedUnit->asciiSymbol);
    }

    /**
     * Test getting Boltzmann constant by symbol.
     */
    public function testGetBoltzmannConstant(): void
    {
        $k = PhysicalConstant::get('k');

        $this->assertInstanceOf(Quantity::class, $k);
        $this->assertApproxEqual(1.380649e-23, $k->value);
        $this->assertSame('J/K', $k->derivedUnit->asciiSymbol);
    }

    /**
     * Test getting Avogadro constant by symbol.
     */
    public function testGetAvogadroConstant(): void
    {
        $NA = PhysicalConstant::get('NA');

        $this->assertInstanceOf(Quantity::class, $NA);
        $this->assertApproxEqual(6.02214076e23, $NA->value);
        $this->assertSame('mol-1', $NA->derivedUnit->asciiSymbol);
    }

    /**
     * Test getting elementary charge by symbol.
     */
    public function testGetElementaryCharge(): void
    {
        $e = PhysicalConstant::get('e');

        $this->assertInstanceOf(Quantity::class, $e);
        $this->assertApproxEqual(1.602176634e-19, $e->value);
        $this->assertSame('C', $e->derivedUnit->asciiSymbol);
    }

    /**
     * Test getting caesium hyperfine transition frequency by symbol.
     */
    public function testGetCaesiumFrequency(): void
    {
        $deltaNuCs = PhysicalConstant::get('deltaNuCs');

        $this->assertInstanceOf(Quantity::class, $deltaNuCs);
        $this->assertSame(9192631770.0, $deltaNuCs->value);
        $this->assertSame('Hz', $deltaNuCs->derivedUnit->asciiSymbol);
    }

    /**
     * Test getting luminous efficacy by symbol.
     */
    public function testGetLuminousEfficacy(): void
    {
        $kcd = PhysicalConstant::get('Kcd');

        $this->assertInstanceOf(Quantity::class, $kcd);
        $this->assertSame(683.0, $kcd->value);
        $this->assertSame('lm/W', $kcd->derivedUnit->asciiSymbol);
    }

    /**
     * Test getting standard gravity by symbol.
     */
    public function testGetStandardGravity(): void
    {
        $g = PhysicalConstant::get('g');

        $this->assertInstanceOf(Quantity::class, $g);
        $this->assertSame(9.80665, $g->value);
        $this->assertSame('m/s2', $g->derivedUnit->asciiSymbol);
    }

    /**
     * Test getting a dimensionless constant (fine-structure constant).
     */
    public function testGetDimensionlessConstant(): void
    {
        $alpha = PhysicalConstant::get('alpha');

        $this->assertInstanceOf(Quantity::class, $alpha);
        $this->assertApproxEqual(7.2973525693e-3, $alpha->value);
        $this->assertSame('', $alpha->derivedUnit->asciiSymbol);
    }

    /**
     * Test getting the molar gas constant by symbol.
     */
    public function testGetMolarGasConstant(): void
    {
        $gasConst = PhysicalConstant::get('R');

        $this->assertInstanceOf(Quantity::class, $gasConst);
        $this->assertApproxEqual(8.314462618, $gasConst->value);
        $this->assertSame('J/(K*mol)', $gasConst->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Caching tests

    /**
     * Test that get() returns the same cached instance on repeated calls.
     */
    public function testGetReturnsCachedInstance(): void
    {
        $c1 = PhysicalConstant::get('c');
        $c2 = PhysicalConstant::get('c');

        $this->assertSame($c1, $c2);
    }

    // endregion

    // region Case sensitivity tests

    /**
     * Test that symbol lookup is case-sensitive (G vs g).
     */
    public function testSymbolLookupIsCaseSensitive(): void
    {
        $gravConst = PhysicalConstant::get('G');
        $g = PhysicalConstant::get('g');

        // G is gravitational constant, g is standard gravity - different values.
        $this->assertNotSame($gravConst, $g);
        $this->assertApproxEqual(6.67430e-11, $gravConst->value);
        $this->assertSame(9.80665, $g->value);
    }

    // endregion

    // region getByName() tests

    /**
     * Test getting a constant by name.
     */
    public function testGetByName(): void
    {
        $c = PhysicalConstant::getByName('speed of light in vacuum');

        $this->assertInstanceOf(Quantity::class, $c);
        $this->assertSame(299792458.0, $c->value);
    }

    /**
     * Test that name lookup is case-insensitive.
     */
    public function testGetByNameIsCaseInsensitive(): void
    {
        $c1 = PhysicalConstant::getByName('speed of light in vacuum');
        $c2 = PhysicalConstant::getByName('SPEED OF LIGHT IN VACUUM');
        $c3 = PhysicalConstant::getByName('Speed Of Light In Vacuum');

        // All should return the same cached instance.
        $this->assertSame($c1, $c2);
        $this->assertSame($c2, $c3);
    }

    /**
     * Test getByName() for various constants.
     */
    public function testGetByNameVariousConstants(): void
    {
        $gravConst = PhysicalConstant::getByName('gravitational constant');
        $this->assertApproxEqual(6.67430e-11, $gravConst->value);

        $h = PhysicalConstant::getByName('Planck constant');
        $this->assertApproxEqual(6.62607015e-34, $h->value);

        $k = PhysicalConstant::getByName('Boltzmann constant');
        $this->assertApproxEqual(1.380649e-23, $k->value);
    }

    // endregion

    // region getAll() tests

    /**
     * Test that getAll() returns all constants.
     */
    public function testGetAllReturnsAllConstants(): void
    {
        $all = PhysicalConstant::getAll();

        $this->assertIsArray($all);
        $this->assertNotEmpty($all);

        // Check that all 7 SI defining constants are present.
        $this->assertArrayHasKey('deltaNuCs', $all);
        $this->assertArrayHasKey('c', $all);
        $this->assertArrayHasKey('h', $all);
        $this->assertArrayHasKey('e', $all);
        $this->assertArrayHasKey('k', $all);
        $this->assertArrayHasKey('NA', $all);
        $this->assertArrayHasKey('Kcd', $all);

        // Check some other constants.
        $this->assertArrayHasKey('G', $all);
        $this->assertArrayHasKey('g', $all);
        $this->assertArrayHasKey('alpha', $all);
    }

    /**
     * Test that getAll() returns Quantity instances.
     */
    public function testGetAllReturnsQuantityInstances(): void
    {
        $all = PhysicalConstant::getAll();

        foreach ($all as $symbol => $quantity) {
            $this->assertIsString($symbol);
            $this->assertInstanceOf(Quantity::class, $quantity);
        }
    }

    // endregion

    // region Exception tests

    /**
     * Test that get() throws for unknown symbol.
     */
    public function testGetThrowsForUnknownSymbol(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Unknown constant symbol: 'xyz'.");

        PhysicalConstant::get('xyz');
    }

    /**
     * Test that getByName() throws for unknown name.
     */
    public function testGetByNameThrowsForUnknownName(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Unknown constant name: 'unknown constant'.");

        PhysicalConstant::getByName('unknown constant');
    }

    // endregion

    // region Practical usage tests

    /**
     * Test using constants in calculations (E = mc²).
     */
    public function testEnergyMassEquivalence(): void
    {
        $c = PhysicalConstant::get('c');
        $mass = Quantity::create(1, 'kg');

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
        $gravConst = PhysicalConstant::get('G');

        // Gravitational force between two 1 kg masses 1 m apart.
        // F = G * m1 * m2 / r²
        $m1 = Quantity::create(1, 'kg');
        $m2 = Quantity::create(1, 'kg');
        $r = Quantity::create(1, 'm');

        $force = $gravConst->mul($m1)->mul($m2)->div($r->pow(2));
        $newtons = $force->to('N');

        // F = 6.67430e-11 N
        $this->assertApproxEqual(6.67430e-11, $newtons->value);
    }

    // endregion
}
