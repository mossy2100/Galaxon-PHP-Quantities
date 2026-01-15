<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Unit;
use Galaxon\Quantities\UnitData;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Tests for Unit class.
 */
#[CoversClass(Unit::class)]
final class UnitTest extends TestCase
{
    // region Constructor tests

    /**
     * Test constructor with SI base unit data (metre).
     */
    public function testConstructorWithSiBaseUnit(): void
    {
        $data = [
            'asciiSymbol'  => 'm',
            'quantityType' => 'length',
            'dimension'    => 'L',
            'system'       => 'si_base',
            'prefixGroup'  => UnitData::PREFIX_GROUP_METRIC,
        ];

        $unit = new Unit('metre', $data);

        $this->assertSame('metre', $unit->name);
        $this->assertSame('m', $unit->asciiSymbol);
        $this->assertNull($unit->unicodeSymbol);
        $this->assertSame('length', $unit->quantityType);
        $this->assertSame('L', $unit->dimension);
        $this->assertSame('si_base', $unit->system);
        $this->assertSame(UnitData::PREFIX_GROUP_METRIC, $unit->prefixGroup);
        $this->assertNull($unit->siPrefix);
        $this->assertSame(1.0, $unit->expansionValue);
        $this->assertNull($unit->expansionUnit);
    }

    /**
     * Test constructor with SI named unit data (hertz).
     */
    public function testConstructorWithSiNamedUnit(): void
    {
        $data = [
            'asciiSymbol'   => 'Hz',
            'quantityType'  => 'frequency',
            'dimension'     => 'T-1',
            'system'        => 'si_named',
            'prefixGroup'   => UnitData::PREFIX_GROUP_METRIC,
            'expansionUnit' => 's-1',
        ];

        $unit = new Unit('hertz', $data);

        $this->assertSame('hertz', $unit->name);
        $this->assertSame('Hz', $unit->asciiSymbol);
        $this->assertNull($unit->unicodeSymbol);
        $this->assertSame('frequency', $unit->quantityType);
        $this->assertSame('T-1', $unit->dimension);
        $this->assertSame('si_named', $unit->system);
        $this->assertSame('s-1', $unit->expansionUnit);
        $this->assertSame(1.0, $unit->expansionValue);
    }

    /**
     * Test constructor with custom Unicode symbol (ohm).
     */
    public function testConstructorWithCustomUnicodeSymbol(): void
    {
        $data = [
            'asciiSymbol'   => 'ohm',
            'unicodeSymbol' => 'Ω',
            'quantityType'  => 'resistance',
            'dimension'     => 'T-3L2MI-2',
            'system'        => 'si_named',
            'prefixGroup'   => UnitData::PREFIX_GROUP_METRIC,
            'expansionUnit' => 'kg*m2*s-3*A-2',
        ];

        $unit = new Unit('ohm', $data);

        $this->assertSame('ohm', $unit->asciiSymbol);
        $this->assertSame('Ω', $unit->unicodeSymbol);
    }

    /**
     * Test constructor with SI prefix (gram with kilo prefix).
     */
    public function testConstructorWithSiPrefix(): void
    {
        $data = [
            'asciiSymbol'  => 'g',
            'quantityType' => 'mass',
            'dimension'    => 'M',
            'system'       => 'si_base',
            'prefixGroup'  => UnitData::PREFIX_GROUP_METRIC,
            'siPrefix'     => 'k',
        ];

        $unit = new Unit('gram', $data);

        $this->assertSame('k', $unit->siPrefix);
    }

    /**
     * Test constructor with expansion value (pound force).
     */
    public function testConstructorWithEquivalentValue(): void
    {
        $data = [
            'asciiSymbol'    => 'lbf',
            'quantityType'   => 'force',
            'dimension'      => 'T-2LM',
            'system'         => 'us_customary',
            'expansionValue' => 9.80665 / 0.3048,
            'expansionUnit'  => 'ft*lb/s2',
        ];

        $unit = new Unit('pound force', $data);

        $this->assertEqualsWithDelta(9.80665 / 0.3048, $unit->expansionValue, 1e-10);
        $this->assertSame('ft*lb/s2', $unit->expansionUnit);
    }

