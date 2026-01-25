<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Core\Traits\Equatable;
use Galaxon\Core\Types;
use Galaxon\Quantities\Registry\DimensionRegistry;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\Registry\UnitRegistry;
use Override;

/**
 * Represents a unit of measurement.
 */
class Unit implements UnitInterface
{
    use Equatable;

    // region Constants

    private const string RX_ASCII_WORDS = '[a-z]+(?: [a-z]+)*';

    // region Properties

    /**
     * The unit name (e.g. 'metre', 'gram', 'hertz').
     */
    private(set) string $name;

    /**
     * The ASCII unit symbol (e.g. 'm', 'g', 'Hz').
     * This symbol is mainly for parsing from code and must be ASCII.
     */
    private(set) string $asciiSymbol;

    /**
     * The Unicode symbol (e.g. 'Ω' for ohm, '°' for degree).
     * This symbol is mainly for display and can contain Unicode characters.
     */
    private(set) string $unicodeSymbol;

    /**
     * The dimension code (e.g. 'L', 'M', 'T-1').
     */
    private(set) string $dimension;

    /**
     * The quantity type, e.g. 'length'.
     */
    private(set) string $quantityType;

    /**
     * Bitwise flags indicating which prefixes are allowed (0 if none).
     */
    private(set) int $prefixGroup;

    /**
     * For expandable units, the expansion unit symbol. Null if not applicable.
     */
    private(set) ?string $expansionUnitSymbol;

    /**
     * For expandable units, the expansion value. Null if not applicable.
     */
    private(set) ?float $expansionValue;

    // endregion

    // region Property hooks

    // phpcs:disable PSR2.Classes.PropertyDeclaration
    // phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact

    /**
     * The expansion unit.
     */
    public ?DerivedUnit $expansionUnit {
        /**
         * @throws FormatException If the expansion unit symbol format is invalid.
         * @throws DomainException If the expansion unit symbol contains unknown units.
         */
        get {
            if ($this->expansionUnitSymbol === null) {
                return null;
            }

            // Convert the expansion unit symbol into a DerivedUnit if not already done.
            $this->expansionUnit ??= DerivedUnit::parse($this->expansionUnitSymbol);
            return $this->expansionUnit;
        }
    }

    /**
     * Get all allowed prefixes for this unit.
     *
     * @var array<string, float>
     */
    public array $allowedPrefixes {
        get => PrefixRegistry::getPrefixes($this->prefixGroup);
    }

    // phpcs:enable PSR2.Classes.PropertyDeclaration
    // phpcs:enable Generic.WhiteSpace.ScopeIndent.IncorrectExact

    // endregion

    // region Constructor

    /**
     * Constructor.
     *
     * @param string $name The unit name.
     * @param array<string, mixed> $data The unit details.
     * @throws FormatException If the unit symbols contain invalid characters.
     * @throws DomainException If the dimension code is invalid.
     */
    public function __construct(string $name, array $data)
    {
        // Check ASCII symbol contains ASCII letters only.
        if (!self::isValidAsciiSymbol($data['asciiSymbol'])) {
            throw new FormatException("Unit symbol '{$data['asciiSymbol']}' must only contain ASCII letters.");
        }

        // Validate Unicode symbol.
        if (isset($data['unicodeSymbol']) && !self::isValidUnicodeSymbol($data['unicodeSymbol'])) {
            throw new FormatException(
                "Unit symbol '{$data['unicodeSymbol']}' must only contain letters, or the degree, prime, or " .
                'double prime characters (i.e. °′″).'
            );
        }

        // Set the properties.
        $this->name = $name;
        $this->asciiSymbol = $data['asciiSymbol'];
        $this->unicodeSymbol = $data['unicodeSymbol'] ?? $data['asciiSymbol'];
        $this->quantityType = $data['quantityType'];
        $this->dimension = DimensionRegistry::normalize($data['dimension']);
        $this->prefixGroup = $data['prefixGroup'] ?? 0;
        $this->expansionUnitSymbol = $data['expansionUnitSymbol'] ?? null;
        $this->expansionValue = isset($data['expansionUnitSymbol']) ? ($data['expansionValue'] ?? 1.0) : null;
    }

