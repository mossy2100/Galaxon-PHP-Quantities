<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Registry;

use DomainException;
use Galaxon\Quantities\Unit;

class UnitRegistry
{
    // region Static properties

    /**
     * All known/supported units including defaults and custom.
     *
     * @var ?array<string, Unit>
     */
    private static ?array $units = null;

    // endregion

    // region Static methods

    /**
     * Initialize the units array from the QuantityType classes.
     *
     * This is called lazily on first access.
     */
    private static function init(): void
    {
        if (self::$units === null) {
            self::$units = [];

            // Load units from QuantityType classes.
            foreach (QuantityTypeRegistry::getAll() as $dimension => $qtyType) {
                // Check we have a class with a getUnitDefinitions() method to call.
                if ($qtyType->class === null || !method_exists($qtyType->class, 'getUnitDefinitions')) {
                    continue;
                }

                // Get units from the class and add them.
                $units = $qtyType->class::getUnitDefinitions();
                foreach ($units as $name => $definition) {
                    // Add quantityType to the definition.
                    $definition['quantityType'] = $qtyType->name;
                    self::$units[$name] = new Unit($name, $definition);
                }
            }
        }
    }

    /**
     * Get all known/supported units.
     *
     * @return array<string, Unit>
     */
    public static function getAll(): array
    {
        self::init();
        return self::$units;
    }

    /**
     * Get all units with an expansion.
     *
     * @return list<Unit>
     */
    public static function getExpandableUnits(): array
    {
        $allUnits = self::getAll();
        $namedUnits = [];
        foreach ($allUnits as $name => $unit) {
            if ($unit->hasExpansion()) {
                $namedUnits[] = $unit;
            }
        }
        return $namedUnits;
    }

    /**
     * Add a unit to the system.
     *
     * @param string $name The unit name (e.g. 'metre', 'gram').
     * @param string $asciiSymbol The ASCII symbol (e.g. 'm', 'g').
     * @param ?string $unicodeSymbol The Unicode symbol (e.g. 'Ω'), or null to use the ASCII symbol.
     * @param string $quantityType The quantity type (e.g. 'length', 'mass').
     * @param string $dimension The dimension code (e.g. 'L', 'M', 'T-1').
     * @param string $system The measurement system (e.g. 'si_base', 'metric', 'us_customary').
     * @param int $prefixGroup Bitwise flags indicating which prefixes are allowed (0 if none).
     * @param ?string $siPrefix The SI prefix for this unit (e.g. 'k' for kilogram), or null.
     * @param ?string $expansionUnit For named units, the expansion unit symbol, or null.
     * @throws DomainException If the name or symbol already exists, or if the symbol is not ASCII.
     */
    public static function add(
        string $name,
        string $asciiSymbol,
        ?string $unicodeSymbol,
        string $quantityType,
        string $dimension,
        string $system,
        int $prefixGroup = 0,
        ?string $siPrefix = null,
        ?string $expansionUnit = null
    ): void {
        self::init();

        // Check name is unique.
        if (isset(self::$units[$name])) {
            throw new DomainException("Unit name '$name' already exists.");
        }

        // Check primary and Unicode symbols are unique (not matching any existing primary or Unicode symbols).
        foreach (self::$units as $existing) {
            if ($existing->asciiSymbol === $asciiSymbol) {
                throw new DomainException(
                    "ASCII symbol '$asciiSymbol' conflicts with ASCII symbol of '{$existing->name}'."
                );
            }
            if ($existing->unicodeSymbol === $asciiSymbol) {
                throw new DomainException(
                    "ASCII symbol '$asciiSymbol' conflicts with Unicode symbol of '{$existing->name}'."
                );
            }
            if ($existing->asciiSymbol === $unicodeSymbol) {
                throw new DomainException(
                    "Format symbol '$unicodeSymbol' conflicts with ASCII symbol of '{$existing->name}'."
                );
            }
            if ($existing->unicodeSymbol === $unicodeSymbol) {
                throw new DomainException(
                    "Format symbol '$unicodeSymbol' conflicts with Unicode symbol of '{$existing->name}'."
                );
            }
        }

        // Build the data array for the Unit constructor.
        $data = [
            'asciiSymbol'   => $asciiSymbol,
            'unicodeSymbol' => $unicodeSymbol,
            'quantityType'  => $quantityType,
            'dimension'     => $dimension,
            'system'        => $system,
            'prefixGroup'   => $prefixGroup,
            'siPrefix'      => $siPrefix,
            'expansionUnit' => $expansionUnit,
        ];

        self::$units[$name] = new Unit($name, $data);
    }

    /**
     * Remove a unit from the system.
     *
     * @param string $name The unit name to remove.
     */
    public static function remove(string $name): void
    {
        self::init();
        unset(self::$units[$name]);
    }

    /**
     * Get all valid supported unit symbols, include base and prefixed variants, but excluding exponents.
     *
     * @return list<string>
     */
    public static function getAllValidSymbols(): array
    {
        self::init();

        $validUnits = [];

        // Loop through the base units.
        foreach (self::$units as $unit) {
            // Add the base unit symbol.
            $validUnits[] = $unit->asciiSymbol;

            // Add the Unicode symbol, if set.
            if ($unit->unicodeSymbol !== null) {
                $validUnits[] = $unit->unicodeSymbol;
            }

            // Check if prefixes are allowed with this unit.
            if ($unit->prefixGroup > 0) {
                // Get the valid prefixes for this unit.
                $prefixes = PrefixRegistry::getPrefixes($unit->prefixGroup);

                // Add all prefixed units.
                foreach ($prefixes as $prefix => $multiplier) {
                    $validUnits[] = $prefix . $unit->asciiSymbol;

                    // Add the Unicode symbol with a prefix, if set.
                    if ($unit->unicodeSymbol !== null) {
                        $validUnits[] = $prefix . $unit->unicodeSymbol;
                    }
                }
            }
        }

        return $validUnits;
    }

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
        return array_find(
            self::$units,
            static fn (Unit $unit) => $unit->asciiSymbol === $symbol || $unit->unicodeSymbol === $symbol
        );
    }

    /**
     * Get all units matching the given dimension.
     *
     * @param string $dimension
     * @return array<string, Unit>
     */
    public static function getByDimension(string $dimension): array
    {
        self::init();
        return array_filter(self::$units, static fn ($unit) => $unit->dimension === $dimension);
    }

    // endregion
}
