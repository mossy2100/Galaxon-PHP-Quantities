<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Internal;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Core\Traits\Comparison\Equatable;
use Galaxon\Quantities\Exceptions\UnknownUnitException;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Services\DimensionService;
use Galaxon\Quantities\Services\PrefixService;
use Galaxon\Quantities\Services\QuantityTypeService;
use Galaxon\Quantities\Services\UnitService;
use Galaxon\Quantities\UnitSystem;
use InvalidArgumentException;
use Override;

/**
 * Represents a unit of measurement.
 */
class Unit implements UnitInterface
{
    use Equatable;

    // region Private constants

    private const string RX_ASCII_LETTER = '[a-z]';

    private const string RX_ASCII_WORD = self::RX_ASCII_LETTER . '+';

    private const string RX_ASCII_WORDS = self::RX_ASCII_WORD . '(?: ' . self::RX_ASCII_WORD . '){0,2}';

    private const string RX_ASCII_SPECIAL_CHR = "[!-'?@`]";

    private const string RX_ASCII_SYMBOL = self::RX_ASCII_WORDS . '|' . self::RX_ASCII_SPECIAL_CHR;

    private const string RX_UNICODE_LETTER = '\p{L}';

    private const string RX_TEMPERATURE_SYMBOL = '°[a-z]';

    private const string RX_UNICODE_SYMBOL =
        self::RX_UNICODE_LETTER . '|' . self::RX_UNICODE_SPECIAL_CHR . '|' . self::RX_TEMPERATURE_SYMBOL;

    // endregion

    // region Public constants

    public const string RX_UNICODE_SPECIAL_CHR = "[!-'?@`′″‰\p{So}\p{Sc}]";

    public const string RX_UNIT = self::RX_ASCII_WORDS . '|' . self::RX_UNICODE_SYMBOL;

    public const string RX_PREFIX = '[a-z]{1,2}|' . self::RX_UNICODE_LETTER;

    // endregion

    // region Public properties

    /**
     * The unit name (e.g. 'meter', 'gram', 'hertz').
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
     * An additional symbol that will be accepted by the parser. It cannot accept prefixes.
     */
    private(set) ?string $alternateSymbol = null;

    /**
     * The dimension code (e.g. 'L', 'M', 'T-1').
     */
    private(set) string $dimension;

    /**
     * Bitwise flags indicating which prefixes are allowed (0 if none).
     */
    private(set) int $prefixGroup;

    /**
     * The measurement systems this unit belongs to.
     *
     * @var list<UnitSystem>
     */
    private(set) array $systems;

    // endregion

    // region Private properties

    /**
     * The expansion quantity, if one exists and is known.
     */
    private ?Quantity $expansion = null;

    // endregion

    // region Property hooks

    /**
     * Allowed prefixes for this unit.
     *
     * @var list<Prefix>
     */
    public array $allowedPrefixes {
        get => PrefixService::getPrefixes($this->prefixGroup);
    }

