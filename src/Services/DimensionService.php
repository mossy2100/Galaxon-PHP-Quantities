<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Services;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Quantities\Internal\DerivedUnit;
use Galaxon\Quantities\Internal\UnitTerm;

/**
 * Utility class for working with physical dimension codes.
 *
 * Dimension codes represent the fundamental physical dimensions of a quantity (mass, length, time, etc.) using
 * single-letter codes with optional exponents. For example, 'MLT-2' represents mass × length × time⁻² (force).
 *
 * @see https://en.wikipedia.org/wiki/International_System_of_Quantities
 * @see https://en.wikipedia.org/wiki/Dimensional_analysis
 */
class DimensionService
{
    // region Constants

    /**
     * Dimension codes are based on ISQ (International System of Quantities) dimensional symbols, with a few
     * variations and additions.
     *
     * The order of the codes determines the canonical ordering of terms within a dimension code, necessary for
     * comparing dimensions.
     *
     * It also determines the ordering of unit terms in an SI derived unit. This is based on the most common ordering of
     * base unit terms when writing SI units.
     *
     * The dimension 'A' is not part of the ISQ, being considered a ratio of lengths, but we need it for this system.
     *
     * The dimension 'C' is reserved for a future currency extension. 'XAU' (which means gold troy ounces) is used as
     * the base unit, being the least biased and most widely accepted currency.
     *
     * The ISQ uses 'Θ' (Greek capital theta) for temperature. 'H' is used here because ASCII characters are easier to
     * type. 'H' is chosen because capital theta has a horizontal bar, like 'H', plus 'H' suggests heat.
     *
     * NB: The SI base units shown here are the 7 actual SI base units, plus 3 bonus ones we need for the system:
     * - rad (angle)
     * - B (data)
     * - XAU (currency)
     *
     * @see https://en.wikipedia.org/wiki/International_System_of_Quantities
     * @see https://en.wikipedia.org/wiki/Dimensional_analysis
     *
     * @var array<string, array{name: string, siBaseUnitSymbol: string, englishBaseUnitSymbol?: string}>
     */
    public const array DIMENSION_CODES = [
        'M' => [
            'name'                  => 'mass',
            'siBaseUnitSymbol'      => 'kg',
            'englishBaseUnitSymbol' => 'lb',
        ],
        'L' => [
            'name'                  => 'length',
            'siBaseUnitSymbol'      => 'm',
            'englishBaseUnitSymbol' => 'ft',
        ],
        'A' => [
            'name'                  => 'angle',
            'siBaseUnitSymbol'      => 'rad',
            'englishBaseUnitSymbol' => 'deg',
        ],
        'D' => [
            'name'             => 'data',
            'siBaseUnitSymbol' => 'B',
        ],
        'C' => [
            'name'             => 'currency',
            'siBaseUnitSymbol' => 'XAU',
        ],
        'T' => [
            'name'             => 'time',
            'siBaseUnitSymbol' => 's',
        ],
        'I' => [
            'name'             => 'electric current',
            'siBaseUnitSymbol' => 'A',
        ],
        'N' => [
            'name'             => 'amount of substance',
            'siBaseUnitSymbol' => 'mol',
        ],
        'H' => [
            'name'                  => 'temperature',
            'siBaseUnitSymbol'      => 'K',
            'englishBaseUnitSymbol' => 'degR',
        ],
        'J' => [
            'name'             => 'luminous intensity',
            'siBaseUnitSymbol' => 'cd',
        ],
    ];

    // endregion

    // region Validation methods

    /**
     * Get the valid dimension code letters for use in regex patterns.
     *
     * @return list<string> The valid dimension code letters.
     */
    private static function getLetterCodes(): array
    {
        return array_keys(self::DIMENSION_CODES);
    }

    /**
     * Get the valid dimension code letters as a string for use in regex patterns.
     *
     * @return string The valid dimension code letters concatenated (e.g. 'MLADCTINHJ').
     */
    private static function getLetterCodesString(): string
    {
        return implode('', self::getLetterCodes());
    }

    /**
     * Check if a dimension code string is valid.
     *
     * @param string $dimension The dimension code to validate (e.g. 'L', 'MLT-2', '' for dimensionless).
     * @return bool True if valid, false otherwise.
     */
    public static function isValid(string $dimension): bool
    {
        $validCodes = self::getLetterCodesString();
        return (bool)preg_match("/^([$validCodes](-?\d)?)*$/", $dimension);
    }

    // endregion

    // region Decompose/compose methods

