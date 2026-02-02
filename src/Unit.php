<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Core\Traits\Equatable;
use Galaxon\Quantities\Helpers\DimensionUtils;
use Galaxon\Quantities\Helpers\PrefixUtils;
use Galaxon\Quantities\Helpers\UnitRegistry;
use Override;

/**
 * Represents a unit of measurement.
 */
class Unit implements UnitInterface
{
    use Equatable;

    // region Constants

    private const string RX_ASCII_WORD = '[a-z]+';

    private const string RX_ASCII_SYMBOL = '|%|' . self::RX_ASCII_WORD . '(?: ' . self::RX_ASCII_WORD . '){0,2}';

    private const string RX_NON_LETTER_SYMBOL = '[\p{Po}\p{So}\p{Sc}]';

    private const string RX_TEMPERATURE_SYMBOL = '°[a-z]';

    private const string RX_UNICODE_WORD = '\p{L}+';

    private const string RX_UNICODE_SYMBOL =
        self::RX_NON_LETTER_SYMBOL . '|' . self::RX_TEMPERATURE_SYMBOL . '|' . self::RX_UNICODE_WORD;

    // endregion Constants

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
     * This symbol is mainly for display and can contain Unicode character.
     */
    private(set) string $unicodeSymbol;

    /**
     * An additional symbol that will be accepted by the parser. It cannot accept prefixes.
     */
    private(set) ?string $alternateSymbol = null;

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

    /**
     * The expansion unit.
     */
    public ?DerivedUnit $expansionUnit = null {
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
        get => PrefixUtils::getPrefixes($this->prefixGroup);
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

            // Add alternate symbol, if set and different.
            if ($this->alternateSymbol !== null && $this->alternateSymbol !== $this->asciiSymbol) {
                $symbols[] = $this->alternateSymbol;
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
     * @param ?string $alternateSymbol An additional symbol that will be accepted by the parser, or null.
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
        ?string $alternateSymbol = null,
        ?string $expansionUnitSymbol = null,
        ?float $expansionValue = null,
        array $systems = []
    ) {
        // Check ASCII symbol contains ASCII letters only.
        if (!self::isValidAsciiSymbol($asciiSymbol)) {
            throw new FormatException(
                "Unit symbol '$asciiSymbol' must only contain ASCII letters. " .
                'Up to three words are allowed, separated by single spaces.'
            );
        }

        // Validate Unicode symbol.
        if (isset($unicodeSymbol) && !self::isValidUnicodeSymbol($unicodeSymbol)) {
            throw new FormatException(
                "Unit symbol '$unicodeSymbol' must only contain letters, or punctuation or mathematical " .
                'symbols (e.g. °′″%).'
            );
        }

        // Set the properties.
        $this->name = $name;
        $this->asciiSymbol = $asciiSymbol;
        $this->unicodeSymbol = $unicodeSymbol ?? $asciiSymbol;
        $this->quantityType = $quantityType;
        $this->dimension = DimensionUtils::normalize($dimension);
        $this->prefixGroup = $prefixGroup;
        $this->alternateSymbol = $alternateSymbol;
        $this->expansionUnitSymbol = $expansionUnitSymbol ?? null;
        $this->expansionValue = isset($expansionUnitSymbol) ? ($expansionValue ?? 1.0) : null;
        $this->systems = $systems;
    }

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

    // region String methods

    public static function regex(): string
    {
        return '(' . self::RX_NON_LETTER_SYMBOL . '|°[a-z]|\p{L}+|' . self::RX_ASCII_SYMBOL . ')';
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
                "Unit symbol '$symbol' can only contain letters, or the degree, prime, double prime, single " .
                "quote, or double quote characters (i.e. °′″'\")."
            );
        }

        // Get the unit from the registry.
        $unit = UnitRegistry::getBySymbol($symbol);

        // If not found, throw an exception.
        return $unit ?? throw new DomainException("Unknown unit symbol '$symbol'.");
    }

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
     * Check if a string is a single non-letter, non-digit, non-space symbol (punctuation or other).
     *
     * @param string $symbol The string to check.
     * @return bool True if the string contains only a single non-letter symbol.
     */
    public static function isValidNonLetterSymbol(string $symbol): bool
    {
        return (bool)preg_match('/^' . self::RX_NON_LETTER_SYMBOL . '$/iu', $symbol);
    }

    /**
     * Check if a string contains only ASCII letters (a-z, A-Z).
     *
     * @param string $symbol The string to check.
     * @return bool True if the string contains only ASCII letters.
     */
    public static function isValidAsciiSymbol(string $symbol): bool
    {
        return (bool)preg_match('/^(' . self::RX_ASCII_SYMBOL . ')$/i', $symbol);
    }

    /**
     * Check if a string is a valid Unicode unit symbol.
     *
     * Valid symbols contain only ASCII or Unicode letters or punctuation or mathematical symbols.
     *
     * @param string $symbol The string to check.
     * @return bool True if the string is a valid Unicode unit symbol.
     */
    public static function isValidUnicodeSymbol(string $symbol): bool
    {
        return (bool)preg_match('/^(' . self::RX_UNICODE_SYMBOL . ')$/iu', $symbol);
    }

    // endregion
}