    /**
     * Test constructor with no prefix group (defaults to 0).
     */
    public function testConstructorWithNoPrefixGroup(): void
    {
        $data = [
            'asciiSymbol'  => 'ha',
            'quantityType' => 'area',
            'dimension'    => 'L2',
            'system'       => 'metric',
        ];

        $unit = new Unit('hectare', $data);

        $this->assertSame(0, $unit->prefixGroup);
    }

    /**
     * Test constructor normalizes dimension code.
     */
    public function testConstructorNormalizesDimension(): void
    {
        $data = [
            'asciiSymbol'  => 'N',
            'quantityType' => 'force',
            'dimension'    => 'MLT-2', // Not in canonical order
            'system'       => 'si_named',
        ];

        $unit = new Unit('newton', $data);

        // Dimension should be normalized to canonical order (T, L, M...)
        $this->assertSame('MLT-2', $unit->dimension);
    }

    // endregion

    // region Property hook tests

    /**
     * Test equivalent property returns null when expansionUnit is null.
     */
    public function testEquivalentPropertyReturnsNullWhenNoEquivalentUnit(): void
    {
        $data = [
            'asciiSymbol'  => 'm',
            'quantityType' => 'length',
            'dimension'    => 'L',
            'system'       => 'si_base',
        ];

        $unit = new Unit('metre', $data);

        $this->assertNull($unit->expansion);
    }

    /**
     * Test equivalent property returns Quantity when expansionUnit is set.
     */
    public function testEquivalentPropertyReturnsQuantity(): void
    {
        $data = [
            'asciiSymbol'   => 'Hz',
            'quantityType'  => 'frequency',
            'dimension'     => 'T-1',
            'system'        => 'si_named',
            'expansionUnit' => 's-1',
        ];

        $unit = new Unit('hertz', $data);

        $equivalent = $unit->expansion;

        $this->assertInstanceOf(Quantity::class, $equivalent);
        $this->assertSame(1.0, $equivalent->value);
        $this->assertSame('s-1', $equivalent->derivedUnit->format(true));
    }

    /**
     * Test equivalent property caches the Quantity instance.
     */
    public function testEquivalentPropertyCachesInstance(): void
    {
        $data = [
            'asciiSymbol'   => 'Hz',
            'quantityType'  => 'frequency',
            'dimension'     => 'T-1',
            'system'        => 'si_named',
            'expansionUnit' => 's-1',
        ];

        $unit = new Unit('hertz', $data);

        $equivalent1 = $unit->expansion;
        $equivalent2 = $unit->expansion;

        $this->assertSame($equivalent1, $equivalent2);
    }

    /**
     * Test equivalent property with custom expansionValue.
     */
    public function testEquivalentPropertyWithCustomValue(): void
    {
        $data = [
            'asciiSymbol'    => 'lbf',
            'quantityType'   => 'force',
            'dimension'      => 'T-2LM',
            'system'         => 'us_customary',
            'expansionValue' => 4.44822,
            'expansionUnit'  => 'N',
        ];

        $unit = new Unit('pound force', $data);

        $equivalent = $unit->expansion;

        $this->assertEqualsWithDelta(4.44822, $equivalent->value, 1e-10);
        $this->assertSame('N', $equivalent->derivedUnit->format(true));
    }

    // endregion

    // region Prefix methods tests

    /**
     * Test acceptsPrefixes returns true when prefixGroup is greater than 0.
     */
    public function testAcceptsPrefixesReturnsTrueWhenPrefixGroupSet(): void
    {
        $data = [
            'asciiSymbol'  => 'm',
            'quantityType' => 'length',
            'dimension'    => 'L',
            'system'       => 'si_base',
            'prefixGroup'  => UnitData::PREFIX_GROUP_METRIC,
        ];

        $unit = new Unit('metre', $data);

        $this->assertTrue($unit->acceptsPrefixes());
    }

    /**
     * Test acceptsPrefixes returns false when prefixGroup is 0.
     */
    public function testAcceptsPrefixesReturnsFalseWhenNoPrefixGroup(): void
    {
        $data = [
            'asciiSymbol'  => 'ha',
            'quantityType' => 'area',
            'dimension'    => 'L2',
            'system'       => 'metric',
        ];

        $unit = new Unit('hectare', $data);

        $this->assertFalse($unit->acceptsPrefixes());
    }