    /**
     * Symbol variants for this unit, including prefixed versions.
     *
     * Keyed by the full symbol string (e.g. 'km', 'μm', '°C'). Each value is a tuple of
     * [unitSymbol, prefixSymbol] where prefixSymbol is null for unprefixed variants.
     *
     * Cached on first access.
     *
     * @var array<string, array{string, ?string}>
     */
    private(set) array $symbols = [] {
        get {
            // Return cached result if available.
            if ($this->symbols !== []) {
                return $this->symbols;
            }

            // Initialize result array.
            $symbols = [];

            // Add ASCII symbol.
            self::addSymbol($symbols, $this->asciiSymbol);

            // Add the Unicode symbol, if different.
            if ($this->unicodeSymbol !== $this->asciiSymbol) {
                self::addSymbol($symbols, $this->unicodeSymbol);
            }

            // Add alternate symbol, if set and different.
            if (
                $this->alternateSymbol !== null &&
                $this->alternateSymbol !== $this->asciiSymbol &&
                $this->alternateSymbol !== $this->unicodeSymbol
            ) {
                self::addSymbol($symbols, $this->alternateSymbol);
            }

            // Add prefixed symbols.
            $prefixes = $this->allowedPrefixes;
            foreach ($prefixes as $prefix) {
                // Add prefixed ASCII symbols.
                self::addSymbol($symbols, $this->asciiSymbol, $prefix->asciiSymbol);
                if ($prefix->unicodeSymbol !== $prefix->asciiSymbol) {
                    self::addSymbol($symbols, $this->asciiSymbol, $prefix->unicodeSymbol);
                }

                // Add prefixed Unicode symbols, if different.
                if ($this->unicodeSymbol !== $this->asciiSymbol) {
                    self::addSymbol($symbols, $this->unicodeSymbol, $prefix->asciiSymbol);
                    if ($prefix->unicodeSymbol !== $prefix->asciiSymbol) {
                        self::addSymbol($symbols, $this->unicodeSymbol, $prefix->unicodeSymbol);
                    }
                }
            }

            $this->symbols = $symbols;
            return $symbols;
        }
    }

    /**
     * The quantity type this unit is for, if known.
     */
    public ?QuantityType $quantityType {
        get => QuantityTypeService::getByDimension($this->dimension);
    }

    // endregion

    // region Constructor

    /**
     * Constructor.
     *
     * @param string $name The unit name (e.g. 'meter', 'gram').
     * @param string $asciiSymbol The ASCII symbol (e.g. 'm', 'g').
     * @param string $dimension The dimension code (e.g. 'L', 'M', 'T-1').
     * @param UnitSystem|list<UnitSystem> $systems The measurement systems this unit belongs to.
     * @param int $prefixGroup Bitwise flags indicating which prefixes are allowed (0 if none).
     * @param ?string $unicodeSymbol The Unicode symbol (e.g. 'Ω'), or null if it's the same as ASCII.
     * @param ?string $alternateSymbol An additional symbol that will be accepted by the parser, or null.
     * @throws FormatException If the unit symbols contain invalid characters.
     * @throws DomainException If the dimension code or systems are invalid.
     * @throws InvalidArgumentException If the systems array contains non-UnitSystem values.
     */
    public function __construct(
        string $name,
        string $asciiSymbol,
        string $dimension,
        array $systems = [UnitSystem::Custom],
        int $prefixGroup = 0,
        ?string $unicodeSymbol = null,
        ?string $alternateSymbol = null
    ) {
        // Check the name is non-empty, ASCII, and up to 3 words.
        $name = trim($name);
        if (!self::isValidUnitName($name)) {
            throw new FormatException("Invalid unit name: '$name'.");
        }

        // Check ASCII symbol contains ASCII letters only (empty is allowed for dimensionless/scalar).
        if ($asciiSymbol !== '' && !self::isValidAsciiSymbol($asciiSymbol)) {
            throw new FormatException("Invalid ASCII unit symbol: '$asciiSymbol'.");
        }

        // Make sure the $systems array is a non-empty array of UnitSystem values.
        if (empty($systems)) {
            throw new DomainException('Cannot create a unit with no measurement systems.');
        }
        $systems = array_values(array_unique($systems, SORT_REGULAR));
        foreach ($systems as $system) {
            if (!$system instanceof UnitSystem) {
                throw new InvalidArgumentException(
                    'Cannot create a unit with non-UnitSystem values in the systems array.'
                );
            }
        }

        // Validate prefix group.
        if ($prefixGroup < 0 || $prefixGroup > PrefixService::GROUP_ALL) {
            throw new DomainException("Invalid prefix group: $prefixGroup.");
        }

        // Validate Unicode symbol.
        if (isset($unicodeSymbol) && !self::isValidUnicodeSymbol($unicodeSymbol)) {
            throw new FormatException("Invalid Unicode unit symbol: '$unicodeSymbol'.");
        }

        // Check if the alternate symbol contains a single ASCII non-letter symbol only.
        if (isset($alternateSymbol) && !self::isValidAlternateSymbol($alternateSymbol)) {
            throw new FormatException("Invalid alternate unit symbol: '$alternateSymbol'.");
        }

        // Set the properties.
        $this->name = $name;
        $this->asciiSymbol = $asciiSymbol;
        $this->dimension = DimensionService::normalize($dimension);
        $this->systems = $systems;
        $this->prefixGroup = $prefixGroup;
        $this->unicodeSymbol = $unicodeSymbol ?? $asciiSymbol;
        $this->alternateSymbol = $alternateSymbol;
    }

