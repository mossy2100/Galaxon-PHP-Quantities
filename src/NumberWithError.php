<?php

declare(strict_types=1);

namespace Galaxon\Units;

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
class NumberWithError
{
    public readonly int|float $value;

    public readonly int $error;

    private const MAX_SAFE_INTEGER = 9007199254740992; // 2^53

    /**
     * Constructor.
     *
     * @param int|float $value The numeric value.
     * @param ?int $error Optional error score. If null, calculated from the value.
     */
    public function __construct(int|float $value = 0, ?int $error = null)
    {
        $this->value = $value;
        $this->error = $error ?? self::exactnessScore($value);
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
     * @param int|float $value The value to score.
     * @return int The score, ranging from 0 (probably exact) to 2 (probably inexact).
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

        // If the string contains an 'E', strip off anything from the 'E' onwards.
        $ePos = stripos($s, 'E');
        if ($ePos !== false) {
            $s = substr($s, 0, $ePos);
        }

        // Trim any leading or trailing zeros or decimal point.
        $s = trim($s, '0.');

        // Count the number of significant figures. If it has more than 13, it's likely to be inexact.
        $nSigFigs = preg_match_all('/\d/', $s);
        return $nSigFigs <= 13 ? 2 : 5;
    }

    /**
     * Add another NumberWithError to this one.
     *
     * @param self $other The value to add.
     * @return self A new NumberWithError representing the sum.
     */
    public function add(self $other): self
    {
        // Handle simple cases to avoid increasing error score.
        // Addition of 0 is always the same as the original factor.
        if ($this->isZero()) {
            return $other;
        }
        if ($other->isZero()) {
            return $this;
        }

        // Calculate the sum.
        $value = $this->value + $other->value;

        // Calculate the error score of the result.
        $error = $this->error + $other->error + self::exactnessScore($value);
//        $error = self::exactnessScore($value);

        return new self($value, $error);
    }

    /**
     * Subtract another NumberWithError from this one.
     *
     * @param self $other The value to subtract.
     * @return self A new NumberWithError representing the difference.
     */
    public function sub(self $other): self
    {
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
        $value = $this->value - $other->value;

        // Calculate the error score of the result.
        $error = $this->error + $other->error + self::exactnessScore($value);
//        $error = self::exactnessScore($value);

        // Return the result.
        return new self($value, $error);
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
     * @param self $other The value to multiply by.
     * @return self A new NumberWithError representing the product.
     */
    public function mul(self $other): self
    {
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
        $prod = $this->value * $other->value;

        // If all are integers, we'll make the operation cost 0 instead of 1, because it's exact.
        $op1Exactness = self::exactnessScore($this->value);
        $op2Exactness = self::exactnessScore($other->value);
        $prodExactness = self::exactnessScore($prod);
//        $opCost = $op1Exactness === 0 && $op2Exactness === 0 && $prodExactness === 0 ? 0 : 1;

        // Calculate the error score of the result.
        $error = $this->error + $other->error + $prodExactness;
//        $error = $prodExactness;

        // Return the result.
        return new self($prod, $error);
    }

    /**
     * Divide this value by another NumberWithError.
     *
     * Adds a penalty of +2 to the error score for the division operation.
     *
     * @param self $other The divisor.
     * @return self A new NumberWithError representing the quotient.
     * @throws DivisionByZeroError If the divisor is zero.
     */
    public function div(self $other): self
    {
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
        $quot = $this->value / $other->value;

        // If all are integers, we'll make the operation cost 0 instead of 1, because it's exact.
        $op1Exactness = self::exactnessScore($this->value);
        $op2Exactness = self::exactnessScore($other->value);
        $quotExactness = self::exactnessScore($quot);
//        $opCost = $op1Exactness === 0 && $op2Exactness === 0 && $quotExactness === 0 ? 0 : 1;

        // Calculate the error score of the result.
        $error = $this->error + $other->error + $quotExactness;
//        $error = $op1Exactness;

        // Return the result.
        return new self($quot, $error);
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
