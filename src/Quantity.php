<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use DivisionByZeroError;
use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Core\Exceptions\IncomparableTypesException;
use Galaxon\Core\Floats;
use Galaxon\Core\Numbers;
use Galaxon\Core\Traits\Comparison\ApproxComparable;
use Galaxon\Quantities\Exceptions\DimensionMismatchException;
use Galaxon\Quantities\Exceptions\UnknownUnitException;
use Galaxon\Quantities\Internal\CompoundUnit;
use Galaxon\Quantities\Internal\QuantityType;
use Galaxon\Quantities\Internal\Unit;
use Galaxon\Quantities\Internal\UnitInterface;
use Galaxon\Quantities\Internal\UnitSystem;
use Galaxon\Quantities\Internal\UnitTerm;
use Galaxon\Quantities\Services\ConversionService;
use Galaxon\Quantities\Services\DimensionService;
use Galaxon\Quantities\Services\PrefixService;
use Galaxon\Quantities\Services\QuantityTypeService;
use Galaxon\Quantities\Services\UnitService;
use InvalidArgumentException;
use LogicException;
use Override;
use RoundingMode;
use Stringable;
use UnexpectedValueException;

/**
 * Base class for physical measurements with units.
 *
 * Provides a framework for creating strongly typed measurement classes (Length, Mass, Time, etc.)
 * with automatic unit conversion, arithmetic operations, and comparison capabilities.
 *
 * Derived classes may optionally override:
 * - getUnitDefinitions(): Define the base and expandable units, and the prefixes they accept.
 * - getConversionDefinitions(): Define conversions between units.
 *
 * Prefix system:
 * - Units can specify allowed prefixes using bitwise flags (GROUP_METRIC, GROUP_BINARY, etc.)
 * - Provides fine-grained control (e.g. radian can accept only small metric prefixes)
 * - Supports combinations (e.g. byte can accept both metric and binary prefixes)
 *
 * Features:
 * - Automatic validation of units and values
 * - Lazy initialization of Converter for each measurement type
 * - Type-safe arithmetic operations (add, subtract, multiply, divide)
 * - Comparison and equality testing with epsilon tolerance
 * - Flexible string formatting and parsing
 */
class Quantity implements Stringable
{
    use ApproxComparable;

    // region Public properties

    /**
     * The numeric value of the quantity in the specified unit.
     */
    public readonly float $value;

    /**
     * The unit of the quantity.
     */
    public readonly CompoundUnit $compoundUnit;

    // endregion

    // region Property hooks

    /**
     * The dimension.
     */
    public string $dimension {
        get => $this->compoundUnit->dimension;
    }

    /**
     * The quantity type this quantity is for, if known.
     */
    public ?QuantityType $quantityType {
        get => static::getQuantityType();
    }

    // endregion

    // region Private static properties

    /**
     * Flag to permit call to new Quantity().
     */
    private static bool $allowConstruct = false;

    // endregion

    // region Constructor

    /**
     * Constructor.
     *
     * Creates a new measurement with the specified value and unit. If the unit is omitted, the quantity is
     * dimensionless.
     *
     * Direct instantiation of the base `Quantity` class is forbidden — use `Quantity::create()` or
     * `Quantity::parse()` instead. Subclasses (e.g. `Length`, `Mass`) should be instantiated directly with their
     * respective constructors.
     *
     * The constructor validates that:
     * - The calling class is a subclass of Quantity, or the call is via `Quantity::create()`.
     * - The value is finite.
     * - The unit is valid.
     * - The calling class matches the unit's dimension.
     *
     * @param float $value The numeric value in the given unit.
     * @param null|string|UnitInterface $unit The unit as a symbol string (e.g. 'kg', 'mm', 'hr'),
     * object, or null if dimensionless.
     * @throws LogicException If called directly on Quantity, or the wrong subclass constructor is being called
     * for the given unit's dimension.
     * @throws DomainException If the value is non-finite (±INF or NAN).
     * @throws FormatException If the unit is provided as a string, and it cannot be parsed.
     * @throws UnknownUnitException If the unit is provided as a string, and it contains unknown units.
     */
    final public function __construct(float $value, null|string|UnitInterface $unit = null)
    {
        // Check they aren't calling `new Quantity()`. We only want them to use `new Angle()` (for example),
        // `Quantity::create()` or `Quantity::parse()`.
        $qtyClass = self::class;
        $callingClass = static::class;
        if ($qtyClass === $callingClass && !self::$allowConstruct) {
            throw new LogicException(
                'The Quantity constructor should not be called directly. Use `Quantity::create()`, ' .
                '`Quantity::parse()`, or a subclass constructor (e.g. `new Angle(...)`.'
            );
        }

        // Check the value is finite.
        if (!is_finite($value)) {
            throw new DomainException('Cannot create a quantity with a non-finite value.');
        }

        // Convert the provided unit argument into an object if it isn't already.
        // This will throw if the unit is provided as a string that doesn't represent a valid CompoundUnit.
        $compoundUnit = CompoundUnit::toCompoundUnit($unit);

        // Check they are calling the correct constructor.
        $quantityType = $compoundUnit->quantityType;
        $correctClass = $quantityType->class ?? $qtyClass;
        if ($callingClass !== $correctClass) {
            $classStr = $quantityType === null
                ? "quantity with dimension '$compoundUnit->dimension'"
                : "$quantityType->name quantity";
            throw new LogicException(
                "A $classStr cannot be instantiated by calling `new $callingClass()`. Instead, " .
                ' call `Quantity::create()`, which will automatically create an object of the correct class.'
            );
        }

        // Set the properties.
        $this->value = Floats::normalizeZero($value);
        $this->compoundUnit = $compoundUnit;
    }

    // endregion

    // region Factory method

    /**
     * Create a Quantity of the appropriate type for the given unit.
     *
     * This works whether called from Quantity or a subclass.
     * Uses the dimension class registry to instantiate the correct subclass.
     * For example, a unit with dimension 'L2' will create an Area object (assuming Area is registered).
     * For unregistered dimensions, falls back to a generic Quantity object.
     *
     * @param float $value The numeric value.
     * @param null|string|UnitInterface $unit The unit as a symbol string, a unit object, or null for dimensionless.
     * @return self A Quantity of the appropriate type.
     * @throws DomainException If the value is non-finite (±INF or NAN).
     * @throws FormatException If the unit is provided as a string, and it cannot be parsed.
     * @throws UnknownUnitException If the unit is provided as a string, and it contains unknown units.
     */
    public static function create(float $value, null|string|UnitInterface $unit = null): self
    {
        // Get unit as CompoundUnit.
        $unit = CompoundUnit::toCompoundUnit($unit);

        // If there's a registered subclass for this dimension code, create an object of that class.
        $quantityType = $unit->quantityType;
        if ($quantityType !== null) {
            return new ($quantityType->class)($value, $unit);
        }

        // Fall back to a generic Quantity object.
        return self::new($value, $unit);
    }

