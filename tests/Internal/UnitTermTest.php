<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Internal;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Quantities\Internal\Unit;
use Galaxon\Quantities\Internal\UnitTerm;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Tests for UnitTerm class.
 */
#[CoversClass(UnitTerm::class)]
final class UnitTermTest extends TestCase
{
    // region Setup

    public static function setUpBeforeClass(): void
    {
        // Load Imperial units for cross-system tests.
        UnitRegistry::loadSystem(System::Imperial);
    }

    // endregion

    // region Constructor tests

    /**
     * Test constructor with unit symbol only.
     */
    public function testConstructorWithUnitOnly(): void
    {
        $term = new UnitTerm('m');

        $this->assertSame('metre', $term->unit->name);
        $this->assertNull($term->prefix);
        $this->assertSame(1, $term->exponent);
    }

    /**
     * Test constructor with prefix.
     */
    public function testConstructorWithPrefix(): void
    {
        $term = new UnitTerm('m', 'k');

        $this->assertSame('k', $term->prefix?->asciiSymbol);
        $this->assertSame(1, $term->exponent);
    }

    /**
     * Test constructor with exponent.
     */
    public function testConstructorWithExponent(): void
    {
        $term = new UnitTerm('m', null, 2);

        $this->assertNull($term->prefix);
        $this->assertSame(2, $term->exponent);
    }

    /**
     * Test constructor with prefix and exponent.
     */
    public function testConstructorWithPrefixAndExponent(): void
    {
        $term = new UnitTerm('m', 'k', 2);

        $this->assertSame('k', $term->prefix?->asciiSymbol);
        $this->assertSame(2, $term->exponent);
    }

    /**
     * Test constructor with negative exponent.
     */
    public function testConstructorWithNegativeExponent(): void
    {
        $term = new UnitTerm('s', null, -2);

        $this->assertSame(-2, $term->exponent);
    }

    /**
     * Test constructor throws for invalid prefix.
     */
    public function testConstructorThrowsForInvalidPrefix(): void
    {
        $this->expectException(DomainException::class);

        new UnitTerm('m', 'invalid');
    }

    /**
     * Test constructor throws for prefix on unit that doesn't accept prefixes.
     */
    public function testConstructorThrowsForPrefixOnNonPrefixableUnit(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('is invalid');

        new UnitTerm('ha', 'k');
    }

    /**
     * Test constructor throws for exponent of zero.
     */
    public function testConstructorThrowsForExponentZero(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("can't be zero");

        new UnitTerm('m', null, 0);
    }

    /**
     * Test constructor throws for exponent out of range (positive).
     */
    public function testConstructorThrowsForExponentTooLarge(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('must be between -9 and 9');

        new UnitTerm('m', null, 10);
    }

    /**
     * Test constructor throws for exponent out of range (negative).
     */
    public function testConstructorThrowsForExponentTooSmall(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('must be between -9 and 9');

        new UnitTerm('m', null, -10);
    }

    /**
     * Test constructor with various unit symbols.
     */
    public function testConstructorWithVariousSymbols(): void
    {
        $metre = new UnitTerm('m');
        $this->assertSame('metre', $metre->unit->name);

        $second = new UnitTerm('s');
        $this->assertSame('second', $second->unit->name);

        $hertz = new UnitTerm('Hz');
        $this->assertSame('hertz', $hertz->unit->name);

        $newton = new UnitTerm('N');
        $this->assertSame('newton', $newton->unit->name);
    }

    /**
     * Test constructor throws for unknown unit symbol.
     */
    public function testConstructorThrowsForUnknownSymbol(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Unit 'xyz' is unknown");

        new UnitTerm('xyz');
    }

    // endregion

    // region Computed property tests

    /**
     * Test asciiSymbol property for base unit.
     */
    public function testAsciiSymbolPropertyForUnit(): void
    {
        $term = new UnitTerm('m');

        $this->assertSame('m', $term->asciiSymbol);
    }

