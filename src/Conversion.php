<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use Stringable;
use ValueError;

/**
 * Represents an affine transformation for unit conversion.
 *
 * Implements the conversion formula: y = m*x + k
 * where:
 * - m is the multiplier (scale factor)
 * - k is the offset (additive constant, used for temperature conversions)
 * - x is the input value in the source unit
 * - y is the output value in the destination unit
 *
 * Error scores are tracked through all operations to enable finding optimal
 * conversion paths in the unit conversion graph.
 */
class Conversion implements Stringable
{
    // region Properties

    /**
     * The dimension.
     *
     * @var string
     */
    public readonly string $dimension;

    /**
     * The source unit.
     *
     * @var UnitTerm
     */
    public readonly UnitTerm $srcUnitTerm;

    /**
     * The destination unit.
     *
     * @var UnitTerm
     */
    public readonly UnitTerm $destUnitTerm;

    /**
     * The scale factor.
     *
     * @var FloatWithError
     */
    public readonly FloatWithError $factor;

    // endregion

    // region Property hooks

    // PHPCS doesn't know property hooks yet.
    // phpcs:disable PSR2.Classes.PropertyDeclaration
    // phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact

    /**
     * The error score for this conversion.
     *
     * @var float
     */
    public float $totalAbsoluteError {
        get => $this->factor->absoluteError;
    }

    // phpcs:enable PSR2.Classes.PropertyDeclaration
    // phpcs:enable Generic.WhiteSpace.ScopeIndent.IncorrectExact

    // endregion

    // region Constructor

    /**
     * Constructor.
     *
     * @param string|UnitTerm $srcUnitTerm The source unit term.
     * @param string|UnitTerm $destUnitTerm The destination unit term.
     * @param float|FloatWithError $factor The scale factor (cannot be 0).
     * @throws ValueError If the source or destination unit term is a string that does not represent a valid unit
     * term, or if the multiplier is zero.
     */
    public function __construct(
        string $dimension,
        string|UnitTerm $srcUnitTerm,
        string|UnitTerm $destUnitTerm,
        float|FloatWithError $factor
    ) {
        // Ensure dimension is valid.
        if (!Dimensions::isValid($dimension)) {
            throw new ValueError("Invalid dimension '$dimension'.");
        }

        // Ensure source unit is a UnitTerm.
        if (is_string($srcUnitTerm)) {
            $srcUnitTerm = UnitTerm::parse($srcUnitTerm);
        }

        // Ensure source unit term has the correct dimension.
        if ($srcUnitTerm->dimension !== $dimension) {
            throw new ValueError("Unit '$srcUnitTerm' does not have dimension '$dimension'.");
        }

        // Ensure destination unit is a UnitTerm.
        if (is_string($destUnitTerm)) {
            $destUnitTerm = UnitTerm::parse($destUnitTerm);
        }

        // Ensure destination unit term has the correct dimension.
        if ($destUnitTerm->dimension !== $dimension) {
            throw new ValueError("Unit '$destUnitTerm' does not have dimension '$dimension'.");
        }

        // Ensure multiplier is a FloatWithError.
        if (!$factor instanceof FloatWithError) {
            $factor = new FloatWithError($factor);
        }

        // Ensure multiplier is not zero.
        if ($factor->value === 0.0) {
            throw new ValueError('Multiplier cannot be zero.');
        }

        // Set the properties.
        $this->dimension = $dimension;
        $this->srcUnitTerm = $srcUnitTerm;
        $this->destUnitTerm = $destUnitTerm;
        $this->factor = $factor;
    }

    // endregion

    // region Application methods

    /**
     * Apply conversion to an input value.
     *
     * @param float|FloatWithError $value The input value.
     * @return FloatWithError The result of the conversion.
     */
    public function apply(float|FloatWithError $value): FloatWithError
    {
        // Convert the value. y = mx + k
        return $this->factor->mul($value);
    }

    // endregion

    // region Transformation methods

    /**
     * Invert this conversion to go from destination unit back to source unit.
     *
     * Given: b = a * m1
     * Solve for a: a = b * (1/m1)
     *
     * @return self The inverted conversion (dest->source).
     */
    public function invert(): self
    {
        $m1 = $this->factor;

        // m = 1 / m1
        $m = $m1->inv();

        // Swap the units when inverting.
        return new self($this->dimension, $this->destUnitTerm, $this->srcUnitTerm, $m);
    }

    /**
     * Compose two conversions sequentially: source->mid and mid->dest.
     *
     * Given:
     *   b = a * m1  (this conversion)
     *   c = b * m2  (other conversion)
     * Result: c = a * (m1 * m2)
     *
     * @param self $other The second conversion (mid->dest).
     * @return self The combined conversion (source->dest).
     */
    public function combineSequential(self $other): self
    {
        $m1 = $this->factor;
        $m2 = $other->factor;

        // m = m1 * m2
        $m = $m1->mul($m2);

        // Result is source->dest.
        return new self($this->dimension, $this->srcUnitTerm, $other->destUnitTerm, $m);
    }

