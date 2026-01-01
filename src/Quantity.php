<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use DivisionByZeroError;
use Galaxon\Core\Arrays;
use Galaxon\Core\Floats;
use Galaxon\Core\Numbers;
use Galaxon\Core\Traits\ApproxComparable;
use Galaxon\Core\Types;
use LogicException;
use Override;
use ReflectionClass;
use RuntimeException;
use Stringable;
use TypeError;
use ValueError;

/**
 * Abstract base class for physical measurements with units.
 *
 * Provides a framework for creating strongly-typed measurement classes (Length, Mass, Time, etc.)
 * with automatic unit conversion, arithmetic operations, and comparison capabilities.
 *
 * Derived classes must implement:
 * - getUnits(): Define the base and derived units, and specify the prefixes they accept.
 * (A derived unit is a base unit with an exponent, e.g. 'm3'.)
 * - Optionally override getConversions(): Define conversion factors between units
 *
 * Prefix system:
 * - Units can specify allowed prefixes using bitwise flags (PREFIX_CODE_METRIC, PREFIX_CODE_BINARY, etc.)
 * - Provides fine-grained control (e.g., radian can accept only small metric prefixes)
 * - Supports combinations (e.g., byte can accept both metric and binary prefixes)
 *
 * Features:
 * - Automatic validation of units and values
 * - Lazy initialization of UnitConverter for each measurement type
 * - Type-safe arithmetic operations (add, subtract, multiply, divide)
 * - Comparison and equality testing with epsilon tolerance
 * - Flexible string formatting and parsing
 */
class Quantity implements Stringable
{
    use ApproxComparable;

    // region Instance properties

    /**
     * The numeric value of the measurement in the specified unit.
     *
     * @var float
     */
    public readonly float $value;

    /**
     * The unit of the measurement.
     *
     * @var DerivedUnit
     */
    public readonly DerivedUnit $unit;

    // endregion

    // region Constructor

    /**
     * Constructor.
     *
     * Creates a new measurement with the specified value and unit.
     * Validates that the value is finite and the unit is valid.
     *
     * TODO Check if there's a registered type for the given unit
     *
     * The constructor is final because sometimes called as "new static" to instantiate a derived class.
     * In PHP, when overriding the constructor in a derived class, the signature may be altered.
     * We want to prevent that so "new static" expressions won't break.
     *
     * @param float $value The numeric value in the given unit.
     * @param string|DerivedUnit $unit The unit as a symbol string (e.g., 'kg', 'mm', 'hr') or object.
     * @throws ValueError If the value is non-finite (±INF or NAN) or if the unit symbol is invalid.
     */
    public function __construct(float $value, string|DerivedUnit $unit)
    {
        // Check the value is finite.
        if (!is_finite($value)) {
            throw new ValueError('Quantity value cannot be ±INF or NAN.');
        }

        // Set the value.
        $this->value = Floats::normalizeZero($value);

        // Convert the unit into an object if it isn't already.
        // This will throw if the unit is provided as a string that doesn't represent a valid DerivedUnit.
        $unit = DerivedUnit::toDerivedUnit($unit);

        // If the method is being called from a subclass, ensure the provided unit matches the class's dimension.
        if (static::class !== self::class) {
            $dimension = QuantityTypes::getDimensionByClass(static::class);
            if ($unit->dimension !== $dimension) {
                throw new TypeError("Cannot create " . static::class . ": expected dimension '$dimension', but unit '$unit' has dimension '$unit->dimension'.");
            }
        }

        $this->unit = $unit;
    }

    // endregion

    // region Static methods

    /**
     * Create a Quantity of the appropriate type for the given unit.
     *
     * Uses the dimension class registry to instantiate the correct subclass.
     * For example, a unit with dimension 'L2' will create an Area object if Area is registered.
     *
     * @param float $value The numeric value.
     * @param string|DerivedUnit $unit The unit.
     * @return static A Quantity of the appropriate type.
     */
    public static function create(float $value, string|DerivedUnit $unit): static
    {
        // Convert the unit to a DerivedUnit object if it isn't already.
        $unit = DerivedUnit::toDerivedUnit($unit);

        // Check if Quantity::create() was called rather than the method in a subclass.
        if (static::class === self::class) {
            // Check if there's a registered class for this dimension code.
            $qtyType = QuantityTypes::get($unit->dimension);
            $class = $qtyType['class'] ?? null;
            if ($class !== null) {
                // Create an object of the subclass.
                return new $class($value, $unit);
            }

            // Fall back to a generic Quantity.
            return new self($value, $unit);
        }

        // Create the new object. This will throw if the unit is invalid for the subclass.
        return new static($value, $unit);
    }