    /**
     * Test asciiSymbol property with prefix.
     */
    public function testAsciiSymbolPropertyWithPrefix(): void
    {
        $term = new UnitTerm('m', 'k');

        $this->assertSame('km', $term->asciiSymbol);
    }

    /**
     * Test asciiSymbol property with exponent.
     */
    public function testAsciiSymbolPropertyWithExponent(): void
    {
        $term = new UnitTerm('m', null, 2);

        $this->assertSame('m2', $term->asciiSymbol);
    }

    /**
     * Test asciiSymbol property with prefix and exponent.
     */
    public function testAsciiSymbolPropertyWithPrefixAndExponent(): void
    {
        $term = new UnitTerm('m', 'k', 2);

        $this->assertSame('km2', $term->asciiSymbol);
    }

    /**
     * Test asciiSymbol property with negative exponent.
     */
    public function testAsciiSymbolPropertyWithNegativeExponent(): void
    {
        $term = new UnitTerm('s', null, -2);

        $this->assertSame('s-2', $term->asciiSymbol);
    }

    /**
     * Test unprefixedAsciiSymbol property.
     */
    public function testUnprefixedAsciiSymbolProperty(): void
    {
        $term = new UnitTerm('m', 'k', 2);

        $this->assertSame('m2', $term->unprefixedAsciiSymbol);
    }

    /**
     * Test unexponentiatedAsciiSymbol property.
     */
    public function testUnexponentiatedAsciiSymbolProperty(): void
    {
        $term = new UnitTerm('m', 'k', 2);

        $this->assertSame('km', $term->unexponentiatedAsciiSymbol);
    }

    /**
     * Test prefixMultiplier property without prefix.
     */
    public function testPrefixMultiplierWithoutPrefix(): void
    {
        $term = new UnitTerm('m');

        $this->assertSame(1.0, $term->prefixMultiplier);
    }

    /**
     * Test prefixMultiplier property with kilo prefix.
     */
    public function testPrefixMultiplierWithKilo(): void
    {
        $term = new UnitTerm('m', 'k');

        $this->assertSame(1000.0, $term->prefixMultiplier);
    }

    /**
     * Test prefixMultiplier property with milli prefix.
     */
    public function testPrefixMultiplierWithMilli(): void
    {
        $term = new UnitTerm('m', 'm');

        $this->assertSame(0.001, $term->prefixMultiplier);
    }

    /**
     * Test multiplier property without prefix (exponent 1).
     */
    public function testMultiplierWithoutPrefix(): void
    {
        $term = new UnitTerm('m');

        $this->assertSame(1.0, $term->multiplier);
    }

    /**
     * Test multiplier property with prefix (exponent 1).
     */
    public function testMultiplierWithPrefix(): void
    {
        $term = new UnitTerm('m', 'k');

        $this->assertSame(1000.0, $term->multiplier);
    }

    /**
     * Test multiplier property with prefix and exponent.
     */
    public function testMultiplierWithPrefixAndExponent(): void
    {
        $term = new UnitTerm('m', 'k', 2);

        // 1000^2 = 1,000,000
        $this->assertSame(1e6, $term->multiplier);
    }

    /**
     * Test multiplier property with prefix and negative exponent.
     */
    public function testMultiplierWithPrefixAndNegativeExponent(): void
    {
        $term = new UnitTerm('s', 'm', -2);

        // 0.001^-2 = 1,000,000
        $this->assertSame(1e6, $term->multiplier);
    }

    /**
     * Test dimension property for base unit.
     */
    public function testDimensionPropertyForUnit(): void
    {
        $term = new UnitTerm('m');

        $this->assertSame('L', $term->dimension);
    }

    /**
     * Test dimension property with exponent.
     */
    public function testDimensionPropertyWithExponent(): void
    {
        $term = new UnitTerm('m', null, 2);

        $this->assertSame('L2', $term->dimension);
    }

    /**
     * Test dimension property with negative exponent.
     */
    public function testDimensionPropertyWithNegativeExponent(): void
    {
        $term = new UnitTerm('s', null, -2);

        $this->assertSame('T-2', $term->dimension);
    }

    // endregion

    // region regex() tests

