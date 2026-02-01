<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests;

use Error;
use Galaxon\Quantities\DerivedUnit;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\Unit;
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
            'prefixGroup'  => PrefixRegistry::GROUP_CODE_METRIC,
        ];

        $unit = new Unit('metre', $data);

        $this->assertSame('metre', $unit->name);
        $this->assertSame('m', $unit->asciiSymbol);
        $this->assertSame('m', $unit->unicodeSymbol);
        $this->assertSame('length', $unit->quantityType);
        $this->assertSame('L', $unit->dimension);
        $this->assertSame('si_base', $unit->system);
        $this->assertSame(PrefixRegistry::GROUP_CODE_METRIC, $unit->prefixGroup);
        $this->assertNull($unit->expansionUnitSymbol);
    }

    /**
     * Test constructor with SI named unit data (hertz).
     */
    public function testConstructorWithSiNamedUnit(): void
    {
        $data = [
            'asciiSymbol'         => 'Hz',
            'quantityType'        => 'frequency',
            'dimension'           => 'T-1',
            'system'              => 'si_named',
            'prefixGroup'         => PrefixRegistry::GROUP_CODE_METRIC,
            'expansionUnitSymbol' => 's-1',
        ];

        $unit = new Unit('hertz', $data);

        $this->assertSame('hertz', $unit->name);
        $this->assertSame('Hz', $unit->asciiSymbol);
        $this->assertSame('Hz', $unit->unicodeSymbol);
        $this->assertSame('frequency', $unit->quantityType);
        $this->assertSame('T-1', $unit->dimension);
        $this->assertSame('si_named', $unit->system);
        $this->assertSame('s-1', $unit->expansionUnitSymbol);
    }

    /**
     * Test constructor with custom Unicode symbol (ohm).
     */
    public function testConstructorWithCustomUnicodeSymbol(): void
    {
        $data = [
            'asciiSymbol'         => 'ohm',
            'unicodeSymbol'       => 'Ω',
            'quantityType'        => 'resistance',
            'dimension'           => 'T-3L2MI-2',
            'system'              => 'si_named',
            'prefixGroup'         => PrefixRegistry::GROUP_CODE_METRIC,
            'expansionUnitSymbol' => 'kg*m2*s-3*A-2',
        ];

        $unit = new Unit('ohm', $data);

        $this->assertSame('ohm', $unit->asciiSymbol);
        $this->assertSame('Ω', $unit->unicodeSymbol);
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
     * Test asciiSymbol property can be read.
     */
    public function testAsciiSymbolPropertyIsReadable(): void
    {
        $data = [
            'asciiSymbol'  => 'm',
            'quantityType' => 'length',
            'dimension'    => 'L',
            'system'       => 'si_base',
        ];

        $unit = new Unit('metre', $data);

        $this->assertSame('m', $unit->asciiSymbol);
    }

    /**
     * Test unicodeSymbol property can be read.
     */
    public function testUnicodeSymbolPropertyIsReadable(): void
    {
        $data = [
            'asciiSymbol'   => 'ohm',
            'unicodeSymbol' => 'Ω',
            'quantityType'  => 'resistance',
            'dimension'     => 'T-3L2MI-2',
            'system'        => 'si_named',
        ];

        $unit = new Unit('ohm', $data);

        $this->assertSame('Ω', $unit->unicodeSymbol);
    }

    /**
     * Test unicodeSymbol defaults to asciiSymbol when not specified.
     */
    public function testUnicodeSymbolDefaultsToAsciiSymbol(): void
    {
        $data = [
            'asciiSymbol'  => 'm',
            'quantityType' => 'length',
            'dimension'    => 'L',
            'system'       => 'si_base',
        ];

        $unit = new Unit('metre', $data);

        $this->assertSame('m', $unit->unicodeSymbol);
    }

    /**
     * Test dimension property can be read.
     */
    public function testDimensionPropertyIsReadable(): void
    {
        $data = [
            'asciiSymbol'  => 'm',
            'quantityType' => 'length',
            'dimension'    => 'L',
            'system'       => 'si_base',
        ];

        $unit = new Unit('metre', $data);

        $this->assertSame('L', $unit->dimension);
    }

    /**
     * Test asciiSymbol property cannot be written.
     */
    public function testAsciiSymbolPropertyIsNotWritable(): void
    {
        $data = [
            'asciiSymbol'  => 'm',
            'quantityType' => 'length',
            'dimension'    => 'L',
            'system'       => 'si_base',
        ];

        $unit = new Unit('metre', $data);

        $this->expectException(Error::class);
        $unit->asciiSymbol = 'km';
    }

    /**
     * Test unicodeSymbol property cannot be written.
     */
    public function testUnicodeSymbolPropertyIsNotWritable(): void
    {
        $data = [
            'asciiSymbol'  => 'm',
            'quantityType' => 'length',
            'dimension'    => 'L',
            'system'       => 'si_base',
        ];

        $unit = new Unit('metre', $data);

        $this->expectException(Error::class);
        $unit->unicodeSymbol = 'km';
    }

    /**
     * Test dimension property cannot be written.
     */
    public function testDimensionPropertyIsNotWritable(): void
    {
        $data = [
            'asciiSymbol'  => 'm',
            'quantityType' => 'length',
            'dimension'    => 'L',
            'system'       => 'si_base',
        ];

        $unit = new Unit('metre', $data);

        $this->expectException(Error::class);
        $unit->dimension = 'M';
    }

    /**
     * Test equivalent property returns null when expansionUnitSymbol is null.
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

        $this->assertNull($unit->expansionUnitSymbol);
    }

    /**
     * Test equivalent property returns Quantity when expansionUnitSymbol is set.
     */
    public function testEquivalentPropertyReturnsQuantity(): void
    {
        $data = [
            'asciiSymbol'         => 'Hz',
            'quantityType'        => 'frequency',
            'dimension'           => 'T-1',
            'system'              => 'si_named',
            'expansionUnitSymbol' => 's-1',
        ];

        $unit = new Unit('hertz', $data);

        $expansionUnit = $unit->expansionUnit;

        $this->assertInstanceOf(DerivedUnit::class, $expansionUnit);
        $this->assertSame('s-1', $expansionUnit->format(true));
    }

    /**
     * Test equivalent property caches the Quantity instance.
     */
    public function testEquivalentPropertyCachesInstance(): void
    {
        $data = [
            'asciiSymbol'         => 'Hz',
            'quantityType'        => 'frequency',
            'dimension'           => 'T-1',
            'system'              => 'si_named',
            'expansionUnitSymbol' => 's-1',
        ];

        $unit = new Unit('hertz', $data);

        $expansion1 = $unit->expansionUnitSymbol;
        $expansion2 = $unit->expansionUnitSymbol;

        $this->assertSame($expansion1, $expansion2);
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
            'prefixGroup'  => PrefixRegistry::GROUP_CODE_METRIC,
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
            'prefixGroup'  => PrefixRegistry::GROUP_CODE_METRIC,
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
            'prefixGroup'  => PrefixRegistry::GROUP_CODE_METRIC,
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
            'prefixGroup'  => PrefixRegistry::GROUP_CODE_SMALL_METRIC,
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
            'prefixGroup'  => PrefixRegistry::GROUP_CODE_LARGE_METRIC,
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
            'prefixGroup'  => PrefixRegistry::GROUP_CODE_LARGE,
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
            'prefixGroup'  => PrefixRegistry::GROUP_CODE_METRIC,
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
        $data = [
            'asciiSymbol'  => 'm',
            'dimension'    => 'L',
            'system'       => 'si_base',
            'prefixGroup'  => PrefixRegistry::GROUP_CODE_METRIC,
            'quantityType' => 'length',
        ];
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
        $data = [
            'asciiSymbol'  => 'g',
            'dimension'    => 'M',
            'system'       => 'si_base',
            'prefixGroup'  => PrefixRegistry::GROUP_CODE_METRIC,
            'quantityType' => 'mass',
        ];
        $unit = new Unit('gram', $data);

        $this->assertSame('gram', $unit->name);
        $this->assertSame('g', $unit->asciiSymbol);
        $this->assertTrue($unit->isSiBase());
    }

    /**
     * Test creating BaseUnit from actual UnitData::UNITS data for ohm.
     */
    public function testCreateFromUnitDataOhm(): void
    {
        $data = [
            'asciiSymbol'         => 'ohm',
            'unicodeSymbol'       => 'Ω',
            'dimension'           => 'T-3L2MI-2',
            'system'              => 'si_named',
            'prefixGroup'         => PrefixRegistry::GROUP_CODE_METRIC,
            'expansionUnitSymbol' => 'kg*m2*s-3*A-2',
            'quantityType'        => 'resistance',
        ];
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
        $data = [
            'asciiSymbol'  => 'B',
            'dimension'    => 'D',
            'system'       => 'metric',
            'prefixGroup'  => PrefixRegistry::GROUP_CODE_LARGE,
            'quantityType' => 'data',
        ];
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