    /**
     * Parse a string representation into a Quantity object.
     *
     * Accepts formats like "123.45 km", "90deg", "1.5e3 ms". Whitespace between value and unit is allowed.
     *
     * The string may have multiple parts, which must be separated by whitespace.
     * Examples:
     *    - "4y 5mo 6d 12h 34min 56.789s"
     *    - "12° 34′ 56.789″"
     *    - "12 mi 34 yd 567 ft 8.9 in"
     *
     * @param string $input The string to parse.
     * @return self A new Quantity parsed from the string.
     * @throws FormatException If the input string format is invalid.
     * @throws DomainException If the string contains unknown units, or the string contains multiple parts and either
     * the quantity type is unregistered or the result unit symbol is invalid.
     * @throws DimensionMismatchException If called on a subclass and the parsed unit's dimension doesn't match.
     * @throws UnexpectedValueException If there's an unexpected error during parsing.
     * @throws InvalidArgumentException If the string contains multiple parts, and any of the part unit symbols are not
     * @throws LogicException If the string contains multiple parts, and the first part is negative.
     * strings.
     * @example
     *   Length::parse("123.45 km")  // Length(123.45, 'km')
     *   Angle::parse("90deg")       // Angle(90.0, 'deg')
     *   Time::parse("1.5e3 ms")     // Time(1500.0, 'ms')
     */
    public static function parse(string $input): static
    {
        // Prepare an error message with the original value.
        $quantityType = static::getQuantityType();
        $name = $quantityType === null ? '' : (' ' . $quantityType->name);
        $err = "The provided string '$input' does not represent a valid$name quantity.";

        // Reject empty input.
        $input = trim($input);
        if ($input === '') {
            throw new FormatException($err);
        }

        // Try to parse as <num><unit>. Whitespace between the number and unit is permitted. The unit is optional, as
        // for a dimensionless quantity.
        if (self::isValidQuantity($input, $m)) {
            assert(isset($m[1]));
            $result = self::create((float)$m[1], $m[2] ?? null);
        } else {
            // Try to parse the string as multiple parts, as if output from formatParts().
            $result = static::parseParts($input);
        }

        // If this method is not called from Quantity, check the result class is the same as the calling class.
        // For example, it's ok to call Quantity::parse() and get an Angle, and it's ok to call Angle::parse() and get
        // an Angle, but it's not ok to call Length::parse() and get an Angle.
        if (self::class !== static::class && $result::class !== static::class) {
            throw new DimensionMismatchException(static::getDimension(), $result->dimension);
        }

        return $result;
    }

    // endregion

    // region Transformation methods

    /**
     * Convert a value from a source unit to a destination unit.
     *
     * @param float $value The numeric value to convert.
     * @param string|UnitInterface $srcUnit The source unit.
     * @param string|UnitInterface $destUnit The destination unit.
     * @return float The converted value.
     * @throws FormatException If a unit string cannot be parsed.
     * @throws DomainException If a unit string contains unknown units.
     * @throws LogicException If no conversion path exists between the units.
     */
    public static function convert(float $value, string|UnitInterface $srcUnit, string|UnitInterface $destUnit): float
    {
        // Delegate to the ConversionService.
        return ConversionService::convert($value, $srcUnit, $destUnit);
    }

    /**
     * Convert this Quantity to a different unit.
     *
     * Returns a new Quantity object with the equivalent value in the destination unit.
     *
     * @param string|UnitInterface $destUnit The destination unit to convert to.
     * @return static A new Quantity in the specified unit.
     * @throws DomainException If the destination unit is invalid.
     * @throws LogicException If no conversion path exists between the units.
     * @example
     *   $length = new Length(1000, 'm');
     *   $km = $length->to('km');  // Length(1, 'km')
     */
    public function to(string|UnitInterface $destUnit): static
    {
        // Convert the value to the target unit.
        $value = static::convert($this->value, $this->compoundUnit, $destUnit);

        // Create new object.
        $result = self::create($value, $destUnit);
        assert($result instanceof static);
        return $result;
    }

    /**
     * Convert this Quantity to SI base units without simplification or auto-prefixing.
     *
     * Unlike toSi(), this method returns purely SI base units (e.g., kg·m·s⁻² instead of N).
     * Useful for calculations or when you need the fundamental SI form.
     *
     * @return static The Quantity expressed in SI base units.
     */
    public function toSiBase(): static
    {
        return $this->to($this->compoundUnit->toSiBase());
    }

    /**
     * Convert this quantity to SI units.
     *
     * Base units will be replaced by expandable units where possible, e.g. kg*m/s2 => N
     * The expandable unit that replaces the largest number of base units will be chosen.
     *
     * To auto-prefix the result, chain with autoPrefix():
     *   $q->toSi()->autoPrefix()
     *
     * @return static The Quantity expressed in SI units.
     */
    public function toSi(): static
    {
        return $this->toSiBase()->toDerived();
    }

    /**
     * Convert this Quantity to English base units.
     *
     * This method returns purely base units (e.g., lb·ft·s⁻² instead of lbf).
     *
     * @return static A new Quantity expressed in English base units.
     */
    public function toEnglishBase(): static
    {
        return $this->to($this->compoundUnit->toEnglishBase());
    }

    /**
     * Convert this quantity to English units.
     *
     * Base units will be replaced by expandable units where possible, e.g. lb*ft/s2 => lbf
     * The expandable unit that replaces the largest number of base units will be chosen.
     *
     * @return static The Quantity expressed in English units.
     */
    public function toEnglish(): static
    {
        return $this->toEnglishBase()->toDerived();
    }

    /**
     * Convert this Quantity to base units.
     *
     * This method will convert the quantity to SI or English units, depending on what's a better fit with the
     * existing compound unit. For example, units like lbf, mi, ac, US gal, etc. will be converted to lb, ft, and s.
     * But units like km, mg, N, Hz, etc. will be converted to kg, m, and s.
     *
     * @return static A new Quantity expressed in SI or English base units.
     */
    public function toBase(): static
    {
        return $this->compoundUnit->siPreferred() ? $this->toSiBase() : $this->toEnglishBase();
    }

