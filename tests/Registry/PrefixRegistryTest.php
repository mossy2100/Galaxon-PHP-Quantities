<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Registry;

use DomainException;
use Galaxon\Quantities\Internal\Prefix;
use Galaxon\Quantities\Registry\PrefixRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for PrefixRegistry class.
 */
#[CoversClass(PrefixRegistry::class)]
final class PrefixRegistryTest extends TestCase
{
    // region Constants tests

    /**
     * Test base prefix group constants have correct values.
     */
    public function testBaseGroupConstantValues(): void
    {
        $this->assertSame(1, PrefixRegistry::GROUP_SMALL_METRIC);
        $this->assertSame(2, PrefixRegistry::GROUP_MEDIUM_METRIC);
        $this->assertSame(4, PrefixRegistry::GROUP_LARGE_METRIC);
        $this->assertSame(8, PrefixRegistry::GROUP_BINARY);
    }

    /**
     * Test combined prefix group constants have correct values.
     */
    public function testCombinedGroupConstantValues(): void
    {
        // Metric = small metric + medium metric + large metric.
        $this->assertSame(
            PrefixRegistry::GROUP_SMALL_METRIC | PrefixRegistry::GROUP_MEDIUM_METRIC |
            PrefixRegistry::GROUP_LARGE_METRIC,
            PrefixRegistry::GROUP_METRIC
        );

        // Engineering = small metric + large metric.
        $this->assertSame(
            PrefixRegistry::GROUP_SMALL_METRIC | PrefixRegistry::GROUP_LARGE_METRIC,
            PrefixRegistry::GROUP_ENGINEERING
        );

        // Large = large engineering metric + binary.
        $this->assertSame(
            PrefixRegistry::GROUP_LARGE_METRIC | PrefixRegistry::GROUP_BINARY,
            PrefixRegistry::GROUP_LARGE
        );

        // All = metric + binary.
        $this->assertSame(PrefixRegistry::GROUP_METRIC | PrefixRegistry::GROUP_BINARY, PrefixRegistry::GROUP_ALL);
    }

    // endregion

    // region getPrefixes() tests

    /**
     * Test getPrefixes() with zero returns empty array.
     */
    public function testGetPrefixesWithZeroReturnsEmpty(): void
    {
        $result = PrefixRegistry::getPrefixes(0);

        $this->assertSame([], $result);
    }

    /**
     * Test getPrefixes() returns array of Prefix objects.
     */
    public function testGetPrefixesReturnsArrayOfPrefixObjects(): void
    {
        $result = PrefixRegistry::getPrefixes(PrefixRegistry::GROUP_METRIC);

        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertContainsOnlyInstancesOf(Prefix::class, $result);
    }

    /**
     * Test getPrefixes() with small metric group.
     */
    public function testGetPrefixesSmallMetric(): void
    {
        $result = PrefixRegistry::getPrefixes(PrefixRegistry::GROUP_SMALL_METRIC);

        $symbols = array_map(static fn (Prefix $p) => $p->asciiSymbol, $result);

        $this->assertContains('m', $symbols);  // milli
        $this->assertContains('u', $symbols);  // micro
        $this->assertContains('n', $symbols);  // nano
        $this->assertContains('p', $symbols);  // pico
        $this->assertContains('q', $symbols);  // quecto
        $this->assertNotContains('c', $symbols);  // centi (non-engineering)
        $this->assertNotContains('k', $symbols);  // kilo (large)
    }

    /**
     * Test getPrefixes() with medium metric group.
     */
    public function testGetPrefixesMediumMetric(): void
    {
        $result = PrefixRegistry::getPrefixes(PrefixRegistry::GROUP_MEDIUM_METRIC);

        $symbols = array_map(static fn (Prefix $p) => $p->asciiSymbol, $result);

        $this->assertContains('c', $symbols);  // centi
        $this->assertContains('h', $symbols);  // hecto
        $this->assertNotContains('m', $symbols);  // milli (engineering)
    }

    /**
     * Test getPrefixes() with large metric group.
     */
    public function testGetPrefixesLargeMetric(): void
    {
        $result = PrefixRegistry::getPrefixes(PrefixRegistry::GROUP_LARGE_METRIC);

        $symbols = array_map(static fn (Prefix $p) => $p->asciiSymbol, $result);

        $this->assertContains('k', $symbols);   // kilo
        $this->assertContains('M', $symbols);   // mega
        $this->assertContains('G', $symbols);   // giga
        $this->assertContains('T', $symbols);   // tera
        $this->assertContains('Q', $symbols);   // quetta
        $this->assertNotContains('h', $symbols);  // hecto (non-engineering)
        $this->assertNotContains('m', $symbols);  // milli (small)
    }