    /**
     * Test acceptsPrefix returns true for valid metric prefix.
     */
    public function testAcceptsPrefixReturnsTrueForValidPrefix(): void
    {
        $data = [
            'asciiSymbol'  => 'm',
            'quantityType' => 'length',
            'dimension'    => 'L',
            'system'       => 'si_base',
            'prefixGroup'  => UnitData::PREFIX_GROUP_METRIC,
        ];

        $unit = new Unit('metre', $data);

        $this->assertTrue($unit->acceptsPrefix('k'));
        $this->assertTrue($unit->acceptsPrefix('m'));
        $this->assertTrue($unit->acceptsPrefix('c'));
        $this->assertTrue($unit->acceptsPrefix('M'));
        $this->assertTrue($unit->acceptsPrefix('G'));
    }

    /**
     * Test acceptsPrefix returns false for invalid prefix.
     */
    public function testAcceptsPrefixReturnsFalseForInvalidPrefix(): void
    {
        $data = [
            'asciiSymbol'  => 'm',
            'quantityType' => 'length',
            'dimension'    => 'L',
            'system'       => 'si_base',
            'prefixGroup'  => UnitData::PREFIX_GROUP_METRIC,
        ];

        $unit = new Unit('metre', $data);

        $this->assertFalse($unit->acceptsPrefix('invalid'));
        $this->assertFalse($unit->acceptsPrefix('Ki')); // Binary prefix not in METRIC group
    }

    /**
     * Test acceptsPrefix returns false when no prefixes allowed.
     */
    public function testAcceptsPrefixReturnsFalseWhenNoPrefixesAllowed(): void
    {
        $data = [
            'asciiSymbol'  => 'ha',
            'quantityType' => 'area',
            'dimension'    => 'L2',
            'system'       => 'metric',
        ];

        $unit = new Unit('hectare', $data);

        $this->assertFalse($unit->acceptsPrefix('k'));
    }

    /**
     * Test acceptsPrefix with small metric prefixes only.
     */
    public function testAcceptsPrefixWithSmallMetricOnly(): void
    {
        $data = [
            'asciiSymbol'  => 'rad',
            'quantityType' => 'angle',
            'dimension'    => 'A',
            'system'       => 'si_derived',
            'prefixGroup'  => UnitData::PREFIX_GROUP_SMALL_METRIC,
        ];

        $unit = new Unit('radian', $data);

        // Small metric prefixes should be accepted
        $this->assertTrue($unit->acceptsPrefix('m'));
        $this->assertTrue($unit->acceptsPrefix('c'));
        $this->assertTrue($unit->acceptsPrefix('n'));

        // Large metric prefixes should not be accepted
        $this->assertFalse($unit->acceptsPrefix('k'));
        $this->assertFalse($unit->acceptsPrefix('M'));
    }

    /**
     * Test acceptsPrefix with large metric prefixes only.
     */
    public function testAcceptsPrefixWithLargeMetricOnly(): void
    {
        $data = [
            'asciiSymbol'  => 'cal',
            'quantityType' => 'energy',
            'dimension'    => 'T-2L2M',
            'system'       => 'metric',
            'prefixGroup'  => UnitData::PREFIX_GROUP_LARGE_METRIC,
        ];

        $unit = new Unit('calorie', $data);

        // Large metric prefixes should be accepted
        $this->assertTrue($unit->acceptsPrefix('k'));
        $this->assertTrue($unit->acceptsPrefix('M'));

        // Small metric prefixes should not be accepted
        $this->assertFalse($unit->acceptsPrefix('m'));
        $this->assertFalse($unit->acceptsPrefix('c'));
    }