    /**
     * Test regex matches simple unit.
     */
    public function testRegexMatchesSimpleUnit(): void
    {
        $pattern = '/^' . UnitTerm::regex() . '$/u';

        $this->assertSame(1, preg_match($pattern, 'm'));
        $this->assertSame(1, preg_match($pattern, 'km'));
        $this->assertSame(1, preg_match($pattern, 'Hz'));
    }

    /**
     * Test regex matches unit with ASCII exponent.
     */
    public function testRegexMatchesUnitWithAsciiExponent(): void
    {
        $pattern = '/^' . UnitTerm::regex() . '$/u';

        $this->assertSame(1, preg_match($pattern, 'm2'));
        $this->assertSame(1, preg_match($pattern, 's-2'));
        $this->assertSame(1, preg_match($pattern, 'km3'));
    }

    /**
     * Test regex matches unit with superscript exponent.
     */
    public function testRegexMatchesUnitWithSuperscriptExponent(): void
    {
        $pattern = '/^' . UnitTerm::regex() . '$/u';

        $this->assertSame(1, preg_match($pattern, 'm²'));
        $this->assertSame(1, preg_match($pattern, 's⁻²'));
        $this->assertSame(1, preg_match($pattern, 'km³'));
    }

    /**
     * Test regex does not match invalid formats.
     */
    public function testRegexDoesNotMatchInvalidFormats(): void
    {
        $pattern = '/^' . UnitTerm::regex() . '$/u';

        $this->assertSame(0, preg_match($pattern, '123'));
    }

    // endregion

    // region parse() tests

    /**
     * Test parse with simple unit.
     */
    public function testParseSimpleUnit(): void
    {
        $term = UnitTerm::parse('m');

        $this->assertSame('metre', $term->unit->name);
        $this->assertNull($term->prefix);
        $this->assertSame(1, $term->exponent);
    }

    /**
     * Test parse with prefixed unit.
     */
    public function testParsePrefixedUnit(): void
    {
        $term = UnitTerm::parse('km');

        $this->assertSame('metre', $term->unit->name);
        $this->assertSame('k', $term->prefix?->asciiSymbol);
        $this->assertSame(1, $term->exponent);
    }

    /**
     * Test parse with ASCII exponent.
     */
    public function testParseWithAsciiExponent(): void
    {
        $term = UnitTerm::parse('m2');

        $this->assertSame('metre', $term->unit->name);
        $this->assertSame(2, $term->exponent);
    }

    /**
     * Test parse with negative ASCII exponent.
     */
    public function testParseWithNegativeAsciiExponent(): void
    {
        $term = UnitTerm::parse('s-2');

        $this->assertSame('second', $term->unit->name);
        $this->assertSame(-2, $term->exponent);
    }

    /**
     * Test parse with superscript exponent.
     */
    public function testParseWithSuperscriptExponent(): void
    {
        $term = UnitTerm::parse('m²');

        $this->assertSame('metre', $term->unit->name);
        $this->assertSame(2, $term->exponent);
    }

    /**
     * Test parse with negative superscript exponent.
     */
    public function testParseWithNegativeSuperscriptExponent(): void
    {
        $term = UnitTerm::parse('s⁻²');

        $this->assertSame('second', $term->unit->name);
        $this->assertSame(-2, $term->exponent);
    }

    /**
     * Test parse with prefix and exponent.
     */
    public function testParseWithPrefixAndExponent(): void
    {
        $term = UnitTerm::parse('km2');

        $this->assertSame('metre', $term->unit->name);
        $this->assertSame('k', $term->prefix?->asciiSymbol);
        $this->assertSame(2, $term->exponent);
    }

    /**
     * Test parse throws for invalid format.
     */
    public function testParseThrowsForInvalidFormat(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid unit');

        UnitTerm::parse('123');
    }

    /**
     * Test parse throws for unknown unit.
     */
    public function testParseThrowsForUnknownUnit(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Unknown or unsupported unit');

        UnitTerm::parse('xyz');
    }

