<?php

declare(strict_types=1);

namespace Galaxon\Units;

use DivisionByZeroError;
use Galaxon\Core\Comparable;
use Galaxon\Core\Equatable;
use Galaxon\Core\Floats;
use Galaxon\Core\Types;
use LogicException;
use Override;
use ReflectionClass;
use Stringable;
use TypeError;
use ValueError;

abstract class Measurement implements Stringable, Equatable
{
    use Comparable;

    // region Instance properties

    /**
     * The size of the Measurement in the specified unit.
     *
     * @var float
     */
    public readonly float $value;

    /**
     * The unit of the Measurement.
     *
     * @var string
     */
    public readonly string $unit;

    // endregion

    // region Static properties

    /**
     * The UnitConverter objects that manage conversions for Measurement types implemented in derived classes.
     *
     * @var array<class-string, UnitConverter>
     */
    private static array $unitConverters = [];

    /**
     * Metric prefixes.
     *
     * @var array<string, int|float>
     */
    public const METRIC_PREFIXES = [
        // Large prefixes:
        'Q'  => 1e30,   // quetta
        'R'  => 1e27,   // ronna
        'Y'  => 1e24,   // yotta
        'Z'  => 1e21,   // zetta
        'E'  => 1e18,   // exa
        'P'  => 1e15,   // peta
        'T'  => 1e12,   // tera
        'G'  => 1e9,    // giga
        'M'  => 1e6,    // mega
        'k'  => 1e3,    // kilo
        'h'  => 1e2,    // hecto
        'da' => 1e1,    // deca

        // Small prefixes:
        'd'  => 1e-1,   // deci
        'c'  => 1e-2,   // centi
        'm'  => 1e-3,   // milli
        'μ'  => 1e-6,   // micro
        'u'  => 1e-6,   // micro (alternate symbol)
        'n'  => 1e-9,   // nano
        'p'  => 1e-12,  // pico
        'f'  => 1e-15,  // femto
        'a'  => 1e-18,  // atto
        'z'  => 1e-21,  // zepto
        'y'  => 1e-24,  // yocto
        'r'  => 1e-27,  // ronto
        'q'  => 1e-30,  // quecto
    ];

    // endregion

    // region Constructor

    /**
     * Constructor.
     *
     * @param float $value The size of the Measurement in the given unit.
     * @param string $unit The unit of the Measurement.
     * @throws ValueError If the size is non-finite (±∞ or NaN) or if the unit is invalid.
     * @throws LogicException If the calling class has not been properly set up.
     */
    public function __construct(float $value, string $unit)
    {
        // Check the value is finite.
        if (!is_finite($value)) {
            throw new ValueError('Measurement size cannot be ±∞ or NaN.');
        }

        // Ensure the UnitConverter has been validated and created.
        $unitConverter = static::getUnitConverter();

        // Check the unit is valid.
        $unitConverter->checkUnitIsValid($unit);

        // Set the properties.
        $this->value = $value;
        $this->unit = $unit;
    }

    // endregion

    // region Factory methods

    /**
     * Converts a string into a Measurement.
     *
     * If valid, the new Measurement is returned; otherwise, an exception is thrown.
     *
     * @param string $value The string to parse.
     * @return static A new Measurement equivalent to the provided string.
     * @throws ValueError If the string does not represent a valid Measurement of the derived type.
     */
    public static function parse(string $value): static
    {
        // Prepare an error message with the original value.
        $class = new ReflectionClass(static::class)->getShortName();
        $err = "The provided string '$value' does not represent a valid $class.";

        // Reject empty input.
        $value = trim($value);
        if ($value === '') {
            throw new ValueError($err);
        }

        // Look for <num><unit>.
        // Whitespace between the number and unit is permitted, and the unit must be valid (case-sensitive).
        $num = '[-+]?(\d+(?:\.\d+)?|\.\d+)(?:[eE][+-]?\d+)?';
        $units = implode('|', static::getUnitConverter()->getValidUnits());
        if (preg_match("/^($num)\s*($units)$/", $value, $m)) {
            return new static((float)$m[1], $m[2]);
        }

        // Invalid format.
        throw new ValueError($err);
    }

    // endregion

    // region Instance methods