    /**
     * Test getPrefixes() with binary group.
     */
    public function testGetPrefixesBinary(): void
    {
        $result = PrefixRegistry::getPrefixes(PrefixRegistry::GROUP_BINARY);

        $symbols = array_map(static fn (Prefix $p) => $p->asciiSymbol, $result);

        $this->assertContains('Ki', $symbols);  // kibi
        $this->assertContains('Mi', $symbols);  // mebi
        $this->assertContains('Gi', $symbols);  // gibi
        $this->assertContains('Ti', $symbols);  // tebi
        $this->assertNotContains('k', $symbols);  // no metric
    }

    /**
     * Test getPrefixes() with metric group returns all metric prefixes.
     */
    public function testGetPrefixesMetric(): void
    {
        $result = PrefixRegistry::getPrefixes(PrefixRegistry::GROUP_METRIC);

        $symbols = array_map(static fn (Prefix $p) => $p->asciiSymbol, $result);

        // Small engineering.
        $this->assertContains('m', $symbols);
        $this->assertContains('u', $symbols);
        // Small non-engineering.
        $this->assertContains('c', $symbols);
        $this->assertContains('d', $symbols);
        // Large non-engineering.
        $this->assertContains('da', $symbols);
        $this->assertContains('h', $symbols);
        // Large engineering.
        $this->assertContains('k', $symbols);
        $this->assertContains('M', $symbols);
        // No binary.
        $this->assertNotContains('Ki', $symbols);
    }

    /**
     * Test getPrefixes() with all groups.
     */
    public function testGetPrefixesAll(): void
    {
        $result = PrefixRegistry::getPrefixes(PrefixRegistry::GROUP_ALL);

        $symbols = array_map(static fn (Prefix $p) => $p->asciiSymbol, $result);

        // Metric.
        $this->assertContains('m', $symbols);
        $this->assertContains('k', $symbols);
        $this->assertContains('c', $symbols);
        // Binary.
        $this->assertContains('Ki', $symbols);
    }

    /**
     * Test getPrefixes() with default parameter returns all.
     */
    public function testGetPrefixesDefaultReturnsAll(): void
    {
        $default = PrefixRegistry::getPrefixes();
        $all = PrefixRegistry::getPrefixes(PrefixRegistry::GROUP_ALL);

        $this->assertSame(count($all), count($default));
    }

    /**
     * Test getPrefixes() results are sorted by multiplier.
     */
    public function testGetPrefixesAreSortedByMultiplier(): void
    {
        $result = PrefixRegistry::getPrefixes(PrefixRegistry::GROUP_METRIC);

        $multipliers = array_map(static fn (Prefix $p) => $p->multiplier, $result);

        // Check ascending order.
        $sorted = $multipliers;
        sort($sorted);

        $this->assertSame($sorted, $multipliers);
    }

    // endregion

    // region getBySymbol() tests

    /**
     * Test getBySymbol() returns Prefix for valid ASCII symbol.
     */
    public function testGetBySymbolReturnsForValidAscii(): void
    {
        $prefix = PrefixRegistry::getBySymbol('k');

        $this->assertInstanceOf(Prefix::class, $prefix);
        $this->assertSame('kilo', $prefix->name);
        $this->assertSame('k', $prefix->asciiSymbol);
        $this->assertSame(1e3, $prefix->multiplier);
    }

    /**
     * Test getBySymbol() returns Prefix for valid Unicode symbol.
     */
    public function testGetBySymbolReturnsForValidUnicode(): void
    {
        $prefix = PrefixRegistry::getBySymbol('μ');

        $this->assertInstanceOf(Prefix::class, $prefix);
        $this->assertSame('micro', $prefix->name);
        $this->assertSame(1e-6, $prefix->multiplier);
    }

    /**
     * Test getBySymbol() returns Prefix for micro ASCII alias.
     */
    public function testGetBySymbolReturnsForMicroAsciiAlias(): void
    {
        $prefix = PrefixRegistry::getBySymbol('u');

        $this->assertInstanceOf(Prefix::class, $prefix);
        $this->assertSame('micro', $prefix->name);
    }

    /**
     * Test getBySymbol() returns Prefix for binary prefix.
     */
    public function testGetBySymbolReturnsForBinaryPrefix(): void
    {
        $prefix = PrefixRegistry::getBySymbol('Ki');

        $this->assertInstanceOf(Prefix::class, $prefix);
        $this->assertSame('kibi', $prefix->name);
        $this->assertSame((float)(2 ** 10), $prefix->multiplier);
    }

