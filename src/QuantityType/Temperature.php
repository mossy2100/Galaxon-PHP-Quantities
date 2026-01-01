<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Core\Arrays;
use Galaxon\Quantities\BaseUnit;
use Galaxon\Quantities\DerivedUnit;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\UnitData;
use Galaxon\Quantities\UnitTerm;
use Override;
use ValueError;

class Temperature extends Quantity
{
    // region Constants

    /**
     * Offset to convert Celsius to Kelvin.
     */
    public const float CELSIUS_OFFSET = 273.15;

    /**
     * Offset to convert Fahrenheit to Rankine.
     */
    public const float FAHRENHEIT_OFFSET = 459.67;

    /**
     * Factor to convert Kelvin to Rankine.
     */
    public const float RANKINE_PER_KELVIN = 1.8;

    /**
     * Conversion factors for temperature units.
     *
     * Note: Temperature conversions are special because Celsius and Fahrenheit have offsets.
     * The convert() method is overridden to handle these correctly.
     *
     * @return list<array{string, string, float}>
     */
    public static function getConversions(): array
    {
        return [
            ['degC', 'K', 1],
            ['degF', 'degR', 1],
            ['K', 'degR', 1.8],
        ];
    }

    // endregion

    /**
     * This override is necessary because Celsius and Fahrenheit are offset from absolute zero, but the conversion
     * engine used by the package only supports conversion by multipliying.
     *
     * @param float $value
     * @param string|BaseUnit|UnitTerm|DerivedUnit $srcUnit
     * @param string|BaseUnit|UnitTerm|DerivedUnit $destUnit
     * @return float
     * @throws ValueError
     */
    #[Override]
    public static function convert(
        float $value,
        string|BaseUnit|UnitTerm|DerivedUnit $srcUnit,
        string|BaseUnit|UnitTerm|DerivedUnit $destUnit
    ): float
    {
        $origSrcUnitSymbol = (string)$srcUnit;
        $origDestUnitSymbol = (string)$destUnit;

        // If the source unit is provided as a string, convert it to an object.
        if (is_string($srcUnit)) {
            $srcUnit = DerivedUnit::parse($srcUnit);
        }

        // If the destination unit is provided as a string, convert it to an object.
        if (is_string($destUnit)) {
            $destUnit = DerivedUnit::parse($destUnit);
        }

        $srcUnitSymbol = (string)$srcUnit;
        $destUnitSymbol = (string)$destUnit;

        // Check that the source and destination units are valid.
        $validUnitSymbols = self::getUnitSymbols();
        $quoted = implode(', ', Arrays::quoteValues($validUnitSymbols));
        if (!in_array($srcUnitSymbol, $validUnitSymbols, true)) {
            throw new ValueError("Invalid temperature unit '$origSrcUnitSymbol'. Valid units are: $quoted.");
        }
        if (!in_array($destUnitSymbol, $validUnitSymbols, true)) {
            throw new ValueError("Invalid temperature unit '$origDestUnitSymbol'. Valid units are: $quoted.");
        }

        // We done?
        if ($srcUnitSymbol === $destUnitSymbol) {
            return $value;
        }

        // Apply offset as needed to get value from absolute zero.
        if ($srcUnitSymbol === 'degC') {
            // Convert Celsius to Kelvin.
            $value += self::CELSIUS_OFFSET;
            $srcUnitSymbol = 'K';
        }
        elseif ($srcUnitSymbol === 'degF') {
            // Convert Fahrenheit to Rankine.
            $value += self::FAHRENHEIT_OFFSET;
            $srcUnitSymbol = 'degR';
        }

        // We done?
        if ($srcUnitSymbol === $destUnitSymbol) {
            return $value;
        }

        // Scale as needed.
        if ($srcUnitSymbol === 'K') {
            // Convert Kelvin to Rankine.
            $value *= self::RANKINE_PER_KELVIN;
            $srcUnitSymbol = 'degR';
        }
        else {
            // Convert Rankine to Kelvin.
            $value /= self::RANKINE_PER_KELVIN;
            $srcUnitSymbol = 'K';
        }

        // We done?
        if ($srcUnitSymbol === $destUnitSymbol) {
            return $value;
        }

        // Convert Kelvin to Celsius.
        if ($srcUnitSymbol === 'K') {
            return $value - self::CELSIUS_OFFSET;
        }

        // Convert Rankine to Fahrenheit.
        return $value - self::FAHRENHEIT_OFFSET;
    }

    public static function getUnitSymbols(): array
    {
        $symbols = [];
        $units = UnitData::getByDimension('H');
        foreach ($units as $unit) {
            $symbols[] = $unit->symbol;
            if ($unit->format !== null) {
                $symbols[] = $unit->format;
            }
        }
        return $symbols;
    }

    // region Factory methods

//    /**
//     * Parse a temperature string.
//     *
//     * Accepts standard formats like "25C", "98.6F", "273.15K"
//     * or with degree symbols like "25°C", "98.6°F".
//     *
//     * @param string $value The string to parse.
//     * @return static A new Temperature instance.
//     * @throws ValueError If the string is not a valid temperature format.
//     */
//    #[Override]
//    public static function parse(string $value): static
//    {
//        try {
//            // Try to parse using Quantity::parse().
//            return parent::parse($value);
//        } catch (ValueError $e) {
//            // Check for Celsius or Fahrenheit with a degree symbol, e.g. "25°C" or "98.6°F".
//            $rxNum = '[-+]?(?:\d+(?:\.\d+)?|\.\d+)(?:[eE][+-]?\d+)?';
//            $pattern = "/^($rxNum)\s*°([CF])$/";
//
//            if (preg_match($pattern, $value, $matches)) {
//                return new static((float)$matches[1], $matches[2]);
//            }
//
//            // Invalid format.
//            throw $e;
//        }
//    }

    // endregion

}
