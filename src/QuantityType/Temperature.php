<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Override;
use ValueError;

class Temperature extends Quantity
{
    // region Factory methods

    /**
     * Parse a temperature string.
     *
     * Accepts standard formats like "25C", "98.6F", "273.15K"
     * or with degree symbols like "25°C", "98.6°F".
     *
     * @param string $value The string to parse.
     * @return static A new Temperature instance.
     * @throws ValueError If the string is not a valid temperature format.
     */
    #[Override]
    public static function parse(string $value): static
    {
        try {
            // Try to parse using Quantity::parse().
            return parent::parse($value);
        } catch (ValueError $e) {
            // Check for Celsius or Fahrenheit with a degree symbol, e.g. "25°C" or "98.6°F".
            $rxNum = '[-+]?(?:\d+(?:\.\d+)?|\.\d+)(?:[eE][+-]?\d+)?';
            $pattern = "/^($rxNum)\s*°([CF])$/";

            if (preg_match($pattern, $value, $matches)) {
                return new static((float)$matches[1], $matches[2]);
            }

            // Invalid format.
            throw $e;
        }
    }

    // endregion

    // region Formatting methods

//    /**
//     * Format the unit.
//     *
//     * @param string $unit
//     * @return string
//     */
//    public static function formatUnit(string $unit): string
//    {
//        // Add the degree symbol for Celsius and Fahrenheit units.
//        if ($unit === 'C' || $unit === 'F') {
//            return '°' . $unit;
//        }
//
//        // Otherwise, use the parent method.
//        return parent::formatUnit($unit);
//    }

    // endregion

    // region Static getters

    /**
     * Get the dimension code for this quantity type. This method must be overridden in derived classes.
     *
     * @return ?string
     */
    #[Override]
    public static function getDimensionCode(): ?string
    {
        return 'H';
    }

    // endregion
}
