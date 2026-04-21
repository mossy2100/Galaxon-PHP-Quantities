<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Internal;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Core\Integers;
use Galaxon\Core\Traits\Comparison\Equatable;
use Galaxon\Quantities\Exceptions\UnknownUnitException;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Services\DimensionService;
use Galaxon\Quantities\Services\PrefixService;
use Galaxon\Quantities\Services\QuantityTypeService;
use Galaxon\Quantities\Services\UnitService;
use Override;

/**
 * Represents a decomposed unit symbol.
 *
 * A unit symbol like 'km2' is decomposed into:
 * - unit: The unit object.
 * - prefix: The SI/binary prefix object.
 * - exponent: The power (2).
 *
 * Computed properties:
 * - asciiSymbol: The full unit term symbol in ASCII form ('km2').
 * - unicodeSymbol: The full unit term symbol in Unicode form with superscript exponent ('km²').
 * - unprefixedAsciiSymbol: The unit symbol without its prefix, in ASCII form ('m2').
 * - unexponentiatedAsciiSymbol: The unit symbol without its exponent, in ASCII form ('km').
 * - prefixMultiplier: The prefix multiplier alone (1000 for km).
 * - multiplier: The prefix multiplier raised to the exponent (1000² = 1e6 for km²).
 * - dimension: The dimension code with exponent applied.
 * - quantityType: The registered quantity type for this dimension, or null if none.
 */
class UnitTerm implements UnitInterface
{
    use Equatable;

    // region UnitInterface properties

    /**
     * The ASCII version of the unit term symbol, including prefix and exponent if set (e.g. 'deg', 'm2', 'MN', 's-1').
     */
    public string $asciiSymbol {
        get => $this->format(true);
    }

    /**
     * The Unicode version of the unit term symbol, including prefix and exponent if set (e.g. 'µs', 'mΩ').
     * The exponent will be formatted using superscript (e.g. 'km²', 'ms⁻¹').
     */
    public string $unicodeSymbol {
        get => $this->format();
    }

    /**
     * The dimension code.
     */
    public string $dimension {
        get => DimensionService::pow($this->unit->dimension, $this->exponent);
    }

    // endregion

    // region Public properties

    /**
     * The unit.
     */
    public readonly Unit $unit;

    /**
     * The SI/binary prefix or null if none.
     */
    public readonly ?Prefix $prefix;

    /**
     * The exponent (e.g. 2 for m², -1 for s⁻¹).
     */
    public readonly int $exponent;

    // endregion

    // region Private properties

    /**
     * The expansion quantity, if one exists and is known.
     */
    private ?Quantity $expansion = null;

    // endregion

    // region Property hooks

    /**
     * The unprefixed unit term symbol (e.g. 'm2', 's-1').
     * This property returns the ASCII version. For the Unicode symbol, cast to string (__toString()).
     */
    private(set) ?string $unprefixedAsciiSymbol = null {
        get => $this->unprefixedAsciiSymbol ??=
            $this->unit->asciiSymbol . ($this->exponent === 1 ? '' : $this->exponent);
    }

    /**
     * The unit term with no exponent (e.g. 'km', 's').
     * This property returns the ASCII version. For the Unicode symbol, cast to string (__toString()).
     */
    private(set) ?string $unexponentiatedAsciiSymbol = null {
        get => $this->unexponentiatedAsciiSymbol ??= $this->prefix?->asciiSymbol . $this->unit->asciiSymbol;
    }

    /**
     * The prefix multiplier.
     */
    private(set) ?float $prefixMultiplier = null {
        get => $this->prefixMultiplier ??= $this->prefix === null ? 1.0 : $this->prefix->multiplier;
    }

    /**
     * The prefix multiplier raised to the exponent (e.g. 1000² = 1e6 for km²).
     */
    private(set) ?float $multiplier = null {
        get => $this->multiplier ??= $this->prefixMultiplier ** $this->exponent;
    }

