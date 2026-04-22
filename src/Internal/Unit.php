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
use InvalidArgumentException;
use Override;

/**
 * Represents a unit of measurement.
 */
class Unit implements UnitInterface
{
    use Equatable;

    // region Private constants

    /**
     * Letters that can appear in unit symbols.
     */
    public const string RX_ASCII_LETTERS = 'a-z';
    public const string RX_LETTERS = '\p{L}';

    /**
     * Unit symbols (prefixed or not) can be:
     * - A single word (ASCII or Unicode), e.g. 'km', 'MΩ'.
     * - A sequence of up to 3 ASCII words, e.g. 'US fl oz'.
     */
    private const string RX_ASCII_WORD = '[' . self::RX_ASCII_LETTERS . ']{1,7}';
    public const string RX_ASCII_WORDS = self::RX_ASCII_WORD . '(?: ' . self::RX_ASCII_WORD . '){0,2}';
    public const string RX_WORD = '[' . self::RX_LETTERS . ']{1,7}';

    /**
     * Non-letters that can be used as unit symbols. These cannot be combined with prefixes.
     */
    public const string RX_ASCII_NON_LETTERS = '\'"%$#';
    public const string RX_NON_ASCII_NON_LETTERS = '°′″‰\p{Sc}\p{So}';
    public const string RX_NON_LETTERS = self::RX_ASCII_NON_LETTERS . self::RX_NON_ASCII_NON_LETTERS;

    /**
     * A unique symbol format used by temperature units. These also cannot be combined with prefixes.
     */
    public const string RX_TEMPERATURE_SYMBOL = '°[A-Z]';

    // endregion

    // region UnitInterface properties

    /**
     * The ASCII unit symbol (e.g. 'm', 'g', 'Hz').
     * This symbol is mainly for parsing from code and must be ASCII.
     */
    public readonly string $asciiSymbol;

    /**
     * The Unicode symbol (e.g. 'Ω' for ohm, '°' for degree).
     * This symbol is mainly for display and can contain Unicode characters.
     */
    public readonly string $unicodeSymbol;

    /**
     * The dimension code (e.g. 'L', 'M', 'T-1').
     */
    public readonly string $dimension;

    // endregion

    // region Properties

    /**
     * The unit name (e.g. 'meter', 'gram', 'hertz').
     */
    public readonly string $name;

    /**
     * An additional symbol that will be accepted by the parser. It cannot accept prefixes.
     */
    public readonly ?string $alternateSymbol;

    /**
     * Bitwise flags indicating which prefixes are allowed (0 if none).
     */
    public readonly int $prefixGroup;

    /**
     * The measurement systems this unit belongs to.
     *
     * @var list<UnitSystem>
     */
    public readonly array $systems;

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
    private(set) ?array $allowedPrefixes = null {
        get => $this->allowedPrefixes ??= PrefixService::getPrefixes($this->prefixGroup);
    }

    /**
     * Symbol variants for this unit, including prefixed versions.
     *
     * Keyed by the full symbol string (e.g. 'km', 'μm', '°C'). Each value is a tuple of
     * [unitSymbol, prefixSymbol] where prefixSymbol is null for unprefixed variants.
     *
     * Cached on first access.
     *
     * @var ?array<string, array{string, ?string}>
     */
    private(set) ?array $symbols = null {
        get => $this->symbols ??= $this->generateSymbols();
    }

    /**
     * The quantity type this unit is for, if known.
     */
    private(set) false|null|QuantityType $quantityType = false {
        get {
            if ($this->quantityType === false) {
                $this->quantityType = QuantityTypeService::getByDimension($this->dimension);
            }
            return $this->quantityType;
        }
    }

    // endregion

    // region Constructor

