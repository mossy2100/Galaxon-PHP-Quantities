<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Internal;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Quantities\Internal\Unit;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Tests for Unit class.
 */
#[CoversClass(Unit::class)]
final class UnitTest extends TestCase
{
    // region Setup

    public static function setUpBeforeClass(): void
    {
        // Load units for tests.
        UnitRegistry::loadSystem(System::Imperial);
    }

    // endregion

    // region Constructor tests

    /**
     * Test constructor with SI base unit data (meter).
     */
    public function testConstructorWithSiBaseUnit(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            prefixGroup: PrefixRegistry::GROUP_METRIC,
            systems: [System::Si]
        );

        $this->assertSame('meter', $unit->name);
        $this->assertSame('m', $unit->asciiSymbol);
        $this->assertSame('m', $unit->unicodeSymbol);
        $this->assertSame('L', $unit->dimension);
        $this->assertContains(System::Si, $unit->systems);
        $this->assertSame(PrefixRegistry::GROUP_METRIC, $unit->prefixGroup);
    }

    /**
     * Test constructor with SI named unit data (hertz).
     */
    public function testConstructorWithSiNamedUnit(): void
    {
        $unit = new Unit(
            name: 'hertz',
            asciiSymbol: 'Hz',
            dimension: 'T-1',
            prefixGroup: PrefixRegistry::GROUP_METRIC,
            systems: [System::Si]
        );

        $this->assertSame('hertz', $unit->name);
        $this->assertSame('Hz', $unit->asciiSymbol);
        $this->assertSame('Hz', $unit->unicodeSymbol);
        $this->assertSame('T-1', $unit->dimension);
    }

    /**
     * Test constructor with custom Unicode symbol (ohm).
     */
    public function testConstructorWithCustomUnicodeSymbol(): void
    {
        $unit = new Unit(
            name: 'ohm',
            asciiSymbol: 'ohm',
            unicodeSymbol: 'Ω',
            dimension: 'T-3L2MI-2',
            prefixGroup: PrefixRegistry::GROUP_METRIC,
            systems: [System::Si]
        );

        $this->assertSame('ohm', $unit->asciiSymbol);
        $this->assertSame('Ω', $unit->unicodeSymbol);
    }

    /**
     * Test constructor with no prefix group (defaults to 0).
     */
    public function testConstructorWithNoPrefixGroup(): void
    {
        $unit = new Unit(
            name: 'hectare',
            asciiSymbol: 'ha',
            dimension: 'L2',
            systems: [System::SiAccepted]
        );

        $this->assertSame(0, $unit->prefixGroup);
    }

    /**
     * Test constructor normalizes dimension code.
     */
    public function testConstructorNormalizesDimension(): void
    {
        $unit = new Unit(
            name: 'newton',
            asciiSymbol: 'N',
            dimension: 'MLT-2',
            systems: [System::Si]
        );

        // Dimension should be normalized.
        $this->assertSame('MLT-2', $unit->dimension);
    }

    /**
     * Test constructor throws for invalid ASCII symbol.
     */
    public function testConstructorThrowsForInvalidAsciiSymbol(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('must only contain ASCII characters');

        new Unit(
            name: 'test',
            asciiSymbol: 'm²',
            dimension: 'L',
            systems: [System::Si]
        );
    }

    /**
     * Test constructor throws for invalid Unicode symbol.
     */
    public function testConstructorThrowsForInvalidUnicodeSymbol(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('must only contain Unicode letters');

        new Unit(
            name: 'test',
            asciiSymbol: 'm',
            unicodeSymbol: '123',
            dimension: 'L',
            systems: [System::Si]
        );
    }

    /**
     * Test constructor defaults systems to [System::Custom] when not specified.
     */
    public function testConstructorDefaultsSystemToCustom(): void
    {
        $unit = new Unit(name: 'test', asciiSymbol: 'tst', dimension: 'L');

        $this->assertCount(1, $unit->systems);
        $this->assertContains(System::Custom, $unit->systems);
    }

    /**
     * Test constructor throws for empty name.
     */
    public function testConstructorThrowsForEmptyName(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Unit name must');

        new Unit(name: '', asciiSymbol: 'tst', dimension: 'L');
    }

    /**
     * Test constructor throws for name containing digits.
     */
    public function testConstructorThrowsForNameWithDigits(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Unit name must');

        new Unit(name: 'unit123', asciiSymbol: 'tst', dimension: 'L');
    }

    /**
     * Test constructor throws for name with too many words.
     */
    public function testConstructorThrowsForNameWithTooManyWords(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Unit name must');

        new Unit(name: 'one two three four', asciiSymbol: 'tst', dimension: 'L');
    }

    /**
     * Test constructor accepts name with up to three words.
     */
    public function testConstructorAcceptsThreeWordName(): void
    {
        $unit = new Unit(name: 'US fluid ounce', asciiSymbol: 'floz', dimension: 'L3');

        $this->assertSame('US fluid ounce', $unit->name);
    }

    /**
     * Test constructor throws for empty systems array.
     */
    public function testConstructorThrowsForEmptySystems(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('at least one measurement system');

        new Unit(
            name: 'test',
            asciiSymbol: 'tst',
            dimension: 'L',
            systems: []
        );
    }

    /**
     * Test constructor throws for non-System values in systems array.
     */
    public function testConstructorThrowsForNonSystemValues(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('must be specified as System enum values');

        new Unit(
            name: 'test',
            asciiSymbol: 'tst',
            dimension: 'L',
            systems: ['SI', 'Imperial'] // @phpstan-ignore argument.type
        );
    }

    /**
     * Test constructor deduplicates systems.
     */
    public function testConstructorDeduplicatesSystems(): void
    {
        $unit = new Unit(
            name: 'test',
            asciiSymbol: 'tst',
            dimension: 'L',
            systems: [System::Si, System::Si, System::Imperial]
        );

        $this->assertCount(2, $unit->systems);
    }

    /**
     * Test constructor throws for prefix group below range.
     */
    public function testConstructorThrowsForNegativePrefixGroup(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Prefix group must be in the range');

        new Unit(name: 'test', asciiSymbol: 'tst', dimension: 'L', prefixGroup: -1);
    }

    /**
     * Test constructor throws for prefix group above range.
     */
    public function testConstructorThrowsForPrefixGroupAboveRange(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Prefix group must be in the range');

        new Unit(name: 'test', asciiSymbol: 'tst', dimension: 'L', prefixGroup: 16);
    }

    /**
     * Test constructor throws for invalid expansion unit symbol.
     */
    public function testConstructorThrowsForInvalidExpansionUnitSymbol(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid expansion unit symbol');

        new Unit(name: 'test', asciiSymbol: 'tst', dimension: 'L', expansionUnitSymbol: '<invalid>');
    }

    /**
     * Test constructor throws for zero expansion value.
     */
    public function testConstructorThrowsForZeroExpansionValue(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Must be positive');

        new Unit(name: 'test', asciiSymbol: 'tst', dimension: 'L', expansionUnitSymbol: 'm', expansionValue: 0.0);
    }

    /**
     * Test constructor throws for negative expansion value.
     */
    public function testConstructorThrowsForNegativeExpansionValue(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Must be positive');

        new Unit(name: 'test', asciiSymbol: 'tst', dimension: 'L', expansionUnitSymbol: 'm', expansionValue: -1.0);
    }

    /**
     * Test constructor defaults expansion value to 1.0 when expansion unit symbol is set.
     */
    public function testConstructorDefaultsExpansionValueToOne(): void
    {
        $unit = new Unit(name: 'test', asciiSymbol: 'tst', dimension: 'L', expansionUnitSymbol: 'm');

        $this->assertSame(1.0, $unit->expansionValue);
    }

    // endregion

    // region Property tests

    /**
     * Test asciiSymbol property can be read.
     */
    public function testAsciiSymbolPropertyIsReadable(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [System::Si]
        );

        $this->assertSame('m', $unit->asciiSymbol);
    }

    /**
     * Test unicodeSymbol property can be read.
     */
    public function testUnicodeSymbolPropertyIsReadable(): void
    {
        $unit = new Unit(
            name: 'ohm',
            asciiSymbol: 'ohm',
            unicodeSymbol: 'Ω',
            dimension: 'T-3L2MI-2',
            systems: [System::Si]
        );

        $this->assertSame('Ω', $unit->unicodeSymbol);
    }

    /**
     * Test unicodeSymbol defaults to asciiSymbol when not specified.
     */
    public function testUnicodeSymbolDefaultsToAsciiSymbol(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [System::Si]
        );

        $this->assertSame('m', $unit->unicodeSymbol);
    }

    /**
     * Test dimension property can be read.
     */
    public function testDimensionPropertyIsReadable(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [System::Si]
        );

        $this->assertSame('L', $unit->dimension);
    }

    // endregion

    /**
     * Test acceptsPrefix returns true for valid metric prefix with string.
     */
    public function testAcceptsPrefixReturnsTrueForValidPrefixString(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            prefixGroup: PrefixRegistry::GROUP_METRIC,
            systems: [System::Si]
        );

        $this->assertTrue($unit->acceptsPrefix('k'));
        $this->assertTrue($unit->acceptsPrefix('m'));
        $this->assertTrue($unit->acceptsPrefix('c'));
        $this->assertTrue($unit->acceptsPrefix('M'));
        $this->assertTrue($unit->acceptsPrefix('G'));
    }

    /**
     * Test acceptsPrefix returns true for Unicode prefix string.
     */
    public function testAcceptsPrefixReturnsTrueForUnicodePrefixString(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            prefixGroup: PrefixRegistry::GROUP_METRIC,
            systems: [System::Si]
        );

        $this->assertTrue($unit->acceptsPrefix('μ'));
    }

    /**
     * Test acceptsPrefix returns false for prefix not in unit's group.
     */
    public function testAcceptsPrefixReturnsFalseForInvalidPrefix(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            prefixGroup: PrefixRegistry::GROUP_METRIC,
            systems: [System::Si]
        );

        // Binary prefix not in METRIC group.
        $this->assertFalse($unit->acceptsPrefix('Ki'));
    }

    /**
     * Test acceptsPrefix returns false for unknown prefix string.
     */
    public function testAcceptsPrefixReturnsFalseForUnknownPrefixString(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            prefixGroup: PrefixRegistry::GROUP_METRIC,
            systems: [System::Si]
        );

        $this->assertFalse($unit->acceptsPrefix('invalid'));
        $this->assertFalse($unit->acceptsPrefix('X'));
    }

    /**
     * Test acceptsPrefix returns false when no prefixes allowed.
     */
    public function testAcceptsPrefixReturnsFalseWhenNoPrefixesAllowed(): void
    {
        $unit = new Unit(
            name: 'hectare',
            asciiSymbol: 'ha',
            dimension: 'L2',
            systems: [System::SiAccepted]
        );

        $this->assertFalse($unit->acceptsPrefix('k'));
    }

    /**
     * Test allowedPrefixes property returns array of Prefix objects.
     */
    public function testAllowedPrefixesPropertyReturnsArray(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            prefixGroup: PrefixRegistry::GROUP_METRIC,
            systems: [System::Si]
        );

        $prefixes = $unit->allowedPrefixes;

        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertIsArray($prefixes);
        $this->assertNotEmpty($prefixes);
    }

    /**
     * Test allowedPrefixes property returns empty array when no prefixes allowed.
     */
    public function testAllowedPrefixesPropertyReturnsEmptyArray(): void
    {
        $unit = new Unit(
            name: 'hectare',
            asciiSymbol: 'ha',
            dimension: 'L2',
            systems: [System::SiAccepted]
        );

        $prefixes = $unit->allowedPrefixes;

        $this->assertSame([], $prefixes);
    }

    // endregion

    // region Formatting methods tests

    /**
     * Test __toString returns the Unicode symbol.
     */
    public function testToStringReturnsUnicodeSymbol(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [System::Si]
        );

        $this->assertSame('m', (string)$unit);
    }

    /**
     * Test __toString returns Unicode symbol when different from ASCII symbol.
     */
    public function testToStringReturnsUnicodeSymbolWhenDifferent(): void
    {
        $unit = new Unit(
            name: 'ohm',
            asciiSymbol: 'ohm',
            unicodeSymbol: 'Ω',
            dimension: 'T-3L2MI-2',
            systems: [System::Si]
        );

        $this->assertSame('Ω', (string)$unit);
    }

    /**
     * Test format() returns the Unicode symbol by default.
     */
    public function testFormatReturnsUnicodeSymbol(): void
    {
        $unit = new Unit(
            name: 'ohm',
            asciiSymbol: 'ohm',
            unicodeSymbol: 'Ω',
            dimension: 'T-3L2MI-2',
            systems: [System::Si]
        );

        $this->assertSame('Ω', $unit->format());
        $this->assertSame('Ω', $unit->format(false));
    }

    /**
     * Test format(true) returns the ASCII symbol.
     */
    public function testFormatAsciiReturnsSymbol(): void
    {
        $unit = new Unit(
            name: 'ohm',
            asciiSymbol: 'ohm',
            unicodeSymbol: 'Ω',
            dimension: 'T-3L2MI-2',
            systems: [System::Si]
        );

        $this->assertSame('ohm', $unit->format(true));
    }

    /**
     * Test format() with degree symbol.
     */
    public function testFormatWithDegreeSymbol(): void
    {
        $unit = new Unit(
            name: 'degree',
            asciiSymbol: 'deg',
            unicodeSymbol: '°',
            dimension: 'A',
            systems: [System::SiAccepted]
        );

        $this->assertSame('°', (string)$unit);
        $this->assertSame('°', $unit->format());
        $this->assertSame('deg', $unit->format(true));
    }

    // endregion

    // region Comparison methods tests

    /**
     * Test equal returns true for same Unit instance.
     */
    public function testEqualReturnsTrueForSameInstance(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [System::Si]
        );

        $this->assertTrue($unit->equal($unit));
    }

    /**
     * Test equal returns true for different instances with same name.
     */
    public function testEqualReturnsTrueForSameName(): void
    {
        $unit1 = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [System::Si]
        );
        $unit2 = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [System::Si]
        );

        $this->assertTrue($unit1->equal($unit2));
    }

    /**
     * Test equal returns false for different unit names.
     */
    public function testEqualReturnsFalseForDifferentNames(): void
    {
        $meter = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [System::Si]
        );
        $foot = new Unit(
            name: 'foot',
            asciiSymbol: 'ft',
            dimension: 'L',
            systems: [System::Imperial]
        );

        $this->assertFalse($meter->equal($foot));
    }

    /**
     * Test equal returns false for different types.
     */
    public function testEqualReturnsFalseForDifferentTypes(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [System::Si]
        );

        $this->assertFalse($unit->equal('m'));
        $this->assertFalse($unit->equal(1));
        $this->assertFalse($unit->equal(null));
        $this->assertFalse($unit->equal(new stdClass()));
    }

    // endregion

    // region Integration tests with UnitRegistry

    /**
     * Test getting meter from UnitRegistry.
     */
    public function testGetMeterFromRegistry(): void
    {
        $unit = UnitRegistry::getBySymbol('m');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertSame('meter', $unit->name);
        $this->assertSame('m', $unit->asciiSymbol);
        $this->assertSame('L', $unit->dimension);
    }

    /**
     * Test getting ohm from UnitRegistry.
     */
    public function testGetOhmFromRegistry(): void
    {
        $unit = UnitRegistry::getBySymbol('ohm');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertSame('ohm', $unit->asciiSymbol);
        $this->assertSame('Ω', $unit->unicodeSymbol);
        $this->assertSame('Ω', $unit->format());
        $this->assertSame('Ω', (string)$unit);
        $this->assertSame('ohm', $unit->format(true));
    }

    /**
     * Test getting byte from UnitRegistry.
     */
    public function testGetByteFromRegistry(): void
    {
        $unit = UnitRegistry::getBySymbol('B');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertSame('byte', $unit->name);
        $this->assertSame('B', $unit->asciiSymbol);

        // Should accept both binary and large metric prefixes.
        $this->assertTrue($unit->acceptsPrefix('Ki'));
        $this->assertTrue($unit->acceptsPrefix('Mi'));
        $this->assertTrue($unit->acceptsPrefix('k'));
        $this->assertTrue($unit->acceptsPrefix('M'));

        // Should not accept small metric prefixes.
        $this->assertFalse($unit->acceptsPrefix('m'));
        $this->assertFalse($unit->acceptsPrefix('c'));
    }

    // endregion

    // region alternateSymbol tests

    /**
     * Test alternateSymbol property is set correctly.
     */
    public function testAlternateSymbolPropertyIsSet(): void
    {
        $unit = new Unit(
            name: 'liter',
            asciiSymbol: 'L',
            dimension: 'L3',
            alternateSymbol: '#',
            systems: [System::SiAccepted]
        );

        $this->assertSame('#', $unit->alternateSymbol);
    }

    /**
     * Test constructor throws for alternate symbol with non-ASCII characters.
     */
    public function testConstructorThrowsForNonAsciiAlternateSymbol(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('may only contain');

        new Unit(
            name: 'liter',
            asciiSymbol: 'L',
            dimension: 'L3',
            alternateSymbol: 'ℓ',
            systems: [System::SiAccepted]
        );
    }

    /**
     * Test alternateSymbol accepts a single letter.
     */
    public function testAlternateSymbolAcceptsSingleLetter(): void
    {
        $unit = new Unit(
            name: 'liter',
            asciiSymbol: 'L',
            dimension: 'L3',
            alternateSymbol: 'l',
            systems: [System::SiAccepted]
        );

        $this->assertSame('l', $unit->alternateSymbol);
    }

    /**
     * Test alternateSymbol property is null when not set.
     */
    public function testAlternateSymbolPropertyIsNullByDefault(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [System::Si]
        );

        $this->assertNull($unit->alternateSymbol);
    }

    // endregion

    // region symbols property tests

    /**
     * Test symbols property returns array with ASCII symbol.
     */
    public function testSymbolsPropertyIncludesAsciiSymbol(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [System::Si]
        );

        $symbols = $unit->symbols;

        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertIsArray($symbols);
        $this->assertArrayHasKey('m', $symbols);
    }

    /**
     * Test symbols property includes Unicode symbol when different.
     */
    public function testSymbolsPropertyIncludesUnicodeSymbol(): void
    {
        $unit = new Unit(
            name: 'ohm',
            asciiSymbol: 'ohm',
            unicodeSymbol: 'Ω',
            dimension: 'T-3L2MI-2',
            systems: [System::Si]
        );

        $symbols = $unit->symbols;

        $this->assertArrayHasKey('ohm', $symbols);
        $this->assertArrayHasKey('Ω', $symbols);
    }

    /**
     * Test symbols property includes alternate symbol when set.
     */
    public function testSymbolsPropertyIncludesAlternateSymbol(): void
    {
        $unit = new Unit(
            name: 'liter',
            asciiSymbol: 'L',
            dimension: 'L3',
            alternateSymbol: '#',
            systems: [System::SiAccepted]
        );

        $symbols = $unit->symbols;

        $this->assertArrayHasKey('L', $symbols);
        $this->assertArrayHasKey('#', $symbols);
    }

    /**
     * Test symbols property includes prefixed symbols.
     */
    public function testSymbolsPropertyIncludesPrefixedSymbols(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            prefixGroup: PrefixRegistry::GROUP_METRIC,
            systems: [System::Si]
        );

        $symbols = $unit->symbols;

        $this->assertArrayHasKey('m', $symbols);
        $this->assertArrayHasKey('km', $symbols);
        $this->assertArrayHasKey('cm', $symbols);
        $this->assertArrayHasKey('mm', $symbols);
    }

    /**
     * Test symbols property includes prefixed Unicode symbols.
     */
    public function testSymbolsPropertyIncludesPrefixedUnicodeSymbols(): void
    {
        $unit = new Unit(
            name: 'ohm',
            asciiSymbol: 'ohm',
            unicodeSymbol: 'Ω',
            dimension: 'T-3L2MI-2',
            prefixGroup: PrefixRegistry::GROUP_METRIC,
            systems: [System::Si]
        );

        $symbols = $unit->symbols;

        // ASCII prefixed.
        $this->assertArrayHasKey('kohm', $symbols);
        $this->assertArrayHasKey('Mohm', $symbols);

        // Unicode symbol prefixed.
        $this->assertArrayHasKey('kΩ', $symbols);
        $this->assertArrayHasKey('MΩ', $symbols);
    }

    // endregion

    // region belongsToSystem tests

    /**
     * Test belongsToSystem returns true for system the unit belongs to.
     */
    public function testBelongsToSystemReturnsTrueForMatchingSystem(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [System::Si]
        );

        $this->assertTrue($unit->belongsToSystem(System::Si));
    }

    /**
     * Test belongsToSystem returns false for system the unit doesn't belong to.
     */
    public function testBelongsToSystemReturnsFalseForNonMatchingSystem(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [System::Si]
        );

        $this->assertFalse($unit->belongsToSystem(System::Imperial));
    }

    /**
     * Test belongsToSystem with unit belonging to multiple systems.
     */
    public function testBelongsToSystemWithMultipleSystems(): void
    {
        $unit = new Unit(
            name: 'second',
            asciiSymbol: 's',
            dimension: 'T',
            systems: [System::Si, System::Imperial]
        );

        $this->assertTrue($unit->belongsToSystem(System::Si));
        $this->assertTrue($unit->belongsToSystem(System::Imperial));
    }

    // endregion

    // region isSi() tests

    /**
     * Test isSi returns true for SI unit.
     */
    public function testIsSiReturnsTrueForSiUnit(): void
    {
        $unit = UnitRegistry::getBySymbol('m');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertTrue($unit->isSi());
    }

    /**
     * Test isSi returns false for Imperial unit.
     */
    public function testIsSiReturnsFalseForImperialUnit(): void
    {
        $unit = UnitRegistry::getBySymbol('ft');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertFalse($unit->isSi());
    }

    /**
     * Test isSi returns false for SIAccepted unit that is not SI.
     */
    public function testIsSiReturnsFalseForSiAcceptedOnly(): void
    {
        $unit = UnitRegistry::getBySymbol('ha');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertFalse($unit->isSi());
    }

    // endregion

    // region isBase() tests

    /**
     * Test isBase returns true for unit with single dimension term.
     */
    public function testIsBaseReturnsTrueForSingleDimensionTerm(): void
    {
        $unit = UnitRegistry::getBySymbol('m');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertTrue($unit->isBase());
    }

    /**
     * Test isBase returns false for unit with multiple dimension terms.
     */
    public function testIsBaseReturnsFalseForMultipleDimensionTerms(): void
    {
        // Newton has dimension MLT-2 (3 terms).
        $unit = UnitRegistry::getBySymbol('N');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertFalse($unit->isBase());
    }

    // endregion

    // region parse() tests

    /**
     * Test parse returns Unit for valid symbol.
     */
    public function testParseReturnsUnitForValidSymbol(): void
    {
        $unit = Unit::parse('m');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertSame('meter', $unit->name);
        $this->assertSame('m', $unit->asciiSymbol);
    }

    /**
     * Test parse returns Unit for Unicode symbol.
     */
    public function testParseReturnsUnitForUnicodeSymbol(): void
    {
        $unit = Unit::parse('Ω');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertSame('ohm', $unit->name);
    }

    /**
     * Test parse throws FormatException for invalid characters.
     */
    public function testParseThrowsFormatExceptionForInvalidCharacters(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('can only contain letters');

        Unit::parse('m²');
    }

    /**
     * Test parse throws DomainException for unknown symbol.
     */
    public function testParseThrowsDomainExceptionForUnknownSymbol(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Unknown unit symbol 'xyz'");

        Unit::parse('xyz');
    }

    // endregion

    // region isExpandable() tests

    /**
     * Test isExpandable returns true for Newton (has expansion to kg*m/s2).
     */
    public function testIsExpandableReturnsTrueForNewton(): void
    {
        $unit = UnitRegistry::getBySymbol('N');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertTrue($unit->isExpandable());
    }

    /**
     * Test isExpandable returns true for Hertz (has expansion to s-1).
     */
    public function testIsExpandableReturnsTrueForHertz(): void
    {
        $unit = UnitRegistry::getBySymbol('Hz');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertTrue($unit->isExpandable());
    }

    /**
     * Test isExpandable returns false for meter (SI base unit).
     */
    public function testIsExpandableReturnsFalseForMeter(): void
    {
        $unit = UnitRegistry::getBySymbol('m');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertFalse($unit->isExpandable());
    }

    /**
     * Test isExpandable returns false for second (SI base unit).
     */
    public function testIsExpandableReturnsFalseForSecond(): void
    {
        $unit = UnitRegistry::getBySymbol('s');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertFalse($unit->isExpandable());
    }

    /**
     * Test isExpandable returns false for foot (non-SI base unit).
     */
    public function testIsExpandableReturnsFalseForFoot(): void
    {
        $unit = UnitRegistry::getBySymbol('ft');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertFalse($unit->isExpandable());
    }

    // endregion
}