    /**
     * Test getBySymbol() returns null for invalid symbol.
     */
    public function testGetBySymbolReturnsNullForInvalid(): void
    {
        $this->assertNull(PrefixRegistry::getBySymbol('X'));
        $this->assertNull(PrefixRegistry::getBySymbol('invalid'));
        $this->assertNull(PrefixRegistry::getBySymbol(''));
    }

    /**
     * Test getBySymbol() is case-sensitive.
     */
    public function testGetBySymbolIsCaseSensitive(): void
    {
        // 'M' is mega (1e6).
        $mega = PrefixRegistry::getBySymbol('M');
        $this->assertInstanceOf(Prefix::class, $mega);
        $this->assertSame('mega', $mega->name);
        $this->assertSame(1e6, $mega->multiplier);

        // 'm' is milli (1e-3).
        $milli = PrefixRegistry::getBySymbol('m');
        $this->assertInstanceOf(Prefix::class, $milli);
        $this->assertSame('milli', $milli->name);
        $this->assertSame(1e-3, $milli->multiplier);

        // 'K' is not a valid prefix (kilo is lowercase 'k').
        $this->assertNull(PrefixRegistry::getBySymbol('K'));
    }

    // endregion

    // region invert() tests

    /**
     * Test invert() converts kilo to milli.
     */
    public function testInvertKiloToMilli(): void
    {
        $kilo = PrefixRegistry::getBySymbol('k');
        $result = PrefixRegistry::invert($kilo);

        $this->assertInstanceOf(Prefix::class, $result);
        $this->assertSame('milli', $result->name);
        $this->assertSame('m', $result->asciiSymbol);
    }

    /**
     * Test invert() converts milli to kilo.
     */
    public function testInvertMilliToKilo(): void
    {
        $milli = PrefixRegistry::getBySymbol('m');
        $result = PrefixRegistry::invert($milli);

        $this->assertInstanceOf(Prefix::class, $result);
        $this->assertSame('kilo', $result->name);
    }

    /**
     * Test invert() converts mega to micro.
     */
    public function testInvertMegaToMicro(): void
    {
        $mega = PrefixRegistry::getBySymbol('M');
        $result = PrefixRegistry::invert($mega);

        $this->assertInstanceOf(Prefix::class, $result);
        $this->assertSame('micro', $result->name);
    }

    /**
     * Test invert() converts micro to mega.
     */
    public function testInvertMicroToMega(): void
    {
        $micro = PrefixRegistry::getBySymbol('μ');
        $result = PrefixRegistry::invert($micro);

        $this->assertInstanceOf(Prefix::class, $result);
        $this->assertSame('mega', $result->name);
    }

    /**
     * Test invert() converts giga to nano.
     */
    public function testInvertGigaToNano(): void
    {
        $giga = PrefixRegistry::getBySymbol('G');
        $result = PrefixRegistry::invert($giga);

        $this->assertInstanceOf(Prefix::class, $result);
        $this->assertSame('nano', $result->name);
    }

    /**
     * Test invert() converts tera to pico.
     */
    public function testInvertTeraToPico(): void
    {
        $tera = PrefixRegistry::getBySymbol('T');
        $result = PrefixRegistry::invert($tera);

        $this->assertInstanceOf(Prefix::class, $result);
        $this->assertSame('pico', $result->name);
    }

    /**
     * Test invert() converts hecto to centi.
     */
    public function testInvertHectoToCenti(): void
    {
        $hecto = PrefixRegistry::getBySymbol('h');
        $result = PrefixRegistry::invert($hecto);

        $this->assertInstanceOf(Prefix::class, $result);
        $this->assertSame('centi', $result->name);
    }

    /**
     * Test invert() converts deca to deci.
     */
    public function testInvertDecaToDeci(): void
    {
        $deca = PrefixRegistry::getBySymbol('da');
        $result = PrefixRegistry::invert($deca);

        $this->assertInstanceOf(Prefix::class, $result);
        $this->assertSame('deci', $result->name);
    }

    /**
     * Test invert() converts quetta to quecto.
     */
    public function testInvertQuettaToQuecto(): void
    {
        $quetta = PrefixRegistry::getBySymbol('Q');
        $result = PrefixRegistry::invert($quetta);

        $this->assertInstanceOf(Prefix::class, $result);
        $this->assertSame('quecto', $result->name);
    }