    /**
     * The quantity type this unit term is for, if known.
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
     * The exponent must be in the range -9..9, excluding 0.
     *
     * @param string|Unit $unit The unit or its symbol (e.g. 'm', 'ft', 'N').
     * @param null|string|Prefix $prefix The prefix symbol or object, or null if none.
     * @param int $exponent The exponent (default 1). Must be in -9..9 and not 0.
     * @throws UnknownUnitException If the unit symbol is not recognized.
     * @throws DomainException If the exponent or prefix is invalid.
     */
    public function __construct(string|Unit $unit = '', null|string|Prefix $prefix = null, int $exponent = 1)
    {
        // Allow for the unit to be provided as a symbol.
        if (is_string($unit)) {
            $symbol = $unit;
            $unit = UnitService::getBySymbol($symbol);
            if ($unit === null) {
                throw new UnknownUnitException($symbol);
            }
        }

        // Allow for the prefix to be provided as a symbol.
        if (is_string($prefix)) {
            $prefixSymbol = $prefix;
            $prefix = PrefixService::getBySymbol($prefix);
            if ($prefix === null) {
                throw new DomainException("Prefix '$prefixSymbol' is unknown.");
            }
        }

        // Validate prefix.
        if ($prefix !== null && !$unit->acceptsPrefix($prefix)) {
            throw new DomainException("Prefix '$prefix' is invalid for unit '$unit->asciiSymbol'.");
        }

        // Validate exponent.
        if ($exponent < -9 || $exponent > 9) {
            throw new DomainException("Exponent $exponent is outside the valid range -9 to 9.");
        }
        if ($exponent === 0) {
            throw new DomainException('Cannot have an exponent of 0.');
        }

        // Set properties.
        $this->unit = $unit;
        $this->prefix = $prefix;
        $this->exponent = $exponent;
    }

    // endregion

    // region Factory methods

    /**
     * Convert the argument to a UnitTerm if necessary.
     *
     * @param string|Unit|self $value The value to convert.
     * @return self The equivalent UnitTerm object.
     * @throws FormatException If a string is provided with an invalid format.
     * @throws UnknownUnitException If a string or Unit symbol is not recognized.
     * @throws DomainException If the exponent or prefix is invalid.
     */
    public static function toUnitTerm(string|Unit|self $value): self
    {
        // If the value is already a UnitTerm, return it as is.
        if ($value instanceof self) {
            return $value;
        }

        // If the value is a string, parse it.
        if (is_string($value)) {
            return self::parse($value);
        }

        // Otherwise, construct a new UnitTerm.
        return new self($value);
    }

    /**
     * Parses the given symbol to return the matching UnitTerm.
     *
     * Results are cached for efficiency.
     *
     * An explicit exponent of 1 (e.g. 'm1') is accepted but non-canonical — write 'm' instead. Exponents of 0 and
     * those outside the range -9..9 are rejected by the constructor.
     *
     * @param string $symbol The unit symbol with an optional prefix and/or exponent (e.g. 'm2', 's-1').
     * @return self The parsed unit term.
     * @throws FormatException If the format is invalid, or if a prefix or non-unity exponent is combined with a
     * non-letter or dimensionless unit.
     * @throws UnknownUnitException If the unit symbol is not recognized.
     * @throws DomainException If the exponent is 0 or outside the range -9..9.
     */
    public static function parse(string $symbol): self
    {
        // Maintain a cache of parsed unit terms.
        static $cache = [];
        if (isset($cache[$symbol])) {
            return $cache[$symbol];
        }

        // An empty string is a dimensionless scalar.
        if ($symbol === '') {
            $unitTerm = new self();
            $cache[$symbol] = $unitTerm;
            return $unitTerm;
        }

        // Validate the format.
        if (!self::isValidSymbol($symbol, $matches)) {
            throw new FormatException("Invalid unit term symbol format: '$symbol'.");
        }

        // Get the prefixed unit symbol.
        assert(isset($matches[1]));
        $prefixedUnitSymbol = $matches[1];

        // Get the exponent.
        if (!isset($matches[2]) || $matches[2] === '') {
            // Default.
            $exp = 1;
        } else {
            // Accept either ASCII or superscript exponents.
            $expStr = $matches[2];
            $exp = Integers::isSuperscript($expStr) ? Integers::fromSuperscript($expStr) : (int)$expStr;

            // Disallow explicit '1'.
            // NB: Invalid exponents (0, and any outside the range of -9..9) are checked by the constructor.
            if ($exp === 1) {
                throw new DomainException('Cannot have an exponent of 1.');
            }
        }

        // Search for a matching unit symbol.
        foreach (UnitService::getAll() as $unit) {
            if (array_key_exists($prefixedUnitSymbol, $unit->symbols)) {
                // Found a match. Get the unit symbol and prefix.
                [$unitSymbol, $prefixSymbol] = $unit->symbols[$prefixedUnitSymbol];

                // Units that contain non-letters or that are dimensionless may not have an exponent.
                if ($exp !== 1) {
                    // Check unit symbol contains only letters.
                    if (!Unit::isValidWord($unitSymbol)) {
                        throw new FormatException(
                            "Invalid unit term symbol: '$symbol'. Units containing non-letters cannot have exponents."
                        );
                    }

                    // Check unit is not dimensionless.
                    if ($unit->dimension === '') {
                        throw new FormatException(
                            "Invalid unit term symbol: '$symbol'. Dimensionless units cannot have exponents."
                        );
                    }
                }

                // Construct, cache, and return the result.
                $unitTerm = new self($unitSymbol, $prefixSymbol, $exp);
                $cache[$symbol] = $unitTerm;
                return $unitTerm;
            }
        }

        // No match was found; the unit is invalid or unknown.
        throw new UnknownUnitException($symbol);
    }

