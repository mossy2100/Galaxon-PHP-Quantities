<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Stringable;

/**
 * Represents a linear transformation for unit conversion.
 *
 * Implements the conversion formula: y = mx
 * where:
 * - m is the multiplier (scale factor)
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
     * @var DerivedUnit
     */
    private(set) DerivedUnit $srcUnit;

    /**
     * The destination unit.
     *
     * @var DerivedUnit
     */
    private(set) DerivedUnit $destUnit;

    /**
     * The scale factor.
     *
     * @var FloatWithError
     */
    private(set) FloatWithError $factor;

    // endregion

    // region Property hooks

    /**
     * The Conversion dimension.
     *
     * @var string
     */
    public string $dimension {
        get => $this->srcUnit->dimension;
    }

    // endregion

    // region Constructor

    /**
     * Constructor.
     *
     * @param string|UnitInterface $srcUnit The source unit.
     * @param string|UnitInterface $destUnit The destination unit.
     * @param float|FloatWithError $factor The scale factor (must be positive).
     * @throws FormatException If either unit is provided as a string that cannot be parsed.
     * @throws DomainException If dimensions don't match or the factor is not positive.
     */
    public function __construct(
        string|UnitInterface $srcUnit,
        string|UnitInterface $destUnit,
        float|FloatWithError $factor
    ) {
        // Ensure the units are DerivedUnit objects.
        $srcUnit = DerivedUnit::toDerivedUnit($srcUnit);
        $destUnit = DerivedUnit::toDerivedUnit($destUnit);

        // Ensure dimensions match.
        if ($srcUnit->dimension !== $destUnit->dimension) {
            throw new DomainException(
                "Cannot create conversion: '$srcUnit->asciiSymbol' ($srcUnit->dimension) " .
                "and '$destUnit->asciiSymbol' ($destUnit->dimension) have different dimensions."
            );
        }

        // Ensure the factor is a FloatWithError.
        if (!$factor instanceof FloatWithError) {
            $factor = new FloatWithError($factor);
        }

        // Ensure the factor is positive.
        if ($factor->value <= 0.0) {
            throw new DomainException('Conversion factor must be positive.');
        }

        // Set the properties.
        $this->srcUnit = $srcUnit;
        $this->destUnit = $destUnit;
        $this->factor = $factor;
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
    public function inv(): self
    {
        $m1 = $this->factor;

        // m = 1 / m1
        $m = $m1->inv();

        // Swap the units when inverting.
        return new self($this->destUnit, $this->srcUnit, $m);
    }

    /**
     * Create a new conversion by applying an exponent.
     *
     * @param int $newExponent The new exponent.
     * @return self A new conversion with exponentiated units.
     */
    public function pow(int $newExponent): self
    {
        // Apply the exponent to the units.
        $newSrcUnitTerm = $this->srcUnit->pow($newExponent);
        $newDestUnitTerm = $this->destUnit->pow($newExponent);

        // Calculate new factor.
        $newFactor = $this->factor->pow($newExponent);

        // Create and return the new conversion with updated units and multiplier.
        return new self($newSrcUnitTerm, $newDestUnitTerm, $newFactor);
    }

    /**
     * Generate a new conversion from an existing one by removing prefixes from the source and destination unit terms.
     *
     * @return self New conversion between unprefixed unit terms.
     */
    public function removePrefixes(): self
    {
        $srcUnit = $this->srcUnit->removePrefixes();
        $destUnit = $this->destUnit->removePrefixes();
        $factor = $this->factor->mul($this->destUnit->multiplier)->div($this->srcUnit->multiplier);
        return new self($srcUnit, $destUnit, $factor);
    }

    // endregion

    // region Combination methods

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
        $m1 = $this->factor;
        $m2 = $other->factor;

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
        $m1 = $this->factor;
        $m2 = $other->factor;

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
        $m1 = $this->factor;
        $m2 = $other->factor;

        // m = 1 / (m1 * m2)
        $m = $m1->mul($m2)->inv();

        // Result is source->dest.
        return new self($this->destUnit, $other->srcUnit, $m);
    }

    // endregion

    // region String methods

    /**
     * Format as "1 srcUnit = 9.999999 destUnit".
     *
     * @return string The string representation.
     */
    public function __toString(): string
    {
        return "1 $this->srcUnit = $this->factor->value $this->destUnit";
    }

    // endregion
}