    /**
     * Test acceptsPrefix with binary prefixes.
     */
    public function testAcceptsPrefixWithBinaryPrefixes(): void
    {
        $data = [
            'asciiSymbol'  => 'B',
            'quantityType' => 'data',
            'dimension'    => 'D',
            'system'       => 'metric',
            'prefixGroup'  => UnitData::PREFIX_GROUP_LARGE,
        ];

        $unit = new Unit('byte', $data);

        // Binary prefixes should be accepted
        $this->assertTrue($unit->acceptsPrefix('Ki'));
        $this->assertTrue($unit->acceptsPrefix('Mi'));
        $this->assertTrue($unit->acceptsPrefix('Gi'));

        // Large metric prefixes should also be accepted
        $this->assertTrue($unit->acceptsPrefix('k'));
        $this->assertTrue($unit->acceptsPrefix('M'));
        $this->assertTrue($unit->acceptsPrefix('G'));

        // Small metric prefixes should not be accepted
        $this->assertFalse($unit->acceptsPrefix('m'));
        $this->assertFalse($unit->acceptsPrefix('c'));
    }

    /**
     * Test getAllowedPrefixes returns correct prefixes for metric units.
     */
    public function testGetAllowedPrefixesForMetricUnit(): void
    {
        $data = [
            'asciiSymbol'  => 'm',
            'quantityType' => 'length',
            'dimension'    => 'L',
            'system'       => 'si_base',
            'prefixGroup'  => UnitData::PREFIX_GROUP_METRIC,
        ];

        $unit = new Unit('metre', $data);

        $prefixes = $unit->getAllowedPrefixes();

        $this->assertArrayHasKey('k', $prefixes);
        $this->assertArrayHasKey('m', $prefixes);
        $this->assertArrayHasKey('c', $prefixes);
        $this->assertArrayHasKey('M', $prefixes);
        $this->assertArrayHasKey('G', $prefixes);
        $this->assertArrayHasKey('n', $prefixes);
        $this->assertSame(1e3, $prefixes['k']);
        $this->assertSame(1e-3, $prefixes['m']);
    }

    /**
     * Test getAllowedPrefixes returns empty array when no prefixes allowed.
     */
    public function testGetAllowedPrefixesReturnsEmptyArray(): void
    {
        $data = [
            'asciiSymbol'  => 'ha',
            'quantityType' => 'area',
            'dimension'    => 'L2',
            'system'       => 'metric',
        ];

        $unit = new Unit('hectare', $data);

        $prefixes = $unit->getAllowedPrefixes();

        $this->assertSame([], $prefixes);
    }

    // endregion

    // region Inspection methods tests

    /**
     * Test isSiBase returns true for SI base unit.
     */
    public function testIsSiBaseReturnsTrueForSiBaseUnit(): void
    {
        $data = [
            'asciiSymbol'  => 'm',
            'quantityType' => 'length',
            'dimension'    => 'L',
            'system'       => 'si_base',
        ];

        $unit = new Unit('metre', $data);

        $this->assertTrue($unit->isSiBase());
    }

    /**
     * Test isSiBase returns false for non-SI base unit.
     */
    public function testIsSiBaseReturnsFalseForNonSiBase(): void
    {
        $data = [
            'asciiSymbol'  => 'Hz',
            'quantityType' => 'frequency',
            'dimension'    => 'T-1',
            'system'       => 'si_named',
        ];

        $unit = new Unit('hertz', $data);

        $this->assertFalse($unit->isSiBase());
    }

    /**
     * Test isSiDerived returns true for SI derived unit.
     */
    public function testIsSiDerivedReturnsTrueForSiDerivedUnit(): void
    {
        $data = [
            'asciiSymbol'  => 'rad',
            'quantityType' => 'angle',
            'dimension'    => 'A',
            'system'       => 'si_derived',
        ];

        $unit = new Unit('radian', $data);

        $this->assertTrue($unit->isSiDerived());
    }

    /**
     * Test isSiDerived returns false for non-SI derived unit.
     */
    public function testIsSiDerivedReturnsFalseForNonSiDerived(): void
    {
        $data = [
            'asciiSymbol'  => 'm',
            'quantityType' => 'length',
            'dimension'    => 'L',
            'system'       => 'si_base',
        ];

        $unit = new Unit('metre', $data);

        $this->assertFalse($unit->isSiDerived());
    }