    /**
     * Get a new Measurement, equivalent to this one, but in the given unit.
     *
     * @param string $unit The unit to convert the Measurement to.
     * @return static The Measurement in the specified unit.
     * @throws ValueError If the target unit is invalid.
     * @throws LogicException If no conversion between the units could be found.
     */
    public function to(string $unit): static
    {
        // Convert the value to the target unit.
        $value = static::getUnitConverter()->convert($this->value, $this->unit, $unit);

        // Return the new Measurement.
        return new static($value, $unit);
    }

    /**
     * Format the Measurement as a string.
     *
     * By default, there will be a space between the number and the unit, e.g. '45.67 km'.
     * You can remove the space by setting $includeSpace to false.
     *
     * @param string $specifier The format specifier to use (default is 'f').
     * @param ?int $precision Number of digits to show, behavior depending on the specifier.
     * @param bool $trimZeros If true (default), remove trailing zeros and decimal point from the output.
     * @param bool $includeSpace If true (default), put a space between the value and the unit.
     * @return string The Measurement as a string.
     * @throws ValueError If $specifier or $precision are invalid.
     *
     * @example
     *  $angle->format('f', 2, true)  // "90.00 deg"
     *  $angle->format('e', 3)        // "1.571e+0rad"
     *  $angle->format('f', 0)        // "90deg"
     *
     * @see static::formatValue() for more information on the formatting options.
     */
    public function format(
        string $specifier = 'f',
        ?int $precision = null,
        bool $trimZeros = true,
        bool $includeSpace = true
    ): string
    {
        // Return the formatted string. Arguments will be validated in formatFloat().
        return static::formatValue($this->value, $specifier, $precision, $trimZeros)
               . ($includeSpace ? ' ' : '') . static::formatUnit($this->unit);
    }

    /**
     * Return the Measurement as a string.
     *
     * Uses PHP's default float-to-string conversion, which is simple and fast.
     * For formatted output with control over decimals, spaces, and fixed vs. scientific notation, use format().
     *
     * @return string The Measurement as a string.
     * @example
     *  echo $angle;  // "1.5707963267949 rad"
     */
    #[Override]
    public function __toString(): string
    {
        return Floats::normalizeZero($this->value) . ' ' . static::formatUnit($this->unit);
    }

    /**
     * Compare two measurements of the same type.
     *
     * @param mixed $other The Measurement to compare with.
     * @param float $epsilon The maximum absolute difference between the values to still consider them equal.
     * @return int -1 if this < other, 0 if equal, 1 if this > other.
     * @throws TypeError If the Measurement to compare with is a different type.
     * @throws LogicException If no conversion between the units could be found.
     */
    #[Override]
    public function compare(mixed $other, float $epsilon = Floats::EPSILON): int
    {
        // Check the two measurements have the same types.
        if (!$this->hasSameType($other)) {
            throw new TypeError('The two measurements being compared must be of the same types.');
        }

        // Get the other Measurement in the same unit as this one.
        /** @var Measurement $other */
        $other_value = $this->unit === $other->unit ? $other->value : $other->to($this->unit)->value;

        // Compare the values.
        return Floats::compare($this->value, $other_value, $epsilon);
    }

    // endregion

    // region Arithmetic methods

    /**
     * Add a Measurement to this one.
     *
     * @param self|int|float $otherOrValue The other Measurement, or the value of the other Measurement.
     * @param null|string $otherUnit Null, or the unit of the other Measurement.
     * @return static The sum as a new Measurement.
     * @throws TypeError If the argument types are incorrect.
     * @throws ValueError If the value is non-finite (±∞ or NaN) or if the unit is invalid.
     * @throws LogicException If no conversion could be found between the units.
     */
    public function add(self|int|float $otherOrValue, ?string $otherUnit = null): static
    {
        // Validate the arguments.
        $other = self::checkAddSubArgs($otherOrValue, $otherUnit);

        // Get the other Measurement in the same unit as this one.
        $other_value = $this->unit === $other->unit
            ? $other->value
            : self::getUnitConverter()->convert($other->value, $other->unit, $this->unit);

        // Add the two values.
        return new static($this->value + $other_value, $this->unit);
    }