    // endregion

    // region Prefix methods

    /**
     * Check if this unit accepts prefixes.
     *
     * @return bool True if prefixes are allowed.
     */
    public function acceptsPrefixes(): bool
    {
        return $this->prefixGroup > 0;
    }

    /**
     * Check if a specific prefix is allowed for this unit.
     *
     * @param string $prefix The prefix to check.
     * @return bool True if the prefix is allowed.
     */
    public function acceptsPrefix(string $prefix): bool
    {
        return isset($this->allowedPrefixes[$prefix]);
    }

    // endregion

    // region Inspection methods

    /**
     * Check if this unit has an expansion (i.e. can be expressed in terms of other units).
     *
     * @return bool True if this unit has an expansion.
     */
    public function hasExpansion(): bool
    {
        return $this->expansionUnitSymbol !== null;
    }

    // endregion

    // region Formatting methods

    /**
     * Format the unit as a string.
     *
     * If $ascii is false (default), returns the Unicode symbol if available, otherwise the ASCII symbol.
     * If $ascii is true, returns the ASCII symbol.
     *
     * @param bool $ascii If true, return ASCII symbol; if false (default), return Unicode symbol if available.
     * @return string The formatted unit.
     */
    #[Override]
    public function format(bool $ascii = false): string
    {
        return $ascii ? $this->asciiSymbol : $this->unicodeSymbol;
    }

    public static function regex(): string
    {
        return '([\p{L}°′″]+|' . self::RX_ASCII_WORDS . ')';
    }

    /**
     * Parse a unit symbol and return the matching Unit.
     *
     * @param string $symbol The unit symbol to parse (e.g. 'm', 'kg', 'Hz').
     * @return static The matching Unit.
     * @throws FormatException If the symbol contains invalid characters.
     * @throws DomainException If the symbol is not recognized.
     */
    #[Override]
    public static function parse(string $symbol): static
    {
        // Validate the symbol format.
        if (!self::isValidUnicodeSymbol($symbol)) {
            throw new FormatException(
                "Unit symbol '$symbol' can only contain letters, or the degree, prime, or double prime " .
                'characters (i.e. °′″).'
            );
        }

        // Get the unit from the registry.
        $unit = UnitRegistry::getBySymbol($symbol);

        // If not found, throw an exception.
        return $unit ?? throw new DomainException("Unknown unit symbol '$symbol'.");
    }

    /**
     * Convert the unit to a string. This will use the format version, which may include non-ASCII characters.
     * For the ASCII version, use format(true).
     *
     * @return string The unit as a string.
     */
    #[Override]
    public function __toString(): string
    {
        return $this->format();
    }

    // endregion

    // region Comparison methods
    #[Override]
    public function equal(mixed $other): bool
    {
        // Check for same types.
        if (!Types::same($this, $other)) {
            return false;
        }

        return $this->name === $other->name;
    }

    // endregion

    // region Helper methods

    /**
     * Check if a string contains only ASCII letters (a-z, A-Z).
     *
     * @param string $symbol The string to check.
     * @return bool True if the string contains only ASCII letters.
     */
    private static function isValidAsciiSymbol(string $symbol): bool
    {
        return (bool)preg_match('/^' . self::RX_ASCII_WORDS . '$/i', $symbol);
    }

    /**
     * Check if a string is a valid Unicode unit symbol.
     *
     * Valid symbols contain only Unicode letters or the degree (°), prime (′), or double prime (″) characters.
     *
     * @param string $symbol The string to check.
     * @return bool True if the string is a valid Unicode unit symbol.
     */
    private static function isValidUnicodeSymbol(string $symbol): bool
    {
        return (bool)preg_match('/^' . self::regex() . '$/iu', $symbol);
    }

    // endregion
}
