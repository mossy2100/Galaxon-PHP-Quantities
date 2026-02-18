<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Registry;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Quantities\Internal\Conversion;
use Galaxon\Quantities\Internal\Converter;
use Galaxon\Quantities\Internal\DerivedUnit;
use Galaxon\Quantities\Internal\Dimensions;
use Galaxon\Quantities\Internal\Unit;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\System;

/**
 * Registry for unit conversions.
 *
 * Stores and retrieves conversions between units, organized by dimension.
 * Conversions are loaded per-system via loadSystem().
 */
class ConversionRegistry
{
    // region Static properties

    /**
     * Conversion matrix storing known conversions between units.
     *
     * NB: The source and destination units symbols are the ASCII versions in canonical form.
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
     * @param string $srcUnitSymbol The source unit ASCII symbol.
     * @param string $destUnitSymbol The destination unit ASCII symbol.
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

    /**
     * Look for an expansion conversion for this unit.
     *
     * That means a conversion from a non-base unit to a base unit.
     * If the provided unit is a base unit, or if no expansion conversion is found, return null.
     *
     * A conversion with a factor of 1 is a direct expansion and is returned first.
     * If not found, the conversion with the least relative error will be returned.
     *
     * Note, new expansion conversions can be discovered. For example, an expansion of eV is not defined, but there is a
     * conversion from eV to J, which has an expansion to kg*m2/s2. Therefore, even though the first time this method is
     * called for a unit there might not be an expansion conversion, the next time there might be.
     *
     * @param Unit $unit The unit to look for an expansion for.
     * @return ?Conversion The expansion conversion, or null if none was found.
     */
    public static function getExpansion(Unit $unit): ?Conversion
    {
        // Base units cannot be expanded.
        if ($unit->isBase()) {
            return null;
        }

        // Scan the registry looking for a suitable expansion conversion.
        $conversionMatrix = self::getByDimension($unit->dimension);
        $bestConversion = null;
        $minErr = INF;
        foreach ($conversionMatrix as $src => $conversionList) {
            foreach ($conversionList as $dest => $conversion) {
                // We allow for expansions to be defined in either order, e.g. ['N', 'kg*m/s2', 1] or
                // ['kg*m/s2', 'N', 1].

                // Check for compound -> base conversion.
                if ($conversion->srcUnit->equal($unit) && $conversion->destUnit->isBase()) {
                    // Check for a unity expansion.
                    if ($conversion->factor->value === 1.0) {
                        // This is the best match, no need to keep looking.
                        return $conversion;
                    }

                    // See if this is an improvement.
                    if ($conversion->factor->relativeError < $minErr) {
                        $minErr = $conversion->factor->relativeError;
                        $bestConversion = $conversion;
                    }
                }
                // Check for base -> compound conversion.
                elseif ($conversion->destUnit->equal($unit) && $conversion->srcUnit->isBase()) {
                    // Flip it.
                    $invConversion = $conversion->inv();

                    // Check for a unity expansion.
                    if ($invConversion->factor->value === 1.0) {
                        // This is the best match, no need to keep looking.
                        return $invConversion;
                    }

                    // See if this is an improvement.
                    if ($invConversion->factor->relativeError < $minErr) {
                        $minErr = $invConversion->factor->relativeError;
                        $bestConversion = $invConversion;
                    }
                }
            }
        }

        // Return the best conversion expansion found or null if none were found.
        return $bestConversion;
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
     * Load all conversions for a specific measurement system.
     *
     * Iterates through all conversion definitions and adds any where at least one unit belongs to the specified system.
     * Both units must be known (in the registry).
     *
     * @param System $system The measurement system to load conversions for.
     * @throws FormatException If either unit in a conversion definition is provided as a string that cannot be parsed.
     * @throws DomainException If the dimensions of the units in a conversion definition don't match or the factor is
     * not positive.
     */
    public static function loadSystem(System $system): void
    {
        foreach (self::getAllDefinitions() as [$srcSymbol, $destSymbol, $factor]) {
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
                self::loadSystem($system);
            }
        }
    }

    /**
     * Get all conversion definitions from all QuantityType classes.
     *
     * @return list<array{string, string, float}> Array of [srcSymbol, destSymbol, factor] tuples.
     */
    private static function getAllDefinitions(): array
    {
        $definitions = [];

        foreach (QuantityTypeRegistry::getAll() as $qtyType) {
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
}
