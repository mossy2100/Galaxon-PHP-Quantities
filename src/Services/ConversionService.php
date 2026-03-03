<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Services;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Quantities\Internal\Conversion;
use Galaxon\Quantities\Internal\Converter;
use Galaxon\Quantities\Internal\DerivedUnit;
use Galaxon\Quantities\Internal\Unit;
use Galaxon\Quantities\Internal\UnitInterface;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\UnitSystem;
use LogicException;

/**
 * Services for unit conversions.
 *
 * Stores and retrieves conversions between units, organized by dimension.
 * Conversions are loaded per-system via loadSystem().
 */
class ConversionService
{
    // region Static methods for loading conversions

    /**
     * Load all conversions from definitions.
     *
     * Iterates through all conversion definitions and adds any new ones.
     * Both units must be known (in the registry).
     * This process will create and initialize all necessary Converters.
     *
     * By default, this method does not replace any existing conversions, making it useful for adding any now-valid
     * conversion definitions to the Converters' conversion matrixes after new units are loaded.
     *
     * @param bool $replaceExisting Determines action to take if a conversion already exists in the registry.
     * If true, the existing conversion will be replaced; otherwise, the operation will be terminated.
     * @throws FormatException If either unit in a conversion definition is provided as a string that cannot be parsed.
     * @throws DomainException If the dimensions of the units in a conversion definition don't match or the factor is
     * not positive.
     */
    public static function loadDefinitions(bool $replaceExisting = false): void
    {
        // Scan all existing definitions for any that match the specified system.
        foreach (
            self::getAllDefinitions() as [$srcSymbol, $destSymbol, $factor]
        ) {
            // Try to get the source unit as a DerivedUnit object. This will validate the provided value.
            try {
                $srcUnit = DerivedUnit::toDerivedUnit($srcSymbol);
            } catch (DomainException) {
                // The symbol contains an unknown unit.
                continue;
            }

            // Try to get the destination unit as a DerivedUnit object. This will validate the provided value.
            try {
                $destUnit = DerivedUnit::toDerivedUnit($destSymbol);
            } catch (DomainException) {
                // The symbol contains an unknown unit.
                continue;
            }

            // Add the conversion (replacing any existing if specified).
            self::add(new Conversion($srcUnit, $destUnit, $factor), $replaceExisting);
        }
    }

    /**
     * Get all conversion definitions from all QuantityType classes.
     *
     * @return list<array{string, string, float}> Array of [srcSymbol, destSymbol, factor] tuples.
     */
    public static function getAllDefinitions(): array
    {
        $definitions = [];

        foreach (QuantityTypeService::getAll() as $qtyType) {
            $qtyTypeClass = $qtyType->class;
            assert(is_subclass_of($qtyTypeClass, Quantity::class));

            // Collect conversion definitions.
            foreach ($qtyTypeClass::getConversionDefinitions() as $definition) {
                $definitions[] = $definition;
            }
        }

        return $definitions;
    }

    // endregion

    // region Static methods for adding/removing conversions to/from the Converters

    /**
     * Add a conversion to the appropriate Converter.
     *
     * @param Conversion $conversion The conversion to add.
     * @param bool $replaceExisting If true, replace any existing conversion between the same units.
     * @throws LogicException If the conversion's dimension doesn't match its Converter.
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
     * Remove all conversions involving units from a specific measurement system.
     *
     * @param UnitSystem $system The measurement system to unload.
     */
    public static function unloadBySystem(UnitSystem $system): void
    {
        // Get all the units in this system.
        $units = UnitService::getBySystem($system);

        // Remove any conversions involving these units.
        foreach ($units as $unit) {
            self::removeByUnit($unit);
        }
    }

    // endregion

    // region Static methods for retrieving and querying conversions

