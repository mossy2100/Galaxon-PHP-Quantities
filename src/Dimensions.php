<?php

declare(strict_types = 1);

namespace Galaxon\Quantities;

use LogicException;
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
     * Parse a dimension code string into an array of dimension codes and exponents.
     *
     * @param string $dimensionCode The dimension code string (e.g., 'MLT-2').
     * @return array<string, int> Array mapping dimension codes to their exponents.
     * @throws LogicException If there's an error parsing the dimension.
     * @throws ValueError If the dimension code is invalid.
     */
    public static function parse(string $dimensionCode): array
    {
        // Get the matching terms.
        $validCodes = implode('', array_keys(self::DIMENSION_CODES));
        $nMatches = preg_match_all("/([$validCodes])(-?\d+)?/", $dimensionCode, $matches, PREG_SET_ORDER);

        // Check for errors.
        if ($nMatches === false) {
            throw new LogicException("Error parsing '$dimensionCode'.");
        }
        if ($nMatches === 0) {
            throw new ValueError("Invalid dimension code '$dimensionCode'.");
        }

        // Convert to an array of parts.
        $dimCodes = [];
        foreach ($matches as $match) {
            $dimCode = $match[1];
            $exp = empty($match[2]) ? 1 : (int)$match[2];
            $dimCodes[$dimCode] = $exp;
        }

        return $dimCodes;
    }

    public static function sort(array $dimCodes): array
    {
        $isqCodes = array_flip(array_keys(self::DIMENSION_CODES));
        $fn = static fn (string $code1, string $code2) => $isqCodes[$code1] <=> $isqCodes[$code2];
        uksort($dimCodes, $fn);
        return $dimCodes;
    }

    public static function combine(array $dimCodes): string
    {
        $result = '';
        foreach ($dimCodes as $dimCode => $exp) {
            $result .= $dimCode . ($exp === 1 ? '' : $exp);
        }
        return $result;
    }

    /**
     * Normalize a dimension code string.
     *
     * @param string $dimensionCode The dimension code string to normalize.
     * @return string The normalized dimension code.
     */
    public static function normalize(string $dimensionCode): string
    {
        // Disassemble it.
        $codes = self::parse($dimensionCode);

        // Sort the terms.
        $codes = self::sort($codes);

        // Reassemble it.
        return self::combine($codes);
    }
}