    /**
     * Constructor.
     *
     * @param string $name The unit name (e.g. 'meter', 'gram').
     * @param string $asciiSymbol The ASCII symbol (e.g. 'm', 'g').
     * @param string $dimension The dimension code (e.g. 'L', 'M', 'T-1').
     * @param list<UnitSystem> $systems The measurement systems this unit belongs to.
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
        if (!self::isValidName($name)) {
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
        if ($unicodeSymbol !== null && !self::isValidUnicodeSymbol($unicodeSymbol)) {
            throw new FormatException("Invalid Unicode unit symbol: '$unicodeSymbol'.");
        }

        // Check if the alternate symbol contains a single ASCII non-letter symbol only.
        if ($alternateSymbol !== null && !self::isValidAlternateSymbol($alternateSymbol)) {
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
     * Parse the given symbol to return the matching Unit.
     *
     * Results are cached for efficiency.
     *
     * @param string $symbol The unit symbol to parse (e.g. 'm', 'kg', 'Hz').
     * @return self The matching Unit.
     * @throws FormatException If the symbol contains invalid characters.
     * @throws UnknownUnitException If the symbol is not recognized.
     */
    #[Override]
    public static function parse(string $symbol): self
    {
        // Maintain a cache of parsed units.
        static $cache = [];
        if (isset($cache[$symbol])) {
            return $cache[$symbol];
        }

        // Validate the symbol format.
        if (!self::isValidSymbol($symbol)) {
            throw new FormatException("Invalid unit symbol format: '$symbol'.");
        }

        // Get the unit from the registry.
        $unit = UnitService::getBySymbol($symbol);

        // If not found, throw an exception.
        if ($unit === null) {
            throw new UnknownUnitException($symbol);
        }

        // Remember the unit in the cache.
        $cache[$symbol] = $unit;

        return $unit;
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
     * @param Prefix $prefix The prefix to check.
     * @return bool True if the prefix is allowed.
     */
    public function acceptsPrefix(Prefix $prefix): bool
    {
        $allowedPrefixes = $this->allowedPrefixes;
        assert($allowedPrefixes !== null);
        return array_any($allowedPrefixes, static fn (Prefix $allowedPrefix) => $allowedPrefix->equal($prefix));
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
        return $other instanceof self && $this->name === $other->name;
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
    private static function isValidName(string $name): bool
    {
        // Allow letters (including Latin variants), spaces, and the right single quotation mark used by "Pa’anga"
        // (currency of Tonga).
        return $name !== '' && preg_match('/^[\p{Latin}’ ]+$/iu', $name);
    }

    /**
     * Check if a string is a valid ASCII unit symbol (unprefixed).
     *
     * @param string $symbol The string to check.
     * @return bool True if the string is a valid ASCII unit symbol.
     */
    private static function isValidAsciiSymbol(string $symbol): bool
    {
        return (bool)preg_match(
            '/^(?:(?:' . self::RX_ASCII_WORDS . ')|[' . self::RX_ASCII_NON_LETTERS . '])$/i',
            $symbol
        );
    }

    /**
     * Check if a string is a valid Unicode unit symbol (unprefixed).
     *
     * @param string $symbol The string to check.
     * @return bool True if the string is a valid Unicode unit symbol.
     */
    private static function isValidUnicodeSymbol(string $symbol): bool
    {
        return (bool)preg_match(
            '/^(?:[' . self::RX_LETTERS . self::RX_NON_LETTERS . ']|(?:' . self::RX_TEMPERATURE_SYMBOL . '))$/u',
            $symbol
        );
    }

    /**
     * Check if a string is a single character valid for use as an alternate unit symbol.
     *
     * @param string $symbol The string to check.
     * @return bool True if the string is a valid alternate unit symbol.
     */
    private static function isValidAlternateSymbol(string $symbol): bool
    {
        return (bool)preg_match('/^[' . self::RX_LETTERS . self::RX_NON_LETTERS . ']$/u', $symbol);
    }

    /**
     * Check if a string is a valid unit symbol (ASCII, Unicode, or alternate).
     *
     * @param string $symbol The string to check.
     * @return bool True if the string is a valid unit symbol.
     */
    private static function isValidSymbol(string $symbol): bool
    {
        return $symbol === ''
            || self::isValidAsciiSymbol($symbol)
            || self::isValidUnicodeSymbol($symbol)
            || self::isValidAlternateSymbol($symbol);
    }

    /**
     * Check if a string is a valid ASCII or Unicode letter.
     *
     * @param string $symbol The string to check.
     * @return bool True if the string is a single letter.
     * @internal
     */
    public static function isValidLetter(string $symbol): bool
    {
        return (bool)preg_match('/^[' . self::RX_LETTERS . ']$/u', $symbol);
    }

    /**
     * Check if a string is a valid ASCII or Unicode non-letter. This is used for formatting decisions.
     *
     * @param string $symbol The string to check.
     * @return bool True if the string is a single non-letter.
     * @internal
     */
    public static function isValidNonLetter(string $symbol): bool
    {
        return (bool)preg_match('/^[' . self::RX_NON_LETTERS . ']$/u', $symbol);
    }

    /**
     * Check if a string is a valid ASCII or Unicode word (max 7 letters).
     *
     * @param string $symbol The string to check.
     * @return bool True if the string is a word.
     * @internal
     */
    public static function isValidWord(string $symbol): bool
    {
        return (bool)preg_match('/^' . self::RX_WORD . '$/u', $symbol);
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
     * @internal
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
     * Helper method to add one symbol variant to the unit's symbol list.
     *
     * If $prefix is null, the unit symbol is added as-is, keyed by itself.
     * If $prefix is non-null, the prefix symbol is added only if the unit is dimensionless and the unit symbol contains
     * only letters. Unit symbols containing non-letter characters (e.g. '°C', '"') are silently skipped, since prefixes
     * can't meaningfully combine with them.
     *
     * @param array<string, array{string, ?string}> &$symbols The array to add the symbol to.
     * @param string $symbol The unit symbol to add.
     * @param ?string $prefix The prefix to apply to the symbol, or null for the unprefixed variant.
     */
    private function addSymbol(array &$symbols, string $symbol, ?string $prefix = null): void
    {
        if ($prefix === null) {
            // Add the symbol with no prefix.
            $symbols[$symbol] = [$symbol, null];
        } elseif ($this->dimension !== '' && self::isValidWord($symbol)) {
            // Add the symbol with a prefix only if the unit is not dimensionless and the symbol contains letters only.
            $symbols[$prefix . $symbol] = [$symbol, $prefix];
        }
    }

    /**
     * Helper method to add a unit symbol and all its prefixed variants to the unit's symbol list.
     *
     * Adds the unprefixed form, then one entry per supplied prefix × (ASCII, Unicode, alternate) combination.
     * Prefixed variants are skipped for dimensionless units and symbols that contain non-letters.
     *
     * @param array<string, array{string, ?string}> &$symbols The array to add symbols to.
     * @param string $unitSymbol The unit symbol to add (prefixed variants will be derived from this).
     * @param list<Prefix> $prefixes The prefixes to combine with the unit symbol.
     */
    private function addSymbols(array &$symbols, string $unitSymbol, array $prefixes): void
    {
        // Add unprefixed symbol.
        $this->addSymbol($symbols, $unitSymbol);

        // Add prefixed symbols.
        foreach ($prefixes as $prefix) {
            // Add symbol with ASCII prefix.
            $this->addSymbol($symbols, $unitSymbol, $prefix->asciiSymbol);

            // Add symbol with Unicode prefix, if different to the ASCII symbol.
            if ($prefix->unicodeSymbol !== $prefix->asciiSymbol) {
                $this->addSymbol($symbols, $unitSymbol, $prefix->unicodeSymbol);
            }

            // Add symbol with alternate prefix, if set.
            if ($prefix->alternateSymbol !== null) {
                $this->addSymbol($symbols, $unitSymbol, $prefix->alternateSymbol);
            }
        }
    }

    /**
     * Generate the symbol variants for this unit, including prefixed forms.
     *
     * Produces an entry for the unprefixed ASCII symbol, for the Unicode symbol (if distinct from the ASCII one),
     * and for the alternate symbol (if set). Each is combined with every allowed prefix via addSymbols().
     *
     * @return array<string, array{string, ?string}> Keyed by the full symbol, values are [unitSymbol, prefixSymbol]
     * tuples where prefixSymbol is null for unprefixed variants.
     */
    private function generateSymbols(): array
    {
        // Initialize result array.
        $symbols = [];

        // Get the allowed prefixes for this unit.
        $prefixes = $this->allowedPrefixes;
        assert($prefixes !== null);

        // Add the unprefixed and prefixed ASCII symbol.
        $this->addSymbols($symbols, $this->asciiSymbol, $prefixes);

        // Add unprefixed and prefixed Unicode symbol, if different to the ASCII symbol.
        if ($this->unicodeSymbol !== $this->asciiSymbol) {
            $this->addSymbols($symbols, $this->unicodeSymbol, $prefixes);
        }

        // Add unprefixed and prefixed alternate symbol, if set.
        if ($this->alternateSymbol !== null) {
            $this->addSymbols($symbols, $this->alternateSymbol, $prefixes);
        }

        return $symbols;
    }

    // endregion
}
