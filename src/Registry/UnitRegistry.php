<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Registry;

use DomainException;
use Galaxon\Quantities\Internal\Unit;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\System;

/**
 * Registry of known units.
 */
class UnitRegistry
{
    // region Constants

    public const int ON_DUPLICATE_THROW = 0;
    public const int ON_DUPLICATE_SKIP = 1;
    public const int ON_DUPLICATE_REPLACE = 2;

    // endregion

    // region Static properties

    /**
     * All known/supported units including defaults and custom.
     *
     * @var ?array<string, Unit>
     */
    private static ?array $units = null;

    /**
     * The systems of units that have been loaded so far.
     *
     * @var list<System>
     */
    private static array $loadedSystems = [];

    // endregion

    // region Static accessors

    /** @return list<System> */
    public static function getLoadedSystems(): array
    {
        self::init();
        return self::$loadedSystems;
    }

    // endregion

    // region Static methods for unit lookup

    /**
     * Get the Unit object matching the given symbol, or null if not found.
     *
     * Supports both the ASCII symbol and the Unicode symbol.
     * Since unit symbols are required to be unique, this method can only match zero or one units.
     *
     * @param string $symbol The unit symbol to search for.
     * @return ?Unit The matching unit, or null if not found.
     */
    public static function getBySymbol(string $symbol): ?Unit
    {
        self::init();
        assert(self::$units !== null);

        return array_find(
            self::$units,
            static fn (Unit $unit) => $unit->asciiSymbol === $symbol ||
                $unit->unicodeSymbol === $symbol ||
                $unit->alternateSymbol === $symbol
        );
    }

    /**
     * Get all units belonging to the given system of units.
     *
     * @param System $system The system of units to match.
     * @return list<Unit> Units belonging to the given system.
     */
    public static function getBySystem(System $system): array
    {
        self::init();
        assert(self::$units !== null);

        return array_values(array_filter(self::$units, static fn (Unit $unit) => $unit->belongsToSystem($system)));
    }

    /**
     * Get all known/supported units.
     *
     * @return array<string, Unit>
     */
    public static function getAll(): array
    {
        self::init();
        assert(self::$units !== null);

        return self::$units;
    }

    /**
     * Get all valid unit symbols, including base and prefixed variants.
     *
     * @return list<string> All valid symbols.
     */
    public static function getAllSymbols(): array
    {
        self::init();
        assert(self::$units !== null);

        $allSymbols = [];

        // Loop through the units, adding all its valid symbols.
        foreach (self::$units as $unit) {
            $allSymbols = array_merge($allSymbols, array_keys($unit->symbols));
        }

        /** @var list<string> $allSymbols */
        return $allSymbols;
    }

    // endregion

    // region Static methods for adding/removing units to/from the registry

    /**
     * Add a unit to the system.
     *
     * @param Unit $unit The unit to add.
     * @param int $onDuplicateAction Action to take if the unit already exists in the registry. Defaults to throwing an
     * exception.
     * @throws DomainException If a unit with the same name already exists in the registry, and $onDuplicateAction is
     * set to ON_DUPLICATE_THROW; or if any of the new unit's possible symbols conflict with existing ones.
     */
    public static function add(Unit $unit, int $onDuplicateAction = self::ON_DUPLICATE_THROW): void
    {
        // Ensure the registry is initialized (unless we're in the middle of init).
        if (self::$units === null) {
            self::init();
        }

        // Check if we already have a unit with this name.
        if (isset(self::$units[$unit->name])) {
            // Throw if requested.
            if ($onDuplicateAction === self::ON_DUPLICATE_THROW) {
                throw new DomainException("Unit '$unit->name' already exists in the registry.");
            }

            // Skip if requested.
            if ($onDuplicateAction === self::ON_DUPLICATE_SKIP) {
                return;
            }

            // Replace the existing unit. Remove it first, so the call to getAllSymbols() doesn't include the
            // symbols from this unit.
            self::remove($unit->name);
        }

        // Get all existing symbols.
        $existingSymbols = self::getAllSymbols();

        // Check if the new unit's symbols conflict with existing ones.
        foreach ($unit->symbols as $symbol => $details) {
            if (in_array($symbol, $existingSymbols, true)) {
                throw new DomainException(
                    "The symbol '$symbol' for $unit->name is already being used by another unit."
                );
            }
        }

        // All good, add the unit to the registry.
        self::$units[$unit->name] = $unit;
    }

