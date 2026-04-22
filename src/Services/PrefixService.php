<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Services;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Core\Floats;
use Galaxon\Quantities\Internal\Prefix;

/**
 * Utility class for working with SI and binary prefixes.
 *
 * Provides access to metric prefixes (milli, kilo, mega, etc.) and binary prefixes (kibi, mebi, etc.)
 * organized by group codes for flexible filtering.
 */
class PrefixService
{
    // region Public constants

    /**
     * Base prefix group codes.
     */
    public const int GROUP_SMALL_METRIC = 1;
    public const int GROUP_MEDIUM_METRIC = 2;
    public const int GROUP_LARGE_METRIC = 4;
    public const int GROUP_BINARY = 8;

    /**
     * Combined prefix group codes.
     */
    public const int GROUP_METRIC = self::GROUP_SMALL_METRIC | self::GROUP_MEDIUM_METRIC | self::GROUP_LARGE_METRIC;
    public const int GROUP_ENGINEERING = self::GROUP_SMALL_METRIC | self::GROUP_LARGE_METRIC;
    public const int GROUP_LARGE = self::GROUP_LARGE_METRIC | self::GROUP_BINARY;
    public const int GROUP_ALL = self::GROUP_METRIC | self::GROUP_BINARY;

    // endregion

    // region Private constants

