<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use DomainException;

class UnitData
{
    // region Constants

    /**
     * Constants for prefix groups.
     */

    // 1 = Small metric (quecto to deci)
    public const int PREFIX_GROUP_SMALL_METRIC = 1;

    // 2 = Large metric (deca to quetta)
    public const int PREFIX_GROUP_LARGE_METRIC = 2;

    // 3 = All metric (1|2)
    public const int PREFIX_GROUP_METRIC = self::PREFIX_GROUP_SMALL_METRIC | self::PREFIX_GROUP_LARGE_METRIC;

    // 4 = Binary (Ki, Mi, Gi, etc.)
    public const int PREFIX_GROUP_BINARY = 4;

    // 6 = Large metric + binary (2|4)
    public const int PREFIX_GROUP_LARGE = self::PREFIX_GROUP_LARGE_METRIC | self::PREFIX_GROUP_BINARY;

    // 7 = All (1|2|4)
    public const int PREFIX_GROUP_ALL = self::PREFIX_GROUP_METRIC | self::PREFIX_GROUP_BINARY;

    /**
     * Standard metric prefixes down to quecto (10^-30).
     *
     * Includes both standard symbols and alternatives (e.g. 'u' for micro).
     *
     * @var array<string, float>
     */
    public const array PREFIXES_SMALL_METRIC = [
        'q' => 1e-30,  // quecto
        'r' => 1e-27,  // ronto
        'y' => 1e-24,  // yocto
        'z' => 1e-21,  // zepto
        'a' => 1e-18,  // atto
        'f' => 1e-15,  // femto
        'p' => 1e-12,  // pico
        'n' => 1e-9,   // nano
        'μ' => 1e-6,   // micro
        'u' => 1e-6,   // micro (alias)
        'm' => 1e-3,   // milli
        'c' => 1e-2,   // centi
        'd' => 1e-1,   // deci
    ];

    /**
     * Standard metric prefixes up to quetta (10^30).
     *
     * @var array<string, float>
     */
    public const array PREFIXES_LARGE_METRIC = [
        'da' => 1e1,    // deca
        'h'  => 1e2,    // hecto
        'k'  => 1e3,    // kilo
        'M'  => 1e6,    // mega
        'G'  => 1e9,    // giga
        'T'  => 1e12,   // tera
        'P'  => 1e15,   // peta
        'E'  => 1e18,   // exa
        'Z'  => 1e21,   // zetta
        'Y'  => 1e24,   // yotta
        'R'  => 1e27,   // ronna
        'Q'  => 1e30,   // quetta
    ];

    /**
     * Binary prefixes for memory measurements.
     *
     * @var array<string, float>
     */
    public const array PREFIXES_BINARY = [
        'Ki' => 2 ** 10, // kibi
        'Mi' => 2 ** 20, // mebi
        'Gi' => 2 ** 30, // gibi
        'Ti' => 2 ** 40, // tebi
        'Pi' => 2 ** 50, // pebi
        'Ei' => 2 ** 60, // exbi
        'Zi' => 2 ** 70, // zebi
        'Yi' => 2 ** 80, // yobi
    ];

    // endregion

    // region Static properties

    /**
     * All known/supported units including defaults and custom.
     *
     * @var array<string, Unit>|null
     */
    private static ?array $units = null;

    // endregion

    // region Static methods

