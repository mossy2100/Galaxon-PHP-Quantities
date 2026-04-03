<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Services;

use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Core\Numbers;
use Galaxon\Quantities\Internal\DerivedUnit;
use Galaxon\Quantities\Internal\UnitTerm;
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
     * @var array<string, array{
     *     quantityTypeName: string,
     *     siBaseUnitSymbol?: string,
     *     englishBaseUnitSymbol?: string,
     *     commonBaseUnitSymbol?: string,
     * }>
     */
    public const array DIMENSION_CODES = [
        'M' => [
            'quantityTypeName'      => 'mass',
            'siBaseUnitSymbol'      => 'kg',
            'englishBaseUnitSymbol' => 'lb',
        ],
        'L' => [
            'quantityTypeName'      => 'length',
            'siBaseUnitSymbol'      => 'm',
            'englishBaseUnitSymbol' => 'ft',
        ],
        'A' => [
            'quantityTypeName'      => 'angle',
            'siBaseUnitSymbol'      => 'rad',
            'englishBaseUnitSymbol' => 'deg',
        ],
        'D' => [
            'quantityTypeName'     => 'data',
            'commonBaseUnitSymbol' => 'B',
        ],
        'C' => [
            'quantityTypeName'     => 'money',
            'commonBaseUnitSymbol' => 'XAU',
        ],
        'T' => [
            'quantityTypeName' => 'time',
            'siBaseUnitSymbol' => 's',
        ],
        'I' => [
            'quantityTypeName' => 'electric current',
            'siBaseUnitSymbol' => 'A',
        ],
        'N' => [
            'quantityTypeName' => 'amount of substance',
            'siBaseUnitSymbol' => 'mol',
        ],
        'H' => [
            'quantityTypeName'      => 'temperature',
            'siBaseUnitSymbol'      => 'K',
            'englishBaseUnitSymbol' => 'degR',
        ],
        'J' => [
            'quantityTypeName' => 'luminous intensity',
            'siBaseUnitSymbol' => 'cd',
        ],
    ];

    // endregion

    // region Validation methods

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

    // region Composition methods

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

    // region Comparison methods

    /**
     * Check if dimension1 is a subset of dimension2.
     *
     * Returns true if every dimension term in dimension1 exists in dimension2 with the same sign and an equal or
     * smaller absolute exponent. This is used by simplify() to determine whether a unit's dimension fits inside a
     * quantity's dimension.
     *
     * @param string $dimension1 The candidate subset dimension (e.g. 'MLT-2').
     * @param string $dimension2 The containing dimension (e.g. 'ML2T-2').
     * @return bool True if dimension1 is a subset of dimension2.
     * @throws FormatException If either of the dimension codes are invalid.
     */
    public static function lessThanOrEqual(string $dimension1, string $dimension2): bool
    {
        $dimTerms1 = self::decompose($dimension1);
        $dimTerms2 = self::decompose($dimension2);

        foreach ($dimTerms1 as $code => $exp) {
            if (!isset($dimTerms2[$code])) {
                return false;
            }
            if (Numbers::sign($exp) !== Numbers::sign($dimTerms2[$code])) {
                return false;
            }
            if (abs($dimTerms2[$code]) < abs($exp)) {
                return false;
            }
        }

        return true;
    }

    // endregion

    // region Binary arithmetic methods

    /**
     * Subtract dimension2 from dimension1.
     *
     * Subtracts each exponent in dimension2 from the corresponding exponent in dimension1.
     * Terms that cancel to zero are removed. Terms in dimension2 that are not in dimension1 are ignored.
     *
     * @param string $dimension1 The dimension to subtract from (e.g. 'ML2T-2').
     * @param string $dimension2 The dimension to subtract (e.g. 'MLT-2').
     * @return string The resulting dimension code (e.g. 'L').
     * @throws FormatException If either of the dimension codes are invalid.
     */
    public static function sub(string $dimension1, string $dimension2): string
    {
        $dimTerms1 = self::decompose($dimension1);
        $dimTerms2 = self::decompose($dimension2);
        $dimTerms3 = [];

        foreach ($dimTerms1 as $code => $exp) {
            $newExp = $exp - ($dimTerms2[$code] ?? 0);
            if ($newExp !== 0) {
                $dimTerms3[$code] = $newExp;
            }
        }

        return self::compose($dimTerms3);
    }

    // endregion

    // region Power methods

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
     * @throws FormatException If the dimension code is invalid.
     */
    public static function pow(string $dimension, int $exponent): string
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

    // endregion

    // region Utility methods

    /**
     * Convert a dimension code letter into an int [0..9].
     *
     * @param string $letter The dimension letter code.
     * @return int The int value.
     * @throws FormatException If the letter is invalid.
     */
    public static function letterToInt(string $letter): int
    {
        // Convert the letter to a position in the array.
        $x = array_search($letter, self::getLetterCodes(), true);

        // If not found, throw an exception.
        if ($x === false) {
            throw new FormatException("Invalid dimension code letter: '$letter'.");
        }

        return $x;
    }

    /**
     * Count the total number of base unit slots in a dimension code.
     *
     * Each dimension term contributes the absolute value of its exponent. For example, 'MLT-2' has
     * M (1) + L (1) + T (2) = 4 unit slots.
     *
     * @param string $dimension The dimension code (e.g. 'MLT-2').
     * @return int The total unit count.
     * @throws FormatException If the dimension code is invalid.
     */
    public static function countUnits(string $dimension): int
    {
        $dimTerms = self::decompose($dimension);
        return array_reduce($dimTerms, static fn (int $count, int $exp) => $count + abs($exp), 0);
    }

    // endregion

    // region Base unit methods

    /**
     * Get the SI or English base unit term symbol for the given dimension letter code.
     *
     * The unit may be prefixed (e.g. 'kg' for 'M').
     *
     * @param string $dimensionLetterCode Single-letter dimension code.
     * @param bool $si Whether to return the SI base unit symbol (true) or the English base unit symbol (false).
     * @return string The unit term symbol.
     * @throws FormatException If the dimension code is invalid.
     * @throws LogicException If no base unit is defined for the dimension.
     */
    public static function getBaseUnitSymbol(string $dimensionLetterCode, bool $si): string
    {
        // Validate the code.
        if (strlen($dimensionLetterCode) !== 1 || !array_key_exists($dimensionLetterCode, self::DIMENSION_CODES)) {
            throw new FormatException("Invalid dimension code letter: '$dimensionLetterCode'.");
        }

        // If not SI and has an English base unit, return it.
        if (!$si && isset(self::DIMENSION_CODES[$dimensionLetterCode]['englishBaseUnitSymbol'])) {
            return self::DIMENSION_CODES[$dimensionLetterCode]['englishBaseUnitSymbol'];
        }

        // Check for an SI base unit.
        if (isset(self::DIMENSION_CODES[$dimensionLetterCode]['siBaseUnitSymbol'])) {
            return self::DIMENSION_CODES[$dimensionLetterCode]['siBaseUnitSymbol'];
        }

        // Check for a common base unit.
        // @phpstan-ignore isset.offset
        if (isset(self::DIMENSION_CODES[$dimensionLetterCode]['commonBaseUnitSymbol'])) {
            return self::DIMENSION_CODES[$dimensionLetterCode]['commonBaseUnitSymbol'];
        }

        // @codeCoverageIgnoreStart
        // @phpstan-ignore deadCode.unreachable
        $system = $si ? 'SI' : 'English';
        throw new LogicException("No $system base unit is defined for dimension '$dimensionLetterCode'.");
        // @codeCoverageIgnoreEnd
    }

    /**
     * Get the SI or English base unit term for a given dimension code letter.
     *
     * @param string $dimensionLetterCode Single-letter dimension code.
     * @param bool $si Whether to return the SI base unit term (true) or the English base unit term (false).
     * @return UnitTerm The unit term.
     * @throws FormatException If the dimension code is invalid.
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
     * @throws FormatException If the dimension code is invalid.
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

    // region Helper methods

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

    // endregion
}
