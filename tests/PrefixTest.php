<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Quantities\Prefix;
use Galaxon\Quantities\Registry\PrefixRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Prefix class.
 */
#[CoversClass(Prefix::class)]
final class PrefixTest extends TestCase
{
    // region Constructor tests

    /**
     * Test constructor creates prefix with valid parameters.
     */
    public function testConstructorWithValidParameters(): void
    {
        $prefix = new Prefix('kilo', 'k', null, 1000.0, PrefixRegistry::GROUP_LARGE_ENG_METRIC);

        $this->assertSame('kilo', $prefix->name);
        $this->assertSame('k', $prefix->asciiSymbol);
        $this->assertSame('k', $prefix->unicodeSymbol);  // Falls back to ASCII
        $this->assertSame(1000.0, $prefix->multiplier);
        $this->assertSame(PrefixRegistry::GROUP_LARGE_ENG_METRIC, $prefix->groupCode);
    }

    /**
     * Test constructor with Unicode symbol.
     */
    public function testConstructorWithUnicodeSymbol(): void
    {
        $prefix = new Prefix('micro', 'u', 'μ', 1e-6, PrefixRegistry::GROUP_SMALL_ENG_METRIC);

        $this->assertSame('u', $prefix->asciiSymbol);
        $this->assertSame('μ', $prefix->unicodeSymbol);
    }

    /**
     * Test constructor with two-letter ASCII symbol.
     */
    public function testConstructorWithTwoLetterAsciiSymbol(): void
    {
        $prefix = new Prefix('deca', 'da', null, 10.0, PrefixRegistry::GROUP_LARGE_NON_ENG_METRIC);

        $this->assertSame('da', $prefix->asciiSymbol);
    }

    /**
     * Test constructor with two-letter binary symbol.
     */
    public function testConstructorWithBinarySymbol(): void
    {
        $prefix = new Prefix('kibi', 'Ki', null, 1024.0, PrefixRegistry::GROUP_BINARY);

        $this->assertSame('Ki', $prefix->asciiSymbol);
        $this->assertSame(1024.0, $prefix->multiplier);
    }

    // endregion

    // region Constructor validation tests

    /**
     * Test constructor throws for empty ASCII symbol.
     */
    public function testConstructorThrowsForEmptyAsciiSymbol(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid ASCII symbol');

        new Prefix('test', '', null, 1000.0, PrefixRegistry::GROUP_LARGE_ENG_METRIC);
    }

    /**
     * Test constructor throws for ASCII symbol with numbers.
     */
    public function testConstructorThrowsForAsciiSymbolWithNumbers(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid ASCII symbol');

        new Prefix('test', 'k2', null, 1000.0, PrefixRegistry::GROUP_LARGE_ENG_METRIC);
    }

    /**
     * Test constructor throws for ASCII symbol too long.
     */
    public function testConstructorThrowsForAsciiSymbolTooLong(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid ASCII symbol');

        new Prefix('test', 'abc', null, 1000.0, PrefixRegistry::GROUP_LARGE_ENG_METRIC);
    }

    /**
     * Test constructor throws for ASCII symbol with special characters.
     */
    public function testConstructorThrowsForAsciiSymbolWithSpecialChars(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid ASCII symbol');

        new Prefix('test', 'k!', null, 1000.0, PrefixRegistry::GROUP_LARGE_ENG_METRIC);
    }

    /**
     * Test constructor throws for invalid Unicode symbol.
     */
    public function testConstructorThrowsForInvalidUnicodeSymbol(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid Unicode symbol');

        new Prefix('test', 'k', '123', 1000.0, PrefixRegistry::GROUP_LARGE_ENG_METRIC);
    }

    /**
     * Test constructor throws for Unicode symbol too long.
     */
    public function testConstructorThrowsForUnicodeSymbolTooLong(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid Unicode symbol');

        new Prefix('test', 'k', 'abc', 1000.0, PrefixRegistry::GROUP_LARGE_ENG_METRIC);
    }

