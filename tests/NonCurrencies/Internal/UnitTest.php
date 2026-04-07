<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\NonCurrencies\Internal;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Quantities\Exceptions\UnknownUnitException;
use Galaxon\Quantities\Internal\Converter;
use Galaxon\Quantities\Internal\Unit;
use Galaxon\Quantities\Internal\UnitSystem;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Services\PrefixService;
use Galaxon\Quantities\Services\UnitService;
use InvalidArgumentException;
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
     * Test constructor with SI base unit data (meter).
     */
    public function testConstructorWithSiBaseUnit(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [UnitSystem::Si],
            prefixGroup: PrefixService::GROUP_METRIC
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
            systems: [UnitSystem::Si],
            prefixGroup: PrefixService::GROUP_METRIC
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
            dimension: 'T-3L2MI-2',
            systems: [UnitSystem::Si],
            prefixGroup: PrefixService::GROUP_METRIC,
            unicodeSymbol: 'Ω'
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
        $this->expectExceptionMessage('Invalid ASCII unit symbol');

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
        $this->expectExceptionMessage('Invalid Unicode unit symbol');

        new Unit(
            name: 'test',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [UnitSystem::Si],
            unicodeSymbol: '123'
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
        $this->expectExceptionMessage('Invalid unit name');

        new Unit(name: '', asciiSymbol: 'tst', dimension: 'L');
    }

    /**
     * Test constructor throws for name containing digits.
     */
    public function testConstructorThrowsForNameWithDigits(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid unit name');

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
        $this->expectExceptionMessage('Cannot create a unit with no measurement systems');

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
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot create a unit with non-UnitSystem values');

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
        $this->expectExceptionMessage('Invalid prefix group');

        new Unit(name: 'test', asciiSymbol: 'tst', dimension: 'L', prefixGroup: -1);
    }

    /**
     * Test constructor throws for prefix group above range.
     */
    public function testConstructorThrowsForPrefixGroupAboveRange(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid prefix group');

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
            dimension: 'T-3L2MI-2',
            systems: [UnitSystem::Si],
            unicodeSymbol: 'Ω'
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

    // region acceptsPrefix() and allowedPrefixes tests

    /**
     * Test acceptsPrefix returns true for valid metric prefix with string.
     */
    public function testAcceptsPrefixReturnsTrueForValidPrefixString(): void
    {
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [UnitSystem::Si],
            prefixGroup: PrefixService::GROUP_METRIC
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
            systems: [UnitSystem::Si],
            prefixGroup: PrefixService::GROUP_METRIC
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
            systems: [UnitSystem::Si],
            prefixGroup: PrefixService::GROUP_METRIC
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
            systems: [UnitSystem::Si],
            prefixGroup: PrefixService::GROUP_METRIC
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
            systems: [UnitSystem::Si],
            prefixGroup: PrefixService::GROUP_METRIC
        );

        $prefixes = $unit->allowedPrefixes;

        $this->assertIsArray($prefixes); // @phpstan-ignore method.alreadyNarrowedType
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

    // region quantityType property tests

    /**
     * Test quantityType property returns QuantityType for a registered dimension.
     */
    public function testQuantityTypePropertyReturnsQuantityType(): void
    {
        $unit = UnitService::getBySymbol('m');
        $this->assertInstanceOf(Unit::class, $unit);

        $this->assertNotNull($unit->quantityType);
        $this->assertSame('length', $unit->quantityType->name);
    }

    /**
     * Test quantityType property returns null for a unit with unregistered dimension.
     */
    public function testQuantityTypePropertyReturnsNullForUnregisteredDimension(): void
    {
        $unit = new Unit(
            name: 'unregistered test',
            asciiSymbol: 'Xur',
            dimension: 'L5T3',
            systems: [UnitSystem::Custom]
        );

        $this->assertNull($unit->quantityType);
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
            dimension: 'T-3L2MI-2',
            systems: [UnitSystem::Si],
            unicodeSymbol: 'Ω'
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
            dimension: 'T-3L2MI-2',
            systems: [UnitSystem::Si],
            unicodeSymbol: 'Ω'
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
            dimension: 'T-3L2MI-2',
            systems: [UnitSystem::Si],
            unicodeSymbol: 'Ω'
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
            dimension: 'A',
            systems: [UnitSystem::SiAccepted],
            unicodeSymbol: '°'
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
            systems: [UnitSystem::SiAccepted],
            alternateSymbol: '#'
        );

        $this->assertSame('#', $unit->alternateSymbol);
    }

    /**
     * Test constructor throws for alternate symbol with non-ASCII characters.
     */
    public function testConstructorThrowsForNonAsciiAlternateSymbol(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid alternate unit symbol');

        new Unit(
            name: 'liter',
            asciiSymbol: 'L',
            dimension: 'L3',
            systems: [UnitSystem::SiAccepted],
            alternateSymbol: 'ℓ'
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
            systems: [UnitSystem::SiAccepted],
            alternateSymbol: 'l'
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

        $this->assertIsArray($symbols); // @phpstan-ignore method.alreadyNarrowedType
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
            dimension: 'T-3L2MI-2',
            systems: [UnitSystem::Si],
            unicodeSymbol: 'Ω'
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
            systems: [UnitSystem::SiAccepted],
            alternateSymbol: '#'
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
            systems: [UnitSystem::Si],
            prefixGroup: PrefixService::GROUP_METRIC
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
            dimension: 'T-3L2MI-2',
            systems: [UnitSystem::Si],
            prefixGroup: PrefixService::GROUP_METRIC,
            unicodeSymbol: 'Ω'
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

    // region tryExpand() tests

    /**
     * Test tryExpand expands a named unit to base units.
     */
    public function testTryExpandExpandsNamedUnit(): void
    {
        $unit = UnitService::getBySymbol('N');

        $this->assertInstanceOf(Unit::class, $unit);

        $expansion = $unit->tryExpand();

        $this->assertInstanceOf(Quantity::class, $expansion);
        $this->assertSame(1.0, $expansion->value);
        $this->assertSame('kg*m/s2', $expansion->compoundUnit->asciiSymbol);
    }

    /**
     * Test tryExpand returns null for a base unit.
     */
    public function testTryExpandReturnsNullForBaseUnit(): void
    {
        $unit = UnitService::getBySymbol('m');

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertNull($unit->tryExpand());
    }

    /**
     * Test tryExpand returns cached result on second call.
     */
    public function testTryExpandReturnsCachedExpansion(): void
    {
        $unit = UnitService::getBySymbol('N');
        $this->assertInstanceOf(Unit::class, $unit);

        $expansion1 = $unit->tryExpand();
        $expansion2 = $unit->tryExpand();

        $this->assertInstanceOf(Quantity::class, $expansion1);
        $this->assertSame($expansion1, $expansion2);
    }

    /**
     * Test tryExpand returns null when no conversions exist for the unit.
     */
    public function testTryExpandReturnsNullWhenNoConversionsExist(): void
    {
        // Create a non-base unit with no conversions registered.
        $unit = new Unit(
            name: 'no expand test',
            asciiSymbol: 'Xne',
            dimension: 'ML2T-2',
            systems: [UnitSystem::Si]
        );
        UnitService::add($unit);

        try {
            $this->assertNull($unit->tryExpand());
        } finally {
            UnitService::remove($unit);
            Converter::removeAllInstances();
        }
    }

    /**
     * Test tryExpand prefers unity expansion factor.
     */
    public function testTryExpandPrefersUnityFactor(): void
    {
        // Reset to ensure fresh Unit objects with no cached expansions.
        UnitService::reset();

        // J (joule) has a unity conversion to kg*m2/s2 (factor = 1).
        $unit = UnitService::getBySymbol('J');
        $this->assertInstanceOf(Unit::class, $unit);

        $expansion = $unit->tryExpand();

        $this->assertInstanceOf(Quantity::class, $expansion);
        $this->assertSame(1.0, $expansion->value);
        $this->assertSame('kg*m2/s2', $expansion->compoundUnit->asciiSymbol);
    }

    /**
     * Test tryExpand with a non-unity expansion factor.
     */
    public function testTryExpandWithNonUnityFactor(): void
    {
        UnitService::reset();

        // lbf expands to lb*ft/s2 with factor ≈ 32.174 (not 1.0).
        $unit = UnitService::getBySymbol('lbf');
        $this->assertInstanceOf(Unit::class, $unit);

        $expansion = $unit->tryExpand();

        $this->assertInstanceOf(Quantity::class, $expansion);
        $this->assertEqualsWithDelta(9.80665 / 0.3048, $expansion->value, 1e-4);
        $this->assertSame('lb*ft/s2', $expansion->compoundUnit->asciiSymbol);
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
     * Test parse throws UnknownUnitException for unknown symbol.
     */
    public function testParseThrowsUnknownUnitExceptionForUnknownSymbol(): void
    {
        $this->expectException(UnknownUnitException::class);
        $this->expectExceptionMessage("Unknown unit: 'xyz'");

        Unit::parse('xyz');
    }

    // endregion
}
