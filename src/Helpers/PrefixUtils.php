<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Helpers;

use DomainException;
use Galaxon\Core\Floats;
use Galaxon\Quantities\Prefix;

/**
 * Registry for SI and binary prefixes.
 *
 * Provides access to metric prefixes (milli, kilo, mega, etc.) and binary prefixes (kibi, mebi, etc.)
 * organized by group codes for flexible filtering.
 */
class PrefixUtils
{
    // region Prefix group constants

    public const int GROUP_CODE_SMALL_ENGINEERING_METRIC = 1;
    public const int GROUP_CODE_SMALL_NON_ENGINEERING_METRIC = 2;
    public const int GROUP_CODE_LARGE_NON_ENGINEERING_METRIC = 4;
    public const int GROUP_CODE_LARGE_ENGINEERING_METRIC = 8;
    public const int GROUP_CODE_BINARY = 16;

    public const int GROUP_CODE_SMALL_METRIC =
        self::GROUP_CODE_SMALL_ENGINEERING_METRIC |
        self::GROUP_CODE_SMALL_NON_ENGINEERING_METRIC;

    public const int GROUP_CODE_LARGE_METRIC =
        self::GROUP_CODE_LARGE_NON_ENGINEERING_METRIC |
        self::GROUP_CODE_LARGE_ENGINEERING_METRIC;

    public const int GROUP_CODE_ENGINEERING_METRIC =
        self::GROUP_CODE_SMALL_ENGINEERING_METRIC |
        self::GROUP_CODE_LARGE_ENGINEERING_METRIC;

    public const int GROUP_CODE_METRIC = self::GROUP_CODE_SMALL_METRIC | self::GROUP_CODE_LARGE_METRIC;

    public const int GROUP_CODE_LARGE = self::GROUP_CODE_LARGE_ENGINEERING_METRIC | self::GROUP_CODE_BINARY;

    public const int GROUP_CODE_ALL = self::GROUP_CODE_METRIC | self::GROUP_CODE_BINARY;

    // endregion

    // region Static properties

    /**
     * List of all prefixes, ordered by multiplier.
     *
     * @var ?list<Prefix>
     */
    private static ?array $prefixes = null;

    // endregion

    // region Static public methods