    /**
     * Test parse with empty string returns dimensionless scalar unit.
     */
    public function testParseEmptyStringReturnsDimensionlessUnit(): void
    {
        $term = UnitTerm::parse('');

        $this->assertSame('scalar', $term->unit->name);
        $this->assertNull($term->prefix);
        $this->assertSame(1, $term->exponent);
    }

    /**
     * Test parse throws for ASCII minus with superscript digit.
     */
    public function testParseThrowsForAsciiMinusWithSuperscriptDigit(): void
    {
        $this->expectException(FormatException::class);

        // ASCII minus with superscript digit is not valid.
        UnitTerm::parse('m-²');
    }

    /**
     * Test parse throws for exponent of zero.
     */
    public function testParseThrowsForExponentZero(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid exponent 0');

        UnitTerm::parse('m0');
    }

    // endregion

    // region format() tests

    /**
     * Test format with ASCII mode for base unit.
     */
    public function testFormatAsciiForUnit(): void
    {
        $term = new UnitTerm('m');

        $this->assertSame('m', $term->format(true));
    }

    /**
     * Test format with ASCII mode with prefix and exponent.
     */
    public function testFormatAsciiWithPrefixAndExponent(): void
    {
        $term = new UnitTerm('m', 'k', 2);

        $this->assertSame('km2', $term->format(true));
    }

    /**
     * Test format with Unicode mode (default) for base unit.
     */
    public function testFormatUnicodeForUnit(): void
    {
        $term = new UnitTerm('m');

        $this->assertSame('m', $term->format());
    }

    /**
     * Test format with Unicode mode converts exponent to superscript.
     */
    public function testFormatUnicodeConvertsSuperscript(): void
    {
        $term = new UnitTerm('m', 'k', 2);

        $this->assertSame('km²', $term->format());
    }

    /**
     * Test format with Unicode mode uses Unicode symbol.
     */
    public function testFormatUnicodeUsesUnicodeSymbol(): void
    {
        $term = new UnitTerm('ohm', 'k');

        $this->assertSame('kΩ', $term->format());
    }

    /**
     * Test format with negative exponent.
     */
    public function testFormatWithNegativeExponent(): void
    {
        $term = new UnitTerm('s', null, -2);

        $this->assertSame('s⁻²', $term->format());
        $this->assertSame('s-2', $term->format(true));
    }

    // endregion

    // region __toString() tests

    /**
     * Test __toString returns Unicode format (with superscript exponent).
     */
    public function testToStringReturnsUnicodeFormat(): void
    {
        $term = new UnitTerm('m', 'k', 2);

        $this->assertSame('km²', (string)$term);
    }

    /**
     * Test __toString with negative exponent returns Unicode superscript.
     */
    public function testToStringWithNegativeExponent(): void
    {
        $term = new UnitTerm('s', 'm', -2);

        $this->assertSame('ms⁻²', (string)$term);
    }

    /**
     * Test __toString uses Unicode symbol when available.
     */
    public function testToStringUsesUnicodeSymbol(): void
    {
        $term = new UnitTerm('ohm', 'k');

        $this->assertSame('kΩ', (string)$term);
    }

    /**
     * Test __toString with exponent of 1 omits exponent.
     */
    public function testToStringOmitsExponentOfOne(): void
    {
        $term = new UnitTerm('m');

        $this->assertSame('m', (string)$term);
    }

    // endregion

    // region Transformation method tests

    /**
     * Test inv() negates exponent.
     */
    public function testInvNegatesExponent(): void
    {
        $term = new UnitTerm('m', 'k', 2);

        $inverted = $term->inv();

        $this->assertSame(-2, $inverted->exponent);
        $this->assertSame('k', $inverted->prefix?->asciiSymbol);
        $this->assertSame($term->unit, $inverted->unit);
    }

    /**
     * Test inv() returns new instance.
     */
    public function testInvReturnsNewInstance(): void
    {
        $term = new UnitTerm('m', null, 2);

        $inverted = $term->inv();

        $this->assertNotSame($term, $inverted);
    }