    /**
     * Subtract another Measurement from this Measurement.
     *
     * @param self|int|float $otherOrValue The other Measurement, or the value of the other Measurement.
     * @param null|string $otherUnit Null, or the unit of the other Measurement.
     * @return static The difference as a new Measurement.
     * @throws TypeError If the argument types are incorrect.
     * @throws ValueError If the size is non-finite (±∞ or NaN) or if the unit is invalid.
     * @throws LogicException If no conversion could be found between the units.
     */
    public function sub(self|int|float $otherOrValue, ?string $otherUnit = null): static
    {
        // Validate the arguments.
        $other = self::checkAddSubArgs($otherOrValue, $otherUnit);

        // Get the other Measurement in the same unit as this one.
        $other_value = $this->unit === $other->unit
            ? $other->value
            : self::getUnitConverter()->convert($other->value, $other->unit, $this->unit);

        // Subtract the values.
        return new static($this->value - $other_value, $this->unit);
    }

    /**
     * Multiply this Measurement by a factor.
     *
     * @param float $k The scale factor.
     * @return static The scaled Measurement.
     * @throws ValueError If the multiplier is a non-finite number.
     */
    public function mul(float $k): static
    {
        // Guard.
        if (!is_finite($k)) {
            throw new ValueError('Multiplier cannot be ±∞ or NaN.');
        }

        // Multiply the Measurement.
        return new static($this->value * $k, $this->unit);
    }

    /**
     * Divide this Measurement by a factor.
     *
     * @param float $k The scale factor.
     * @return static The scaled Measurement.
     * @throws DivisionByZeroError If the divisor is 0.
     * @throws ValueError If the divisor is a non-finite number.
     */
    public function div(float $k): static
    {
        // Guards.
        if ($k === 0.0) {
            throw new DivisionByZeroError('Divisor cannot be 0.');
        }
        if (!is_finite($k)) {
            throw new ValueError('Divisor cannot be ±∞ or NaN.');
        }

        // Divide the Measurement.
        return new static(fdiv($this->value, $k), $this->unit);
    }

    /**
     * Get the absolute value of this Measurement.
     *
     * @return static A new Measurement with the same unit and a non-negative value.
     */
    public function abs(): static
    {
        return new static(abs($this->value), $this->unit);
    }

    // endregion

    // region Static abstract methods (must be implemented in derived classes)

    /**
     * The base units for this Measurement type. None should have a multiplicative prefix (metric, binary or other).
     *
     * Keys are units as strings; values are booleans indicating whether the unit can be used with a prefix or not.
     *
     * Example units that can be used with a prefix:
     * - 's' (second)
     * - 'm' (metre)
     * - 'g' (gram)
     * - 'K' (Kelvin)
     * - 'rad' (radian)
     * - 'B' (byte)
     *
     * @return array<string, bool> The base units for this Measurement type.
     */
    abstract public static function getBaseUnits(): array;

    // endregion

    // region Static methods that can be overridden in derived classes

    /**
     * Get the list of valid prefixes for this Measurement type.
     *
     * This can be overridden by derived classes to provide custom prefixes.
     *
     * @return array<string, int|float>
     */
    public static function getPrefixes(): array
    {
        return self::METRIC_PREFIXES;
    }

    /**
     * Get the conversion factors between units.
     *
     * Each conversion is an array with 3-4 elements:
     * - [0] string: Initial unit
     * - [1] string: Final unit
     * - [2] float: Multiplier (must be positive)
     * - [3] float: Optional offset
     *
     * @return array<int, array> Array of conversion definitions.
     */
    public static function getConversions(): array
    {
        return [];
    }