    /**
     * Get a known conversion from the matrix without attempting to discover new paths.
     *
     * @param string|UnitInterface $srcUnit The source unit.
     * @param string|UnitInterface $destUnit The destination unit.
     * @return ?Conversion The conversion, or null if not in the matrix.
     * @throws FormatException If a unit string cannot be parsed.
     * @throws DomainException If a unit string contains unknown units or the dimensions don't match.
     */
    public static function get(string|UnitInterface $srcUnit, string|UnitInterface $destUnit): ?Conversion
    {
        [$srcUnit, $destUnit] = self::validateUnits($srcUnit, $destUnit);
        $converter = Converter::getInstance($srcUnit->dimension);
        return $converter->getConversion($srcUnit, $destUnit);
    }

    /**
     * Check whether a conversion exists in the matrix.
     *
     * @param string|UnitInterface $srcUnit The source unit.
     * @param string|UnitInterface $destUnit The destination unit.
     * @return bool True if the conversion exists.
     * @throws FormatException If a unit string cannot be parsed.
     * @throws DomainException If a unit string contains unknown units or the dimensions don't match.
     */
    public static function has(string|UnitInterface $srcUnit, string|UnitInterface $destUnit): bool
    {
        return self::get($srcUnit, $destUnit) !== null;
    }

    // endregion

    // region Static methods for conversions

    /**
     * Convert a value from one unit to another.
     *
     * @param float $value The numeric value to convert.
     * @param string|UnitInterface $srcUnit The source unit.
     * @param string|UnitInterface $destUnit The destination unit.
     * @return float The converted value.
     * @throws FormatException If a unit string cannot be parsed.
     * @throws DomainException If a unit string contains unknown units or the dimensions don't match.
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
     * @throws FormatException If a unit string cannot be parsed.
     * @throws DomainException If a unit string contains unknown units or the dimensions don't match.
     */
    public static function find(string|UnitInterface $srcUnit, string|UnitInterface $destUnit): ?Conversion
    {
        [$srcUnit, $destUnit] = self::validateUnits($srcUnit, $destUnit);
        $converter = Converter::getInstance($srcUnit->dimension);
        return $converter->findConversion($srcUnit, $destUnit);
    }

    /**
     * Find the conversion factor between two units, discovering new paths if necessary.
     *
     * @param string|UnitInterface $srcUnit The source unit.
     * @param string|UnitInterface $destUnit The destination unit.
     * @return ?float The conversion factor, or null if no path exists.
     * @throws FormatException If a unit string cannot be parsed.
     * @throws DomainException If a unit string contains unknown units or the dimensions don't match.
     */
    public static function findFactor(string|UnitInterface $srcUnit, string|UnitInterface $destUnit): ?float
    {
        [$srcUnit, $destUnit] = self::validateUnits($srcUnit, $destUnit);
        $converter = Converter::getInstance($srcUnit->dimension);
        return $converter->findConversionFactor($srcUnit, $destUnit);
    }

    // endregion

    // region Validation methods

    /**
     * Validate and convert both units to DerivedUnit objects, ensuring they share the same dimension.
     *
     * @param string|UnitInterface $srcUnit The source unit.
     * @param string|UnitInterface $destUnit The destination unit.
     * @return array{DerivedUnit, DerivedUnit} The validated DerivedUnit pair.
     * @throws FormatException If a unit string cannot be parsed.
     * @throws DomainException If a unit string contains unknown units or the dimensions don't match.
     */
    private static function validateUnits(string|UnitInterface $srcUnit, string|UnitInterface $destUnit): array
    {
        $srcUnit = DerivedUnit::toDerivedUnit($srcUnit);
        $destUnit = DerivedUnit::toDerivedUnit($destUnit);

        if ($srcUnit->dimension !== $destUnit->dimension) {
            throw new DomainException(
                "Cannot convert between units of different dimensions: '$srcUnit' ($srcUnit->dimension) " .
                "and '$destUnit' ($destUnit->dimension)."
            );
        }

        return [$srcUnit, $destUnit];
    }

    // endregion
}