    /**
     * Parse a string representation into a Quantity object.
     *
     * Accepts formats like "123.45 km", "90deg", "1.5e3 ms".
     * Whitespace between value and unit is optional.
     *
     * @param string $value The string to parse.
     * @return static A new Quantity parsed from the string.
     * @throws ValueError If the string format is invalid.
     *
     * @example
     *   Length::parse("123.45 km")  // Length(123.45, 'km')
     *   Angle::parse("90deg")       // Angle(90.0, 'deg')
     *   Time::parse("1.5e3 ms")     // Time(1500.0, 'ms')
     */
    public static function parse(string $value): static
    {
        // Prepare an error message with the original value.
        $err = "The provided string '$value' does not represent a valid quantity.";

        // Reject empty input.
        $value = trim($value);
        if ($value === '') {
            throw new ValueError($err);
        }

        // Look for <num><unit>. Whitespace between the number and unit is permitted.
        $rxNum = '-?(?:\d+(?:\.\d+)?|\.\d+)(?:[eE][+-]?\d+)?';
        $rxUnitTerm = '\p{L}+(-?\d)?';
        $rxUnit = "$rxUnitTerm([*.·\/]$rxUnitTerm)*";
        if (preg_match("/^($rxNum)\s*($rxUnit)$/", $value, $m)) {
            return self::create((float)$m[1], $m[2]);
        }

        // Invalid format.
        throw new ValueError($err);
    }

    public static function convert(
        float $value,
        string|BaseUnit|UnitTerm|DerivedUnit $srcUnit,
        string|BaseUnit|UnitTerm|DerivedUnit $destUnit
    ): float
    {
        // Get the units as DerivedUnit objects.
        $srcUnit = DerivedUnit::toDerivedUnit($srcUnit);
        $destUnit = DerivedUnit::toDerivedUnit($destUnit);

        // Check the target unit has compatible dimensions.
        $srcDim = $srcUnit->dimension;
        $destDim = $destUnit->dimension;

        if ($srcDim !== $destDim) {
            throw new ValueError("Cannot convert from '$srcUnit' to '$destUnit' as these units represent different quantity types.");
        }

        // Expand the units.
        $srcUnit = $srcUnit->expand();
        $destUnit = $destUnit->expand();

        // Make sure they have the same number of terms. In theory this should never happen if the dimensions match, but
        // we'll keep this for safety.
        if (count($srcUnit->unitTerms) !== count($destUnit->unitTerms)) {
            throw new ValueError("Cannot convert from '$srcUnit' to '$destUnit' as these units have different numbers of terms.");
        }

        // Now we've validated the units, check for a shortcut. This could be done at the top of the function, but
        // it's probably better to make sure the method is being called with the correct units anyway, which should help
        // catch bugs early.
        if ($value === 0.0) {
            return 0.0;
        }

        // Calculate the conversion factor.
        $result = $value;

        foreach ($srcUnit->unitTerms as $i => $srcUnitTerm) {
            // Get the matching destination unit term.
            $destUnitTerm = $destUnit->unitTerms[$i];

            // Double check the dimensions match. May be able to remove this check later.
            if ($srcUnitTerm->dimension !== $destUnitTerm->dimension) {
                throw new RuntimeException('Source unit term dimension does not match destination unit term dimension.');
            }

            // Try to get the conversion factor between the two unit terms.
            $converter = Converter::get($srcUnitTerm->dimension);
            $conversion = $converter->getConversion($srcUnitTerm, $destUnitTerm);

            if ($conversion !== null) {
                $factor = $conversion->factor->value;
            }
            elseif ($srcUnitTerm->exponent === $destUnitTerm->exponent) {
                // If no conversion was found, see if we can find one between unit terms with no exponents.
                $srcUnitTermWithNoExp = $srcUnitTerm->removeExponent();
                $destUnitTermWithNoExp = $destUnitTerm->removeExponent();
                $converter = Converter::get($srcUnitTermWithNoExp->dimension);
                $conversion = $converter->getConversion($srcUnitTermWithNoExp, $destUnitTermWithNoExp);

                if ($conversion !== null) {
                    // Apply the exponent to the conversion factor.
                    $factor = $conversion->factor->value ** $srcUnitTerm->exponent;
                }
            }

            if ($conversion === null) {
                throw new LogicException("No conversion path exists between units '$srcUnitTerm' and '$destUnitTerm'.");
            }

            // Multiply the result by the conversion factor.
            $result *= $factor;
        }

        return $result;
    }