    /**
     * Format a float with a given precision and specifier.
     *
     * NB: This is a protected method called from format().
     * This can be overridden by derived classes to provide custom formatting of the value.
     *
     * The meaning of the precision argument varies according to the specifier:
     * - For 'f': decimal places
     * - For 'e': mantissa decimals
     * - For 'g': significant digits
     *
     * For more information on specifier codes:
     * @see https://www.php.net/manual/en/function.sprintf.php
     *
     * @param float $value The value to format.
     * @param string $specifier The format specifier to use (default is 'f').
     * @param ?int $precision Number of digits to show, depending on the specifier.
     * @param bool $trimZeros If true (default), remove trailing zeros and decimal point from the output.
     * @return string The formatted string.
     * @throws ValueError If any arguments are invalid.
     */
    protected static function formatValue(
        float $value,
        string $specifier = 'f',
        ?int $precision = null,
        bool $trimZeros = true
    ): string
    {
        // Validate the value (defensive check since this is a protected method).
        if (!is_finite($value)) {
            throw new ValueError('The value to format must be finite.');
        }

        // Validate the specifier.
        if (!in_array($specifier, ['e', 'E', 'f', 'F', 'g', 'G'], true)) {
            throw new ValueError("The specifier must be 'e', 'E', 'f', 'F', 'g', or 'G'.");
        }

        // Validate the precision.
        if ($precision !== null && ($precision < 0 || $precision > 17)) {
            throw new ValueError('The precision must be null or an integer between 0 and 17.');
        }

        // Canonicalize -0.0 to 0.0.
        $value = Floats::normalizeZero($value);

        // Format with the desired precision and specifier.
        // If the precision is null, omit it from the format string to use the sprintf default (usually 6).
        $formatString = $precision === null ? "%{$specifier}" : "%.{$precision}{$specifier}";
        $str = sprintf($formatString, $value);

        // Remove trailing zeros and decimal point from the number (i.e. the part before the 'E' or 'e', if present).
        if ($trimZeros) {
            $ePos = stripos($str, 'E');
            if ($ePos !== false) {
                $str = rtrim(substr($str, 0, $ePos), '0.') . substr($str, $ePos);
            } else {
                $str = rtrim($str, '0.');
            }
        }

        return $str;
    }

    /**
     * Format the unit.
     *
     * NB: This is a protected method called from format() and __toString().
     * This can be overridden by derived classes to provide custom formatting of the unit.
     *
     * @param string $unit The unit to format.
     * @return string The formatted unit string.
     */
    protected static function formatUnit(string $unit): string
    {
        // Convert 'u' to 'μ' if necessary. Looks better.
        $unitsPrefixOk = array_keys(array_filter(static::getBaseUnits()));
        if (preg_match('/^u(' . implode('|', $unitsPrefixOk) . ')$/', $unit)) {
            return 'μ' . substr($unit, 1);
        }

        return $unit;
    }

    // endregion

    // region Static helper methods

    /**
     * Get the fully qualified class name of the derived Measurement type.
     *
     * @return string The fully qualified class name.
     */
    private static function getClassName(): string
    {
        return static::class;
    }

    /**
     * Get the UnitConverter for the calling Measurement-derived class.
     * If it hasn't been constructed yet, do it now.
     *
     * @return UnitConverter
     * @throws LogicException If the UnitConverter couldn't be created due to invalid setup.
     */
    protected static function getUnitConverter(): UnitConverter
    {
        // Get the name of the calling class.
        $className = static::getClassName();

        // Check the unit converted for the derived class has been validated and created.
        if (!isset(self::$unitConverters[$className])) {
            // Initialize the UnitConverter for this Measurement type.
            // The UnitConverter constructor will validate the class setup.
            try {
                self::$unitConverters[$className] = new UnitConverter(
                    static::getBaseUnits(),
                    static::getPrefixes(),
                    static::getConversions()
                );
            } catch (LogicException $e) {
                throw new LogicException('The ' . static::getClassName() . ' class is not properly set up: ' .
                                         $e->getMessage());
            }
        }

        // Return the UnitConverter.
        return self::$unitConverters[$className];
    }

    /**
     * Validate the arguments for add() or sub().
     *
     * @param self|int|float $otherOrValue The other Measurement, or the value of the other Measurement.
     * @param null|string $otherUnit Null, or the unit of the other Measurement.
     * @return static The Measurement to add or subtract.
     * @throws TypeError If the argument types are incorrect.
     * @throws ValueError If the value is non-finite (±∞ or NaN) or if the unit is invalid.
     * @throws LogicException If the calling class has not been properly set up.
     */
    protected static function checkAddSubArgs(self|int|float $otherOrValue, ?string $otherUnit = null): static
    {
        if ($otherOrValue instanceof static && $otherUnit === null) {
            return $otherOrValue;
        }

        if (Types::isNumber($otherOrValue) && is_string($otherUnit)) {
            // This will throw if the value is non-finite or the unit is invalid.
            return new static($otherOrValue, $otherUnit);
        }

        // Invalid argument types.
        $class = static::getClassName();
        throw new TypeError("Invalid argument types. Either the first argument must be an object of " .
                            "type $class, and the second must be null or omitted; or, the first argument must be " .
                            "the value (int or float) of the measurement to add, and the second must be its unit " .
                            "(string).");
    }

    // endregion
}
