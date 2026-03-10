<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use ArgumentCountError;
use DivisionByZeroError;
use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Core\Exceptions\IncomparableTypesException;
use Galaxon\Core\Floats;
use Galaxon\Core\Integers;
use Galaxon\Core\Numbers;
use Galaxon\Core\Traits\ApproxComparable;
use Galaxon\Quantities\Internal\DerivedUnit;
use Galaxon\Quantities\Internal\QuantityType;
use Galaxon\Quantities\Internal\UnitInterface;
use Galaxon\Quantities\Internal\UnitTerm;
use Galaxon\Quantities\Services\ConversionService;
use Galaxon\Quantities\Services\PrefixService;
use Galaxon\Quantities\Services\QuantityPartsService;
use Galaxon\Quantities\Services\QuantityTypeService;
use Galaxon\Quantities\Services\RegexService;
use Galaxon\Quantities\Services\UnitService;
use InvalidArgumentException;
use LogicException;
use Override;
use Stringable;
use UnexpectedValueException;

/**
 * Abstract base class for physical measurements with units.
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

    // region Static properties

    /**
     * Flag to permit call to new Quantity().
     *
     */
    private static bool $allowConstruct = false;

    // endregion

    // region Instance properties

    /**
     * The numeric value of the measurement in the specified unit.
     */
    public readonly float $value;

    /**
     * The unit of the measurement.
     */
    public readonly DerivedUnit $derivedUnit;

    // endregion

    // region Property hooks

    /**
     * The dimension.
     *
     */
    public string $dimension {
        get => $this->derivedUnit->dimension;
    }

    /**
     * The quantity type this quantity is for, if known.
     *
     */
    public ?QuantityType $quantityType {
        get => QuantityTypeService::getByDimension($this->dimension);
    }

    // endregion

    // region Constructor and factory method

    /**
     * Constructor.
     *
     * Creates a new measurement with the specified value and unit.
     * Validates that the value is finite and the unit is valid.
     * Furthermore, ensures the calling class matches the unit dimension.
     * If the unit is omitted, the quantity is dimensionless.
     *
     * @param float $value The numeric value in the given unit.
     * @param null|string|UnitInterface $unit The unit as a symbol string (e.g. 'kg', 'mm', 'hr'),
     * object, or null if dimensionless.
     * @throws DomainException If the value is non-finite (±INF or NAN).
     * @throws FormatException If the unit is provided as a string, and it cannot be parsed.
     * @throws DomainException If the unit is provided as a string, and it contains unknown units.
     * @throws LogicException If the wrong constructor is being called for a quantity with the given unit.
     */
    public function __construct(float $value, null|string|UnitInterface $unit = null)
    {
        // Check they aren't calling `new Quantity()`. We only want them to use `new Angle()` (for example) or
        // `Quantity::create()`.
        $qtyClass = self::class;
        $callingClass = static::class;
        if ($qtyClass === $callingClass && !self::$allowConstruct) {
            throw new LogicException(
                'The Quantity constructor should not be called directly. Use `Quantity::create()`, ' .
                '`Quantity::parse()`, or a specific quantity constructor (e.g. `new Angle(...)`.'
            );
        }

        // Check the value is finite.
        if (!is_finite($value)) {
            throw new DomainException('Quantity value cannot be ±INF or NAN.');
        }

        // Convert the provided unit argument into an object if it isn't already.
        // This will throw if the unit is provided as a string that doesn't represent a valid DerivedUnit.
        $derivedUnit = DerivedUnit::toDerivedUnit($unit);

        // Check they are calling the correct constructor.
        $qtyType = $derivedUnit->quantityType;
        $correctClass = $qtyType->class ?? $qtyClass;
        if ($callingClass !== $correctClass) {
            $classStr = $qtyType === null
                ? "quantity with dimension '$derivedUnit->dimension'"
                : "$qtyType->name quantity";
            throw new LogicException(
                "A $classStr cannot be instantiated by calling `new $callingClass()`. Instead, " .
                ' call `Quantity::create()`, which will automatically create an object of the correct class.'
            );
        }

        // Set the properties.
        $this->value = Floats::normalizeZero($value);
        $this->derivedUnit = $derivedUnit;
    }

    /**
     * Create a Quantity of the appropriate type for the given unit.
     *
     * This works whether called from Quantity or a subclass.
     * Uses the dimension class registry to instantiate the correct subclass.
     * For example, a unit with dimension 'L2' will create an Area object (assuming Area is registered).
     *
     * @param float $value The numeric value.
     * @param null|string|UnitInterface $unit The unit.
     * @return self A Quantity of the appropriate type.
     * @throws DomainException If the value is non-finite (±INF or NAN).
     * @throws FormatException If the unit is provided as a string, and it cannot be parsed.
     * @throws DomainException If the unit is provided as a string, and it contains unknown units.
     */
    public static function create(float $value, null|string|UnitInterface $unit = null): self
    {
        // Check the value is finite.
        if (!is_finite($value)) {
            throw new DomainException('Value cannot be ±INF or NAN.');
        }

        // Get unit as DerivedUnit.
        $unit = DerivedUnit::toDerivedUnit($unit);

        // If there's a registered subclass for this dimension code, create an object of that class.
        $qtyType = $unit->quantityType;
        if ($qtyType !== null) {
            return new ($qtyType->class)($value, $unit);
        }

        // Fall back to a generic Quantity object. Temporarily enable calling `new Quantity()`.
        self::$allowConstruct = true;
        try {
            return new self($value, $unit);
        } finally {
            self::$allowConstruct = false;
        }
    }

    // endregion

    // region Overrideable methods

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

    // endregion

    // region Static access methods

    /**
     * Get the quantity type corresponding to the calling class, if known.
     *
     * Static equivalent to the $quantityType property.
     * This method will return null if called on Quantity itself or if the subclass is not registered.
     *
     * @return ?QuantityType The quantity type, or null if not registered.
     */
    public static function getQuantityType(): ?QuantityType
    {
        return QuantityTypeService::getByClass(static::class);
    }

    // endregion

    // region Conversion methods

    /**
     * Convert this Quantity to a different unit.
     *
     * Returns a new Quantity object with the equivalent value in the destination unit.
     *
     * @param string|UnitInterface $destUnit The destination unit to convert to.
     * @return self A new Quantity in the specified unit.
     * @throws DomainException If the destination unit is invalid.
     * @throws LogicException If no conversion path exists between the units.
     * @example
     *   $length = new Length(1000, 'm');
     *   $km = $length->to('km');  // Length(1, 'km')
     */
    public function to(string|UnitInterface $destUnit): self
    {
        // Convert the value to the target unit.
        $value = static::convert($this->value, $this->derivedUnit, $destUnit);

        // Return the new object.
        return self::create($value, $destUnit);
    }

    /**
     * Convert this quantity to SI units.
     *
     * If $simplify is true, base units will be replaced by expandable units where possible, e.g. kg*m/s2 => N
     * The expandable unit that replaces the largest number of base units will be chosen.
     *
     * If $autoPrefix is true, the result will be converted to use the best prefix, defined as:
     * 1. Is valid for the first unit term.
     * 2. Represents a multiple of 1000 or 1/1000. This is often called an engineering prefix.
     * 3. Produces the smallest value greater than or equal to 1.
     *
     * @param bool $simplify If true, base units will be replaced by expandable units where possible.
     * @param bool $autoPrefix If true, the result will be converted to the best SI prefix.
     * @return self A new Quantity with the value converted to SI units.
     */
    public function toSi(bool $simplify = true, bool $autoPrefix = true): self
    {
        // Convert to SI base units.
        $result = $this->toSiBase();

        // Simplify if requested.
        if ($simplify) {
            return $result->simplify($autoPrefix);
        }

        // Auto-prefix if requested.
        return $autoPrefix ? $result->autoPrefix() : $result;
    }

    /**
     * Convert this Quantity to SI base units without simplification or auto-prefixing.
     *
     * Unlike toSi(), this method returns purely SI base units (e.g., kg·m·s⁻² instead of N).
     * Useful for calculations or when you need the fundamental SI form.
     *
     * @return self A new Quantity expressed in SI base units.
     */
    public function toSiBase(): self
    {
        return $this->to($this->derivedUnit->toSiBase());
    }

    /**
     * Convert this Quantity to English base units.
     *
     * This method returns purely base units (e.g., lb·ft·s⁻² instead of lbf).
     *
     * @return self A new Quantity expressed in English base units.
     */
    public function toEnglishBase(): self
    {
        return $this->to($this->derivedUnit->toEnglishBase());
    }

    /**
     * Convert this Quantity to base units.
     *
     * This method will convert the quantity to SI or English units, depending on what's a better fit with the
     * existing derived unit. For example, units like lbf, mi, ac, US gal, etc. will be converted to lb, ft, and s.
     * But units like km, mg, N, Hz, etc. will be converted to kg, m and s.
     *
     * @return self A new Quantity expressed in SI or English base units.
     */
    public function toBase(): self
    {
        return $this->derivedUnit->siExpansionPreferred() ? $this->toSiBase() : $this->toEnglishBase();
    }

    // endregion

    // region Transformation methods

    /**
     * Create a new Quantity with the same unit but a different value.
     *
     * @param float $value The new numeric value.
     * @return self A new Quantity with the given value in the same unit.
     */
    public function withValue(float $value): self
    {
        if ($this->value === $value) {
            return $this;
        }

        return self::create($value, $this->derivedUnit);
    }

    /**
     * Substitute expandable units for base units, e.g. N => kg*m/s2
     *
     * @return self A new Quantity with expandable (named) units expanded.
     */
    public function expand(): self
    {
        // Try to expand the derived unit.
        $expansion = $this->derivedUnit->tryExpand();

        // Return if we found an expansion.
        if ($expansion !== null) {
            // Multiply the expansion Quantity by this Quantity's value.
            return $expansion->mul($this->value);
        }

        // If no expansion was found, fall back to SI or English base units, whichever is more suitable.
        // This process will add a new expansion conversion to the registry, so if this method is called again with the
        // same derived unit, it will exit earlier, after the call to tryExpand().
        return $this->toBase();
    }

    /**
     * Merge units that have the same dimension, e.g. 'm' and 'ft', or 's' and 'h', or 'lb' and 'kg'.
     *
     * The first unit encountered of a given dimension will be the one any others are converted to.
     *
     * @return self A new Quantity with compatible units merged.
     */
    public function merge(): self
    {
        if (!$this->derivedUnit->isMergeable()) {
            return $this;
        }

        // Merge the derived unit.
        $mergeQty = $this->derivedUnit->merge();

        // Multiply the merged Quantity by this Quantity's value.
        return $mergeQty->mul($this->value);
    }

    /**
     * Find the best SI prefix and construct a new Quantity equal to this one, but with the prefix applied.
     *
     * @return self A new Quantity with the best SI prefix applied.
     */
    public function autoPrefix(): self
    {
        // See what prefixes are available for the first unit term.
        $firstUnitTerm = $this->derivedUnit->firstUnitTerm;
        if ($firstUnitTerm === null || $firstUnitTerm->unit->prefixGroup === 0) {
            // There is no first unit (dimensionless), or no prefixes are available for the first unit, so we can't add
            // one.
            return $this;
        }

        // Initialize the new value and derived unit by removing all current prefixes.
        $newValue = $this->value * $this->derivedUnit->multiplier;
        $newDerivedUnit = $this->derivedUnit->removePrefixes();

        // Get the new first unit term.
        $firstUnitTerm = $newDerivedUnit->firstUnitTerm;
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

        // If we found a better prefix than none at all, apply it if it's different.
        if ($bestPrefix !== null) {
            // Remove the first unit term.
            $newDerivedUnit->removeUnitTerm($firstUnitTerm);
            // Create a new one with the prefix applied.
            $newUnitTerm = new UnitTerm($firstUnitTerm->unit, $bestPrefix, $firstUnitTerm->exponent);
            // Add it.
            $newDerivedUnit->addUnitTerm($newUnitTerm);
        }

        // Create the result object.
        return self::create($bestValue * $sign, $newDerivedUnit);
    }

    /**
     * Substitute base units for expandable units, e.g. kg*m/s2 => N
     *
     * The expandable unit that replaces the largest number of base units will be chosen.
     *
     * 's-1' will not be replaced by 'Hz' unless it's the only unit term.
     * Furthermore, 's-1' is not replaced by 'Bq'. You can call $q->to('Bq') to get that effect.
     *
     * @return self A new Quantity with expandable units substituted for base units.
     */
    public function simplify(bool $autoPrefix = true): self
    {
        // Merge compatible units.
        $qty = $this->merge();

        // Handle Hertz separately. We only want to swap 's-1' for 'Hz' if it's the only unit term.
        if (count($qty->derivedUnit->unitTerms) === 1) {
            $unitTerm = $qty->derivedUnit->firstUnitTerm;

            // Check if we have s-1.
            if ($unitTerm !== null && $unitTerm->unit->asciiSymbol === 's' && $unitTerm->exponent === -1) {
                // Create the Hz unit term.
                $newUnitTerm = new UnitTerm('Hz', PrefixService::invert($unitTerm->prefix));
                $result = self::create($qty->value, $newUnitTerm);

                // Auto-prefix if requested.
                return $autoPrefix ? $result->autoPrefix() : $result;
            }
        }

        // Start constructing the result.
        $newValue = $qty->value;
        $newUnit = clone $qty->derivedUnit;

        // Track the best match.
        $bestExpandableUnit = null;
        $bestMatchScore = 0;
        $bestUnitToReplace = null;

        // Loop through the units and try to find an expandable unit that matches the quantity.
        foreach (UnitService::getAll() as $unit) {
            /// Skip any units that don't have an expansion, or that we don't yet know the expansion for.
            if ($unit->expansion === null) {
                continue;
            }

            // Get the expansion unit.
            $expansionUnit = $unit->expansion->derivedUnit;

            // Skip units that expand to s-1 (i.e. 'Hz' or 'Bq').
            if (count($expansionUnit->unitTerms) === 1 && $expansionUnit->firstUnitTerm?->asciiSymbol === 's-1') {
                continue;
            }

            $expandableUnitMatchesQty = true;
            $matchScore = 0;
            $unitToReplace = new DerivedUnit();

            // Go through the expansion unit terms and try to match against the quantity unit terms.
            foreach ($expansionUnit->unitTerms as $expansionUnitTerm) {
                $matchingQtyUnitTermFound = false;

                // See if the quantity has all the unit terms of the expansion unit.
                foreach ($qty->derivedUnit->unitTerms as $qtyUnitTerm) {
                    // For a unit term from the quantity to match one from the expansion unit, it must have:
                    // - the same unexponentiated symbol (i.e. matching prefixed unit)
                    // - the same sign of the exponent
                    // - the absolute value of the exponent greater than or equal to that of the expansion unit term
                    $exp = abs($expansionUnitTerm->exponent);
                    if (
                        $qtyUnitTerm->unexponentiatedAsciiSymbol === $expansionUnitTerm->unexponentiatedAsciiSymbol &&
                        Numbers::sign($qtyUnitTerm->exponent) === Numbers::sign($expansionUnitTerm->exponent) &&
                        abs($qtyUnitTerm->exponent) >= $exp
                    ) {
                        $matchScore += $exp;
                        $matchingQtyUnitTermFound = true;
                        $unitToReplace->addUnitTerm(
                            new UnitTerm($qtyUnitTerm->unit, $qtyUnitTerm->prefix, $expansionUnitTerm->exponent)
                        );
                        break;
                    }
                }

                // If we didn't find a matching unit term, this expandable unit is not a match.
                if (!$matchingQtyUnitTermFound) {
                    $expandableUnitMatchesQty = false;
                    break;
                }
            }

            // If we found a better match, update our search result.
            if ($expandableUnitMatchesQty && $matchScore > $bestMatchScore) {
                $bestExpandableUnit = $unit;
                $bestMatchScore = $matchScore;
                $bestUnitToReplace = $unitToReplace;
            }
        }

        // If we found a match, substitute the necessary unit terms for the expandable unit.
        if ($bestExpandableUnit !== null && $bestUnitToReplace !== null) {
            // Remove the unit terms (or parts thereof) to replace.
            foreach ($bestUnitToReplace->unitTerms as $unitTermToReplace) {
                $newUnit->addUnitTerm($unitTermToReplace->inv());
            }

            // Add the expandable unit.
            $newUnit->addUnitTerm(new UnitTerm($bestExpandableUnit));

            // Multiply by the conversion factor.
            $newValue *= static::convert(1, $bestUnitToReplace, $bestExpandableUnit);
        }

        // Construct the result.
        $result = self::create($newValue, $newUnit);

        // Auto-prefix if requested.
        return $autoPrefix ? $result->autoPrefix() : $result;
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
     * @throws InvalidArgumentException If the Quantities have different dimensions.
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
            // This will throw the other Quantity has a different type or dimension.
            $otherValue = $this->preCompare($other);
        } catch (DomainException | IncomparableTypesException) {
            // If the other Quantity has a different type or dimension to this one.
            return false;
        }

        // Now we have the other Quantity in the same unit, compare the values.
        return Floats::approxEqual($this->value, $otherValue, $relTol, $absTol);
    }

    // endregion

    // region Arithmetic methods

    /**
     * Convert the arguments supplied to an arithmetic method into a Quantity object.
     *
     * @param Quantity|float|string|UnitInterface $operand The first operand (Quantity, number, or unit).
     * @param string|UnitInterface|null $operandUnit The unit of the first operand, if it is a float.
     * @return self The equivalent Quantity.
     * @throws ArgumentCountError If a unit is specified when the operand is not a float.
     * @throws DomainException If the value is non-finite or the unit is unknown.
     * @throws FormatException If a unit string cannot be parsed.
     */
    private static function argsToQuantity(
        self|float|string|UnitInterface $operand,
        null|string|UnitInterface $operandUnit = null
    ): self {
        // Construct the Quantity from the given value and unit.
        if (is_float($operand)) {
            // If the operand unit is null or not provided, this will create a dimensionless Quantity.
            return self::create($operand, $operandUnit);
        }

        // Check no unit was provided.
        if ($operandUnit !== null) {
            throw new ArgumentCountError('Cannot specify a unit unless the first argument is a float.');
        }

        // If the operand is a Quantity, just return it.
        if ($operand instanceof self) {
            return $operand;
        }

        // Construct the Quantity from the given unit (string, Unit, UnitTerm, or DerivedUnit).
        return self::create(1, DerivedUnit::toDerivedUnit($operand));
    }

    /**
     * Get the absolute value of this Quantity.
     *
     * @return self A new Quantity with a non-negative value and the same unit.
     * @example
     *   $temp = new Temperature(-10, 'C');
     *   $abs = $temp->abs();  // Temperature(10, 'C')
     */
    public function abs(): self
    {
        return self::create(abs($this->value), $this->derivedUnit);
    }

    /**
     * Negate a Quantity.
     *
     * @return self A new Quantity containing the negative of this Quantity's unit.
     * @example
     *   $length = new Length(10, 'm');
     *   $negated = $length->neg();  // Length(-10, 'm')
     */
    public function neg(): self
    {
        return self::create(-$this->value, $this->derivedUnit);
    }

    /**
     * Add another Quantity to this one. Units must be compatible, i.e. have the same dimension.
     *
     * Supports multiple call styles:
     * - add($quantity)
     * - add($value, $unit)
     *
     * Automatically converts units before adding.
     *
     * @param self|float $operand Another Quantity or a numeric value.
     * @param null|string|UnitInterface $operandUnit The other quantity's unit, if a numeric value was provided.
     * @return self A new Quantity containing the sum in this measurement's unit.
     * @throws ArgumentCountError If a unit is specified when the operand is a Quantity.
     * @throws DomainException If the value is non-finite, the unit is unknown, or the dimensions don't match.
     * @throws FormatException If a unit string cannot be parsed.
     * @throws LogicException If no conversion path exists between the units.
     * @example
     *   $a = new Length(100, 'm');
     *   $b = new Length(2, 'km');
     *   $sum = $a->add($b);         // Length(2100, 'm')
     *   $sum2 = $a->add(50, 'cm');  // Length(100.5, 'm')
     */
    public function add(self|float $operand, null|string|UnitInterface $operandUnit = null): self
    {
        // Get the other operand as a Quantity object.
        $other = self::argsToQuantity($operand, $operandUnit);

        // Get the other Quantity in the same unit as this one.
        $otherValue = $this->derivedUnit->equal($other->derivedUnit)
            ? $other->value
            : $other->to($this->derivedUnit)->value;

        // Add the two values.
        return $this->withValue($this->value + $otherValue);
    }

    /**
     * Subtract another Quantity from this one. Units must be compatible, i.e. have the same dimension.
     *
     * Supports multiple call styles:
     * - sub($quantity)
     * - sub($value, $unit)
     *
     * Automatically converts units before subtracting.
     *
     * @param self|float $operand Another Quantity or a numeric value.
     * @param null|string|UnitInterface $operandUnit The other quantity's unit, if a numeric value was provided.
     * @return self A new Quantity containing the difference in this measurement's unit.
     * @throws ArgumentCountError If a unit is specified when the operand is a Quantity.
     * @throws DomainException If the value is non-finite, the unit is unknown, or the dimensions don't match.
     * @throws FormatException If a unit string cannot be parsed.
     * @throws LogicException If no conversion path exists between the units.
     * @example
     *   $a = new Length(100, 'm');
     *   $b = new Length(2, 'km');
     *   $diff = $a->sub($b);  // Length(-1900, 'm')
     */
    public function sub(self|float $operand, null|string|UnitInterface $operandUnit = null): self
    {
        // Get the other operand as a Quantity object.
        $other = self::argsToQuantity($operand, $operandUnit);

        // Get the other Quantity in the same unit as this one.
        $otherValue = $this->derivedUnit->equal($other->derivedUnit)
            ? $other->value
            : $other->to($this->derivedUnit)->value;

        // Subtract the values.
        return $this->withValue($this->value - $otherValue);
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
            throw new DivisionByZeroError('Cannot invert a quantity with a value of 0.');
        }

        // Invert the value and unit.
        return self::create(1.0 / $this->value, $this->derivedUnit->inv());
    }

    /**
     * Multiply this Quantity by a scalar factor, another Quantity, or a unit.
     *
     * Supports multiple call styles:
     * - mul($quantity)
     * - mul($value)
     * - mul($unit)
     * - mul($value, $unit)
     *
     * @param self|float|string|UnitInterface $operand Another Quantity or a numeric value or a unit.
     * @param null|string|UnitInterface $operandUnit The other quantity's unit, if a numeric value was provided.
     * @return self A new Quantity representing the result of the multiplication.
     * @throws ArgumentCountError If a unit is specified when the operand is not a float.
     * @throws DomainException If a value is non-finite or the unit is unknown.
     * @throws FormatException If a unit string cannot be parsed.
     * @example
     *   $length = new Length(10, 'm');
     *   $doubled = $length->mul(2);  // Length(20, 'm')
     */
    public function mul(float|self|string|UnitInterface $operand, null|string|UnitInterface $operandUnit = null): self
    {
        // Check for simple multiplication by a scalar.
        if (is_float($operand) && $operandUnit === null) {
            return $this->withValue($this->value * $operand);
        }

        // Get the other operand as a Quantity object.
        $other = self::argsToQuantity($operand, $operandUnit);

        // Start by multiplying the values.
        $newValue = $this->value * $other->value;

        // Create a new unit from this unit.
        $newUnit = clone $this->derivedUnit;

        // Add each unit term from the other Quantity.
        foreach ($other->derivedUnit->unitTerms as $otherUnitTerm) {
            $newUnit->addUnitTerm($otherUnitTerm);
        }

        // Create the result Quantity.
        return self::create($newValue, $newUnit);
    }

    /**
     * Divide this Quantity by a scalar factor, another Quantity, or a unit.
     *
     * Supports multiple call styles:
     * - div($quantity)
     * - div($value)
     * - div($unit)
     * - div($value, $unit)
     *
     * @param self|float|string|UnitInterface $operand Another Quantity or a numeric value or a unit.
     * @param null|string|UnitInterface $operandUnit The other quantity's unit, if a numeric value was provided.
     * @return self A new Quantity representing the result of the division.
     * @throws ArgumentCountError If a unit is specified when the operand is not a float.
     * @throws DivisionByZeroError If the divisor is zero.
     * @throws DomainException If a value is non-finite or the unit is unknown.
     * @throws FormatException If a unit string cannot be parsed.
     * @example
     *   $length = new Length(10, 'm');
     *   $half = $length->div(2);  // Length(5, 'm')
     */
    public function div(float|self|string|UnitInterface $operand, null|string|UnitInterface $operandUnit = null): self
    {
        // Check for simple division by a scalar.
        if (is_float($operand) && $operandUnit === null) {
            if ($operand === 0.0) {
                throw new DivisionByZeroError('Cannot divide a quantity by 0.');
            }

            return $this->withValue($this->value / $operand);
        }

        // Get the other operand as a Quantity object.
        $other = self::argsToQuantity($operand, $operandUnit);

        // Multiply by the inverse.
        return $this->mul($other->inv());
    }

    /**
     * Raise the Quantity to an exponent.
     *
     * @param int $exponent The exponent to raise to.
     * @return self A new Quantity representing the result of the exponentiation.
     * @throws DomainException If the exponent is 0.
     * @example
     *   $length = new Length(10, 'm');
     *   $squared = $length->pow(2);  // Length(100, 'm^2')
     */
    public function pow(int $exponent): self
    {
        // Apply the exponent to the value.
        $value = $this->value ** $exponent;

        // Apply the exponent to each unit term.
        $unitTerms = [];
        foreach ($this->derivedUnit->unitTerms as $unitTerm) {
            $unitTerms[] = $unitTerm->pow($exponent);
        }

        // Construct the result Quantity.
        return self::create($value, new DerivedUnit($unitTerms));
    }

    // endregion

    // region String methods

    /**
     * Parse a string representation into a Quantity object.
     *
     * Accepts formats like "123.45 km", "90deg", "1.5e3 ms".
     * Whitespace between value and unit is optional.
     *
     * @param string $input The string to parse.
     * @return self A new Quantity parsed from the string.
     * @throws FormatException If the string format is invalid.
     * @throws DomainException If the string contains unknown units.
     * @throws UnexpectedValueException
     * @example
     *   Length::parse("123.45 km")  // Length(123.45, 'km')
     *   Angle::parse("90deg")       // Angle(90.0, 'deg')
     *   Time::parse("1.5e3 ms")     // Time(1500.0, 'ms')
     */
    public static function parse(string $input): self
    {
        // Prepare an error message with the original value.
        $qtyType = static::getQuantityType();
        $name = $qtyType === null ? '' : (' ' . $qtyType->name);
        $err = "The provided string '$input' does not represent a valid$name quantity.";

        // Reject empty input.
        $input = trim($input);
        if ($input === '') {
            throw new FormatException($err);
        }

        // Look for <num><unit>. Whitespace between the number and unit is permitted. The unit is optional, as for a
        // dimensionless quantity.
        if (RegexService::isValidQuantity($input, $m)) {
            assert(isset($m[1]));
            return self::create((float)$m[1], $m[2] ?? null);
        }

        // Try to parse the string as multiple parts, as if output from formatParts().
        return self::parseParts($input);
    }

    /**
     * Format a numeric value as a string.
     *
     * Format specifiers:
     *      - 'e': Scientific notation with lowercase 'e'.
     *      - 'E': Scientific notation with uppercase 'E'.
     *      - 'f': Fixed-point notation (locale-aware). [default]
     *      - 'F': Fixed-point notation (non-locale-aware, always uses '.' as decimal separator).
     *      - 'g': Shortest of 'e' or 'f' (lower-case 'e'/locale-aware).
     *      - 'G': Shortest of 'E' or 'f' (upper-case 'E'/locale-aware).
     *      - 'h': Shortest of 'e' or 'F' (lower-case 'e'/non-locale-aware).
     *      - 'H': Shortest of 'E' or 'F' (upper-case 'E'/non-locale-aware).
     * For more information, see https://www.php.net/manual/en/function.sprintf.php
     *
     * The meaning of the precision argument depends on the format specifier.
     *      - For e/E/f/F, precision means the number of digits after the decimal point.
     *      - For g/G/h/H, precision means the number of significant digits.
     *
     * When $precision is null, trailing zeros (and a trailing decimal point) are automatically trimmed.
     * When an explicit precision is given, all digits are preserved.
     *
     * When $ascii is false and scientific notation is used, the exponent is rendered as ×10 with
     * superscript digits (e.g. 1.50×10³) instead of e+3.
     *
     * @param float $value The numeric value to format.
     * @param string $specifier The format specifier.
     * @param ?int $precision Number of digits (null = sprintf default with trailing zeros trimmed).
     * @param bool $ascii If true, use ASCII e notation. If false, use ×10 with superscript exponents.
     * @return string The formatted value string.
     * @throws DomainException If the specifier or precision is invalid.
     */
    public static function formatValue(
        float $value,
        string $specifier = 'f',
        ?int $precision = null,
        bool $ascii = false
    ): string {
        // Validate the specifier.
        if (!in_array(strtolower($specifier), ['e', 'f', 'g', 'h'], true)) {
            throw new DomainException("The specifier must be 'e', 'E', 'f', 'F', 'g', 'G', 'h', or 'H'.");
        }

        // Validate the precision.
        if ($precision !== null && ($precision < 0 || $precision > 17)) {
            throw new DomainException('The precision must be null or an integer between 0 and 17.');
        }

        // Canonicalize -0.0 to 0.0.
        $value = Floats::normalizeZero($value);

        // Format with the desired precision and specifier.
        // If the precision is null, omit it from the format string to use the sprintf default (usually 6).
        $formatString = $precision === null ? "%$specifier" : "%.$precision$specifier";
        $valueStr = sprintf($formatString, $value);

        // If precision is null and there's a decimal point in the string, remove trailing zeros and possibly also the
        // decimal point from the number. If there's an 'E' or 'e' in the string, this only applies to the mantissa.
        if ($precision === null && str_contains($valueStr, '.')) {
            $ePos = stripos($valueStr, 'E');
            $mantissa = $ePos === false ? $valueStr : substr($valueStr, 0, $ePos);
            $exp = $ePos === false ? '' : substr($valueStr, $ePos);
            $valueStr = rtrim(rtrim($mantissa, '0'), '.') . $exp;
        }

        // If $ascii is false and there's an exponent, replace it with the Unicode version.
        if (!$ascii) {
            $ePos = stripos($valueStr, 'E');
            if ($ePos !== false) {
                $exp = (int)substr($valueStr, $ePos + 1);
                $valueStr = substr($valueStr, 0, $ePos) . '×10' . Integers::toSuperscript($exp);
            }
        }

        return $valueStr;
    }

    /**
     * Format the measurement as a string with control over precision and notation.
     *
     * Precision meaning varies by specifier:
     *  - 'f'/'F': Number of decimal places.
     *  - 'e'/'E': Number of decimal places in the mantissa.
     *  - 'g'/'G'/'h'/'H': Number of significant figures.
     * For more information, see https://www.php.net/manual/en/function.sprintf.php
     *
     * When $precision is null, trailing zeros are automatically trimmed. When an explicit precision is
     * given, all digits are preserved.
     *
     * When $ascii is false (default) and scientific notation is used, the exponent is rendered as ×10
     * with superscript digits (e.g. 1.50×10³) instead of e+3.
     *
     * It's usually best to leave $includeSpace as null, which uses common style rules to determine if a
     * space should be placed between the number and the unit. The rule is: if the unit is a single
     * non-letter symbol (e.g. °, %, "), no space is inserted. Otherwise, a space is inserted, including
     * for units that start with a non-letter such as °C.
     *
     * @param string $specifier The format specifier. See formatValue() for the full list.
     * @param ?int $precision Number of digits (null = sprintf default with trailing zeros trimmed).
     * @param ?bool $includeSpace Space between value and unit (null = auto, true = always, false = never).
     * @param bool $ascii If true, use ASCII symbols and e notation. If false, use Unicode symbols and ×10 notation.
     * @return string The formatted measurement string.
     * @throws DomainException If the specifier or precision is invalid.
     */
    public function format(
        string $specifier = 'f',
        ?int $precision = null,
        ?bool $includeSpace = null,
        bool $ascii = false
    ): string {
        // Format the value.
        $valueStr = self::formatValue($this->value, $specifier, $precision, $ascii);

        // Get the unit as a string.
        $unitSymbol = $this->derivedUnit->format($ascii);

        // If the unit is empty, return the value as a string.
        if ($unitSymbol === '') {
            return $valueStr;
        }

        // If $includeSpace is not specified, do not insert a space between the value and unit if the unit is a single
        // non-letter unit symbol (e.g. °, %, "). Otherwise, insert one space.
        if ($includeSpace === null) {
            $includeSpace = !RegexService::isValidUnicodeSpecialChar($unitSymbol);
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

    // region Parts-related methods

    /**
     * Get the part unit symbols for this quantity type.
     *
     * Subclasses can override this to provide hardcoded defaults.
     *
     * @return ?list<string> The part unit symbols, or null if none configured.
     * @throws DomainException If the quantity type is unregistered.
     */
    public static function getPartUnitSymbols(): ?array
    {
        return QuantityPartsService::getPartUnitSymbols(static::getQuantityType());
    }

    /**
     * Set the part unit symbols for this quantity type.
     *
     * @param ?list<string> $partUnitSymbols The part unit symbols, or null to clear.
     * @throws DomainException If the quantity type is unregistered or the array is empty.
     * @throws InvalidArgumentException If the array contains non-string values.
     */
    public static function setPartUnitSymbols(?array $partUnitSymbols): void
    {
        QuantityPartsService::setPartUnitSymbols(static::getQuantityType(), $partUnitSymbols);
    }

    /**
     * Get the result unit symbol for this quantity type.
     *
     * Subclasses can override this to provide hardcoded defaults.
     *
     * @return ?string The result unit symbol, or null if none configured.
     * @throws DomainException If the quantity type is unregistered.
     */
    public static function getResultUnitSymbol(): ?string
    {
        return QuantityPartsService::getResultUnitSymbol(static::getQuantityType());
    }

    /**
     * Set the result unit symbol for this quantity type.
     *
     * @param ?string $resultUnitSymbol The result unit symbol, or null to clear.
     * @throws DomainException If the quantity type is unregistered or the value is an empty string.
     */
    public static function setResultUnitSymbol(?string $resultUnitSymbol): void
    {
        QuantityPartsService::setResultUnitSymbol(static::getQuantityType(), $resultUnitSymbol);
    }

    /**
     * Create a new Quantity object as a sum of measurements of different units.
     *
     * @param array<string, int|float> $parts The parts.
     * @return Quantity A new Quantity representing the sum of the parts.
     * @throws InvalidArgumentException If any of the unit symbols are not strings, or any of the values are not
     * numbers.
     * @throws DomainException If the quantity type is unregistered, or the result unit symbol or sign is invalid.
     * @see QuantityPartsService::fromParts()
     */
    public static function fromParts(array $parts): Quantity
    {
        return QuantityPartsService::fromParts(static::getQuantityType(), $parts);
    }

    /**
     * Convert the quantity to parts.
     *
     * @param ?int $precision The number of decimal places for rounding the smallest unit, or null for no rounding.
     * @return array<string, int|float> Array of parts, plus the sign (1 or -1).
     * @throws DomainException If the quantity type is unregistered, precision is negative, or part unit symbols are
     * invalid.
     * @throws InvalidArgumentException If any of the part unit symbols are not strings.
     * @see QuantityPartsService::toParts()
     */
    public function toParts(?int $precision = null): array
    {
        return QuantityPartsService::toParts($this, $precision);
    }

    /**
     * Parse a string of quantity parts.
     *
     * @param string $input The string to parse.
     * @return Quantity A new Quantity representing the sum of the parts.
     * @throws FormatException If the input string is invalid.
     * @throws UnexpectedValueException If there is an unexpected error during parsing.
     * @throws DomainException If the quantity type is unregistered or the result unit symbol is invalid.
     * @throws InvalidArgumentException If any of the part unit symbols are not strings.
     * @see QuantityPartsService::parseParts()
     */
    public static function parseParts(string $input): Quantity
    {
        return QuantityPartsService::parseParts(static::getQuantityType(), $input);
    }

    /**
     * Format quantity as parts.
     *
     * @param ?int $precision The number of decimal places for rounding the smallest unit, or null for no rounding.
     * @param bool $showZeros If true, show all parts including zeros; if false, skip zero-value components.
     * @param bool $ascii If true, use ASCII characters only.
     * @return string The formatted string.
     * @throws DomainException If the quantity type is unregistered.
     * @throws InvalidArgumentException If any of the part unit symbols are not strings.
     * @see QuantityPartsService::formatParts()
     */
    public function formatParts(?int $precision = null, bool $showZeros = false, bool $ascii = false): string
    {
        return QuantityPartsService::formatParts($this, $precision, $showZeros, $ascii);
    }

    // endregion

    // region Helper methods

    /**
     * Check the $this and $other objects have the same type and get the value of the $other Quantity in the same
     * unit as the $this one. Return the value.
     *
     * @param mixed $other The other measurement to compare with.
     * @return float The value of the other measurement in the same unit as this one.
     * @throws LogicException If no conversion path exists between the units.
     * @throws IncomparableTypesException If the other value is not a Quantity.
     * @throws DomainException If the two Quantities have different dimensions.
     */
    protected function preCompare(mixed $other): float
    {
        // Check the two values are both Quantity objects.
        if (!$other instanceof self) {
            throw new IncomparableTypesException($this, $other);
        }

        // Check the two Quantities have the same dimension.
        $dim1 = $this->derivedUnit->dimension;
        $dim2 = $other->derivedUnit->dimension;
        if ($dim1 !== $dim2) {
            throw new DomainException("Cannot compare quantities with different dimensions, got '$dim1' and '$dim2'.");
        }

        // Get the other Quantity in the same unit as this one.
        return $this->derivedUnit->equal($other->derivedUnit) ? $other->value : $other->to($this->derivedUnit)->value;
    }

    // endregion
}
