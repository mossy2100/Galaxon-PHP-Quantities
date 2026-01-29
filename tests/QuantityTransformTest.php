<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests;

use Galaxon\Core\Floats;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType\Energy;
use Galaxon\Quantities\QuantityType\Force;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for transformation operations on Quantity objects.
 */
#[CoversClass(Quantity::class)]
final class QuantityTransformTest extends TestCase
{
    // region Setup

    public static function setUpBeforeClass(): void
    {
        // Load Imperial/US units for cross-system tests.
        UnitRegistry::loadSystem(System::Imperial);
        UnitRegistry::loadSystem(System::US);
    }

    // endregion

    // region toSi() tests

    /**
     * Test toSi() on a length in metres (already SI).
     */
    public function testToSiAlreadySi(): void
    {
        $length = new Length(100, 'm');
        $si = $length->toSi();

        $this->assertSame(100.0, $si->value);
        $this->assertSame('m', $si->derivedUnit->asciiSymbol);
    }

    /**
     * Test toSi() on a length in kilometres.
     */
    public function testToSiKilometres(): void
    {
        $length = new Length(1, 'km');
        $si = $length->toSi();

        $this->assertSame(1000.0, $si->value);
        $this->assertSame('m', $si->derivedUnit->asciiSymbol);
    }

    /**
     * Test toSi() on a length in feet.
     */
    public function testToSiFeet(): void
    {
        $length = new Length(1, 'ft');
        $si = $length->toSi();

        $this->assertSame(0.3048, $si->value);
        $this->assertSame('m', $si->derivedUnit->asciiSymbol);
    }

    /**
     * Test toSi() on mass in pounds.
     */
    public function testToSiPounds(): void
    {
        $mass = new Mass(1, 'lb');
        $si = $mass->toSi();

        $this->assertSame(0.45359237, $si->value);
        $this->assertSame('kg', $si->derivedUnit->asciiSymbol);
    }

    /**
     * Test toSi() with autoPrefix.
     */
    public function testToSiWithAutoPrefix(): void
    {
        $length = new Length(5000, 'm');
        $si = $length->toSi(false, true);

        $this->assertSame(5.0, $si->value);
        $this->assertSame('km', $si->derivedUnit->asciiSymbol);
    }

    /**
     * Test toSi() with autoPrefix on small value.
     */
    public function testToSiWithAutoPrefixSmall(): void
    {
        $length = new Length(0.005, 'm');
        $si = $length->toSi(false, true);

        $this->assertSame(5.0, $si->value);
        $this->assertSame('mm', $si->derivedUnit->asciiSymbol);
    }

    // endregion

    // region expand() tests

    /**
     * Test expand() on newton to base units.
     */
    public function testExpandNewton(): void
    {
        $force = new Force(1, 'N');
        $expanded = $force->expand();

        // N = kg⋅m⋅s⁻²
        $this->assertSame(1.0, $expanded->value);
        $this->assertSame('kg*m*s-2', $expanded->derivedUnit->asciiSymbol);
    }

    /**
     * Test expand() on joule to base units.
     */
    public function testExpandJoule(): void
    {
        $energy = new Energy(1, 'J');
        $expanded = $energy->expand();

        // J = kg⋅m²⋅s⁻²
        $this->assertSame(1.0, $expanded->value);
        $this->assertSame('kg*m2*s-2', $expanded->derivedUnit->asciiSymbol);
    }

    /**
     * Test expand() on unit without expansion (already base).
     */
    public function testExpandAlreadyBase(): void
    {
        $length = new Length(10, 'm');
        $expanded = $length->expand();

        $this->assertSame(10.0, $expanded->value);
        $this->assertSame('m', $expanded->derivedUnit->asciiSymbol);
    }

    // endregion

    // region compact() tests

    /**
     * Test compact() on kg*m*s-2 to newton.
     */
    public function testCompactToNewton(): void
    {
        // Create kg⋅m⋅s⁻² manually
        $kgms2 = Quantity::create(1, 'kg*m*s-2');
        $compacted = $kgms2->compact();

        $this->assertSame(1.0, $compacted->value);
        $this->assertSame('N', $compacted->derivedUnit->asciiSymbol);
    }

    /**
     * Test compact() on kg*m2*s-2 to joule.
     */
    public function testCompactToJoule(): void
    {
        // Create kg⋅m²⋅s⁻² manually
        $kgm2s2 = Quantity::create(1, 'kg*m2*s-2');
        $compacted = $kgm2s2->compact();

        $this->assertSame(1.0, $compacted->value);
        $this->assertSame('J', $compacted->derivedUnit->asciiSymbol);
    }