    // endregion

    // region Inspection methods

    public function isSi(): bool
    {
        return $this->unit->isSi();
    }

    // endregion

    // region Transformation methods

    /**
     * Convert this Quantity to a different unit.
     *
     * Returns a new Quantity object with the equivalent value in the destination unit.
     *
     * @param string|DerivedUnit $destUnit The destination unit to convert to.
     * @return static A new Quantity in the specified unit.
     * @throws ValueError If the destination unit is invalid.
     * @throws LogicException If no conversion path exists between the units.
     *
     * @example
     *   $length = new Length(1000, 'm');
     *   $km = $length->to('km');  // Length(1, 'km')
     */
    public function to(string|DerivedUnit $destUnit): static
    {
        // Convert the value to the target unit.
        $value = static::convert($this->value, $this->unit, $destUnit);

        // Return the new object. In this case we can bypass create() because we know the object will have the same
        // class as the calling class.
        return new static($value, $destUnit);
    }

    /**
     * Expand any named units and reduce multiple terms with the same dimension to a single term.
     *
     * This will, for example, reduce multiple length unit terms to one. e.g. if there are km, m, ft, etc. unit terms,
     * these will be resolved to a single unit term with the first base unit encountered.
     *
     * @return static
     */
//    public function reduce(): static
//    {
//        // Get the value and expanded unit.
//        $value = $this->value;
//        $unit = $this->unit->expand();
//
//        $dimensions = array_keys(Dimensions::DIMENSION_CODES);
//        $newUnitTerms = [];
//
//        // Check each dimension (single letter codes) and make sure we have at most one unit term per each.
//        foreach ($dimensions as $dimension) {
//            foreach ($unit->unitTerms as $unitTerm) {
//                if ($unitTerm->base->dimension !== $dimension) {
//                    continue;
//                }
//
//                // If this is the first unit term found for this dimension, add it to the array.
//                if (!isset($newUnitTerms[$dimension])) {
//                    $newUnitTerms[$dimension] = $unitTerm;
//                }
//                else {
//                    // Combine the unit term with the one in the array.
//                    // Get the conversion factor between the two base units.
//                    $factor = self::convert(1, $unitTerm->base, $newUnitTerms[$dimension]->base);
//                    // Multiply the quantity value by the conversion factor raised to the exponent.
//                    $value *= $factor ** $unitTerm->exponent;
//                    // Add the exponents.
//                    $newUnitTerms[$dimension]->exponent += $unitTerm->exponent;
//                }
//            }
//        }
//
//        // Construct the new DerivedUnit.
//        $du = new DerivedUnit();
//        foreach ($newUnitTerms as $newUnitTerm) {
//            $du->addUnitTerm($newUnitTerm);
//        }
//
//        // Construct the new Quantity.
//        return self::create($value, $du);
//    }

    // endregion

    // region Comparison methods

    /**
     * Compare two Quantities.
     *
     * This method will only return 0 for *exactly* equal.
     * It's usually preferable to use approxCompare() instead, which allows for user-defined tolerances.
     *
     * Automatically converts the other measurement to this one's unit before comparing.
     *
     * @param mixed $other The measurement to compare with.
     * @return int -1 if this < other, 0 if equal, 1 if this > other.
     * @throws TypeError If the other Quantity has a different type.
     * @throws LogicException If no conversion path exists between the units.
     */
    public function compare(mixed $other): int
    {
        $otherValue = $this->preCompare($other);
        return Numbers::sign($this->value <=> $otherValue);
    }

