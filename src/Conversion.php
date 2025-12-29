<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use Galaxon\Core\Numbers;
use Galaxon\Math\FloatWithError;
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
     * The source unit.
     *
     * @var string
     */
    public readonly string $srcUnit;

    /**
     * The destination unit.
     *
     * @var string
     */
    public readonly string $destUnit;

    /**
     * The scale factor (cannot be zero).
     *
     * @var FloatWithError
     */
    public readonly FloatWithError $multiplier;

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
        get => $this->multiplier->absoluteError;
    }

    // phpcs:enable PSR2.Classes.PropertyDeclaration
    // phpcs:enable Generic.WhiteSpace.ScopeIndent.IncorrectExact

    // endregion

    // region Constructor

    /**
     * Constructor.
     *
     * @param string $srcUnit The source unit.
     * @param string $destUnit The destination unit.
     * @param float|FloatWithError $multiplier The scale factor (cannot be 0).
     * @throws ValueError If the multiplier is zero.
     */
    public function __construct(
        string $srcUnit,
        string $destUnit,
        float|FloatWithError $multiplier
    ) {
        // Ensure multiplier is a FloatWithError.
        if (!$multiplier instanceof FloatWithError) {
            $multiplier = new FloatWithError($multiplier);
        }

        // Ensure multiplier is not zero.
        if ($multiplier->value === 0.0) {
            throw new ValueError('Multiplier cannot be zero.');
        }

        // Set the properties.
        $this->srcUnit = $srcUnit;
        $this->destUnit = $destUnit;
        $this->multiplier = $multiplier;
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
        return $this->multiplier->mul($value);
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
        $m1 = $this->multiplier;

        // m = 1 / m1
        $m = $m1->inv();
        // Swap the units when inverting.
        return new self($this->destUnit, $this->srcUnit, $m);
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
        $m1 = $this->multiplier;
        $m2 = $other->multiplier;

        // m = m1 * m2
        $m = $m1->mul($m2);
        // Result is source->dest.
        return new self($this->srcUnit, $other->destUnit, $m);
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
        $m1 = $this->multiplier;
        $m2 = $other->multiplier;

        // m = m1 / m2
        $m = $m1->div($m2);
        // Result is source->dest.
        return new self($this->srcUnit, $other->srcUnit, $m);
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
        $m1 = $this->multiplier;
        $m2 = $other->multiplier;

        // m = m2 / m1
        $m = $m2->div($m1);
        // Result is source->dest.
        return new self($this->destUnit, $other->destUnit, $m);
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
        $m1 = $this->multiplier;
        $m2 = $other->multiplier;

        // m = 1 / (m1 * m2)
        $m = $m1->mul($m2)->inv();
        // Result is source->dest.
        return new self($this->destUnit, $other->srcUnit, $m);
    }

    // endregion

    // region Conversion methods

    /**
     * Convert this conversion to a string representation.
     *
     * Format: "destUnit = srcUnit * multiplier + offset (error score: X)"
     * Omits multiplier if 1, omits offset if 0.
     *
     * @return string The string representation of this conversion.
     */
    public function __toString(): string
    {
        $str = "$this->destUnit = $this->srcUnit";
        if (!Numbers::equal($this->multiplier->value, 1)) {
            $str .= " * {$this->multiplier->value}";
        }
        $str .= " (total absolute error: $this->totalAbsoluteError)";
        return $str;
    }

    // endregion
}
