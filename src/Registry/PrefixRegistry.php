<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Registry;

use DomainException;
use Galaxon\Core\Floats;

class PrefixRegistry
{
    // region Constants

    /**
     * Constants for prefix groups.
     */

    // 1 = Small metric (quecto to deci)
    public const int PREFIX_GROUP_SMALL_METRIC = 1;

    // 2 = Large metric (deca to quetta)
    public const int PREFIX_GROUP_LARGE_METRIC = 2;

    // 3 = All metric (1|2)
    public const int PREFIX_GROUP_METRIC = self::PREFIX_GROUP_SMALL_METRIC | self::PREFIX_GROUP_LARGE_METRIC;

    // 4 = Binary (Ki, Mi, Gi, etc.)
    public const int PREFIX_GROUP_BINARY = 4;

    // 6 = Large metric + binary (2|4)
    public const int PREFIX_GROUP_LARGE = self::PREFIX_GROUP_LARGE_METRIC | self::PREFIX_GROUP_BINARY;

    // 7 = All (1|2|4)
    public const int PREFIX_GROUP_ALL = self::PREFIX_GROUP_METRIC | self::PREFIX_GROUP_BINARY;

    /**
     * Standard metric prefixes down to quecto (10^-30).
     *
     * Includes both standard symbols and alternatives (e.g. 'u' for micro).
     *
     * @var array<string, float>
     */
    public const array PREFIXES_SMALL_METRIC = [
        'q' => 1e-30,  // quecto
        'r' => 1e-27,  // ronto
        'y' => 1e-24,  // yocto
        'z' => 1e-21,  // zepto
        'a' => 1e-18,  // atto
        'f' => 1e-15,  // femto
        'p' => 1e-12,  // pico
        'n' => 1e-9,   // nano
        'μ' => 1e-6,   // micro
        'u' => 1e-6,   // micro (alias)
        'm' => 1e-3,   // milli
        'c' => 1e-2,   // centi
        'd' => 1e-1,   // deci
    ];

    /**
     * Standard metric prefixes up to quetta (10^30).
     *
     * @var array<string, float>
     */
    public const array PREFIXES_LARGE_METRIC = [
        'da' => 1e1,    // deca
        'h'  => 1e2,    // hecto
        'k'  => 1e3,    // kilo
        'M'  => 1e6,    // mega
        'G'  => 1e9,    // giga
        'T'  => 1e12,   // tera
        'P'  => 1e15,   // peta
        'E'  => 1e18,   // exa
        'Z'  => 1e21,   // zetta
        'Y'  => 1e24,   // yotta
        'R'  => 1e27,   // ronna
        'Q'  => 1e30,   // quetta
    ];

    /**
     * Binary prefixes for memory measurements.
     *
     * @var array<string, float>
     */
    public const array PREFIXES_BINARY = [
        'Ki' => 2 ** 10, // kibi
        'Mi' => 2 ** 20, // mebi
        'Gi' => 2 ** 30, // gibi
        'Ti' => 2 ** 40, // tebi
        'Pi' => 2 ** 50, // pebi
        'Ei' => 2 ** 60, // exbi
        'Zi' => 2 ** 70, // zebi
        'Yi' => 2 ** 80, // yobi
    ];

    // endregion

    // region Static methods

    /**
     * Return an array of prefixes, with multipliers, given an integer group code comprising bitwise flags.
     *
     * This can be overridden in the derived class.
     *
     * @param int $prefixGroup Code indicating the prefix groups to include.
     * @return array<string, float>
     */
    public static function getPrefixes(int $prefixGroup = self::PREFIX_GROUP_ALL): array
    {
        $prefixes = [];

        // No prefixes.
        if ($prefixGroup === 0) {
            return $prefixes;
        }

        // Get the prefixes corresponding to the given code.
        if ($prefixGroup & self::PREFIX_GROUP_SMALL_METRIC) {
            $prefixes = array_merge($prefixes, self::PREFIXES_SMALL_METRIC);
        }
        if ($prefixGroup & self::PREFIX_GROUP_LARGE_METRIC) {
            $prefixes = array_merge($prefixes, self::PREFIXES_LARGE_METRIC);
        }
        if ($prefixGroup & self::PREFIX_GROUP_BINARY) {
            $prefixes = array_merge($prefixes, self::PREFIXES_BINARY);
        }

        return $prefixes;
    }

    /**
     * Return all metric prefixes (small and large).
     *
     * @return array<string, float> Prefix symbols mapped to multipliers.
     */
    public static function getMetricPrefixes(): array
    {
        return self::getPrefixes(self::PREFIX_GROUP_METRIC);
    }

    /**
     * Return engineering prefixes only (exponents that are multiples of 3).
     *
     * Excludes c (centi), d (deci), da (deca), and h (hecto).
     * This is the standard convention in engineering and scientific notation.
     *
     * @return array<string, float> Prefix symbols mapped to multipliers.
     */
    public static function getEngineeringPrefixes(): array
    {
        return array_filter(
            self::getMetricPrefixes(),
            static fn ($multiplier) => Floats::isApproxInt(log($multiplier, 1000))
        );
    }

    /**
     * Return the multiplier for a given prefix.
     *
     * @param string $prefix Prefix code, e.g. 'k' for kilo.
     * @return ?float Prefix multiplier, e.g. 1000 for kilo, or null if not found.
     */
    public static function getPrefixMultiplier(string $prefix): ?float
    {
        // Get all the prefixes.
        $prefixes = self::getPrefixes();

        // Return the multiplier for the given prefix, or null if not found.
        return $prefixes[$prefix] ?? null;
    }

    /**
     * Invert a metric prefix symbol.
     *
     * Returns the prefix with the opposite exponent, e.g. 'k' (10³) → 'm' (10⁻³).
     *
     * @param ?string $prefix The prefix symbol to invert.
     * @return ?string The inverted prefix symbol, or null if null was passed.
     * @throws \DomainException If the prefix is not a metric prefix (e.g. binary prefix).
     */
    public static function invert(?string $prefix): ?string
    {
        if ($prefix === null) {
            return null;
        }

        // Get the multiplier for this prefix.
        $prefixes = self::getPrefixes();
        $multiplier = $prefixes[$prefix] ?? null;

        // Check the prefix is valid.
        if ($multiplier === null) {
            throw new DomainException("Unknown prefix '$prefix'.");
        }

        // Find the reciprocal multiplier and matching prefix.
        $inversePrefix = array_find_key($prefixes, static fn ($mul) => Floats::approxEqual($mul, 1.0 / $multiplier));

        // Check an inverse could be found.
        if ($inversePrefix === null) {
            throw new DomainException("Inverse of prefix '$prefix' not found.");
        }

        return $inversePrefix;
    }

    // endregion
}
