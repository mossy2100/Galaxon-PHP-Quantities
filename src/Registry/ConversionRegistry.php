<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Registry;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Quantities\Conversion;
use Galaxon\Quantities\DerivedUnit;
use Galaxon\Quantities\Dimensions;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\System;

/**
 * Registry for unit conversions.
 *
 * Stores and retrieves conversions between units, organized by dimension.
 * Conversions are loaded per-system via loadConversions().
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
     * @var ?array<string, array<string, array<string, Conversion>>>
     */
    private static ?array $conversions = null;

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
        self::init();
        assert(self::$conversions !== null);

        return self::$conversions[$dimension][$srcUnitSymbol][$destUnitSymbol] ?? null;
    }

    /**
     * Get all conversions matching a given dimension.
     *
     * @param string $dimension The dimension code.
     * @return array<string, array<string, Conversion>>
     * @throws FormatException If the dimension code is invalid.
     */
    public static function getByDimension(string $dimension): array
    {
        self::init();
        assert(self::$conversions !== null);

        // This will throw if the dimension is invalid.
        $dimension = Dimensions::normalize($dimension);
        return self::$conversions[$dimension] ?? [];
    }

    // endregion

    // region Static methods for adding conversions

    /**
     * Add a conversion to the registry.
     *
     * @param Conversion $conversion The conversion to add.
     */
    public static function add(Conversion $conversion): void
    {
        self::init();
        assert(self::$conversions !== null);

        $dim = $conversion->dimension;
        $src = $conversion->srcUnit->asciiSymbol;
        $dest = $conversion->destUnit->asciiSymbol;
        self::$conversions[$dim][$src][$dest] = $conversion;

        // If prefixes are present, also add the unprefixed conversion.
        if ($conversion->srcUnit->hasPrefixes() || $conversion->destUnit->hasPrefixes()) {
            self::add($conversion->removePrefixes());
        }
    }

    /**
     * Load conversions for a specific measurement system.
     *
     * Iterates through all conversion definitions and adds anywhere at least one unit belongs to the specified system.
     * Both units must be known (in the registry).
     *
     * @param System $system The measurement system to load conversions for.
     * @throws FormatException If either unit in a conversion definition is provided as a string that cannot be parsed.
     * @throws DomainException If the dimensions of the units in a conversion definition don't match or the factor is
     * not positive.
     */
    public static function loadConversions(System $system): void
    {
        foreach (self::getAllConversionDefinitions() as [$srcSymbol, $destSymbol, $factor]) {
            // Try to get the source unit.
            try {
                $srcUnit = DerivedUnit::toDerivedUnit($srcSymbol);
            } catch (DomainException) {
                // The symbol contains an unknown unit.
                continue;
            }

            // Try to get the destination unit.
            try {
                $destUnit = DerivedUnit::toDerivedUnit($destSymbol);
            } catch (DomainException) {
                // The symbol contains an unknown unit.
                continue;
            }

            // Check if at least one unit belongs to the specified system.
            $srcBelongs = $srcUnit->firstUnitTerm?->unit->belongsToSystem($system) ?? false;
            $destBelongs = $destUnit->firstUnitTerm?->unit->belongsToSystem($system) ?? false;
            if (!$srcBelongs && !$destBelongs) {
                continue;
            }

            // Add the conversion (replacing any existing).
            self::add(new Conversion($srcUnit, $destUnit, $factor));
        }
    }

    // endregion

    // region Static methods for removing conversions

    /**
     * Remove a conversion from the registry.
     *
     * @param Conversion $conversion The conversion to remove.
     */
    public static function remove(Conversion $conversion): void
    {
        // Skip if the registry is not initialized yet.
        if (self::$conversions === null) {
            return;
        }

        $dim = $conversion->dimension;
        $src = $conversion->srcUnit->asciiSymbol;
        $dest = $conversion->destUnit->asciiSymbol;
        unset(self::$conversions[$dim][$src][$dest]);
    }

    /**
     * Reset the registry to its default initial state.
     * This will trigger a re-initialization on first access.
     */
    public static function reset(): void
    {
        self::$conversions = null;
    }

    /**
     * Remove all conversions.
     * This will NOT trigger a re-initialization on next access.
     * The array would have to be rebuilt using init() or add().
     */
    public static function clear(): void
    {
        self::$conversions = [];
    }

    /**
     * Remove all conversions for a given dimension.
     *
     * @param string $dimension The dimension code to reset.
     */
    public static function clearByDimension(string $dimension): void
    {
        // Skip if the registry is not initialized yet.
        if (self::$conversions === null) {
            return;
        }

        self::$conversions[$dimension] = [];
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
        self::init();
        assert(self::$conversions !== null);

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
        self::init();
        assert(self::$conversions !== null);

        return self::has($conversion->dimension, $conversion->srcUnit->asciiSymbol, $conversion->destUnit->asciiSymbol);
    }

    // endregion

    // region Private static helper methods

    /**
     * Initialize the conversions array.
     *
     * This is called lazily on first access.
     *
     * @throws FormatException If a unit definition cannot be parsed.
     * @throws DomainException If any unit is unknown, or dimensions mismatch, or a conversion factor is non-positive.
     */
    private static function init(): void
    {
        if (self::$conversions === null) {
            self::clear();

            // Get the loaded systems of units.
            $systems = UnitRegistry::getLoadedSystems();
            foreach ($systems as $system) {
                self::loadConversions($system);
            }
        }
    }

    /**
     * Get all conversion definitions from all QuantityType classes.
     *
     * This includes both explicit conversion definitions and expansion-based conversions.
     *
     * @return list<array{string, string, float}> Array of [srcSymbol, destSymbol, factor] tuples.
     */
    private static function getAllConversionDefinitions(): array
    {
        $definitions = [];

        foreach (QuantityTypeRegistry::getAll() as $qtyType) {
            /** @var ?class-string<Quantity> $qtyTypeClass */
            $qtyTypeClass = $qtyType->class;

            // Skip quantity types without a class.
            if ($qtyTypeClass === null) {
                continue;
            }

            // Explicit conversion definitions.
            foreach ($qtyTypeClass::getConversionDefinitions() as $definition) {
                $definitions[] = $definition;
            }

            // Expansion-based conversions.
            foreach ($qtyTypeClass::getUnitDefinitions() as $unitDef) {
                if (isset($unitDef['expansionUnitSymbol'])) {
                    $definitions[] = [
                        $unitDef['asciiSymbol'],
                        $unitDef['expansionUnitSymbol'],
                        $unitDef['expansionValue'] ?? 1.0
                    ];
                }
            }
        }

        return $definitions;
    }

    // endregion
}
