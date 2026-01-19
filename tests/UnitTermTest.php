<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests;

use DomainException;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\Unit;
use Galaxon\Quantities\UnitTerm;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Tests for UnitTerm class.
 */
#[CoversClass(UnitTerm::class)]
final class UnitTermTest extends TestCase
{
    /**
     * Create an ohm Unit (has Unicode symbol) for testing.
     */
    private function createOhmUnit(): Unit
    {
        return new Unit('ohm', [
            'asciiSymbol'   => 'ohm',
            'unicodeSymbol' => 'Ω',
            'dimension'     => 'T-3L2MI-2',
            'system'        => 'si_named',
            'prefixGroup'   => PrefixRegistry::PREFIX_GROUP_METRIC,
            'expansionUnit' => 'kg*m2*s-3*A-2',
            'quantityType'  => 'resistance',
        ]);
    }

    // region Test fixtures

    /**
     * Create a metre Unit for testing.
     */
    private function createMetreUnit(): Unit
    {
        return new Unit(
            'metre',
            [
                'asciiSymbol'  => 'm',
                'dimension'    => 'L',
                'system'       => 'si_base',
                'prefixGroup'  => PrefixRegistry::PREFIX_GROUP_METRIC,
                'quantityType' => 'length',
            ]
        );
    }

    /**
     * Create a second Unit for testing.
     */
    private function createSecondUnit(): Unit
    {
        return new Unit('second', [
            'asciiSymbol'  => 's',
            'dimension'    => 'T',
            'system'       => 'si_base',
            'prefixGroup'  => PrefixRegistry::PREFIX_GROUP_METRIC,
            'quantityType' => 'time',
        ]);
    }

    /**
     * Create a hectare Unit (no prefixes allowed) for testing.
     */
    private function createHectareUnit(): Unit
    {
        return new Unit('hectare', [
            'asciiSymbol'  => 'ha',
            'dimension'    => 'L2',
            'system'       => 'metric',
            'quantityType' => 'area',
        ]);
    }

    // endregion

    // region Constructor tests

    /**
     * Test constructor with base unit only.
     */
    public function testConstructorWithUnitOnly(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base);