    /**
     * Test isSiNamed returns true for SI named unit.
     */
    public function testIsSiNamedReturnsTrueForSiNamedUnit(): void
    {
        $data = [
            'asciiSymbol'  => 'Hz',
            'quantityType' => 'frequency',
            'dimension'    => 'T-1',
            'system'       => 'si_named',
        ];

        $unit = new Unit('hertz', $data);

        $this->assertTrue($unit->isSiNamed());
    }

    /**
     * Test isSiNamed returns false for non-SI named unit.
     */
    public function testIsSiNamedReturnsFalseForNonSiNamed(): void
    {
        $data = [
            'asciiSymbol'  => 'm',
            'quantityType' => 'length',
            'dimension'    => 'L',
            'system'       => 'si_base',
        ];

        $unit = new Unit('metre', $data);

        $this->assertFalse($unit->isSiNamed());
    }

    /**
     * Test isSi returns true for any SI unit type.
     */
    public function testIsSiReturnsTrueForAnySiUnit(): void
    {
        $siBaseData = [
            'asciiSymbol'  => 'm',
            'quantityType' => 'length',
            'dimension'    => 'L',
            'system'       => 'si_base',
        ];

        $siDerivedData = [
            'asciiSymbol'  => 'rad',
            'quantityType' => 'angle',
            'dimension'    => 'A',
            'system'       => 'si_derived',
        ];

        $siNamedData = [
            'asciiSymbol'  => 'Hz',
            'quantityType' => 'frequency',
            'dimension'    => 'T-1',
            'system'       => 'si_named',
        ];

        $siBase = new Unit('metre', $siBaseData);
        $siDerived = new Unit('radian', $siDerivedData);
        $siNamed = new Unit('hertz', $siNamedData);

        $this->assertTrue($siBase->isSi());
        $this->assertTrue($siDerived->isSi());
        $this->assertTrue($siNamed->isSi());
    }

    /**
     * Test isSi returns false for non-SI units.
     */
    public function testIsSiReturnsFalseForNonSiUnits(): void
    {
        $metricData = [
            'asciiSymbol'  => 'L',
            'quantityType' => 'volume',
            'dimension'    => 'L3',
            'system'       => 'metric',
        ];

        $usCustomaryData = [
            'asciiSymbol'  => 'ft',
            'quantityType' => 'length',
            'dimension'    => 'L',
            'system'       => 'us_customary',
        ];

        $metric = new Unit('litre', $metricData);
        $usCustomary = new Unit('foot', $usCustomaryData);

        $this->assertFalse($metric->isSi());
        $this->assertFalse($usCustomary->isSi());
    }

    /**
     * Test isMetric returns true for SI units.
     */
    public function testIsMetricReturnsTrueForSiUnits(): void
    {
        $data = [
            'asciiSymbol'  => 'm',
            'quantityType' => 'length',
            'dimension'    => 'L',
            'system'       => 'si_base',
        ];

        $unit = new Unit('metre', $data);

        $this->assertTrue($unit->isMetric());
    }

    /**
     * Test isMetric returns true for non-SI metric units.
     */
    public function testIsMetricReturnsTrueForNonSiMetricUnits(): void
    {
        $data = [
            'asciiSymbol'  => 'L',
            'quantityType' => 'volume',
            'dimension'    => 'L3',
            'system'       => 'metric',
        ];

        $unit = new Unit('litre', $data);

        $this->assertTrue($unit->isMetric());
    }

    /**
     * Test isMetric returns false for US customary units.
     */
    public function testIsMetricReturnsFalseForUsCustomaryUnits(): void
    {
        $data = [
            'asciiSymbol'  => 'ft',
            'quantityType' => 'length',
            'dimension'    => 'L',
            'system'       => 'us_customary',
        ];

        $unit = new Unit('foot', $data);

        $this->assertFalse($unit->isMetric());
    }

    // endregion

    // region Formatting methods tests

    /**
     * Test __toString returns the Unicode symbol.
     */
    public function testToStringReturnsUnicodeSymbol(): void
    {
        $data = [
            'asciiSymbol'  => 'm',
            'quantityType' => 'length',
            'dimension'    => 'L',
            'system'       => 'si_base',
        ];

        $unit = new Unit('metre', $data);

        $this->assertSame('m', (string)$unit);
    }