    /**
     * Prefix definitions.
     *
     * @var array<string, array{
     *     multiplier: int|float,
     *     prefixGroup: int,
     *     asciiSymbol: string,
     *     unicodeSymbol?: string,
     *     alternateSymbol?: string
     * }>
     */
    private const array PREFIX_DEFINITIONS = [
        'quecto' => [
            'multiplier'  => 1e-30,
            'prefixGroup' => self::GROUP_SMALL_METRIC,
            'asciiSymbol' => 'q',
        ],
        'ronto'  => [
            'multiplier'  => 1e-27,
            'prefixGroup' => self::GROUP_SMALL_METRIC,
            'asciiSymbol' => 'r',
        ],
        'yocto'  => [
            'multiplier'  => 1e-24,
            'prefixGroup' => self::GROUP_SMALL_METRIC,
            'asciiSymbol' => 'y',
        ],
        'zepto'  => [
            'multiplier'  => 1e-21,
            'prefixGroup' => self::GROUP_SMALL_METRIC,
            'asciiSymbol' => 'z',
        ],
        'atto'   => [
            'multiplier'  => 1e-18,
            'prefixGroup' => self::GROUP_SMALL_METRIC,
            'asciiSymbol' => 'a',
        ],
        'femto'  => [
            'multiplier'  => 1e-15,
            'prefixGroup' => self::GROUP_SMALL_METRIC,
            'asciiSymbol' => 'f',
        ],
        'pico'   => [
            'multiplier'  => 1e-12,
            'prefixGroup' => self::GROUP_SMALL_METRIC,
            'asciiSymbol' => 'p',
        ],
        'nano'   => [
            'multiplier'  => 1e-9,
            'prefixGroup' => self::GROUP_SMALL_METRIC,
            'asciiSymbol' => 'n',
        ],
        'micro'  => [
            'multiplier'      => 1e-6,
            'prefixGroup'     => self::GROUP_SMALL_METRIC,
            'asciiSymbol'     => 'u',
            'unicodeSymbol'   => 'µ', // U+00B5 MICRO SIGN
            'alternateSymbol' => 'μ', // U+03BC GREEK SMALL LETTER MU
        ],
        'milli'  => [
            'multiplier'  => 1e-3,
            'prefixGroup' => self::GROUP_SMALL_METRIC,
            'asciiSymbol' => 'm',
        ],
        'centi'  => [
            'multiplier'  => 1e-2,
            'prefixGroup' => self::GROUP_MEDIUM_METRIC,
            'asciiSymbol' => 'c',
        ],
        'deci'   => [
            'multiplier'  => 1e-1,
            'prefixGroup' => self::GROUP_MEDIUM_METRIC,
            'asciiSymbol' => 'd',
        ],
        'deca'   => [
            'multiplier'  => 1e1,
            'prefixGroup' => self::GROUP_MEDIUM_METRIC,
            'asciiSymbol' => 'da',
        ],
        'hecto'  => [
            'multiplier'  => 1e2,
            'prefixGroup' => self::GROUP_MEDIUM_METRIC,
            'asciiSymbol' => 'h',
        ],
        'kilo'   => [
            'multiplier'  => 1e3,
            'prefixGroup' => self::GROUP_LARGE_METRIC,
            'asciiSymbol' => 'k',
        ],
        'mega'   => [
            'multiplier'  => 1e6,
            'prefixGroup' => self::GROUP_LARGE_METRIC,
            'asciiSymbol' => 'M',
        ],
        'giga'   => [
            'multiplier'  => 1e9,
            'prefixGroup' => self::GROUP_LARGE_METRIC,
            'asciiSymbol' => 'G',
        ],
        'tera'   => [
            'multiplier'  => 1e12,
            'prefixGroup' => self::GROUP_LARGE_METRIC,
            'asciiSymbol' => 'T',
        ],
        'peta'   => [
            'multiplier'  => 1e15,
            'prefixGroup' => self::GROUP_LARGE_METRIC,
            'asciiSymbol' => 'P',
        ],
        'exa'    => [
            'multiplier'  => 1e18,
            'prefixGroup' => self::GROUP_LARGE_METRIC,
            'asciiSymbol' => 'E',
        ],
        'zetta'  => [
            'multiplier'  => 1e21,
            'prefixGroup' => self::GROUP_LARGE_METRIC,
            'asciiSymbol' => 'Z',
        ],
        'yotta'  => [
            'multiplier'  => 1e24,
            'prefixGroup' => self::GROUP_LARGE_METRIC,
            'asciiSymbol' => 'Y',
        ],
        'ronna'  => [
            'multiplier'  => 1e27,
            'prefixGroup' => self::GROUP_LARGE_METRIC,
            'asciiSymbol' => 'R',
        ],
        'quetta' => [
            'multiplier'  => 1e30,
            'prefixGroup' => self::GROUP_LARGE_METRIC,
            'asciiSymbol' => 'Q',
        ],
        'kibi'   => [
            'multiplier'  => 2 ** 10,
            'prefixGroup' => self::GROUP_BINARY,
            'asciiSymbol' => 'Ki',
        ],
        'mebi'   => [
            'multiplier'  => 2 ** 20,
            'prefixGroup' => self::GROUP_BINARY,
            'asciiSymbol' => 'Mi',
        ],
        'gibi'   => [
            'multiplier'  => 2 ** 30,
            'prefixGroup' => self::GROUP_BINARY,
            'asciiSymbol' => 'Gi',
        ],
        'tebi'   => [
            'multiplier'  => 2 ** 40,
            'prefixGroup' => self::GROUP_BINARY,
            'asciiSymbol' => 'Ti',
        ],
        'pebi'   => [
            'multiplier'  => 2 ** 50,
            'prefixGroup' => self::GROUP_BINARY,
            'asciiSymbol' => 'Pi',
        ],
        'exbi'   => [
            'multiplier'  => 2 ** 60,
            'prefixGroup' => self::GROUP_BINARY,
            'asciiSymbol' => 'Ei',
        ],
        'zebi'   => [
            'multiplier'  => 2 ** 70,
            'prefixGroup' => self::GROUP_BINARY,
            'asciiSymbol' => 'Zi',
        ],
        'yobi'   => [
            'multiplier'  => 2 ** 80,
            'prefixGroup' => self::GROUP_BINARY,
            'asciiSymbol' => 'Yi',
        ],
        'robi'   => [
            'multiplier'  => 2 ** 90,
            'prefixGroup' => self::GROUP_BINARY,
            'asciiSymbol' => 'Ri',
        ],
        'quebi'  => [
            'multiplier'  => 2 ** 100,
            'prefixGroup' => self::GROUP_BINARY,
            'asciiSymbol' => 'Qi',
        ],
    ];