    /**
     * Test invert() throws for binary prefixes (no inverse exists).
     */
    public function testInvertThrowsForBinaryPrefixes(): void
    {
        $kibi = PrefixRegistry::getBySymbol('Ki');

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Inverse of prefix 'Ki' not found");

        PrefixRegistry::invert($kibi);
    }

    /**
     * Test invert() returns null for null input.
     */
    public function testInvertReturnsNullForNullInput(): void
    {
        $this->assertNull(PrefixRegistry::invert(null));
    }

    /**
     * Test invert() round-trip returns equivalent prefix.
     */
    public function testInvertRoundTrip(): void
    {
        $prefixes = ['m', 'n', 'p', 'k', 'M', 'G'];

        foreach ($prefixes as $symbol) {
            $original = PrefixRegistry::getBySymbol($symbol);
            $inverted = PrefixRegistry::invert($original);
            $roundTrip = PrefixRegistry::invert($inverted);

            $this->assertInstanceOf(Prefix::class, $original);
            $this->assertInstanceOf(Prefix::class, $roundTrip);
            $this->assertSame($original->name, $roundTrip->name);
            $this->assertSame($original->multiplier, $roundTrip->multiplier);
        }
    }

    /**
     * Test invert() multipliers are reciprocals.
     */
    public function testInvertMultipliersAreReciprocals(): void
    {
        $prefixes = ['k', 'm', 'M', 'G', 'n', 'T', 'p'];

        foreach ($prefixes as $symbol) {
            $prefix = PrefixRegistry::getBySymbol($symbol);
            $inverse = PrefixRegistry::invert($prefix);

            // multiplier × inverseMultiplier should equal 1.
            $this->assertInstanceOf(Prefix::class, $prefix);
            $this->assertInstanceOf(Prefix::class, $inverse);
            $this->assertEqualsWithDelta(1.0, $prefix->multiplier * $inverse->multiplier, 1e-20);
        }
    }

    // endregion

    // region isValidGroupCode() tests

    /**
     * Test isValidGroupCode() returns true for base group codes.
     */
    public function testIsValidGroupCodeReturnsTrueForBaseGroups(): void
    {
        $this->assertTrue(PrefixRegistry::isValidGroupCode(PrefixRegistry::GROUP_SMALL_METRIC));
        $this->assertTrue(PrefixRegistry::isValidGroupCode(PrefixRegistry::GROUP_MEDIUM_METRIC));
        $this->assertTrue(PrefixRegistry::isValidGroupCode(PrefixRegistry::GROUP_LARGE_METRIC));
        $this->assertTrue(PrefixRegistry::isValidGroupCode(PrefixRegistry::GROUP_BINARY));
    }

    /**
     * Test isValidGroupCode() returns false for combined group codes.
     */
    public function testIsValidGroupCodeReturnsFalseForCombinedGroups(): void
    {
        // Combined codes are not "valid" base codes.
        $this->assertFalse(PrefixRegistry::isValidGroupCode(PrefixRegistry::GROUP_METRIC));
        $this->assertFalse(PrefixRegistry::isValidGroupCode(PrefixRegistry::GROUP_LARGE));
        $this->assertFalse(PrefixRegistry::isValidGroupCode(PrefixRegistry::GROUP_ALL));
    }

    /**
     * Test isValidGroupCode() returns false for invalid values.
     */
    public function testIsValidGroupCodeReturnsFalseForInvalidValues(): void
    {
        $this->assertFalse(PrefixRegistry::isValidGroupCode(0));
        $this->assertFalse(PrefixRegistry::isValidGroupCode(-1));
        $this->assertFalse(PrefixRegistry::isValidGroupCode(32));
        $this->assertFalse(PrefixRegistry::isValidGroupCode(100));
    }

    // endregion

    // region Prefix object property tests

    /**
     * Test that prefixes have correct groupCode property.
     */
    public function testPrefixGroupCodeProperty(): void
    {
        $kilo = PrefixRegistry::getBySymbol('k');
        $this->assertInstanceOf(Prefix::class, $kilo);
        $this->assertSame(PrefixRegistry::GROUP_LARGE_METRIC, $kilo->groupCode);

        $milli = PrefixRegistry::getBySymbol('m');
        $this->assertInstanceOf(Prefix::class, $milli);
        $this->assertSame(PrefixRegistry::GROUP_SMALL_METRIC, $milli->groupCode);

        $centi = PrefixRegistry::getBySymbol('c');
        $this->assertInstanceOf(Prefix::class, $centi);
        $this->assertSame(PrefixRegistry::GROUP_MEDIUM_METRIC, $centi->groupCode);

        $kibi = PrefixRegistry::getBySymbol('Ki');
        $this->assertInstanceOf(Prefix::class, $kibi);
        $this->assertSame(PrefixRegistry::GROUP_BINARY, $kibi->groupCode);
    }