    /**
     * Remove a unit from the system.
     *
     * @param string $name The unit name to remove.
     */
    public static function remove(string $name): void
    {
        // If the registry is not initialized yet, do nothing.
        if (self::$units === null) {
            return;
        }

        // Remove the unit from the registry.
        unset(self::$units[$name]);
    }

    /**
     * Reset the registry to its default initial state.
     *
     * It will be re-initialized on next access to the default systems of units.
     */
    public static function reset(): void
    {
        self::$units = null;
        self::$loadedSystems = [];
    }

    /**
     * Removal all units from the registry.
     *
     * This will NOT trigger a re-initialization on next access.
     * The array would have to be manually rebuilt using init(), loadSystem(), or add().
     */
    public static function clear(): void
    {
        self::$units = [];
        self::$loadedSystems = [];
    }

    /**
     * Load all units belonging to a specific measurement system.
     *
     * @param System $system The measurement system to load units for.
     */
    public static function loadSystem(System $system): void
    {
        // If this system has already been loaded, do nothing.
        if (in_array($system, self::$loadedSystems, true)) {
            return;
        }

        // Loop through all unit definitions and add any belonging to the specified system.
        foreach (self::getAllDefinitions() as $name => $definition) {
            // Only load units for the specified system.
            $unitSystems = $definition['systems'] ?? [];
            if (!in_array($system, $unitSystems, true)) {
                continue;
            }

            // Add the unit. If it's already in there, skip it.
            self::add(new Unit(
                $name,
                $definition['asciiSymbol'],
                $definition['dimension'],
                $unitSystems,
                $definition['prefixGroup'] ?? 0,
                $definition['unicodeSymbol'] ?? null,
                $definition['alternateSymbol'] ?? null,
                $definition['expansionUnitSymbol'] ?? null,
                $definition['expansionValue'] ?? null
            ), self::ON_DUPLICATE_SKIP);
        }

        // Keep track of which systems have been loaded.
        self::$loadedSystems[] = $system;

        // Load any conversions involving these units.
        ConversionRegistry::loadSystem($system);
    }

    // endregion

    // region Static inspection methods

    /**
     * Check if a unit is in the registry.
     *
     * @param string $name The unit name to check.
     * @return bool True if the unit is in the registry.
     */
    public static function has(string $name): bool
    {
        self::init();
        return isset(self::$units[$name]);
    }

    // endregion

    // region Private static helper methods

    /**
     * Initialize the units array from the QuantityType classes.
     *
     * This is called lazily on first access.
     */
    private static function init(): void
    {
        if (self::$units === null) {
            self::clear();

            // Load the default measurement systems.
            self::loadSystem(System::Si);
            self::loadSystem(System::SiAccepted);
            self::loadSystem(System::Common);
        }
    }

    /**
     * Gather all unit definitions from all QuantityType classes.
     *
     * Each definition is augmented with the dimension from its QuantityType.
     *
     * @return array<string, array{asciiSymbol: string, dimension: string, ...}>
     */
    private static function getAllDefinitions(): array
    {
        $definitions = [];

        foreach (QuantityTypeRegistry::getAll() as $qtyType) {
            $qtyTypeClass = $qtyType->class;
            assert($qtyTypeClass === null || is_subclass_of($qtyTypeClass, Quantity::class));

            // Skip quantity types without a class.
            if ($qtyTypeClass === null) {
                continue;
            }

            // Collect unit definitions, injecting the dimension.
            foreach ($qtyTypeClass::getUnitDefinitions() as $name => $definition) {
                $definition['dimension'] = $qtyType->dimension;
                $definitions[$name] = $definition;
            }
        }

        return $definitions;
    }

    // endregion
}