    /**
     * Test __toString returns Unicode symbol when different from ASCII symbol.
     */
    public function testToStringReturnsUnicodeSymbolWhenDifferent(): void
    {
        $data = [
            'asciiSymbol'   => 'ohm',
            'unicodeSymbol' => 'Ω',
            'quantityType'  => 'resistance',
            'dimension'     => 'T-3L2MI-2',
            'system'        => 'si_named',
        ];

        $unit = new Unit('ohm', $data);

        $this->assertSame('Ω', (string)$unit);
    }

    /**
     * Test format() returns the Unicode symbol by default.
     */
    public function testFormatReturnsUnicodeSymbol(): void
    {
        $data = [
            'asciiSymbol'   => 'ohm',
            'unicodeSymbol' => 'Ω',
            'quantityType'  => 'resistance',
            'dimension'     => 'T-3L2MI-2',
            'system'        => 'si_named',
        ];

        $unit = new Unit('ohm', $data);

        $this->assertSame('Ω', $unit->format());
        $this->assertSame('Ω', $unit->format(false));
    }

    /**
     * Test format(true) returns the ASCII symbol.
     */
    public function testFormatAsciiReturnsSymbol(): void
    {
        $data = [
            'asciiSymbol'   => 'ohm',
            'unicodeSymbol' => 'Ω',
            'quantityType'  => 'resistance',
            'dimension'     => 'T-3L2MI-2',
            'system'        => 'si_named',
        ];

        $unit = new Unit('ohm', $data);

        $this->assertSame('ohm', $unit->format(true));
    }

    /**
     * Test format() returns symbol when format is not specified.
     */
    public function testFormatReturnsSymbolWhenNoFormatSpecified(): void
    {
        $data = [
            'asciiSymbol'  => 'm',
            'quantityType' => 'length',
            'dimension'    => 'L',
            'system'       => 'si_base',
        ];

        $unit = new Unit('metre', $data);

        $this->assertSame('m', $unit->format());
        $this->assertSame('m', $unit->format(true));
    }

    /**
     * Test format() with degree symbol.
     */
    public function testFormatWithDegreeSymbol(): void
    {
        $data = [
            'asciiSymbol'   => 'deg',
            'unicodeSymbol' => '°',
            'quantityType'  => 'angle',
            'dimension'     => 'A',
            'system'        => 'metric',
        ];

        $unit = new Unit('degree', $data);

        $this->assertSame('°', (string)$unit);
        $this->assertSame('°', $unit->format());
        $this->assertSame('deg', $unit->format(true));
    }

    // endregion

    // region Comparison methods tests

    /**
     * Test equal returns true for same BaseUnit instance.
     */
    public function testEqualReturnsTrueForSameInstance(): void
    {
        $data = [
            'asciiSymbol'  => 'm',
            'quantityType' => 'length',
            'dimension'    => 'L',
            'system'       => 'si_base',
        ];

        $unit = new Unit('metre', $data);

        $this->assertTrue($unit->equal($unit));
    }

    /**
     * Test equal returns true for different instances with same name.
     */
    public function testEqualReturnsTrueForSameName(): void
    {
        $data = [
            'asciiSymbol'  => 'm',
            'quantityType' => 'length',
            'dimension'    => 'L',
            'system'       => 'si_base',
        ];

        $unit1 = new Unit('metre', $data);
        $unit2 = new Unit('metre', $data);

        $this->assertTrue($unit1->equal($unit2));
    }

    /**
     * Test equal returns false for different unit names.
     */
    public function testEqualReturnsFalseForDifferentNames(): void
    {
        $metreData = [
            'asciiSymbol'  => 'm',
            'quantityType' => 'length',
            'dimension'    => 'L',
            'system'       => 'si_base',
        ];

        $footData = [
            'asciiSymbol'  => 'ft',
            'quantityType' => 'length',
            'dimension'    => 'L',
            'system'       => 'us_customary',
        ];

        $metre = new Unit('metre', $metreData);
        $foot = new Unit('foot', $footData);

        $this->assertFalse($metre->equal($foot));
    }