    /**
     * Test withExponent() changes exponent.
     */
    public function testWithExponentChangesExponent(): void
    {
        $term = new UnitTerm('m', 'k', 2);

        $newTerm = $term->withExponent(3);

        $this->assertSame(3, $newTerm->exponent);
        $this->assertSame('k', $newTerm->prefix?->asciiSymbol);
    }

    /**
     * Test withExponent() returns new instance.
     */
    public function testWithExponentReturnsNewInstance(): void
    {
        $term = new UnitTerm('m');

        $newTerm = $term->withExponent(2);

        $this->assertNotSame($term, $newTerm);
    }

    /**
     * Test pow() multiplies exponents.
     */
    public function testPowMultipliesExponents(): void
    {
        $term = new UnitTerm('m', null, 2);

        $newTerm = $term->pow(3);

        $this->assertSame(6, $newTerm->exponent);
    }

    /**
     * Test removeExponent() resets to 1.
     */
    public function testRemoveExponentResetsToOne(): void
    {
        $term = new UnitTerm('m', 'k', 3);

        $newTerm = $term->removeExponent();

        $this->assertSame(1, $newTerm->exponent);
        $this->assertSame('k', $newTerm->prefix?->asciiSymbol);
    }

    /**
     * Test removePrefix() removes prefix.
     */
    public function testRemovePrefixRemovesPrefix(): void
    {
        $term = new UnitTerm('m', 'k', 2);

        $newTerm = $term->removePrefix();

        $this->assertNull($newTerm->prefix);
        $this->assertSame(2, $newTerm->exponent);
    }

    /**
     * Test removePrefix returns same instance when no prefix exists.
     */
    public function testRemovePrefixReturnsSameInstanceWhenNoPrefix(): void
    {
        $term = new UnitTerm('m', null, 2);

        $newTerm = $term->removePrefix();

        $this->assertSame($term, $newTerm);
    }

    // endregion

    // region equal() tests

    /**
     * Test equal returns true for same instance.
     */
    public function testEqualReturnsTrueForSameInstance(): void
    {
        $term = new UnitTerm('m', 'k', 2);

        $this->assertTrue($term->equal($term));
    }

    /**
     * Test equal returns true for equivalent terms.
     */
    public function testEqualReturnsTrueForEquivalentTerms(): void
    {
        $term1 = new UnitTerm('m', 'k', 2);
        $term2 = new UnitTerm('m', 'k', 2);

        $this->assertTrue($term1->equal($term2));
    }

    /**
     * Test equal returns false for different base units.
     */
    public function testEqualReturnsFalseForDifferentUnits(): void
    {
        $term1 = new UnitTerm('m');
        $term2 = new UnitTerm('s');

        $this->assertFalse($term1->equal($term2));
    }

    /**
     * Test equal returns false for different prefixes.
     */
    public function testEqualReturnsFalseForDifferentPrefixes(): void
    {
        $term1 = new UnitTerm('m', 'k');
        $term2 = new UnitTerm('m', 'M');

        $this->assertFalse($term1->equal($term2));
    }

    /**
     * Test equal returns false for different exponents.
     */
    public function testEqualReturnsFalseForDifferentExponents(): void
    {
        $term1 = new UnitTerm('m', null, 2);
        $term2 = new UnitTerm('m', null, 3);

        $this->assertFalse($term1->equal($term2));
    }

    /**
     * Test equal returns false for different types.
     */
    public function testEqualReturnsFalseForDifferentTypes(): void
    {
        $term = new UnitTerm('m');

        $this->assertFalse($term->equal('m'));
        $this->assertFalse($term->equal(1));
        $this->assertFalse($term->equal(null));
        $this->assertFalse($term->equal(new stdClass()));
    }

    // endregion

    // region getBySymbol() tests

    /**
     * Test getBySymbol finds unprefixed unit.
     */
    public function testGetBySymbolFindsUnprefixedUnit(): void
    {
        $match = UnitTerm::getBySymbol('m');

        $this->assertInstanceOf(UnitTerm::class, $match);
        $this->assertSame('metre', $match->unit->name);
        $this->assertNull($match->prefix);
        $this->assertSame(1, $match->exponent);
    }

