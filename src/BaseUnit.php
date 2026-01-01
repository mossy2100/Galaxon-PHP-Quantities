<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use Stringable;

/**
 * Represents a unit of measurement.
 */
readonly class BaseUnit implements Stringable
{
    /**
     * The unit name (e.g., 'metre', 'gram', 'hertz'). In theory this should be unique (currently not enforced).
     */
    public string $name;

    /**
     * The unit symbol (e.g., 'm', 'g', 'Hz').
     *
     * TODO Add a check that this string is ASCII.
     */
    public string $symbol;

    /**
     * The formatted symbol (e.g., 'Ω' for ohm, '°' for degree), or null if same as symbol.
     */
    public ?string $format;

    /**
     * The physical quantity this unit measures (e.g., 'length', 'mass', 'frequency').
     */
    public string $quantity;

    /**
     * The dimension code (e.g., 'L', 'M', 'T-1').
     */
    public string $dimension;

    /**
     * The measurement system (e.g., 'si_base', 'si_named', 'metric', 'us_customary').
     */
    public string $system;

    /**
     * Bitwise flags indicating which prefixes are allowed (0 if none).
     */
    public int $prefixes;

    /**
     * The SI prefix for this unit (e.g., 'k' for kilogram), or null if none.
     */
    public ?string $siPrefix;

    /**
     * For named units, the equivalent expression in simpler units (e.g., 'kg*m*s-2' for newton, 'nmi/h' for knot), or
     * null if not applicable.
     */
    public ?string $equivalent;

    /**
     * Constructor.
     *
     * @param string $name The unit name (key from UnitData::UNITS).
     * @param array<string, mixed> $data The unit data (value from UnitData::UNITS).
     */
    public function __construct(string $name, array $data)
    {
        $this->name = $name;
        $this->symbol = $data['symbol'];
        $this->format = $data['format'] ?? null;
        $this->quantity = $data['quantity'];
        $this->dimension = Dimensions::normalize($data['dimension']);
        $this->system = $data['system'];
        $this->prefixes = $data['prefixes'] ?? 0;
        $this->siPrefix = $data['si_prefix'] ?? null;
        $this->equivalent = $data['equivalent'] ?? null;
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
        return UnitData::getPrefixes($this->prefixes);
    }

    /**
     * Get the display symbol (format symbol if available, otherwise the primary symbol).
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

    public function equal(BaseUnit $unit): bool
    {
        return $this->name === $unit->name;
    }
}
