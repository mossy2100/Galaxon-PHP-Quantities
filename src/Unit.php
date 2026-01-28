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
     * The quantity type, e.g. 'length'.
     */
    private(set) string $quantityType;

    /**
     * The dimension code (e.g. 'L', 'M', 'T-1').
     */
    private(set) string $dimension;

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

    /**
     * The measurement systems this unit belongs to.
     *
     * @var list<System>
     */
    private(set) array $systems;

    // endregion

    // region Property hooks

    // phpcs:disable PSR2.Classes.PropertyDeclaration
    // phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact

    /**
     * The expansion unit.
     */
    public ?DerivedUnit $expansionUnit {
        get {
            if ($this->expansionUnitSymbol === null) {
                return null;
            }

            // Convert the expansion unit symbol into a DerivedUnit if not already done, and the expansion unit symbol
            // is valid.
            if ($this->expansionUnit === null) {
                try {
                    $this->expansionUnit = DerivedUnit::parse($this->expansionUnitSymbol);
                } catch (DomainException) {
                    return null;
                }
            }

            return $this->expansionUnit;
        }
    }

    /**
     * Get all allowed prefixes for this unit.
     *
     * @var list<Prefix>
     */
    public array $allowedPrefixes {
        get => PrefixRegistry::getPrefixes($this->prefixGroup);
    }

    /**
     * Get all symbol variants for a unit, including prefixed versions.
     *
     * @var list<string> All symbol variants.
     */
    public array $symbols {
        get {
            // Add ASCII symbol.
            $symbols = [$this->asciiSymbol];

            // Add Unicode symbol, if different.
            if ($this->unicodeSymbol !== $this->asciiSymbol) {
                $symbols[] = $this->unicodeSymbol;
            }

            // Add prefixed symbols.
            $prefixes = $this->allowedPrefixes;
            foreach ($prefixes as $prefix) {
                // Add prefixed ASCII symbols.
                $symbols[] = $prefix->asciiSymbol . $this->asciiSymbol;
                if ($prefix->unicodeSymbol !== $prefix->asciiSymbol) {
                    $symbols[] = $prefix->unicodeSymbol . $this->asciiSymbol;
                }

                // Add prefixed Unicode symbols, if different.
                if ($this->unicodeSymbol !== $this->asciiSymbol) {
                    $symbols[] = $prefix->asciiSymbol . $this->unicodeSymbol;
                    if ($prefix->unicodeSymbol !== $prefix->asciiSymbol) {
                        $symbols[] = $prefix->unicodeSymbol . $this->unicodeSymbol;
                    }
                }
            }

            return $symbols;
        }
    }

    // phpcs:enable PSR2.Classes.PropertyDeclaration
    // phpcs:enable Generic.WhiteSpace.ScopeIndent.IncorrectExact

    // endregion

    // region Constructor

    /**
     * Constructor.
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
     * @throws FormatException If the unit symbols contain invalid characters.
     * @throws DomainException If the dimension code is invalid.
     */
    public function __construct(
        string $name,
        string $asciiSymbol,
        ?string $unicodeSymbol,
        string $quantityType,
        string $dimension,
        int $prefixGroup = 0,
        ?string $expansionUnitSymbol = null,
        ?float $expansionValue = null,
        array $systems = []
    ) {
        // Check ASCII symbol contains ASCII letters only.
        if (!self::isValidAsciiSymbol($asciiSymbol)) {
            throw new FormatException("Unit symbol '$asciiSymbol' must only contain ASCII letters.");
        }

        // Validate Unicode symbol.
        if (isset($unicodeSymbol) && !self::isValidUnicodeSymbol($unicodeSymbol)) {
            throw new FormatException(
                "Unit symbol '$unicodeSymbol' must only contain letters, or the degree, prime, or " .
                'double prime characters (i.e. °′″).'
            );
        }

        // Set the properties.
        $this->name = $name;
        $this->asciiSymbol = $asciiSymbol;
        $this->unicodeSymbol = $unicodeSymbol ?? $asciiSymbol;
        $this->quantityType = $quantityType;
        $this->dimension = DimensionRegistry::normalize($dimension);
        $this->prefixGroup = $prefixGroup;
        $this->expansionUnitSymbol = $expansionUnitSymbol ?? null;
        $this->expansionValue = isset($expansionUnitSymbol) ? ($expansionValue ?? 1.0) : null;
        $this->systems = $systems;
    }

    // endregion

    // region Accessors

    // endregion

    // region System methods

    /**
     * Check if this unit belongs to a specific measurement system.
     *
     * @param System $system The system to check.
     * @return bool True if the unit belongs to the system.
     */
    public function belongsToSystem(System $system): bool
    {
        return in_array($system, $this->systems, true);
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
     * @param Prefix $prefix The prefix to check.
     * @return bool True if the prefix is allowed.
     */
    public function acceptsPrefix(Prefix $prefix): bool
    {
        return array_any($this->allowedPrefixes, static fn ($allowedPrefix) => $allowedPrefix->equal($prefix));
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
        return '([°′″]|°[a-z]|\p{L}+|' . self::RX_ASCII_WORDS . ')';
    }

    /**
     * Parse a unit symbol and return the matching Unit.
     *
     * @param string $symbol The unit symbol to parse (e.g. 'm', 'kg', 'Hz').
     * @return self The matching Unit.
     * @throws FormatException If the symbol contains invalid characters.
     * @throws DomainException If the symbol is not recognized.
     */
    #[Override]
    public static function parse(string $symbol): self
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
        return $other instanceof self && $this->asciiSymbol === $other->asciiSymbol;
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
