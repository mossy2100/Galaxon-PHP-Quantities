<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Core\Floats;
use Galaxon\Core\Numbers;
use Galaxon\Quantities\Internal\RegexHelper;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;
use InvalidArgumentException;
use Override;
use TypeError;

/**
 * Represents angle quantities.
 */
class Angle extends Quantity
{
    // region Constants

    /**
     * Epsilons for comparisons.
     */
    public const float RAD_EPSILON = 1e-9;
    public const float TRIG_EPSILON = 1e-15;

    // endregion

    // region Static properties

    /**
     * Default part unit symbols for output methods.
     *
     * @var list<string>
     */
    protected static array $defaultPartUnitSymbols = ['deg', 'arcmin', 'arcsec'];

    /**
     * Default result unit symbol for input methods.
     *
     * @var string
     */
    protected static string $defaultResultUnitSymbol = 'deg';

    // endregion

    // region Overridden methods

    /**
     * Unit definitions for angle.
     *
     * @return array<string, array{
     *      asciiSymbol: string,
     *      unicodeSymbol?: string,
     *      prefixGroup?: int,
     *      alternateSymbol?: string,
     *      systems: list<System>,
     *      expansionUnitSymbol?: string,
     *      expansionValue?: float
     *  }>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'radian'    => [
                'asciiSymbol' => 'rad',
                'prefixGroup' => PrefixRegistry::GROUP_METRIC,
                'systems'     => [System::Si],
            ],
            'degree'    => [
                'asciiSymbol'   => 'deg',
                'unicodeSymbol' => '°',
                'systems'       => [System::SiAccepted],
            ],
            'arcminute' => [
                'asciiSymbol'     => 'arcmin',
                'unicodeSymbol'   => '′',
                'alternateSymbol' => "'",
                'systems'         => [System::SiAccepted],
            ],
            'arcsecond' => [
                'asciiSymbol'     => 'arcsec',
                'unicodeSymbol'   => '″',
                'alternateSymbol' => '"',
                'prefixGroup'     => PrefixRegistry::GROUP_SMALL_METRIC,
                'systems'         => [System::SiAccepted],
            ],
            'gradian'   => [
                'asciiSymbol' => 'grad',
                'systems'     => [System::Common],
            ],
            'turn'      => [
                'asciiSymbol' => 'turn',
                'systems'     => [System::Common],
            ],
        ];
    }

    /**
     * Conversion factors for angle units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            ['turn', 'rad', Floats::TAU],
            ['turn', 'deg', 360],
            ['deg', 'arcmin', 60],
            ['arcmin', 'arcsec', 60],
            ['turn', 'grad', 400],
        ];
    }

    // endregion

    // region Inspection methods

    /**
     * Check if the Angle is in radians.
     *
     * @return bool
     */
    public function isRadians(): bool
    {
        return (string)$this->derivedUnit === 'rad';
    }

    // endregion

    // region Comparison methods

    /**
     * Check if this Angle is approximately equal to another.
     *
     * Overrides the parent method to use different default tolerances for comparing Angles.
     * For Angles, we want to compare the absolute difference in radians.
     *
     * @param mixed $other The value to compare with.
     * @param float $relTol The relative tolerance (default 0).
     * @param float $absTol The absolute tolerance (default 1e-9).
     * @return bool True if the values are equal, false otherwise (including for incompatible types).
     */
    #[Override]
    public function approxEqual(mixed $other, float $relTol = 0, float $absTol = self::RAD_EPSILON): bool
    {
        // Check for incompatible types.
        if (!$other instanceof self) {
            return false;
        }

        // Compare the values as radians.
        return Floats::approxEqual($this->toRadians(), $other->toRadians(), $relTol, $absTol);
    }

    // endregion

    // region Transformation methods

    /**
     * Get the size of the angle in radians.
     *
     * @return float
     */
    public function toRadians(): float
    {
        if ($this->isRadians()) {
            return $this->value;
        }

        return $this->to('rad')->value;
    }

    /**
     * Normalize an angle to a standard range.
     *
     * The range of values varies depending on the $unitsPerTurn parameter *and* the $signed flag.
     *
     * If $signed is true (default), the range is (-$unitsPerTurn/2, $unitsPerTurn/2]
     * This means the minimum value is *excluded* in the range, while the maximum value is *included*.
     * For radians, this is (-π, π]
     * For degrees, this is (-180, 180]
     *
     * If $signed is false, the range is [0, $unitsPerTurn)
     * This means the minimum value is *included* in the range, while the maximum value is *excluded*.
     * For radians, this is [0, τ)
     * For degrees, this is [0, 360)
     *
     * @param bool $signed If true, wrap to the signed range; otherwise wrap to the unsigned range.
     * @return Quantity A new angle with the wrapped value.
     *
     * @example
     * $alpha = new Angle(270, 'deg');
     * $wrapped = $alpha->wrap(); // now $wrapped->value == -90
     */
    public function wrap(bool $signed = true): Quantity
    {
        // Get the units per turn for the current unit.
        $unitsPerTurn = self::convert(1, 'turn', $this->derivedUnit);

        // Wrap the value.
        $r = Floats::wrap($this->value, $unitsPerTurn, $signed);

        // Return a new Angle with the wrapped value.
        return self::create($r, $this->derivedUnit);
    }

    // endregion

    // region Trigonometric methods

    /**
     * Sine of the angle.
     *
     * @return float The sine value.
     */
    public function sin(): float
    {
        return sin($this->toRadians());
    }

    /**
     * Cosine of the angle.
     *
     * @return float The cosine value.
     */
    public function cos(): float
    {
        return cos($this->toRadians());
    }

    /**
     * Tangent of the angle.
     *
     * @return float The tangent value.
     */
    public function tan(): float
    {
        $radians = $this->toRadians();
        $s = sin($radians);
        $c = cos($radians);

        // If cos is effectively zero, return ±INF (sign chosen by the side, i.e., sign of sine).
        // The built-in tan() function normally doesn't ever return ±INF.
        if (Floats::approxEqual($c, 0, 0, self::TRIG_EPSILON)) {
            return Numbers::copySign(INF, $s);
        }

        // Otherwise do IEEE‑754 division (no warnings/exceptions).
        return fdiv($s, $c);
    }

    /**
     * Secant of the angle (1/cos).
     *
     * @return float The secant value.
     */
    public function sec(): float
    {
        $c = cos($this->toRadians());

        // If cos is effectively zero, return ±INF.
        if (Floats::approxEqual($c, 0, 0, self::TRIG_EPSILON)) {
            return Numbers::copySign(INF, $c);
        }

        return fdiv(1.0, $c);
    }

    /**
     * Cosecant of the angle (1/sin).
     *
     * @return float The cosecant value.
     */
    public function csc(): float
    {
        $s = sin($this->toRadians());

        // If sin is effectively zero, return ±INF.
        if (Floats::approxEqual($s, 0, 0, self::TRIG_EPSILON)) {
            return Numbers::copySign(INF, $s);
        }

        return fdiv(1.0, $s);
    }

    /**
     * Cotangent of the angle (cos/sin).
     *
     * @return float The cotangent value.
     */
    public function cot(): float
    {
        $radians = $this->toRadians();
        $s = sin($radians);
        $c = cos($radians);

        // If sin is effectively zero, return ±INF.
        if (Floats::approxEqual($s, 0, 0, self::TRIG_EPSILON)) {
            return Numbers::copySign(INF, $c);
        }

        return fdiv($c, $s);
    }

    // endregion
}
