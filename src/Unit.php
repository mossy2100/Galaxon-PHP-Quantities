<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use Stringable;
use ValueError;

/**
 * Represents a unit of measurement.
 *
 * This class wraps the data from UnitData::UNITS and provides a typed object interface.
 */
class Unit implements Stringable
{
    /**
     * Cache of Unit instances, keyed by symbol.
     *
     * @var array<string, Unit>
     */
    private static array $cache = [];

    /**
     * The unit symbol (e.g., 'm', 'g', 'Hz').
     */
    public readonly string $symbol;

    /**
     * The unit name (e.g., 'metre', 'gram', 'hertz').
     */
    public readonly string $name;

    /**
     * The physical quantity this unit measures (e.g., 'length', 'mass', 'frequency').
     */
    public readonly string $quantity;

    /**
     * The dimension code (e.g., 'L', 'M', 'T-1').
     */
    public readonly string $dimension;

    /**
     * The measurement system (e.g., 'si_base', 'si_named', 'metric', 'us_customary').
     */
    public readonly string $system;

    /**
     * Bitwise flags indicating which prefixes are allowed (0 if none).
     */
    public readonly int $prefixes;

    /**
     * The SI prefix for this unit (e.g., 'k' for kilogram), or null if none.
     */
    public readonly ?string $siPrefix;

    /**
     * The formatted symbol for display (e.g., 'Ω' for ohm, '°' for degree), or null if same as symbol.
     */
    public readonly ?string $format;

    /**
     * The equivalent expression in base units (e.g., 'kg*m*s-2' for newton), or null if not applicable.
     */
    public readonly ?string $equivalent;

    /**
     * Constructor.
     *
     * @param string $symbol The unit symbol (key from UnitData::UNITS).
     * @param array<string, mixed> $data The unit data (value from UnitData::UNITS).
     */
    public function __construct(string $symbol, array $data)
    {
        $this->symbol = $symbol;
        $this->name = $data['name'];
        $this->quantity = $data['quantity'];
        $this->dimension = $data['dimension'];
        $this->system = $data['system'];
        $this->prefixes = $data['prefixes'] ?? 0;
        $this->siPrefix = $data['si_prefix'] ?? null;
        $this->format = $data['format'] ?? null;
        $this->equivalent = $data['equivalent'] ?? null;
    }

    /**
     * Check if a symbol is a valid unit.
     *
     * @param string $symbol The symbol to check.
     * @return bool True if valid, false otherwise.
     */
    public static function isValid(string $symbol): bool
    {
        return isset(UnitData::UNITS[$symbol]);
    }

    /**
     * Check if this unit accepts prefixes.
     *
     * @return bool True if prefixes are allowed.
     */
    public function acceptsPrefixes(): bool
    {
        return $this->prefixes > 0;
    }

    /**
     * Check if a specific prefix is allowed for this unit.
     *
     * @param string $prefix The prefix to check.
     * @return bool True if the prefix is allowed.
     */
    public function acceptsPrefix(string $prefix): bool
    {
        $allowedPrefixes = $this->getAllowedPrefixes();
        return isset($allowedPrefixes[$prefix]);
    }

    /**
     * Get all allowed prefixes for this unit.
     *
     * @return array<string, float> Map of prefix symbols to multipliers.
     */
    public function getAllowedPrefixes(): array
    {
        if ($this->prefixes === 0) {
            return [];
        }

        return self::getPrefixes($this->prefixes);
    }

    /**
     * Get the display symbol (formatted if available, otherwise the regular symbol).
     *
     * @return string The symbol to use for display.
     */
    public function getDisplaySymbol(): string
    {
        return $this->format ?? $this->symbol;
    }

    /**
     * Check if this is an SI base unit.
     *
     * @return bool True if this is an SI base unit.
     */
    public function isSiBase(): bool
    {
        return $this->system === 'si_base';
    }

    /**
     * Check if this is an SI derived unit.
     *
     * @return bool True if this is an SI derived unit.
     */
    public function isSiDerived(): bool
    {
        return $this->system === 'si_derived';
    }

    /**
     * Check if this is an SI named unit.
     *
     * @return bool True if this is an SI named unit.
     */
    public function isSiNamed(): bool
    {
        return $this->system === 'si_named';
    }

    /**
     * Check if this is any kind of SI unit.
     *
     * @return bool True if this is an SI unit.
     */
    public function isSi(): bool
    {
        return str_starts_with($this->system, 'si_');
    }

    /**
     * Check if this is a metric unit (SI or non-SI metric).
     *
     * @return bool True if this is a metric unit.
     */
    public function isMetric(): bool
    {
        return $this->isSi() || $this->system === 'metric';
    }