    /**
     * Test getBySymbol finds prefixed unit.
     */
    public function testGetBySymbolFindsPrefixedUnit(): void
    {
        $match = UnitTerm::getBySymbol('km');

        $this->assertInstanceOf(UnitTerm::class, $match);
        $this->assertSame('metre', $match->unit->name);
        $this->assertSame('k', $match->prefix?->asciiSymbol);
    }

    /**
     * Test getBySymbol finds unit by Unicode symbol.
     */
    public function testGetBySymbolFindsByUnicodeSymbol(): void
    {
        $match = UnitTerm::getBySymbol('Ω');

        $this->assertInstanceOf(UnitTerm::class, $match);
        $this->assertSame('ohm', $match->unit->name);
    }

    /**
     * Test getBySymbol finds prefixed unit by Unicode symbol.
     */
    public function testGetBySymbolFindsPrefixedByUnicodeSymbol(): void
    {
        $match = UnitTerm::getBySymbol('kΩ');

        $this->assertInstanceOf(UnitTerm::class, $match);
        $this->assertSame('ohm', $match->unit->name);
        $this->assertSame('k', $match->prefix?->asciiSymbol);
    }

    /**
     * Test getBySymbol returns null for unknown symbol.
     */
    public function testGetBySymbolReturnsNullForUnknown(): void
    {
        $match = UnitTerm::getBySymbol('xyz');

        $this->assertNull($match);
    }

    /**
     * Test getBySymbol returns UnitTerm with exponent 1.
     */
    public function testGetBySymbolReturnsUnitTermWithExponentOne(): void
    {
        $match = UnitTerm::getBySymbol('s');

        $this->assertInstanceOf(UnitTerm::class, $match);
        $this->assertSame('second', $match->unit->name);
        $this->assertSame(1, $match->exponent);
    }

    /**
     * Test getBySymbol finds scalar unit with empty string.
     */
    public function testGetBySymbolFindsScalarWithEmptyString(): void
    {
        $match = UnitTerm::getBySymbol('');

        $this->assertInstanceOf(UnitTerm::class, $match);
        $this->assertSame('scalar', $match->unit->name);
    }

    /**
     * Test getBySymbol finds unit by alternate symbol.
     */
    public function testGetBySymbolFindsByAlternateSymbol(): void
    {
        // 'u' is the alternate symbol for 'micro' prefix on some units.
        // 'um' should match micrometre.
        $match = UnitTerm::getBySymbol('um');

        $this->assertInstanceOf(UnitTerm::class, $match);
        $this->assertSame('metre', $match->unit->name);
        $this->assertSame('u', $match->prefix?->asciiSymbol);
    }

    // endregion

    // region toUnitTerm() tests

    /**
     * Test toUnitTerm with UnitTerm returns same instance.
     */
    public function testToUnitTermWithUnitTermReturnsSame(): void
    {
        $term = new UnitTerm('m', 'k', 2);
        $result = UnitTerm::toUnitTerm($term);

        $this->assertSame($term, $result);
    }

    /**
     * Test toUnitTerm with string parses it.
     */
    public function testToUnitTermWithStringParsesIt(): void
    {
        $result = UnitTerm::toUnitTerm('km2');

        $this->assertInstanceOf(UnitTerm::class, $result);
        $this->assertSame('metre', $result->unit->name);
        $this->assertSame('k', $result->prefix?->asciiSymbol);
        $this->assertSame(2, $result->exponent);
    }

    /**
     * Test toUnitTerm with Unit creates UnitTerm.
     */
    public function testToUnitTermWithUnitCreatesUnitTerm(): void
    {
        $unit = UnitRegistry::getBySymbol('m');

        $this->assertInstanceOf(Unit::class, $unit);
        $result = UnitTerm::toUnitTerm($unit);

        $this->assertInstanceOf(UnitTerm::class, $result);
        $this->assertSame('metre', $result->unit->name);
        $this->assertNull($result->prefix);
        $this->assertSame(1, $result->exponent);
    }

    // endregion