    /**
     * Substitute base units for derived units, e.g. kg*m/s2 => N
     *
     * The derived unit that replaces the largest number of base units will be chosen.
     *
     * 's-1' will not be replaced by 'Hz' unless it's the only unit term.
     * Furthermore, 's-1' is not replaced by 'Bq'. You can call $q->to('Bq') to get that effect.
     *
     * To auto-prefix the result, chain with autoPrefix():
     *   $q->toDerived()->autoPrefix()
     *
     * @return static A new Quantity with derived units substituted for base units.
     * @throws LogicException If no conversion path exists between two units of the same dimension.
     */
    public function toDerived(): static
    {
        // Merge compatible units.
        $qty = $this->merge();

        // Handle Hertz separately. We only want to swap 's-1' for 'Hz' if it's the only unit term.
        if (count($qty->compoundUnit->unitTerms) === 1) {
            $unitTerm = $qty->compoundUnit->firstUnitTerm;

            // Check if we have s-1.
            if ($unitTerm !== null && $unitTerm->unit->asciiSymbol === 's' && $unitTerm->exponent === -1) {
                // Create the Hz unit term.
                $newUnitTerm = new UnitTerm('Hz', PrefixService::invert($unitTerm->prefix));
                $result = static::create($qty->value, $newUnitTerm);
                assert($result instanceof static);
                return $result;
            }
        }

        // Check if we should substitute SI or English units.
        $si = $qty->compoundUnit->siPreferred();

        // Initialize tracking variables.
        $maxUnitsReplaced = 0;
        $bestDerivedUnit = null;

        // Loop through all the units and try to find a derived unit that matches the quantity.
        foreach (UnitService::getAll() as $unit) {
            // Filter by unit system.
            if ($si) {
                // Ensure unit is SI.
                if (!$unit->belongsToSystem(UnitSystem::Si)) {
                    continue;
                }
            } else {
                // Ensure unit belongs to both the Imperial and US Customary systems of units.
                // We don't want to replace 'in3' with 'US gal' or 'imp gal', for example.
                if (!$unit->belongsToSystem(UnitSystem::UsCustomary) || !$unit->belongsToSystem(UnitSystem::Imperial)) {
                    continue;
                }
            }

            // Skip base (non-expandable) units.
            if ($unit->isBase()) {
                continue;
            }

            // Expand the unit to base units.
            $expansionUnit = DimensionService::getBaseCompoundUnit($unit->dimension, $si);

            // Skip units that expand to s-1 (i.e. 'Hz' or 'Bq'), since we already handled these.
            if (count($expansionUnit->unitTerms) === 1 && $expansionUnit->firstUnitTerm?->asciiSymbol === 's-1') {
                continue;
            }

            // Check if it's a candidate for substitution.
            if (!DimensionService::lessThanOrEqual($expansionUnit->dimension, $qty->dimension)) {
                continue;
            }

            // Count the number of units that will be replaced.
            $nUnitsReplaced = DimensionService::countUnits($expansionUnit->dimension);

            // If it's an improvement, remember it.
            if ($nUnitsReplaced > $maxUnitsReplaced) {
                $maxUnitsReplaced = $nUnitsReplaced;
                $bestDerivedUnit = $unit;
            }
        }

        // If we found a match, substitute the necessary unit terms for the derived unit.
        if ($bestDerivedUnit !== null) {
            // Get the remaining base units not replaced by the derived unit.
            $rem = DimensionService::sub($qty->dimension, $bestDerivedUnit->dimension);
            $remUnit = DimensionService::getBaseCompoundUnit($rem, $si);
            $newUnit = new CompoundUnit($bestDerivedUnit)->mul($remUnit);
            return $qty->to($newUnit);
        }

        // No replacements found. Return $qty as-is; it's already a fresh object from merge().
        return $qty;
    }

    /**
     * Merge units that have the same dimension, e.g. 'm' and 'ft', or 's' and 'h', or 'lb' and 'kg'.
     *
     * The first unit encountered of a given dimension will be the one any others are converted to.
     *
     * @return static A new Quantity with compatible units merged.
     * @throws LogicException If no conversion path exists between two units of the same dimension.
     */
    public function merge(): static
    {
        if (!$this->compoundUnit->isMergeable()) {
            return clone $this;
        }

        // Merge the compound unit.
        $mergeQty = $this->compoundUnit->merge();

        // Multiply the merged Quantity by this Quantity's value.
        $result = $mergeQty->mul($this->value);
        assert($result instanceof static);
        return $result;
    }

    /**
     * Find the best SI prefix and construct a new Quantity equal to this one, but with the prefix applied.
     *
     * @return static A new Quantity with the best SI prefix applied.
     */
    public function autoPrefix(): static
    {
        // See what prefixes are available for the first unit term.
        $firstUnitTerm = $this->compoundUnit->firstUnitTerm;
        if ($firstUnitTerm === null || $firstUnitTerm->unit->prefixGroup === 0) {
            // There is no first unit (dimensionless), or no prefixes are available for the first unit, so we can't add
            // one.
            return $this;
        }

        // Initialize the new value and compound unit by removing all current prefixes.
        $newValue = $this->value * $this->compoundUnit->multiplier;
        $newCompoundUnit = $this->compoundUnit->removePrefixes();

        // Get the new first unit term.
        $firstUnitTerm = $newCompoundUnit->firstUnitTerm;
        assert($firstUnitTerm instanceof UnitTerm);

        // Choose the prefix that produces the smallest value greater than or equal to 1.
        // Start with the current situation, which is no prefix.
        $absValue = abs($newValue);
        $sign = Numbers::sign($newValue);
        $bestPrefix = null;
        $bestValue = $absValue;

        // Try each allowed prefix to see if it's better. We want the prefix that produces the smallest value greater
        // than or equal to 1.
        foreach ($firstUnitTerm->unit->allowedPrefixes as $prefix) {
            // We only want to consider engineering prefixes for this. The middle metric prefixes (c, d, da, h) are
            // rarely used for most units. We also don't want binary prefixes (e.g. 'kB' is usually preferred to 'KiB').
            if (!$prefix->isEngineering()) {
                continue;
            }

            // Compute the value we'd have if we use this prefix.
            $prefixedValue = $absValue / ($prefix->multiplier ** $firstUnitTerm->exponent);

            // Check if it's an improvement.
            if (
                ($bestValue < 1.0 && $prefixedValue > $bestValue) ||
                ($prefixedValue >= 1.0 && $prefixedValue < $bestValue)
            ) {
                $bestPrefix = $prefix;
                $bestValue = $prefixedValue;
            }
        }

        // If we found a better prefix than none at all, rebuild the compound unit with the first term replaced.
        if ($bestPrefix !== null) {
            $prefixedUnitTerm = new UnitTerm($firstUnitTerm->unit, $bestPrefix, $firstUnitTerm->exponent);
            $newUnitTerms = array_values($newCompoundUnit->unitTerms);
            $newUnitTerms[0] = $prefixedUnitTerm;
            $newCompoundUnit = new CompoundUnit($newUnitTerms);
        }

        // Create the result object.
        $result = static::create($bestValue * $sign, $newCompoundUnit);
        assert($result instanceof static);
        return $result;
    }