    /**
     * Initialize the units array from the QuantityType classes.
     *
     * This is called lazily on first access.
     */
    private static function initUnits(): void
    {
        if (self::$units === null) {
            self::$units = [];

            // Load units from QuantityType classes.
            foreach (QuantityTypes::getAll() as $dimension => $data) {
                $class = $data['class'] ?? null;
                if ($class === null || !method_exists($class, 'getUnits')) {
                    continue;
                }

                // Get units from the class and add them.
                $quantityType = $data['quantityType'];
                foreach ($class::getUnits() as $name => $definition) {
                    // Add quantityType to the definition if not set.
                    if (!isset($definition['quantityType'])) {
                        $definition['quantityType'] = $quantityType;
                    }
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
    public static function getUnits(): array
    {
        self::initUnits();
        return self::$units;
    }

    /**
     * Get all units with an expansion.
     *
     * @return list<Unit>
     */
    public static function getExpandableUnits(): array
    {
        $allUnits = self::getUnits();
        $namedUnits = [];
        foreach ($allUnits as $name => $unit) {
            if ($unit->expansion !== null) {
                $namedUnits[] = $unit->expansion;
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
     * @param float $expansionValue For named units, the expansion unit value.
     * @param ?string $expansionUnit For named units, the expansion unit symbol, or null.
     * @throws DomainException If the name or symbol already exists, or if the symbol is not ASCII.
     */
    public static function addUnit(
        string $name,
        string $asciiSymbol,
        ?string $unicodeSymbol,
        string $quantityType,
        string $dimension,
        string $system,
        int $prefixGroup = 0,
        ?string $siPrefix = null,
        float $expansionValue = 1.0,
        ?string $expansionUnit = null
    ): void {
        self::initUnits();

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
            'asciiSymbol'    => $asciiSymbol,
            'unicodeSymbol'  => $unicodeSymbol,
            'quantityType'   => $quantityType,
            'dimension'      => $dimension,
            'system'         => $system,
            'prefixGroup'    => $prefixGroup,
            'siPrefix'       => $siPrefix,
            'expansionValue' => $expansionValue,
            'expansionUnit'  => $expansionUnit,
        ];

        self::$units[$name] = new Unit($name, $data);
    }

    /**
     * Remove a unit from the system.
     *
     * @param string $name The unit name to remove.
     */
    public static function removeUnit(string $name): void
    {
        self::initUnits();
        unset(self::$units[$name]);
    }

    /**
     * Get all valid supported unit symbols, include base and prefixed variants, but excluding exponents.
     *
     * @return list<string>
     */
    public static function getAllValidUnitSymbols(): array
    {
        self::initUnits();

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
                $prefixes = self::getPrefixes($unit->prefixGroup);

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
     * Get the unit matching the given symbol, or null if not found.
     *
     * Supports both the ASCII symbol and the Unicode symbol.
     *
     * @param string $symbol The unit symbol to search for.
     * @return ?Unit The matching unit, or null if not found.
     */
    public static function getBySymbol(string $symbol): ?Unit
    {
        self::initUnits();
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
        self::initUnits();
        return array_filter(self::$units, static fn ($unit) => $unit->dimension === $dimension);
    }

    /**
     * Return an array of prefixes, with multipliers, given an integer group code comprising bitwise flags.
     *
     * This can be overridden in the derived class.
     *
     * @param int $prefixGroup Code indicating the prefix groups to include.
     * @return array<string, float>
     */
    public static function getPrefixes(int $prefixGroup = self::PREFIX_GROUP_ALL): array
    {
        $prefixes = [];

        // No prefixes.
        if ($prefixGroup === 0) {
            return $prefixes;
        }

        // Get the prefixes corresponding to the given code.
        if ($prefixGroup & self::PREFIX_GROUP_SMALL_METRIC) {
            $prefixes = array_merge($prefixes, self::PREFIXES_SMALL_METRIC);
        }
        if ($prefixGroup & self::PREFIX_GROUP_LARGE_METRIC) {
            $prefixes = array_merge($prefixes, self::PREFIXES_LARGE_METRIC);
        }
        if ($prefixGroup & self::PREFIX_GROUP_BINARY) {
            $prefixes = array_merge($prefixes, self::PREFIXES_BINARY);
        }

        return $prefixes;
    }

    /**
     * Return the multiplier for a given prefix.
     *
     * @param string $prefix Prefix code, e.g. 'k' for kilo.
     * @return ?float Prefix multiplier, e.g. 1000 for kilo, or null if not found.
     */
    public static function getPrefixMultiplier(string $prefix): ?float
    {
        // Get all the prefixes.
        $prefixes = self::getPrefixes();

        // Return the multiplier for the given prefix, or null if not found.
        return $prefixes[$prefix] ?? null;
    }

    // endregion
}
