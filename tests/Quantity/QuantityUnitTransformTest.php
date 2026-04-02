<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Quantity;

use Galaxon\Core\Traits\FloatAssertions;
use Galaxon\Quantities\Internal\QuantityType;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType\Angle;
use Galaxon\Quantities\QuantityType\Area;
use Galaxon\Quantities\QuantityType\Energy;
use Galaxon\Quantities\QuantityType\Force;
use Galaxon\Quantities\QuantityType\Frequency;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\QuantityType\Time;
use Galaxon\Quantities\QuantityType\Volume;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for transformation operations on Quantity objects.
 */
#[CoversClass(Quantity::class)]
final class QuantityUnitTransformTest extends TestCase
{
    use FloatAssertions;

    // region toSi() tests

    /**
     * Test toSi() on a length in meters (already SI).
     */
    public function testToSiAlreadySi(): void
    {
        $length = new Length(100, 'm');
        $si = $length->toSi();

        $this->assertSame(100.0, $si->value);
        $this->assertSame('m', $si->derivedUnit->asciiSymbol);
    }

    /**
     * Test toSi() on a length in kilometers converts to meters.
     */
    public function testToSiKilometers(): void
    {
        $length = new Length(1, 'km');
        $si = $length->toSi();

        $this->assertSame(1000.0, $si->value);
        $this->assertSame('m', $si->derivedUnit->asciiSymbol);
    }

    /**
     * Test toSi() on a length in feet converts to meters.
     */
    public function testToSiFeet(): void
    {
        $length = new Length(1, 'ft');
        $si = $length->toSi();

        $this->assertSame(0.3048, $si->value);
        $this->assertSame('m', $si->derivedUnit->asciiSymbol);
    }

    /**
     * Test toSi() on mass in pounds converts to kilograms.
     */
    public function testToSiPounds(): void
    {
        $mass = new Mass(1, 'lb');
        $si = $mass->toSi();

        $this->assertSame(0.45359237, $si->value);
        $this->assertSame('kg', $si->derivedUnit->asciiSymbol);
    }

    /**
     * Test toSi() then autoPrefix() chooses best prefix.
     */
    public function testToSiWithAutoPrefix(): void
    {
        $length = new Length(1, 'ft');
        $si = $length->toSi()->autoPrefix();

        $this->assertSame(304.8, $si->value);
        $this->assertSame('mm', $si->derivedUnit->asciiSymbol);
    }

    /**
     * Test toSi() does not auto-prefix large values.
     */
    public function testToSiLargeValueNoAutoPrefix(): void
    {
        $length = new Length(5000, 'm');
        $si = $length->toSi();

        $this->assertSame(5000.0, $si->value);
        $this->assertSame('m', $si->derivedUnit->asciiSymbol);
    }

    /**
     * Test toSiBase() keeps base units without simplification.
     */
    public function testToSiBaseKeepsBaseUnits(): void
    {
        $force = new Force(1, 'N');
        $si = $force->toSiBase();

        $this->assertSame(1.0, $si->value);
        $this->assertSame('kg*m/s2', $si->derivedUnit->asciiSymbol);
    }

    // endregion

    // region toBase() tests

    /**
     * Test toBase() on newton converts to SI base units.
     */
    public function testToBaseNewton(): void
    {
        $force = new Force(1, 'N');
        $base = $force->toBase();

        // N = kg⋅m⋅s⁻²
        $this->assertSame(1.0, $base->value);
        $this->assertSame('kg*m/s2', $base->derivedUnit->asciiSymbol);
    }

    /**
     * Test toBase() on joule converts to SI base units.
     */
    public function testToBaseJoule(): void
    {
        $energy = new Energy(1, 'J');
        $base = $energy->toBase();

        // J = kg⋅m²⋅s⁻²
        $this->assertSame(1.0, $base->value);
        $this->assertSame('kg*m2/s2', $base->derivedUnit->asciiSymbol);
    }

    /**
     * Test toBase() on unit already in base units is a no-op.
     */
    public function testToBaseAlreadyBase(): void
    {
        $length = new Length(10, 'm');
        $base = $length->toBase();

        $this->assertSame(10.0, $base->value);
        $this->assertSame('m', $base->derivedUnit->asciiSymbol);
    }

    /**
     * Test toBase() on English unit converts to English base units.
     */
    public function testToBaseEnglishUnit(): void
    {
        $force = new Force(1, 'lbf');
        $base = $force->toBase();

        $this->assertSame('lb*ft/s2', $base->derivedUnit->asciiSymbol);
    }

    // endregion

    // region merge() tests