    /**
     * Create a new Quantity with the same unit but a different value.
     *
     * @param float $value The new numeric value.
     * @return static A new Quantity with the given value in the same unit.
     * @throws DomainException If the value is non-finite (±INF or NAN).
     */
    public function withValue(float $value): static
    {
        // If called from Quantity (unregistered quantity type), use the new() helper to bypass the
        // direct-instantiation guard.
        if (self::class === static::class) {
            $result = self::new($value, $this->compoundUnit);
            assert($result instanceof static);
            return $result;
        }

        // If called from a subclass, construct directly. This skips the dimension→class lookup in
        // create() since the dimension is unchanged, and we know the class is correct.
        return new static($value, $this->compoundUnit);
    }

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
     * @throws IncomparableTypesException If the other Quantity has a different type.
     * @throws DimensionMismatchException If the Quantities have different dimensions.
     * @throws LogicException If no conversion path exists between the units.
     */
    #[Override]
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
     */
    #[Override]
    public function approxEqual(
        mixed $other,
        float $relTol = Floats::DEFAULT_RELATIVE_TOLERANCE,
        float $absTol = Floats::DEFAULT_ABSOLUTE_TOLERANCE
    ): bool {
        try {
            // Get the other Quantity's value in the same unit.
            // This will throw if the other Quantity has a different type or dimension.
            $otherValue = $this->preCompare($other);
        } catch (DimensionMismatchException | IncomparableTypesException) {
            // If the other Quantity has a different type or dimension to this one.
            return false;
        }

        // Now we have the other Quantity in the same unit, compare the values.
        return Floats::approxEqual($this->value, $otherValue, $relTol, $absTol);
    }

    // endregion

    // region Unary arithmetic methods

    /**
     * Get the absolute value of this Quantity.
     *
     * @return static A new Quantity with a non-negative value and the same unit.
     * @example
     *   $temp = new Temperature(-10, 'C');
     *   $abs = $temp->abs();  // Temperature(10, 'C')
     */
    public function abs(): static
    {
        return $this->withValue(abs($this->value));
    }

    /**
     * Negate a Quantity.
     *
     * @return static A new Quantity containing the negative of this Quantity's unit.
     * @example
     *   $length = new Length(10, 'm');
     *   $negated = $length->neg();  // Length(-10, 'm')
     */
    public function neg(): static
    {
        return $this->withValue(-$this->value);
    }

    /**
     * Invert this Quantity (1/x) or divide a scalar by this Quantity (n/x).
     *
     * @return self A new Quantity with the value and unit inverted.
     * @throws DivisionByZeroError If the value is zero.
     * @example
     *   $length = new Length(10, 'm');
     *   $inv = $length->inv();  // Length(0.1, 'm^-1')
     */
    public function inv(): self
    {
        // Guards.
        if ($this->value === 0.0) {
            throw new DivisionByZeroError('Cannot invert zero.');
        }

        // Invert the value and unit.
        return self::create(1.0 / $this->value, $this->compoundUnit->inv());
    }

    // endregion

    // region Binary arithmetic methods

    /**
     * Add another Quantity to this one. Units must be compatible, i.e. have the same dimension.
     *
     * Automatically converts units before adding.
     *
     * @param self $other The Quantity to add.
     * @return self A new Quantity containing the sum in this measurement's unit.
     * @throws DimensionMismatchException If the units have different dimensions.
     * @throws LogicException If no conversion path exists between the units.
     * @example
     *   $a = new Length(100, 'm');
     *   $b = new Length(2, 'km');
     *   $sum = $a->add($b);  // Length(2100, 'm')
     */
    public function add(self $other): self
    {
        // Get the other Quantity in the same unit as this one.
        $otherValue = $this->compoundUnit->equal($other->compoundUnit)
            ? $other->value
            : $other->to($this->compoundUnit)->value;

        // Add the two values.
        return $this->withValue($this->value + $otherValue);
    }

    /**
     * Subtract another Quantity from this one. Units must be compatible, i.e. have the same dimension.
     *
     * Automatically converts units before subtracting.
     *
     * @param self $other The Quantity to subtract.
     * @return self A new Quantity containing the difference in this measurement's unit.
     * @throws DimensionMismatchException If the units have different dimensions.
     * @throws LogicException If no conversion path exists between the units.
     * @example
     *   $a = new Length(100, 'm');
     *   $b = new Length(2, 'km');
     *   $diff = $a->sub($b);  // Length(-1900, 'm')
     */
    public function sub(self $other): self
    {
        // Get the other Quantity in the same unit as this one.
        $otherValue = $this->compoundUnit->equal($other->compoundUnit)
            ? $other->value
            : $other->to($this->compoundUnit)->value;

        // Subtract the values.
        return $this->withValue($this->value - $otherValue);
    }

    /**
     * Multiply this Quantity by a scalar factor, another Quantity, or a unit.
     *
     * Supports multiple call styles:
     * - mul($quantity)
     * - mul($value)
     * - mul($unit)
     *
     * @param self|float|string|UnitInterface $other The Quantity, number, or unit to multiply.
     * @return self A new Quantity representing the result of the multiplication.
     * @throws DomainException If the result value is non-finite.
     * @throws FormatException If a unit string cannot be parsed.
     * @throws UnknownUnitException If a unit string contains unknown units.
     * @example
     *   $length = new Length(10, 'm');
     *   $doubled = $length->mul(2);  // Length(20, 'm')
     */
    public function mul(float|self|string|UnitInterface $other): self
    {
        // Check for simple multiplication by a scalar.
        if (is_float($other)) {
            return $this->withValue($this->value * $other);
        }

        // Get the other operand as a Quantity object.
        if (!$other instanceof self) {
            $other = self::create(1, CompoundUnit::toCompoundUnit($other));
        }

        // Start by multiplying the values.
        $newValue = $this->value * $other->value;

        // Combine the unit terms from both operands. The CompoundUnit constructor collapses same-dimension terms.
        $newUnitTerms = array_merge(
            array_values($this->compoundUnit->unitTerms),
            array_values($other->compoundUnit->unitTerms)
        );

        // Create the result Quantity.
        return self::create($newValue, new CompoundUnit($newUnitTerms));
    }

