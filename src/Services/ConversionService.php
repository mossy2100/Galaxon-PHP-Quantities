<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Services;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Quantities\Exceptions\DimensionMismatchException;
use Galaxon\Quantities\Exceptions\UnknownUnitException;
use Galaxon\Quantities\Internal\Conversion;
use Galaxon\Quantities\Internal\Converter;
use Galaxon\Quantities\Internal\DerivedUnit;
use Galaxon\Quantities\Internal\Unit;
use Galaxon\Quantities\Internal\UnitInterface;
use Galaxon\Quantities\UnitSystem;
use LogicException;

/**
 * Services for unit conversions.
 *
 * Stores and retrieves conversions between units, organized by dimension.
 * Conversions are loaded on Converter construction.
 */
class ConversionService
{
    // region Lookup methods

    /**
     * Get a known conversion from the matrix without attempting to discover new paths.
     *
     * @param string|UnitInterface $srcUnit The source unit.
     * @param string|UnitInterface $destUnit The destination unit.
     * @return ?Conversion The conversion, or null if not in the matrix.
     * @throws FormatException If a unit string cannot be parsed.
     * @throws UnknownUnitException If a unit string contains unknown units.
     * @throws DimensionMismatchException If the dimensions don't match.
     */
    public static function get(string|UnitInterface $srcUnit, string|UnitInterface $destUnit): ?Conversion
    {
        [$srcUnit, $destUnit] = self::validateUnits($srcUnit, $destUnit);
        $converter = Converter::getInstance($srcUnit->dimension);
        return $converter->getConversion($srcUnit, $destUnit);
    }

    // endregion

    // region Registry methods

    /**
     * Add a conversion to the appropriate Converter.
     *
     * @param Conversion $conversion The conversion to add.
     * @param bool $replaceExisting If true, replace any existing conversion between the same units.
     */
    public static function add(Conversion $conversion, bool $replaceExisting = false): void
    {
        $converter = Converter::getInstance($conversion->dimension);
        $converter->addConversion($conversion, $replaceExisting);
    }

    /**
     * Remove a specific conversion from the appropriate Converter.
     *
     * @param Conversion $conversion The conversion to remove.
     */
    public static function remove(Conversion $conversion): void
    {
        $converter = Converter::getInstance($conversion->dimension);
        $converter->removeConversion($conversion);
    }

    /**
     * Remove all conversions involving a given unit from all Converters.
     *
     * @param Unit $unit The unit whose conversions should be removed.
     */
    public static function removeByUnit(Unit $unit): void
    {
        $converters = Converter::getInstances();
        foreach ($converters as $converter) {
            $converter->removeConversionsByUnit($unit);
        }
    }

    /**
     * Remove all conversions involving units from a specific system.
     *
     * @param UnitSystem $system The unit system to unload.
     */
    public static function removeBySystem(UnitSystem $system): void
    {
        // Get all the units in this system.
        $units = UnitService::getBySystem($system);

        // Remove any conversions involving these units.
        foreach ($units as $unit) {
            self::removeByUnit($unit);
        }
    }

    // endregion

    // region Inspection methods

    /**
     * Check whether a conversion exists in the matrix.
     *
     * @param string|UnitInterface $srcUnit The source unit.
     * @param string|UnitInterface $destUnit The destination unit.
     * @return bool True if the conversion exists.
     * @throws FormatException If a unit string cannot be parsed.
     * @throws UnknownUnitException If a unit string contains unknown units.
     * @throws DimensionMismatchException If the dimensions don't match.
     */
    public static function has(string|UnitInterface $srcUnit, string|UnitInterface $destUnit): bool
    {
        return self::get($srcUnit, $destUnit) !== null;
    }

    // endregion

    // region Computation methods

    /**
     * Convert a value from one unit to another.
     *
     * @param float $value The numeric value to convert.
     * @param string|UnitInterface $srcUnit The source unit.
     * @param string|UnitInterface $destUnit The destination unit.
     * @return float The converted value.
     * @throws DomainException If a string is provided, and an exponent is zero.
     * @throws FormatException If a unit string cannot be parsed.
     * @throws UnknownUnitException If a unit string contains unknown units.
     * @throws DimensionMismatchException If the dimensions don't match.
     * @throws LogicException If no conversion path exists between the units.
     */
    public static function convert(float $value, string|UnitInterface $srcUnit, string|UnitInterface $destUnit): float
    {
        [$srcUnit, $destUnit] = self::validateUnits($srcUnit, $destUnit);
        $converter = Converter::getInstance($srcUnit->dimension);
        return $converter->convert($value, $srcUnit, $destUnit);
    }

    /**
     * Find a conversion between two units, discovering new paths if necessary.
     *
     * Unlike get(), this method will attempt to generate new conversions through path-finding if no direct conversion
     * exists in the matrix.
     *
     * @param string|UnitInterface $srcUnit The source unit.
     * @param string|UnitInterface $destUnit The destination unit.
     * @return ?Conversion The conversion, or null if no path exists.
     * @throws DomainException If a string is provided, and an exponent is zero.
     * @throws FormatException If a unit string cannot be parsed.
     * @throws UnknownUnitException If a unit string contains unknown units.
     * @throws DimensionMismatchException If the dimensions don't match.
     */
    public static function find(string|UnitInterface $srcUnit, string|UnitInterface $destUnit): ?Conversion
    {
        [$srcUnit, $destUnit] = self::validateUnits($srcUnit, $destUnit);
        $converter = Converter::getInstance($srcUnit->dimension);
        return $converter->findConversion($srcUnit, $destUnit);
    }

    // endregion

    // region Helper methods

    /**
     * Validate and convert both units to DerivedUnit objects, ensuring they share the same dimension.
     *
     * @param string|UnitInterface $srcUnit The source unit.
     * @param string|UnitInterface $destUnit The destination unit.
     * @return array{DerivedUnit, DerivedUnit} The validated DerivedUnit pair.
     * @throws FormatException If a unit string cannot be parsed.
     * @throws UnknownUnitException If a unit string contains unknown units.
     * @throws DimensionMismatchException If the dimensions don't match.
     * @throws DomainException If a string is provided, and an exponent is zero.
     */
    private static function validateUnits(string|UnitInterface $srcUnit, string|UnitInterface $destUnit): array
    {
        $srcUnit = DerivedUnit::toDerivedUnit($srcUnit);
        $destUnit = DerivedUnit::toDerivedUnit($destUnit);

        if ($srcUnit->dimension !== $destUnit->dimension) {
            throw new DimensionMismatchException($srcUnit->dimension, $destUnit->dimension);
        }

        return [$srcUnit, $destUnit];
    }

    // endregion
}
