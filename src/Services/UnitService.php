<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Services;

use DomainException;
use Galaxon\Core\Arrays;
use Galaxon\Quantities\Internal\QuantityType;
use Galaxon\Quantities\Internal\Unit;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\UnitSystem;

/**
 * Service to support management of units used by the package.
 */
class UnitService
{
    // region Static properties

    /**
     * All known/supported units including defaults and custom.
     * Keys are unit names.
     *
     * @var ?array<string, Unit>
     */
    private static ?array $units = null;

    /**
     * The systems of units that have been loaded so far.
     *
     * @var list<UnitSystem>
     */
    private static array $loadedSystems = [];

    // endregion

    // region Static accessors

    /**
     * Get the systems of units that have been loaded so far.
     *
     * @return list<UnitSystem>
     */
    public static function getLoadedSystems(): array
    {
        self::init();
        return self::$loadedSystems;
    }

    // endregion

    // region Static methods for unit lookup

    /**
     * Get a unit by its name, or null if not found.
     *
     * @param string $name The unit name (e.g. 'meter', 'kilogram').
     * @return ?Unit The matching unit, or null if not found.
     */
    public static function getByName(string $name): ?Unit
    {
        self::init();
        assert(self::$units !== null);
        return self::$units[$name] ?? null;
    }

    /**
     * Get the Unit object matching the given symbol, or null if not found.
     *
     * Supports ASCII, Unicode, and alternate symbols, with or without prefixes.
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
            static fn (Unit $unit) => in_array($symbol, array_keys($unit->symbols), true)
        );
    }

    /**
     * Get all units belonging to the given system of units.
     *
     * @param UnitSystem $system The system of units to match.
     * @return list<Unit> Units belonging to the given system.
     */
    public static function getBySystem(UnitSystem $system): array
    {
        self::init();
        assert(self::$units !== null);

        return array_values(array_filter(self::$units, static fn (Unit $unit) => $unit->belongsToSystem($system)));
    }

