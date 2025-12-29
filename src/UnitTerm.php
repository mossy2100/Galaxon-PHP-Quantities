<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use Galaxon\Core\Integers;
use LogicException;
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
     * The base unit without prefix or exponent (e.g., 'm', 's').
     */
    private(set) string $base;

    /**
     * The SI/binary prefix symbol (e.g., 'k', 'm', 'G'), or null if none.
     */
    private(set) ?string $prefix = null;

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
     * The derived unit symbol without prefix (e.g. 'm2', 's-1').
     */
    public string $derived {
        get => $this->base . ($this->exponent === 1 ? '' : $this->exponent);
    }

    /**
     * The full prefixed unit symbol (e.g. 'km2', 'ms-1').
     */
    public string $prefixed {
        get => $this->prefix . $this->derived;
    }

    /**
     * The prefix multiplier.
     */
    public float $prefixMultiplier {
        get => $this->prefix ? Unit::getPrefixMultiplier($this->prefix) : 1;
    }

    /**
     * The prefix multiplier raised to the exponent (e.g., 1000² = 1e6 for km²).
     */
    public float $multiplier {
        get => $this->prefixMultiplier ** $this->exponent;
    }

    // phpcs:enable PSR2.Classes.PropertyDeclaration
    // phpcs:enable Generic.WhiteSpace.ScopeIndent.IncorrectExact

    // endregion

    // region Constructor

    /**
     * Constructor.
     *
     * @param string $base The base unit symbol (e.g., 'm', 'lb').
     * @param ?string $prefix The prefix symbol (e.g., 'k', 'm', 'G'), or null if none.
     * @param int $exponent The exponent (default 1).
     * @throws ValueError If the base unit is invalid.
     */
    public function __construct(string $base, ?string $prefix = null, int $exponent = 1)
    {
        // Load the Unit object.
        $unit = Unit::lookup($base);

        // Throw if the symbol is invalid.
        if ($unit === null) {
            throw new ValueError("Unknown unit '$base'.");
        }

        // If a prefix was supplied, make sure it's valid for this unit.
        if ($prefix !== null && !$unit->acceptsPrefix($prefix)) {
            throw new ValueError("The prefix '$prefix' cannot be used with the unit '$base'.");
        }

        // Validate the exponent.
        if ($exponent < -9 || $exponent === 0 || $exponent > 9) {
            throw new ValueError("Invalid exponent $exponent. Must be in the range -9 to 9 and not equal to 0.");
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
        $unitValid = preg_match('/^(\p{L}+)(-?\d+)?$/u', $symbol, $matches);
        if (!$unitValid) {
            throw new ValueError(
                "Invalid unit '$symbol'. A unit must comprise one or more letters optionally followed by an exponent."
            );
        }

        // Get the base unit.
        $prefixedUnitSymbol = $matches[1];

        // Check if the symbol has a prefix.
        $matchingUnits = Unit::search($prefixedUnitSymbol);

        // Check we found a match.
        if (empty($matchingUnits)) {
            throw new ValueError("Unknown or unsupported unit '$prefixedUnitSymbol'.");
        }

        // Check we only found one match.
        if (count($matchingUnits) > 1) {
            throw new ValueError("Multiple matching units found for '$prefixedUnitSymbol.");
        }

        // Extract the base and prefix.
        $matchingUnit = $matchingUnits[0];
        $base = $matchingUnit['base'];
        $prefix = $matchingUnit['prefix'];

        // Get the exponent.
        $exp = isset($matches[2]) ? (int)$matches[2] : 1;

        // Set properties.
        return new UnitTerm($base, $prefix, $exp);
    }

    // endregion

    // region Conversion methods

    /**
     * Format the unit term as a string for display.
     *
     * Exponents are converted to superscript (e.g., 'm2' → 'm²').
     * The format symbol is used if set.
     *
     * @return string The formatted unit term.
     */
    public function __toString(): string
    {
        // Get the exponent in superscript.
        $exp = $this->exponent === 1 ? '' : Integers::toSuperscript($this->exponent);

        // Get the base unit symbol.
        $baseUnit = Unit::lookup($this->base);
        $formattedSymbol = $baseUnit?->format ?? $baseUnit?->symbol;

        // Construct the full unit term symbol.
        return $this->prefix . $formattedSymbol . $exp;
    }

    // endregion

    // region Transformation methods

    public function invert(): self
    {
        return new self($this->base, $this->prefix, -$this->exponent);
    }

    // endregion

    // region Extraction methods

    /**
     *
     *
     * @throws LogicException If the base unit is unsupported (should never happen).
     * @throws ValueError If the dimension code is invalid (should never happen).
     */
    public function getDimensionCode()
    {
        // Get the dimension code for the base unit.
        $unit = Unit::lookup($this->base);

        // Check we found a match.
        if ($unit === null) {
            throw new LogicException("Unit not found: '$this->base'. This should never happen.");
        }

        // If the exponent is 1, return the dimension code as-is.
        if ($this->exponent === 1) {
            return $unit->dimension;
        }

        // Break the dimension code into its parts and multiply each by the exponent.
        $parts = Dimensions::parse($unit->dimension);
        foreach ($parts as $dimCode => $dimExp) {
            $parts[$dimCode] = $dimExp * $this->exponent;
        }
        Dimensions::sort($parts);
        return Dimensions::combine($parts);
    }

    // endregion
}
