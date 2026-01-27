<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Registry;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Quantities\Conversion;
use Galaxon\Quantities\DerivedUnit;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\UnitInterface;

/**
 * Registry for unit conversions.
 *
 * Stores and retrieves conversions between units, organized by dimension.
 * Conversions are loaded lazily from QuantityType classes on first access.
 */
class ConversionRegistry
{
    // region Constants

    public const int ON_MISSING_UNIT_IGNORE = 1;
    public const int ON_MISSING_UNIT_THROW = 2;

    // endregion

    // region Static properties

    /**
     * Conversion matrix storing known conversions between units.
     *
     * Structure: $conversions[$dimension][$srcSymbol][$destSymbol] = Conversion
     *
     * @var array<string, array<string, array<string, Conversion>>>
     */
    private static array $conversions = [];

    // endregion

    // region Static methods for looking up conversions

    /**
     * Get a conversion between two units.
     *
     * @param string $dimension The conversion dimension.
     * @param string $srcUnitSymbol The source unit symbol.
     * @param string $destUnitSymbol The destination unit symbol.
     * @return ?Conversion The conversion, or null if not found.
     */
    public static function get(string $dimension, string $srcUnitSymbol, string $destUnitSymbol): ?Conversion
    {
        return self::$conversions[$dimension][$srcUnitSymbol][$destUnitSymbol] ?? null;
    }

    /**
     * Get all conversions matching a given dimension.
     *
     * @param string $dimension The dimension code.
     * @return array<string, array<string, Conversion>>
     * @throws FormatException If the dimension code is invalid.
     * @throws DomainException If any conversion definitions are invalid.
     */
    public static function getByDimension(string $dimension): array
    {
        // Check the dimension code is valid.
        if (!DimensionRegistry::isValid($dimension)) {
            throw new FormatException("Invalid dimension code '$dimension'.");
        }

        // Load the conversion for this dimension.
        self::init($dimension);

        // Check if we have it.
        $dimension = DimensionRegistry::normalize($dimension);
        return self::$conversions[$dimension] ?? [];
    }

    // endregion

    // region Static methods for adding/removing conversions

    /**
     * Add a conversion between two units.
     *
     * If the units have prefixes, the unprefixed conversion is also added automatically.
     *
     * @param string|UnitInterface $srcUnit The source unit symbol or object.
     * @param string|UnitInterface $destUnit The destination unit symbol or object.
     * @param float $factor The conversion factor (destValue = srcValue * factor).
     * @param int $onMissingUnit How to handle missing units:
     *   - ON_MISSING_UNIT_IGNORE: Skip the conversion silently.
     *   - ON_MISSING_UNIT_THROW: Throw a DomainException.
     * @throws DomainException If a unit is unknown and $onMissingUnit is ON_MISSING_UNIT_THROW.
     */
    public static function add(
        string|UnitInterface $srcUnit,
        string|UnitInterface $destUnit,
        float $factor,
        int $onMissingUnit = self::ON_MISSING_UNIT_THROW
    ): void {
        // Get the source unit as a DerivedUnit object.
        try {
            $srcUnit = DerivedUnit::toDerivedUnit($srcUnit);
        } catch (DomainException) {
            if ($onMissingUnit === self::ON_MISSING_UNIT_IGNORE) {
                return;
            }
            throw new DomainException("Unit '$srcUnit' is unknown.");
        }

        // Get the destination unit as a DerivedUnit object.
        try {
            $destUnit = DerivedUnit::toDerivedUnit($destUnit);
        } catch (DomainException) {
            if ($onMissingUnit === self::ON_MISSING_UNIT_IGNORE) {
                return;
            }
            throw new DomainException("Unit '$destUnit' is unknown.");
        }

        // Construct the new Conversion.
        $conversion = new Conversion($srcUnit, $destUnit, $factor);

        // Add the Conversion to the registry.
        self::addConversion($conversion);
    }

    /**
     * Add a conversion to the registry.
     *
     * @param Conversion $conversion The conversion to add.
     */
    public static function addConversion(Conversion $conversion): void
    {
        $dim = $conversion->dimension;
        $src = $conversion->srcUnit->asciiSymbol;
        $dest = $conversion->destUnit->asciiSymbol;
        self::$conversions[$dim][$src][$dest] = $conversion;

        // If prefixes are present, also add the unprefixed conversion.
        if ($conversion->srcUnit->hasPrefixes() || $conversion->destUnit->hasPrefixes()) {
            self::addConversion($conversion->removePrefixes());
        }
    }

    /**
     * Remove a conversion from the registry.
     *
     * @param Conversion $conversion The conversion to remove.
     */
    public static function removeConversion(Conversion $conversion): void
    {
        $dim = $conversion->dimension;
        $src = $conversion->srcUnit->asciiSymbol;
        $dest = $conversion->destUnit->asciiSymbol;
        unset(self::$conversions[$dim][$src][$dest]);
    }

