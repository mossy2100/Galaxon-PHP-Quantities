<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Quantities\Internal\DerivedUnit;
use Galaxon\Quantities\Internal\UnitInterface;
use Galaxon\Quantities\Internal\UnitTerm;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\System;
use Override;

/**
 * Represents temperature quantities.
 */
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
     *     alternateSymbol?: string,
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
                'prefixGroup' => PrefixRegistry::GROUP_METRIC,
                'systems'     => [System::Si],
            ],
            'celsius'    => [
                'asciiSymbol'         => 'degC',
                'unicodeSymbol'       => '°C',
                'systems'             => [System::Si],
                'expansionUnitSymbol' => 'K',
            ],
            'fahrenheit' => [
                'asciiSymbol'         => 'degF',
                'unicodeSymbol'       => '°F',
                'systems'             => [System::Imperial, System::UsCustomary],
                'expansionUnitSymbol' => 'degR',
            ],
            'rankine'    => [
                'asciiSymbol'   => 'degR',
                'unicodeSymbol' => '°R',
                'systems'       => [System::Imperial, System::UsCustomary],
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

        // If either unit is not a known temperature unit, delegate to parent immediately.
        // This avoids partial transformations that would corrupt the value for custom units.
        if (!self::isKnownTemperatureUnit($srcUnit) || !self::isKnownTemperatureUnit($destUnit)) {
            return parent::convert($value, $srcUnit, $destUnit);
        }

        // Get the ASCII symbols.
        $srcSymbol = $srcUnit->asciiSymbol;
        $destSymbol = $destUnit->asciiSymbol;

        // We done?
        if ($srcSymbol === $destSymbol) {
            return $value;
        }

        // ----------------------------------------------------------------------------------------
        // Get the source unit as a value from absolute zero (i.e. either K or degR).

        // Check for a source unit of Kelvin with SI prefix.
        if (self::isPrefixedKelvin($srcUnit)) {
            // Convert to unprefixed Kelvin.
            $value *= $srcUnit->multiplier;
            $srcSymbol = 'K';
        }

        // Apply offset as needed.
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

        // ----------------------------------------------------------------------------------------
        // Convert SI to imperial or vice-versa.

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

        // ----------------------------------------------------------------------------------------
        // The source unit is now in the same system as the destination unit and will be a
        // value from absolute zero (i.e. in K or degR). Convert it to the destination unit.

        // Convert Kelvin to Celsius.
        if ($destSymbol === 'degC') {
            return $value - self::CELSIUS_OFFSET;
        }

        // Convert Rankine to Fahrenheit.
        if ($destSymbol === 'degF') {
            return $value - self::FAHRENHEIT_OFFSET;
        }

        // Destination unit must be Kelvin with an SI prefix.
        return $value / $destUnit->multiplier;
    }

    // endregion

    // region Private helper methods

    /**
     * Check if a unit is a prefixed Kelvin (e.g. mK, μK).
     */
    private static function isPrefixedKelvin(DerivedUnit $unit): bool
    {
        $unitTerm = $unit->firstUnitTerm;
        assert($unitTerm instanceof UnitTerm);
        return $unitTerm->unit->asciiSymbol === 'K' && $unitTerm->prefix !== null;
    }

    /**
     * Check if a unit is a known temperature unit that this class can convert.
     *
     * Known units are: K, degC, degF, degR, and prefixed Kelvin (mK, μK, etc.).
     * Custom temperature units are not handled by this class and should be
     * delegated to the parent convert() method.
     */
    private static function isKnownTemperatureUnit(DerivedUnit $unit): bool
    {
        // Check for prefixed Kelvin first.
        if (self::isPrefixedKelvin($unit)) {
            return true;
        }

        // Check for the four base temperature units.
        return in_array($unit->asciiSymbol, ['K', 'degC', 'degF', 'degR'], true);
    }

    // endregion
}
