<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Utility;

use DomainException;
use Galaxon\Quantities\Prefix;
use Galaxon\Quantities\Utility\PrefixUtility;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for PrefixUtility class.
 */
#[CoversClass(PrefixUtility::class)]
final class PrefixUtilityTest extends TestCase
{
    // region Constants tests

    /**
     * Test base prefix group constants have correct values.
     */
    public function testBaseGroupConstantValues(): void
    {
        $this->assertSame(1, PrefixUtility::GROUP_CODE_SMALL_ENGINEERING_METRIC);
        $this->assertSame(2, PrefixUtility::GROUP_CODE_SMALL_NON_ENGINEERING_METRIC);
        $this->assertSame(4, PrefixUtility::GROUP_CODE_LARGE_NON_ENGINEERING_METRIC);
        $this->assertSame(8, PrefixUtility::GROUP_CODE_LARGE_ENGINEERING_METRIC);
        $this->assertSame(16, PrefixUtility::GROUP_CODE_BINARY);
    }

    /**
     * Test combined prefix group constants have correct values.
     */
    public function testCombinedGroupConstantValues(): void
    {
        // Small metric = small engineering + small non-engineering.
        $this->assertSame(
            PrefixUtility::GROUP_CODE_SMALL_ENGINEERING_METRIC | PrefixUtility::GROUP_CODE_SMALL_NON_ENGINEERING_METRIC,
            PrefixUtility::GROUP_CODE_SMALL_METRIC
        );

        // Large metric = large non-engineering + large engineering.
        $this->assertSame(
            PrefixUtility::GROUP_CODE_LARGE_NON_ENGINEERING_METRIC | PrefixUtility::GROUP_CODE_LARGE_ENGINEERING_METRIC,
            PrefixUtility::GROUP_CODE_LARGE_METRIC
        );

        // Engineering metric = small engineering + large engineering.
        $this->assertSame(
            PrefixUtility::GROUP_CODE_SMALL_ENGINEERING_METRIC | PrefixUtility::GROUP_CODE_LARGE_ENGINEERING_METRIC,
            PrefixUtility::GROUP_CODE_ENGINEERING_METRIC
        );

        // Metric = small metric + large metric.
        $this->assertSame(
            PrefixUtility::GROUP_CODE_SMALL_METRIC | PrefixUtility::GROUP_CODE_LARGE_METRIC,
            PrefixUtility::GROUP_CODE_METRIC
        );

        // Large = large engineering metric + binary.
        $this->assertSame(
            PrefixUtility::GROUP_CODE_LARGE_ENGINEERING_METRIC | PrefixUtility::GROUP_CODE_BINARY,
            PrefixUtility::GROUP_CODE_LARGE
        );

        // All = metric + binary.
        $this->assertSame(
            PrefixUtility::GROUP_CODE_METRIC | PrefixUtility::GROUP_CODE_BINARY,
            PrefixUtility::GROUP_CODE_ALL
        );
    }

    // endregion

    // region getPrefixes() tests

    /**
     * Test getPrefixes() with zero returns empty array.
     */
    public function testGetPrefixesWithZeroReturnsEmpty(): void
    {
        $result = PrefixUtility::getPrefixes(0);

        $this->assertSame([], $result);
    }

    /**
     * Test getPrefixes() returns array of Prefix objects.
     */
    public function testGetPrefixesReturnsArrayOfPrefixObjects(): void
    {
        $result = PrefixUtility::getPrefixes(PrefixUtility::GROUP_CODE_METRIC);

        $this->assertIsArray($result); // @phpstan-ignore method.alreadyNarrowedType
        $this->assertNotEmpty($result);
        $this->assertContainsOnlyInstancesOf(Prefix::class, $result);
    }