    /**
     * Compare this Quantity with another and determine if they are equal, within user-defined tolerances.
     *
     * @param mixed $other The value to compare with (can be any type).
     * @return bool True if the values are equal, false otherwise.
     * @throws LogicException If no conversion path exists between the units.
     */
    #[Override]
    public function approxEqual(
        mixed $other,
        float $relTol = Floats::DEFAULT_RELATIVE_TOLERANCE,
        float $absTol = Floats::DEFAULT_ABSOLUTE_TOLERANCE
    ): bool
    {
        // Get the other Quantity's value in the same unit.
        try {
            // This will throw a TypeError if the other Quantity has a different type.
            $otherValue = $this->preCompare($other);
        } catch (TypeError) {
            // If the other Quantity has a different type, it's not equal to this one.
            return false;
        }

        // Now we have the other Quantity in the same unit, compare the values.
        return Floats::approxEqual($this->value, $otherValue, $relTol, $absTol);
    }

    // endregion

    // region Arithmetic methods

    /**
     * Add another measurement to this one.
     *
     * Supports two call styles:
     * - add($otherQuantity)
     * - add($value, $unit)
     *
     * Automatically converts units before adding.
     *
     * @param self|float $otherOrValue Another Quantity or a numeric value.
     * @param ?string $otherUnit The unit if providing a numeric value.
     * @return static A new Quantity containing the sum in this measurement's unit.
     * @throws ValueError If value is non-finite or unit is invalid.
     * @throws LogicException If no conversion path exists between units.
     *
     * @example
     *   $a = new Length(100, 'm');
     *   $b = new Length(2, 'km');
     *   $sum = $a->add($b);           // Length(2100, 'm')
     *   $sum2 = $a->add(50, 'cm');    // Length(100.5, 'm')
     */
    public function add(self|float $otherOrValue, ?string $otherUnit = null): static
    {
        // Create the Quantity to add if necessary.
        if (is_float($otherOrValue) && is_string($otherUnit)) {
            // This will throw if the value is non-finite or the unit is invalid.
            $other = self::create($otherOrValue, $otherUnit);
        }
        else {
            $other = $otherOrValue;
        }

        // Get the other Quantity in the same unit as this one.
        $otherValue = $this->unit === $other->unit ? $other->value : $this->to($otherUnit)->value;

        // Add the two values.
        return self::create($this->value + $otherValue, $this->unit);
    }

    /**
     * Subtract another measurement from this one.
     *
     * Supports two call styles:
     * - sub($otherQuantity)
     * - sub($value, $unit)
     *
     * Automatically converts units before subtracting.
     *
     * @param self|float $otherOrValue Another Quantity or a numeric value.
     * @param ?string $otherUnit The unit if providing a numeric value.
     * @return static A new Quantity containing the difference in this measurement's unit.
     * @throws ValueError If value is non-finite or unit is invalid.
     * @throws LogicException If no conversion path exists between units.
     *
     * @example
     *   $a = new Length(100, 'm');
     *   $b = new Length(2, 'km');
     *   $diff = $a->sub($b);          // Length(-1900, 'm')
     */
    public function sub(self|float $otherOrValue, ?string $otherUnit = null): static
    {
        // Create the Quantity to add if necessary.
        if (is_float($otherOrValue) && is_string($otherUnit)) {
            // This will throw if the value is non-finite or the unit is invalid.
            $other = self::create($otherOrValue, $otherUnit);
        }
        else {
            $other = $otherOrValue;
        }

        // Get the other Quantity in the same unit as this one.
        $otherValue = $this->unit === $other->unit ? $other->value : $this->to($otherUnit)->value;

        // Subtract the values.
        return self::create($this->value - $otherValue, $this->unit);
    }

    /**
     * Negate a measurement.
     *
     * @return static A new Quantity containing the negative of this measurement's unit.
     */
    public function neg(): static
    {
        return self::create(-$this->value, $this->unit);
    }

