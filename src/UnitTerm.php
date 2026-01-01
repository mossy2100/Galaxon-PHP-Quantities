<?php

declare(strict_types = 1);

namespace Galaxon\Quantities;

use Galaxon\Core\Integers;
use Stringable;
use ValueError;

/**
 * Represents a decomposed unit symbol.
 *
 * A unit symbol like 'km2' is decomposed into:
 * - base: The unit without prefix or exponent ('m').
 * - prefix: The SI/binary prefix ('k').
 * - prefixMultiplier: The prefix multiplier (1000).
 * - exponent: The power (2).
 *
 * Computed properties:
 * - derived: The unit without prefix ('m2').
 * - prefixed: The full unit symbol ('km2').
 * - multiplier: The prefix multiplier raised to the exponent (1000² = 1e6).
 */
class UnitTerm implements Stringable
{
    // region Properties

    /**
     * The base unit.
     */
    public BaseUnit $base;

    /**
     * The SI/binary prefix symbol (e.g., 'k', 'm', 'G'), or null if none.
     */
    public ?string $prefix = null;

    /**
     * The exponent (e.g., 2 for m², -1 for s⁻¹).
     */
    public int $exponent = 1;

    // endregion

    // region Property hooks

    // PHP_CodeSniffer doesn't know about property hooks yet.
    // phpcs:disable PSR2.Classes.PropertyDeclaration
    // phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact

    /**
     * The unprefixed unit term symbol (e.g. 'm2', 's-1').
     */
    public string $unprefixedSymbol {
        get => $this->base . ($this->exponent === 1 ? '' : $this->exponent);
    }

    /**
     * The full prefixed unit symbol (e.g. 'km2', 'ms-1').
     */
    public string $prefixed {
        get => $this->prefix . $this->unprefixedSymbol;
    }

    /**
     * The prefix multiplier.
     */
    public float $prefixMultiplier {
        get => $this->prefix ? UnitData::getPrefixMultiplier($this->prefix) : 1;
    }

    /**
     * The prefix multiplier raised to the exponent (e.g., 1000² = 1e6 for km²).
     */
    public float $multiplier {
        get => $this->prefixMultiplier ** $this->exponent;
    }

    /**
     * The dimension code.
     */
    public string $dimension
    {
        get => Dimensions::applyExponent($this->base->dimension, $this->exponent);
    }

    // phpcs:enable PSR2.Classes.PropertyDeclaration
    // phpcs:enable Generic.WhiteSpace.ScopeIndent.IncorrectExact

    // endregion

    // region Constructor

    /**
     * Constructor.
     *
     * @param BaseUnit $base The base unit.
     * @param ?string $prefix The prefix symbol (e.g., 'k', 'm', 'G'), or null if none.
     * @param int $exponent The exponent (default 1).
     * @throws ValueError If the exponent is 0 or the prefix is not valid for the unit.
     */
    public function __construct(BaseUnit $base, ?string $prefix = null, int $exponent = 1)
    {
        // If a prefix was supplied, make sure it's valid for this unit.
        if ($prefix !== null && !$base->acceptsPrefix($prefix)) {
            throw new ValueError("The prefix '$prefix' cannot be used with the unit '$base'.");
        }

        // Validate the exponent.
        if ($exponent === 0) {
            throw new ValueError('Invalid exponent 0. Exponents cannot be equal to 0.');
        }

        // Set properties.
        $this->base = $base;
        $this->prefix = $prefix;
        $this->exponent = $exponent;
    }

    /**
     * Constructor.
     *
     * Parses the given symbol to extract the base unit, prefix, and exponent.
     *
     * @param string $symbol The unit symbol with optional prefix and/or exponent (e.g., 'm2', 's-1').
     * @return self The parsed unit term.
     * @throws ValueError If the format is invalid or the derived unit is not recognized.
     */
    public static function parse(string $symbol): self
    {
        // Validate the unit string.
        $unitValid = preg_match('/^(\p{L}+)(-?\d)?$/u', $symbol, $matches);
        if (!$unitValid) {
            throw new ValueError(
                "Invalid unit '$symbol'. A unit must comprise one or more letters optionally followed by an exponent."
            );
        }

        // Get the base unit.
        $prefixedUnitSymbol = $matches[1];

        // Get the exponent.
        $exp = isset($matches[2]) ? (int)$matches[2] : 1;

        // Make sure the exponent isn't 0.
        if ($exp === 0) {
            throw new ValueError('Invalid exponent 0. A unit must have a non-zero exponent.');
        }

        // Search for a matching unit symbol.
        $matchingUnits = UnitData::getBySymbol($prefixedUnitSymbol);

        // Check we found a match.
        if (empty($matchingUnits)) {
            throw new ValueError("Unknown or unsupported unit '$prefixedUnitSymbol'.");
        }

        // Check we only found one match.
        if (count($matchingUnits) > 1) {
            throw new ValueError("Multiple matching units found for '$prefixedUnitSymbol.");
        }

        // Create the new object.
        return $matchingUnits[0]->applyExponent($exp);
    }

    // endregion

    // region Conversion methods

    /**
     * Format the unit term as a string for display.
     *
     * If $ascii is false (default), the format symbol is used (if set), and exponents are converted to superscript
     * (e.g. 'm²').
     *
     * If $ascii is true, then the primary (ASCII) symbol will be used, and the exponent will not be
     * converted to superscript.
     *
     * @param bool $ascii Whether to generate the ASCII version (default false).
     * @return string The formatted unit term.
     */
    public function format(bool $ascii = false): string
    {
        if ($ascii) {
            // Get the exponent.
            $exp = $this->exponent === 1 ? '' : (string)$this->exponent;

            // Get the primary symbol.
            $formattedSymbol = $this->base->symbol;
        }
        else {
            // Get the exponent in superscript.
            $exp = $this->exponent === 1 ? '' : Integers::toSuperscript($this->exponent);

            // Get the formatted symbol.
            $formattedSymbol = $this->base?->format ?? $this->base->symbol;
        }

        // Construct the full unit term symbol.
        return $this->prefix . $formattedSymbol . $exp;
    }

    /**
     * Convert the unit term to a string. This will use the ASCII version.
     * For the Unicode version, use format().
     *
     * @return string The unit term (ASCII version).
     * @see format
     */
    public function __toString(): string
    {
        return $this->format(true);
    }

    // endregion

    // region Transformation methods

    public function invert(): self
    {
        return new self($this->base, $this->prefix, -$this->exponent);
    }

    public function withExponent(int $exp): self
    {
        return new self($this->base, $this->prefix, $exp);
    }

    public function applyExponent(int $exp): self
    {
        return $this->withExponent($this->exponent * $exp);
    }

    public function removeExponent(): self
    {
        return $this->withExponent(1);
    }

    public function withPrefix(?string $prefix): self
    {
        return new self($this->base, $prefix, $this->exponent);
    }

    public function removePrefix(): self
    {
        return $this->withPrefix(null);
    }

    // endregion

    // region Comparison methods

    public function equal(UnitTerm $other): bool
    {
        return $this->base->equal($other->base) && $this->prefix === $other->prefix && $this->exponent === $other->exponent;
    }

    // endregion

    // region Inspection methods

    public function isSi(): bool {
        return $this->base->isSi();
    }

    // endregion
}