    /**
     * Test compact() when no compaction possible.
     */
    public function testCompactNoChange(): void
    {
        $length = new Length(10, 'm');
        $compacted = $length->compact();

        $this->assertSame(10.0, $compacted->value);
        $this->assertSame('m', $compacted->derivedUnit->asciiSymbol);
    }

    /**
     * Test compact() on s-1 to Hz.
     */
    public function testCompactToHertz(): void
    {
        $frequency = Quantity::create(1, 's-1');
        $compacted = $frequency->compact();

        $this->assertSame(1.0, $compacted->value);
        $this->assertSame('Hz', $compacted->derivedUnit->asciiSymbol);
    }

    // endregion

    // region merge() tests

    /**
     * Test merge() combines same dimension units.
     */
    public function testMergeSameDimension(): void
    {
        // Create m*ft (metres times feet)
        $length1 = new Length(2, 'm');
        $length2 = new Length(3, 'ft');
        $product = $length1->mul($length2);

        // Before merge: m*ft
        $this->assertSame('m*ft', $product->derivedUnit->asciiSymbol);

        // After merge: m2
        $merged = $product->merge();
        $this->assertSame('m2', $merged->derivedUnit->asciiSymbol);

        // Value should be 2 * (3 * 0.3048) = 1.8288 m²
        $this->assertTrue(Floats::approxEqual(2 * 3 * 0.3048, $merged->value));
    }

    /**
     * Test merge() when no merge needed.
     */
    public function testMergeNoChange(): void
    {
        $length = new Length(10, 'm');
        $merged = $length->merge();

        $this->assertSame(10.0, $merged->value);
        $this->assertSame('m', $merged->derivedUnit->asciiSymbol);
    }

    // endregion

    // region autoPrefix() tests

    /**
     * Test autoPrefix() on large value.
     */
    public function testAutoPrefixLarge(): void
    {
        $length = new Length(5000, 'm');
        $prefixed = $length->autoPrefix();

        $this->assertSame(5.0, $prefixed->value);
        $this->assertSame('km', $prefixed->derivedUnit->asciiSymbol);
    }

    /**
     * Test autoPrefix() on small value.
     */
    public function testAutoPrefixSmall(): void
    {
        $length = new Length(0.005, 'm');
        $prefixed = $length->autoPrefix();

        $this->assertSame(5.0, $prefixed->value);
        $this->assertSame('mm', $prefixed->derivedUnit->asciiSymbol);
    }

    /**
     * Test autoPrefix() on value already optimal.
     */
    public function testAutoPrefixOptimal(): void
    {
        $length = new Length(5, 'm');
        $prefixed = $length->autoPrefix();

        $this->assertSame(5.0, $prefixed->value);
        $this->assertSame('m', $prefixed->derivedUnit->asciiSymbol);
    }

    /**
     * Test autoPrefix() on very small value.
     */
    public function testAutoPrefixVerySmall(): void
    {
        $length = new Length(0.000005, 'm');
        $prefixed = $length->autoPrefix();

        $this->assertTrue(Floats::approxEqual(5.0, $prefixed->value));
        $this->assertSame('μm', $prefixed->derivedUnit->unicodeSymbol);
    }

    /**
     * Test autoPrefix() on negative value.
     */
    public function testAutoPrefixNegative(): void
    {
        $length = new Length(-5000, 'm');
        $prefixed = $length->autoPrefix();

        $this->assertSame(-5.0, $prefixed->value);
        $this->assertSame('km', $prefixed->derivedUnit->asciiSymbol);
    }

    /**
     * Test autoPrefix() preserves existing prefix when optimal.
     */
    public function testAutoPrefixWithExistingPrefix(): void
    {
        $length = new Length(5, 'km');
        $prefixed = $length->autoPrefix();

        $this->assertSame(5.0, $prefixed->value);
        $this->assertSame('km', $prefixed->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Round-trip tests

    /**
     * Test expand then compact returns equivalent value.
     */
    public function testExpandCompactRoundTrip(): void
    {
        $force = new Force(10, 'N');
        $expanded = $force->expand();
        $compacted = $expanded->compact();

        $this->assertTrue($force->approxEqual($compacted));
        $this->assertSame('N', $compacted->derivedUnit->asciiSymbol);
    }

    // endregion
}