    // endregion

    // region Inspection methods

    /**
     * Check if this unit term's unit belongs to the SI system.
     *
     * @return bool True if the unit is an SI unit.
     */
    public function isSi(): bool
    {
        return $this->unit->isSi();
    }

    /**
     * Check if this unit is a base unit, i.e. dimension is a single letter with no exponent.
     * (e.g. m, kg, ft, lb, s)
     *
     * @return bool True if the unit is a base unit.
     */
    public function isBase(): bool
    {
        return $this->unit->isBase();
    }

    // endregion

    // region Comparison methods

    /**
     * Check if this UnitTerm is equal to another.
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

    // region Unary arithmetic methods

    /**
     * Return a new UnitTerm with the exponent negated.
     *
     * @return self A new instance with the inverse exponent (e.g. m² → m⁻²).
     */
    public function inv(): self
    {
        return new self($this->unit, $this->prefix, -$this->exponent);
    }

    // endregion

    // region Power methods

    /**
     * Return a new UnitTerm with the exponent multiplied by the given value.
     *
     * @param int $exponent The exponent to raise the unit term to.
     * @return self A new instance with the multiplied exponent (e.g. m² with exp=3 → m⁶).
     * @throws DomainException If the resulting exponent is invalid (i.e. 0 or outside the range -9..9).
     */
    public function pow(int $exponent): self
    {
        return $this->withExponent($this->exponent * $exponent);
    }

    // endregion

    // region Transformation methods

    /**
     * Return a new UnitTerm with a different exponent.
     *
     * @param int $exp The new exponent.
     * @return self A new instance with the specified exponent.
     * @throws DomainException If the exponent is invalid (i.e. 0 or outside the range -9..9).
     */
    public function withExponent(int $exp): self
    {
        return new self($this->unit, $this->prefix, $exp);
    }

    /**
     * Return a new UnitTerm with the exponent set to 1.
     *
     * @return self A new instance with exponent 1 (e.g. m² → m).
     */
    public function removeExponent(): self
    {
        return $this->withExponent(1);
    }

    /**
     * Return a new UnitTerm with the prefix removed.
     *
     * @return self A new instance with no prefix (e.g. km → m).
     */
    public function removePrefix(): self
    {
        return new self($this->unit, null, $this->exponent);
    }

    // endregion

    // region Conversion methods

