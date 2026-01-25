<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Registry;

use DomainException;
use Galaxon\Quantities\Quantity;
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
     * @param string $asciiSymbol
     * @param string|null $unicodeSymbol
     * @param int $prefixGroup
     * @return string[]
     */
    private static function getSymbols(string $asciiSymbol, ?string $unicodeSymbol, int $prefixGroup): array
    {
        // Add ASCII symbol.
        $symbols = [$asciiSymbol];

        // Add Unicode symbol, if different.
        if ($unicodeSymbol !== null) {
            $symbols[] = $unicodeSymbol;
        }

        // Add prefixed symbols.
        if ($prefixGroup > 0) {
            $prefixes = PrefixRegistry::getPrefixes($prefixGroup);
            foreach ($prefixes as $prefix => $multiplier) {
                // Add prefixed ASCII symbol.
                $symbols[] = $prefix . $asciiSymbol;

                // Add prefixed Unicode symbol, if different.
                if ($unicodeSymbol !== null) {
                    $symbols[] = $prefix . $unicodeSymbol;
                }
            }
        }

        return $symbols;
    }

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
                /** @var ?class-string<Quantity> $qtyTypeClass */
                $qtyTypeClass = $qtyType->class;

                // Skip quantity types without a class.
                if ($qtyTypeClass === null) {
                    continue;
                }

                // Get units from the class and add them.
                $units = $qtyTypeClass::getUnitDefinitions();
                foreach ($units as $name => $definition) {
                    self::add(
                        $name,
                        $definition['asciiSymbol'],
                        $definition['unicodeSymbol'] ?? null,
                        $qtyType->name,
                        $dimension,
                        $definition['prefixGroup'] ?? 0,
                        $definition['expansionUnitSymbol'] ?? null,
                        $definition['expansionValue'] ?? null,
                    );
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
     * @param ?string $unicodeSymbol The Unicode symbol (e.g. 'Ω'), or null if same as ASCII.
     * @param string $quantityType The quantity type (e.g. 'length', 'mass').
     * @param string $dimension The dimension code (e.g. 'L', 'M', 'T-1').
     * @param int $prefixGroup Bitwise flags indicating which prefixes are allowed (0 if none).
     * @param ?string $expansionUnitSymbol For named units, the expansion unit symbol, or null.
     * @param ?float $expansionValue For named units with non-1:1 expansion, the multiplier.
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
    ): void {
        // Ensure registry is initialized (unless we're in the middle of init).
        if (self::$units === null) {
            self::init();
        }

        // Check name is unique.
        if (isset(self::$units[$name])) {
            throw new DomainException("Unit name '$name' already exists.");
        }

        // Get all symbols for the new unit (including prefixed variants).
        $newSymbols = self::getSymbols($asciiSymbol, $unicodeSymbol, $prefixGroup);

        // Get all existing symbols.
        $existingSymbols = self::getAllValidSymbols();

        // Check for conflicts.
        foreach ($newSymbols as $symbol) {
            if (in_array($symbol, $existingSymbols, true)) {
                throw new DomainException("Unit symbol '$symbol' already exists.");
            }
        }

        // Build the data array for the Unit constructor.
        $data = [
            'asciiSymbol'         => $asciiSymbol,
            'unicodeSymbol'       => $unicodeSymbol,
            'quantityType'        => $quantityType,
            'dimension'           => $dimension,
            'prefixGroup'         => $prefixGroup,
            'expansionUnitSymbol' => $expansionUnitSymbol,
            'expansionValue'      => $expansionValue,
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

            // Add the Unicode symbol, if different.
            if ($unit->unicodeSymbol !== $unit->asciiSymbol) {
                $validUnits[] = $unit->unicodeSymbol;
            }

            // Check if prefixes are allowed with this unit.
            if ($unit->prefixGroup > 0) {
                // Get the valid prefixes for this unit.
                $prefixes = PrefixRegistry::getPrefixes($unit->prefixGroup);

                // Add all prefixed units.
                foreach ($prefixes as $prefix => $multiplier) {
                    $validUnits[] = $prefix . $unit->asciiSymbol;

                    // Add the Unicode symbol with a prefix, if different.
                    if ($unit->unicodeSymbol !== $unit->asciiSymbol) {
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