        $this->assertSame($base, $term->unit);
        $this->assertNull($term->prefix);
        $this->assertSame(1, $term->exponent);
    }

    /**
     * Test constructor with prefix.
     */
    public function testConstructorWithPrefix(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base, 'k');

        $this->assertSame('k', $term->prefix);
        $this->assertSame(1, $term->exponent);
    }

    /**
     * Test constructor with exponent.
     */
    public function testConstructorWithExponent(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base, null, 2);

        $this->assertNull($term->prefix);
        $this->assertSame(2, $term->exponent);
    }

    /**
     * Test constructor with prefix and exponent.
     */
    public function testConstructorWithPrefixAndExponent(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base, 'k', 2);

        $this->assertSame('k', $term->prefix);
        $this->assertSame(2, $term->exponent);
    }

    /**
     * Test constructor with negative exponent.
     */
    public function testConstructorWithNegativeExponent(): void
    {
        $base = $this->createSecondUnit();
        $term = new UnitTerm($base, null, -2);

        $this->assertSame(-2, $term->exponent);
    }

    /**
     * Test constructor throws for invalid prefix.
     */
    public function testConstructorThrowsForInvalidPrefix(): void
    {
        $base = $this->createMetreUnit();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Prefix 'invalid' is invalid");

        new UnitTerm($base, 'invalid');
    }

    /**
     * Test constructor throws for prefix on unit that doesn't accept prefixes.
     */
    public function testConstructorThrowsForPrefixOnNonPrefixableUnit(): void
    {
        $base = $this->createHectareUnit();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('is invalid');

        new UnitTerm($base, 'k');
    }

    /**
     * Test constructor throws for exponent of zero.
     */
    public function testConstructorThrowsForExponentZero(): void
    {
        $base = $this->createMetreUnit();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("can't be zero");

        new UnitTerm($base, null, 0);
    }

    /**
     * Test constructor throws for exponent out of range (positive).
     */
    public function testConstructorThrowsForExponentTooLarge(): void
    {
        $base = $this->createMetreUnit();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('must be between -9 and 9');

        new UnitTerm($base, null, 10);
    }

    /**
     * Test constructor throws for exponent out of range (negative).
     */
    public function testConstructorThrowsForExponentTooSmall(): void
    {
        $base = $this->createMetreUnit();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('must be between -9 and 9');

        new UnitTerm($base, null, -10);
    }

    /**
     * Test constructor with string unit symbol.
     */
    public function testConstructorWithStringSymbol(): void
    {
        $term = new UnitTerm('m');

        $this->assertSame('metre', $term->unit->name);
        $this->assertSame('m', $term->unit->asciiSymbol);
        $this->assertNull($term->prefix);
        $this->assertSame(1, $term->exponent);
    }

    /**
     * Test constructor with string unit symbol and prefix.
     */
    public function testConstructorWithStringSymbolAndPrefix(): void
    {
        $term = new UnitTerm('m', 'k');

        $this->assertSame('metre', $term->unit->name);
        $this->assertSame('k', $term->prefix);
        $this->assertSame(1, $term->exponent);
    }

    /**
     * Test constructor with string unit symbol, prefix, and exponent.
     */
    public function testConstructorWithStringSymbolPrefixAndExponent(): void
    {
        $term = new UnitTerm('s', 'm', -2);

        $this->assertSame('second', $term->unit->name);
        $this->assertSame('m', $term->prefix);
        $this->assertSame(-2, $term->exponent);
    }

    /**
     * Test constructor with various string unit symbols.
     */
    public function testConstructorWithVariousStringSymbols(): void
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
     * Test constructor throws for unknown string unit symbol.
     */
    public function testConstructorThrowsForUnknownStringSymbol(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Unit 'xyz' is invalid");

        new UnitTerm('xyz');
    }

    // endregion

    // region Computed property tests

    /**
     * Test symbol property for base unit.
     */
    public function testSymbolPropertyForUnit(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base);

        $this->assertSame('m', $term->symbol);
    }

    /**
     * Test symbol property with prefix.
     */
    public function testSymbolPropertyWithPrefix(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base, 'k');

        $this->assertSame('km', $term->symbol);
    }

    /**
     * Test symbol property with exponent.
     */
    public function testSymbolPropertyWithExponent(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base, null, 2);

        $this->assertSame('m2', $term->symbol);
    }

    /**
     * Test symbol property with prefix and exponent.
     */
    public function testSymbolPropertyWithPrefixAndExponent(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base, 'k', 2);

        $this->assertSame('km2', $term->symbol);
    }

    /**
     * Test symbol property with negative exponent.
     */
    public function testSymbolPropertyWithNegativeExponent(): void
    {
        $base = $this->createSecondUnit();
        $term = new UnitTerm($base, null, -2);

        $this->assertSame('s-2', $term->symbol);
    }

    /**
     * Test symbolWithoutPrefix property.
     */
    public function testSymbolWithoutPrefixProperty(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base, 'k', 2);

        $this->assertSame('m2', $term->unprefixedSymbol);
    }

    /**
     * Test symbolWithoutExponent property.
     */
    public function testSymbolWithoutExponentProperty(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base, 'k', 2);

        $this->assertSame('km', $term->unexponentiatedSymbol);
    }

    /**
     * Test prefixMultiplier property without prefix.
     */
    public function testPrefixMultiplierWithoutPrefix(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base);

        $this->assertSame(1.0, $term->prefixMultiplier);
    }

    /**
     * Test prefixMultiplier property with kilo prefix.
     */
    public function testPrefixMultiplierWithKilo(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base, 'k');

        $this->assertSame(1000.0, $term->prefixMultiplier);
    }

    /**
     * Test prefixMultiplier property with milli prefix.
     */
    public function testPrefixMultiplierWithMilli(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base, 'm');

        $this->assertSame(0.001, $term->prefixMultiplier);
    }

    /**
     * Test multiplier property without prefix (exponent 1).
     */
    public function testMultiplierWithoutPrefix(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base);

        $this->assertSame(1.0, $term->multiplier);
    }

    /**
     * Test multiplier property with prefix (exponent 1).
     */
    public function testMultiplierWithPrefix(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base, 'k');

        $this->assertSame(1000.0, $term->multiplier);
    }

    /**
     * Test multiplier property with prefix and exponent.
     */
    public function testMultiplierWithPrefixAndExponent(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base, 'k', 2);

        // 1000^2 = 1,000,000
        $this->assertSame(1e6, $term->multiplier);
    }

    /**
     * Test multiplier property with prefix and negative exponent.
     */
    public function testMultiplierWithPrefixAndNegativeExponent(): void
    {
        $base = $this->createSecondUnit();
        $term = new UnitTerm($base, 'm', -2);

        // 0.001^-2 = 1,000,000
        $this->assertSame(1e6, $term->multiplier);
    }

    /**
     * Test dimension property for base unit.
     */
    public function testDimensionPropertyForUnit(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base);

        $this->assertSame('L', $term->dimension);
    }

    /**
     * Test dimension property with exponent.
     */
    public function testDimensionPropertyWithExponent(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base, null, 2);

        $this->assertSame('L2', $term->dimension);
    }

    /**
     * Test dimension property with negative exponent.
     */
    public function testDimensionPropertyWithNegativeExponent(): void
    {
        $base = $this->createSecondUnit();
        $term = new UnitTerm($base, null, -2);

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
        $this->assertSame(0, preg_match($pattern, ''));
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
        $this->assertSame('k', $term->prefix);
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
        $this->assertSame('k', $term->prefix);
        $this->assertSame(2, $term->exponent);
    }

    /**
     * Test parse throws for invalid format.
     */
    public function testParseThrowsForInvalidFormat(): void
    {
        $this->expectException(DomainException::class);
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
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base);

        $this->assertSame('m', $term->format(true));
    }

    /**
     * Test format with ASCII mode with prefix and exponent.
     */
    public function testFormatAsciiWithPrefixAndExponent(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base, 'k', 2);

        $this->assertSame('km2', $term->format(true));
    }

    /**
     * Test format with Unicode mode (default) for base unit.
     */
    public function testFormatUnicodeForUnit(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base);

        $this->assertSame('m', $term->format());
    }

    /**
     * Test format with Unicode mode converts exponent to superscript.
     */
    public function testFormatUnicodeConvertsSuperscript(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base, 'k', 2);

        $this->assertSame('km²', $term->format());
    }

    /**
     * Test format with Unicode mode uses Unicode symbol.
     */
    public function testFormatUnicodeUsesUnicodeSymbol(): void
    {
        $base = $this->createOhmUnit();
        $term = new UnitTerm($base, 'k');

        $this->assertSame('kΩ', $term->format());
    }

    /**
     * Test format with negative exponent.
     */
    public function testFormatWithNegativeExponent(): void
    {
        $base = $this->createSecondUnit();
        $term = new UnitTerm($base, null, -2);

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
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base, 'k', 2);

        $this->assertSame('km²', (string)$term);
    }

    /**
     * Test __toString with negative exponent returns Unicode superscript.
     */
    public function testToStringWithNegativeExponent(): void
    {
        $base = $this->createSecondUnit();
        $term = new UnitTerm($base, 'm', -2);

        $this->assertSame('ms⁻²', (string)$term);
    }

    /**
     * Test __toString uses Unicode symbol when available.
     */
    public function testToStringUsesUnicodeSymbol(): void
    {
        $base = $this->createOhmUnit();
        $term = new UnitTerm($base, 'k');

        $this->assertSame('kΩ', (string)$term);
    }

    /**
     * Test __toString with exponent of 1 omits exponent.
     */
    public function testToStringOmitsExponentOfOne(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base);

        $this->assertSame('m', (string)$term);
    }

    // endregion

    // region Transformation method tests

    /**
     * Test inv() negates exponent.
     */
    public function testInvNegatesExponent(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base, 'k', 2);

        $inverted = $term->inv();

        $this->assertSame(-2, $inverted->exponent);
        $this->assertSame('k', $inverted->prefix);
        $this->assertSame($term->unit, $inverted->unit);
    }

    /**
     * Test inv() returns new instance.
     */
    public function testInvReturnsNewInstance(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base, null, 2);

        $inverted = $term->inv();

        $this->assertNotSame($term, $inverted);
    }

    /**
     * Test withExponent() changes exponent.
     */
    public function testWithExponentChangesExponent(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base, 'k', 2);

        $newTerm = $term->withExponent(3);

        $this->assertSame(3, $newTerm->exponent);
        $this->assertSame('k', $newTerm->prefix);
    }

    /**
     * Test withExponent() returns new instance.
     */
    public function testWithExponentReturnsNewInstance(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base);

        $newTerm = $term->withExponent(2);

        $this->assertNotSame($term, $newTerm);
    }

    /**
     * Test applyExponent() multiplies exponents.
     */
    public function testApplyExponentMultipliesExponents(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base, null, 2);

        $newTerm = $term->applyExponent(3);

        $this->assertSame(6, $newTerm->exponent);
    }

    /**
     * Test removeExponent() resets to 1.
     */
    public function testRemoveExponentResetsToOne(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base, 'k', 3);

        $newTerm = $term->removeExponent();

        $this->assertSame(1, $newTerm->exponent);
        $this->assertSame('k', $newTerm->prefix);
    }

    /**
     * Test withPrefix() changes prefix.
     */
    public function testWithPrefixChangesPrefix(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base, 'k', 2);

        $newTerm = $term->withPrefix('M');

        $this->assertSame('M', $newTerm->prefix);
        $this->assertSame(2, $newTerm->exponent);
    }

    /**
     * Test withPrefix() can set to null.
     */
    public function testWithPrefixCanSetToNull(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base, 'k');

        $newTerm = $term->withPrefix(null);

        $this->assertNull($newTerm->prefix);
    }

    /**
     * Test withPrefix() returns new instance.
     */
    public function testWithPrefixReturnsNewInstance(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base, 'k');

        $newTerm = $term->withPrefix('M');

        $this->assertNotSame($term, $newTerm);
    }

    /**
     * Test removePrefix() removes prefix.
     */
    public function testRemovePrefixRemovesPrefix(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base, 'k', 2);

        $newTerm = $term->removePrefix();

        $this->assertNull($newTerm->prefix);
        $this->assertSame(2, $newTerm->exponent);
    }

    // endregion

    // region equal() tests

    /**
     * Test equal returns true for same instance.
     */
    public function testEqualReturnsTrueForSameInstance(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base, 'k', 2);

        $this->assertTrue($term->equal($term));
    }

    /**
     * Test equal returns true for equivalent terms.
     */
    public function testEqualReturnsTrueForEquivalentTerms(): void
    {
        $base1 = $this->createMetreUnit();
        $base2 = $this->createMetreUnit();
        $term1 = new UnitTerm($base1, 'k', 2);
        $term2 = new UnitTerm($base2, 'k', 2);

        $this->assertTrue($term1->equal($term2));
    }

    /**
     * Test equal returns false for different base units.
     */
    public function testEqualReturnsFalseForDifferentUnits(): void
    {
        $metre = $this->createMetreUnit();
        $second = $this->createSecondUnit();
        $term1 = new UnitTerm($metre);
        $term2 = new UnitTerm($second);

        $this->assertFalse($term1->equal($term2));
    }

    /**
     * Test equal returns false for different prefixes.
     */
    public function testEqualReturnsFalseForDifferentPrefixes(): void
    {
        $base = $this->createMetreUnit();
        $term1 = new UnitTerm($base, 'k');
        $term2 = new UnitTerm($base, 'M');

        $this->assertFalse($term1->equal($term2));
    }

    /**
     * Test equal returns false for different exponents.
     */
    public function testEqualReturnsFalseForDifferentExponents(): void
    {
        $base = $this->createMetreUnit();
        $term1 = new UnitTerm($base, null, 2);
        $term2 = new UnitTerm($base, null, 3);

        $this->assertFalse($term1->equal($term2));
    }

    /**
     * Test equal returns false for different types.
     */
    public function testEqualReturnsFalseForDifferentTypes(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base);

        $this->assertFalse($term->equal('m'));
        $this->assertFalse($term->equal(1));
        $this->assertFalse($term->equal(null));
        $this->assertFalse($term->equal(new stdClass()));
    }

    // endregion

    // region isSi() tests

    /**
     * Test isSi returns true for SI base unit.
     */
    public function testIsSiReturnsTrueForSiUnit(): void
    {
        $base = $this->createMetreUnit();
        $term = new UnitTerm($base, 'k', 2);

        $this->assertTrue($term->isSi());
    }

    /**
     * Test isSi returns false for non-SI unit.
     */
    public function testIsSiReturnsFalseForNonSiUnit(): void
    {
        $base = new Unit('foot', [
            'asciiSymbol'  => 'ft',
            'quantityType' => 'length',
            'dimension'    => 'L',
            'system'       => 'us_customary',
        ]);
        $term = new UnitTerm($base);

        $this->assertFalse($term->isSi());
    }

    // endregion

    // region getBySymbol() tests

    /**
     * Test getBySymbol finds unprefixed unit.
     */
    public function testGetBySymbolFindsUnprefixedUnit(): void
    {
        $matches = UnitTerm::getBySymbol('m');

        $this->assertNotEmpty($matches);
        $this->assertContainsOnlyInstancesOf(UnitTerm::class, $matches);

        // Find the metre match (should be unprefixed)
        $metreMatch = null;
        foreach ($matches as $match) {
            if ($match->unit->name === 'metre' && !$match->hasPrefix()) {
                $metreMatch = $match;
                break;
            }
        }

        $this->assertNotNull($metreMatch);
        $this->assertSame('metre', $metreMatch->unit->name);
        $this->assertNull($metreMatch->prefix);
    }

    /**
     * Test getBySymbol finds prefixed unit.
     */
    public function testGetBySymbolFindsPrefixedUnit(): void
    {
        $matches = UnitTerm::getBySymbol('km');

        $this->assertNotEmpty($matches);

        // Should find kilometre
        $found = false;
        foreach ($matches as $match) {
            if ($match->unit->name === 'metre' && $match->prefix === 'k') {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Should find kilometre (km)');
    }

    /**
     * Test getBySymbol finds unit by Unicode symbol.
     */
    public function testGetBySymbolFindsByUnicodeSymbol(): void
    {
        $matches = UnitTerm::getBySymbol('Ω');

        $this->assertNotEmpty($matches);

        // Should find ohm
        $found = false;
        foreach ($matches as $match) {
            if ($match->unit->name === 'ohm') {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Should find ohm by Unicode symbol Ω');
    }

    /**
     * Test getBySymbol finds prefixed unit by Unicode symbol.
     */
    public function testGetBySymbolFindsPrefixedByUnicodeSymbol(): void
    {
        $matches = UnitTerm::getBySymbol('kΩ');

        $this->assertNotEmpty($matches);

        // Should find kilo-ohm
        $found = false;
        foreach ($matches as $match) {
            if ($match->unit->name === 'ohm' && $match->prefix === 'k') {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Should find kilo-ohm by Unicode symbol kΩ');
    }

    /**
     * Test getBySymbol returns empty array for unknown symbol.
     */
    public function testGetBySymbolReturnsEmptyForUnknown(): void
    {
        $matches = UnitTerm::getBySymbol('xyz');

        $this->assertIsArray($matches);
        $this->assertEmpty($matches);
    }

    /**
     * Test getBySymbol returns array of UnitTerm objects.
     */
    public function testGetBySymbolReturnsUnitTermArray(): void
    {
        $matches = UnitTerm::getBySymbol('s');

        $this->assertIsArray($matches);
        $this->assertNotEmpty($matches);

        foreach ($matches as $match) {
            $this->assertInstanceOf(UnitTerm::class, $match);
            $this->assertSame(1, $match->exponent);
        }
    }

    // endregion
}