    /**
     * Convert to string (returns the symbol).
     *
     * @return string The unit symbol.
     */
    public function __toString(): string
    {
        return $this->symbol;
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
        // Get the prefixes corresponding to the given code.
        $prefixes = [];
        if ($prefixSetCode & UnitData::PREFIX_CODE_SMALL_METRIC) {
            $prefixes = array_merge($prefixes, UnitData::PREFIXES_SMALL_METRIC);
        }
        if ($prefixSetCode & UnitData::PREFIX_CODE_LARGE_METRIC) {
            $prefixes = array_merge($prefixes, UnitData::PREFIXES_LARGE_METRIC);
        }
        if ($prefixSetCode & UnitData::PREFIX_CODE_BINARY) {
            $prefixes = array_merge($prefixes, UnitData::PREFIXES_BINARY);
        }

        return $prefixes;
    }

    /**
     * Return the multiplier for a given prefix.
     *
     * @param string $prefix Prefix code, e.g. 'k' for kilo.
     * @return float Prefix multiplier, e.g. 1000 for kilo.
     * @throws ValueError If the prefix is unknown.
     */
    public static function getPrefixMultiplier(string $prefix): float
    {
        // Get all the prefixes.
        $prefixes = self::getPrefixes();

        // Return the multiplier for the given prefix, if known.
        if (array_key_exists($prefix, $prefixes)) {
            return $prefixes[$prefix];
        }

        // Unknown prefix.
        throw new ValueError("Unknown prefix: '$prefix'");
    }

    /**
     * Get all valid supported units.
     *
     * @return array
     */
    public static function getValidUnits(): array
    {
        $validUnits = [];

        // Loop through the base units.
        foreach (UnitData::UNITS as $unitSymbol => $unit) {

            // Add the base unit symbol.
            $validUnits[] = $unitSymbol;

            // Add the formatted unit symbol, if it exists.
            if (isset($unit['format'])) {
                $validUnits[] = $unit['format'];
            }

            // Check if prefixes are allowed with this unit.
            if (isset($unit['prefixes']) && $unit['prefixes'] > 0) {

                // Get the valid prefixes for this unit.
                $prefixes = self::getPrefixes($unit['prefixes']);

                // Add all prefixed units.
                foreach ($prefixes as $prefix => $multiplier) {
                    $validUnits[] = $prefix . $unitSymbol;

                    // Add the formatted unit symbol with a prefix, if it exists.
                    if (isset($unit['format'])) {
                        $validUnits[] = $prefix . $unit['format'];
                    }
                }
            }
        }

        return $validUnits;
    }

    /**
     * Get a Unit instance by symbol. Accepts both primary (ASCII) and formatted symbols.
     *
     * Uses caching to avoid creating multiple instances for the same unit.
     *
     * @param string $symbol The unit symbol.
     * @return ?Unit The Unit instance or null if no match was found.
     */
    public static function lookup(string $symbol): ?Unit
    {
        // Convert format symbol to primary symbol, which is the array key.
        $fn = static fn ($unitInfo, $primarySymbol) => ($unitInfo['format'] ?? null) === $symbol;
        $primarySymbol = array_find_key(UnitData::UNITS, $fn) ?? $symbol;

        // Check cache first.
        if (isset(self::$cache[$primarySymbol])) {
            return self::$cache[$primarySymbol];
        }

        // If not found, throw an exception.
        if (!isset(UnitData::UNITS[$primarySymbol])) {
            return null;
        }

        // Create and cache the Unit.
        $unit = new self($primarySymbol, UnitData::UNITS[$primarySymbol]);
        self::$cache[$primarySymbol] = $unit;

        return $unit;
    }

    /**
     * Look up a base or prefixed unit by its symbol.
     *
     * @param string $symbol The prefixed unit symbol to search for.
     * @return array<int, array<string, mixed>> Array of matching unit data.
     */
    public static function search(string $symbol): array
    {
        $matches = [];

        // Look for a matching base unit.
        $baseUnit = self::lookup($symbol);
        if ($baseUnit !== null) {
            $matches[] = [
                'prefix' => null,
                'base' => $baseUnit->symbol,
            ];
        }

        // Look for any matching prefixed units.s
        foreach (UnitData::UNITS as $base => $unit) {

            // Check if prefixes are allowed with this unit.
            if (isset($unit['prefixes']) && $unit['prefixes'] > 0) {

                // Get the valid prefixes for this unit.
                $prefixes = self::getPrefixes($unit['prefixes']);

                // Loop through the prefixes and see if any match.
                foreach ($prefixes as $prefix => $multiplier) {
                    if ($prefix . $base === $symbol ||
                            (isset($unit['format']) && $prefix . $unit['format'] === $symbol)) {
                        $matches[] = compact('prefix', 'base');
                    }
                }
            }
        }

        return $matches;
    }

    /**
     * Get all units matching the given dimension code.
     *
     * @param string $dimensionCode
     * @return array
     */
    public static function getAllByDimensionCode(string $dimensionCode): array
    {
        $units = [];
        foreach (UnitData::UNITS as $symbol => $unitInfo) {
            if ($unitInfo['dimension'] === $dimensionCode) {
                $unit = self::lookup($symbol);
                if ($unit !== null) {
                    $units[] = $unit;
                }
            }
        }
        return $units;
    }
}