    /**
     * Test equal returns false for different types.
     */
    public function testEqualReturnsFalseForDifferentTypes(): void
    {
        $data = [
            'asciiSymbol'  => 'm',
            'quantityType' => 'length',
            'dimension'    => 'L',
            'system'       => 'si_base',
        ];

        $unit = new Unit('metre', $data);

        $this->assertFalse($unit->equal('m'));
        $this->assertFalse($unit->equal(1));
        $this->assertFalse($unit->equal(null));
        $this->assertFalse($unit->equal(new stdClass()));
    }

    /**
     * Test equal is symmetric.
     */
    public function testEqualIsSymmetric(): void
    {
        $data1 = [
            'asciiSymbol'  => 'm',
            'quantityType' => 'length',
            'dimension'    => 'L',
            'system'       => 'si_base',
        ];

        $data2 = [
            'asciiSymbol'  => 'ft',
            'quantityType' => 'length',
            'dimension'    => 'L',
            'system'       => 'us_customary',
        ];

        $metre1 = new Unit('metre', $data1);
        $metre2 = new Unit('metre', $data1);
        $foot = new Unit('foot', $data2);

        // Same name: both directions should be true
        $this->assertSame($metre1->equal($metre2), $metre2->equal($metre1));

        // Different name: both directions should be false
        $this->assertSame($metre1->equal($foot), $foot->equal($metre1));
    }

    // endregion

    // region Integration tests with UnitData

    /**
     * Test creating BaseUnit from actual UnitData::UNITS data.
     */
    public function testCreateFromUnitDataMetre(): void
    {
        $data = UnitData::UNITS['metre'];
        $unit = new Unit('metre', $data);

        $this->assertSame('metre', $unit->name);
        $this->assertSame('m', $unit->asciiSymbol);
        $this->assertSame('length', $unit->quantityType);
        $this->assertSame('L', $unit->dimension);
        $this->assertSame('si_base', $unit->system);
        $this->assertTrue($unit->acceptsPrefixes());
        $this->assertTrue($unit->isSiBase());
        $this->assertTrue($unit->isSi());
        $this->assertTrue($unit->isMetric());
    }

    /**
     * Test creating BaseUnit from actual UnitData::UNITS data for gram.
     */
    public function testCreateFromUnitDataGram(): void
    {
        $data = UnitData::UNITS['gram'];
        $unit = new Unit('gram', $data);

        $this->assertSame('gram', $unit->name);
        $this->assertSame('g', $unit->asciiSymbol);
        $this->assertSame('k', $unit->siPrefix);
        $this->assertTrue($unit->isSiBase());
    }

    /**
     * Test creating BaseUnit from actual UnitData::UNITS data for ohm.
     */
    public function testCreateFromUnitDataOhm(): void
    {
        $data = UnitData::UNITS['ohm'];
        $unit = new Unit('ohm', $data);

        $this->assertSame('ohm', $unit->asciiSymbol);
        $this->assertSame('Ω', $unit->unicodeSymbol);
        $this->assertSame('Ω', $unit->format());
        $this->assertSame('Ω', (string)$unit);
        $this->assertSame('ohm', $unit->format(true));
        $this->assertTrue($unit->isSiNamed());
    }

    /**
     * Test creating BaseUnit from actual UnitData::UNITS data for byte.
     */
    public function testCreateFromUnitDataByte(): void
    {
        $data = UnitData::UNITS['byte'];
        $unit = new Unit('byte', $data);

        $this->assertSame('byte', $unit->name);
        $this->assertSame('B', $unit->asciiSymbol);
        $this->assertSame('data', $unit->quantityType);

        // Should accept both binary and large metric prefixes
        $this->assertTrue($unit->acceptsPrefix('Ki'));
        $this->assertTrue($unit->acceptsPrefix('Mi'));
        $this->assertTrue($unit->acceptsPrefix('k'));
        $this->assertTrue($unit->acceptsPrefix('M'));

        // Should not accept small metric prefixes
        $this->assertFalse($unit->acceptsPrefix('m'));
        $this->assertFalse($unit->acceptsPrefix('c'));
    }

    // endregion
}