    /**
     * Format the unit term as a string.
     *
     * If $ascii is false (default), the Unicode symbol is used (if set), and exponents are converted to superscript
     * (e.g. 'm²').
     *
     * If $ascii is true, then the primary (ASCII) symbol will be used, and the exponent will not be converted to
     * superscript.
     *
     * @param bool $ascii If true, return the ASCII format; if false (default), return the Unicode format.
     * @return string The formatted unit term.
     */
    public function format(bool $ascii = false): string
    {
        if ($ascii) {
            // Get the ASCII parts.
            $prefix = $this->prefix?->asciiSymbol;
            $symbol = $this->unit->asciiSymbol;
            $exp = $this->exponent === 1 ? '' : $this->exponent;
        } else {
            // Get the Unicode parts.
            $prefix = $this->prefix?->unicodeSymbol;
            if ($this->exponent === 1) {
                $symbol = $this->unit->unicodeSymbol;
                $exp = '';
            } else {
                // If the Unicode symbol contains non-letters than use the ASCII version with the exponent.
                // This way, for example, we get 'deg²' instead of '°²'.
                $symbol = Unit::isValidWord($this->unit->unicodeSymbol)
                    ? $this->unit->unicodeSymbol
                    : $this->unit->asciiSymbol;
                $exp = Integers::toSuperscript($this->exponent);
            }
        }

        // Construct the full unit term symbol.
        return $prefix . $symbol . $exp;
    }

    /**
     * Convert the unit term to a string. This will use the format version, which may include non-ASCII characters.
     * For the ASCII version, use format(true).
     *
     * @return string The unit term as a string.
     */
    public function __toString(): string
    {
        return $this->format();
    }

    // endregion

    // region Validation methods

    /**
     * Get the regex pattern for matching a unit term.
     *
     * @return string The regex pattern (without delimiters or anchors).
     */
    public static function regex(): string
    {
        // Cache this to save rebuilding every time.
        static $rx = null;

        if ($rx === null) {
            // Get the regular expression for an exponent (ASCII or superscript).
            $superscriptChars = Integers::SUPERSCRIPT_CHARACTERS;
            $superscriptMinus = $superscriptChars['-'];
            unset($superscriptChars['-']);
            $superscriptDigits = implode('', $superscriptChars);
            $rxExponent = "(?:-?\d)|(?:$superscriptMinus?[$superscriptDigits])";

            // Get the regular expression to match the prefixed or unprefixed unit symbol.
            $rxUnit = '(?:' . Unit::RX_WORD . ')'                   // 1-7 letters (ASCII or Unicode)
                . '|' . '(?:' . Unit::RX_ASCII_WORDS . ')'          // 1-3 ASCII words
                . '|' . '(?:[' . Unit::RX_NON_LETTERS . '])'        // 1 non-letter symbol
                . '|' . '(?:' . Unit::RX_TEMPERATURE_SYMBOL . ')';  // degree symbol plus one letter

            // Get the full regular expression with two captures.
            $rx = "($rxUnit)($rxExponent)?";
        }

        return $rx;
    }

    /**
     * Check if a string is a valid unit term symbol.
     *
     * This method checks the format only - it doesn't validate the symbol, exponent, or prefix.
     * That is done in parse().
     *
     * The match results:
     * - $matches[0] is the entire match
     * - $matches[1] is the prefixed unit symbol
     * - $matches[2] is the exponent (not set if none found)
     *
     * @param string $symbol The symbol to validate.
     * @param ?array<int, string> $matches Output array for match results.
     * @return bool True if the symbol is a valid unit term.
     */
    public static function isValidSymbol(string $symbol, ?array &$matches): bool
    {
        // Match against the regex.
        return (bool)preg_match('/^' . self::regex() . '$/iu', $symbol, $matches);
    }

    // endregion

    // region Helper methods

    /**
     * Attempt to expand this unit term into base units.
     *
     * Delegates to the underlying Unit's expansion and adjusts the result for this term's prefix multiplier and
     * exponent.
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

        // Check if there's anything to do.
        if ($this->isBase()) {
            return null;
        }

        // Try to find the expansion for this unit.
        $unitExpansion = $this->unit->tryExpand();

        // If none found, the expansion doesn't exist for this unit term.
        if ($unitExpansion === null) {
            return null;
        }

        // Multiply by the conversion factor modified by prefix and exponent.
        $resultValue = ($unitExpansion->value * $this->prefixMultiplier) ** $this->exponent;

        // Construct the expansion Quantity and cache it in the private property.
        $this->expansion = Quantity::create($resultValue, $unitExpansion->compoundUnit->pow($this->exponent));
        return $this->expansion;
    }

    // endregion
}
