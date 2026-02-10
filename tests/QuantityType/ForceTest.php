<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\QuantityType;

use Galaxon\Core\Traits\FloatAssertions;
use Galaxon\Quantities\QuantityType\Acceleration;
use Galaxon\Quantities\QuantityType\Force;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;
use Galaxon\Quantities\Tests\Traits\ArrayShapeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Force quantity type.
 */
#[CoversClass(Force::class)]
final class ForceTest extends TestCase
{
    use ArrayShapeTrait;
    use FloatAssertions;

    // region Setup

    public static function setUpBeforeClass(): void
    {
        // Load Imperial/US units.
        UnitRegistry::loadSystem(System::Imperial);
        UnitRegistry::loadSystem(System::UsCustomary);
    }

    // endregion

    // region Overridden methods

    /**
     * Test getUnitDefinitions() returns valid unit definitions.
     */
    public function testGetUnitDefinitionsReturnsValidArray(): void
    {
        $units = Force::getUnitDefinitions();

        $this->assertValidUnitDefinitionsShape($units);
    }

    /**
     * Test getConversionDefinitions() returns empty array.
     */
    public function testGetConversionDefinitionsReturnsEmptyArray(): void
    {
        $conversions = Force::getConversionDefinitions();

        $this->assertEmpty($conversions);
    }

    // endregion

    // region Metric conversion tests