    /**
     * Multiply this measurement by a scalar factor.
     *
     * @param float|Quantity $otherOrValue The scale factor or Quantity to multiply by.
     * @return static A new Quantity representing the result of the multiplication.
     * @throws ValueError If the multiplier is a non-finite float (±INF or NAN).
     *
     * @example
     *   $length = new Length(10, 'm');
     *   $doubled = $length->mul(2);  // Length(20, 'm')
     */
    public function mul(float|Quantity $otherOrValue, ?string $otherUnit = null): static
    {
        if (is_float($otherOrValue)) {
            // Check if multiplying by a scalar.
            if ($otherUnit === null) {
                // Guard.
                if (!is_finite($otherOrValue)) {
                    throw new ValueError('Multiplier cannot be ±INF or NAN.');
                }

                // Multiply the Quantity.
                return self::create($this->value * $otherOrValue, $this->unit);
            }

            // Create the Quantity to multiply.
            $other = self::create($otherOrValue, $otherUnit);
        }
        else {
            $other = $otherOrValue;
        }

        // Start by multiplying the values.
        $value = $this->value * $other->value;

        // Get the unit terms from each quantity.
        $thisUnitTerms = $this->unit->unitTerms;
        $otherUnitTerms = $other->unit->unitTerms;

        foreach ($otherUnitTerms as $otherUnitTerm) {
            // Get the term with no exponent.
            $otherUnitTermNoExp = $otherUnitTerm->removeExponent();

            // Get the dimension.
            $dim = $otherUnitTermNoExp->dimension;

            // See if there's already a unit term with this dimension.
            if (isset($thisUnitTerms[$dim])) {
                // Get the existing unit term with no exponent.
                $thisUnitTermNoExp = $thisUnitTerms[$dim]->removeExponent();

                // Multiply the conversion factor, raised to the power of the exponent of the other unit term..
                $value *= self::convert(1, $otherUnitTermNoExp, $thisUnitTermNoExp) ** $otherUnitTerm->exponent;

                // Add the exponent to the existing term.
                $thisUnitTerms[$dim]->exponent += $otherUnitTerm->exponent;
            }
            else {
                // Add a new unit term.
                $thisUnitTerms[$dim] = $otherUnitTerm;
            }
        }

        // Create a new Quantity.
        return self::create($value, new DerivedUnit($thisUnitTerms));
    }

    /**
     * Divide this measurement by a scalar factor.
     *
     * @param float $k The divisor.
     * @return static A new Quantity with the value divided.
     * @throws DivisionByZeroError If the divisor is zero.
     * @throws ValueError If the divisor is non-finite (±INF or NAN).
     *
     * @example
     *   $length = new Length(10, 'm');
     *   $half = $length->div(2);  // Length(5, 'm')
     */
    public function div(float $k): static
    {
        // Guards.
        if ($k === 0.0) {
            throw new DivisionByZeroError('Divisor cannot be 0.');
        }
        if (!is_finite($k)) {
            throw new ValueError('Divisor cannot be ±INF or NAN.');
        }

        // Divide the Quantity.
        return self::create(fdiv($this->value, $k), $this->unit);
    }

    /**
     * Get the absolute value of this measurement.
     *
     * @return static A new Quantity with non-negative value in the same unit.
     *
     * @example
     *   $temp = new Temperature(-10, 'C');
     *   $abs = $temp->abs();  // Temperature(10, 'C')
     */
    public function abs(): static
    {
        return self::create(abs($this->value), $this->unit);
    }

    // endregion

    // region Formatting methods

