<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use DivisionByZeroError;
use Galaxon\Core\Floats;
use Galaxon\Core\Numbers;

/**
 * Represents a numeric value with an associated error score.
 *
 * The error score is a heuristic measure of accumulated floating-point error,
 * used to find optimal conversion paths through unit conversion graphs.
 * Lower scores indicate more exact values.
 */
class NumberWithErrorOld
{
    public readonly int|float $value;

    public readonly float $error;

    private const int MAX_SAFE_INTEGER = 9007199254740992; // 2^53

    /**
     * Constructor.
     *
     * @param int|float $value The numeric value.
     * @param ?float $error Optional error score. If null, calculated from the value.
     */
    public function __construct(int|float $value = 0, ?float $error = null)
    {
        $this->value = $value;
        $this->error = $error ?? (float)self::exactnessScore($value);
    }

    /**
     * Check if the value is zero.
     *
     * @return bool True if value is zero, false otherwise.
     */
    public function isZero(): bool
    {
        return Numbers::equal($this->value, 0);
    }

    /**
     * Check if the value is one.
     *
     * @return bool True if value is one, false otherwise.
     */
    public function isOne(): bool
    {
        return Numbers::equal($this->value, 1);
    }

    /**
     * Returns a score indicating how exact a float is likely to be.
     *
     * Integers are treated as exact.
     * For non-integers, the score is calculated from the number of digits in the string representation.
     *
     * @param int|float $value The value to score.
     * @return int The error score.
     */
    public static function exactnessScore(int|float $value): int
    {
        // Assume integers are exact.
        if (is_int($value)) {
            return 0;
        }

        // If the float represents an integer, with no loss of precision, assume it's exact.
        $i = Floats::tryConvertToInt($value);
        if ($i !== null && $i >= -self::MAX_SAFE_INTEGER && $i <= self::MAX_SAFE_INTEGER) {
            return 0;
        }

        // Get the float as a string.
        $s = (string)$value;

        // If the string contains an exponent, remove it.
        $ePos = stripos($s, 'e');
        if ($ePos !== false) {
            $s = substr($s, 0, $ePos);
        }

        // Strip off trailing zeros and decimal point.
        $s = trim($s, '0.');
//        echo "$s\n";

        // Count the number of digits remaining in the string (which equal the number of significant digits).
        $nSigFigs = (int)preg_match_all('/\d/', $s);

        return $nSigFigs < 13 ? 1 : 2;
    }

    /**
     * Convert a value to a NumberWithError, if necessary.
     *
     * @param int|float|self $value The value to convert.
     * @return self The converted value.
     */
    private static function toNWE(int|float|self $value): self
    {
        return $value instanceof self ? $value : new self($value);
    }

    /**
     * Add another NumberWithError to this one.
     *
     * @param int|float|self $other The value to add.
     * @return self A new NumberWithError representing the sum.
     */
    public function add(int|float|self $other): self
    {
        // Ensure other is a NumberWithError.
        $other = self::toNWE($other);

        // Handle simple cases to avoid increasing error score.
        // Addition of 0 is always the same as the original factor.
        if ($this->isZero()) {
            return $other;
        }
        if ($other->isZero()) {
            return $this;
        }

        // Calculate the sum.
        $result = $this->value + $other->value;

        // Calculate the error score of the result.
        $error = $this->error + $other->error + self::exactnessScore($result);

        return new self($result, $error);
    }

    /**
     * Subtract another NumberWithError from this one.
     *
     * @param int|float|self $other The value to subtract.
     * @return self A new NumberWithError representing the difference.
     */
    public function sub(int|float|self $other): self
    {
        // Ensure other is a NumberWithError.
        $other = self::toNWE($other);

        // Handle simple cases.
        if ($this->isZero()) {
            // Negate the second operand.
            return $other->neg();
        }
        if ($other->isZero()) {
            // Return the first operand.
            return $this;
        }

        // Calculate the difference.
        $result = $this->value - $other->value;

        // Calculate the error score of the result.
        $error = $this->error + $other->error + self::exactnessScore($result);

        // Return the result.
        return new self($result, $error);
    }

    /**
     * Negate this value.
     *
     * @return self A new NumberWithError with negated value but same error score.
     */
    public function neg(): self
    {
        // Negate the value.
        return new self(-$this->value, $this->error);
    }

    /**
     * Multiply this value by another NumberWithError.
     *
     * Adds a penalty of +1 to the error score for the multiplication operation.
     *
     * @param int|float|self $other The value to multiply by.
     * @return self A new NumberWithError representing the product.
     */
    public function mul(int|float|self $other): self
    {
        // Ensure other is a NumberWithError.
        $other = self::toNWE($other);

        // Handle simple cases to avoid increasing error score.
        // Multiplication by 0 is always 0.
        if ($this->isZero()) {
            return new self();
        }
        if ($other->isZero()) {
            return new self();
        }

        // Multiplication by 1 is always equal to the original factor.
        if ($this->isOne()) {
            return $other;
        }
        if ($other->isOne()) {
            return $this;
        }

        // Calculate the product.
        $result = $this->value * $other->value;

        // Calculate the error score of the result.
        $error = $this->error + $other->error + self::exactnessScore($result);

        // Return the result.
        return new self($result, $error);
    }

    /**
     * Divide this value by another NumberWithError.
     *
     * Adds a penalty of +2 to the error score for the division operation.
     *
     * @param int|float|self $other The divisor.
     * @return self A new NumberWithError representing the quotient.
     * @throws DivisionByZeroError If the divisor is zero.
     */
    public function div(int|float|self $other): self
    {
        // Ensure other is a NumberWithError.
        $other = self::toNWE($other);

        // Check for division by zero.
        if ($other->isZero()) {
            throw new DivisionByZeroError('Cannot divide by zero.');
        }

        // Handle the simple case of dividing 0 by something.
        if ($this->isZero()) {
            return new self();
        }

        // Handle the simple case of dividing by 1.
        if ($other->isOne()) {
            return $this;
        }

        // Calculate the quotient.
        $result = $this->value / $other->value;

        // Calculate the error score of the result.
        $error = $this->error + $other->error + self::exactnessScore($result);

        // Return the result.
        return new self($result, $error);
    }

    /**
     * Compute the multiplicative inverse (reciprocal) of this value.
     *
     * Equivalent to 1 / value. Adds a penalty of +2 to the error score.
     *
     * @return self A new NumberWithError representing the reciprocal.
     * @throws DivisionByZeroError If this value is zero.
     */
    public function inv(): self
    {
        // Check for division by zero.
        if ($this->isZero()) {
            throw new DivisionByZeroError('Cannot invert zero.');
        }

        // Handle the simple case of inverting 1.
        if ($this->isOne()) {
            return $this; // 1/1 = 1
        }

        // Calculate the reciprocal.
        $value = 1.0 / $this->value;

        // Calculate the error score of the result.
        $error = $this->error + self::exactnessScore($value);

        // Return the result.
        return new self($value, $error);
    }
}
