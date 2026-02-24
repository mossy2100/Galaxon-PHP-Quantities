<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Internal;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Quantities\Internal\Unit;
use Galaxon\Quantities\Services\PrefixService;
use Galaxon\Quantities\Services\UnitService;
use Galaxon\Quantities\UnitSystem;
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
        UnitService::loadBySystem(UnitSystem::Imperial);
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
            prefixGroup: PrefixService::GROUP_METRIC,
            systems: [UnitSystem::Si]
        );

        $this->assertSame('meter', $unit->name);
        $this->assertSame('m', $unit->asciiSymbol);
        $this->assertSame('m', $unit->unicodeSymbol);
        $this->assertSame('L', $unit->dimension);
        $this->assertContains(UnitSystem::Si, $unit->systems);
        $this->assertSame(PrefixService::GROUP_METRIC, $unit->prefixGroup);
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
            prefixGroup: PrefixService::GROUP_METRIC,
            systems: [UnitSystem::Si]
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
            prefixGroup: PrefixService::GROUP_METRIC,
            systems: [UnitSystem::Si]
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
            systems: [UnitSystem::SiAccepted]
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
            systems: [UnitSystem::Si]
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
            systems: [UnitSystem::Si]
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
            systems: [UnitSystem::Si]
        );
    }

    /**
     * Test constructor defaults systems to [UnitSystem::Custom] when not specified.
     */
    public function testConstructorDefaultsSystemToCustom(): void
    {
        $unit = new Unit(name: 'test', asciiSymbol: 'tst', dimension: 'L');

        $this->assertCount(1, $unit->systems);
        $this->assertContains(UnitSystem::Custom, $unit->systems);
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
     * Test constructor throws for non-UnitSystem values in systems array.
     */
    public function testConstructorThrowsForNonSystemValues(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('must be specified as UnitSystem enum values');

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
            systems: [UnitSystem::Si, UnitSystem::Si, UnitSystem::Imperial]
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
            systems: [UnitSystem::Si]
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
            systems: [UnitSystem::Si]
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
            systems: [UnitSystem::Si]
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
            systems: [UnitSystem::Si]
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
            prefixGroup: PrefixService::GROUP_METRIC,
            systems: [UnitSystem::Si]
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
            prefixGroup: PrefixService::GROUP_METRIC,
            systems: [UnitSystem::Si]
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
            prefixGroup: PrefixService::GROUP_METRIC,
            systems: [UnitSystem::Si]
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
            prefixGroup: PrefixService::GROUP_METRIC,
            systems: [UnitSystem::Si]
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
            systems: [UnitSystem::SiAccepted]
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
            prefixGroup: PrefixService::GROUP_METRIC,
            systems: [UnitSystem::Si]
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
            systems: [UnitSystem::SiAccepted]
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
            systems: [UnitSystem::Si]
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
            systems: [UnitSystem::Si]
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
            systems: [UnitSystem::Si]
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
            systems: [UnitSystem::Si]
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
            systems: [UnitSystem::SiAccepted]
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
            systems: [UnitSystem::Si]
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
            systems: [UnitSystem::Si]
        );
        $unit2 = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [UnitSystem::Si]
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
            systems: [UnitSystem::Si]
        );
        $foot = new Unit(
            name: 'foot',
            asciiSymbol: 'ft',
            dimension: 'L',
            systems: [UnitSystem::Imperial]
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
            systems: [UnitSystem::Si]
        );

        $this->assertFalse($unit->equal('m'));
        $this->assertFalse($unit->equal(1));
        $this->assertFalse($unit->equal(null));
        $this->assertFalse($unit->equal(new stdClass()));
    }

    // endregion

    // region Integration tests with UnitService

    /**
     * Test getting meter from UnitService.
     */
    public function testGetMeterFromService(): void
    {
        $unit = UnitService::getBySymbol('m');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertSame('meter', $unit->name);
        $this->assertSame('m', $unit->asciiSymbol);
        $this->assertSame('L', $unit->dimension);
    }

    /**
     * Test getting ohm from UnitService.
     */
    public function testGetOhmFromService(): void
    {
        $unit = UnitService::getBySymbol('ohm');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertSame('ohm', $unit->asciiSymbol);
        $this->assertSame('Ω', $unit->unicodeSymbol);
        $this->assertSame('Ω', $unit->format());
        $this->assertSame('Ω', (string)$unit);
        $this->assertSame('ohm', $unit->format(true));
    }

    /**
     * Test getting byte from UnitService.
     */
    public function testGetByteFromService(): void
    {
        $unit = UnitService::getBySymbol('B');

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
            systems: [UnitSystem::SiAccepted]
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
            systems: [UnitSystem::SiAccepted]
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
            systems: [UnitSystem::SiAccepted]
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
            systems: [UnitSystem::Si]
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
            systems: [UnitSystem::Si]
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
            systems: [UnitSystem::Si]
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
            systems: [UnitSystem::SiAccepted]
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
            prefixGroup: PrefixService::GROUP_METRIC,
            systems: [UnitSystem::Si]
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
            prefixGroup: PrefixService::GROUP_METRIC,
            systems: [UnitSystem::Si]
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
            systems: [UnitSystem::Si]
        );

        $this->assertTrue($unit->belongsToSystem(UnitSystem::Si));
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
            systems: [UnitSystem::Si]
        );

        $this->assertFalse($unit->belongsToSystem(UnitSystem::Imperial));
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
            systems: [UnitSystem::Si, UnitSystem::Imperial]
        );

        $this->assertTrue($unit->belongsToSystem(UnitSystem::Si));
        $this->assertTrue($unit->belongsToSystem(UnitSystem::Imperial));
    }

    // endregion

    // region isSi() tests

    /**
     * Test isSi returns true for SI unit.
     */
    public function testIsSiReturnsTrueForSiUnit(): void
    {
        $unit = UnitService::getBySymbol('m');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertTrue($unit->isSi());
    }

    /**
     * Test isSi returns false for Imperial unit.
     */
    public function testIsSiReturnsFalseForImperialUnit(): void
    {
        $unit = UnitService::getBySymbol('ft');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertFalse($unit->isSi());
    }

    /**
     * Test isSi returns false for SIAccepted unit that is not SI.
     */
    public function testIsSiReturnsFalseForSiAcceptedOnly(): void
    {
        $unit = UnitService::getBySymbol('ha');

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
        $unit = UnitService::getBySymbol('m');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertTrue($unit->isBase());
    }

    /**
     * Test isBase returns false for unit with multiple dimension terms.
     */
    public function testIsBaseReturnsFalseForMultipleDimensionTerms(): void
    {
        // Newton has dimension MLT-2 (3 terms).
        $unit = UnitService::getBySymbol('N');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertFalse($unit->isBase());
    }

    // endregion

    // region isExpandable() tests

    /**
     * Test isExpandable returns true for a named SI unit with an expansion.
     */
    public function testIsExpandableReturnsTrueForNamedSiUnit(): void
    {
        // Newton has an expansion to kg*m/s2.
        $unit = UnitService::getBySymbol('N');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertTrue($unit->isExpandable());
    }

    /**
     * Test isExpandable returns false for a base unit.
     */
    public function testIsExpandableReturnsFalseForBaseUnit(): void
    {
        // Meter is a base unit with no expansion.
        $unit = UnitService::getBySymbol('m');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertFalse($unit->isExpandable());
    }

    /**
     * Test expansion property returns a Conversion for an expandable unit.
     */
    public function testExpansionPropertyReturnsConversionForExpandableUnit(): void
    {
        $unit = UnitService::getBySymbol('N');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertNotNull($unit->expansion);
    }

    /**
     * Test expansion property returns null for a base unit.
     */
    public function testExpansionPropertyReturnsNullForBaseUnit(): void
    {
        $unit = UnitService::getBySymbol('m');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertNull($unit->expansion);
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
}
