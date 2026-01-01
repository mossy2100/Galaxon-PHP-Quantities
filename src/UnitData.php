<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use Galaxon\Quantities\QuantityType;

class UnitData
{
    // region Constants

    /**
     * Constants for prefix set codes.
     */

    // 1 = Small metric (quecto to deci)
    public const int PREFIX_CODE_SMALL_METRIC = 1;

    // 2 = Large metric (deca to quetta)
    public const int PREFIX_CODE_LARGE_METRIC = 2;

    // 3 = All metric (1|2)
    public const int PREFIX_CODE_METRIC = self::PREFIX_CODE_SMALL_METRIC | self::PREFIX_CODE_LARGE_METRIC;

    // 4 = Binary (Ki, Mi, Gi, etc.)
    public const int PREFIX_CODE_BINARY = 4;

    // 6 = Large metric + binary (2|4)
    public const int PREFIX_CODE_LARGE = self::PREFIX_CODE_LARGE_METRIC | self::PREFIX_CODE_BINARY;

    // 7 = All (1|2|4)
    public const int PREFIX_CODE_ALL = self::PREFIX_CODE_METRIC | self::PREFIX_CODE_BINARY;

    /**
     * Standard metric prefixes down to quecto (10^-30).
     *
     * Includes both standard symbols and alternatives (e.g., 'u' for micro).
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

    /**
     * Default known/supported base units, keyed by name.
     *
     * Parsing should accept both the primary symbol and the format symbol.
     * Formatting should use the format string.
     *
     * Exponents in dimension codes are written as suffixes: L2 = L², T-1 = T⁻¹, MLT-2 = M·L·T⁻²
     *
     * @var array<string, array{symbol: string, quantity: string, dimension: string, system: string, prefixes?: int, si_prefix?: string, format?: string, equivalent?: string}>
     */
    public const array UNITS = [
        // SI base units
        'metre'            => ['symbol' => 'm', 'quantity' => 'length', 'dimension' => 'L', 'system' => 'si_base', 'prefixes' => self::PREFIX_CODE_METRIC],
        'gram'             => ['symbol' => 'g', 'quantity' => 'mass', 'dimension' => 'M', 'system' => 'si_base', 'prefixes' => self::PREFIX_CODE_METRIC, 'si_prefix' => 'k'],
        'second'           => ['symbol' => 's', 'quantity' => 'time', 'dimension' => 'T', 'system' => 'si_base', 'prefixes' => self::PREFIX_CODE_METRIC],
        'ampere'           => ['symbol' => 'A', 'quantity' => 'electric current', 'dimension' => 'I', 'system' => 'si_base', 'prefixes' => self::PREFIX_CODE_METRIC],
        'kelvin'           => ['symbol' => 'K', 'quantity' => 'temperature', 'dimension' => 'H', 'system' => 'si_base', 'prefixes' => self::PREFIX_CODE_METRIC],
        'mole'             => ['symbol' => 'mol', 'quantity' => 'amount of substance', 'dimension' => 'N', 'system' => 'si_base', 'prefixes' => self::PREFIX_CODE_METRIC],
        'candela'          => ['symbol' => 'cd', 'quantity' => 'luminous intensity', 'dimension' => 'J', 'system' => 'si_base', 'prefixes' => self::PREFIX_CODE_METRIC],

        // SI derived units
        'radian'           => ['symbol' => 'rad', 'quantity' => 'angle', 'dimension' => 'A', 'system' => 'si_derived', 'prefixes' => self::PREFIX_CODE_SMALL_METRIC],
        'steradian'        => ['symbol' => 'sr', 'quantity' => 'solid angle', 'dimension' => 'A2', 'system' => 'si_derived', 'prefixes' => self::PREFIX_CODE_SMALL_METRIC],

        // SI named units
        'hertz'            => ['symbol' => 'Hz', 'quantity' => 'frequency', 'dimension' => 'T-1', 'system' => 'si_named', 'prefixes' => self::PREFIX_CODE_METRIC, 'equivalent' => 's-1'],
        'newton'           => ['symbol' => 'N', 'quantity' => 'force', 'dimension' => 'T-2LM', 'system' => 'si_named', 'prefixes' => self::PREFIX_CODE_METRIC, 'equivalent' => 'kg*m*s-2'],
        'pascal'           => ['symbol' => 'Pa', 'quantity' => 'pressure', 'dimension' => 'T-2L-1M', 'system' => 'si_named', 'prefixes' => self::PREFIX_CODE_METRIC, 'equivalent' => 'kg*m-1*s-2'],
        'joule'            => ['symbol' => 'J', 'quantity' => 'energy', 'dimension' => 'T-2L2M', 'system' => 'si_named', 'prefixes' => self::PREFIX_CODE_METRIC, 'equivalent' => 'kg*m2*s-2'],
        'watt'             => ['symbol' => 'W', 'quantity' => 'power', 'dimension' => 'T-3L2M', 'system' => 'si_named', 'prefixes' => self::PREFIX_CODE_METRIC, 'equivalent' => 'kg*m2*s-3'],
        'coulomb'          => ['symbol' => 'C', 'quantity' => 'electric charge', 'dimension' => 'TI', 'system' => 'si_named', 'prefixes' => self::PREFIX_CODE_METRIC, 'equivalent' => 's*A'],
        'volt'             => ['symbol' => 'V', 'quantity' => 'voltage', 'dimension' => 'T-3L2MI-1', 'system' => 'si_named', 'prefixes' => self::PREFIX_CODE_METRIC, 'equivalent' => 'kg*m2*s-3*A-1'],
        'farad'            => ['symbol' => 'F', 'quantity' => 'capacitance', 'dimension' => 'T4L-2M-1I2', 'system' => 'si_named', 'prefixes' => self::PREFIX_CODE_METRIC, 'equivalent' => 'kg-1*m-2*s4*A2'],
        'ohm'              => ['symbol' => 'ohm', 'format' => 'Ω', 'quantity' => 'resistance', 'dimension' => 'T-3L2MI-2', 'system' => 'si_named', 'prefixes' => self::PREFIX_CODE_METRIC, 'equivalent' => 'kg*m2*s-3*A-2'],
        'siemens'          => ['symbol' => 'S', 'quantity' => 'conductance', 'dimension' => 'T3L-2M-1I2', 'system' => 'si_named', 'prefixes' => self::PREFIX_CODE_METRIC, 'equivalent' => 'kg-1*m-2*s3*A2'],
        'weber'            => ['symbol' => 'Wb', 'quantity' => 'magnetic flux', 'dimension' => 'T-2L2MI-1', 'system' => 'si_named', 'prefixes' => self::PREFIX_CODE_METRIC, 'equivalent' => 'kg*m2*s-2*A-1'],
        'tesla'            => ['symbol' => 'T', 'quantity' => 'magnetic flux density', 'dimension' => 'T-2MI-1', 'system' => 'si_named', 'prefixes' => self::PREFIX_CODE_METRIC, 'equivalent' => 'kg*s-2*A-1'],
        'henry'            => ['symbol' => 'H', 'quantity' => 'inductance', 'dimension' => 'T-2L2MI-2', 'system' => 'si_named', 'prefixes' => self::PREFIX_CODE_METRIC, 'equivalent' => 'kg*m2*s-2*A-2'],
        'lumen'            => ['symbol' => 'lm', 'quantity' => 'luminous flux', 'dimension' => 'JA2', 'system' => 'si_named', 'prefixes' => self::PREFIX_CODE_METRIC, 'equivalent' => 'cd*sr'],
        'lux'              => ['symbol' => 'lx', 'quantity' => 'illuminance', 'dimension' => 'L-2JA2', 'system' => 'si_named', 'prefixes' => self::PREFIX_CODE_METRIC, 'equivalent' => 'cd*sr*m-2'],
        'becquerel'        => ['symbol' => 'Bq', 'quantity' => 'radioactivity', 'dimension' => 'T-1', 'system' => 'si_named', 'prefixes' => self::PREFIX_CODE_METRIC, 'equivalent' => 's-1'],
        'gray'             => ['symbol' => 'Gy', 'quantity' => 'absorbed dose', 'dimension' => 'T-2L2', 'system' => 'si_named', 'prefixes' => self::PREFIX_CODE_METRIC, 'equivalent' => 'm2*s-2'],
        'sievert'          => ['symbol' => 'Sv', 'quantity' => 'equivalent dose', 'dimension' => 'T-2L2', 'system' => 'si_named', 'prefixes' => self::PREFIX_CODE_METRIC, 'equivalent' => 'm2*s-2'],
        'katal'            => ['symbol' => 'kat', 'quantity' => 'catalytic activity', 'dimension' => 'T-1N', 'system' => 'si_named', 'prefixes' => self::PREFIX_CODE_METRIC, 'equivalent' => 'mol*s-1'],

        // US named units
        'knot'             => ['symbol' => 'kn', 'quantity' => 'velocity', 'dimension' => 'T-1L', 'system' => 'us_named', 'equivalent' => 'nmi*h-1'],

        // Non-SI metric units
        'litre'            => ['symbol' => 'L', 'quantity' => 'volume', 'dimension' => 'L3', 'system' => 'metric', 'prefixes' => self::PREFIX_CODE_METRIC],
        'hectare'          => ['symbol' => 'ha', 'quantity' => 'area', 'dimension' => 'L2', 'system' => 'metric'],
        'tonne'            => ['symbol' => 't', 'quantity' => 'mass', 'dimension' => 'M', 'system' => 'metric'],
        'bar'              => ['symbol' => 'bar', 'quantity' => 'pressure', 'dimension' => 'T-2L-1M', 'system' => 'metric'],
        'electronvolt'     => ['symbol' => 'eV', 'quantity' => 'energy', 'dimension' => 'T-2L2M', 'system' => 'metric', 'prefixes' => self::PREFIX_CODE_METRIC],
        'calorie'          => ['symbol' => 'cal', 'quantity' => 'energy', 'dimension' => 'T-2L2M', 'system' => 'metric', 'prefixes' => self::PREFIX_CODE_LARGE_METRIC],

        // Non-SI angle units
        'degree'           => ['symbol' => 'deg', 'format' => '°', 'quantity' => 'angle', 'dimension' => 'A', 'system' => 'metric'],
        'arcminute'        => ['symbol' => 'arcmin', 'format' => '′', 'quantity' => 'angle', 'dimension' => 'A', 'system' => 'metric'],
        'arcsecond'        => ['symbol' => 'arcsec', 'format' => '″', 'quantity' => 'angle', 'dimension' => 'A', 'system' => 'metric'],
        'gradian'          => ['symbol' => 'grad', 'quantity' => 'angle', 'dimension' => 'A', 'system' => 'metric'],
        'turn'             => ['symbol' => 'turn', 'quantity' => 'angle', 'dimension' => 'A', 'system' => 'metric'],

        // Non-SI temperature units
        'celsius'          => ['symbol' => 'degC', 'format' => '°C', 'quantity' => 'temperature', 'dimension' => 'H', 'system' => 'metric'],
        'fahrenheit'       => ['symbol' => 'degF', 'format' => '°F', 'quantity' => 'temperature', 'dimension' => 'H', 'system' => 'us_customary'],
        'rankine'          => ['symbol' => 'degR', 'format' => '°R', 'quantity' => 'temperature', 'dimension' => 'H', 'system' => 'us_customary'],

        // Non-SI time units
        'minute'           => ['symbol' => 'min', 'quantity' => 'time', 'dimension' => 'T', 'system' => 'metric'],
        'hour'             => ['symbol' => 'h', 'quantity' => 'time', 'dimension' => 'T', 'system' => 'metric'],
        'day'              => ['symbol' => 'd', 'quantity' => 'time', 'dimension' => 'T', 'system' => 'metric'],
        'week'             => ['symbol' => 'w', 'quantity' => 'time', 'dimension' => 'T', 'system' => 'metric'],
        'month'            => ['symbol' => 'mo', 'quantity' => 'time', 'dimension' => 'T', 'system' => 'metric'],
        'year'             => ['symbol' => 'y', 'quantity' => 'time', 'dimension' => 'T', 'system' => 'metric'],
        'century'          => ['symbol' => 'c', 'quantity' => 'time', 'dimension' => 'T', 'system' => 'metric'],

        // Non-SI pressure units
        'mmHg'             => ['symbol' => 'mmHg', 'quantity' => 'pressure', 'dimension' => 'T-2L-1M', 'system' => 'us_customary'],
        'atmosphere'       => ['symbol' => 'atm', 'quantity' => 'pressure', 'dimension' => 'T-2L-1M', 'system' => 'us_customary'],

        // Astronomical length units
        'astronomical unit' => ['symbol' => 'au', 'quantity' => 'length', 'dimension' => 'L', 'system' => 'metric'],
        'light year'       => ['symbol' => 'ly', 'quantity' => 'length', 'dimension' => 'L', 'system' => 'metric'],
        'parsec'           => ['symbol' => 'pc', 'quantity' => 'length', 'dimension' => 'L', 'system' => 'metric', 'prefixes' => self::PREFIX_CODE_LARGE_METRIC],

        // US customary length units
        'pixel'            => ['symbol' => 'px', 'quantity' => 'length', 'dimension' => 'L', 'system' => 'us_customary'],
        'point'            => ['symbol' => 'pt', 'quantity' => 'length', 'dimension' => 'L', 'system' => 'us_customary'],
        'inch'             => ['symbol' => 'in', 'quantity' => 'length', 'dimension' => 'L', 'system' => 'us_customary'],
        'foot'             => ['symbol' => 'ft', 'quantity' => 'length', 'dimension' => 'L', 'system' => 'us_customary'],
        'yard'             => ['symbol' => 'yd', 'quantity' => 'length', 'dimension' => 'L', 'system' => 'us_customary'],
        'mile'             => ['symbol' => 'mi', 'quantity' => 'length', 'dimension' => 'L', 'system' => 'us_customary'],
        'nautical mile'    => ['symbol' => 'nmi', 'quantity' => 'length', 'dimension' => 'L', 'system' => 'us_customary'],

        // US customary area units
        'acre'             => ['symbol' => 'ac', 'quantity' => 'area', 'dimension' => 'L2', 'system' => 'us_customary'],

        // US customary volume units
        'teaspoon'         => ['symbol' => 'tsp', 'quantity' => 'volume', 'dimension' => 'L3', 'system' => 'us_customary'],
        'tablespoon'       => ['symbol' => 'tbsp', 'quantity' => 'volume', 'dimension' => 'L3', 'system' => 'us_customary'],
        'fluid ounce'      => ['symbol' => 'floz', 'quantity' => 'volume', 'dimension' => 'L3', 'system' => 'us_customary'],
        'cup'              => ['symbol' => 'cup', 'quantity' => 'volume', 'dimension' => 'L3', 'system' => 'us_customary'],
        'pint'             => ['symbol' => 'pint', 'quantity' => 'volume', 'dimension' => 'L3', 'system' => 'us_customary'],
        'quart'            => ['symbol' => 'qt', 'quantity' => 'volume', 'dimension' => 'L3', 'system' => 'us_customary'],
        'gallon'           => ['symbol' => 'gal', 'quantity' => 'volume', 'dimension' => 'L3', 'system' => 'us_customary'],

        // US customary mass units
        'ounce'            => ['symbol' => 'oz', 'quantity' => 'mass', 'dimension' => 'M', 'system' => 'us_customary'],
        'pound'            => ['symbol' => 'lb', 'quantity' => 'mass', 'dimension' => 'M', 'system' => 'us_customary'],
        'stone'            => ['symbol' => 'st', 'quantity' => 'mass', 'dimension' => 'M', 'system' => 'us_customary'],
        'short ton'        => ['symbol' => 'ton', 'quantity' => 'mass', 'dimension' => 'M', 'system' => 'us_customary'],

        // US customary force units
        'pound force'      => ['symbol' => 'lbf', 'quantity' => 'force', 'dimension' => 'T-2LM', 'system' => 'us_customary'],

        // Data units
        'bit'              => ['symbol' => 'b', 'quantity' => 'data', 'dimension' => 'D', 'system' => 'metric', 'prefixes' => self::PREFIX_CODE_LARGE],
        'byte'             => ['symbol' => 'B', 'quantity' => 'data', 'dimension' => 'D', 'system' => 'metric', 'prefixes' => self::PREFIX_CODE_LARGE],
    ];

