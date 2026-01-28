<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Registry;

use DomainException;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\System;
use Galaxon\Quantities\Unit;

class UnitRegistry
{
    // region Constants

    public const int ON_DUPLICATE_IGNORE = 1;
    public const int ON_DUPLICATE_REPLACE = 2;
    public const int ON_DUPLICATE_THROW = 3;

    // endregion

    // region Static properties

    /**
     * All known/supported units including defaults and custom.
     *
     * @var ?array<string, Unit>
     */
    private static ?array $units = null;

    // endregion

    // region Static methods for unit lookup

    /**
     * Get the Unit object matching the given symbol, or null if not found.
     *
     * Supports both the ASCII symbol and the Unicode symbol.
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
            static fn (Unit $unit) => $unit->asciiSymbol === $symbol || $unit->unicodeSymbol === $symbol
        );
    }

    /**
     * Get all units matching the given dimension.
     *
     * @param string $dimension The dimension code (e.g. 'L', 'M', 'T-1').
     * @return array<string, Unit> Units keyed by name.
     */
    public static function getByDimension(string $dimension): array
    {
        self::init();
        assert(self::$units !== null);

        return array_filter(self::$units, static fn ($unit) => $unit->dimension === $dimension);
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
     * Get all units with an expansion.
     *
     * @return list<Unit>
     */
    public static function getExpandable(): array
    {
        $allUnits = self::getAll();
        $expandableUnits = [];
        foreach ($allUnits as $unit) {
            if ($unit->expansionUnit !== null) {
                $expandableUnits[] = $unit;
            }
        }
        return $expandableUnits;
    }

    // endregion

    // region Static methods for adding/removing units to/from the registry

    /**
     * Add a unit to the system.
     *
     * @param string $name The unit name (e.g. 'metre', 'gram').
     * @param string $asciiSymbol The ASCII symbol (e.g. 'm', 'g').
     * @param ?string $unicodeSymbol The Unicode symbol (e.g. 'Ω'), or null if same as ASCII.
     * @param string $quantityType The quantity type (e.g. 'length', 'mass').
     * @param string $dimension The dimension code (e.g. 'L', 'M', 'T-1').
     * @param int $prefixGroup Bitwise flags indicating which prefixes are allowed (0 if none).
     * @param ?string $expansionUnitSymbol For expandable units, the expansion unit symbol, or null.
     * @param ?float $expansionValue For expandable units with non-1:1 expansion, the multiplier.
     * @param list<System> $systems The measurement systems this unit belongs to.
     * @param int $onDuplicateAction How to handle duplicate names:
     * - self::ON_DUPLICATE_IGNORE: Ignore the unit and do nothing.
     * - self::ON_DUPLICATE_REPLACE: Replace the existing unit with the new one.
     * - self::ON_DUPLICATE_THROW: Throw an exception.
     * @throws DomainException If the name or symbol already exists.
     */
    public static function add(
        string $name,
        string $asciiSymbol,
        ?string $unicodeSymbol,
        string $quantityType,
        string $dimension,
        int $prefixGroup = 0,
        ?string $expansionUnitSymbol = null,
        ?float $expansionValue = null,
        array $systems = [],
        int $onDuplicateAction = self::ON_DUPLICATE_THROW
    ): void {
        // Ensure registry is initialized (unless we're in the middle of init).
        if (self::$units === null) {
            self::init();
        }

        // Check if we already have a unit with this name.
        if (isset(self::$units[$name])) {
            switch ($onDuplicateAction) {
                case self::ON_DUPLICATE_IGNORE:
                    return;

                case self::ON_DUPLICATE_REPLACE:
                    // Replace the existing unit.
                    // Remove it first so the call to getAllValidSymbols() doesn't include the symbols from this unit.
                    self::remove($name);
                    break;

                case self::ON_DUPLICATE_THROW:
                    throw new DomainException(
                        "The unit name '$name' is being used by another unit. Either call `remove()` first, " .
                        'or call `add()` with `onDuplicateAction` set to `ON_DUPLICATE_REPLACE`.'
                    );

                default:
                    throw new DomainException("Invalid onDuplicateAction value: $onDuplicateAction");
            }
        }

        // Create the new unit.
        $unit = new Unit(
            $name,
            $asciiSymbol,
            $unicodeSymbol,
            $quantityType,
            $dimension,
            $prefixGroup,
            $expansionUnitSymbol,
            $expansionValue,
            $systems
        );

        // Get all existing symbols.
        $existingSymbols = self::getAllSymbols();

        // Check if the new unit's symbols conflict with existing ones.
        foreach ($unit->symbols as $symbol) {
            if (in_array($symbol, $existingSymbols, true)) {
                throw new DomainException("The symbol '$symbol' for $name is already being used by another unit.");
            }
        }

        // All good, add the unit to the registry.
        self::$units[$name] = $unit;
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
     * Reset the registry to an empty state.
     *
     * After calling this method, use loadSystem() or add() to populate the registry
     * with the desired units. This allows customizing which measurement systems are available.
     */
    public static function reset(): void
    {
        self::$units = [];
    }

    /**
     * Load all units belonging to a specific measurement system.
     *
     * Also loads any conversions involving the newly loaded units.
     *
     * @param System $system The measurement system to load units for.
     */
    public static function loadSystem(System $system): void
    {
        foreach (QuantityTypeRegistry::getAll() as $dimension => $qtyType) {
            /** @var ?class-string<Quantity> $qtyTypeClass */
            $qtyTypeClass = $qtyType->class;

            // Skip quantity types without a class.
            if ($qtyTypeClass === null) {
                continue;
            }

            // Get units from the class and add them.
            $units = $qtyTypeClass::getUnitDefinitions();
            foreach ($units as $name => $definition) {
                // Only load units for the specified system.
                $unitSystems = $definition['systems'] ?? [];
                if (!in_array($system, $unitSystems, true)) {
                    continue;
                }

                // Add the unit. If it's already in there, ignore it.
                self::add(
                    $name,
                    $definition['asciiSymbol'],
                    $definition['unicodeSymbol'] ?? null,
                    $qtyType->name,
                    $dimension,
                    $definition['prefixGroup'] ?? 0,
                    $definition['expansionUnitSymbol'] ?? null,
                    $definition['expansionValue'] ?? null,
                    $unitSystems,
                    self::ON_DUPLICATE_IGNORE
                );
            }
        }

        // Load any conversions involving the newly loaded units.
        ConversionRegistry::loadConversions($system);
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
            self::reset();

            // Load the default measurement systems.
            self::loadSystem(System::SI);
            self::loadSystem(System::SIAccepted);
            self::loadSystem(System::Common);
        }
    }

    /**
     * Get all valid unit symbols, including base and prefixed variants.
     *
     * @return list<string> All valid symbols.
     */
    private static function getAllSymbols(): array
    {
        self::init();
        assert(self::$units !== null);

        $allSymbols = [];

        // Loop through the units, adding all its valid symbols.
        foreach (self::$units as $unit) {
            $allSymbols = array_merge($allSymbols, $unit->symbols);
        }

        return $allSymbols;
    }

    // endregion
}
