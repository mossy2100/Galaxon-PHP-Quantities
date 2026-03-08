<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Quantity;

use Galaxon\Core\Traits\FloatAssertions;
use Galaxon\Quantities\Internal\QuantityType;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType\Angle;
use Galaxon\Quantities\QuantityType\Energy;
use Galaxon\Quantities\QuantityType\Force;
use Galaxon\Quantities\QuantityType\Frequency;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\QuantityType\Time;
use Galaxon\Quantities\Services\UnitService;
use Galaxon\Quantities\UnitSystem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for transformation operations on Quantity objects.
 */
#[CoversClass(Quantity::class)]
final class QuantityTransformTest extends TestCase
{
    use FloatAssertions;

    // region Setup

    public static function setUpBeforeClass(): void
    {
        // Load Imperial/US units for cross-system tests.
        UnitService::loadSystem(UnitSystem::Imperial);
        UnitService::loadSystem(UnitSystem::UsCustomary);
    }

    // endregion

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
     * Test toSi() on a length in kilometers (autoPrefix keeps it as km).
     */
    public function testToSiKilometers(): void
    {
        $length = new Length(1, 'km');
        $si = $length->toSi();

        $this->assertSame(1.0, $si->value);
        $this->assertSame('km', $si->derivedUnit->asciiSymbol);
    }

    /**
     * Test toSi() on a length in feet (autoPrefix chooses mm).
     */
    public function testToSiFeet(): void
    {
        $length = new Length(1, 'ft');
        $si = $length->toSi();

        $this->assertSame(304.8, $si->value);
        $this->assertSame('mm', $si->derivedUnit->asciiSymbol);
    }

    /**
     * Test toSi() on mass in pounds (autoPrefix chooses g).
     */
    public function testToSiPounds(): void
    {
        $mass = new Mass(1, 'lb');
        $si = $mass->toSi();

        $this->assertSame(453.59237, $si->value);
        $this->assertSame('g', $si->derivedUnit->asciiSymbol);
    }

    /**
     * Test toSi() without autoPrefix.
     */
    public function testToSiWithoutAutoPrefix(): void
    {
        $length = new Length(5000, 'm');
        $si = $length->toSi(true, false);

        $this->assertSame(5000.0, $si->value);
        $this->assertSame('m', $si->derivedUnit->asciiSymbol);
    }

    /**
     * Test toSi() without simplify keeps base units.
     */
    public function testToSiWithoutSimplify(): void
    {
        $force = new Force(1, 'N');
        $si = $force->toSiBase();

        $this->assertSame(1.0, $si->value);
        $this->assertSame('kg*m/s2', $si->derivedUnit->asciiSymbol);
    }

    /**
     * Test toSi() with simplify=false expands but does not compact back to named unit.
     */
    public function testToSiWithSimplifyFalse(): void
    {
        $force = new Force(1, 'N');
        $si = $force->toSi(simplify: false);

        // Should remain in base units (not compacted back to N).
        $this->assertSame(1.0, $si->value);
        $this->assertSame('kg*m/s2', $si->derivedUnit->asciiSymbol);
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
        $this->assertSame('kg*m/s2', $expanded->derivedUnit->asciiSymbol);
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
        $this->assertSame('kg*m2/s2', $expanded->derivedUnit->asciiSymbol);
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

    /**
     * Test expand() on English unit falls back to English base units.
     */
    public function testExpandEnglishUnitFallsBackToEnglishBase(): void
    {
        $force = new Force(1, 'lbf');
        $expanded = $force->expand();

        $this->assertSame('lb*ft/s2', $expanded->derivedUnit->asciiSymbol);
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
     * Test simplify() compacts and auto-prefixes a large value.
     */
    public function testSimplifyCompactsAndPrefixes(): void
    {
        // 5000 kg⋅m⋅s⁻² → 5000 N → 5 kN
        $qty = new Force(5000, 'kg*m*s-2');
        $simplified = $qty->simplify();

        $this->assertSame(5.0, $simplified->value);
        $this->assertSame('kN', $simplified->derivedUnit->asciiSymbol);
    }

    /**
     * Test simplify() compacts and auto-prefixes a small value.
     */
    public function testSimplifyCompactsAndPrefixesSmall(): void
    {
        // 0.005 kg⋅m²⋅s⁻² → 0.005 J → 5 mJ
        $qty = new Energy(0.005, 'kg*m2*s-2');
        $simplified = $qty->simplify();

        $this->assertSame(5.0, $simplified->value);
        $this->assertSame('mJ', $simplified->derivedUnit->asciiSymbol);
    }

    /**
     * Test simplify() on a base unit applies auto-prefix only.
     */
    public function testSimplifyBaseUnitAutoPrefixOnly(): void
    {
        $length = new Length(5000, 'm');
        $simplified = $length->simplify();

        $this->assertSame(5.0, $simplified->value);
        $this->assertSame('km', $simplified->derivedUnit->asciiSymbol);
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
     * Test simplify() on large s⁻¹ compacts to Hz with autoPrefix.
     */
    public function testSimplifyInverseSecondsToKilohertz(): void
    {
        // 5000 s⁻¹ → 5000 Hz → 5 kHz
        $qty = new Frequency(5000, 's-1');
        $simplified = $qty->simplify();

        // Note: Hz special case in simplify() should apply autoPrefix
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

        $this->assertSame(-3.0, $simplified->value);
        $this->assertSame('kN', $simplified->derivedUnit->asciiSymbol);
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

    // endregion

    // region Round-trip tests

    /**
     * Test expand() then simplify() returns equivalent value.
     */
    public function testExpandCompactRoundTrip(): void
    {
        $force = new Force(10, 'N');
        $expanded = $force->expand();
        $simplified = $expanded->simplify();

        $this->assertTrue($force->approxEqual($simplified));
        $this->assertSame('N', $simplified->derivedUnit->asciiSymbol);
    }

    // endregion

    // region withValue() tests

    /**
     * Test withValue() returns same instance when value unchanged.
     */
    public function testWithValueReturnsSameInstanceWhenUnchanged(): void
    {
        $length = new Length(10, 'm');
        $result = $length->withValue(10.0);

        $this->assertSame($length, $result);
    }

    /**
     * Test withValue() returns new instance when value changed.
     */
    public function testWithValueReturnsNewInstanceWhenChanged(): void
    {
        $length = new Length(10, 'm');
        $result = $length->withValue(20.0);

        $this->assertNotSame($length, $result);
        $this->assertSame(20.0, $result->value);
        $this->assertSame('m', $result->derivedUnit->asciiSymbol);
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