    // endregion

    // region Static properties

    /**
     * All known/supported units including defaults and custom.
     *
     * @var array<string, BaseUnit>|null
     */
    private static ?array $units = null;

    // endregion

    // region Static methods

    /**
     * Initialize the units array from the UNITS constant.
     *
     * This is called lazily on first access.
     */
    private static function initUnits(): void
    {
        if (self::$units === null) {
            self::$units = [];
            foreach (self::UNITS as $name => $definition) {
                self::$units[$name] = new BaseUnit($name, $definition);
            }
        }
    }

    /**
     * Get all known/supported units.
     *
     * @return array<string, BaseUnit>
     */
    public static function getUnits(): array
    {
        self::initUnits();
        return self::$units;
    }

    /**
     * Add or update a unit in the system.
     *
     * TODO add a check here that the symbol is unique.
     * TODO add a check here that the primary unit symbol is ASCII.
     *
     * @param BaseUnit $unit The unit to add.
     */
    public static function addUnit(BaseUnit $unit): void
    {
        self::initUnits();
        self::$units[$unit->name] = $unit;
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
     * @return array
     */
    public static function getAllValidUnitSymbols(): array
    {
        self::initUnits();

        $validUnits = [];

        // Loop through the base units.
        foreach (self::$units as $unit) {

            // Add the base unit symbol.
            $validUnits[] = $unit->symbol;

            // Add the format symbol if it exists.
            if ($unit->format !== null) {
                $validUnits[] = $unit->format;
            }

            // Check if prefixes are allowed with this unit.
            if ($unit->prefixes > 0) {

                // Get the valid prefixes for this unit.
                $prefixes = self::getPrefixes($unit->prefixes);

                // Add all prefixed units.
                foreach ($prefixes as $prefix => $multiplier) {
                    $validUnits[] = $prefix . $unit->symbol;

                    // Add the format symbol with a prefix if it exists.
                    if ($unit->format !== null) {
                        $validUnits[] = $prefix . $unit->format;
                    }
                }
            }
        }

        return $validUnits;
    }

    /**
     * Look up a base or prefixed unit by its symbol.
     *
     * @param string $symbol The prefixed unit symbol to search for.
     * @return list<UnitTerm> Array of matching unit terms.
     */
    public static function getBySymbol(string $symbol): array
    {
        self::initUnits();

        $matches = [];

        // Look for any matching units.
        foreach (self::$units as $unit) {

            // See if the unprefixed unit matches.
            if ($unit->symbol === $symbol || $unit->format === $symbol) {
                $matches[] = new UnitTerm($unit);
            }

            // Check if prefixes are allowed with this unit.
            if ($unit->prefixes > 0) {

                // Get the valid prefixes for this unit.
                $prefixes = self::getPrefixes($unit->prefixes);

                // Loop through the prefixed units and see if any match.
                foreach ($prefixes as $prefix => $multiplier) {
                    if ($prefix . $unit->symbol === $symbol ||
                            ($unit->format !== null && $prefix . $unit->format === $symbol)) {
                        $matches[] = new UnitTerm($unit, $prefix);
                    }
                }
            }
        }

        return $matches;
    }

    /**
     * Get all units matching the given dimension.
     *
     * @param string $dimension
     * @return list<BaseUnit>
     */
    public static function getByDimension(string $dimension): array
    {
        self::initUnits();
        return array_filter(self::$units, static fn ($unit) => $unit->dimension === $dimension);
    }

    /**
     * Return a set of prefixes, with multipliers, given an integer code comprising bitwise flags.
     *
     * This can be overridden in the derived class.
     *
     * @param int $prefixSetCode Code indicating the prefix sets to include.
     * @return array<string, float>
     */
    public static function getPrefixes(int $prefixSetCode = UnitData::PREFIX_CODE_ALL): array
    {
        $prefixes = [];

        // No prefixes.
        if ($prefixSetCode === 0) {
            return $prefixes;
        }

        // Get the prefixes corresponding to the given code.
        if ($prefixSetCode & self::PREFIX_CODE_SMALL_METRIC) {
            $prefixes = array_merge($prefixes, self::PREFIXES_SMALL_METRIC);
        }
        if ($prefixSetCode & self::PREFIX_CODE_LARGE_METRIC) {
            $prefixes = array_merge($prefixes, self::PREFIXES_LARGE_METRIC);
        }
        if ($prefixSetCode & self::PREFIX_CODE_BINARY) {
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