    /**
     * Test that micro prefix has correct Unicode symbol.
     */
    public function testMicroPrefixUnicodeSymbol(): void
    {
        $micro = PrefixRegistry::getBySymbol('u');

        $this->assertInstanceOf(Prefix::class, $micro);
        $this->assertSame('u', $micro->asciiSymbol);
        $this->assertSame('μ', $micro->unicodeSymbol);
    }

    /**
     * Test that prefixes without special Unicode have matching symbols.
     */
    public function testPrefixesWithoutUnicodeHaveMatchingSymbols(): void
    {
        $kilo = PrefixRegistry::getBySymbol('k');
        $this->assertInstanceOf(Prefix::class, $kilo);
        $this->assertSame($kilo->asciiSymbol, $kilo->unicodeSymbol);

        $mega = PrefixRegistry::getBySymbol('M');
        $this->assertInstanceOf(Prefix::class, $mega);
        $this->assertSame($mega->asciiSymbol, $mega->unicodeSymbol);
    }

    // endregion

    // region Prefix definitions tests

    /**
     * Test all small engineering metric prefixes are defined.
     */
    public function testSmallEngineeringMetricPrefixesDefined(): void
    {
        $expected = ['q', 'r', 'y', 'z', 'a', 'f', 'p', 'n', 'u', 'm'];
        $result = PrefixRegistry::getPrefixes(PrefixRegistry::GROUP_SMALL_METRIC);
        $symbols = array_map(static fn (Prefix $p) => $p->asciiSymbol, $result);

        foreach ($expected as $symbol) {
            $this->assertContains($symbol, $symbols, "Expected prefix '$symbol' not found");
        }
    }

    /**
     * Test all large engineering metric prefixes are defined.
     */
    public function testLargeEngineeringMetricPrefixesDefined(): void
    {
        $expected = ['k', 'M', 'G', 'T', 'P', 'E', 'Z', 'Y', 'R', 'Q'];
        $result = PrefixRegistry::getPrefixes(PrefixRegistry::GROUP_LARGE_METRIC);
        $symbols = array_map(static fn (Prefix $p) => $p->asciiSymbol, $result);

        foreach ($expected as $symbol) {
            $this->assertContains($symbol, $symbols, "Expected prefix '$symbol' not found");
        }
    }

    /**
     * Test all binary prefixes are defined.
     */
    public function testBinaryPrefixesDefined(): void
    {
        $expected = ['Ki', 'Mi', 'Gi', 'Ti', 'Pi', 'Ei', 'Zi', 'Yi', 'Ri', 'Qi'];
        $result = PrefixRegistry::getPrefixes(PrefixRegistry::GROUP_BINARY);
        $symbols = array_map(static fn (Prefix $p) => $p->asciiSymbol, $result);

        foreach ($expected as $symbol) {
            $this->assertContains($symbol, $symbols, "Expected prefix '$symbol' not found");
        }
    }

    /**
     * Test binary prefix multipliers are powers of 2.
     */
    public function testBinaryPrefixMultipliers(): void
    {
        $this->assertSame((float)(2 ** 10), PrefixRegistry::getBySymbol('Ki')?->multiplier);
        $this->assertSame((float)(2 ** 20), PrefixRegistry::getBySymbol('Mi')?->multiplier);
        $this->assertSame((float)(2 ** 30), PrefixRegistry::getBySymbol('Gi')?->multiplier);
        $this->assertSame((float)(2 ** 40), PrefixRegistry::getBySymbol('Ti')?->multiplier);
        $this->assertSame((float)(2 ** 50), PrefixRegistry::getBySymbol('Pi')?->multiplier);
        $this->assertSame((float)(2 ** 60), PrefixRegistry::getBySymbol('Ei')?->multiplier);
    }

    // endregion

    // region reset() tests

    /**
     * Test reset() clears the prefix cache.
     */
    public function testResetClearsPrefixCache(): void
    {
        // First ensure prefixes are loaded.
        $before = PrefixRegistry::getPrefixes();
        $this->assertNotEmpty($before);

        // Reset the cache.
        PrefixRegistry::reset();

        // Access prefixes again - should reinitialize.
        $after = PrefixRegistry::getPrefixes();
        $this->assertNotEmpty($after);

        // Should have the same prefixes.
        $this->assertCount(count($before), $after);
    }

    // endregion
}