    // endregion

    // region Private static properties

    /**
     * List of all prefixes.
     *
     * @var list<Prefix>
     */
    private static array $prefixes = [];

    // endregion

    // region Lookup methods

    /**
     * Return an array of prefixes given an integer group code comprising bitwise flags.
     *
     * @param int $prefixGroup Code indicating the prefix group(s) to include.
     * @return list<Prefix>
     */
    public static function getPrefixes(int $prefixGroup = self::GROUP_ALL): array
    {
        self::init();

        // No prefixes.
        if ($prefixGroup === 0) {
            return [];
        }

        // Get the prefixes for the given group code.
        return array_values(
            array_filter(self::$prefixes, static fn (Prefix $prefix) => (bool)($prefix->groupCode & $prefixGroup))
        );
    }

    /**
     * Get a prefix by its symbol.
     *
     * Supports ASCII, Unicode, and alternate symbols.
     *
     * @param string $symbol The prefix symbol to search for.
     * @return ?Prefix The matching prefix, or null if not found.
     */
    public static function getBySymbol(string $symbol): ?Prefix
    {
        self::init();

        return array_find(
            self::$prefixes,
            static fn (Prefix $prefix) => $prefix->asciiSymbol === $symbol
                || $prefix->unicodeSymbol === $symbol
                || $prefix->alternateSymbol === $symbol
        );
    }

    // endregion

    // region Registry methods

    /**
     * Clear the prefixes cache.
     *
     * The cache will be re-initialized lazily on the next access.
     */
    public static function removeAll(): void
    {
        self::$prefixes = [];
    }

    // endregion

    // region Transformation methods

    /**
     * Invert a prefix.
     *
     * Returns the prefix with the opposite exponent, e.g. 'k' (10³) → 'm' (10⁻³).
     *
     * @param ?Prefix $prefix The prefix to invert. Can be null.
     * @return ?Prefix The inverted prefix, or null if null was passed.
     * @throws DomainException If no inverse could be found for the given prefix.
     */
    public static function invert(?Prefix $prefix): ?Prefix
    {
        self::init();

        // Handle the null case.
        if ($prefix === null) {
            return null;
        }

        // Find the reciprocal multiplier and matching prefix.
        $inversePrefix = array_find(
            self::$prefixes,
            static fn (Prefix $p) => Floats::approxEqual($p->multiplier, 1.0 / $prefix->multiplier)
        );

        // Throw an exception if no inverse could be found.
        if ($inversePrefix === null) {
            throw new DomainException("Inverse of prefix '$prefix' not found.");
        }

        return $inversePrefix;
    }

    // endregion

    // region Helper methods

    /**
     * Initialize the prefixes array from the prefix definitions.
     *
     * This is called lazily on first access.
     *
     * @throws FormatException If any symbols are invalid.
     * @throws DomainException If any multipliers are invalid.
     */
    private static function init(): void
    {
        if (self::$prefixes === []) {
            // Keep track of seen symbols.
            $seen = [];

            // Create the prefix objects from the definitions and add to the array.
            foreach (self::PREFIX_DEFINITIONS as $name => $definition) {
                // Check for duplicates.
                foreach (['asciiSymbol', 'unicodeSymbol', 'alternateSymbol'] as $key) {
                    $sym = $definition[$key] ?? null;
                    if ($sym === null) {
                        continue;
                    }
                    if (isset($seen[$sym])) {
                        // @codeCoverageIgnoreStart
                        throw new DomainException("Duplicate prefix symbol: '$sym'.");
                        // @codeCoverageIgnoreEnd
                    }
                    $seen[$sym] = true;
                }

                // Construct the new prefix and add it to the cache.
                self::$prefixes[] = new Prefix(
                    $name,
                    $definition['multiplier'],
                    $definition['prefixGroup'],
                    $definition['asciiSymbol'],
                    $definition['unicodeSymbol'] ?? null,
                    $definition['alternateSymbol'] ?? null
                );
            }
        }
    }

    // endregion
}