    /**
     * Compose two conversions convergently: source->mid and dest->mid.
     *
     * Both conversions point toward the intermediate unit.
     *
     * Given:
     *   b = a * m1  (this conversion: source->mid)
     *   b = c * m2  (other conversion: dest->mid)
     * Result: c = a * (m1 / m2)
     *
     * @param self $other The second conversion (dest->mid).
     * @return self The combined conversion (source->dest).
     */
    public function combineConvergent(self $other): self
    {
        $m1 = $this->factor;
        $m2 = $other->factor;

        // m = m1 / m2
        $m = $m1->div($m2);

        // Result is source->dest.
        return new self($this->dimension, $this->srcUnitTerm, $other->srcUnitTerm, $m);
    }

    /**
     * Compose two conversions divergently: mid->source and mid->dest.
     *
     * Both conversions point away from the intermediate unit.
     *
     * Given:
     *   a = b * m1  (this conversion: mid->source)
     *   c = b * m2  (other conversion: mid->dest)
     * Result: c = a * (m2 / m1)
     *
     * @param self $other The second conversion (mid->dest).
     * @return self The combined conversion (source->dest).
     */
    public function combineDivergent(self $other): self
    {
        $m1 = $this->factor;
        $m2 = $other->factor;

        // m = m2 / m1
        $m = $m2->div($m1);

        // Result is source->dest.
        return new self($this->dimension, $this->destUnitTerm, $other->destUnitTerm, $m);
    }

    /**
     * Compose two conversions oppositely: mid->source and dest->mid.
     *
     * Conversions flow in opposite directions through the intermediate unit.
     *
     * Given:
     *   a = b * m1  (this conversion: mid->source)
     *   b = c * m2  (other conversion: dest->mid)
     * Result: c = a / (m1 * m2)
     *
     * @param self $other The second conversion (dest->mid).
     * @return self The combined conversion (source->dest).
     */
    public function combineOpposite(self $other): self
    {
        $m1 = $this->factor;
        $m2 = $other->factor;

        // m = 1 / (m1 * m2)
        $m = $m1->mul($m2)->inv();

        // Result is source->dest.
        return new self($this->dimension, $this->destUnitTerm, $other->srcUnitTerm, $m);
    }

    /**
     * Create a new conversion with different prefixes applied.
     *
     * Takes an existing conversion between units and adjusts the multiplier to account for changing the prefixes
     * while keeping the units otherwise unchanged.
     *
     * Uses FloatWithError arithmetic to propagate error scores through the prefix adjustment calculation.
     *
     * @param ?string $newSrcUnitPrefix The new source unit prefix (null for none).
     * @param ?string $newDestUnitPrefix The new destination unit prefix (null for none).
     * @return self A new conversion with adjusted parameters for the prefixed units.
     * @throws ValueError If either prefix is invalid.
     *
     * @example
     *   // Given conversion: m→ft with multiplier 3.28084
     *   // alterPrefixes(..., 'k', '') produces: km→ft with multiplier 3280.84
     */
    public function alterPrefixes(?string $newSrcUnitPrefix, ?string $newDestUnitPrefix): self
    {
        // Compose the new unit terms.
        $newSrcUnitTerm = new UnitTerm($this->srcUnitTerm->base, $newSrcUnitPrefix, $this->srcUnitTerm->exponent);
        $newDestUnitTerm = new UnitTerm($this->destUnitTerm->base, $newDestUnitPrefix, $this->destUnitTerm->exponent);

        // Calculate total prefix multiplier.
        $totalPrefixMultiplier = ($this->destUnitTerm->multiplier * $newSrcUnitTerm->multiplier) /
                                 ($newDestUnitTerm->multiplier * $this->srcUnitTerm->multiplier);

        // Apply the adjustments to the multiplier using FloatWithError for proper error tracking.
        $newFactor = $this->factor->mul($totalPrefixMultiplier);

        // Create and return the new conversion with updated units and multiplier.
        return new Conversion($this->dimension, $newSrcUnitTerm, $newDestUnitTerm, $newFactor);
    }

    /**
     * Generate a new conversion from an existing one by removing prefixes from the source and destination unit
     * terms.
     *
     * @return self New conversion between unprefixed unit terms.
     */
    public function removePrefixes(): self
    {
        return $this->alterPrefixes(null, null);
    }

    // endregion

    // region Conversion methods

    /**
     * Convert this conversion to a string representation.
     *
     * Format: "destUnit = srcUnit * (factor ± error)"
     *
     * @return string The string representation of this conversion.
     */
    public function __toString(): string
    {
        return "$this->destUnitTerm = $this->srcUnitTerm * ($this->factor)";
    }

    // endregion
}
