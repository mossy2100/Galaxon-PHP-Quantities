<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Core\Traits\Equatable;
use Galaxon\Quantities\Registry\PrefixRegistry;

/**
 * Represents an SI prefix.
 */
class Prefix
{
    use Equatable;

    // region Properties

    /**
     * The prefix name (e.g. 'milli', 'kilo').
     */
    private(set) string $name;

    /**
     * The ASCII unit symbol (e.g. 'm', 'k', 'M').
     * This symbol is mainly for parsing from code and must be ASCII.
     */
    private(set) string $asciiSymbol;

    /**
     * The Unicode symbol (e.g. 'µ' for micro).
     * This symbol is mainly for display and can contain Unicode characters.
     */
    private(set) string $unicodeSymbol;

    /**
     * The multiplier for the prefix (e.g. 0.001 for milli, 1000 for kilo).
     */
    private(set) float $multiplier;

    /**
     * The group code for the prefix (see PrefixRegistry::GROUP_* constants).
     * This will only be one of the base groups and will therefore be a power of 2 (i.e. 1, 2, 4, 8, or 16).
     * @see PrefixRegistry::GROUP_SMALL_ENG_METRIC
     */
    private(set) int $groupCode;

    // endregion

    // region Constructor

    /**
     * Constructor.
     *
     * @param string $name The prefix name.
     * @param string $asciiSymbol The ASCII symbol.
     * @param ?string $unicodeSymbol The Unicode symbol, or null to use the ASCII symbol.
     * @param float $multiplier The multiplier.
     * @param int $groupCode The group code (see PrefixRegistry::GROUP_* constants).
     * @throws FormatException If the ASCII or Unicode symbols are invalid.
     * @throws DomainException If the multiplier is invalid.
     */
    public function __construct(
        string $name,
        string $asciiSymbol,
        ?string $unicodeSymbol,
        float $multiplier,
        int $groupCode
    ) {
        // Validate the ASCII symbol. Max two ASCII letters.
        if (!preg_match('/^[a-z]{1,2}$/i', $asciiSymbol)) {
            throw new FormatException("Invalid ASCII symbol: $asciiSymbol");
        }

        // Validate the Unicode symbol. Max two Unicode letters.
        if ($unicodeSymbol !== null && !preg_match('/^\p{L}{1,2}$/u', $unicodeSymbol)) {
            throw new FormatException("Invalid Unicode symbol: $unicodeSymbol");
        }

        // Validate multiplier.
        if ($multiplier <= 0) {
            throw new DomainException("Multiplier must be positive: $multiplier");
        }
        if ($multiplier === 1.0) {
            throw new DomainException("Multiplier must not be equal to 1: $multiplier");
        }

        // Validate group code.
        if (!PrefixRegistry::isValidGroupCode($groupCode)) {
            throw new DomainException("Invalid group code: $groupCode");
        }

        // Set properties.
        $this->name = $name;
        $this->asciiSymbol = $asciiSymbol;
        $this->unicodeSymbol = $unicodeSymbol ?? $asciiSymbol;
        $this->multiplier = $multiplier;
        $this->groupCode = $groupCode;
    }

    // endregion

    // region Comparison methods

    /**
     * Check if this prefix is equal to another.
     *
     * @param mixed $other The other value to compare.
     * @return bool True if the prefixes have the same ASCII symbol.
     */
    public function equal(mixed $other): bool
    {
        return $other instanceof self && $this->asciiSymbol === $other->asciiSymbol;
    }

    // endregion

    // region Inspection methods

    /**
     * Check if this prefix is an engineering prefix.
     *
     * Engineering prefixes have exponents that are multiples of 3 (e.g. kilo, mega, milli, micro).
     *
     * @return bool True if this is an engineering prefix.
     */
    public function isEngineering(): bool
    {
        return (bool)($this->groupCode & PrefixRegistry::GROUP_ENG_METRIC);
    }

    // endregion

    // region String methods

    /**
     * Format the prefix for display.
     *
     * @param bool $ascii If true, return ASCII symbol; if false (default), return Unicode symbol.
     * @return string The formatted prefix.
     */
    public function format(bool $ascii = false): string
    {
        return $ascii ? $this->asciiSymbol : $this->unicodeSymbol;
    }

    /**
     * Return a string representation of the prefix.
     *
     * @return string The formatted prefix.
     */
    public function __toString(): string
    {
        return $this->format();
    }

    // endregion
}