    /**
     * Test converting newtons to kilonewtons.
     */
    public function testConvertNewtonsToKilonewtons(): void
    {
        $force = new Force(1000, 'N');
        $kn = $force->to('kN');

        $this->assertInstanceOf(Force::class, $kn);
        $this->assertSame(1.0, $kn->value);
        $this->assertSame('kN', $kn->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting kilonewtons to newtons.
     */
    public function testConvertKilonewtonsToNewtons(): void
    {
        $force = new Force(5, 'kN');
        $n = $force->to('N');

        $this->assertSame(5000.0, $n->value);
    }

    /**
     * Test converting newtons to millinewtons.
     */
    public function testConvertNewtonsToMillinewtons(): void
    {
        $force = new Force(1, 'N');
        $mn = $force->to('mN');

        $this->assertSame(1000.0, $mn->value);
    }

    /**
     * Test converting millinewtons to newtons.
     */
    public function testConvertMillinewtonsToNewtons(): void
    {
        $force = new Force(500, 'mN');
        $n = $force->to('N');

        $this->assertSame(0.5, $n->value);
    }

    /**
     * Test converting newtons to meganewtons.
     */
    public function testConvertNewtonsToMeganewtons(): void
    {
        $force = new Force(1000000, 'N');
        $mn = $force->to('MN');

        $this->assertSame(1.0, $mn->value);
    }

    // endregion

    // region Cross-system conversion tests

    /**
     * Test converting newtons to pound force.
     */
    public function testConvertNewtonsToPoundForce(): void
    {
        $force = new Force(1, 'N');
        $lbf = $force->to('lbf');

        // 1 N = 1 / 4.4482216152605 lbf ≈ 0.224809 lbf
        // 1 lbf = 0.45359237 kg × 9.80665 m/s² = 4.4482216152605 N
        $this->assertApproxEqual(1 / 4.4482216152605, $lbf->value);
    }

    /**
     * Test converting pound force to newtons.
     */
    public function testConvertPoundForceToNewtons(): void
    {
        $force = new Force(1, 'lbf');
        $n = $force->to('N');

        // 1 lbf = 4.4482216152605 N
        $this->assertApproxEqual(4.4482216152605, $n->value);
    }

    /**
     * Test converting kilonewtons to pound force.
     */
    public function testConvertKilonewtonsToPoundForce(): void
    {
        $force = new Force(1, 'kN');
        $lbf = $force->to('lbf');

        // 1 kN = 1000 N = 1000 / 4.4482216152605 lbf ≈ 224.809 lbf
        $this->assertApproxEqual(1000 / 4.4482216152605, $lbf->value);
    }

    /**
     * Test converting pound force to kilonewtons.
     */
    public function testConvertPoundForceToKilonewtons(): void
    {
        $force = new Force(1000, 'lbf');
        $kn = $force->to('kN');

        // 1000 lbf = 1000 × 4.4482216152605 N = 4448.2216152605 N = 4.4482216152605 kN
        $this->assertApproxEqual(4.4482216152605, $kn->value);
    }

    // endregion

    // region SI base unit conversion tests

    /**
     * Test converting newtons to base SI units.
     */
    public function testConvertNewtonsToBaseSI(): void
    {
        $force = new Force(10, 'N');
        $si = $force->toSiBase();

        // 10 N = 10 kg·m·s⁻²
        $this->assertSame(10.0, $si->value);
        $this->assertSame('kg*m/s2', $si->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting pound force to base SI units.
     */
    public function testConvertPoundForceToBaseSI(): void
    {
        $force = new Force(1, 'lbf');
        $si = $force->toSiBase();

        // 1 lbf = 4.4482216152605 kg·m·s⁻²
        $this->assertApproxEqual(4.4482216152605, $si->value);
        $this->assertSame('kg*m/s2', $si->derivedUnit->asciiSymbol);
    }

    // endregion

    // region F = ma tests (Mass × Acceleration = Force)

    /**
     * Test calculating force from mass and acceleration (SI units).
     */
    public function testForceFromMassAndAccelerationSI(): void
    {
        $mass = new Mass(10, 'kg');
        $acceleration = new Acceleration(5, 'm/s2');
        $result = $mass->mul($acceleration);

        // F = ma = 10 kg × 5 m/s² = 50 N
        $this->assertInstanceOf(Force::class, $result);
        $this->assertSame(50.0, $result->value);

        // Convert to newtons
        $n = $result->to('N');
        $this->assertSame(50.0, $n->value);
    }

    /**
     * Test calculating force from mass and acceleration (different units).
     */
    public function testForceFromMassAndAccelerationGrams(): void
    {
        $mass = new Mass(1000, 'g');
        $acceleration = new Acceleration(10, 'm/s2');
        $result = $mass->mul($acceleration);

        // F = ma = 1000 g × 10 m/s² = 1 kg × 10 m/s² = 10 N
        $n = $result->to('N');
        $this->assertSame(10.0, $n->value);
    }

    // endregion

    // region Weight calculation tests

    /**
     * Test calculating weight from mass (Earth gravity).
     */
    public function testWeightFromMass(): void
    {
        // Standard gravity g₀ = 9.80665 m/s²
        $mass = new Mass(1, 'kg');
        $gravity = new Acceleration(9.80665, 'm/s2');
        $weight = $mass->mul($gravity);

        // Weight = 1 kg × 9.80665 m/s² = 9.80665 N
        $n = $weight->to('N');
        $this->assertApproxEqual(9.80665, $n->value);
    }

    /**
     * Test that 1 kg weighs approximately 2.205 lbf on Earth.
     */
    public function testKilogramWeightInPoundForce(): void
    {
        $mass = new Mass(1, 'kg');
        $gravity = new Acceleration(9.80665, 'm/s2');
        $weight = $mass->mul($gravity);

        $lbf = $weight->to('lbf');
        // 9.80665 N / 4.4482216152605 N/lbf ≈ 2.20462 lbf
        $this->assertApproxEqual(9.80665 / 4.4482216152605, $lbf->value);
    }

    // endregion

    // region Addition tests

    /**
     * Test adding newtons to newtons.
     */
    public function testAddNewtonsToNewtons(): void
    {
        $a = new Force(100, 'N');
        $b = new Force(50, 'N');
        $result = $a->add($b);

        $this->assertInstanceOf(Force::class, $result);
        $this->assertSame(150.0, $result->value);
        $this->assertSame('N', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding millinewtons to newtons.
     */
    public function testAddMillinewtonsToNewtons(): void
    {
        $a = new Force(1, 'N');
        $b = new Force(500, 'mN');
        $result = $a->add($b);

        // 1 N + 500 mN = 1 N + 0.5 N = 1.5 N
        $this->assertInstanceOf(Force::class, $result);
        $this->assertSame(1.5, $result->value);
        $this->assertSame('N', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding pound force to newtons (cross-system).
     */
    public function testAddPoundForceToNewtons(): void
    {
        $a = new Force(10, 'N');
        $b = new Force(1, 'lbf');
        $result = $a->add($b);

        // 10 N + 1 lbf = 10 N + 4.4482216152605 N ≈ 14.448 N
        $this->assertInstanceOf(Force::class, $result);
        $this->assertApproxEqual(10 + 4.4482216152605, $result->value);
        $this->assertSame('N', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding kilonewtons to pound force.
     */
    public function testAddKilonewtonsToPoundForce(): void
    {
        $a = new Force(100, 'lbf');
        $b = new Force(1, 'kN');
        $result = $a->add($b);

        // 100 lbf + 1 kN = 100 lbf + 224.809... lbf ≈ 324.809 lbf
        $this->assertInstanceOf(Force::class, $result);
        $this->assertApproxEqual(100 + 1000 / 4.4482216152605, $result->value);
        $this->assertSame('lbf', $result->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Parse tests

    /**
     * Test parsing newtons.
     */
    public function testParseNewtons(): void
    {
        $force = Force::parse('500 N');

        $this->assertInstanceOf(Force::class, $force);
        $this->assertSame(500.0, $force->value);
        $this->assertSame('N', $force->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing kilonewtons.
     */
    public function testParseKilonewtons(): void
    {
        $force = Force::parse('2.5 kN');

        $this->assertSame(2.5, $force->value);
        $this->assertSame('kN', $force->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing pound force.
     */
    public function testParsePoundForce(): void
    {
        $force = Force::parse('100 lbf');

        $this->assertSame(100.0, $force->value);
        $this->assertSame('lbf', $force->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Static convert method tests

    /**
     * Test static convert method.
     */
    public function testStaticConvertMethod(): void
    {
        $value = Force::convert(1000, 'N', 'kN');

        $this->assertSame(1.0, $value);
    }

    /**
     * Test static convert cross-system.
     */
    public function testStaticConvertCrossSystem(): void
    {
        $value = Force::convert(1, 'lbf', 'N');

        $this->assertApproxEqual(4.4482216152605, $value);
    }

    // endregion

    // region Practical examples

    /**
     * Test typical car engine thrust.
     */
    public function testCarEngineThrust(): void
    {
        // A typical car might produce 3000 N of thrust
        $thrust = new Force(3000, 'N');
        $lbf = $thrust->to('lbf');

        // ≈ 674.4 lbf
        $this->assertApproxEqual(3000 / 4.4482216152605, $lbf->value);
    }

    /**
     * Test zero force conversion.
     */
    public function testZeroForceConversion(): void
    {
        $force = new Force(0, 'N');
        $lbf = $force->to('lbf');

        $this->assertSame(0.0, $lbf->value);
    }

    // endregion

    // region Expansion and derived unit tests

    /**
     * Test expanding newtons to base units.
     */
    public function testExpandNewtons(): void
    {
        $force = new Force(10, 'N');
        $expanded = $force->expand();

        // N expands to kg·m·s⁻²
        $this->assertSame(10.0, $expanded->value);
        $this->assertSame('kg*m/s2', $expanded->derivedUnit->asciiSymbol);
    }

    /**
     * Test expanding pound force to base units.
     */
    public function testExpandPoundForce(): void
    {
        $force = new Force(1, 'lbf');
        $expanded = $force->expand();

        // lbf expands to lb·ft·s⁻² with factor g₀/0.3048 ≈ 32.174
        // 1 lbf = 32.174... lb·ft·s⁻²
        $this->assertApproxEqual(9.80665 / 0.3048, $expanded->value);
        $this->assertSame('lb*ft/s2', $expanded->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting pound force to lb·ft/s².
     */
    public function testConvertPoundForceToLbFtPerSecondSquared(): void
    {
        $force = new Force(1, 'lbf');
        $derived = $force->to('lb*ft/s2');

        // 1 lbf = g₀/0.3048 lb·ft/s² ≈ 32.174 lb·ft/s²
        $this->assertApproxEqual(9.80665 / 0.3048, $derived->value);
    }

    /**
     * Test converting lb·ft/s² to pound force.
     */
    public function testConvertLbFtPerSecondSquaredToPoundForce(): void
    {
        // g₀/0.3048 lb·ft/s² = 1 lbf
        $force = new Force(9.80665 / 0.3048, 'lb*ft/s2');
        $lbf = $force->to('lbf');

        $this->assertApproxEqual(1.0, $lbf->value);
    }

    /**
     * Test converting newtons to lb·ft/s².
     */
    public function testConvertNewtonsToLbFtPerSecondSquared(): void
    {
        $force = new Force(1, 'N');
        $derived = $force->to('lb*ft/s2');

        // 1 N = 1 kg·m/s²
        // 1 kg = 1/0.45359237 lb
        // 1 m = 1/0.3048 ft
        // 1 N = (1/0.45359237) × (1/0.3048) lb·ft/s² ≈ 7.2330 lb·ft/s²
        $expected = 1 / (0.45359237 * 0.3048);
        $this->assertApproxEqual($expected, $derived->value);
    }

    /**
     * Test converting lb·ft/s² to newtons.
     */
    public function testConvertLbFtPerSecondSquaredToNewtons(): void
    {
        $force = new Force(1, 'lb*ft/s2');
        $n = $force->to('N');

        // 1 lb·ft/s² = 0.45359237 kg × 0.3048 m / s² ≈ 0.1383 N
        $expected = 0.45359237 * 0.3048;
        $this->assertApproxEqual($expected, $n->value);
    }

    // endregion
}
