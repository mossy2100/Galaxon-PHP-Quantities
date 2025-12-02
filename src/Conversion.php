<?php

declare(strict_types=1);

namespace Galaxon\Units;

use Galaxon\Core\Numbers;
use ValueError;

/**
 * Represents an affine transformation for unit conversion: y = mx + k
 *
 * Where:
 * - m is the multiplier (scale factor)
 * - k is the offset (additive constant, used for temperature conversions)
 *
 * Error scores are tracked through all operations to find optimal conversion paths.
 */
class Conversion
{
    /**
     * The initial unit (source).
     *
     * @var string
     */
    public readonly string $initialUnit;

    /**
     * The final unit (destination).
     *
     * @var string
     */
    public readonly string $finalUnit;

    /**
     * The scale factor (cannot be 0).
     *
     * @var NumberWithError
     */
    public readonly NumberWithError $multiplier;

    /**
     * The additive offset (default 0).
     *
     * @var NumberWithError
     */
    public readonly NumberWithError $offset;

    /**
     * The total error score for this conversion.
     *
     * @var int
     */
    public int $error {
        get => $this->multiplier->error + $this->offset->error;
    }

    /**
     * Constructor.
     *
     * @param string $initialUnit The initial unit (source).
     * @param string $finalUnit The final unit (destination).
     * @param int|float|NumberWithError $multiplier The scale factor (cannot be 0).
     * @param int|float|NumberWithError $offset The additive offset (default 0).
     * @throws ValueError If the multiplier is zero.
     */
    public function __construct(
        string $initialUnit,
        string $finalUnit,
        int|float|NumberWithError $multiplier,
        int|float|NumberWithError $offset = 0
    )
    {
        // Set the unit properties.
        $this->initialUnit = $initialUnit;
        $this->finalUnit = $finalUnit;

        // Ensure multiplier is a NumberWithError.
        if (!$multiplier instanceof NumberWithError) {
            $multiplier = new NumberWithError($multiplier);
        }

        // Ensure multiplier is not zero.
        if ($multiplier->isZero()) {
            throw new ValueError('Multiplier cannot be zero.');
        }

        // Ensure offset is a NumberWithError.
        if (!$offset instanceof NumberWithError) {
            $offset = new NumberWithError($offset);
        }

        // Set the properties.
        $this->multiplier = $multiplier;
        $this->offset = $offset;
    }

    /**
     * Invert this conversion.
     *
     * a * m1 + k1 = b
     * =>
     * b * (1 / m1) + (-k1 / m1) = a
     *
     * @return self The inverted conversion.
     */
    public function invert(): self {
        $m1 = $this->multiplier;
        $k1 = $this->offset;

        // m = 1 / m1
        $m = $m1->inv();
        // k = -k1 / m1
        $k = $k1->neg()->div($m1);
        // Swap the units when inverting
        return new self($this->finalUnit, $this->initialUnit, $m, $k);
    }

    /**
     * Apply prefix multipliers and update unit names.
     *
     * This method creates a new Conversion with adjusted multiplier based on unit prefixes
     * (e.g., kilo, milli) and updates the unit names to include the prefixes.
     *
     * @param string $initialUnit The full initial unit name (with prefix if applicable).
     * @param string $finalUnit The full final unit name (with prefix if applicable).
     * @param float $initialPrefixMultiplier The multiplier for the initial unit's prefix (default 1.0).
     * @param float $finalPrefixMultiplier The multiplier for the final unit's prefix (default 1.0).
     * @return self A new Conversion with adjusted multiplier and updated unit names.
     */
    public function applyPrefixes(
        string $initialUnit,
        string $finalUnit,
        float $initialPrefixMultiplier = 1.0,
        float $finalPrefixMultiplier = 1.0
    ): self {
        // Calculate combined prefix multiplier.
        // Multiply by initial prefix (e.g., kilo = 1000)
        // Divide by final prefix (e.g., milli = 0.001, so we multiply by 1000)
        $prefixMultiplier = $initialPrefixMultiplier / $finalPrefixMultiplier;

        // If there are no prefix multipliers, or they cancel each other out, just update unit names.
        if (Numbers::approxEqual($prefixMultiplier, 1)) {
            return new self($initialUnit, $finalUnit, $this->multiplier, $this->offset);
        }

        // Apply the prefix multiplication (scale the multiplier, keep offset unchanged).
        $m = $this->multiplier->mul(new NumberWithError($prefixMultiplier));
        return new self($initialUnit, $finalUnit, $m, $this->offset);
    }