    /**
     * Divide this Quantity by a scalar factor, another Quantity, or a unit.
     *
     * Supports multiple call styles:
     * - div($quantity)
     * - div($value)
     * - div($unit)
     *
     * @param self|float|string|UnitInterface $other The Quantity, number, or unit to divide.
     * @return self A new Quantity representing the result of the division.
     * @throws DivisionByZeroError If the divisor is zero.
     * @throws DomainException If the result value is non-finite.
     * @throws FormatException If a unit string cannot be parsed.
     * @throws UnknownUnitException If a unit string contains unknown units.
     * @example
     *   $length = new Length(10, 'm');
     *   $half = $length->div(2);  // Length(5, 'm')
     */
    public function div(float|self|string|UnitInterface $other): self
    {
        // Check for simple division by a scalar.
        if (is_float($other)) {
            if ($other === 0.0) {
                throw new DivisionByZeroError('Cannot divide by zero.');
            }

            return $this->withValue($this->value / $other);
        }

        // Get the other operand as a Quantity object.
        if (!$other instanceof self) {
            $other = self::create(1, CompoundUnit::toCompoundUnit($other));
        }

        // Multiply by the inverse.
        return $this->mul($other->inv());
    }

    // endregion

    // region Power methods

    /**
     * Raise the Quantity to an exponent.
     *
     * @param int $exponent The exponent to raise to.
     * @return self A new Quantity representing the result of the exponentiation.
     * @throws DomainException If the exponent is 0.
     * @example
     *   $length = new Length(10, 'm');
     *   $cubed = $length->pow(3);  // Volume(1000, 'm3')
     */
    public function pow(int $exponent): self
    {
        // Apply the exponent to the value.
        $value = $this->value ** $exponent;

        // Apply the exponent to each unit term.
        $unitTerms = [];
        foreach ($this->compoundUnit->unitTerms as $unitTerm) {
            $unitTerms[] = $unitTerm->pow($exponent);
        }

        // Construct the result Quantity.
        return self::create($value, new CompoundUnit($unitTerms));
    }

    /**
     * Square this Quantity.
     *
     * Equivalent to pow(2).
     *
     * @return self A new Quantity representing the square of this Quantity.
     */
    public function sqr(): self
    {
        return $this->mul($this);
    }

    // endregion

    // region Rounding methods

    /**
     * Round the value to the given precision.
     *
     * @param int $precision The number of decimal places to round to (default 0).
     * @param RoundingMode $mode The rounding mode (default RoundingMode::HalfAwayFromZero).
     * @return static A new Quantity with the rounded value in the same unit.
     */
    public function round(int $precision = 0, RoundingMode $mode = RoundingMode::HalfAwayFromZero): static
    {
        return $this->withValue(round($this->value, $precision, $mode));
    }

    /**
     * Round the value down (towards negative infinity).
     *
     * @return static A new Quantity with the value rounded down, in the same unit.
     */
    public function floor(): static
    {
        return $this->withValue(floor($this->value));
    }

    /**
     * Round the value up (towards positive infinity).
     *
     * @return static A new Quantity with the value rounded up, in the same unit.
     */
    public function ceil(): static
    {
        return $this->withValue(ceil($this->value));
    }

    // endregion

    // region Conversion methods

    /**
     * Format the measurement as a string with control over precision and notation.
     *
     * See Floats::format() for details on the $specifier, $precision, and $trimZeros parameters.
     *
     * It's usually best to leave $includeSpace as null, which uses common style rules to determine if a
     * space should be placed between the number and the unit. The rule is: if the unit is a single
     * non-letter symbol (e.g. °, %, "), no space is inserted. Otherwise, a space is inserted, including
     * for units that start with a non-letter such as °C.
     *
     * @param string $specifier The format specifier.
     * @param ?int $precision Number of decimal places for e/f, or significant digits for g/h (default null = 6).
     * @param ?bool $trimZeros If trailing zeros should be trimmed (default null for auto).
     * @param ?bool $includeSpace Space between value and unit (null = auto, true = always, false = never).
     * @param bool $ascii If true, use ASCII symbols and e notation. If false, use Unicode symbols and ×10 notation.
     * @return string The formatted quantity.
     * @throws DomainException If the specifier or precision is invalid.
     */
    public function format(
        string $specifier = 'g',
        ?int $precision = null,
        ?bool $trimZeros = null,
        ?bool $includeSpace = null,
        bool $ascii = false
    ): string {
        // Format the value.
        $valueStr = Floats::format($this->value, $specifier, $precision, $trimZeros, $ascii);

        // Get the unit as a string.
        $unitSymbol = $this->compoundUnit->format($ascii);

        // If the unit is empty, return the value as a string.
        if ($unitSymbol === '') {
            return $valueStr;
        }

        // If $includeSpace is not specified, do not insert a space between the value and unit if the unit is a single
        // non-letter unit symbol (e.g. °, %, "). Otherwise, insert one space.
        if ($includeSpace === null) {
            $includeSpace = !Unit::isValidNonLetter($unitSymbol);
        }

        // Return the formatted string.
        return $valueStr . ($includeSpace ? ' ' : '') . $unitSymbol;
    }

    /**
     * Convert the measurement to a string using default formatting.
     *
     * For custom formatting, use format().
     *
     * @return string The measurement as a string (e.g. "1.5707963267949 rad").
     */
    #[Override]
    public function __toString(): string
    {
        return $this->format();
    }

    // endregion

    // region Lookup methods

    /**
     * Get the quantity type corresponding to the calling class, if known.
     *
     * This method will return null if called on the Quantity base class, or if the subclass is not registered in the
     * QuantityTypeService.
     *
     * @return ?QuantityType The quantity type, or null if not registered.
     */
    public static function getQuantityType(): ?QuantityType
    {
        // Cached results.
        static $quantityTypes = [];

        // If we got it already, use the cached value.
        if (isset($quantityTypes[static::class])) {
            return $quantityTypes[static::class];
        }

        // Get the QuantityType.
        $quantityTypes[static::class] = QuantityTypeService::getByClass(static::class);

        return $quantityTypes[static::class];
    }

    /**
     * Get the dimension code corresponding to the calling class, if known.
     *
     * Returns the dimension code (e.g. 'L' for Length, 'M' for Mass) if the calling class is a registered
     * quantity type subclass. Returns null if called on Quantity itself or if the subclass is not registered.
     *
     * @return ?string The dimension code, or null if not registered.
     */
    public static function getDimension(): ?string
    {
        return static::getQuantityType()?->dimension;
    }

    // endregion

    // region Parts methods

