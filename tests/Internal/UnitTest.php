<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Internal;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Quantities\Internal\Prefix;
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
     * Test constructor with SI base unit data (metre).
     */
    public function testConstructorWithSiBaseUnit(): void
    {
        $unit = new Unit(
            name: 'metre',
            asciiSymbol: 'm',
            unicodeSymbol: null,
            dimension: 'L',
            prefixGroup: PrefixRegistry::GROUP_METRIC,
            systems: [System::Si]
        );

        $this->assertSame('metre', $unit->name);
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
            unicodeSymbol: null,
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
            unicodeSymbol: null,
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
            unicodeSymbol: null,
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
            unicodeSymbol: null,
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
        $this->expectExceptionMessage('must only contain letters');

        new Unit(
            name: 'test',
            asciiSymbol: 'm',
            unicodeSymbol: '123',
            dimension: 'L',
            systems: [System::Si]
        );
    }

    // endregion

    // region Property tests

    /**
     * Test asciiSymbol property can be read.
     */
    public function testAsciiSymbolPropertyIsReadable(): void
    {
        $unit = new Unit(
            name: 'metre',
            asciiSymbol: 'm',
            unicodeSymbol: null,
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
            name: 'metre',
            asciiSymbol: 'm',
            unicodeSymbol: null,
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
            name: 'metre',
            asciiSymbol: 'm',
            unicodeSymbol: null,
            dimension: 'L',
            systems: [System::Si]
        );

        $this->assertSame('L', $unit->dimension);
    }

    // endregion

    // region Prefix methods tests

    /**
     * Test acceptsPrefixes returns true when prefixGroup is greater than 0.
     */
    public function testAcceptsPrefixesReturnsTrueWhenPrefixGroupSet(): void
    {
        $unit = new Unit(
            name: 'metre',
            asciiSymbol: 'm',
            unicodeSymbol: null,
            dimension: 'L',
            prefixGroup: PrefixRegistry::GROUP_METRIC,
            systems: [System::Si]
        );

        $this->assertTrue($unit->acceptsPrefixes());
    }

    /**
     * Test acceptsPrefixes returns false when prefixGroup is 0.
     */
    public function testAcceptsPrefixesReturnsFalseWhenNoPrefixGroup(): void
    {
        $unit = new Unit(
            name: 'hectare',
            asciiSymbol: 'ha',
            unicodeSymbol: null,
            dimension: 'L2',
            systems: [System::SiAccepted]
        );

        $this->assertFalse($unit->acceptsPrefixes());
    }

    /**
     * Test acceptsPrefix returns true for valid metric prefix with Prefix object.
     */
    public function testAcceptsPrefixReturnsTrueForValidPrefixObject(): void
    {
        $unit = new Unit(
            name: 'metre',
            asciiSymbol: 'm',
            unicodeSymbol: null,
            dimension: 'L',
            prefixGroup: PrefixRegistry::GROUP_METRIC,
            systems: [System::Si]
        );

        $prefix = PrefixRegistry::getBySymbol('k');
        $this->assertInstanceOf(Prefix::class, $prefix);
        $this->assertTrue($unit->acceptsPrefix($prefix));
    }

    /**
     * Test acceptsPrefix returns true for valid metric prefix with string.
     */
    public function testAcceptsPrefixReturnsTrueForValidPrefixString(): void
    {
        $unit = new Unit(
            name: 'metre',
            asciiSymbol: 'm',
            unicodeSymbol: null,
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
            name: 'metre',
            asciiSymbol: 'm',
            unicodeSymbol: null,
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
            name: 'metre',
            asciiSymbol: 'm',
            unicodeSymbol: null,
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
            name: 'metre',
            asciiSymbol: 'm',
            unicodeSymbol: null,
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
            unicodeSymbol: null,
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
            name: 'metre',
            asciiSymbol: 'm',
            unicodeSymbol: null,
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
            unicodeSymbol: null,
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
            name: 'metre',
            asciiSymbol: 'm',
            unicodeSymbol: null,
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
            name: 'metre',
            asciiSymbol: 'm',
            unicodeSymbol: null,
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
            name: 'metre',
            asciiSymbol: 'm',
            unicodeSymbol: null,
            dimension: 'L',
            systems: [System::Si]
        );
        $unit2 = new Unit(
            name: 'metre',
            asciiSymbol: 'm',
            unicodeSymbol: null,
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
        $metre = new Unit(
            name: 'metre',
            asciiSymbol: 'm',
            unicodeSymbol: null,
            dimension: 'L',
            systems: [System::Si]
        );
        $foot = new Unit(
            name: 'foot',
            asciiSymbol: 'ft',
            unicodeSymbol: null,
            dimension: 'L',
            systems: [System::Imperial]
        );

        $this->assertFalse($metre->equal($foot));
    }

    /**
     * Test equal returns false for different types.
     */
    public function testEqualReturnsFalseForDifferentTypes(): void
    {
        $unit = new Unit(
            name: 'metre',
            asciiSymbol: 'm',
            unicodeSymbol: null,
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
     * Test getting metre from UnitRegistry.
     */
    public function testGetMetreFromRegistry(): void
    {
        $unit = UnitRegistry::getBySymbol('m');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertSame('metre', $unit->name);
        $this->assertSame('m', $unit->asciiSymbol);
        $this->assertSame('L', $unit->dimension);
        $this->assertTrue($unit->acceptsPrefixes());
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

    // region Symbol validation tests

    /**
     * Test isValidAsciiSymbol returns true for valid symbols.
     */
    public function testIsValidAsciiSymbolReturnsTrueForValid(): void
    {
        $this->assertTrue(Unit::isValidAsciiSymbol('m'));
        $this->assertTrue(Unit::isValidAsciiSymbol('km'));
        $this->assertTrue(Unit::isValidAsciiSymbol('Hz'));
        $this->assertTrue(Unit::isValidAsciiSymbol('ohm'));
    }

    /**
     * Test isValidAsciiSymbol returns false for invalid symbols.
     */
    public function testIsValidAsciiSymbolReturnsFalseForInvalid(): void
    {
        $this->assertFalse(Unit::isValidAsciiSymbol('m²'));
        $this->assertFalse(Unit::isValidAsciiSymbol('123'));
        $this->assertFalse(Unit::isValidAsciiSymbol('Ω'));
    }

    /**
     * Test isValidUnicodeSymbol returns true for valid symbols.
     */
    public function testIsValidUnicodeSymbolReturnsTrueForValid(): void
    {
        $this->assertTrue(Unit::isValidUnicodeSymbol('m'));
        $this->assertTrue(Unit::isValidUnicodeSymbol('Ω'));
        $this->assertTrue(Unit::isValidUnicodeSymbol('°'));
        $this->assertTrue(Unit::isValidUnicodeSymbol('μ'));
    }

    /**
     * Test isValidUnicodeSymbol returns false for invalid symbols.
     */
    public function testIsValidUnicodeSymbolReturnsFalseForInvalid(): void
    {
        $this->assertFalse(Unit::isValidUnicodeSymbol('123'));
    }

    // endregion

    // region regex() tests

    /**
     * Test regex matches valid unit symbols.
     */
    public function testRegexMatchesValidSymbols(): void
    {
        $pattern = '/^' . Unit::regex() . '$/u';

        $this->assertSame(1, preg_match($pattern, 'm'));
        $this->assertSame(1, preg_match($pattern, 'km'));
        $this->assertSame(1, preg_match($pattern, 'Hz'));
        $this->assertSame(1, preg_match($pattern, 'ohm'));
        $this->assertSame(1, preg_match($pattern, 'Ω'));
    }

    /**
     * Test regex does not match invalid symbols.
     */
    public function testRegexDoesNotMatchInvalidSymbols(): void
    {
        $pattern = '/^' . Unit::regex() . '$/u';

        $this->assertSame(0, preg_match($pattern, '123'));
        $this->assertSame(0, preg_match($pattern, 'm²'));
    }

    // endregion

    // region alternateSymbol tests

    /**
     * Test alternateSymbol property is set correctly.
     */
    public function testAlternateSymbolPropertyIsSet(): void
    {
        $unit = new Unit(
            name: 'litre',
            asciiSymbol: 'L',
            unicodeSymbol: null,
            dimension: 'L3',
            alternateSymbol: 'l',
            systems: [System::SiAccepted]
        );

        $this->assertSame('l', $unit->alternateSymbol);
    }

    /**
     * Test constructor throws for alternate symbol with non-ASCII characters.
     */
    public function testConstructorThrowsForNonAsciiAlternateSymbol(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('may only contain');

        new Unit(
            name: 'litre',
            asciiSymbol: 'L',
            unicodeSymbol: null,
            dimension: 'L3',
            alternateSymbol: 'ℓ',
            systems: [System::SiAccepted]
        );
    }

    /**
     * Test alternateSymbol property is null when not set.
     */
    public function testAlternateSymbolPropertyIsNullByDefault(): void
    {
        $unit = new Unit(
            name: 'metre',
            asciiSymbol: 'm',
            unicodeSymbol: null,
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
            name: 'metre',
            asciiSymbol: 'm',
            unicodeSymbol: null,
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
            name: 'litre',
            asciiSymbol: 'L',
            unicodeSymbol: null,
            dimension: 'L3',
            alternateSymbol: 'l',
            systems: [System::SiAccepted]
        );

        $symbols = $unit->symbols;

        $this->assertArrayHasKey('L', $symbols);
        $this->assertArrayHasKey('l', $symbols);
    }

    /**
     * Test symbols property includes prefixed symbols.
     */
    public function testSymbolsPropertyIncludesPrefixedSymbols(): void
    {
        $unit = new Unit(
            name: 'metre',
            asciiSymbol: 'm',
            unicodeSymbol: null,
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
            name: 'metre',
            asciiSymbol: 'm',
            unicodeSymbol: null,
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
            name: 'metre',
            asciiSymbol: 'm',
            unicodeSymbol: null,
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
            unicodeSymbol: null,
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
        $this->assertSame('metre', $unit->name);
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
     * Test isExpandable returns false for metre (SI base unit).
     */
    public function testIsExpandableReturnsFalseForMetre(): void
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

    // region isValidNonLetterSymbol tests

    /**
     * Test isValidNonLetterSymbol returns true for valid symbols.
     */
    public function testIsValidNonLetterSymbolReturnsTrueForValid(): void
    {
        $this->assertTrue(Unit::isValidNonLetterSymbol('°'));
        $this->assertTrue(Unit::isValidNonLetterSymbol('%'));
        $this->assertTrue(Unit::isValidNonLetterSymbol('$'));
        $this->assertTrue(Unit::isValidNonLetterSymbol('€'));
        $this->assertTrue(Unit::isValidNonLetterSymbol('′'));
        $this->assertTrue(Unit::isValidNonLetterSymbol('″'));
    }

    /**
     * Test isValidNonLetterSymbol returns false for letters.
     */
    public function testIsValidNonLetterSymbolReturnsFalseForLetters(): void
    {
        $this->assertFalse(Unit::isValidNonLetterSymbol('m'));
        $this->assertFalse(Unit::isValidNonLetterSymbol('abc'));
    }

    /**
     * Test isValidNonLetterSymbol returns false for digits.
     */
    public function testIsValidNonLetterSymbolReturnsFalseForDigits(): void
    {
        $this->assertFalse(Unit::isValidNonLetterSymbol('1'));
        $this->assertFalse(Unit::isValidNonLetterSymbol('123'));
    }

    /**
     * Test isValidNonLetterSymbol returns false for multiple symbols.
     */
    public function testIsValidNonLetterSymbolReturnsFalseForMultipleSymbols(): void
    {
        $this->assertFalse(Unit::isValidNonLetterSymbol('°C'));
        $this->assertFalse(Unit::isValidNonLetterSymbol('%%'));
    }

    // endregion
}
