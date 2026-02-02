<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Registry;

use DomainException;
use Galaxon\Quantities\Helpers\PrefixUtils;
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
     * Test prefix group constants have correct values.
     */
    public function testPrefixGroupConstantValues(): void
    {
        $this->assertSame(1, PrefixRegistry::GROUP_CODE_SMALL_METRIC);
        $this->assertSame(2, PrefixUtils::GROUP_CODE_LARGE_METRIC);
        $this->assertSame(3, PrefixUtils::GROUP_CODE_METRIC);
        $this->assertSame(4, PrefixUtils::GROUP_CODE_BINARY);
        $this->assertSame(6, PrefixUtils::GROUP_CODE_LARGE);
        $this->assertSame(7, PrefixRegistry::GROUP_CODE_ALL);
    }

    /**
     * Test prefix group constants are proper bitwise combinations.
     */
    public function testPrefixGroupConstantsBitwiseCombinations(): void
    {
        $this->assertSame(
            PrefixRegistry::GROUP_CODE_SMALL_METRIC | PrefixUtils::GROUP_CODE_LARGE_METRIC,
            PrefixRegistry::GROUP_CODE_METRIC
        );
        $this->assertSame(
            PrefixRegistry::GROUP_CODE_LARGE_METRIC | PrefixRegistry::GROUP_CODE_BINARY,
            PrefixUtils::GROUP_CODE_LARGE
        );
        $this->assertSame(
            PrefixUtils::GROUP_CODE_METRIC | PrefixUtils::GROUP_CODE_BINARY,
            PrefixRegistry::GROUP_CODE_ALL
        );
    }

    /**
     * Test small metric prefixes constant contains expected prefixes.
     */
    public function testSmallMetricPrefixesConstant(): void
    {
        $prefixes = PrefixUtils::PREFIXES_SMALL_ENGINEERING_METRIC;

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
        $prefixes = PrefixUtils::PREFIXES_LARGE_ENGINEERING_METRIC;

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
        $prefixes = PrefixUtils::PREFIXES_BINARY;

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
        $result = PrefixUtils::getPrefixes(0);

        $this->assertSame([], $result);
    }

    /**
     * Test getPrefixes() with small metric group.
     */
    public function testGetPrefixesSmallMetric(): void
    {
        $result = PrefixUtils::getPrefixes(PrefixUtils::GROUP_CODE_SMALL_METRIC);

        $this->assertSame(PrefixUtils::PREFIXES_SMALL_ENGINEERING_METRIC, $result);
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
        $result = PrefixRegistry::getPrefixes(PrefixRegistry::GROUP_CODE_LARGE_METRIC);

        $this->assertSame(PrefixUtils::PREFIXES_LARGE_ENGINEERING_METRIC, $result);
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
        $result = PrefixUtils::getPrefixes(PrefixUtils::GROUP_CODE_METRIC);

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
        $result = PrefixUtils::getPrefixes(PrefixRegistry::GROUP_CODE_BINARY);

        $this->assertSame(PrefixUtils::PREFIXES_BINARY, $result);
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
        $result = PrefixUtils::getPrefixes(PrefixUtils::GROUP_CODE_LARGE);

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
        $result = PrefixUtils::getPrefixes(PrefixUtils::GROUP_CODE_ALL);

        $this->assertArrayHasKey('m', $result);   // small metric
        $this->assertArrayHasKey('k', $result);   // large metric
        $this->assertArrayHasKey('Ki', $result);  // binary
    }

    /**
     * Test getPrefixes() with default parameter returns all.
     */
    public function testGetPrefixesDefaultReturnsAll(): void
    {
        $result = PrefixUtils::getPrefixes();

        $this->assertSame(PrefixRegistry::getPrefixes(PrefixUtils::GROUP_CODE_ALL), $result);
    }

    // endregion

    // region getMetricPrefixes() tests

    /**
     * Test getMetricPrefixes() returns both small and large metric prefixes.
     */
    public function testGetMetricPrefixes(): void
    {
        $result = PrefixRegistry::getMetricPrefixes();

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
            PrefixUtils::getPrefixes(PrefixUtils::GROUP_CODE_METRIC),
            PrefixUtils::getMetricPrefixes()
        );
    }

    // endregion

    // region getEngineeringPrefixes() tests

    /**
     * Test getEngineeringPrefixes() includes multiples-of-3 prefixes.
     */
    public function testGetEngineeringPrefixesIncludesMultiplesOf3(): void
    {
        $result = PrefixUtils::getEngineeringPrefixes();

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
        $result = PrefixUtils::getEngineeringPrefixes();

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
        $result = PrefixUtils::getEngineeringPrefixes();

        $this->assertArrayNotHasKey('Ki', $result);
        $this->assertArrayNotHasKey('Mi', $result);
        $this->assertArrayNotHasKey('Gi', $result);
    }

    /**
     * Test getEngineeringPrefixes() has correct multiplier values.
     */
    public function testGetEngineeringPrefixesMultiplierValues(): void
    {
        $result = PrefixUtils::getEngineeringPrefixes();

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
        $this->assertSame(1e3, PrefixRegistry::getPrefixMultiplier('k'));
    }

    /**
     * Test getPrefixMultiplier() returns correct value for milli.
     */
    public function testGetPrefixMultiplierMilli(): void
    {
        $this->assertSame(1e-3, PrefixRegistry::getPrefixMultiplier('m'));
    }

    /**
     * Test getPrefixMultiplier() returns correct value for micro (μ).
     */
    public function testGetPrefixMultiplierMicroUnicode(): void
    {
        $this->assertSame(1e-6, PrefixRegistry::getPrefixMultiplier('μ'));
    }

    /**
     * Test getPrefixMultiplier() returns correct value for micro (u alias).
     */
    public function testGetPrefixMultiplierMicroAscii(): void
    {
        $this->assertSame(1e-6, PrefixUtils::getPrefixMultiplier('u'));
    }

    /**
     * Test getPrefixMultiplier() returns correct value for binary prefix.
     */
    public function testGetPrefixMultiplierBinary(): void
    {
        $this->assertEquals(2 ** 10, PrefixRegistry::getPrefixMultiplier('Ki'));
        $this->assertEquals(2 ** 20, PrefixUtils::getPrefixMultiplier('Mi'));
    }

    /**
     * Test getPrefixMultiplier() returns null for invalid prefix.
     */
    public function testGetPrefixMultiplierInvalidReturnsNull(): void
    {
        $this->assertNull(PrefixRegistry::getPrefixMultiplier('X'));
        $this->assertNull(PrefixUtils::getPrefixMultiplier('invalid'));
        $this->assertNull(PrefixUtils::getPrefixMultiplier(''));
    }

    /**
     * Test getPrefixMultiplier() is case-sensitive.
     */
    public function testGetPrefixMultiplierCaseSensitive(): void
    {
        // 'M' is mega (1e6), 'm' is milli (1e-3)
        $this->assertSame(1e6, PrefixUtils::getPrefixMultiplier('M'));
        $this->assertSame(1e-3, PrefixRegistry::getPrefixMultiplier('m'));

        // 'K' is not a valid prefix (kilo is lowercase 'k')
        $this->assertNull(PrefixUtils::getPrefixMultiplier('K'));
    }

    // endregion

    // region invert() tests

    /**
     * Test invert() converts kilo to milli.
     */
    public function testInvertKiloToMilli(): void
    {
        $this->assertSame('m', PrefixUtils::invert('k'));
    }

    /**
     * Test invert() converts milli to kilo.
     */
    public function testInvertMilliToKilo(): void
    {
        $this->assertSame('k', PrefixUtils::invert('m'));
    }

    /**
     * Test invert() converts mega to micro (μ).
     */
    public function testInvertMegaToMicro(): void
    {
        $this->assertSame('μ', PrefixUtils::invert('M'));
    }

    /**
     * Test invert() converts micro (μ) to mega.
     */
    public function testInvertMicroToMega(): void
    {
        $this->assertSame('M', PrefixRegistry::invert('μ'));
    }

    /**
     * Test invert() converts micro alias (u) to mega.
     */
    public function testInvertMicroAliasToMega(): void
    {
        $this->assertSame('M', PrefixUtils::invert('u'));
    }

    /**
     * Test invert() converts giga to nano.
     */
    public function testInvertGigaToNano(): void
    {
        $this->assertSame('n', PrefixUtils::invert('G'));
    }

    /**
     * Test invert() converts nano to giga.
     */
    public function testInvertNanoToGiga(): void
    {
        $this->assertSame('G', PrefixRegistry::invert('n'));
    }

    /**
     * Test invert() converts tera to pico.
     */
    public function testInvertTeraToPico(): void
    {
        $this->assertSame('p', PrefixUtils::invert('T'));
    }

    /**
     * Test invert() converts pico to tera.
     */
    public function testInvertPicoToTera(): void
    {
        $this->assertSame('T', PrefixUtils::invert('p'));
    }

    /**
     * Test invert() converts hecto to centi.
     */
    public function testInvertHectoToCenti(): void
    {
        $this->assertSame('c', PrefixUtils::invert('h'));
    }

    /**
     * Test invert() converts centi to hecto.
     */
    public function testInvertCentiToHecto(): void
    {
        $this->assertSame('h', PrefixUtils::invert('c'));
    }

    /**
     * Test invert() converts deca to deci.
     */
    public function testInvertDecaToDeci(): void
    {
        $this->assertSame('d', PrefixRegistry::invert('da'));
    }

    /**
     * Test invert() converts deci to deca.
     */
    public function testInvertDeciToDeca(): void
    {
        $this->assertSame('da', PrefixRegistry::invert('d'));
    }

    /**
     * Test invert() converts quetta to quecto.
     */
    public function testInvertQuettaToQuecto(): void
    {
        $this->assertSame('q', PrefixUtils::invert('Q'));
    }

    /**
     * Test invert() converts quecto to quetta.
     */
    public function testInvertQuectoToQuetta(): void
    {
        $this->assertSame('Q', PrefixUtils::invert('q'));
    }

    /**
     * Test invert() throws for binary prefixes.
     */
    public function testInvertThrowsForBinaryPrefixes(): void
    {
        $this->expectException(DomainException::class);

        PrefixUtils::invert('Ki');
    }

    /**
     * Test invert() throws for invalid prefix.
     */
    public function testInvertThrowsForInvalidPrefix(): void
    {
        $this->expectException(DomainException::class);

        PrefixUtils::invert('X');
    }

    /**
     * Test invert() throws for empty string.
     */
    public function testInvertThrowsForEmptyString(): void
    {
        $this->expectException(DomainException::class);

        PrefixUtils::invert('');
    }

    /**
     * Test invert() returns null for null input.
     */
    public function testInvertReturnsNullForNullInput(): void
    {
        $this->assertNull(PrefixUtils::invert(null));
    }

    /**
     * Test invert() round-trip returns original prefix.
     */
    public function testInvertRoundTrip(): void
    {
        // Small → Large → Small
        $this->assertSame('m', PrefixRegistry::invert(PrefixUtils::invert('m')));
        $this->assertSame('n', PrefixUtils::invert(PrefixUtils::invert('n')));
        $this->assertSame('p', PrefixUtils::invert(PrefixUtils::invert('p')));

        // Large → Small → Large
        $this->assertSame('k', PrefixUtils::invert(PrefixUtils::invert('k')));
        $this->assertSame('M', PrefixUtils::invert(PrefixRegistry::invert('M')));
        $this->assertSame('G', PrefixUtils::invert(PrefixUtils::invert('G')));
    }

    /**
     * Test invert() multipliers are reciprocals.
     */
    public function testInvertMultipliersAreReciprocals(): void
    {
        $prefixes = ['k', 'm', 'M', 'μ', 'G', 'n', 'T', 'p'];

        foreach ($prefixes as $prefix) {
            $inverse = PrefixRegistry::invert($prefix);
            $this->assertNotNull($inverse);

            $multiplier = PrefixUtils::getPrefixMultiplier($prefix);
            $inverseMultiplier = PrefixUtils::getPrefixMultiplier($inverse);

            // multiplier × inverseMultiplier should equal 1
            $this->assertEqualsWithDelta(1.0, $multiplier * $inverseMultiplier, 1e-20);
        }
    }

    // endregion
}
