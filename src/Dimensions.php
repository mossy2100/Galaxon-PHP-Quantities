<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use DomainException;
use LogicException;

/**
 * Utility class for working with physical dimension codes.
 *
 * Dimension codes represent the fundamental physical dimensions of a quantity (mass, length, time, etc.) using
 * single-letter codes with optional exponents. For example, 'MLT-2' represents mass × length × time⁻² (force).
 *
 * @see https://en.wikipedia.org/wiki/International_System_of_Quantities
 * @see https://en.wikipedia.org/wiki/Dimensional_analysis
 */
class Dimensions
{
    // region Constants

    /**
     * Dimension codes are based on ISQ (International System of Quantities) dimensional symbols, with a few
     * variations and additions.
     *
     * The order of the codes determines the canonical ordering of terms within a dimension code, which is necessary for
     * comparing dimensions.
     *
     * It also determines the ordering of unit terms in an SI derived unit. Thus, the order is based on the common style
     * of writing SI units.
     *
     * @see https://en.wikipedia.org/wiki/International_System_of_Quantities
     * @see https://en.wikipedia.org/wiki/Dimensional_analysis
     */
    public const array DIMENSION_CODES = [
        'M' => [
            'name'     => 'mass',
            'siBase'   => 'g',
            'siPrefix' => 'k',
        ],
        'L' => [
            'name'   => 'length',
            'siBase' => 'm',
        ],
        // The dimension 'A' is not part of the ISQ, being considered a ratio of lengths.
        'A' => [
            'name'   => 'angle',
            'siBase' => 'rad',
        ],
        'D' => [
            'name'   => 'data',
            'siBase' => 'B',
        ],
        // The dimension 'C' is reserved for a future currency extension.
        'C' => [
            'name'   => 'currency',
            'siBase' => 'XAU',
        ],
        'T' => [
            'name'   => 'time',
            'siBase' => 's',
        ],
        'I' => [
            'name'   => 'electric current',
            'siBase' => 'A',
        ],
        // 'H' is used in place of 'Θ' (Greek capital theta), as used in the International System of Quantities.
        // This is done purely because ASCII characters are easier to type.
        // Capital theta has a horizontal bar, like 'H', and 'H' indicates heat.
        'H' => [
            'name'   => 'temperature',
            'siBase' => 'K',
        ],
        'N' => [
            'name'   => 'amount of substance',
            'siBase' => 'mol',
        ],
        'J' => [
            'name'   => 'luminous intensity',
            'siBase' => 'cd',
        ],
    ];

    // endregion

    // region Validation methods

    /**
     * Get the valid dimension code letters for use in regex patterns.
     *
     * @return list<string> The valid dimension code letters.
     */
    private static function getCodeLetters(): array
    {
        return array_keys(self::DIMENSION_CODES);
    }

    /**
     * Get the valid dimension code letters as a string for use in regex patterns.
     *
     * @return string The valid dimension code letters concatenated (e.g. 'MLADCTINJ').
     */
    private static function getCodesString(): string
    {
        return implode('', self::getCodeLetters());
    }

    /**
     * Check if a dimension code string is valid.
     *
     * @param string $dimension The dimension code to validate (e.g. 'L', 'MLT-2').
     * @return bool True if valid, false otherwise.
     */
    public static function isValid(string $dimension): bool
    {
        $validCodes = self::getCodesString();
        return (bool)preg_match("/^([$validCodes](-?\d)?)+$/", $dimension);
    }

    // endregion

    // region Explode/implode methods

    /**
     * Explode a dimension code string into an array of dimension codes and exponents.
     *
     * @param string $dimension The dimension code (e.g. 'MLT-2').
     * @return array<string, int> Array mapping dimension codes to their exponents.
     * @throws DomainException If the dimension code is invalid.
     */
    public static function explode(string $dimension): array
    {
        // Check the dimension code is valid.
        if (!self::isValid($dimension)) {
            throw new DomainException("Invalid dimension code '$dimension'.");
        }

        // Get the matching terms.
        $validCodes = self::getCodesString();
        preg_match_all("/([$validCodes])(-?\d)?/", $dimension, $matches, PREG_SET_ORDER);

        // Convert to an array of parts.
        $dimTerms = [];
        foreach ($matches as $match) {
            $dim = $match[1];
            $exp = (!isset($match[2]) || $match[2] === '') ? 1 : (int)$match[2];
            $dimTerms[$dim] = $exp;
        }

        return $dimTerms;
    }