    // region Constructor with object arguments tests

    /**
     * Test constructor with Unit object instead of string.
     */
    public function testConstructorWithUnitObject(): void
    {
        $unit = UnitRegistry::getBySymbol('m');
        $this->assertInstanceOf(Unit::class, $unit);

        $term = new UnitTerm($unit);
        $this->assertSame($unit, $term->unit);
        $this->assertNull($term->prefix);
        $this->assertSame(1, $term->exponent);
    }

    /**
     * Test constructor with Unit object and prefix string.
     */
    public function testConstructorWithUnitObjectAndPrefixString(): void
    {
        $unit = UnitRegistry::getBySymbol('m');
        $this->assertInstanceOf(Unit::class, $unit);

        $term = new UnitTerm($unit, 'k', 2);

        $this->assertSame($unit, $term->unit);
        $this->assertSame('k', $term->prefix?->asciiSymbol);
        $this->assertSame(2, $term->exponent);
    }

    /**
     * Test constructor with Prefix object instead of string.
     */
    public function testConstructorWithPrefixObject(): void
    {
        $prefix = PrefixRegistry::getBySymbol('k');
        $term = new UnitTerm('m', $prefix, 2);

        $this->assertSame($prefix, $term->prefix);
        $this->assertSame('km2', $term->asciiSymbol);
    }

    /**
     * Test constructor with both Unit and Prefix objects.
     */
    public function testConstructorWithUnitAndPrefixObjects(): void
    {
        $unit = UnitRegistry::getBySymbol('m');
        $this->assertInstanceOf(Unit::class, $unit);

        $prefix = PrefixRegistry::getBySymbol('M');
        $term = new UnitTerm($unit, $prefix, 3);

        $this->assertSame($unit, $term->unit);
        $this->assertSame($prefix, $term->prefix);
        $this->assertSame(3, $term->exponent);
        $this->assertSame('Mm3', $term->asciiSymbol);
    }

    // endregion

    // region unicodeSymbol property tests

    /**
     * Test unicodeSymbol property directly.
     */
    public function testUnicodeSymbolProperty(): void
    {
        $term = new UnitTerm('m', 'k', 2);

        $this->assertSame('km²', $term->unicodeSymbol);
    }

    /**
     * Test unicodeSymbol property uses Unicode unit symbol.
     */
    public function testUnicodeSymbolPropertyUsesUnicodeUnitSymbol(): void
    {
        $term = new UnitTerm('ohm');

        $this->assertSame('Ω', $term->unicodeSymbol);
    }

    /**
     * Test unicodeSymbol property with prefix and exponent on Unicode unit.
     */
    public function testUnicodeSymbolPropertyWithPrefixAndExponentOnUnicodeUnit(): void
    {
        $term = new UnitTerm('ohm', 'k', -1);

        $this->assertSame('kΩ⁻¹', $term->unicodeSymbol);
    }

    // endregion

    // region isSi() tests

    /**
     * Test isSi returns true for SI unit.
     */
    public function testIsSiReturnsTrueForSiUnit(): void
    {
        $term = new UnitTerm('m');

        $this->assertTrue($term->isSi());
    }

    /**
     * Test isSi returns true for prefixed SI unit.
     */
    public function testIsSiReturnsTrueForPrefixedSiUnit(): void
    {
        $term = new UnitTerm('m', 'k', 2);

        $this->assertTrue($term->isSi());
    }

    /**
     * Test isSi returns false for Imperial unit.
     */
    public function testIsSiReturnsFalseForImperialUnit(): void
    {
        $term = new UnitTerm('ft');

        $this->assertFalse($term->isSi());
    }

    // endregion

    // region isBase() tests

    /**
     * Test isBase returns true for base unit.
     */
    public function testIsBaseReturnsTrueForBaseUnit(): void
    {
        $term = new UnitTerm('m');

        $this->assertTrue($term->isBase());
    }

    /**
     * Test isBase returns true for base unit with exponent.
     */
    public function testIsBaseReturnsTrueForBaseUnitWithExponent(): void
    {
        $term = new UnitTerm('m', null, 2);

        $this->assertTrue($term->isBase());
    }