    /**
     * Create a new Quantity as a sum of measurements of different units.
     *
     * The $parts array may include an optional 'sign' key to indicate the sign of the sum, which must be integer 1
     * (non-negative) or -1 (negative). If omitted, the sign is assumed to be 1.
     *
     * The dimensions of the class quantity type (if not Quantity and registered), and the input and result units must
     * all match.
     *
     * By default, the result quantity will use the base English unit for the quantity type (e.g. 's', '°', 'ft', 'lb'),
     * as parts are typically used with English units. However, $si can be set to true to override this and use the base
     * SI unit for the result. Of course, to() can be called on the result to convert it to whatever unit you want.
     *
     * @param array<string, int|float> $parts The parts, with the part unit symbols as keys, and part values as values.
     * @param bool $si If true, use the SI base unit for the result; if false (default), use the English base unit.
     * @return static A new Quantity representing the sum of the parts.
     * @throws InvalidArgumentException If the sign is not an integer, or if any of the keys in the parts array are not
     * strings, or if any of the values in the parts array are not numbers.
     * @throws DomainException If a sign is specified and it's not -1 or 1, or if any values are non-finite.
     * @throws LogicException If the quantity type is not registered, or if no parts were provided, or if no conversion
     * path exists between a parts unit and the result unit.
     * @throws DimensionMismatchException If the dimensions of the parts do not match the dimension of the quantity
     * type.
     * @throws FormatException If any of the part unit symbols cannot be parsed.
     * @throws UnknownUnitException If any of the part unit symbols are not recognized.
     */
    public static function fromParts(array $parts, bool $si = false): static
    {
        // Validate the sign.
        $sign = 1;
        if (array_key_exists('sign', $parts)) {
            $sign = $parts['sign'];

            // Check the type.
            if (!is_int($sign)) {
                throw new InvalidArgumentException('The "sign" part must be an integer.');
            }

            // Check the value.
            if ($sign !== -1 && $sign !== 1) {
                throw new DomainException("Invalid sign: $sign. Must be -1 or 1.");
            }

            // Remove it from the input array.
            unset($parts['sign']);
        }

        // Check we have some parts. (This must be done after removing the sign part.)
        if (empty($parts)) {
            throw new LogicException('Cannot create a Quantity from an empty parts array.');
        }

        // Ensure we have a QuantityType.
        $quantityType = self::validateQuantityType();
        // Get the dimension.
        $dimension = $quantityType->dimension;
        // Set the result unit to the SI or English base unit matching the dimension.
        $resultUnit = DimensionService::getBaseCompoundUnit($dimension, $si);
        // Initialize the result to 0.
        $result = self::create(0, $resultUnit);

        // Validate and add the parts.
        foreach ($parts as $unitSymbol => $value) {
            // Validate key and value.
            if (!is_string($unitSymbol)) {
                throw new InvalidArgumentException('The parts array must contain only strings as keys.');
            }
            if (!Numbers::isNumber($value)) {
                throw new InvalidArgumentException('The parts array must contain only numbers as values.');
            }
            if (!is_finite($value)) {
                throw new DomainException('The parts values must be finite numbers.');
            }

            // Try to parse the unit. This will throw if the unit is invalid.
            $unit = CompoundUnit::parse($unitSymbol);

            // Check the dimension matches.
            if ($unit->dimension !== $dimension) {
                throw new DimensionMismatchException($dimension, $unit->dimension);
            }

            // Add the part. It will be converted to the result unit automatically.
            $result = $result->add(self::create($value, $unit));
        }

        // Make negative if necessary.
        if ($sign === -1) {
            $result = $result->neg();
        }

        assert($result instanceof static);
        return $result;
    }

    /**
     * Convert a quantity to parts.
     *
     * Returns an array with components from the largest to the smallest unit.
     * Every part unit is included in the result, even if its value is 0.
     * All the part values will be integers except for the smallest unit value.
     * A sign key is also included with an integer value of 1 for non-negative, or -1 for negative.
     *
     * @param ?int $precision The number of decimal places for rounding the smallest unit, or null for no rounding.
     * @param ?list<string> $partUnitSymbols The part unit symbols, or null to use the default for this quantity type.
     * @return array<string, int|float> Array of parts, plus the sign (1 or -1).
     * @throws LogicException If the quantity type is null, or if $partUnitSymbols is empty and no default exists for
     * the quantity type, or if no conversion path exists to a part unit.
     * @throws FormatException If any of the part unit symbols cannot be parsed.
     * @throws DomainException If $precision is specified and negative, or if the $partUnitSymbols array contains
     * duplicate units.
     * @throws InvalidArgumentException If any of the part unit symbols are not strings.
     * @throws UnknownUnitException If a part unit symbol is not recognized.
     * @throws DimensionMismatchException If a part unit is incompatible with the quantity's dimension.
     */
    public function toParts(?int $precision = null, ?array $partUnitSymbols = null): array
    {
        // Ensure we have a QuantityType. Validate the part unit symbols.
        $quantityType = self::validateQuantityType();
        $partUnits = self::validatePartUnits($quantityType, $partUnitSymbols);

        // Validate the precision.
        if ($precision !== null && $precision < 0) {
            throw new DomainException("Invalid precision: $precision. Must be null or a non-negative integer.");
        }

        // Initialize the result array.
        $parts = [
            'sign' => Numbers::sign($this->value, false),
        ];

        // Initialize the remainder to the source value converted to the smallest unit.
        $nUnits = count($partUnits);
        $smallestUnit = $partUnits[$nUnits - 1];
        $smallestUnitSymbol = $smallestUnit->asciiSymbol;
        $rem = abs($this->to($smallestUnit)->value);

        // Get the integer parts.
        for ($i = 0; $i < $nUnits - 1; $i++) {
            // Get the number of current units in the smallest unit.
            $partUnit = $partUnits[$i];
            $factor = self::convert(1, $partUnit, $smallestUnit);
            $curUnitValue = (int)floor($rem / $factor);

            // Add to the array.
            $parts[$partUnit->asciiSymbol] = $curUnitValue;
            $rem = $rem - $curUnitValue * $factor;
        }

        // If the precision is unspecified, we're done.
        if ($precision === null) {
            $parts[$smallestUnitSymbol] = $rem;
        } else {
            // Round off the remainder to the specified precision.
            $rem2 = round($rem, $precision);
            $parts[$smallestUnitSymbol] = $rem2;

            // If the rounding increases the remainder, rounding up one or more larger parts may be necessary.
            if ($rem2 > $rem) {
                // To account for non-integer conversion factors, rebuild the parts array.
                // The fromParts() method is called with $si = false, matching the usual case for parts methods;
                // however, this doesn't actually matter since the quantity is immediately decomposed to parts again.
                // We call toParts() with $precision = null to avoid infinite recursion.
                $parts = static::fromParts($parts)->toParts(null, $partUnitSymbols);

                // Round off the smallest part to the specified precision.
                assert(isset($parts[$smallestUnitSymbol]));
                $parts[$smallestUnitSymbol] = round($parts[$smallestUnitSymbol], $precision);
            }
        }

        return $parts;
    }

