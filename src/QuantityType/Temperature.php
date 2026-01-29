<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Quantities\DerivedUnit;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\System;
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

    // region Overridden methods

    /**
     * Unit definitions for temperature.
     *
     * @return array<string, array{
     *     asciiSymbol: string,
     *     unicodeSymbol?: string,
     *     prefixGroup?: int,
     *     systems: list<System>,
     *     expansionUnitSymbol?: string,
     *     expansionValue?: float
     * }>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'kelvin'     => [
                'asciiSymbol' => 'K',
                'prefixGroup' => PrefixRegistry::GROUP_CODE_METRIC,
                'systems'     => [System::SI],
            ],
            'celsius'    => [
                'asciiSymbol'         => 'degC',
                'unicodeSymbol'       => '°C',
                'systems'             => [System::SI],
                'expansionUnitSymbol' => 'K',
            ],
            'fahrenheit' => [
                'asciiSymbol'         => 'degF',
                'unicodeSymbol'       => '°F',
                'systems'             => [System::Imperial, System::US],
                'expansionUnitSymbol' => 'degR',
            ],
            'rankine'    => [
                'asciiSymbol'   => 'degR',
                'unicodeSymbol' => '°R',
                'systems'       => [System::Imperial, System::US],
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
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            ['K', 'degR', self::RANKINE_PER_KELVIN],
        ];
    }

    /**
     * Convert temperature from one unit to another.
     *
     * This override is necessary because Celsius and Fahrenheit are offset from absolute zero, but the conversion
     * engine used by the package only supports conversion by multiplying.
     *
     * The offset (e.g. 273.15 for Celsius→Kelvin) only applies when converting actual temperatures - that is,
     * quantities with dimension H and no other unit terms or exponents. For derived units containing temperature
     * (e.g. J/°C → J/K), the standard conversion engine is used, which applies only the conversion factor (1),
     * not the offset. This is correct because such quantities represent rates of change, not absolute temperatures.
     *
     * @param float $value The temperature value to convert.
     * @param string|UnitInterface $srcUnit The source temperature unit.
     * @param string|UnitInterface $destUnit The destination temperature unit.
     * @return float The converted temperature value.
     * @throws FormatException If a string cannot be parsed.
     * @throws DomainException If the unit is unknown or not a temperature unit.
     */
    #[Override]
    public static function convert(float $value, string|UnitInterface $srcUnit, string|UnitInterface $destUnit): float
    {
        // Get the original arguments as strings in case of exception.
        $srcSymbol = (string)$srcUnit;
        $destSymbol = (string)$destUnit;

        // Get the units as DerivedUnit objects. These calls could throw exceptions.
        $srcUnit = DerivedUnit::toDerivedUnit($srcUnit);
        $destUnit = DerivedUnit::toDerivedUnit($destUnit);

        // Validate units.
        if ($srcUnit->dimension !== 'H') {
            throw new DomainException("Invalid temperature unit: '$srcSymbol'.");
        }
        if ($destUnit->dimension !== 'H') {
            throw new DomainException("Invalid temperature unit: '$destSymbol'.");
        }

        // Get the ASCII symbols.
        $srcSymbol = $srcUnit->asciiSymbol;
        $destSymbol = $destUnit->asciiSymbol;

        // We done?
        if ($srcSymbol === $destSymbol) {
            return $value;
        }

        // Apply offset as needed to get value from absolute zero.
        if ($srcSymbol === 'degC') {
            // Convert Celsius to Kelvin.
            $value += self::CELSIUS_OFFSET;
            $srcSymbol = 'K';
        } elseif ($srcSymbol === 'degF') {
            // Convert Fahrenheit to Rankine.
            $value += self::FAHRENHEIT_OFFSET;
            $srcSymbol = 'degR';
        }

        // We done?
        if ($srcSymbol === $destSymbol) {
            return $value;
        }

        // Determine if we need to convert from metric (K/degC) to US/imperial (degR/degF) or vice versa.
        $destIsImperial = in_array($destSymbol, ['degR', 'degF'], true);

        // Scale only if crossing sides.
        if ($srcSymbol === 'K' && $destIsImperial) {
            // Convert Kelvin to Rankine.
            $value *= self::RANKINE_PER_KELVIN;
            $srcSymbol = 'degR';
        } elseif ($srcSymbol === 'degR' && !$destIsImperial) {
            // Convert Rankine to Kelvin.
            $value /= self::RANKINE_PER_KELVIN;
            $srcSymbol = 'K';
        }

        // We done?
        if ($srcSymbol === $destSymbol) {
            return $value;
        }

        // Convert Kelvin to Celsius.
        if ($srcSymbol === 'K') {
            return $value - self::CELSIUS_OFFSET;
        }

        // Convert Rankine to Fahrenheit.
        return $value - self::FAHRENHEIT_OFFSET;
    }

    // endregion
}
