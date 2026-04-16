<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\NonCurrencies\Internal;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Quantities\Internal\Prefix;
use Galaxon\Quantities\Services\PrefixService;
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
        $prefix = new Prefix('kilo', 1000.0, PrefixService::GROUP_LARGE_METRIC, 'k');

        $this->assertSame('kilo', $prefix->name);
        $this->assertSame('k', $prefix->asciiSymbol);
        $this->assertSame('k', $prefix->unicodeSymbol);  // Falls back to ASCII
        $this->assertNull($prefix->alternateSymbol);
        $this->assertSame(1000.0, $prefix->multiplier);
        $this->assertSame(PrefixService::GROUP_LARGE_METRIC, $prefix->groupCode);
    }

    /**
     * Test constructor with Unicode symbol.
     */
    public function testConstructorWithUnicodeSymbol(): void
    {
        $prefix = new Prefix('micro', 1e-6, PrefixService::GROUP_SMALL_METRIC, 'u', 'μ');

        $this->assertSame('u', $prefix->asciiSymbol);
        $this->assertSame('μ', $prefix->unicodeSymbol);
        $this->assertNull($prefix->alternateSymbol);
    }

    /**
     * Test constructor with alternate symbol.
     *
     * Uses Unicode escapes so the two visually identical but distinct micro characters are unambiguous:
     * - U+00B5 MICRO SIGN (unicode symbol)
     * - U+03BC GREEK SMALL LETTER MU (alternate symbol)
     */
    public function testConstructorWithAlternateSymbol(): void
    {
        $prefix = new Prefix('micro', 1e-6, PrefixService::GROUP_SMALL_METRIC, 'u', "\u{00B5}", "\u{03BC}");

        $this->assertSame('u', $prefix->asciiSymbol);
        $this->assertSame("\u{00B5}", $prefix->unicodeSymbol);
        $this->assertSame("\u{03BC}", $prefix->alternateSymbol);
    }

    /**
     * Test constructor with two-letter ASCII symbol.
     */
    public function testConstructorWithTwoLetterAsciiSymbol(): void
    {
        $prefix = new Prefix('deca', 10.0, PrefixService::GROUP_MEDIUM_METRIC, 'da');

        $this->assertSame('da', $prefix->asciiSymbol);
    }

    /**
     * Test constructor with two-letter binary symbol.
     */
    public function testConstructorWithBinarySymbol(): void
    {
        $prefix = new Prefix('kibi', 1024.0, PrefixService::GROUP_BINARY, 'Ki');

        $this->assertSame('Ki', $prefix->asciiSymbol);
        $this->assertSame(1024.0, $prefix->multiplier);
    }

    // endregion

    // region Name validation tests

    /**
     * Test constructor throws for empty name.
     */
    public function testConstructorThrowsForEmptyName(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid prefix name');

        new Prefix('', 1000.0, PrefixService::GROUP_LARGE_METRIC, 'k');
    }

    /**
     * Test constructor throws for name shorter than 3 characters.
     */
    public function testConstructorThrowsForNameTooShort(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid prefix name');

        new Prefix('ab', 1000.0, PrefixService::GROUP_LARGE_METRIC, 'k');
    }

    /**
     * Test constructor throws for name longer than 6 characters.
     */
    public function testConstructorThrowsForNameTooLong(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid prefix name');

        new Prefix('abcdefg', 1000.0, PrefixService::GROUP_LARGE_METRIC, 'k');
    }

    /**
     * Test constructor throws for name with uppercase letters.
     */
    public function testConstructorThrowsForNameWithUppercase(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid prefix name');

        new Prefix('Kilo', 1000.0, PrefixService::GROUP_LARGE_METRIC, 'k');
    }

    /**
     * Test constructor throws for name with digits.
     */
    public function testConstructorThrowsForNameWithDigits(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid prefix name');

        new Prefix('kilo1', 1000.0, PrefixService::GROUP_LARGE_METRIC, 'k');
    }

    /**
     * Test constructor throws for name with non-ASCII letters.
     */
    public function testConstructorThrowsForNameWithNonAsciiLetters(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid prefix name');

        new Prefix('μicro', 1e-6, PrefixService::GROUP_SMALL_METRIC, 'u');
    }

    // endregion

    // region ASCII symbol validation tests

    /**
     * Test constructor throws for empty ASCII symbol.
     */
    public function testConstructorThrowsForEmptyAsciiSymbol(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid ASCII prefix symbol');

        new Prefix('test', 1000.0, PrefixService::GROUP_LARGE_METRIC, '');
    }

    /**
     * Test constructor throws for ASCII symbol with numbers.
     */
    public function testConstructorThrowsForAsciiSymbolWithNumbers(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid ASCII prefix symbol');

        new Prefix('test', 1000.0, PrefixService::GROUP_LARGE_METRIC, 'k2');
    }

    /**
     * Test constructor throws for ASCII symbol too long.
     */
    public function testConstructorThrowsForAsciiSymbolTooLong(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid ASCII prefix symbol');

        new Prefix('test', 1000.0, PrefixService::GROUP_LARGE_METRIC, 'abc');
    }

    /**
     * Test constructor throws for ASCII symbol with non-letters.
     */
    public function testConstructorThrowsForAsciiSymbolWithNonLetters(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid ASCII prefix symbol');

        new Prefix('test', 1000.0, PrefixService::GROUP_LARGE_METRIC, 'k!');
    }

    // endregion

    // region Unicode symbol validation tests

    /**
     * Test constructor throws for invalid Unicode symbol.
     */
    public function testConstructorThrowsForInvalidUnicodeSymbol(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid Unicode prefix symbol');

        new Prefix('test', 1000.0, PrefixService::GROUP_LARGE_METRIC, 'k', '1');
    }

    /**
     * Test constructor throws for multi-character Unicode symbol.
     */
    public function testConstructorThrowsForUnicodeSymbolTooLong(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid Unicode prefix symbol');

        new Prefix('test', 1000.0, PrefixService::GROUP_LARGE_METRIC, 'k', 'abc');
    }

    // endregion

    // region Alternate symbol validation tests

    /**
     * Test constructor throws for invalid alternate symbol.
     */
    public function testConstructorThrowsForInvalidAlternateSymbol(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid alternate prefix symbol');

        new Prefix('test', 1000.0, PrefixService::GROUP_LARGE_METRIC, 'k', null, '1');
    }

    /**
     * Test constructor throws for multi-character alternate symbol.
     */
    public function testConstructorThrowsForAlternateSymbolTooLong(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid alternate prefix symbol');

        new Prefix('test', 1000.0, PrefixService::GROUP_LARGE_METRIC, 'k', null, 'abc');
    }

    // endregion

    // region Multiplier validation tests

    /**
     * Test constructor throws for zero multiplier.
     */
    public function testConstructorThrowsForZeroMultiplier(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot create prefix with non-positive multiplier');

        new Prefix('test', 0.0, PrefixService::GROUP_LARGE_METRIC, 'k');
    }

    /**
     * Test constructor throws for negative multiplier.
     */
    public function testConstructorThrowsForNegativeMultiplier(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot create prefix with non-positive multiplier');

        new Prefix('test', -1000.0, PrefixService::GROUP_LARGE_METRIC, 'k');
    }

    /**
     * Test constructor throws for multiplier of 1.
     */
    public function testConstructorThrowsForMultiplierOfOne(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot create prefix with multiplier equal to one');

        new Prefix('test', 1.0, PrefixService::GROUP_LARGE_METRIC, 'k');
    }

    // endregion

    // region Group code validation tests

    /**
     * Test constructor throws for invalid group code.
     */
    public function testConstructorThrowsForInvalidGroupCode(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid prefix group code');

        new Prefix('test', 1000.0, 0, 'k');
    }

    /**
     * Test constructor throws for combined group code.
     */
    public function testConstructorThrowsForCombinedGroupCode(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid prefix group code');

        // Combined codes are not valid for individual prefixes.
        new Prefix('test', 1000.0, PrefixService::GROUP_METRIC, 'k');
    }

    // endregion

    // region equal() tests

    /**
     * Test equal() returns true for same name.
     */
    public function testEqualReturnsTrueForSameName(): void
    {
        $prefix1 = new Prefix('kilo', 1000.0, PrefixService::GROUP_LARGE_METRIC, 'k');
        $prefix2 = new Prefix('kilo', 1000.0, PrefixService::GROUP_LARGE_METRIC, 'k');

        $this->assertTrue($prefix1->equal($prefix2));
    }

    /**
     * Test equal() returns false for different name.
     */
    public function testEqualReturnsFalseForDifferentName(): void
    {
        $prefix1 = new Prefix('kilo', 1000.0, PrefixService::GROUP_LARGE_METRIC, 'k');
        $prefix2 = new Prefix('mega', 1e6, PrefixService::GROUP_LARGE_METRIC, 'M');

        $this->assertFalse($prefix1->equal($prefix2));
    }

    /**
     * Test equal() compares by name, not ASCII symbol.
     *
     * Two prefixes constructed with the same ASCII symbol but different names are NOT equal.
     */
    public function testEqualComparesByNameNotAsciiSymbol(): void
    {
        $prefix1 = new Prefix('kilo', 1000.0, PrefixService::GROUP_LARGE_METRIC, 'k');
        $prefix2 = new Prefix('test', 1000.0, PrefixService::GROUP_LARGE_METRIC, 'k');

        $this->assertFalse($prefix1->equal($prefix2));
    }

    /**
     * Test equal() returns false for non-Prefix object.
     */
    public function testEqualReturnsFalseForNonPrefixObject(): void
    {
        $prefix = new Prefix('kilo', 1000.0, PrefixService::GROUP_LARGE_METRIC, 'k');

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
        $prefix = new Prefix('milli', 0.001, PrefixService::GROUP_SMALL_METRIC, 'm');

        $this->assertTrue($prefix->isEngineering());
    }

    /**
     * Test isEngineering() returns true for large engineering prefix.
     */
    public function testIsEngineeringReturnsTrueForLargeEngineering(): void
    {
        $prefix = new Prefix('kilo', 1000.0, PrefixService::GROUP_LARGE_METRIC, 'k');

        $this->assertTrue($prefix->isEngineering());
    }

    /**
     * Test isEngineering() returns false for medium metric prefix.
     */
    public function testIsEngineeringReturnsFalseForMediumMetric(): void
    {
        $prefix = new Prefix('centi', 0.01, PrefixService::GROUP_MEDIUM_METRIC, 'c');

        $this->assertFalse($prefix->isEngineering());
    }

    /**
     * Test isEngineering() returns false for binary prefix.
     */
    public function testIsEngineeringReturnsFalseForBinary(): void
    {
        $prefix = new Prefix('kibi', 1024.0, PrefixService::GROUP_BINARY, 'Ki');

        $this->assertFalse($prefix->isEngineering());
    }

    // endregion

    // region format() tests

    /**
     * Test format() returns Unicode symbol by default.
     */
    public function testFormatReturnsUnicodeByDefault(): void
    {
        $prefix = new Prefix('micro', 1e-6, PrefixService::GROUP_SMALL_METRIC, 'u', 'μ');

        $this->assertSame('μ', $prefix->format());
    }

    /**
     * Test format() returns ASCII symbol when requested.
     */
    public function testFormatReturnsAsciiWhenRequested(): void
    {
        $prefix = new Prefix('micro', 1e-6, PrefixService::GROUP_SMALL_METRIC, 'u', 'μ');

        $this->assertSame('u', $prefix->format(true));
    }

    /**
     * Test format() returns same symbol when no Unicode symbol provided.
     */
    public function testFormatReturnsSameWhenNoUnicode(): void
    {
        $prefix = new Prefix('kilo', 1000.0, PrefixService::GROUP_LARGE_METRIC, 'k');

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
        $prefix = new Prefix('micro', 1e-6, PrefixService::GROUP_SMALL_METRIC, 'u', 'μ');

        $this->assertSame('μ', (string)$prefix);
    }

    /**
     * Test __toString() for prefix without Unicode.
     */
    public function testToStringForPrefixWithoutUnicode(): void
    {
        $prefix = new Prefix('kilo', 1000.0, PrefixService::GROUP_LARGE_METRIC, 'k');

        $this->assertSame('k', (string)$prefix);
    }

    // endregion

    // region Integration tests with PrefixService prefixes

    /**
     * Test equal() works with PrefixService prefixes.
     */
    public function testEqualWithPrefixUtilityPrefixes(): void
    {
        $utilityKilo = PrefixService::getBySymbol('k');
        $manualKilo = new Prefix('kilo', 1000.0, PrefixService::GROUP_LARGE_METRIC, 'k');

        $this->assertNotNull($utilityKilo);
        $this->assertTrue($utilityKilo->equal($manualKilo));
        $this->assertTrue($manualKilo->equal($utilityKilo));
    }

    /**
     * Test isEngineering() matches PrefixService prefixes.
     */
    public function testIsEngineeringMatchesPrefixUtilityPrefixes(): void
    {
        // Engineering prefixes.
        $this->assertTrue(PrefixService::getBySymbol('k')?->isEngineering());
        $this->assertTrue(PrefixService::getBySymbol('M')?->isEngineering());
        $this->assertTrue(PrefixService::getBySymbol('m')?->isEngineering());
        $this->assertTrue(PrefixService::getBySymbol('n')?->isEngineering());

        // Non-engineering prefixes.
        $this->assertFalse(PrefixService::getBySymbol('c')?->isEngineering());
        $this->assertFalse(PrefixService::getBySymbol('d')?->isEngineering());
        $this->assertFalse(PrefixService::getBySymbol('da')?->isEngineering());
        $this->assertFalse(PrefixService::getBySymbol('h')?->isEngineering());

        // Binary prefixes.
        $this->assertFalse(PrefixService::getBySymbol('Ki')?->isEngineering());
    }

    // endregion
}