    /**
     * Format the measurement as a string with control over precision and notation.
     *
     *  Precision meaning varies by specifier:
     *  - 'f'/'F': Number of decimal places
     *  - 'e'/'E': Number of mantissa digits
     *  - 'g'/'G': Number of significant figures
     *
     * @param string $specifier Format type: 'f'/'F' (fixed), 'e'/'E' (scientific), 'g'/'G' (shortest).
     * @param ?int $precision Number of digits (meaning depends on specifier).
     * @param bool $trimZeros If true, remove trailing zeros and decimal point.
     * @param bool $includeSpace If true, insert space between value and unit.
     * @return string The formatted measurement string.
     * @throws ValueError If specifier or precision are invalid.
     *
     * @example
     *   $angle->format('f', 2)       // "90.00 deg"
     *   $angle->format('e', 3)       // "1.571e+0 rad"
     *   $angle->format('f', 0, true, false)  // "90deg"
     */
    public function format(
        string $specifier = 'f',
        ?int $precision = null,
        bool $trimZeros = true,
        bool $includeSpace = true
    ): string
    {
        // Validate the specifier.
        if (!in_array($specifier, ['e', 'E', 'f', 'F', 'g', 'G'], true)) {
            throw new ValueError("The specifier must be 'e', 'E', 'f', 'F', 'g', or 'G'.");
        }

        // Validate the precision.
        if ($precision !== null && ($precision < 0 || $precision > 17)) {
            throw new ValueError('The precision must be null or an integer between 0 and 17.');
        }

        // Canonicalize -0.0 to 0.0.
        $value = Floats::normalizeZero($this->value);

        // Format with the desired precision and specifier.
        // If the precision is null, omit it from the format string to use the sprintf default (usually 6).
        $formatString = $precision === null ? "%{$specifier}" : "%.{$precision}{$specifier}";
        $str = sprintf($formatString, $value);

        // If $trimZeros is true and there's a decimal point in the string, remove trailing zeros and decimal point from
        // the number. If there's an 'E' or 'e' in the string, this only applies to the mantissa.
        if ($trimZeros && str_contains($str, '.')) {
            $ePos = stripos($str, 'E');
            $mantissa = $ePos === false ? $str : substr($str, 0, $ePos);
            $exp = $ePos === false ? '' : substr($str, $ePos);
            $str = rtrim($mantissa, '0.') . $exp;
        }

        // Return the formatted string.
        return $str . ($includeSpace ? ' ' : '') . $this->unit;
    }

    // endregion

    // region Conversion methods

    /**
     * Convert the measurement to a string using basic formatting.
     *
     * For custom formatting, use format().
     *
     * @return string The measurement as a string (e.g., "1.5707963267949 rad").
     */
    #[Override]
    public function __toString(): string
    {
        return $this->value . ' ' . $this->unit;
    }

    // endregion

    // region Part-related methods

    /**
     * Get an array of units for use in part-related methods.
     *
     * @return array<int|string, string>
     */
    public static function getPartUnits(): array
    {
        return [];
    }

    /**
     * Create a new Quantity object (of the derived type) as a sum of measurements of the same type in different
     * units.
     *
     * All parts must be non-negative.
     * If the resulting value should be negative, include a 'sign' part with a value of -1.
     *
     * @param array<string, int|float> $parts The parts and optional sign.
     * @return static A new Quantity equal to the sum of the parts, with the unit equal to the smallest unit.
     * @throws TypeError If any of the values are not numbers.
     * @throws ValueError If any of the values are non-finite or negative.
     * @throws LogicException If getPartUnits() has not been overridden properly.
     */
    public static function fromPartsArray(array $parts): static
    {
        // Validate and get part units.
        $symbols = static::validateAndTransformPartUnits();
        $partUnits = array_keys($symbols);

        // Validate the parts array.
        $validKeys = ['sign', ...$partUnits];
        foreach ($parts as $key => $value) {
            if (!in_array($key, $validKeys, true)) {
                throw new ValueError('Invalid part name: ' . $key);
            }
            if (!Numbers::isNumber($value)) {
                throw new TypeError('All values must be numbers.');
            }
            if ($key === 'sign') {
                if ($value !== -1 && $value !== 1) {
                    throw new ValueError('Sign must be -1 or 1.');
                }
            } elseif (!is_finite($value) || $value < 0.0) {
                throw new ValueError('All part values must be finite and non-negative.');
            }
        }

        // Initialize the Quantity to 0, with the unit set to the smallest unit.
        $smallestUnitIndex = array_key_last($partUnits);
        $smallestUnit = $partUnits[$smallestUnitIndex]; // @phpstan-ignore offsetAccess.notFound
        $t = new (static::class)(0, $smallestUnit);

        // Check each of the possible units.
        foreach ($partUnits as $unit) {
            // Ignore omitted units.
            if (!isset($parts[$unit])) {
                continue;
            }

            // Add the part. It will be converted to the smallest unit automatically.
            $t = $t->add($parts[$unit], $unit);
        }

        // Make negative if necessary.
        if (isset($parts['sign']) && $parts['sign'] === -1) {
            $t = $t->neg();
        }

        return $t;
    }