    /**
     * Remove all conversions involving a given unit.
     *
     * @param string|UnitInterface $unit The unit to remove conversions for.
     * @throws DomainException If the unit symbol is invalid.
     * @throws FormatException If the unit format is invalid.
     */
    public static function removeByUnit(string|UnitInterface $unit): void
    {
        $unit = DerivedUnit::toDerivedUnit($unit);

        foreach (self::$conversions as $dim => $conversionMatrix) {
            foreach ($conversionMatrix as $src => $conversionList) {
                foreach ($conversionList as $dest => $conversion) {
                    if ($conversion->srcUnit->equal($unit) || $conversion->destUnit->equal($unit)) {
                        unset(self::$conversions[$dim][$src][$dest]);
                    }
                }
            }
        }
    }

    /**
     * Reset the conversions array.
     */
    public static function reset(): void
    {
        self::$conversions = [];
    }

    /**
     * Reset the conversions for a given dimension.
     *
     * @param string $dimension The dimension code to reset.
     */
    public static function resetByDimension(string $dimension): void
    {
        self::$conversions[$dimension] = [];
    }

    /**
     * Load any missing conversions from QuantityType definitions.
     *
     * This method iterates through all QuantityType classes and adds any conversions
     * where both units are now in the UnitRegistry but the conversion isn't already present.
     * This is useful after calling UnitRegistry::loadSystem() to pick up conversions
     * that were previously skipped due to missing units.
     */
    public static function loadConversions(): void
    {
        // Iterate through all QuantityType classes.
        foreach (QuantityTypeRegistry::getAll() as $qtyType) {
            /** @var ?class-string<Quantity> $qtyTypeClass */
            $qtyTypeClass = $qtyType->class;

            // Skip quantity types without a class.
            if ($qtyTypeClass === null) {
                continue;
            }

            // Get conversions from the class.
            $conversionList = $qtyTypeClass::getConversionDefinitions();
            foreach ($conversionList as [$srcSymbol, $destSymbol, $factor]) {
                // Get the source unit as a DerivedUnit object.
                try {
                    $srcUnit = DerivedUnit::toDerivedUnit($srcSymbol);
                } catch (DomainException) {
                    // Unit is unknown.
                    continue;
                }

                // Get the destination unit as a DerivedUnit object.
                try {
                    $destUnit = DerivedUnit::toDerivedUnit($destSymbol);
                } catch (DomainException) {
                    // Unit is unknown.
                    continue;
                }

                // Construct the new Conversion.
                $newConversion = new Conversion($srcUnit, $destUnit, $factor);

                // Add it to the registry if it's not already there.
                if (!self::hasConversion($newConversion)) {
                    self::addConversion($newConversion);
                }
            }

            // Also include any expansions for units now in the registry.
            $units = UnitRegistry::getByDimension($qtyType->dimension);
            foreach ($units as $unit) {
                if ($unit->hasExpansion()) {
                    // Construct the new Conversion.
                    $newConversion = new Conversion($unit, $unit->expansionUnit, $unit->expansionValue);

                    // Add it to the registry if it's not already there.
                    if (!self::hasConversion($newConversion)) {
                        self::addConversion($newConversion);
                    }
                }
            }
        }
    }

    // endregion

    // region Static inspection methods

    /**
     * Check if a conversion exists between two units.
     *
     * @param string $dimension The conversion dimension.
     * @param string $srcUnitSymbol The source unit symbol.
     * @param string $destUnitSymbol The destination unit symbol.
     * @return bool If the conversion exists in the registry.
     */
    public static function has(string $dimension, string $srcUnitSymbol, string $destUnitSymbol): bool
    {
        return isset(self::$conversions[$dimension][$srcUnitSymbol][$destUnitSymbol]);
    }

    /**
     * Check if a conversion exists.
     *
     * @param Conversion $conversion The conversion to check.
     * @return bool If the conversion exists in the registry.
     */
    public static function hasConversion(Conversion $conversion): bool
    {
        return self::has($conversion->dimension, $conversion->srcUnit->asciiSymbol, $conversion->destUnit->asciiSymbol);
    }

    // endregion

    // region Private static helper methods

    /**
     * Initialize the conversions array from the QuantityType class corresponding to a given dimension.
     *
     * This is called lazily on first access.
     *
     * @param string $dimension The dimension code to initialize.
     */
    private static function init(string $dimension): void
    {
        if (!isset(self::$conversions[$dimension])) {
            self::resetByDimension($dimension);

            // Find the relevant QuantityType.
            $qtyType = QuantityTypeRegistry::getByDimension($dimension);

            /** @var ?class-string<Quantity> $qtyTypeClass */
            $qtyTypeClass = $qtyType?->class;

            // If this quantity type has a class with conversion definitions, load them.
            if ($qtyTypeClass !== null) {
                // Get conversions from the class and add them.
                $conversionList = $qtyTypeClass::getConversionDefinitions();
                foreach ($conversionList as [$srcSymbol, $destSymbol, $factor]) {
                    self::add($srcSymbol, $destSymbol, $factor, self::ON_MISSING_UNIT_IGNORE);
                }
            }

            // Also include any expansions.
            $units = UnitRegistry::getByDimension($dimension);
            foreach ($units as $unit) {
                if ($unit->hasExpansion()) {
                    self::add($unit, $unit->expansionUnitSymbol, $unit->expansionValue, self::ON_MISSING_UNIT_IGNORE);
                }
            }
        }
    }

    // endregion
}
