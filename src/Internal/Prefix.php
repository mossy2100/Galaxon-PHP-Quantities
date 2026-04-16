<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Internal;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Core\Traits\Comparison\Equatable;
use Galaxon\Quantities\Services\PrefixService;

/**
 * Represents an SI prefix.
 */
class Prefix
{
    use Equatable;

    // region Public properties

    /**
     * The prefix name (e.g. 'milli', 'kilo').
     */
    public readonly string $name;

    /**
     * The multiplier for the prefix (e.g. 0.001 for milli, 1000 for kilo).
     */
    public readonly float $multiplier;

    /**
     * The group code for the prefix (see PrefixService::GROUP_* constants).
     * This will only be one of the base groups and will therefore be a power of 2 (i.e. 1, 2, 4, or 8).
     */
    public readonly int $groupCode;

    /**
     * The ASCII unit symbol (e.g. 'm', 'k', 'M').
     * This symbol is recognized by parse() and will be used by format() if no Unicode symbol is set.
     */
    public readonly string $asciiSymbol;

    /**
     * A non-ASCII symbol (e.g. 'µ' for micro).
     * This symbol is recognized by parse() and used by format(). It can contain Unicode characters.
     * If no value is provided, this property will match the ASCII symbol.
     */
    public readonly string $unicodeSymbol;

    /**
     * An additional symbol supported by the parser.
     * This symbol is recognized by parse() but not used by format(). It can contain Unicode characters.
     */
    public readonly ?string $alternateSymbol;

    // endregion

    // region Constructor

    /**
     * Constructor.
     *
     * @param string $name The prefix name.
     * @param float $multiplier The multiplier.
     * @param int $groupCode The group code (see PrefixService::GROUP_* constants).
     * @param string $asciiSymbol The ASCII symbol.
     * @param ?string $unicodeSymbol The Unicode symbol, or null to use the ASCII symbol.
     * @param ?string $alternateSymbol The alternate symbol, or null for none.
     * @throws FormatException If any symbols are invalid.
     * @throws DomainException If the multiplier is invalid.
     */
    public function __construct(
        string $name,
        float $multiplier,
        int $groupCode,
        string $asciiSymbol,
        ?string $unicodeSymbol = null,
        ?string $alternateSymbol = null
    ) {
        // Validate the name.
        if (!self::isValidName($name)) {
            throw new FormatException("Invalid prefix name: '$name'.");
        }

        // Validate the ASCII symbol.
        if (!self::isValidAsciiPrefix($asciiSymbol)) {
            throw new FormatException("Invalid ASCII prefix symbol: '$asciiSymbol'.");
        }

        // Validate the Unicode symbol.
        if ($unicodeSymbol !== null && !Unit::isValidLetter($unicodeSymbol)) {
            throw new FormatException("Invalid Unicode prefix symbol: '$unicodeSymbol'.");
        }

        // Validate the alternate symbol. Same constraints as for the Unicode prefix.
        if ($alternateSymbol !== null && !Unit::isValidLetter($alternateSymbol)) {
            throw new FormatException("Invalid alternate prefix symbol: '$alternateSymbol'.");
        }

        // Validate multiplier.
        if ($multiplier <= 0) {
            throw new DomainException("Cannot create prefix with non-positive multiplier, got $multiplier.");
        }
        if ($multiplier === 1.0) {
            throw new DomainException('Cannot create prefix with multiplier equal to one.');
        }

        // Validate group code.
        $validGroupCodes = [
            PrefixService::GROUP_SMALL_METRIC,  PrefixService::GROUP_MEDIUM_METRIC, PrefixService::GROUP_LARGE_METRIC,
            PrefixService::GROUP_BINARY,
        ];
        if (!in_array($groupCode, $validGroupCodes, true)) {
            throw new DomainException("Invalid prefix group code: $groupCode.");
        }

        // Set properties.
        $this->name = $name;
        $this->asciiSymbol = $asciiSymbol;
        $this->unicodeSymbol = $unicodeSymbol ?? $asciiSymbol;
        $this->alternateSymbol = $alternateSymbol;
        $this->multiplier = $multiplier;
        $this->groupCode = $groupCode;
    }

    // endregion

    // region Comparison methods

    /**
     * Check if this prefix is equal to another.
     *
     * @param mixed $other The other value to compare.
     * @return bool True if the prefixes have the same name.
     */
    public function equal(mixed $other): bool
    {
        return $other instanceof self && $this->name === $other->name;
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
        return (bool)($this->groupCode & PrefixService::GROUP_ENGINEERING);
    }

    // endregion

    // region Conversion methods

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

    // region Validation methods

    /**
     * Check if a string is a valid prefix name, which is a string of 3-6 lower-case ASCII letters.
     *
     * @param string $name
     * @return bool
     */
    private static function isValidName(string $name): bool
    {
        return (bool)preg_match('/^[' . Unit::RX_ASCII_LETTERS . ']{3,6}$/', $name);
    }

    /**
     * Check if a string is a valid ASCII prefix symbol (1-2 ASCII letters).
     *
     * @param string $symbol The string to check.
     * @return bool True if the string is a valid ASCII prefix symbol.
     */
    private static function isValidAsciiPrefix(string $symbol): bool
    {
        return (bool)preg_match('/^[' . Unit::RX_ASCII_LETTERS . ']{1,2}$/i', $symbol);
    }

    // endregion
}