    /**
     * Compose two conversions by method 1.
     * For when you have initial->common and common->final.
     *
     * b = a * m1 + k1
     * c = b * m2 + k2
     * =>
     * c = a * (m1 * m2) + (k1 * m2 + k2)
     *
     * @param self $other The second conversion (common->final).
     * @return self The combined conversion (initial->final).
     */
    public function combine1(self $other): self {
        $m1 = $this->multiplier;
        $k1 = $this->offset;
        $m2 = $other->multiplier;
        $k2 = $other->offset;

        // m = m1 * m2
        $m = $m1->mul($m2);
        // k = k1 * m2 + k2
        $k = $k1->mul($m2)->add($k2);
        // Result is initial->final
        return new self($this->initialUnit, $other->finalUnit, $m, $k);
    }

    /**
     * Combines two conversions by method 2.
     * For when you have initial->common and final->common.
     *
     * b = a * m1 + k1
     * b = c * m2 + k2
     * =>
     * c = a * (m1 / m2) + ((k1 - k2) / m2)
     *
     * @param self $other The second conversion (final->common).
     * @return self The combined conversion (initial->final).
     */
    public function combine2(self $other): self {
        $m1 = $this->multiplier;
        $k1 = $this->offset;
        $m2 = $other->multiplier;
        $k2 = $other->offset;

        // m = m1 / m2
        $m = $m1->div($m2);
        // k = (k1 - k2) / m2
        $k = ($k1->sub($k2))->div($m2);
        // Result is initial->final
        return new self($this->initialUnit, $other->initialUnit, $m, $k);
    }

    /**
     * Combines two conversions by method 3.
     * For when you have common->initial and common->final.
     *
     * a = b * m1 + k1
     * c = b * m2 + k2
     * =>
     * c = a * (m2 / m1) + (k2 - (k1 * m2 / m1))
     *
     * @param self $other The second conversion (common->final).
     * @return self The combined conversion (initial->final).
     */
    public function combine3(self $other): self {
        $m1 = $this->multiplier;
        $k1 = $this->offset;
        $m2 = $other->multiplier;
        $k2 = $other->offset;

        // m = m2 / m1
        $m = $m2->div($m1);
        // k = k2 - (k1 * m2 / m1)
        //   = k2 - (k1 * m)
        $k = $k2->sub($k1->mul($m));
        // Result is initial->final
        return new self($this->finalUnit, $other->finalUnit, $m, $k);
    }

    /**
     * Combines two conversions by method 4.
     * For when you have common->initial and final->common.
     *
     * a = b * m1 + k1
     * b = c * m2 + k2
     * =>
     * c = a / (m1 * m2) + (-k2 - (k1 / m1)) / m2
     *
     * @param self $other The second conversion (final->common).
     * @return self The combined conversion (initial->final).
     */
    public function combine4(self $other): self {
        $m1 = $this->multiplier;
        $k1 = $this->offset;
        $m2 = $other->multiplier;
        $k2 = $other->offset;

        // m = 1 / (m1 * m2)
        $m = $m1->mul($m2)->inv();
        // k = (-k2 - (k1 / m1)) / m2
        $k = $k2->neg()->sub($k1->div($m1))->div($m2);
        // Result is initial->final
        return new self($this->finalUnit, $other->initialUnit, $m, $k);
    }

    /**
     * Express this conversion as a string.
     *
     * @return string The string representation of this conversion.
     */
    public function __toString(): string {
        $str = "$this->finalUnit = $this->initialUnit";
        if (!Numbers::equal($this->multiplier->value, 1)) {
            $str .= " * {$this->multiplier->value}";
        }
        if (!Numbers::equal($this->offset->value, 0)) {
            $sign = $this->offset->value < 0 ? '-' : '+';
            $str .= " $sign " . abs($this->offset->value);
        }
        $str .= " (error score: $this->error)";
        return $str;
    }
}
