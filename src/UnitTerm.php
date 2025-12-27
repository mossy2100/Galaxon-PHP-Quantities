<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use Galaxon\Core\Integers;
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
class UnitTerm
{
    // region Properties

    /**
     * The base unit without prefix or exponent (e.g., 'm', 's').
     */
    private(set) string $base;

    /**
     * The SI/binary prefix symbol (e.g., 'k', 'm', 'G'), or empty string if none.
     */
    private(set) string $prefix = '';

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
        get {
            return $this->base . ($this->exponent === 1 ? '' : $this->exponent);
        }
    }

    /**
     * The full prefixed unit symbol (e.g. 'km2', 'ms-1').
     */
    public string $prefixed {
        get {
            return $this->prefix . $this->derived;
        }
    }

    /**
     * The prefix multiplier raised to the exponent (e.g., 1000² = 1e6 for km²).
     */
    public float $multiplier {
        get {
            $multiplier = $this->prefix ? UnitData::getPrefixMultiplier($this->prefix) : 1;
            return $multiplier ** $this->exponent;
        }
    }

    // phpcs:enable PSR2.Classes.PropertyDeclaration
    // phpcs:enable Generic.WhiteSpace.ScopeIndent.IncorrectExact

    // endregion

    // region Constructor

    /**
     * Constructor.
     *
     * @param string $base The base unit symbol (e.g., 'm', 'lb').
     * @param string $prefix The prefix symbol (e.g., 'k', 'm', 'G'), or empty string if none.
     * @param int $exponent The exponent (default 1).
     * @throws ValueError If the base unit is invalid.
     */
    public function __construct(string $base, string $prefix = '', int $exponent = 1)
    {
        // Check the unit provided is valid.
        $matchingUnits = UnitData::lookupBaseUnit($base);

        // Check we found a match.
        if (empty($matchingUnits)) {
            throw new ValueError("Unit '$base' is unsupported.");
        }

        // Check we only found one match.
        if (count($matchingUnits) > 1) {
            throw new ValueError("Multiple matching units found for '$base.");
        }

        // Get the matching unit information.
        $matchingUnit = $matchingUnits[0];

        // If a prefix was supplied, make sure it's valid for this unit.
        if (!empty($prefix)) {
            // Convert 'u' to 'μ'.
            if ($prefix === 'u') {
                $prefix = 'μ';
            }

            // Get all valid prefixes for this unit.
            $prefixes = UnitData::getPrefixes($matchingUnit['prefixes']);

            // Check the provided prefix is valid.
            if (!array_key_exists($prefix, $prefixes)) {
                throw new ValueError("The prefix '$prefix' cannot be used with the unit '$base'.");
            }
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
     * Parses the derived unit to extract the base unit and exponent.
     *
     * @param string $symbol The unit symbol with optional prefix and/or exponent (e.g., 'm2', 's-1').
     * @param ?string $prefix The prefix symbol (e.g., 'k', 'm', 'G'), or null if unspecified.
     * @return self The parsed unit term.
     * @throws ValueError If the format is invalid or the derived unit is not recognized.
     */
    public static function parse(string $symbol, ?string $prefix = null): self
    {
        // Validate the unit string.
        $unitValid = preg_match('/^(\p{L}+)(-?\d+)?$/u', $symbol, $matches);
        if (!$unitValid) {
            throw new ValueError(
                "Invalid unit '$symbol'. A unit must comprise one or more letters optionally followed by an exponent."
            );
        }

        // Get the base unit.
        $base = $matches[1];

        // If a prefix is unspecified, check if the symbol has one.
        if ($prefix === null) {
            $matchingUnits = UnitData::lookupUnit($base);

            // Check we found a match.
            if (empty($matchingUnits)) {
                throw new ValueError("Unknown or unsupported unit '$base'.");
            }

            // Check we only found one match.
            if (count($matchingUnits) > 1) {
                throw new ValueError("Multiple matching units found for '$base.");
            }

            $matchingUnit = $matchingUnits[0];
            $base = $matchingUnit['symbol'];
            $prefix = $matchingUnit['prefix'] ?? '';
        }

        // Get the exponent.
        $exp = isset($matches[2]) ? (int)$matches[2] : 1;

        // Set properties.
        return new UnitTerm($base, $prefix, $exp);
    }

    // endregion

    // region Conversion methods

    /**
     * Format the unit as a string for display.
     *
     * Exponents are converted to superscript (e.g., 'm2' → 'm²').
     * The formatted symbol is used if set.
     *
     * @return string The formatted unit symbol.
     */
    public function __toString(): string
    {
        // Get the exponent in superscript.
        $exp = $this->exponent === 1 ? '' : Integers::toSuperscript($this->exponent);

        $baseUnit = UnitData::lookupBaseUnit($this->base)[0];
        $unitSymbol = $baseUnit['format'] ?? $baseUnit['symbol'];

        // Construct the unit symbol.
        return $this->prefix . $unitSymbol . $exp;
    }

    // endregion

    public function invert(): self
    {
        return new self($this->base, $this->prefix, -$this->exponent);
    }
}