    /**
     * Test constructor throws for zero multiplier.
     */
    public function testConstructorThrowsForZeroMultiplier(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Multiplier must be positive');

        new Prefix('test', 'k', null, 0.0, PrefixRegistry::GROUP_LARGE_ENG_METRIC);
    }

    /**
     * Test constructor throws for negative multiplier.
     */
    public function testConstructorThrowsForNegativeMultiplier(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Multiplier must be positive');

        new Prefix('test', 'k', null, -1000.0, PrefixRegistry::GROUP_LARGE_ENG_METRIC);
    }

    /**
     * Test constructor throws for multiplier of 1.
     */
    public function testConstructorThrowsForMultiplierOfOne(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Multiplier must not be equal to 1');

        new Prefix('test', 'k', null, 1.0, PrefixRegistry::GROUP_LARGE_ENG_METRIC);
    }

    /**
     * Test constructor throws for invalid group code.
     */
    public function testConstructorThrowsForInvalidGroupCode(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid group code');

        new Prefix('test', 'k', null, 1000.0, 0);
    }

    /**
     * Test constructor throws for combined group code.
     */
    public function testConstructorThrowsForCombinedGroupCode(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid group code');

        // Combined codes are not valid for individual prefixes
        new Prefix('test', 'k', null, 1000.0, PrefixRegistry::GROUP_METRIC);
    }

    // endregion

    // region equal() tests

    /**
     * Test equal() returns true for same ASCII symbol.
     */
    public function testEqualReturnsTrueForSameAsciiSymbol(): void
    {
        $prefix1 = new Prefix('kilo', 'k', null, 1000.0, PrefixRegistry::GROUP_LARGE_ENG_METRIC);
        $prefix2 = new Prefix('kilo', 'k', null, 1000.0, PrefixRegistry::GROUP_LARGE_ENG_METRIC);

        $this->assertTrue($prefix1->equal($prefix2));
    }

    /**
     * Test equal() returns false for different ASCII symbol.
     */
    public function testEqualReturnsFalseForDifferentAsciiSymbol(): void
    {
        $prefix1 = new Prefix('kilo', 'k', null, 1000.0, PrefixRegistry::GROUP_LARGE_ENG_METRIC);
        $prefix2 = new Prefix('mega', 'M', null, 1e6, PrefixRegistry::GROUP_LARGE_ENG_METRIC);

        $this->assertFalse($prefix1->equal($prefix2));
    }

    /**
     * Test equal() returns false for non-Prefix object.
     */
    public function testEqualReturnsFalseForNonPrefixObject(): void
    {
        $prefix = new Prefix('kilo', 'k', null, 1000.0, PrefixRegistry::GROUP_LARGE_ENG_METRIC);

        $this->assertFalse($prefix->equal('k'));
        $this->assertFalse($prefix->equal(1000));
        $this->assertFalse($prefix->equal(null));
    }

    // endregion

    // region isEngineering() tests

    /**
     * Test isEngineering() returns true for small engineering prefix.
     */
    public function testIsEngineeringReturnsTrueForSmallEngineering(): void
    {
        $prefix = new Prefix('milli', 'm', null, 0.001, PrefixRegistry::GROUP_SMALL_ENG_METRIC);

        $this->assertTrue($prefix->isEngineering());
    }

    /**
     * Test isEngineering() returns true for large engineering prefix.
     */
    public function testIsEngineeringReturnsTrueForLargeEngineering(): void
    {
        $prefix = new Prefix('kilo', 'k', null, 1000.0, PrefixRegistry::GROUP_LARGE_ENG_METRIC);

        $this->assertTrue($prefix->isEngineering());
    }

    /**
     * Test isEngineering() returns false for small non-engineering prefix.
     */
    public function testIsEngineeringReturnsFalseForSmallNonEngineering(): void
    {
        $prefix = new Prefix('centi', 'c', null, 0.01, PrefixRegistry::GROUP_SMALL_NON_ENG_METRIC);

        $this->assertFalse($prefix->isEngineering());
    }

    /**
     * Test isEngineering() returns false for large non-engineering prefix.
     */
    public function testIsEngineeringReturnsFalseForLargeNonEngineering(): void
    {
        $prefix = new Prefix('hecto', 'h', null, 100.0, PrefixRegistry::GROUP_LARGE_NON_ENG_METRIC);

        $this->assertFalse($prefix->isEngineering());
    }