    /**
     * Test merge() combines same dimension units.
     *
     * mul() does not auto-merge, so m * ft stays as m*ft.
     * merge() then consolidates to a single unit per dimension.
     */
    public function testMergeSameDimension(): void
    {
        $length1 = new Length(2, 'm');
        $length2 = new Length(3, 'ft');
        $product = $length1->mul($length2);

        // mul() keeps both unit terms as-is.
        $this->assertSame(6.0, $product->value);
        $this->assertSame('m*ft', $product->derivedUnit->asciiSymbol);

        // merge() converts to a single unit per dimension (m²).
        $merged = $product->merge();
        $this->assertSame('m2', $merged->derivedUnit->asciiSymbol);

        // Value should be 2 * (3 * 0.3048) = 1.8288 m².
        $this->assertApproxEqual(2 * 3 * 0.3048, $merged->value);
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

        $this->assertApproxEqual(5.0, $prefixed->value);
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

    // region simplify() tests

    /**
     * Test simplify() compacts base units to a named unit.
     */
    public function testSimplifyCompactsToNamedUnit(): void
    {
        $qty = new Force(1, 'kg*m*s-2');
        $simplified = $qty->simplify();

        $this->assertSame(1.0, $simplified->value);
        $this->assertSame('N', $simplified->derivedUnit->asciiSymbol);
    }

    /**
     * Test simplify() compacts to named unit without auto-prefixing.
     */
    public function testSimplifyCompactsWithoutPrefix(): void
    {
        // 5000 kg⋅m⋅s⁻² → 5000 N (no auto-prefix)
        $qty = new Force(5000, 'kg*m*s-2');
        $simplified = $qty->simplify();

        $this->assertSame(5000.0, $simplified->value);
        $this->assertSame('N', $simplified->derivedUnit->asciiSymbol);
    }

    /**
     * Test simplify() then autoPrefix() compacts and prefixes a large value.
     */
    public function testSimplifyThenAutoPrefixLargeValue(): void
    {
        // 5000 kg⋅m⋅s⁻² → 5000 N → 5 kN
        $qty = new Force(5000, 'kg*m*s-2');
        $simplified = $qty->simplify()->autoPrefix();

        $this->assertSame(5.0, $simplified->value);
        $this->assertSame('kN', $simplified->derivedUnit->asciiSymbol);
    }

    /**
     * Test simplify() then autoPrefix() compacts and prefixes a small value.
     */
    public function testSimplifyThenAutoPrefixSmallValue(): void
    {
        // 0.005 kg⋅m²⋅s⁻² → 0.005 J → 5 mJ
        $qty = new Energy(0.005, 'kg*m2*s-2');
        $simplified = $qty->simplify()->autoPrefix();

        $this->assertSame(5.0, $simplified->value);
        $this->assertSame('mJ', $simplified->derivedUnit->asciiSymbol);
    }

    /**
     * Test simplify() on a base unit is a no-op (no auto-prefix).
     */
    public function testSimplifyBaseUnitNoOp(): void
    {
        $length = new Length(5000, 'm');
        $simplified = $length->simplify();

        $this->assertSame(5000.0, $simplified->value);
        $this->assertSame('m', $simplified->derivedUnit->asciiSymbol);
    }

    /**
     * Test simplify() on an already-simple value is a no-op.
     */
    public function testSimplifyAlreadySimple(): void
    {
        $force = new Force(10, 'N');
        $simplified = $force->simplify();

        $this->assertSame(10.0, $simplified->value);
        $this->assertSame('N', $simplified->derivedUnit->asciiSymbol);
    }

    /**
     * Test simplify() on s⁻¹ compacts to Hz.
     */
    public function testSimplifyInverseSecondsToHertz(): void
    {
        $qty = new Frequency(1, 's-1');
        $simplified = $qty->simplify();

        $this->assertSame(1.0, $simplified->value);
        $this->assertSame('Hz', $simplified->derivedUnit->asciiSymbol);
    }

    /**
     * Test simplify() on large s⁻¹ compacts to Hz without auto-prefixing.
     */
    public function testSimplifyLargeInverseSecondsToHertz(): void
    {
        // 5000 s⁻¹ → 5000 Hz (no auto-prefix)
        $qty = new Frequency(5000, 's-1');
        $simplified = $qty->simplify();

        $this->assertSame(5000.0, $simplified->value);
        $this->assertSame('Hz', $simplified->derivedUnit->asciiSymbol);
    }

    /**
     * Test simplify() then autoPrefix() on large s⁻¹ gives kHz.
     */
    public function testSimplifyThenAutoPrefixInverseSecondsToKilohertz(): void
    {
        // 5000 s⁻¹ → 5000 Hz → 5 kHz
        $qty = new Frequency(5000, 's-1');
        $simplified = $qty->simplify()->autoPrefix();

        $this->assertSame(5.0, $simplified->value);
        $this->assertSame('kHz', $simplified->derivedUnit->asciiSymbol);
    }

    /**
     * Test simplify() on a negative value.
     */
    public function testSimplifyNegativeValue(): void
    {
        $qty = new Force(-3000, 'kg*m*s-2');
        $simplified = $qty->simplify();

        $this->assertSame(-3000.0, $simplified->value);
        $this->assertSame('N', $simplified->derivedUnit->asciiSymbol);
    }

    /**
     * Test simplify() on zero value.
     */
    public function testSimplifyZero(): void
    {
        $qty = new Energy(0, 'kg*m2*s-2');
        $simplified = $qty->simplify();

        $this->assertSame(0.0, $simplified->value);
        $this->assertSame('J', $simplified->derivedUnit->asciiSymbol);
    }

    /**
     * Test simplify() compacts English base units to lbf.
     */
    public function testSimplifyEnglishBaseUnitsToLbf(): void
    {
        // 1 lbf = 1 lb * g (standard gravity in ft/s2).
        $qty = Quantity::create(1, 'lb*ft/s2');
        $simplified = $qty->simplify();

        $this->assertSame('lbf', $simplified->derivedUnit->asciiSymbol);
        // 1 lb*ft/s2 ≈ 0.031081 lbf
        $this->assertApproxEqual(0.031081, $simplified->value, 1e-4);
    }

    /**
     * Test simplify() on lbf is a no-op.
     */
    public function testSimplifyLbfAlreadySimple(): void
    {
        $force = new Force(10, 'lbf');
        $simplified = $force->simplify();

        $this->assertSame(10.0, $simplified->value);
        $this->assertSame('lbf', $simplified->derivedUnit->asciiSymbol);
    }

    /**
     * Test simplify() compacts English area to acres.
     */
    public function testSimplifyEnglishAreaToAcres(): void
    {
        // 1 acre = 43560 ft2.
        $area = new Area(43560, 'ft2');
        $simplified = $area->simplify();

        $this->assertSame('ac', $simplified->derivedUnit->asciiSymbol);
        $this->assertApproxEqual(1.0, $simplified->value);
    }

    /**
     * Test simplify() on English volume produces ac*ft, not gallons.
     */
    public function testSimplifyEnglishVolumeAvoidsGallons(): void
    {
        $volume = new Volume(1, 'ft3');
        $simplified = $volume->simplify();

        // Volume should not simplify to US gal or imp gal.
        // Instead, ac (L2) fits inside L3, leaving ft as remainder.
        $this->assertSame('ac*ft', $simplified->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Round-trip tests

    /**
     * Test toBase() then simplify() returns equivalent value.
     */
    public function testBaseSimplifyRoundTrip(): void
    {
        $force = new Force(10, 'N');
        $expanded = $force->toBase();
        $simplified = $expanded->simplify();

        $this->assertTrue($force->approxEqual($simplified));
        $this->assertSame('N', $simplified->derivedUnit->asciiSymbol);
    }

    // endregion

    // region autoPrefix() edge case tests

    /**
     * Test autoPrefix() on dimensionless quantity returns same instance.
     */
    public function testAutoPrefixOnDimensionlessReturnsSelf(): void
    {
        $qty = Quantity::create(1000, '');
        $prefixed = $qty->autoPrefix();

        // Dimensionless quantities have no unit to prefix.
        $this->assertSame(1000.0, $prefixed->value);
        $this->assertSame('', $prefixed->derivedUnit->asciiSymbol);
    }

    // endregion

    // region toEnglishBase() tests

    /**
     * Test toEnglishBase() converts force to lb*ft/s2.
     */
    public function testToEnglishBaseForce(): void
    {
        $force = new Force(1, 'lbf');
        $english = $force->toEnglishBase();

        $this->assertSame('lb*ft/s2', $english->derivedUnit->asciiSymbol);
        // 1 lbf = 1 lb * 32.174049... ft/s2 (standard gravity in ft/s2)
        $this->assertApproxEqual(32.174049, $english->value, 1e-3);
    }

    /**
     * Test toEnglishBase() converts length to feet.
     */
    public function testToEnglishBaseLength(): void
    {
        $length = new Length(1, 'mi');
        $english = $length->toEnglishBase();

        $this->assertSame('ft', $english->derivedUnit->asciiSymbol);
        $this->assertSame(5280.0, $english->value);
    }

    /**
     * Test toEnglishBase() on mass converts to pounds.
     */
    public function testToEnglishBaseMass(): void
    {
        $mass = new Mass(1, 'kg');
        $english = $mass->toEnglishBase();

        $this->assertSame('lb', $english->derivedUnit->asciiSymbol);
        $this->assertApproxEqual(2.20462262, $english->value, 1e-5);
    }

    /**
     * Test toEnglishBase() on angle converts to degrees.
     */
    public function testToEnglishBaseAngle(): void
    {
        $angle = new Angle(M_PI, 'rad');
        $english = $angle->toEnglishBase();

        $this->assertSame('deg', $english->derivedUnit->asciiSymbol);
        $this->assertApproxEqual(180.0, $english->value);
    }

    /**
     * Test toEnglishBase() on time stays in seconds (no English override).
     */
    public function testToEnglishBaseTimeFallsBackToSi(): void
    {
        $time = new Time(1, 'h');
        $english = $time->toEnglishBase();

        // Time has no English base unit, falls back to SI (seconds).
        $this->assertSame('s', $english->derivedUnit->asciiSymbol);
        $this->assertSame(3600.0, $english->value);
    }

    /**
     * Test toEnglishBase() when already in English base unit.
     */
    public function testToEnglishBaseAlreadyBase(): void
    {
        $length = new Length(10, 'ft');
        $english = $length->toEnglishBase();

        $this->assertSame('ft', $english->derivedUnit->asciiSymbol);
        $this->assertSame(10.0, $english->value);
    }

    // endregion

    // region toEnglish() tests

    /**
     * Test toEnglish() on force simplifies to lbf.
     */
    public function testToEnglishForce(): void
    {
        $force = new Force(1, 'N');
        $english = $force->toEnglish();

        $this->assertSame('lbf', $english->derivedUnit->asciiSymbol);
        // 1 N = 0.224809... lbf
        $this->assertApproxEqual(0.224809, $english->value, 1e-3);
    }

    /**
     * Test toEnglish() on length in meters converts to feet.
     */
    public function testToEnglishLength(): void
    {
        $length = new Length(1, 'm');
        $english = $length->toEnglish();

        $this->assertSame('ft', $english->derivedUnit->asciiSymbol);
        $this->assertApproxEqual(3.28084, $english->value, 1e-3);
    }

    /**
     * Test toEnglish() on mass in kilograms converts to pounds.
     */
    public function testToEnglishMass(): void
    {
        $mass = new Mass(1, 'kg');
        $english = $mass->toEnglish();

        $this->assertSame('lb', $english->derivedUnit->asciiSymbol);
        $this->assertApproxEqual(2.20462262, $english->value, 1e-5);
    }

    /**
     * Test toEnglish() when already in English units.
     */
    public function testToEnglishAlreadyEnglish(): void
    {
        $length = new Length(10, 'ft');
        $english = $length->toEnglish();

        $this->assertSame('ft', $english->derivedUnit->asciiSymbol);
        $this->assertSame(10.0, $english->value);
    }

    /**
     * Test toEnglish() on energy simplifies to Btu.
     */
    public function testToEnglishEnergy(): void
    {
        $energy = new Energy(1, 'J');
        $english = $energy->toEnglish();

        // 1 J ≈ 0.000947817 Btu.
        $this->assertSame('Btu', $english->derivedUnit->asciiSymbol);
        $this->assertApproxEqual(0.000947817, $english->value, 1e-6);
    }

    /**
     * Test toEnglish() on miles simplifies to feet (base English length).
     */
    public function testToEnglishMiles(): void
    {
        $length = new Length(1, 'mi');
        $english = $length->toEnglish();

        $this->assertSame('ft', $english->derivedUnit->asciiSymbol);
        $this->assertSame(5280.0, $english->value);
    }

    // endregion

    // region getType() tests

    /**
     * Test getType() returns QuantityType for a registered subclass.
     */
    public function testGetTypeReturnsQuantityType(): void
    {
        $type = Length::getQuantityType();

        $this->assertInstanceOf(QuantityType::class, $type);
        $this->assertSame('length', $type->name);
        $this->assertSame('L', $type->dimension);
        $this->assertSame(Length::class, $type->class);
    }

    /**
     * Test getType() returns null for base Quantity class.
     */
    public function testGetTypeReturnsNullForBaseQuantity(): void
    {
        $this->assertNull(Quantity::getQuantityType());
    }

    /**
     * Test $type property matches getType() on an instance.
     */
    public function testTypePropertyMatchesGetType(): void
    {
        $length = new Length(1, 'm');

        $this->assertSame(Length::getQuantityType(), $length->quantityType);
    }

    /**
     * Test $type property is null for unregistered compound quantity.
     */
    public function testTypePropertyNullForUnregisteredDimension(): void
    {
        // A compound unit with no registered quantity type.
        $qty = Quantity::create(1, 'kg*m3');

        $this->assertNull($qty->quantityType);
    }

    // endregion
}
