<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Core\Integers;
use Galaxon\Core\Traits\Equatable;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\Registry\UnitRegistry;
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

    // region Properties

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
        get => Dimensions::applyExponent($this->unit->dimension, $this->exponent);
    }

    // endregion

    // region Constructor

    /**
     * Constructor.
     *
     * @param string|Unit $unit The unit or its symbol (e.g. 'm', 'ft', 'N').
     * @param null|string|Prefix $prefix The prefix symbol or object, or null if none.
     * @param int $exponent The exponent (default 1).
     * @throws DomainException If the exponent or prefix is invalid.
     */
    public function __construct(string|Unit $unit = '', null|string|Prefix $prefix = null, int $exponent = 1)
    {
        // Allow for the unit to be provided as a symbol.
        if (is_string($unit)) {
            $symbol = $unit;
            $unit = UnitRegistry::getBySymbol($symbol);
            if ($unit === null) {
                throw new DomainException("Unit '$symbol' is unknown.");
            }
        }

        // Allow for the prefix to be provided as a symbol.
        if (is_string($prefix)) {
            $prefix = PrefixRegistry::getBySymbol($prefix);
            if ($prefix === null) {
                throw new DomainException("Prefix '$prefix' is unknown.");
            }
        }

        // Validate prefix.
        if ($prefix !== null && !$unit->acceptsPrefix($prefix)) {
            throw new DomainException("Prefix '$prefix' is invalid for unit '$unit->asciiSymbol'.");
        }

        // Validate exponent.
        if ($exponent < -9 || $exponent > 9) {
            throw new DomainException('Exponent must be between -9 and 9.');
        }
        if ($exponent === 0) {
            throw new DomainException("Exponent can't be zero.");
        }

        // Set properties.
        $this->unit = $unit;
        $this->prefix = $prefix;
        $this->exponent = $exponent;
    }

    // endregion

    // region Static public methods

    /**
     * Look up a unit or prefixed unit by its symbol.
     *
     * Symbol uniqueness is enforced by UnitRegistry, so at most one match is possible.
     *
     * @param string $symbol The prefixed unit symbol to search for. Empty string matches the scalar unit.
     * @return ?self The matching unit term, or null if not found.
     */
    public static function getBySymbol(string $symbol): ?self
    {
        // Look for any matching units.
        foreach (UnitRegistry::getAll() as $unit) {
            // See if the unprefixed unit matches.
            if (
                $unit->asciiSymbol === $symbol ||
                $unit->unicodeSymbol === $symbol ||
                $unit->alternateSymbol === $symbol
            ) {
                return new self($unit);
            }

            // Loop through the prefixed units and see if any match.
            // Alternate symbols aren't checked because they can't have prefixes.
            foreach ($unit->allowedPrefixes as $prefix) {
                if (
                    $prefix->asciiSymbol . $unit->asciiSymbol === $symbol ||
                    $prefix->asciiSymbol . $unit->unicodeSymbol === $symbol ||
                    $prefix->unicodeSymbol . $unit->asciiSymbol === $symbol ||
                    $prefix->unicodeSymbol . $unit->unicodeSymbol === $symbol
                ) {
                    return new self($unit, $prefix);
                }
            }
        }

        return null;
    }

    /**
     * Convert the argument to a UnitTerm if necessary.
     *
     * @param string|Unit|self $value The value to convert.
     * @return self The equivalent UnitTerm object.
     * @throws DomainException If a string is provided that cannot be parsed into a UnitTerm.
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

    // endregion

    // region String methods

    /**
     * Get the regex pattern for matching a unit term.
     *
     * Matches one or more letters (the unit symbol) optionally followed by an exponent
     * in either ASCII digits or Unicode superscript characters.
     *
     * @return string The regex pattern (without delimiters or anchors).
     */
    public static function regex(): string
    {
        $superscriptChars = Integers::SUPERSCRIPT_CHARACTERS;
        $superscriptMinus = $superscriptChars['-'];
        unset($superscriptChars['-']);
        $superscriptDigits = implode('', $superscriptChars);
        return Unit::regex() . "((-?\d)|($superscriptMinus?[$superscriptDigits]))?";
    }

    /**
     * Parses the given symbol to extract the unit, prefix, and exponent.
     *
     * @param string $symbol The unit symbol with an optional prefix and/or exponent (e.g. 'm2', 's-1').
     * @return self The parsed unit term.
     * @throws FormatException If the format is invalid.
     * @throws DomainException If the unit is unknown or the exponent is zero.
     */
    public static function parse(string $symbol): self
    {
        // Handle dimensionless units.
        if ($symbol === '') {
            return new self();
        }

        // Validate the format.
        $unitValid = preg_match('/^' . self::regex() . '$/iu', $symbol, $matches);
        if (!$unitValid) {
            throw new FormatException(
                "Invalid unit '$symbol'. A unit must comprise one or more letters optionally followed by an exponent."
            );
        }

        // Get the prefixed unit symbol.
        $prefixedSymbol = $matches[1];

        // Get the exponent, handling both ASCII and superscript formats.
        $exp = 1;
        if (isset($matches[2]) && $matches[2] !== '') {
            $expStr = $matches[2];
            $exp = Integers::isSuperscript($expStr) ? Integers::fromSuperscript($expStr) : (int)$expStr;
        }

        // Make sure the exponent isn't 0.
        if ($exp === 0) {
            throw new DomainException(
                'Invalid exponent 0. A unit must have a no exponent or a non-zero exponent.'
            );
        }

        // Search for a matching unit symbol.
        $match = self::getBySymbol($prefixedSymbol);

        // Check we found a match.
        if ($match === null) {
            throw new DomainException("Unknown or unsupported unit '$prefixedSymbol'.");
        }

        // Create the new object.
        return $match->pow($exp);
    }

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

    /**
     * Check if this unit term is an SI base unit, with or without an exponent.
     *
     * Thus:
     * Returns true for: kg, m, s, A, K, cd, mol, as well as the honorary (for this package) SI units rad, B, and XAU.
     * Plus any of these units with an exponent, e.g. m2, m3, s-1, etc.
     * Returns false for, e.g.: g (gram; requires the 'k' prefix to be an SI base unit), km ('m' is not an SI base unit
     * with prefix), ft, lb, any Imperial/US customary unit, etc.
     *
     * @return bool True if the unit is an SI base unit.
     */
    public function isSiBase(): bool
    {
        return in_array($this->unexponentiatedAsciiSymbol, Dimensions::getSiBaseUnitSymbols(), true);
    }

    /**
     * Check if this unit term is expandable.
     *
     * @return bool True if the unit term is expandable into base units.
     */
    public function isExpandable(): bool
    {
        return $this->unit->isExpandable();
    }

    // endregion

    // region Transformation methods

    /**
     * Return a new UnitTerm with the exponent negated.
     *
     * @return self A new instance with the inverse exponent (e.g. m² → m⁻²).
     */
    public function inv(): self
    {
        return new self($this->unit, $this->prefix, -$this->exponent);
    }

    /**
     * Return a new UnitTerm with a different exponent.
     *
     * @param int $exp The new exponent.
     * @return self A new instance with the specified exponent.
     */
    public function withExponent(int $exp): self
    {
        // Return the same instance if the exponent is already set.
        if ($this->exponent === $exp) {
            return $this;
        }

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
     * Return a new UnitTerm with the exponent multiplied by the given value.
     *
     * @param int $exponent The exponent to raise the unit term to.
     * @return self A new instance with the multiplied exponent (e.g. m² with exp=3 → m⁶).
     */
    public function pow(int $exponent): self
    {
        return $this->withExponent($this->exponent * $exponent);
    }

    /**
     * Return a new UnitTerm with the prefix removed.
     *
     * @return self A new instance with no prefix (e.g. km → m).
     */
    public function removePrefix(): self
    {
        // Return the same instance if there is no prefix.
        if ($this->prefix === null) {
            return $this;
        }

        return new self($this->unit, null, $this->exponent);
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
        return $other instanceof self &&
            $this->unit->equal($other->unit) &&
            $this->prefix === $other->prefix &&
            $this->exponent === $other->exponent;
    }

    // endregion
}