    /**
     * Test isEngineering() returns false for binary prefix.
     */
    public function testIsEngineeringReturnsFalseForBinary(): void
    {
        $prefix = new Prefix('kibi', 'Ki', null, 1024.0, PrefixRegistry::GROUP_BINARY);

        $this->assertFalse($prefix->isEngineering());
    }

    // endregion

    // region format() tests

    /**
     * Test format() returns Unicode symbol by default.
     */
    public function testFormatReturnsUnicodeByDefault(): void
    {
        $prefix = new Prefix('micro', 'u', 'μ', 1e-6, PrefixRegistry::GROUP_SMALL_ENG_METRIC);

        $this->assertSame('μ', $prefix->format());
    }

    /**
     * Test format() returns ASCII symbol when requested.
     */
    public function testFormatReturnsAsciiWhenRequested(): void
    {
        $prefix = new Prefix('micro', 'u', 'μ', 1e-6, PrefixRegistry::GROUP_SMALL_ENG_METRIC);

        $this->assertSame('u', $prefix->format(true));
    }

    /**
     * Test format() returns same symbol when no Unicode symbol provided.
     */
    public function testFormatReturnsSameWhenNoUnicode(): void
    {
        $prefix = new Prefix('kilo', 'k', null, 1000.0, PrefixRegistry::GROUP_LARGE_ENG_METRIC);

        $this->assertSame('k', $prefix->format());
        $this->assertSame('k', $prefix->format(true));
    }

    // endregion

    // region __toString() tests

    /**
     * Test __toString() returns Unicode symbol.
     */
    public function testToStringReturnsUnicodeSymbol(): void
    {
        $prefix = new Prefix('micro', 'u', 'μ', 1e-6, PrefixRegistry::GROUP_SMALL_ENG_METRIC);

        $this->assertSame('μ', (string)$prefix);
    }

    /**
     * Test __toString() for prefix without Unicode.
     */
    public function testToStringForPrefixWithoutUnicode(): void
    {
        $prefix = new Prefix('kilo', 'k', null, 1000.0, PrefixRegistry::GROUP_LARGE_ENG_METRIC);

        $this->assertSame('k', (string)$prefix);
    }

    // endregion

    // region Integration tests with PrefixRegistry prefixes

    /**
     * Test equal() works with PrefixRegistry prefixes.
     */
    public function testEqualWithPrefixUtilityPrefixes(): void
    {
        $utilityKilo = PrefixRegistry::getBySymbol('k');
        $manualKilo = new Prefix('kilo', 'k', null, 1000.0, PrefixRegistry::GROUP_LARGE_ENG_METRIC);

        $this->assertNotNull($utilityKilo);
        $this->assertTrue($utilityKilo->equal($manualKilo));
        $this->assertTrue($manualKilo->equal($utilityKilo));
    }

    /**
     * Test isEngineering() matches PrefixRegistry prefixes.
     */
    public function testIsEngineeringMatchesPrefixUtilityPrefixes(): void
    {
        // Engineering prefixes
        $this->assertTrue(PrefixRegistry::getBySymbol('k')?->isEngineering());
        $this->assertTrue(PrefixRegistry::getBySymbol('M')?->isEngineering());
        $this->assertTrue(PrefixRegistry::getBySymbol('m')?->isEngineering());
        $this->assertTrue(PrefixRegistry::getBySymbol('n')?->isEngineering());

        // Non-engineering prefixes
        $this->assertFalse(PrefixRegistry::getBySymbol('c')?->isEngineering());
        $this->assertFalse(PrefixRegistry::getBySymbol('d')?->isEngineering());
        $this->assertFalse(PrefixRegistry::getBySymbol('da')?->isEngineering());
        $this->assertFalse(PrefixRegistry::getBySymbol('h')?->isEngineering());

        // Binary prefixes
        $this->assertFalse(PrefixRegistry::getBySymbol('Ki')?->isEngineering());
    }

    // endregion
}
