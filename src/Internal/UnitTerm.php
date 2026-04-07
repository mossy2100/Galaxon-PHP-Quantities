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
 * - symbol: The full unit symbol ('km2').
 * - symbolWithoutPrefix: The unit without prefix ('m2').
 * - symbolWithoutExponent: The unit without exponent ('km').
 * - multiplier: The prefix multiplier raised to the exponent (1000² = 1e6).
 * - dimension: The dimension code with exponent applied.
 */
class UnitTerm implements UnitInterface
{
    use Equatable;

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
     * The full unit symbol with prefix and exponent (e.g. 'km2', 'ms-1').
     * This property returns the ASCII symbol (e.g. 'deg').
     */
    public string $asciiSymbol {
        get => $this->format(true);
    }

    /**
     * The full unit symbol with prefix and exponent formatted as superscript (e.g. 'km²', 'ms⁻¹').
     * This property returns the Unicode symbol if set (e.g. '°').
     */
    public string $unicodeSymbol {
        get => $this->format();
    }

    /**
     * The unprefixed unit term symbol (e.g. 'm2', 's-1').
     * This property returns the ASCII version. For the Unicode symbol, cast to string (__toString()).
     */
    public string $unprefixedAsciiSymbol {
        get => $this->unit->asciiSymbol . ($this->exponent === 1 ? '' : $this->exponent);
    }

    /**
     * The unit term with no exponent (e.g. 'km', 's').
     * This property returns the ASCII version. For the Unicode symbol, cast to string (__toString()).
     */
    public string $unexponentiatedAsciiSymbol {
        get => $this->prefix?->asciiSymbol . $this->unit->asciiSymbol;
    }

    /**
     * The prefix multiplier.
     */
    public float $prefixMultiplier {
        get => $this->prefix === null ? 1.0 : $this->prefix->multiplier;
    }

    /**
     * The prefix multiplier raised to the exponent (e.g. 1000² = 1e6 for km²).
     */
    public float $multiplier {
        get => $this->prefixMultiplier ** $this->exponent;
    }

    /**
     * The dimension code.
     */
    public string $dimension
    {
        get => DimensionService::pow($this->unit->dimension, $this->exponent);
    }

    /**
     * The quantity type this unit term is for, if known.
     */
    public ?QuantityType $quantityType {
        get => QuantityTypeService::getByDimension($this->dimension);
    }

    // endregion

    // region Constructor

    /**
     * Constructor.
     *
     * @param string|Unit $unit The unit or its symbol (e.g. 'm', 'ft', 'N').
     * @param null|string|Prefix $prefix The prefix symbol or object, or null if none.
     * @param int $exponent The exponent (default 1).
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
            throw new DomainException('Cannot have a zero exponent.');
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
     * Parses the given symbol to extract the unit, prefix, and exponent.
     *
     * @param string $symbol The unit symbol with an optional prefix and/or exponent (e.g. 'm2', 's-1').
     * @return self The parsed unit term.
     * @throws FormatException If the format is invalid.
     * @throws UnknownUnitException If the unit symbol is not recognized.
     * @throws DomainException If the exponent is zero.
     */
    public static function parse(string $symbol): self
    {
        // Handle dimensionless units.
        if ($symbol === '') {
            return new self();
        }

        // Validate the format.
        if (!self::isValidUnitTerm($symbol, $matches)) {
            throw new FormatException(
                "Invalid unit '$symbol'. A unit must comprise one or more letters optionally followed by an exponent."
            );
        }

        // Get the prefixed unit symbol.
        assert(isset($matches[1]));
        $prefixedUnitSymbol = $matches[1];

        // Get the exponent, handling both ASCII and superscript formats.
        $exp = 1;
        if (isset($matches[2]) && $matches[2] !== '') {
            $expStr = $matches[2];
            $exp = Integers::isSuperscript($expStr) ? Integers::fromSuperscript($expStr) : (int)$expStr;
        }

        // Make sure the exponent isn't 0.
        if ($exp === 0) {
            throw new DomainException('Cannot have a zero exponent.');
        }

        // Search for a matching unit symbol.
        foreach (UnitService::getAll() as $unit) {
            if (array_key_exists($prefixedUnitSymbol, $unit->symbols)) {
                [$unitSymbol, $prefixSymbol] = $unit->symbols[$prefixedUnitSymbol];
                return new self($unitSymbol, $prefixSymbol, $exp);
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
     * @throws DomainException If the exponent is invalid.
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
            $symbol = $this->unit->unicodeSymbol;
            $exp = $this->exponent === 1 ? '' : Integers::toSuperscript($this->exponent);
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

    // region Regex methods

    /**
     * Get the regex pattern for matching a unit term.
     *
     * @return string The regex pattern (without delimiters or anchors).
     */
    public static function unitTermRegex(): string
    {
        $superscriptChars = Integers::SUPERSCRIPT_CHARACTERS;
        $superscriptMinus = $superscriptChars['-'];
        unset($superscriptChars['-']);
        $superscriptDigits = implode('', $superscriptChars);
        return '((?:' . Unit::RX_PREFIX . ')?(?:' . Unit::RX_UNIT .
            "))((-?\d)|($superscriptMinus?[$superscriptDigits]))?";
    }

    // endregion

    // region Validation methods

    /**
     * Check if a string is a valid unit term symbol.
     *
     * @param string $symbol The symbol to validate.
     * @param ?array<array-key, string> $matches Output array for match results.
     * @return bool True if the symbol is a valid unit term.
     */
    private static function isValidUnitTerm(string $symbol, ?array &$matches): bool
    {
        return (bool)preg_match('/^' . self::unitTermRegex() . '$/iu', $symbol, $matches);
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