    // endregion

    // region Factory methods

    /**
     * Parse a unit symbol and return the matching Unit.
     *
     * @param string $symbol The unit symbol to parse (e.g. 'm', 'kg', 'Hz').
     * @return self The matching Unit.
     * @throws FormatException If the symbol contains invalid characters.
     * @throws UnknownUnitException If the symbol is not recognized.
     */
    #[Override]
    public static function parse(string $symbol): self
    {
        // Validate the symbol format.
        if ($symbol !== '' && !self::isValidUnitSymbol($symbol)) {
            throw new FormatException(
                "Unit symbol '$symbol' can only contain letters and special characters (e.g. °′″'\")."
            );
        }

        // Get the unit from the registry.
        $unit = UnitService::getBySymbol($symbol);

        // If not found, throw an exception.
        return $unit ?? throw new UnknownUnitException($symbol);
    }

    // endregion

    // region Inspection methods

    /**
     * Check if this unit belongs to a specific measurement system.
     *
     * @param UnitSystem $system The system to check.
     * @return bool True if the unit belongs to the system.
     */
    public function belongsToSystem(UnitSystem $system): bool
    {
        return in_array($system, $this->systems, true);
    }

    /**
     * Check if this unit belongs to the SI system.
     *
     * @return bool True if the unit is an SI unit.
     */
    public function isSi(): bool
    {
        return $this->belongsToSystem(UnitSystem::Si);
    }

    /**
     * Check if this unit is a base unit.
     *
     * @return bool True if the unit is a base unit.
     */
    public function isBase(): bool
    {
        // Check if the dimension is a single letter (e.g. 'L') or '' (dimensionless).
        return strlen($this->dimension) <= 1;
    }

    /**
     * Check if a specific prefix is allowed for this unit.
     *
     * @param string|Prefix $prefix The prefix to check.
     * @return bool True if the prefix is allowed.
     */
    public function acceptsPrefix(string|Prefix $prefix): bool
    {
        // Convert the prefix to a Prefix object if needed.
        if (is_string($prefix)) {
            $prefix = PrefixService::getBySymbol($prefix);
        }

        return array_any($this->allowedPrefixes, static fn (Prefix $allowedPrefix) => $allowedPrefix->equal($prefix));
    }

    // endregion

    // region Comparison methods

    /**
     * Check if this Unit is equal to another.
     *
     * @param mixed $other The other value to compare.
     * @return bool True if equal, false otherwise.
     */
    #[Override]
    public function equal(mixed $other): bool
    {
        return $other instanceof self && $this->asciiSymbol === $other->asciiSymbol;
    }

    // endregion

    // region Conversion methods

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

    // region Validation methods

    /**
     * Check if a string is a valid unit name.
     *
     * @param string $name The string to check.
     * @return bool True if the string is a valid unit name.
     */
    private static function isValidUnitName(string $name): bool
    {
        return $name !== '' && preg_match('/^[\p{L}\p{P} ]+$/iu', $name);
    }

    /**
     * Check if a string is a valid ASCII unit symbol.
     *
     * @param string $symbol The string to check.
     * @return bool True if the string is a valid ASCII unit symbol.
     */
    private static function isValidAsciiSymbol(string $symbol): bool
    {
        return (bool)preg_match('/^(' . self::RX_ASCII_SYMBOL . ')$/i', $symbol);
    }

