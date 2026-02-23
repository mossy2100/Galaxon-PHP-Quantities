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
     * Load all conversions for a specific measurement system.
     *
     * Iterates through all conversion definitions and adds any with at least one unit that belongs to the specified
     * system.
     *
     * Both units must be known (in the registry).
     *
     * This process will create and initialize all necessary Converters.
     *
     * @param UnitSystem $system The measurement system to load conversions for.
     * @param bool $replaceExisting Determines action to take if a conversion already exists in the registry.
     * If true, the existing conversion will be replaced; otherwise, the operation will be terminated.
     * @throws FormatException If either unit in a conversion definition is provided as a string that cannot be parsed.
     * @throws DomainException If the dimensions of the units in a conversion definition don't match or the factor is
     * not positive.
     */
    public static function loadSystem(UnitSystem $system, bool $replaceExisting = false): void
    {
        // Scan all existing definitions for any that match the specified system.
        foreach (self::getAllDefinitions() as [$srcSymbol, $destSymbol, $factor]) {
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

            // Check if at least one derived unit contains a unit that belongs to the specified system.
            if (!$srcUnit->belongsToSystem($system) && !$destUnit->belongsToSystem($system)) {
                continue;
            }

            // Add the conversion (replacing any existing).
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

    public static function add(Conversion $conversion, bool $replaceExisting = false): void
    {
        $converter = Converter::getByDimension($conversion->dimension);
        $converter->addConversion($conversion, $replaceExisting);
    }

    public static function remove(Conversion $conversion): void
    {
        $converter = Converter::getByDimension($conversion->dimension);
        $converter->removeConversion($conversion);
    }

    public static function removeByUnit(Unit $unit): void
    {
        $converters = Converter::getInstances();
        foreach ($converters as $converter) {
            $converter->removeConversionsByUnit($unit);
        }
    }

    public static function unloadSystem(UnitSystem $system): void
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

    public static function get(string|UnitInterface $srcUnit, string|UnitInterface $destUnit): ?Conversion
    {
        $srcUnit = DerivedUnit::toDerivedUnit($srcUnit);
        $destUnit = DerivedUnit::toDerivedUnit($destUnit);

        $converter = Converter::getByDimension($srcUnit->dimension);
        return $converter->conversionMatrix[$srcUnit->asciiSymbol][$destUnit->asciiSymbol] ?? null;
    }

    public static function has(string|UnitInterface $srcUnit, string|UnitInterface $destUnit): bool
    {
        return self::get($srcUnit, $destUnit) !== null;
    }

    // endregion
}