    /**
     * Implode an array of dimension terms into a dimension code string.
     *
     * The terms are automatically sorted into canonical order.
     *
     * @param array<string, int> $dimTerms Array mapping dimension codes to exponents.
     * @return string The combined dimension code (e.g. 'MLT-2').
     */
    public static function implode(array $dimTerms): string
    {
        // Sort the terms.
        $letters = array_flip(self::getCodeLetters());
        $fn = static fn (string $code1, string $code2) => $letters[$code1] <=> $letters[$code2];
        uksort($dimTerms, $fn);

        // Assemble into a string.
        $result = '';
        foreach ($dimTerms as $dim => $exp) {
            $result .= $dim . ($exp === 1 ? '' : $exp);
        }
        return $result;
    }

    // endregion

    // region Transformation methods

    /**
     * Normalize a dimension code string.
     *
     * @param string $dimension The dimension code string to normalize.
     * @return string The normalized dimension code.
     * @throws DomainException If the dimension code is invalid.
     */
    public static function normalize(string $dimension): string
    {
        // Disassemble it.
        $dimTerms = self::explode($dimension);

        // Reassemble it.
        return self::implode($dimTerms);
    }

    /**
     * Apply an exponent to a dimension code.
     *
     * For example:
     * - ('L', 3) => 'L3'
     * - ('T-1', 2) => 'T-2'
     * - ('MLT-2', 2) => 'M2L2T-4'
     *
     * @param string $dimension The base dimension code (e.g. 'L', 'T-1', 'MLT-2').
     * @param int $exponent The exponent to apply.
     * @return string The resulting dimension code.
     * @throws DomainException If the dimension code is invalid.
     */
    public static function applyExponent(string $dimension, int $exponent): string
    {
        // If the exponent is 1, return dimension unchanged.
        if ($exponent === 1) {
            return $dimension;
        }

        // Multiply each dimension term by the exponent.
        $dimTerms = self::explode($dimension);
        foreach ($dimTerms as $dim => $curExp) {
            $dimTerms[$dim] = $curExp * $exponent;
        }

        // Reassemble it.
        return self::implode($dimTerms);
    }

    // endregion

    // region Utility methods

    /**
     * Convert a dimension code letter into an int [0..9].
     *
     * @param string $letter The dimension letter code.
     * @return int|null The int value, or null if not found.
     */
    public static function letterToInt(string $letter): ?int
    {
        // Convert the letter to a position in the array.
        $x = array_search($letter, self::getCodeLetters(), true);

        // If the letter isn't a valid dimension code, return null.
        if ($x === false) {
            return null;
        }

        return $x;
    }

    /**
     * Get the SI base unit (or most suitable) symbol for the given dimension code.
     *
     * The unit may be prefixed.
     *
     * @param string $code Single-letter dimension code.
     * @return UnitTerm The unit term.
     * @throws DomainException If the dimension code is invalid.
     */
    public static function getSiUnitTerm(string $code): UnitTerm
    {
        // Validate the code.
        if (strlen($code) !== 1 || !array_key_exists($code, self::DIMENSION_CODES)) {
            throw new DomainException("Invalid dimension code '$code'.");
        }

        // Get the SI base unit and prefix.
        $siBase = self::DIMENSION_CODES[$code]['siBase'] ?? null;
        $siPrefix = self::DIMENSION_CODES[$code]['siPrefix'] ?? null;

        // Make sure we got the SI base unit.
        if ($siBase === null) {
            throw new LogicException("No SI base unit defined for dimension code '$code'.");
        }

        // Construct the UnitTerm.
        return new UnitTerm($siBase, $siPrefix);
    }

    // endregion
}