    /**
     * Return an array of prefixes given an integer group code comprising bitwise flags.
     *
     * @param int $prefixGroup Code indicating the prefix group(s) to include.
     * @return list<Prefix>
     */
    public static function getPrefixes(int $prefixGroup = self::GROUP_CODE_ALL): array
    {
        self::init();
        assert(self::$prefixes !== null);

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
     * Supports both ASCII and Unicode symbols.
     *
     * @param string $symbol The prefix symbol to search for.
     * @return ?Prefix The matching prefix, or null if not found.
     */
    public static function getBySymbol(string $symbol): ?Prefix
    {
        self::init();
        assert(self::$prefixes !== null);

        return array_find(
            self::$prefixes,
            static fn (Prefix $prefix) => $prefix->asciiSymbol === $symbol || $prefix->unicodeSymbol === $symbol
        );
    }

    /**
     * Invert a prefix.
     *
     * Returns the prefix with the opposite exponent, e.g. 'k' (10³) → 'm' (10⁻³).
     *
     * @param ?Prefix $prefix The prefix to invert.
     * @return ?Prefix The inverted prefix, or null if null was passed.
     * @throws DomainException If no inverse could be found for the given prefix.
     */
    public static function invert(?Prefix $prefix): ?Prefix
    {
        self::init();
        assert(self::$prefixes !== null);

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

    /**
     * Check if a group code is valid.
     *
     * Valid group codes are the base codes (1, 2, 4, 8, 16) that represent individual prefix groups.
     *
     * @param int $groupCode The group code to validate.
     * @return bool True if the group code is valid.
     */
    public static function isValidGroupCode(int $groupCode): bool
    {
        return in_array($groupCode, self::getValidGroupCodes(), true);
    }

    // endregion

    // region Helper methods

    /**
     * Get the prefix definitions.
     *
     * @return array<int, array<string, array{0: string, 1: float, 2?: string}>>
     */
    private static function getPrefixDefinitions(): array
    {
        return [
            self::GROUP_CODE_SMALL_ENGINEERING_METRIC     => [
                'quecto' => ['q', 1e-30],
                'ronto'  => ['r', 1e-27],
                'yocto'  => ['y', 1e-24],
                'zepto'  => ['z', 1e-21],
                'atto'   => ['a', 1e-18],
                'femto'  => ['f', 1e-15],
                'pico'   => ['p', 1e-12],
                'nano'   => ['n', 1e-9],
                'micro'  => ['u', 1e-6, 'μ'],
                'milli'  => ['m', 1e-3],
            ],
            self::GROUP_CODE_SMALL_NON_ENGINEERING_METRIC => [
                'centi' => ['c', 1e-2],
                'deci'  => ['d', 1e-1],
            ],
            self::GROUP_CODE_LARGE_NON_ENGINEERING_METRIC => [
                'deca'  => ['da', 1e1],
                'hecto' => ['h', 1e2],
            ],
            self::GROUP_CODE_LARGE_ENGINEERING_METRIC     => [
                'kilo'   => ['k', 1e3],
                'mega'   => ['M', 1e6],
                'giga'   => ['G', 1e9],
                'tera'   => ['T', 1e12],
                'peta'   => ['P', 1e15],
                'exa'    => ['E', 1e18],
                'zetta'  => ['Z', 1e21],
                'yotta'  => ['Y', 1e24],
                'ronna'  => ['R', 1e27],
                'quetta' => ['Q', 1e30],
            ],
            self::GROUP_CODE_BINARY                       => [
                'kibi'  => ['Ki', 2 ** 10],
                'mebi'  => ['Mi', 2 ** 20],
                'gibi'  => ['Gi', 2 ** 30],
                'tebi'  => ['Ti', 2 ** 40],
                'pebi'  => ['Pi', 2 ** 50],
                'exbi'  => ['Ei', 2 ** 60],
                'zebi'  => ['Zi', 2 ** 70],
                'yobi'  => ['Yi', 2 ** 80],
                'robi'  => ['Ri', 2 ** 90],
                'quebi' => ['Qi', 2 ** 100],
            ],
        ];
    }

    /**
     * Initialize the prefixes array from the prefix definitions.
     *
     * This is called lazily on first access.
     */
    private static function init(): void
    {
        if (self::$prefixes === null) {
            // Reset the prefixes array.
            self::$prefixes = [];

            // Get the prefix definitions.
            $prefixDefinitions = self::getPrefixDefinitions();

            // Create the prefix objects from the definitions, and add to the array.
            foreach ($prefixDefinitions as $groupCode => $groupDefinitions) {
                foreach ($groupDefinitions as $name => $definition) {
                    [$asciiSymbol, $multiplier] = $definition;
                    $unicodeSymbol = $definition[2] ?? null;
                    self::$prefixes[] = new Prefix($name, $asciiSymbol, $unicodeSymbol, $multiplier, $groupCode);
                }
            }

            // Sort the prefixes by multiplier.
            usort(self::$prefixes, static fn (Prefix $a, Prefix $b) => $a->multiplier <=> $b->multiplier);
        }
    }

    /**
     * Get the list of valid base group codes.
     *
     * @return list<int> The valid group codes.
     */
    private static function getValidGroupCodes(): array
    {
        return [
            self::GROUP_CODE_SMALL_ENGINEERING_METRIC,
            self::GROUP_CODE_SMALL_NON_ENGINEERING_METRIC,
            self::GROUP_CODE_LARGE_NON_ENGINEERING_METRIC,
            self::GROUP_CODE_LARGE_ENGINEERING_METRIC,
            self::GROUP_CODE_BINARY
        ];
    }

    // endregion
}