    /**
     * Parse a multi-part string into a Quantity.
     *
     * Parts must be separated by spaces.
     * Compound units (e.g. 'kW*h', 'km/h') are supported.
     * Only the first part may be negative.
     *
     * The method varies from parse() as follows:
     * - There cannot be spaces between values and units.
     * - Units containing spaces (e.g. 'US gal') are not supported.
     *
     * By default, the result quantity will use the base English unit for the quantity type (e.g. 's', '°', 'ft', 'lb'),
     * as parts are typically used with these units. However, $si can be set to true to override this and use the base
     * SI unit for the result. Of course, to() can be called on the result to convert it to whatever unit is desired.
     *
     * Examples:
     *     - "4y 5mo 6d 12h 34min 56.789s"
     *     - "12° 34′ 56.789″"
     *
     * @param string $input The string to parse.
     * @param bool $si If true, use the SI base unit for the result; if false (default), use the English base unit.
     * @return static A new Quantity representing the sum of the parts.
     * @throws FormatException If the input string is empty or malformed.
     * @throws LogicException If the quantity type is not registered.
     * @throws UnexpectedValueException If there is an unexpected error during parsing.
     * @throws UnknownUnitException If any units are not recognized.
     * @throws DimensionMismatchException If units have incompatible dimensions.
     */
    public static function parseParts(string $input, bool $si = false): static
    {
        // Ensure the input string is not empty.
        $input = trim($input);
        if ($input === '') {
            throw new FormatException('The input string is empty.');
        }

        // Ensure we have a QuantityType.
        $quantityType = self::validateQuantityType();
        // Prepare error message.
        $err = "Cannot parse input string into a $quantityType->name quantity: '$input'.";

        // Split string on whitespace to get the quantity parts.
        $partStrings = preg_split('/\s+/', $input);
        if ($partStrings === false) {
            // @codeCoverageIgnoreStart
            throw new UnexpectedValueException('Error splitting string into parts.');
            // @codeCoverageIgnoreEnd
        }

        // Collect the parts.
        $parts = [];
        $sign = 1;
        $firstPartSeen = false;
        foreach ($partStrings as $partString) {
            // Check the part string looks like a quantity.
            if (!self::isValidQuantity($partString, $m)) {
                throw new FormatException($err);
            }

            // Check the sign.
            $partValue = (float)$m[1];
            if ($partValue < 0) {
                if (!$firstPartSeen) {
                    $sign = -1;
                } else {
                    throw new FormatException(
                        'In a string with multiple quantity parts, only the first may be negative.'
                    );
                }
            }

            // Add the part to the result array.
            $partSymbol = $m[2] ?? '';
            // Check the unit is new.
            if (array_key_exists($partSymbol, $parts)) {
                throw new FormatException("Duplicate unit symbol in input string: '$partSymbol'.");
            }
            $parts[$partSymbol] = abs($partValue);

            if (!$firstPartSeen) {
                $firstPartSeen = true;
            }
        }

        // Add the sign.
        $parts['sign'] = $sign;

        // Construct the new Quantity from the extracted parts.
        return static::fromParts($parts, $si);
    }

    /**
     * Format the quantity as parts.
     *
     * Examples:
     *   - "4y 5mo 6d 12h 34min 56.789s"
     *   - "12° 34′ 56.789″"
     *
     * Only the smallest unit may have a decimal point. Larger units will be integers.
     *
     * Note, if $showZeros is false, then any parts with zero values will not be included in the result string, unless
     * the quantity value is 0, in which case the result will be 0 of the smallest unit, e.g. "0s" or "0ft".
     *
     * @param ?int $precision The number of decimal places for rounding the smallest unit, or null for no rounding.
     * @param bool $showZeros If true, show all parts including zeros; if false, skip zero-value components.
     * @param bool $ascii If true, use ASCII characters only.
     * @param ?list<string> $partUnitSymbols The part unit symbols, or null to use the default for this quantity type.
     * @return string The formatted string.
     * @throws LogicException If the quantity type is null, or if $partUnitSymbols is empty and no default exists for
     * the quantity type.
     * @throws DomainException If $precision is negative or the $partUnitSymbols array contains duplicate units.
     * @throws InvalidArgumentException If any of the part unit symbols are not strings.
     * @throws UnknownUnitException If a part unit symbol is not recognized.
     * @throws DimensionMismatchException If a part unit is incompatible with the quantity's dimension.
     */
    public function formatParts(
        ?int $precision = null,
        bool $showZeros = false,
        bool $ascii = false,
        ?array $partUnitSymbols = null
    ): string {
        // Get the quantity as parts. This array has the sign as the first item, followed by the parts in descending
        // order by unit size.
        $parts = $this->toParts($precision, $partUnitSymbols);

        // Extract the sign.
        $sign = $parts['sign'];
        unset($parts['sign']);

        // Prep for loop.
        $result = [];
        $smallestUnitSymbol = array_key_last($parts);
        assert($smallestUnitSymbol !== null);

        // Generate substrings for each part.
        foreach ($parts as $unitSymbol => $value) {
            // Include the part if non-zero or including all zeroes.
            $include = !Floats::approxEqual($value, 0) || $showZeros;
            if ($unitSymbol !== $smallestUnitSymbol) {
                if ($include) {
                    $unit = Unit::parse($unitSymbol);
                    $result[] = $value . $unit->format($ascii);
                }
            } else {
                // Include the smallest part if non-zero or including all zeroes or no other parts have been added.
                if ($include || empty($result)) {
                    // Format with decimals. The $ascii parameter does nothing when the specifier is 'f', but it
                    // doesn't hurt to pass it anyway, in case format() changes.
                    $valueStr = Floats::format($value, 'f', $precision, null, $ascii);
                    $smallestUnit = Unit::parse($smallestUnitSymbol);
                    $result[] = $valueStr . $smallestUnit->format($ascii);
                }
            }
        }

        // Return a string of units, separated by spaces. Prepend minus sign if negative.
        return ($sign === -1 ? '-' : '') . implode(' ', $result);
    }

    /**
     * Get the default units for the toParts() result.
     * This method can be overridden in subclasses that provide parts functionality.
     *
     * @return ?list<string>
     */
    public static function getPartUnitSymbols(): ?array
    {
        return null;
    }