    /**
     * Convert Quantity to component parts.
     *
     * Returns an array with components from the largest to the smallest unit.
     * Only the last component may have a fractional part; others are integers.
     *
     * @param string $smallestUnit The smallest unit to include.
     * @param ?int $precision The number of decimal places for rounding the smallest unit, or null for no rounding.
     * @return array<string, int|float> Array of parts, plus the sign, which is always 1 or -1.
     * @throws ValueError If any arguments are invalid.
     * @throws LogicException If getPartUnits() has not been overridden properly.
     */
    public function toPartsArray(string $smallestUnit, ?int $precision = null): array
    {
        // Validate arguments.
        static::validateSmallestUnit($smallestUnit);
        static::validatePrecision($precision);

        // Validate and get part units.
        $symbols = static::validateAndTransformPartUnits();
        $partUnits = array_keys($symbols);

        // Prep.
//        $converter = static::getUnitConverter();
        $sign = Numbers::sign($this->value, false);
        $parts = ['sign' => $sign];
        $smallestUnitIndex = (int)array_search($smallestUnit, $partUnits, true);

        // Initialize the remainder to the source value converted to the smallest unit.
        $rem = abs($this->to($smallestUnit)->value);

        // Get the integer parts.
        for ($i = 0; $i < $smallestUnitIndex; $i++) {
            // Get the number of current units in the smallest unit.
            $curUnit = $partUnits[$i];
            $factor = 1.2345; //$converter->convert(1, $curUnit, $smallestUnit);
            $wholeNumCurUnit = floor($rem / $factor);
            $parts[$curUnit] = (int)$wholeNumCurUnit;
            $rem = $rem - $wholeNumCurUnit * $factor;
        }

        // Round the smallest unit.
        if ($precision === null) {
            // No rounding.
            $parts[$smallestUnit] = $rem;
        } elseif ($precision === 0) {
            // Return an integer.
            $parts[$smallestUnit] = (int)round($rem, $precision);
        } else {
            // Round off.
            $parts[$smallestUnit] = round($rem, $precision);
        }

        // Carry in reverse order.
        if ($precision !== null) {
            for ($i = $smallestUnitIndex; $i >= 1; $i--) {
                $curUnit = $partUnits[$i];
                $prevUnit = $partUnits[$i - 1];
                if ($parts[$curUnit] >= 1.2345 /*$converter->convert(1, $prevUnit, $curUnit)*/) {
                    $parts[$curUnit] = 0;
                    $parts[$prevUnit]++;
                }
            }
        }

        return $parts;
    }

    /**
     * Format measurement as component parts.
     *
     * Examples:
     *   - 4y 5mo 6d 12h 34min 56.789s
     *   - 12° 34′ 56.789″
     *
     * Only the smallest unit may have a decimal point. Larger units will be integers.
     *
     * @param string $smallestUnit The smallest unit to include.
     * @param ?int $precision The number of decimal places for rounding the smallest unit, or null for no rounding.
     * @param bool $showZeros If true, show all components (largest to smallest) including zeros; if false, skip
     * zero-value components.
     * @return string Formatted string.
     * @throws ValueError If any arguments are invalid.
     */
    public function formatParts(string $smallestUnit, ?int $precision = null, bool $showZeros = false): string
    {
        // Validate arguments.
        self::validateSmallestUnit($smallestUnit);
        self::validatePrecision($precision);

        // Validate and get part units.
        $symbols = static::validateAndTransformPartUnits();
        $partUnits = array_keys($symbols);

        // Prep.
        $parts = $this->toPartsArray($smallestUnit, $precision);
        $smallestUnitIndex = (int)array_search($smallestUnit, $partUnits, true);
        $result = [];
        $hasNonZero = false;

        // Generate string as parts.
        for ($i = 0; $i <= $smallestUnitIndex; $i++) {
            $unit = $partUnits[$i];
            $value = $parts[$unit] ?? 0;
            $isZero = Numbers::equal($value, 0);

            // Track if we've seen any non-zero values.
            if (!$isZero) {
                $hasNonZero = true;
            }

            // Skip zero components based on $showZeros flag.
            // When $showZeros is true: show zeros only after the first non-zero (standard DMS notation).
            // When $showZeros is false: skip all zeros (compact time notation).
            if ($isZero && !($showZeros && $hasNonZero)) {
                continue;
            }

            // Format the value with precision for the smallest unit.
            $formattedValue = $i === $smallestUnitIndex && $precision !== null
                ? number_format($value, $precision, '.', '')
                : (string)$value;

            $result[] = $formattedValue . $symbols[$unit];
        }

        // If the value is zero, just show '0' with the smallest unit.
        if (empty($result)) {
            $formattedValue = $precision === null ? '0' : number_format(0, $precision, '.', '');
            $result[] = $formattedValue . $symbols[$smallestUnit];
        }

        // Return string of units, separated by spaces. Prepend minus sign if negative.
        return ($parts['sign'] === -1 ? '-' : '') . implode(' ', $result);
    }

