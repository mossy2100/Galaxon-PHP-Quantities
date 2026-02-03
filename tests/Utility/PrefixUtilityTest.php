<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Utility;

use DomainException;
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
     * Test prefix group constants have correct values.
     */
    public function testPrefixGroupConstantValues(): void
    {
        $this->assertSame(1, PrefixUtility::GROUP_CODE_SMALL_METRIC);
        $this->assertSame(2, PrefixUtility::GROUP_CODE_LARGE_METRIC);
        $this->assertSame(3, PrefixUtility::GROUP_CODE_METRIC);
        $this->assertSame(4, PrefixUtility::GROUP_CODE_BINARY);
        $this->assertSame(6, PrefixUtility::GROUP_CODE_LARGE);
        $this->assertSame(7, PrefixUtility::GROUP_CODE_ALL);
    }

    /**
     * Test prefix group constants are proper bitwise combinations.
     */
    public function testPrefixGroupConstantsBitwiseCombinations(): void
    {
        $this->assertSame(
            PrefixUtility::GROUP_CODE_SMALL_METRIC | PrefixUtility::GROUP_CODE_LARGE_METRIC,
            PrefixUtility::GROUP_CODE_METRIC
        );
        $this->assertSame(
            PrefixUtility::GROUP_CODE_LARGE_METRIC | PrefixUtility::GROUP_CODE_BINARY,
            PrefixUtility::GROUP_CODE_LARGE
        );
        $this->assertSame(
            PrefixUtility::GROUP_CODE_METRIC | PrefixUtility::GROUP_CODE_BINARY,
            PrefixUtility::GROUP_CODE_ALL
        );
    }

    /**
     * Test small metric prefixes constant contains expected prefixes.
     */
    public function testSmallMetricPrefixesConstant(): void
    {
        $prefixes = PrefixUtility::PREFIXES_SMALL_ENGINEERING_METRIC;

        $this->assertArrayHasKey('m', $prefixes);  // milli
        $this->assertArrayHasKey('μ', $prefixes);  // micro
        $this->assertArrayHasKey('u', $prefixes);  // micro alias
        $this->assertArrayHasKey('n', $prefixes);  // nano
        $this->assertArrayHasKey('p', $prefixes);  // pico
        $this->assertArrayHasKey('q', $prefixes);  // quecto

        $this->assertSame(1e-3, $prefixes['m']);
        $this->assertSame(1e-6, $prefixes['μ']);
        $this->assertSame(1e-6, $prefixes['u']);
        $this->assertSame(1e-9, $prefixes['n']);
        $this->assertSame(1e-12, $prefixes['p']);
        $this->assertSame(1e-30, $prefixes['q']);
    }

    /**
     * Test large metric prefixes constant contains expected prefixes.
     */
    public function testLargeMetricPrefixesConstant(): void
    {
        $prefixes = PrefixUtility::PREFIXES_LARGE_ENGINEERING_METRIC;

        $this->assertArrayHasKey('k', $prefixes);   // kilo
        $this->assertArrayHasKey('M', $prefixes);   // mega
        $this->assertArrayHasKey('G', $prefixes);   // giga
        $this->assertArrayHasKey('T', $prefixes);   // tera
        $this->assertArrayHasKey('Q', $prefixes);   // quetta

        $this->assertSame(1e3, $prefixes['k']);
        $this->assertSame(1e6, $prefixes['M']);
        $this->assertSame(1e9, $prefixes['G']);
        $this->assertSame(1e12, $prefixes['T']);
        $this->assertSame(1e30, $prefixes['Q']);
    }

    /**
     * Test binary prefixes constant contains expected prefixes.
     */
    public function testBinaryPrefixesConstant(): void
    {
        $prefixes = PrefixUtility::PREFIXES_BINARY;

        $this->assertArrayHasKey('Ki', $prefixes);  // kibi
        $this->assertArrayHasKey('Mi', $prefixes);  // mebi
        $this->assertArrayHasKey('Gi', $prefixes);  // gibi
        $this->assertArrayHasKey('Ti', $prefixes);  // tebi

        $this->assertSame(2 ** 10, $prefixes['Ki']);
        $this->assertSame(2 ** 20, $prefixes['Mi']);
        $this->assertSame(2 ** 30, $prefixes['Gi']);
        $this->assertSame(2 ** 40, $prefixes['Ti']);
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
     * Test getPrefixes() with small metric group.
     */
    public function testGetPrefixesSmallMetric(): void
    {
        $result = PrefixUtility::getPrefixes(PrefixUtility::GROUP_CODE_SMALL_METRIC);

        $this->assertSame(PrefixUtility::PREFIXES_SMALL_ENGINEERING_METRIC, $result);
        $this->assertArrayHasKey('m', $result);
        $this->assertArrayHasKey('μ', $result);
        $this->assertArrayNotHasKey('k', $result);
        $this->assertArrayNotHasKey('Ki', $result);
    }

    /**
     * Test getPrefixes() with large metric group.
     */
    public function testGetPrefixesLargeMetric(): void
    {
        $result = PrefixUtility::getPrefixes(PrefixUtility::GROUP_CODE_LARGE_METRIC);

        $this->assertSame(PrefixUtility::PREFIXES_LARGE_ENGINEERING_METRIC, $result);
        $this->assertArrayHasKey('k', $result);
        $this->assertArrayHasKey('M', $result);
        $this->assertArrayNotHasKey('m', $result);
        $this->assertArrayNotHasKey('Ki', $result);
    }

    /**
     * Test getPrefixes() with metric group returns both small and large.
     */
    public function testGetPrefixesMetric(): void
    {
        $result = PrefixUtility::getPrefixes(PrefixUtility::GROUP_CODE_METRIC);

        $this->assertArrayHasKey('m', $result);   // small
        $this->assertArrayHasKey('μ', $result);   // small
        $this->assertArrayHasKey('k', $result);   // large
        $this->assertArrayHasKey('M', $result);   // large
        $this->assertArrayNotHasKey('Ki', $result);  // binary
    }

    /**
     * Test getPrefixes() with binary group.
     */
    public function testGetPrefixesBinary(): void
    {
        $result = PrefixUtility::getPrefixes(PrefixUtility::GROUP_CODE_BINARY);

        $this->assertSame(PrefixUtility::PREFIXES_BINARY, $result);
        $this->assertArrayHasKey('Ki', $result);
        $this->assertArrayHasKey('Mi', $result);
        $this->assertArrayNotHasKey('k', $result);
        $this->assertArrayNotHasKey('m', $result);
    }

    /**
     * Test getPrefixes() with large group returns large metric and binary.
     */
    public function testGetPrefixesLarge(): void
    {
        $result = PrefixUtility::getPrefixes(PrefixUtility::GROUP_CODE_LARGE);

        $this->assertArrayHasKey('k', $result);   // large metric
        $this->assertArrayHasKey('M', $result);   // large metric
        $this->assertArrayHasKey('Ki', $result);  // binary
        $this->assertArrayHasKey('Mi', $result);  // binary
        $this->assertArrayNotHasKey('m', $result);   // small metric
        $this->assertArrayNotHasKey('μ', $result);   // small metric
    }

    /**
     * Test getPrefixes() with all groups.
     */
    public function testGetPrefixesAll(): void
    {
        $result = PrefixUtility::getPrefixes(PrefixUtility::GROUP_CODE_ALL);

        $this->assertArrayHasKey('m', $result);   // small metric
        $this->assertArrayHasKey('k', $result);   // large metric
        $this->assertArrayHasKey('Ki', $result);  // binary
    }

    /**
     * Test getPrefixes() with default parameter returns all.
     */
    public function testGetPrefixesDefaultReturnsAll(): void
    {
        $result = PrefixUtility::getPrefixes();

        $this->assertSame(PrefixUtility::getPrefixes(PrefixUtility::GROUP_CODE_ALL), $result);
    }

    // endregion

    // region getMetricPrefixes() tests

    /**
     * Test getMetricPrefixes() returns both small and large metric prefixes.
     */
    public function testGetMetricPrefixes(): void
    {
        $result = PrefixUtility::getMetricPrefixes();

        $this->assertArrayHasKey('m', $result);   // small
        $this->assertArrayHasKey('μ', $result);   // small
        $this->assertArrayHasKey('k', $result);   // large
        $this->assertArrayHasKey('M', $result);   // large
        $this->assertArrayNotHasKey('Ki', $result);  // no binary
    }

    /**
     * Test getMetricPrefixes() equals getPrefixes() with metric group.
     */
    public function testGetMetricPrefixesEqualsGetPrefixesMetric(): void
    {
        $this->assertSame(
            PrefixUtility::getPrefixes(PrefixUtility::GROUP_CODE_METRIC),
            PrefixUtility::getMetricPrefixes()
        );
    }

    // endregion

    // region getEngineeringPrefixes() tests

    /**
     * Test getEngineeringPrefixes() includes multiples-of-3 prefixes.
     */
    public function testGetEngineeringPrefixesIncludesMultiplesOf3(): void
    {
        $result = PrefixUtility::getEngineeringPrefixes();

        // Large prefixes (10^3, 10^6, 10^9, etc.)
        $this->assertArrayHasKey('k', $result);
        $this->assertArrayHasKey('M', $result);
        $this->assertArrayHasKey('G', $result);
        $this->assertArrayHasKey('T', $result);

        // Small prefixes (10^-3, 10^-6, 10^-9, etc.)
        $this->assertArrayHasKey('m', $result);
        $this->assertArrayHasKey('μ', $result);
        $this->assertArrayHasKey('n', $result);
        $this->assertArrayHasKey('p', $result);
    }

    /**
     * Test getEngineeringPrefixes() excludes non-multiples-of-3 prefixes.
     */
    public function testGetEngineeringPrefixesExcludesNonMultiplesOf3(): void
    {
        $result = PrefixUtility::getEngineeringPrefixes();

        $this->assertArrayNotHasKey('c', $result);   // centi (10^-2)
        $this->assertArrayNotHasKey('d', $result);   // deci (10^-1)
        $this->assertArrayNotHasKey('da', $result);  // deca (10^1)
        $this->assertArrayNotHasKey('h', $result);   // hecto (10^2)
    }

    /**
     * Test getEngineeringPrefixes() excludes binary prefixes.
     */
    public function testGetEngineeringPrefixesExcludesBinary(): void
    {
        $result = PrefixUtility::getEngineeringPrefixes();

        $this->assertArrayNotHasKey('Ki', $result);
        $this->assertArrayNotHasKey('Mi', $result);
        $this->assertArrayNotHasKey('Gi', $result);
    }

    /**
     * Test getEngineeringPrefixes() has correct multiplier values.
     */
    public function testGetEngineeringPrefixesMultiplierValues(): void
    {
        $result = PrefixUtility::getEngineeringPrefixes();

        $this->assertSame(1e3, $result['k']);
        $this->assertSame(1e6, $result['M']);
        $this->assertSame(1e-3, $result['m']);
        $this->assertSame(1e-6, $result['μ']);
    }

    // endregion

    // region getPrefixMultiplier() tests

    /**
     * Test getPrefixMultiplier() returns correct value for kilo.
     */
    public function testGetPrefixMultiplierKilo(): void
    {
        $this->assertSame(1e3, PrefixUtility::getPrefixMultiplier('k'));
    }

    /**
     * Test getPrefixMultiplier() returns correct value for milli.
     */
    public function testGetPrefixMultiplierMilli(): void
    {
        $this->assertSame(1e-3, PrefixUtility::getPrefixMultiplier('m'));
    }

    /**
     * Test getPrefixMultiplier() returns correct value for micro (μ).
     */
    public function testGetPrefixMultiplierMicroUnicode(): void
    {
        $this->assertSame(1e-6, PrefixUtility::getPrefixMultiplier('μ'));
    }

    /**
     * Test getPrefixMultiplier() returns correct value for micro (u alias).
     */
    public function testGetPrefixMultiplierMicroAscii(): void
    {
        $this->assertSame(1e-6, PrefixUtility::getPrefixMultiplier('u'));
    }

    /**
     * Test getPrefixMultiplier() returns correct value for binary prefix.
     */
    public function testGetPrefixMultiplierBinary(): void
    {
        $this->assertEquals(2 ** 10, PrefixUtility::getPrefixMultiplier('Ki'));
        $this->assertEquals(2 ** 20, PrefixUtility::getPrefixMultiplier('Mi'));
    }

    /**
     * Test getPrefixMultiplier() returns null for invalid prefix.
     */
    public function testGetPrefixMultiplierInvalidReturnsNull(): void
    {
        $this->assertNull(PrefixUtility::getPrefixMultiplier('X'));
        $this->assertNull(PrefixUtility::getPrefixMultiplier('invalid'));
        $this->assertNull(PrefixUtility::getPrefixMultiplier(''));
    }

    /**
     * Test getPrefixMultiplier() is case-sensitive.
     */
    public function testGetPrefixMultiplierCaseSensitive(): void
    {
        // 'M' is mega (1e6), 'm' is milli (1e-3)
        $this->assertSame(1e6, PrefixUtility::getPrefixMultiplier('M'));
        $this->assertSame(1e-3, PrefixUtility::getPrefixMultiplier('m'));

        // 'K' is not a valid prefix (kilo is lowercase 'k')
        $this->assertNull(PrefixUtility::getPrefixMultiplier('K'));
    }

    // endregion

    // region invert() tests

    /**
     * Test invert() converts kilo to milli.
     */
    public function testInvertKiloToMilli(): void
    {
        $this->assertSame('m', PrefixUtility::invert('k'));
    }

    /**
     * Test invert() converts milli to kilo.
     */
    public function testInvertMilliToKilo(): void
    {
        $this->assertSame('k', PrefixUtility::invert('m'));
    }

    /**
     * Test invert() converts mega to micro (μ).
     */
    public function testInvertMegaToMicro(): void
    {
        $this->assertSame('μ', PrefixUtility::invert('M'));
    }

    /**
     * Test invert() converts micro (μ) to mega.
     */
    public function testInvertMicroToMega(): void
    {
        $this->assertSame('M', PrefixUtility::invert('μ'));
    }

    /**
     * Test invert() converts micro alias (u) to mega.
     */
    public function testInvertMicroAliasToMega(): void
    {
        $this->assertSame('M', PrefixUtility::invert('u'));
    }

    /**
     * Test invert() converts giga to nano.
     */
    public function testInvertGigaToNano(): void
    {
        $this->assertSame('n', PrefixUtility::invert('G'));
    }

    /**
     * Test invert() converts nano to giga.
     */
    public function testInvertNanoToGiga(): void
    {
        $this->assertSame('G', PrefixUtility::invert('n'));
    }

    /**
     * Test invert() converts tera to pico.
     */
    public function testInvertTeraToPico(): void
    {
        $this->assertSame('p', PrefixUtility::invert('T'));
    }

    /**
     * Test invert() converts pico to tera.
     */
    public function testInvertPicoToTera(): void
    {
        $this->assertSame('T', PrefixUtility::invert('p'));
    }

    /**
     * Test invert() converts hecto to centi.
     */
    public function testInvertHectoToCenti(): void
    {
        $this->assertSame('c', PrefixUtility::invert('h'));
    }

    /**
     * Test invert() converts centi to hecto.
     */
    public function testInvertCentiToHecto(): void
    {
        $this->assertSame('h', PrefixUtility::invert('c'));
    }

    /**
     * Test invert() converts deca to deci.
     */
    public function testInvertDecaToDeci(): void
    {
        $this->assertSame('d', PrefixUtility::invert('da'));
    }

    /**
     * Test invert() converts deci to deca.
     */
    public function testInvertDeciToDeca(): void
    {
        $this->assertSame('da', PrefixUtility::invert('d'));
    }

    /**
     * Test invert() converts quetta to quecto.
     */
    public function testInvertQuettaToQuecto(): void
    {
        $this->assertSame('q', PrefixUtility::invert('Q'));
    }

    /**
     * Test invert() converts quecto to quetta.
     */
    public function testInvertQuectoToQuetta(): void
    {
        $this->assertSame('Q', PrefixUtility::invert('q'));
    }

    /**
     * Test invert() throws for binary prefixes.
     */
    public function testInvertThrowsForBinaryPrefixes(): void
    {
        $this->expectException(DomainException::class);

        PrefixUtility::invert('Ki');
    }

    /**
     * Test invert() throws for invalid prefix.
     */
    public function testInvertThrowsForInvalidPrefix(): void
    {
        $this->expectException(DomainException::class);

        PrefixUtility::invert('X');
    }

    /**
     * Test invert() throws for empty string.
     */
    public function testInvertThrowsForEmptyString(): void
    {
        $this->expectException(DomainException::class);

        PrefixUtility::invert('');
    }

    /**
     * Test invert() returns null for null input.
     */
    public function testInvertReturnsNullForNullInput(): void
    {
        $this->assertNull(PrefixUtility::invert(null));
    }

    /**
     * Test invert() round-trip returns original prefix.
     */
    public function testInvertRoundTrip(): void
    {
        // Small → Large → Small
        $this->assertSame('m', PrefixUtility::invert(PrefixUtility::invert('m')));
        $this->assertSame('n', PrefixUtility::invert(PrefixUtility::invert('n')));
        $this->assertSame('p', PrefixUtility::invert(PrefixUtility::invert('p')));

        // Large → Small → Large
        $this->assertSame('k', PrefixUtility::invert(PrefixUtility::invert('k')));
        $this->assertSame('M', PrefixUtility::invert(PrefixUtility::invert('M')));
        $this->assertSame('G', PrefixUtility::invert(PrefixUtility::invert('G')));
    }

    /**
     * Test invert() multipliers are reciprocals.
     */
    public function testInvertMultipliersAreReciprocals(): void
    {
        $prefixes = ['k', 'm', 'M', 'μ', 'G', 'n', 'T', 'p'];

        foreach ($prefixes as $prefix) {
            $inverse = PrefixUtility::invert($prefix);
            $this->assertNotNull($inverse);

            $multiplier = PrefixUtility::getPrefixMultiplier($prefix);
            $inverseMultiplier = PrefixUtility::getPrefixMultiplier($inverse);

            // multiplier × inverseMultiplier should equal 1
            $this->assertEqualsWithDelta(1.0, $multiplier * $inverseMultiplier, 1e-20);
        }
    }

    // endregion
}
