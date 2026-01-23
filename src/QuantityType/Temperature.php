<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use DomainException;
use Galaxon\Core\Arrays;
use Galaxon\Quantities\DerivedUnit;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\UnitInterface;
use Override;

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

    // endregion

    /**
     * Unit definitions for temperature.
     *
     * @return array<string, array<string, string|int>>
     */
    public static function getUnitDefinitions(): array
    {
        return [
            // SI base unit
            'kelvin'     => [
                'asciiSymbol' => 'K',
                'dimension'   => 'H',
                'system'      => 'si_base',
                'prefixGroup' => PrefixRegistry::PREFIX_GROUP_METRIC,
            ],
            // Non-SI metric units
            'celsius'    => [
                'asciiSymbol'   => 'degC',
                'unicodeSymbol' => '°C',
                'dimension'     => 'H',
                'system'        => 'metric',
            ],
            // US customary units
            'fahrenheit' => [
                'asciiSymbol'   => 'degF',
                'unicodeSymbol' => '°F',
                'dimension'     => 'H',
                'system'        => 'us_customary',
            ],
            'rankine'    => [
                'asciiSymbol'   => 'degR',
                'unicodeSymbol' => '°R',
                'dimension'     => 'H',
                'system'        => 'us_customary',
            ],
        ];
    }

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

    /**
     * This override is necessary because Celsius and Fahrenheit are offset from absolute zero, but the conversion
     * engine used by the package only supports conversion by multipliying.
     *
     * @param float $value
     * @param string|UnitInterface $srcUnit
     * @param string|UnitInterface $destUnit
     * @return float
     */
    #[Override]
    public static function convert(float $value, string|UnitInterface $srcUnit, string|UnitInterface $destUnit): float
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
            throw new DomainException("Invalid temperature unit '$origSrcUnitSymbol'. Valid units are: $quoted.");
        }
        if (!in_array($destUnitSymbol, $validUnitSymbols, true)) {
            throw new DomainException("Invalid temperature unit '$origDestUnitSymbol'. Valid units are: $quoted.");
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
        } elseif ($srcUnitSymbol === 'degF') {
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
        } else {
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

    /**
     * Get all temperature unit symbols.
     *
     * @return list<string>
     */
    public static function getUnitSymbols(): array
    {
        $symbols = [];
        $units = UnitRegistry::getByDimension('H');
        foreach ($units as $unit) {
            $symbols[] = $unit->asciiSymbol;
            if ($unit->unicodeSymbol !== $unit->asciiSymbol) {
                $symbols[] = $unit->unicodeSymbol;
            }
        }
        return $symbols;
    }
}
