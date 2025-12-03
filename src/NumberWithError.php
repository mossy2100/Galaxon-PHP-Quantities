<?php

declare(strict_types=1);

namespace Galaxon\Units;

use DivisionByZeroError;

/**
 * Represents a floating-point number with tracked absolute error.
 *
 * This class propagates numerical errors through arithmetic operations,
 * providing a way to track precision loss in calculations.
 */
class NumberWithError
{
    /**
     * The numeric value.
     *
     * @var float
     */
    private(set) float $value;

    /**
     * The absolute error (uncertainty) in the value.
     *
     * @var float
     */
    private(set) float $absoluteError;

    /**
     * Get the relative error (absolute error divided by value).
     *
     * Returns INF if value is zero but error is non-zero.
     * Returns 0.0 if both value and error are zero.
     *
     * @var float
     */
    public float $relativeError {
        get {
            if ($this->value === 0.0) {
                return $this->absoluteError === 0.0 ? 0.0 : INF;
            }
            return abs($this->absoluteError / $this->value);
        }
    }

    /**
     * Constructor.
     *
     * @param int|float $value The numeric value.
     * @param float|null $error The absolute error. If null, estimated from float precision.
     */
    public function __construct(int|float $value, ?float $error = null)
    {
        // Convert value to float.
        $value = (float)$value;

        // Set the value.
        $this->value = $value;

        // If the error isn't given, compute an initial error estimate.
        if ($error === null) {
            $this->absoluteError = $this->isExactFloat($this->value) ? 0.0 : self::ulp($this->value) * 0.5;
        } else {
            $this->absoluteError = $error;
        }
    }

    /**
     * Calculate the Unit in Last Place (ULP) - the spacing between adjacent floats.
     *
     * @param float $value The value to calculate ULP for.
     * @return float The ULP spacing.
     */
    private static function ulp(float $value): float
    {
        if (!is_finite($value)) {
            return INF;
        }
        $abs = abs($value);
        if ($abs === 0.0) {
            return PHP_FLOAT_EPSILON * PHP_FLOAT_MIN;
        }
        // For normalized numbers.
        return $abs * PHP_FLOAT_EPSILON;
    }

    /**
     * Check if a float value is exactly representable (no rounding error).
     *
     * Returns true for finite integers within the exact range (±2^53).
     *
     * @param float $value The value to check.
     * @return bool True if the value is exact, false otherwise.
     */
    private function isExactFloat(float $value): bool
    {
        return is_finite($value) && floor($value) === $value && abs($value) <= (1 << 53);
    }

    // region Arithmetic operations with error propagation

    /**
     * Add another NumberWithError to this one.
     *
     * Error propagation: absolute errors add.
     *
     * @param self $other The number to add.
     * @return self A new NumberWithError with the sum and propagated error.
     */
    public function add(self $other): self
    {
        $newValue = $this->value + $other->value;
        $newError = $this->absoluteError + $other->absoluteError;

        // Only add rounding error if we already had error.
        if ($newError > 0) {
            $newError += self::ulp($newValue) * 0.5;
        }

        return new self($newValue, $newError);
    }

    /**
     * Subtract another NumberWithError from this one.
     *
     * Error propagation: absolute errors add.
     *
     * @param self $other The number to subtract.
     * @return self A new NumberWithError with the difference and propagated error.
     */
    public function sub(self $other): self
    {
        $newValue = $this->value - $other->value;
        $newError = $this->absoluteError + $other->absoluteError;

        // Only add rounding error if we already had error.
        if ($newError > 0) {
            $newError += self::ulp($newValue) * 0.5;
        }

        return new self($newValue, $newError);
    }

    /**
     * Negate this number.
     *
     * Error propagation: error magnitude unchanged.
     *
     * @return self A new NumberWithError with negated value and same error.
     */
    public function neg(): self
    {
        return new self(-$this->value, $this->absoluteError);
    }

    /**
     * Multiply this NumberWithError by another.
     *
     * Error propagation: relative errors add.
     *
     * @param self $other The number to multiply by.
     * @return self A new NumberWithError with the product and propagated error.
     */
    public function mul(self $other): self
    {
        $newValue = $this->value * $other->value;

        // Relative errors add in multiplication.
        $relError = $this->relativeError + $other->relativeError;
        $newError = abs($newValue) * $relError;

        // Only add rounding error if we already had error.
        if ($newError > 0) {
            $newError += self::ulp($newValue) * 0.5;
        }

        return new self($newValue, $newError);
    }

    /**
     * Divide this NumberWithError by another.
     *
     * Error propagation: relative errors add.
     *
     * @param self $other The divisor.
     * @return self A new NumberWithError with the quotient and propagated error.
     * @throws DivisionByZeroError If attempting to divide by zero.
     */
    public function div(self $other): self
    {
        if ($other->value === 0.0) {
            throw new DivisionByZeroError();
        }

        $newValue = $this->value / $other->value;

        // Relative errors add in division.
        $relError = $this->relativeError + $other->relativeError;
        $newError = abs($newValue) * $relError;

        // Only add rounding error if result isn't exact or we already had error.
        if ($newError > 0 || !$this->isExactFloat($newValue)) {
            $newError += self::ulp($newValue) * 0.5;
        }

        return new self($newValue, $newError);
    }

    /**
     * Calculate the multiplicative inverse (1/x).
     *
     * Error propagation: relative error unchanged.
     *
     * @return self A new NumberWithError with the inverse and propagated error.
     * @throws DivisionByZeroError If attempting to invert zero.
     */
    public function inv(): self
    {
        if ($this->value === 0.0) {
            throw new DivisionByZeroError();
        }

        $newValue = 1.0 / $this->value;

        // For 1/x, relative error is same as input.
        $relError = $this->relativeError;
        $newError = abs($newValue) * $relError;

        // Only add rounding error if we already had error.
        if ($newError > 0 || !$this->isExactFloat($newValue)) {
            $newError += self::ulp($newValue) * 0.5;
        }

        return new self($newValue, $newError);
    }

    // endregion

    // region Quality metrics

    /**
     * Calculate the number of reliable significant digits.
     *
     * Based on the relative error, determines how many decimal digits
     * can be trusted in the value.
     *
     * @return int The number of significant digits (PHP_INT_MAX for exact values).
     */
    public function significantDigits(): int
    {
        if ($this->absoluteError === 0.0) {
            return PHP_INT_MAX; // Exact.
        }

        if (!is_finite($this->absoluteError) || $this->value === 0.0) {
            return 0;
        }

        $digits = -log10($this->relativeError);
        return max(0, (int)floor($digits));
    }

    /**
     * Convert to string representation showing value, error, and precision.
     *
     * @return string Formatted as "value ± error (N sig. digits)".
     */
    public function __toString(): string
    {
        return sprintf(
            "%.15g ± %.2e (%d sig. digits)",
            $this->value,
            $this->absoluteError,
            $this->significantDigits()
        );
    }

    // endregion
}