    /**
     * Get all units compatible with the given quantity type.
     *
     * @param QuantityType $quantityType The quantity type to match.
     * @return list<Unit> Units compatible with the given quantity type.
     */
    public static function getByQuantityType(QuantityType $quantityType): array
    {
        self::init();
        assert(self::$units !== null);

        return array_values(
            array_filter(self::$units, static fn (Unit $unit) => $unit->quantityType === $quantityType)
        );
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
     * @param bool $replaceExisting Determines action to take if a unit with this name already exists in the registry.
     * If true, the existing unit will be replaced; otherwise, the operation will be terminated.
     * @return bool True if the unit was added, false if it already existed and was not replaced.
     * @throws DomainException If any of the new unit's symbols are equal to any existing unit's symbol.
     */
    public static function add(Unit $unit, bool $replaceExisting = false): bool
    {
        // Ensure the registry is initialized (unless we're in the middle of init).
        if (self::$units === null) {
            self::init();
        }

        // Check if we already have a unit with this name.
        if (isset(self::$units[$unit->name])) {
            // Skip if requested.
            if (!$replaceExisting) {
                return false;
            }

            // Replace the existing unit. Remove it first, so the call to getAllSymbols() doesn't include the
            // symbols from this unit.
            self::remove($unit);
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
        return true;
    }

    /**
     * Add a new unit from a unit definition.
     *
     * @param string $name The unit name.
     * @param array{
     *            asciiSymbol: string,
     *            dimension: string,
     *            systems?: UnitSystem|list<UnitSystem>,
     *            prefixGroup?: int,
     *            unicodeSymbol?: string,
     *            alternateSymbol?: string
     *        } $definition The unit definition.
     * @param bool $replaceExisting Determines action to take if a unit with this name already exists in the registry.
     * If true, the existing unit will be replaced; otherwise, the operation will be terminated.
     * @throws DomainException
     */
    public static function addFromDefinition(string $name, array $definition, bool $replaceExisting = false): void
    {
        // Add the unit. If it's already in there, skip it.
        self::add(new Unit(
            $name,
            $definition['asciiSymbol'],
            $definition['dimension'],
            $definition['systems'] ?? [],
            $definition['prefixGroup'] ?? 0,
            $definition['unicodeSymbol'] ?? null,
            $definition['alternateSymbol'] ?? null
        ), $replaceExisting);
    }

    /**
     * Remove a unit from the system.
     *
     * @param string|Unit $unit The Unit object or the name of the unit to remove.
     */
    public static function remove(string|Unit $unit): void
    {
        // If the registry is not initialized yet, do nothing.
        if (self::$units === null) {
            return;
        }

        // Get the unit name.
        $name = is_string($unit) ? $unit : $unit->name;

        // Remove the unit from the registry.
        unset(self::$units[$name]);
    }

    /**
     * Remove all units from the registry.
     *
     * This will NOT trigger a re-initialization on next access.
     * The array would have to be manually rebuilt using init(), loadBySystem(), or add().
     */
    public static function removeAll(): void
    {
        self::$units = [];
        self::$loadedSystems = [];
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
     * Load all units belonging to a specific system of units.
     *
     * @param UnitSystem $system The system of units to load units for.
     * @param bool $replaceExisting Determines action to take if any units are found with the same name in the registry.
     * If true, the existing unit will be replaced; otherwise, the operation will be terminated.
     */
    public static function loadBySystem(UnitSystem $system, bool $replaceExisting = false): void
    {
        // Loop through all unit definitions and add any belonging to the specified system.
        foreach (self::getAllDefinitions() as $name => $definition) {
            // Only load units for the specified system.
            $unitSystems = $definition['systems'] ?? [];
            if (!in_array($system, $unitSystems, true)) {
                continue;
            }

            // Add the unit if not already there.
            self::addFromDefinition($name, $definition, $replaceExisting);
        }

        // Keep track of which systems have been loaded.
        if (!in_array($system, self::$loadedSystems, true)) {
            self::$loadedSystems[] = $system;
        }

        // Update conversion definitions in the Converters. With new units, more conversion definitions will be valid.
        ConversionService::loadDefinitions();
    }

    /**
     * Remove all units belonging to a specific system of units.
     *
     * Note, this does not remove conversions from Converters that involve these units.
     * For that, call ConversionService::unloadSystem().
     *
     * @param UnitSystem $system The system of units to unload units for.
     */
    public static function unloadBySystem(UnitSystem $system): void
    {
        // Get all the units of this system.
        $units = self::getBySystem($system);

        // Remove them from the UnitService.
        foreach ($units as $unit) {
            self::remove($unit);
        }

        // Remove the system from the list of loaded systems.
        self::$loadedSystems = Arrays::removeValue(self::$loadedSystems, $system);
    }

    /**
     * Load all units belonging to a specific quantity type.
     *
     * @param QuantityType $quantityType The quantity type to load units for.
     * @param bool $replaceExisting Determines action to take if any units are found with the same name in the registry.
     * If true, the existing unit will be replaced; otherwise, the operation will be terminated.
     */
    public static function loadByQuantityType(QuantityType $quantityType, bool $replaceExisting = false): void
    {
        // Get the unit definitions for this quantity type.
        $unitDefinitions = $quantityType->class::getUnitDefinitions();

        // Loop through all unit definitions and add any that align with the loaded systems.
        foreach ($unitDefinitions as $name => $definition) {
            // Only load units for loaded systems.
            $unitSystems = $definition['systems'] ?? [];
            $intersection = array_uintersect(
                self::$loadedSystems,
                $unitSystems,
                static fn (UnitSystem $a, UnitSystem $b) => $a <=> $b
            );
            if (empty($intersection)) {
                continue;
            }

            // Add the unit.
            $definition['dimension'] = $quantityType->dimension;
            self::addFromDefinition($name, $definition, $replaceExisting);
        }

        // Update conversion definitions in the Converters. With new units, more conversion definitions will be valid.
        ConversionService::loadDefinitions();
    }

    /**
     * Load all units.
     *
     * @param bool $replaceExisting Determines action to take if any units are found with the same name in the registry.
     * If true, the existing unit will be replaced; otherwise, the operation will be terminated.
     * @return void
     * @throws DomainException
     */
    public static function loadAll(bool $replaceExisting = false): void
    {
        // Loop through all unit definitions and add any belonging to the specified system.
        foreach (self::getAllDefinitions() as $name => $definition) {
            // Add the unit if not already there.
            self::addFromDefinition($name, $definition, $replaceExisting);
        }

        // Note all systems have been loaded.
        self::$loadedSystems = UnitSystem::cases();

        // Update conversion definitions in the Converters. With new units, more conversion definitions will be valid.
        ConversionService::loadDefinitions();
    }

    // endregion

    // region Static inspection methods

    /**
     * Check if a unit is in the registry.
     *
     * @param string|Unit $unit The unit or unit name to check.
     * @return bool True if the unit is in the registry.
     */
    public static function has(string|Unit $unit): bool
    {
        self::init();

        // Get the unit name.
        $name = is_string($unit) ? $unit : $unit->name;

        // Check if the unit is in the registry.
        return isset(self::$units[$name]);
    }

    /**
     * Get the number of units in the registry.
     *
     * @return int The number of registered units.
     */
    public static function count(): int
    {
        self::init();
        return count(self::$units);
    }

    // endregion

    // region Private static helper methods

    /**
     * Initialize the units array from the QuantityType classes and default UnitSystems.
     *
     * This is called lazily on first access.
     */
    private static function init(): void
    {
        if (self::$units === null) {
            self::removeAll();

            // Load the default units.
            foreach (UnitSystem::DEFAULTS as $system) {
                self::loadBySystem($system);
            }
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

        foreach (QuantityTypeService::getAll() as $qtyType) {
            $qtyTypeClass = $qtyType->class;
            assert(is_subclass_of($qtyTypeClass, Quantity::class));

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