    // endregion

    // region Helper methods

    /**
     * Check the $this and $other objects have the same type, and get the value of the $other Quantity in the same
     * unit as the $this one. Return the value.
     *
     * @param mixed $other The other measurement to compare with.
     * @return float The value of the other measurement in the same unit as this one.
     * @throws LogicException If no conversion path exists between the units.
     * @throws TypeError If the other Quantity has a different type.
     * @throws ValueError If the target unit is invalid.
     */
    protected function preCompare(mixed $other): float
    {
        // Check the two measurements have the same types.
        if (!Types::same($this, $other)) {
            throw new TypeError('The two measurements being compared must be of the same type.');
        }

        // Get the other Quantity in the same unit as this one.
        /** @var Quantity $other */
        return $this->unit === $other->unit ? $other->value : $other->to($this->unit)->value;
    }

    /**
     * Check smallest unit argument is valid.
     *
     * @param string $smallestUnit
     * @return void
     * @throws ValueError
     */
    protected static function validateSmallestUnit(string $smallestUnit): void
    {
        // Validate and get part units.
        $symbols = static::validateAndTransformPartUnits();
        $partUnits = array_keys($symbols);

        // Check the smallest unit is valid.
        if (!in_array($smallestUnit, $partUnits, true)) {
            throw new ValueError('Invalid smallest unit specified. Must be one of: ' .
                                 implode(', ', Arrays::quoteValues($partUnits)));
        }
    }

    /**
     * Check precision argument is valid.
     *
     * @param ?int $precision The precision to validate.
     * @return void
     * @throws ValueError If precision is negative.
     */
    protected static function validatePrecision(?int $precision): void
    {
        if ($precision !== null && $precision < 0) {
            throw new ValueError('Invalid precision specified. Must be null or a non-negative integer.');
        }
    }

    /**
     * Validate and transform the part units array.
     *
     * @return array<string, string>
     * @throws LogicException If getPartUnits() returns an empty array, or if any of the units or symbols are invalid.
     */
    protected static function validateAndTransformPartUnits(): array
    {
        // Get the part units array. This should be overridden in the derived class and return a non-empty array.
        $partUnits = static::getPartUnits();

        // Ensure we have some part units.
        if (empty($partUnits)) {
            throw new LogicException(
                'The derived Quantity class must define the part units by overriding getPartUnits(), so ' .
                'it returns an array of valid units (with optional alternative symbols).'
            );
        }

        // Create a new array to contain the map of units to symbols.
        $symbols = [];

        // Ensure all part units are valid units.
        $validUnits = array_keys(static::getUnits());
        foreach ($partUnits as $partUnit => $symbol) {
            // If the key is an integer, the unit and the symbol are the same.
            if (is_int($partUnit)) {
                $partUnit = $symbol;
            }

            // Ensure the unit is valid.
            if (!in_array($partUnit, $validUnits, true)) {
                throw new LogicException("Invalid part unit: '$partUnit'.");
            }

            // Ensure the symbol is a non-empty string.
            if (!is_string($symbol) || $symbol === '') {
                throw new LogicException('Unit symbols must be non-empty strings.');
            }

            // Add it to the result.
            $symbols[$partUnit] = $symbol;
        }

        return $symbols;
    }

    // endregion
}