    /**
     * Check if a string is a valid Unicode unit symbol.
     *
     * @param string $symbol The string to check.
     * @return bool True if the string is a valid Unicode unit symbol.
     */
    private static function isValidUnicodeSymbol(string $symbol): bool
    {
        return (bool)preg_match('/^(' . self::RX_UNICODE_SYMBOL . ')$/iu', $symbol);
    }

    /**
     * Check if a string is a single ASCII character valid for use as an alternate unit symbol.
     *
     * @param string $symbol The string to check.
     * @return bool True if the string is a valid alternate unit symbol.
     */
    private static function isValidAlternateSymbol(string $symbol): bool
    {
        return (bool)preg_match('/^(' . self::RX_ASCII_LETTER . '|' . self::RX_ASCII_SPECIAL_CHR . ')$/i', $symbol);
    }

    /**
     * Check if a string is a valid unit symbol (ASCII, Unicode, or alternate).
     *
     * @param string $symbol The string to check.
     * @return bool True if the string is a valid unit symbol.
     */
    public static function isValidUnitSymbol(string $symbol): bool
    {
        return self::isValidAsciiSymbol($symbol) ||
            self::isValidUnicodeSymbol($symbol) ||
            self::isValidAlternateSymbol($symbol);
    }

    // endregion

    // region Helper methods

    /**
     * Attempt to expand this unit into base units.
     *
     * That relies on a conversion from a non-base unit to a base unit.
     * If the provided unit is a base unit, or if no expansion quantity is found, return null.
     * A conversion with a factor of 1 is a direct expansion and is preferred.
     * If not found, the conversion with the least relative error will be used.
     *
     * Note, new expansion conversions can be discovered. For example, an expansion of eV is not defined, but there is a
     * conversion from eV to J, which has an expansion to kg*m2/s2. Therefore, even though the first time this method is
     * called for a unit, there might not be an expansion conversion, the next time there might be.
     *
     * @return ?Quantity The expansion as a Quantity with base units, or null if none found.
     */
    public function tryExpand(): ?Quantity
    {
        // Check if we found the expansion already.
        if ($this->expansion !== null) {
            return $this->expansion;
        }

        // Base units cannot be expanded.
        if ($this->isBase()) {
            return null;
        }

        // Find all conversions originating from this unit.
        $converter = Converter::getInstance($this->dimension);
        $conversionList = $converter->conversionMatrix[$this->asciiSymbol] ?? null;
        if ($conversionList === null) {
            return null;
        }

        // Scan the conversions looking for suitable expansion conversions, which means any conversion to a base unit.
        $bestConversion = null;
        $minErr = INF;
        foreach ($conversionList as $conversion) {
            if ($conversion->destUnit->isBase()) {
                // Check for a unity expansion.
                if ($conversion->factor->value === 1.0) {
                    // This is the best match, no need to keep looking.
                    $bestConversion = $conversion;
                    break;
                }

                // See if this is an improvement.
                if ($conversion->factor->relativeError < $minErr) {
                    $minErr = $conversion->factor->relativeError;
                    $bestConversion = $conversion;
                }
            }
        }

        // If an expansion conversion was found, convert it to a Quantity and cache it in the private property.
        if ($bestConversion !== null) {
            $this->expansion = Quantity::create($bestConversion->factor->value, $bestConversion->destUnit);
        }

        return $this->expansion;
    }

    /**
     * Helper method to add a symbol to the unit's symbol list.
     *
     * @param array<string, array{string, ?string}> &$symbols The array to add the symbol to.
     * @param string $symbol The symbol to add.
     * @param string|null $prefix The prefix for the symbol, if any.
     */
    private static function addSymbol(array &$symbols, string $symbol, ?string $prefix = null): void
    {
        $symbols[$prefix . $symbol] = [$symbol, $prefix];
    }

    // endregion
}