    /**
     * Decompose a dimension code string into an array of dimension codes and exponents.
     *
     * @param string $dimension The dimension code (e.g. 'MLT-2').
     * @return array<string, int> Array mapping dimension codes to their exponents.
     * @throws FormatException If the dimension code is invalid.
     */
    public static function decompose(string $dimension): array
    {
        // Check the dimension code is valid.
        if (!self::isValid($dimension)) {
            throw new FormatException("Invalid dimension code '$dimension'.");
        }

        // Get the matching terms.
        $validCodes = self::getLetterCodesString();
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
     * Compose an array of dimension terms into a dimension code string.
     *
     * The terms are automatically sorted into canonical order.
     *
     * @param array<string, int> $dimTerms Array mapping dimension codes to exponents.
     * @return string The combined dimension code (e.g. 'MLT-2').
     */
    public static function compose(array $dimTerms): string
    {
        // Sort the terms.
        $letters = array_flip(self::getLetterCodes());
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
     * @throws FormatException If the dimension code is invalid.
     */
    public static function normalize(string $dimension): string
    {
        // Disassemble it.
        $dimTerms = self::decompose($dimension);

        // Reassemble it.
        return self::compose($dimTerms);
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

        // Multiply each dimension term's current exponent by the new exponent.
        $dimTerms = self::decompose($dimension);
        foreach ($dimTerms as $dim => $curExp) {
            $dimTerms[$dim] = $curExp * $exponent;
        }

        // Reassemble it.
        return self::compose($dimTerms);
    }

    // endregion

    // region Utility methods

    /**
     * Convert a dimension code letter into an int [0..9].
     *
     * @param string $letter The dimension letter code.
     * @return int The int value.
     * @throws DomainException If the letter is invalid.
     */
    public static function letterToInt(string $letter): int
    {
        // Convert the letter to a position in the array.
        $x = array_search($letter, self::getLetterCodes(), true);

        // If not found, throw an exception.
        if ($x === false) {
            throw new DomainException("Invalid dimension code letter '$letter'.");
        }

        return $x;
    }

    /**
     * Get the SI base unit symbols for all dimensions.
     *
     * NB: We're stretching the definition of an SI base unit for this system to include a few additional units.
     * @see DIMENSION_CODES
     *
     * @return list<string>
     */
    public static function getSiBaseUnitSymbols(): array
    {
        return array_column(self::DIMENSION_CODES, 'siBaseUnitSymbol');
    }

    /**
     * Get the SI or English base unit term symbol for the given dimension letter code.
     *
     * The unit may be prefixed (e.g. 'kg' for 'M').
     *
     * @param string $dimensionLetterCode Single-letter dimension code.
     * @param bool $si Whether to return the SI base unit symbol (true) or the English base unit symbol (false).
     * @return string The unit term symbol.
     * @throws DomainException If the dimension code is invalid.
     */
    public static function getBaseUnitSymbol(string $dimensionLetterCode, bool $si): string
    {
        // Validate the code.
        if (strlen($dimensionLetterCode) !== 1 || !array_key_exists($dimensionLetterCode, self::DIMENSION_CODES)) {
            throw new DomainException("Invalid dimension code letter: '$dimensionLetterCode'.");
        }

        // If not SI and has an English base unit, return it.
        if (!$si && isset(self::DIMENSION_CODES[$dimensionLetterCode]['englishBaseUnitSymbol'])) {
            return self::DIMENSION_CODES[$dimensionLetterCode]['englishBaseUnitSymbol'];
        }

        // Return the SI base unit (which may have a prefix, e.g. 'kg').
        return self::DIMENSION_CODES[$dimensionLetterCode]['siBaseUnitSymbol'];
    }

    /**
     * Get the SI or English base unit term for a given dimension code letter.
     *
     * @param string $dimensionLetterCode Single-letter dimension code.
     * @param bool $si Whether to return the SI base unit term (true) or the English base unit term (false).
     * @return UnitTerm The unit term.
     * @throws DomainException If the dimension code is invalid.
     */
    public static function getBaseUnitTerm(string $dimensionLetterCode, bool $si): UnitTerm
    {
        // Get the base unit symbol (e.g. 'kg', 'lb').
        $baseUnit = self::getBaseUnitSymbol($dimensionLetterCode, $si);

        // Construct the UnitTerm.
        return UnitTerm::parse($baseUnit);
    }

    /**
     * Convert a dimension to a DerivedUnit in SI or English base units.
     *
     * @param string $dimension The dimension code (e.g. 'MLT-2').
     * @param bool $si Whether to return the SI derived unit (true) or the English derived unit (false).
     * @return DerivedUnit The new DerivedUnit.
     * @throws DomainException If the dimension code is invalid.
     */
    public static function getBaseDerivedUnit(string $dimension, bool $si): DerivedUnit
    {
        $unitTerms = [];
        $dimTerms = self::decompose($dimension);
        foreach ($dimTerms as $code => $exp) {
            $unitTerms[] = self::getBaseUnitTerm($code, $si)->pow($exp);
        }
        return new DerivedUnit($unitTerms);
    }

    // endregion
}