    /**
     * Check that the quantity type is registered, and return it.
     *
     * @return QuantityType The quantity type for the calling class.
     * @throws LogicException If the quantity type is null.
     */
    private static function validateQuantityType(): QuantityType
    {
        $quantityType = static::getQuantityType();
        if ($quantityType === null) {
            throw new LogicException('Quantity type cannot be null.');
        }
        return $quantityType;
    }

    /**
     * Validate the part unit symbols and resolve them to Unit objects.
     *
     * If $partUnitSymbols is empty (null or empty array), falls back to the value returned by
     * static::getPartUnitSymbols().
     *
     * Units are sorted in descending order by size.
     *
     * @param QuantityType $quantityType The quantity type for the calling class.
     * @param ?list<string> $partUnitSymbols The part unit symbols to validate, or null to use the default for the given
     * quantity type.
     * @return list<CompoundUnit> The corresponding Unit objects in descending order by size.
     * @throws LogicException If $partUnitSymbols is empty and no default exists for the quantity type.
     * @throws InvalidArgumentException If any of the part unit symbols are not strings.
     * @throws FormatException If any of the part unit symbols cannot be parsed.
     * @throws UnknownUnitException If a part unit symbol is not recognized.
     * @throws DimensionMismatchException If a part unit's dimension doesn't match the quantity type.
     * @throws DomainException If the array contains duplicate units.
     */
    private static function validatePartUnits(QuantityType $quantityType, ?array $partUnitSymbols): array
    {
        // If $partUnitSymbols is null or an empty array, get the default value if there is one.
        if (empty($partUnitSymbols)) {
            $partUnitSymbols = static::getPartUnitSymbols();
        }

        // Ensure we have some units.
        if (empty($partUnitSymbols)) {
            throw new LogicException("No default part unit symbols configured for $quantityType->name quantities.");
        }

        // Resolve each symbol to a Unit object.
        $partUnits = [];
        foreach ($partUnitSymbols as $partUnitSymbol) {
            // Check the item type. The ?list<string> annotation is for static analysis only; PHP itself sees
            // ?array, so a non-string can still arrive here at runtime.
            if (!is_string($partUnitSymbol)) {
                throw new InvalidArgumentException('The array of part unit symbols must contain only strings.');
            }

            // Get the unit. This will throw if the unit is invalid.
            $partUnit = CompoundUnit::parse($partUnitSymbol);

            // Check the dimension matches.
            if ($partUnit->dimension !== $quantityType->dimension) {
                throw new DimensionMismatchException($quantityType->dimension, $partUnit->dimension);
            }

            // Check for duplicate.
            $asciiSymbol = $partUnit->asciiSymbol;
            if (array_key_exists($asciiSymbol, $partUnits)) {
                throw new DomainException("Duplicate part unit: $asciiSymbol");
            }

            // Add the unit.
            $partUnits[$asciiSymbol] = $partUnit;
        }

        // Sort units by decreasing size.
        $baseUnit = DimensionService::getBaseCompoundUnit($quantityType->dimension);
        $sizes = array_map(
            static fn ($unit): float => ConversionService::convert(1, $unit, $baseUnit),
            $partUnits
        );
        arsort($sizes);

        // Build result array.
        $result = [];
        foreach ($sizes as $asciiSymbol => $size) {
            $result[] = $partUnits[$asciiSymbol];
        }

        return $result;
    }

    // endregion

    // region Subclass methods

    /**
     * Unit definitions.
     *
     * This method should be overridden in subclasses to specify the units relevant to that quantity type.
     *
     * @return array<string, array{
     *     asciiSymbol: string,
     *     unicodeSymbol?: string,
     *     prefixGroup?: int,
     *     alternateSymbol?: string,
     *     systems: list<UnitSystem>
     * }>
     */
    public static function getUnitDefinitions(): array
    {
        return [];
    }

    /**
     * Conversion definitions.
     *
     * This method should be overridden in subclasses to specify the conversions relevant to that quantity type.
     *
     * @return list<array{string, string, float}>
     */
    public static function getConversionDefinitions(): array
    {
        return [];
    }

    // endregion

    // region Validation methods

    /**
     * Check if a string is a valid quantity representation (number optionally followed by a unit).
     *
     * The match results:
     * - $matches[0] is the entire match
     * - $matches[1] is the value
     * - $matches[2] is the unit (not set for a dimensionless quantity)
     *
     * @param string $qty The quantity string to validate.
     * @param ?array<array-key, string> $matches Output array for match results.
     * @return bool True if the quantity string is valid.
     */
    public static function isValidQuantity(string $qty, ?array &$matches): bool
    {
        $rxNum = Numbers::REGEX;
        $rxCompoundUnit = CompoundUnit::regex();
        return (bool)preg_match("/^($rxNum)\s*($rxCompoundUnit)?$/iu", $qty, $matches);
    }

    // endregion

    // region Helper methods

    /**
     * Construct a bare Quantity object, bypassing the direct-instantiation guard.
     *
     * For internal use only. This is used by create() and withValue() to construct a generic Quantity
     * for an unregistered dimension code. Always returns a base Quantity, never a subclass.
     *
     * @param float $value The numeric value.
     * @param CompoundUnit $unit The compound unit.
     * @return self A new base Quantity instance.
     * @throws DomainException If the value is non-finite (±INF or NAN).
     */
    private static function new(float $value, CompoundUnit $unit): self
    {
        // Temporarily enable calling `new Quantity()`.
        self::$allowConstruct = true;
        try {
            return new self($value, $unit);
        } finally {
            self::$allowConstruct = false;
        }
    }

    /**
     * Check the $this and $other objects have the same type and get the value of the $other Quantity in the same
     * unit as the $this one. Return the value.
     *
     * @param mixed $other The other measurement to compare with.
     * @return float The value of the other measurement in the same unit as this one.
     * @throws LogicException If no conversion path exists between the units.
     * @throws IncomparableTypesException If the other value is not a Quantity.
     * @throws DimensionMismatchException If the two Quantities have different dimensions.
     */
    private function preCompare(mixed $other): float
    {
        // Check the two values are both Quantity objects.
        if (!$other instanceof self) {
            throw new IncomparableTypesException($this, $other);
        }

        // Check the two Quantities have the same dimension.
        $dim1 = $this->compoundUnit->dimension;
        $dim2 = $other->compoundUnit->dimension;
        if ($dim1 !== $dim2) {
            throw new DimensionMismatchException($dim1, $dim2);
        }

        // Get the other Quantity in the same unit as this one.
        return $this->compoundUnit->equal($other->compoundUnit)
            ? $other->value
            : $other->to($this->compoundUnit)->value;
    }

    // endregion
}