    /**
     * Test isBase returns false for derived unit.
     */
    public function testIsBaseReturnsFalseForDerivedUnit(): void
    {
        // Newton has multiple dimension terms.
        $term = new UnitTerm('N');

        $this->assertFalse($term->isBase());
    }

    // endregion

    // region isSiBase() tests

    /**
     * Test isSiBase returns true for metre.
     */
    public function testIsSiBaseReturnsTrueForMetre(): void
    {
        $term = new UnitTerm('m');

        $this->assertTrue($term->isSiBase());
    }

    /**
     * Test isSiBase returns true for kilogram (the SI base mass unit has prefix).
     */
    public function testIsSiBaseReturnsTrueForKilogram(): void
    {
        $term = new UnitTerm('g', 'k');

        $this->assertTrue($term->isSiBase());
    }

    /**
     * Test isSiBase returns true for second.
     */
    public function testIsSiBaseReturnsTrueForSecond(): void
    {
        $term = new UnitTerm('s');

        $this->assertTrue($term->isSiBase());
    }

    /**
     * Test isSiBase returns false for prefixed non-kg unit.
     */
    public function testIsSiBaseReturnsFalseForPrefixedUnit(): void
    {
        // km is not an SI base unit; m is.
        $term = new UnitTerm('m', 'k');

        $this->assertFalse($term->isSiBase());
    }

    /**
     * Test isSiBase returns true for SI base unit with exponent.
     */
    public function testIsSiBaseReturnsTrueForUnitWithExponent(): void
    {
        $term = new UnitTerm('m', null, 2);

        $this->assertTrue($term->isSiBase());
    }

    /**
     * Test isSiBase returns false for non-SI unit.
     */
    public function testIsSiBaseReturnsFalseForNonSiUnit(): void
    {
        $term = new UnitTerm('ft');

        $this->assertFalse($term->isSiBase());
    }

    /**
     * Test isSiBase returns false for named derived SI unit.
     */
    public function testIsSiBaseReturnsFalseForNamedDerivedUnit(): void
    {
        $term = new UnitTerm('N');

        $this->assertFalse($term->isSiBase());
    }

    /**
     * Test isSiBase returns false for gram without prefix.
     */
    public function testIsSiBaseReturnsFalseForGramWithoutPrefix(): void
    {
        // The SI base for mass is kg, not g.
        $term = new UnitTerm('g');

        $this->assertFalse($term->isSiBase());
    }

    // endregion

    // region isExpandable() tests

    /**
     * Test isExpandable returns true for Newton.
     */
    public function testIsExpandableReturnsTrueForNewton(): void
    {
        $term = new UnitTerm('N');

        $this->assertTrue($term->isExpandable());
    }

    /**
     * Test isExpandable returns true for prefixed expandable unit.
     */
    public function testIsExpandableReturnsTrueForPrefixedExpandableUnit(): void
    {
        $term = new UnitTerm('N', 'k');

        $this->assertTrue($term->isExpandable());
    }

    /**
     * Test isExpandable returns true for Hertz.
     */
    public function testIsExpandableReturnsTrueForHertz(): void
    {
        $term = new UnitTerm('Hz');

        $this->assertTrue($term->isExpandable());
    }

    /**
     * Test isExpandable returns false for base SI unit.
     */
    public function testIsExpandableReturnsFalseForBaseSiUnit(): void
    {
        $term = new UnitTerm('m');

        $this->assertFalse($term->isExpandable());
    }

    /**
     * Test isExpandable returns false for non-SI base unit.
     */
    public function testIsExpandableReturnsFalseForNonSiBaseUnit(): void
    {
        $term = new UnitTerm('ft');

        $this->assertFalse($term->isExpandable());
    }

    /**
     * Test isExpandable is independent of exponent.
     */
    public function testIsExpandableIsIndependentOfExponent(): void
    {
        $term = new UnitTerm('N', null, 3);

        $this->assertTrue($term->isExpandable());
    }

    // endregion
}
