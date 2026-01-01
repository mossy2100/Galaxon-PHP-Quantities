<?php

declare(strict_types = 1);

namespace Galaxon\Quantities;

use ValueError;

class Dimensions
{
    /**
     * Dimension codes are based on ISQ (International System of Quantities) dimensional symbols, with a few
     * variations and additions.
     *
     * @see https://en.wikipedia.org/wiki/International_System_of_Quantities
     * @see https://en.wikipedia.org/wiki/Dimensional_analysis
     */
    public const array DIMENSION_CODES = [
        'T' => 'time',
        'L' => 'length',
        'M' => 'mass',
        'I' => 'electric current',
        'H' => 'temperature', // varies from ISQ, which uses Θ
        'N' => 'amount of substance',
        'J' => 'luminous intensity',
        'A' => 'angle', // actually dimensionless
        'D' => 'data',
        'C' => 'currency', // reserved
    ];

    /**
     * Check if a dimension code is valid.
     *
     * @param string $dimension
     * @return bool
     */
    public static function isValid(string $dimension): bool
    {
        $validCodes = implode('', array_keys(self::DIMENSION_CODES));
        return (bool)preg_match("/^([$validCodes](-?\d)?)+$/", $dimension);
    }

    /**
     * Parse a dimension code string into an array of dimension codes and exponents.
     *
     * @param string $dimension The dimension code (e.g. 'MLT-2').
     * @return array<string, int> Array mapping dimension codes to their exponents.
     * @throws ValueError If the dimension code is invalid.
     */
    public static function parse(string $dimension): array
    {
        // Check the dimension code is valid.
        if (!self::isValid($dimension)) {
            throw new ValueError("Invalid dimension code '$dimension'.");
        }

        // Get the matching terms.
        $validCodes = implode('', array_keys(self::DIMENSION_CODES));
        preg_match_all("/([$validCodes])(-?\d)?/", $dimension, $matches, PREG_SET_ORDER);

        // Convert to an array of parts.
        $dimTerms = [];
        foreach ($matches as $match) {
            $dim = $match[1];
            $exp = empty($match[2]) ? 1 : (int)$match[2];
            $dimTerms[$dim] = $exp;
        }

        return $dimTerms;
    }

    /**
     * Parse a single dimension term (e.g. 'M' or 'L3' or 'T-2').
     *
     * @param string $dimTerm The dimension term to parse.
     * @return array|null Array containing the dimension term's code and exponent, or null if it's not a valid term.
     */
    public static function parseTerm(string $dimTerm): ?array
    {
        if (preg_match('/^([A-Z])(-?\d)$/', $dimTerm, $matches)) {
            return [
                'dimension' => $matches[1],
                'exponent'  => (int)$matches[2],
            ];
        }

        return null;
    }

    public static function sort(array $dimCodes): array
    {
        $isqCodes = array_flip(array_keys(self::DIMENSION_CODES));
        $fn = static fn (string $code1, string $code2) => $isqCodes[$code1] <=> $isqCodes[$code2];
        uksort($dimCodes, $fn);
        return $dimCodes;
    }

    public static function combine(array $dimTerms): string
    {
        $result = '';
        foreach ($dimTerms as $dim => $exp) {
            $result .= $dim . ($exp === 1 ? '' : $exp);
        }
        return $result;
    }

    /**
     * Normalize a dimension code string.
     *
     * @param string $dimension The dimension code string to normalize.
     * @return string The normalized dimension code.
     * @throws ValueError If the dimension code is invalid.
     */
    public static function normalize(string $dimension): string
    {
        // Disassemble it.
        $dimTerms = self::parse($dimension);

        // Sort the terms.
        $dimTerms = self::sort($dimTerms);

        // Reassemble it.
        return self::combine($dimTerms);
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
     * @throws ValueError If the dimension code is invalid.
     */
    public static function applyExponent(string $dimension, int $exponent): string
    {
        // If the exponent is 1, return dimension unchanged.
        if ($exponent === 1) {
            return $dimension;
        }

        // Multiply each dimension term by the exponent.
        $dimTerms = self::parse($dimension);
        foreach ($dimTerms as $dim => $curExp) {
            $dimTerms[$dim] = $curExp * $exponent;
        }

        // Reassemble it.
        return self::combine($dimTerms);
    }
}