    /**
     * Test getPrefixes() with small engineering metric group.
     */
    public function testGetPrefixesSmallEngineeringMetric(): void
    {
        $result = PrefixUtility::getPrefixes(PrefixUtility::GROUP_CODE_SMALL_ENGINEERING_METRIC);

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
     * Test getPrefixes() with small non-engineering metric group.
     */
    public function testGetPrefixesSmallNonEngineeringMetric(): void
    {
        $result = PrefixUtility::getPrefixes(PrefixUtility::GROUP_CODE_SMALL_NON_ENGINEERING_METRIC);

        $symbols = array_map(static fn (Prefix $p) => $p->asciiSymbol, $result);

        $this->assertContains('c', $symbols);  // centi
        $this->assertContains('d', $symbols);  // deci
        $this->assertNotContains('m', $symbols);  // milli (engineering)
    }

    /**
     * Test getPrefixes() with large engineering metric group.
     */
    public function testGetPrefixesLargeEngineeringMetric(): void
    {
        $result = PrefixUtility::getPrefixes(PrefixUtility::GROUP_CODE_LARGE_ENGINEERING_METRIC);

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
     * Test getPrefixes() with large non-engineering metric group.
     */
    public function testGetPrefixesLargeNonEngineeringMetric(): void
    {
        $result = PrefixUtility::getPrefixes(PrefixUtility::GROUP_CODE_LARGE_NON_ENGINEERING_METRIC);

        $symbols = array_map(static fn (Prefix $p) => $p->asciiSymbol, $result);

        $this->assertContains('da', $symbols);  // deca
        $this->assertContains('h', $symbols);   // hecto
        $this->assertNotContains('k', $symbols);  // kilo (engineering)
    }

    /**
     * Test getPrefixes() with binary group.
     */
    public function testGetPrefixesBinary(): void
    {
        $result = PrefixUtility::getPrefixes(PrefixUtility::GROUP_CODE_BINARY);

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
        $result = PrefixUtility::getPrefixes(PrefixUtility::GROUP_CODE_METRIC);

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
        $result = PrefixUtility::getPrefixes(PrefixUtility::GROUP_CODE_ALL);

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
        $default = PrefixUtility::getPrefixes();
        $all = PrefixUtility::getPrefixes(PrefixUtility::GROUP_CODE_ALL);

        $this->assertSame(count($all), count($default));
    }

    /**
     * Test getPrefixes() results are sorted by multiplier.
     */
    public function testGetPrefixesAreSortedByMultiplier(): void
    {
        $result = PrefixUtility::getPrefixes(PrefixUtility::GROUP_CODE_METRIC);

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
        $prefix = PrefixUtility::getBySymbol('k');

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
        $prefix = PrefixUtility::getBySymbol('μ');

        $this->assertInstanceOf(Prefix::class, $prefix);
        $this->assertSame('micro', $prefix->name);
        $this->assertSame(1e-6, $prefix->multiplier);
    }

    /**
     * Test getBySymbol() returns Prefix for micro ASCII alias.
     */
    public function testGetBySymbolReturnsForMicroAsciiAlias(): void
    {
        $prefix = PrefixUtility::getBySymbol('u');

        $this->assertInstanceOf(Prefix::class, $prefix);
        $this->assertSame('micro', $prefix->name);
    }

    /**
     * Test getBySymbol() returns Prefix for binary prefix.
     */
    public function testGetBySymbolReturnsForBinaryPrefix(): void
    {
        $prefix = PrefixUtility::getBySymbol('Ki');

        $this->assertInstanceOf(Prefix::class, $prefix);
        $this->assertSame('kibi', $prefix->name);
        $this->assertSame((float)(2 ** 10), $prefix->multiplier);
    }

    /**
     * Test getBySymbol() returns null for invalid symbol.
     */
    public function testGetBySymbolReturnsNullForInvalid(): void
    {
        $this->assertNull(PrefixUtility::getBySymbol('X'));
        $this->assertNull(PrefixUtility::getBySymbol('invalid'));
        $this->assertNull(PrefixUtility::getBySymbol(''));
    }

    /**
     * Test getBySymbol() is case-sensitive.
     */
    public function testGetBySymbolIsCaseSensitive(): void
    {
        // 'M' is mega (1e6).
        $mega = PrefixUtility::getBySymbol('M');
        $this->assertInstanceOf(Prefix::class, $mega);
        $this->assertSame('mega', $mega->name);
        $this->assertSame(1e6, $mega->multiplier);

        // 'm' is milli (1e-3).
        $milli = PrefixUtility::getBySymbol('m');
        $this->assertInstanceOf(Prefix::class, $milli);
        $this->assertSame('milli', $milli->name);
        $this->assertSame(1e-3, $milli->multiplier);

        // 'K' is not a valid prefix (kilo is lowercase 'k').
        $this->assertNull(PrefixUtility::getBySymbol('K'));
    }

    // endregion

    // region invert() tests

    /**
     * Test invert() converts kilo to milli.
     */
    public function testInvertKiloToMilli(): void
    {
        $kilo = PrefixUtility::getBySymbol('k');
        $result = PrefixUtility::invert($kilo);

        $this->assertInstanceOf(Prefix::class, $result);
        $this->assertSame('milli', $result->name);
        $this->assertSame('m', $result->asciiSymbol);
    }

    /**
     * Test invert() converts milli to kilo.
     */
    public function testInvertMilliToKilo(): void
    {
        $milli = PrefixUtility::getBySymbol('m');
        $result = PrefixUtility::invert($milli);

        $this->assertInstanceOf(Prefix::class, $result);
        $this->assertSame('kilo', $result->name);
    }

    /**
     * Test invert() converts mega to micro.
     */
    public function testInvertMegaToMicro(): void
    {
        $mega = PrefixUtility::getBySymbol('M');
        $result = PrefixUtility::invert($mega);

        $this->assertInstanceOf(Prefix::class, $result);
        $this->assertSame('micro', $result->name);
    }

    /**
     * Test invert() converts micro to mega.
     */
    public function testInvertMicroToMega(): void
    {
        $micro = PrefixUtility::getBySymbol('μ');
        $result = PrefixUtility::invert($micro);

        $this->assertInstanceOf(Prefix::class, $result);
        $this->assertSame('mega', $result->name);
    }

    /**
     * Test invert() converts giga to nano.
     */
    public function testInvertGigaToNano(): void
    {
        $giga = PrefixUtility::getBySymbol('G');
        $result = PrefixUtility::invert($giga);

        $this->assertInstanceOf(Prefix::class, $result);
        $this->assertSame('nano', $result->name);
    }

    /**
     * Test invert() converts tera to pico.
     */
    public function testInvertTeraToPico(): void
    {
        $tera = PrefixUtility::getBySymbol('T');
        $result = PrefixUtility::invert($tera);

        $this->assertInstanceOf(Prefix::class, $result);
        $this->assertSame('pico', $result->name);
    }

    /**
     * Test invert() converts hecto to centi.
     */
    public function testInvertHectoToCenti(): void
    {
        $hecto = PrefixUtility::getBySymbol('h');
        $result = PrefixUtility::invert($hecto);

        $this->assertInstanceOf(Prefix::class, $result);
        $this->assertSame('centi', $result->name);
    }

    /**
     * Test invert() converts deca to deci.
     */
    public function testInvertDecaToDeci(): void
    {
        $deca = PrefixUtility::getBySymbol('da');
        $result = PrefixUtility::invert($deca);

        $this->assertInstanceOf(Prefix::class, $result);
        $this->assertSame('deci', $result->name);
    }

    /**
     * Test invert() converts quetta to quecto.
     */
    public function testInvertQuettaToQuecto(): void
    {
        $quetta = PrefixUtility::getBySymbol('Q');
        $result = PrefixUtility::invert($quetta);

        $this->assertInstanceOf(Prefix::class, $result);
        $this->assertSame('quecto', $result->name);
    }

    /**
     * Test invert() throws for binary prefixes (no inverse exists).
     */
    public function testInvertThrowsForBinaryPrefixes(): void
    {
        $kibi = PrefixUtility::getBySymbol('Ki');

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Inverse of prefix 'Ki' not found");

        PrefixUtility::invert($kibi);
    }

    /**
     * Test invert() returns null for null input.
     */
    public function testInvertReturnsNullForNullInput(): void
    {
        $this->assertNull(PrefixUtility::invert(null));
    }

    /**
     * Test invert() round-trip returns equivalent prefix.
     */
    public function testInvertRoundTrip(): void
    {
        $prefixes = ['m', 'n', 'p', 'k', 'M', 'G'];

        foreach ($prefixes as $symbol) {
            $original = PrefixUtility::getBySymbol($symbol);
            $inverted = PrefixUtility::invert($original);
            $roundTrip = PrefixUtility::invert($inverted);

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
            $prefix = PrefixUtility::getBySymbol($symbol);
            $inverse = PrefixUtility::invert($prefix);

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
        $this->assertTrue(PrefixUtility::isValidGroupCode(PrefixUtility::GROUP_CODE_SMALL_ENGINEERING_METRIC));
        $this->assertTrue(PrefixUtility::isValidGroupCode(PrefixUtility::GROUP_CODE_SMALL_NON_ENGINEERING_METRIC));
        $this->assertTrue(PrefixUtility::isValidGroupCode(PrefixUtility::GROUP_CODE_LARGE_NON_ENGINEERING_METRIC));
        $this->assertTrue(PrefixUtility::isValidGroupCode(PrefixUtility::GROUP_CODE_LARGE_ENGINEERING_METRIC));
        $this->assertTrue(PrefixUtility::isValidGroupCode(PrefixUtility::GROUP_CODE_BINARY));
    }

    /**
     * Test isValidGroupCode() returns false for combined group codes.
     */
    public function testIsValidGroupCodeReturnsFalseForCombinedGroups(): void
    {
        // Combined codes are not "valid" base codes.
        $this->assertFalse(PrefixUtility::isValidGroupCode(PrefixUtility::GROUP_CODE_SMALL_METRIC));
        $this->assertFalse(PrefixUtility::isValidGroupCode(PrefixUtility::GROUP_CODE_LARGE_METRIC));
        $this->assertFalse(PrefixUtility::isValidGroupCode(PrefixUtility::GROUP_CODE_METRIC));
        $this->assertFalse(PrefixUtility::isValidGroupCode(PrefixUtility::GROUP_CODE_ALL));
    }

    /**
     * Test isValidGroupCode() returns false for invalid values.
     */
    public function testIsValidGroupCodeReturnsFalseForInvalidValues(): void
    {
        $this->assertFalse(PrefixUtility::isValidGroupCode(0));
        $this->assertFalse(PrefixUtility::isValidGroupCode(-1));
        $this->assertFalse(PrefixUtility::isValidGroupCode(32));
        $this->assertFalse(PrefixUtility::isValidGroupCode(100));
    }

    // endregion

    // region Prefix object property tests

    /**
     * Test that prefixes have correct groupCode property.
     */
    public function testPrefixGroupCodeProperty(): void
    {
        $kilo = PrefixUtility::getBySymbol('k');
        $this->assertInstanceOf(Prefix::class, $kilo);
        $this->assertSame(PrefixUtility::GROUP_CODE_LARGE_ENGINEERING_METRIC, $kilo->groupCode);

        $milli = PrefixUtility::getBySymbol('m');
        $this->assertInstanceOf(Prefix::class, $milli);
        $this->assertSame(PrefixUtility::GROUP_CODE_SMALL_ENGINEERING_METRIC, $milli->groupCode);

        $centi = PrefixUtility::getBySymbol('c');
        $this->assertInstanceOf(Prefix::class, $centi);
        $this->assertSame(PrefixUtility::GROUP_CODE_SMALL_NON_ENGINEERING_METRIC, $centi->groupCode);

        $hecto = PrefixUtility::getBySymbol('h');
        $this->assertInstanceOf(Prefix::class, $hecto);
        $this->assertSame(PrefixUtility::GROUP_CODE_LARGE_NON_ENGINEERING_METRIC, $hecto->groupCode);

        $kibi = PrefixUtility::getBySymbol('Ki');
        $this->assertInstanceOf(Prefix::class, $kibi);
        $this->assertSame(PrefixUtility::GROUP_CODE_BINARY, $kibi->groupCode);
    }

    /**
     * Test that micro prefix has correct Unicode symbol.
     */
    public function testMicroPrefixUnicodeSymbol(): void
    {
        $micro = PrefixUtility::getBySymbol('u');

        $this->assertInstanceOf(Prefix::class, $micro);
        $this->assertSame('u', $micro->asciiSymbol);
        $this->assertSame('μ', $micro->unicodeSymbol);
    }

    /**
     * Test that prefixes without special Unicode have matching symbols.
     */
    public function testPrefixesWithoutUnicodeHaveMatchingSymbols(): void
    {
        $kilo = PrefixUtility::getBySymbol('k');
        $this->assertInstanceOf(Prefix::class, $kilo);
        $this->assertSame($kilo->asciiSymbol, $kilo->unicodeSymbol);

        $mega = PrefixUtility::getBySymbol('M');
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
        $result = PrefixUtility::getPrefixes(PrefixUtility::GROUP_CODE_SMALL_ENGINEERING_METRIC);
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
        $result = PrefixUtility::getPrefixes(PrefixUtility::GROUP_CODE_LARGE_ENGINEERING_METRIC);
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
        $result = PrefixUtility::getPrefixes(PrefixUtility::GROUP_CODE_BINARY);
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
        $this->assertSame((float)(2 ** 10), PrefixUtility::getBySymbol('Ki')?->multiplier);
        $this->assertSame((float)(2 ** 20), PrefixUtility::getBySymbol('Mi')?->multiplier);
        $this->assertSame((float)(2 ** 30), PrefixUtility::getBySymbol('Gi')?->multiplier);
        $this->assertSame((float)(2 ** 40), PrefixUtility::getBySymbol('Ti')?->multiplier);
        $this->assertSame((float)(2 ** 50), PrefixUtility::getBySymbol('Pi')?->multiplier);
        $this->assertSame((float)(2 ** 60), PrefixUtility::getBySymbol('Ei')?->multiplier);
    }

    // endregion
}
