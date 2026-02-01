<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use DivisionByZeroError;
use DomainException;
use Galaxon\Core\Arrays;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Core\Exceptions\IncomparableTypesException;
use Galaxon\Core\Floats;
use Galaxon\Core\Numbers;
use Galaxon\Core\Traits\ApproxComparable;
use Galaxon\Quantities\Registry\DimensionRegistry;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\Registry\QuantityTypeRegistry;
use Galaxon\Quantities\Registry\UnitRegistry;
use InvalidArgumentException;
use LogicException;
use Override;
use Stringable;

/**
 * Abstract base class for physical measurements with units.
 *
 * Provides a framework for creating strongly-typed measurement classes (Length, Mass, Time, etc.)
 * with automatic unit conversion, arithmetic operations, and comparison capabilities.
 *
 * Derived classes may optionally override:
 * - getUnitDefinitions(): Define the base and expandable units, and the prefixes they accept.
 * - getConversionDefinitions(): Define conversions between units.
 *
 * Prefix system:
 * - Units can specify allowed prefixes using bitwise flags (GROUP_CODE_METRIC, GROUP_CODE_BINARY, etc.)
 * - Provides fine-grained control (e.g. radian can accept only small metric prefixes)
 * - Supports combinations (e.g. byte can accept both metric and binary prefixes)
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
    public readonly DerivedUnit $derivedUnit;

    // endregion

    // region Property hooks

    /**
     * The dimension.
     *
     * @var string
     */
    public string $dimension {
        get => $this->derivedUnit->dimension;
    }

    /**
     * The quantity type.
     *
     * @var ?QuantityType
     */
    public ?QuantityType $type {
        get => QuantityTypeRegistry::getByDimension($this->dimension);
    }

    // endregion

    // region Constructor

    /**
     * Constructor.
     *
     * Creates a new measurement with the specified value and unit.
     * Validates that the value is finite and the unit is valid.
     * Furthermore, ensures the calling class matches the unit dimension.
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
        // Check the value is finite.
        if (!is_finite($value)) {
            throw new DomainException('Quantity value cannot be ±INF or NAN.');
        }

        // Convert unit into an object if it isn't already.
        // This will throw if the unit is provided as a string that doesn't represent a valid DerivedUnit.
        $derivedUnit = DerivedUnit::toDerivedUnit($unit);

        // Check they are calling the correct constructor.
        $qtyClass = self::class;
        $callingClass = static::class;
        $qtyType = QuantityTypeRegistry::getByDimension($derivedUnit->dimension);
        $qtyTypeClass = $qtyType?->class;
        if ($qtyTypeClass !== null && $callingClass !== $qtyTypeClass) {
            throw new LogicException(
                "Cannot instantiate a $qtyType->name quantity by calling `new $callingClass()`. Instead, " .
                " call `new $qtyType->class()`. Otherwise, call `$qtyClass::create()`, which will automatically " .
                "create an object of the correct class."
            );
        }

        // Set the properties.
        $this->value = Floats::normalizeZero($value);
        $this->derivedUnit = $derivedUnit;
    }

    // endregion

    // region Static public methods

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
    public static function create(float $value, null|string|UnitInterface $unit): self
    {
        // Check the value is finite.
        if (!is_finite($value)) {
            throw new DomainException('Value cannot be ±INF or NAN.');
        }

        // Get unit as DerivedUnit.
        $unit = DerivedUnit::toDerivedUnit($unit);

        // If there's a registered subclass for this dimension code, create an object of that class.
        $qtyType = QuantityTypeRegistry::getByDimension($unit->dimension);
        if ($qtyType?->class !== null) {
            return new ($qtyType->class)($value, $unit);
        }

        // Fall back to a generic Quantity object.
        return new self($value, $unit);
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
     *     systems: list<System>,
     *     expansionUnitSymbol?: string,
     *     expansionValue?: float
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
        $srcUnit = DerivedUnit::toDerivedUnit($srcUnit);
        $converter = Converter::getByDimension($srcUnit->dimension);
        return $converter->convert($value, $srcUnit, $destUnit);
    }

    // endregion

    // region Transformation methods

    /**
     * Convert this Quantity to a different unit.
     *
     * Returns a new Quantity object with the equivalent value in the destination unit.
     *
     * @param string|UnitInterface $destUnit The destination unit to convert to.
     * @return self A new Quantity in the specified unit.
     * @throws DomainException If the destination unit is invalid.
     * @throws LogicException If no conversion path exists between the units.
     *
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
     * If $compact is true, base units will be replaced by expandable units where possible, e.g. kg*m/s2 => N
     * The expandable unit that replaces the largest number of base units will be chosen.
     *
     * If $autoPrefix is true, the result will be converted to use the best prefix, defined as:
     * 1. Is valid for the first unit term.
     * 2. Represents a multiple of 1000 or 1/1000. This is often called an engineering prefix.
     * 3. Produces the smallest value greater than or equal to 1.
     *
     * @param bool $compact If true, base units will be replaced by expandable units where possible.
     * @param bool $autoPrefix If true, the result will be converted to the best SI prefix.
     * @return self A new Quantity with the value converted to SI units.
     * @throws DomainException If any dimension codes are invalid or conversion fails.
     * @throws LogicException If any dimension codes do not have an SI base unit defined.
     */
    public function toSi(bool $compact = false, bool $autoPrefix = false): self
    {
        // Expand and merge to get the base units.
        $result = $this->expand()->merge();

        // Convert to SI base units.
        $result = $this->to($result->derivedUnit->toSi());

        // Substitute expandable units if requested.
        if ($compact) {
            $result = $result->compact();
        }

        return $autoPrefix ? $result->autoPrefix() : $result;
    }

    /**
     * Create a new Quantity with the same unit but a different value.
     *
     * @param float $value The new numeric value.
     * @return self A new Quantity with the given value in the same unit.
     */
    public function withValue(float $value): self
    {
        return self::create($value, $this->derivedUnit);
    }

    /**
     * Substitute expandable units for base units, e.g. N => kg*m/s2
     *
     * @return self
     */
    public function expand(): self
    {
        // Check if there's anything to do.
        if (!$this->derivedUnit->hasExpansion()) {
            return $this;
        }

        // Start building new Quantity.
        $newValue = $this->value;
        $newUnit = new DerivedUnit();

        // Expand any units with expansions.
        foreach ($this->derivedUnit->unitTerms as $unitTerm) {
            $expansionUnit = $unitTerm->unit->expansionUnit;
            $factor = 1;

            if ($expansionUnit === null) {
                // Look for an indirect expansion.
                $converter = Converter::getByDimension($unitTerm->dimension);

                foreach ($converter->units as $converterUnit) {
                    // We're just looking for an expandable unit by itself.
                    if (count($converterUnit->unitTerms) !== 1) {
                        continue;
                    }

                    // Get the first unit term.
                    /** @var UnitTerm $converterUnitTerm */
                    $converterUnitTerm = $converterUnit->firstUnitTerm;

                    // See if it's useful for this purpose.
                    if (
                        $converterUnitTerm->exponent === 1 &&
                        $converterUnitTerm->prefix === null &&
                        $converterUnitTerm->unit->expansionUnit !== null
                    ) {
                        $factor = $converter->getConversionFactor($unitTerm, $converterUnitTerm);
                        $expansionUnit = $converterUnitTerm->unit->expansionUnit;
                        break;
                    }
                }
            } else {
                $factor = $unitTerm->unit->expansionValue;
            }

            if ($expansionUnit !== null) {
                // Multiply by the conversion factor modified by prefix and exponent.
                $newValue *= ($factor * $unitTerm->prefixMultiplier) ** $unitTerm->exponent;

                // Add the unit terms from the expansion Quantity.
                foreach ($expansionUnit->unitTerms as $expansionUnitTerm) {
                    // Raise the expansion unit term to the exponent and add.
                    $newUnit->addUnitTerm($expansionUnitTerm->pow($unitTerm->exponent));
                }
            } else {
                $newUnit->addUnitTerm($unitTerm);
            }
        }

        return self::create($newValue, $newUnit)->merge();
    }

    /**
     * Substitute base units for expandable units, e.g. kg*m/s2 => N
     *
     * The expandable unit that replaces the largest number of base units will be chosen.
     *
     * 's-1' will not be replaced by 'Hz' unless it's the only unit term.
     * Furthermore, 's-1' is not replaced by 'Bq'. You can call $q->to('Bq') to get that effect.
     *
     * @return self
     */
    public function compact(): self
    {
        // Merge compatible units.
        $qty = $this->merge();

        // Handle Hertz separately. We only want to swap 's-1' for 'Hz' if it's the only unit term.
        if (count($qty->derivedUnit->unitTerms) === 1) {
            $unitTerm = $qty->derivedUnit->firstUnitTerm;
            if ($unitTerm !== null && $unitTerm->unit->asciiSymbol === 's' && $unitTerm->exponent === -1) {
                $newUnitTerm = new UnitTerm('Hz', PrefixRegistry::invert($unitTerm->prefix));
                return self::create($qty->value, $newUnitTerm);
            }
        }

        // Start constructing result.
        $newValue = $qty->value;
        $newUnit = clone $qty->derivedUnit;

        // Get all expandable units.
        $expandableUnits = UnitRegistry::getExpandable();

        // Track best match.
        $bestExpandableUnit = null;
        $bestMatchScore = 0;
        $bestUnitToReplace = null;

        foreach ($expandableUnits as $expandableUnit) {
            /** @var DerivedUnit $expansionUnit */
            $expansionUnit = $expandableUnit->expansionUnit;

            // Skip 'Hz' or 'Bq'; these are the only expandable units with one unit term in their expansion.
            if (count($expansionUnit->unitTerms) === 1) {
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
                $bestExpandableUnit = $expandableUnit;
                $bestMatchScore = $matchScore;
                $bestUnitToReplace = $unitToReplace;
            }
        }

        // If we found a match, substitute the necessary unit terms for the expandable unit.
        if ($bestExpandableUnit !== null && $bestUnitToReplace !== null) {
            // Remove the unit terms to replace.
            foreach ($bestUnitToReplace->unitTerms as $unitTermToReplace) {
                $newUnit->removeUnitTerm($unitTermToReplace);
            }

            // Add the expandable unit.
            $newUnit->addUnitTerm(new UnitTerm($bestExpandableUnit));

            // Multiply by the conversion factor.
            $newValue *= static::convert(1, $bestUnitToReplace, $bestExpandableUnit);
        }

        // Construct the result.
        return self::create($newValue, $newUnit);
    }

    /**
     * Merge units that have the same dimension, e.g. 'm' and 'ft', or 's' and 'h', or 'lb' and 'kg'.
     *
     * The first unit encountered of a given dimension will be one any others are converted to.
     *
     * @return self A new Quantity with compatible units merged.
     */
    public function merge(): self
    {
        // Check if there's anything to do.
        if (!$this->derivedUnit->hasMergeableUnits()) {
            return $this;
        }

        // Initialize the result components.
        $newValue = $this->value;
        $newUnit = new DerivedUnit();

        foreach ($this->derivedUnit->unitTerms as $thisUnitTerm) {
            // See if there is already a unit term with a unit with this dimension.
            $newUnitTerm1 = array_find(
                $newUnit->unitTerms,
                static fn (UnitTerm $ut) => $ut->unit->dimension === $thisUnitTerm->unit->dimension
            );

            // If no unit exists with this dimension, copy the existing one to the result.
            if ($newUnitTerm1 === null) {
                $newUnit->addUnitTerm($thisUnitTerm);
            } else {
                // If the unexponentiated units are different, convert one to the other.
                $unexponentiatedThisUnitTerm = $thisUnitTerm->removeExponent();
                $unexponentiatedNewUnitTerm1 = $newUnitTerm1->removeExponent();
                if (!$unexponentiatedThisUnitTerm->equal($unexponentiatedNewUnitTerm1)) {
                    // Convert the second unit term to the same unit as the first.
                    $factor = static::convert(1, $unexponentiatedThisUnitTerm, $unexponentiatedNewUnitTerm1);

                    // Multiply by the conversion factor raised to the exponent of the second unit term.
                    $newValue *= $factor ** $thisUnitTerm->exponent;
                }

                // Create a second term with the same unit as the first, but the exponent of the second term.
                $newUnitTerm2 = $newUnitTerm1->withExponent($thisUnitTerm->exponent);

                // Adding the second unit term will combine it with the first, because they have the same
                // unexponentiated symbol.
                $newUnit->addUnitTerm($newUnitTerm2);
            }
        }

        return self::create($newValue, $newUnit);
    }

    /**
     * Find the best SI prefix and modify the Quantity accordingly.
     *
     * @return self
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
        /** @var UnitTerm $firstUnitTerm */
        $firstUnitTerm = $newDerivedUnit->firstUnitTerm;

        // Choose the prefix that produces the smallest value greater than or equal to 1.
        // Start with the current situation, which is no prefix.
        $absValue = abs($newValue);
        $sign = Numbers::sign($newValue);
        $bestPrefix = null;
        $bestValue = $absValue;

        // Try each allowed prefix to see if it's better. We want the prefix that produces the smallest value greater
        // than or equal to 1.
        foreach ($firstUnitTerm->unit->allowedPrefixes as $prefix) {
            // We only want to consider engineering prefixes for this. The others (c, d, da, h) are rarely used for most
            // units. We also don't want binary prefixes (e.g. 'kB' is usually preferred to 'KiB').
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

        // If we found a better prefix than none at all, apply it, if it's different.
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
     * @throws LogicException If no conversion path exists between the units.
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
        } catch (InvalidArgumentException) {
            // If the other Quantity has a different type or dimension, it's not equal to this one.
            return false;
        }

        // Now we have the other Quantity in the same unit, compare the values.
        return Floats::approxEqual($this->value, $otherValue, $relTol, $absTol);
    }

    // endregion

    // region Arithmetic methods

    /**
     * Get the absolute value of this measurement.
     *
     * @return self A new Quantity with non-negative value in the same unit.
     *
     * @example
     *   $temp = new Temperature(-10, 'C');
     *   $abs = $temp->abs();  // Temperature(10, 'C')
     */
    public function abs(): self
    {
        return self::create(abs($this->value), $this->derivedUnit);
    }

    /**
     * Negate a measurement.
     *
     * @return self A new Quantity containing the negative of this measurement's unit.
     */
    public function neg(): self
    {
        return self::create(-$this->value, $this->derivedUnit);
    }

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
     * @param null|string|UnitInterface $otherUnit The other quantity's unit, if a numeric value was provided.
     * @return self A new Quantity containing the sum in this measurement's unit.
     * @throws DomainException If value is non-finite or unit is invalid.
     * @throws LogicException If no conversion path exists between units.
     *
     * @example
     *   $a = new Length(100, 'm');
     *   $b = new Length(2, 'km');
     *   $sum = $a->add($b);           // Length(2100, 'm')
     *   $sum2 = $a->add(50, 'cm');    // Length(100.5, 'm')
     */
    public function add(self|float $otherOrValue, null|string|UnitInterface $otherUnit = null): self
    {
        // Get the other quantity as an object.
        $other = is_float($otherOrValue) ? self::create($otherOrValue, $otherUnit) : $otherOrValue;

        // Get the other Quantity in the same unit as this one.
        $otherValue = $this->derivedUnit->equal($other->derivedUnit)
            ? $other->value
            : $other->to($this->derivedUnit)->value;

        // Add the two values.
        return $this->withValue($this->value + $otherValue);
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
     * @param null|string|UnitInterface $otherUnit The other quantity's unit, if a numeric value was provided.
     * @return self A new Quantity containing the difference in this measurement's unit.
     * @throws DomainException If value is non-finite or unit is invalid.
     * @throws LogicException If no conversion path exists between units.
     *
     * @example
     *   $a = new Length(100, 'm');
     *   $b = new Length(2, 'km');
     *   $diff = $a->sub($b);  // Length(-1900, 'm')
     */
    public function sub(self|float $otherOrValue, null|string|UnitInterface $otherUnit = null): self
    {
        // Get the other quantity as an object.
        $other = is_float($otherOrValue) ? self::create($otherOrValue, $otherUnit) : $otherOrValue;

        // Get the other Quantity in the same unit as this one.
        $otherValue = $this->derivedUnit->equal($other->derivedUnit)
            ? $other->value
            : $other->to($this->derivedUnit)->value;

        // Subtract the values.
        return $this->withValue($this->value - $otherValue);
    }

    /**
     * Invert this quantity (1/x).
     *
     * @return self A new Quantity with inverted value and unit.
     * @throws DivisionByZeroError If the value is zero.
     */
    public function inv(): self
    {
        // Guards.
        if ($this->value === 0.0) {
            throw new DivisionByZeroError('Cannot invert a quantity with a value of 0.');
        }

        return self::create(1.0 / $this->value, $this->derivedUnit->inv());
    }

    /**
     * Multiply this measurement by a scalar factor or another Quantity.
     *
     * Note, this operation does no conversions. If you multiply a quantity in metres by one in feet, you will get a
     * quantity in m*ft, not m2.
     *
     * There are several ways to avoid this effect:
     * 1. Convert both operands to SI, e.g. $a->toSi()->mul($b->toSi())
     * 2. Convert the result to SI, e.g. $a->mul($b)->toSi(). Same result as above, just a different path.
     * 3. Simplify the result. e.g. $a->mul($b)->simplify().
     *
     * @param self|float $otherOrValue Another Quantity or a numeric value.
     * @param null|string|UnitInterface $otherUnit The other quantity's unit, if a numeric value was provided.
     * @return self A new Quantity representing the result of the multiplication.
     * @throws DomainException If the multiplier is a non-finite float (±INF or NAN).
     *
     * @example
     *   $length = new Length(10, 'm');
     *   $doubled = $length->mul(2);  // Length(20, 'm')
     */
    public function mul(float|self $otherOrValue, null|string|UnitInterface $otherUnit = null): self
    {
        // Check for simple multiplication by a scalar.
        if (is_float($otherOrValue) && $otherUnit === null) {
            return $this->withValue($this->value * $otherOrValue);
        }

        // Get the other quantity as an object.
        $other = is_float($otherOrValue) ? self::create($otherOrValue, $otherUnit) : $otherOrValue;

        // Start by multiplying the values.
        $newValue = $this->value * $other->value;

        // Create a new unit from this unit.
        $newUnit = clone $this->derivedUnit;

        // Add each unit term from the other Quantity.
        foreach ($other->derivedUnit->unitTerms as $otherUnitTerm) {
            $newUnit->addUnitTerm($otherUnitTerm);
        }

        // Create the result Quantity and merge units.
        return self::create($newValue, $newUnit)->merge();
    }

    /**
     * Divide this measurement by a scalar factor.
     *
     * @param float|self $otherOrValue The scalar or Quantity to divide by.
     * @param null|string|UnitInterface $otherUnit The other quantity's unit, if a numeric value was provided.
     * @return self A new Quantity representing the result of the division.
     * @throws DivisionByZeroError If the divisor is zero.
     * @throws DomainException If the divisor is non-finite (±INF or NAN).
     *
     * @example
     *   $length = new Length(10, 'm');
     *   $half = $length->div(2);  // Length(5, 'm')
     */
    public function div(float|self $otherOrValue, null|string|UnitInterface $otherUnit = null): self
    {
        // Check for simple division by a scalar.
        if (is_float($otherOrValue) && $otherUnit === null) {
            return $this->withValue($this->value / $otherOrValue);
        }

        // Get the other quantity as an object.
        $other = is_float($otherOrValue) ? self::create($otherOrValue, $otherUnit) : $otherOrValue;

        // Multiply by the inverse.
        return $this->mul($other->inv());
    }

    /**
     * Raise the Quantity to an exponent.
     *
     * @param int $exponent
     * @return self
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
     * @param string $value The string to parse.
     * @return self A new Quantity parsed from the string.
     * @throws FormatException If the string format is invalid.
     * @throws DomainException If the string contains unknown units.
     *
     * @example
     *   Length::parse("123.45 km")  // Length(123.45, 'km')
     *   Angle::parse("90deg")       // Angle(90.0, 'deg')
     *   Time::parse("1.5e3 ms")     // Time(1500.0, 'ms')
     */
    public static function parse(string $value): self
    {
        // Prepare an error message with the original value.
        $qtyType = QuantityTypeRegistry::getByClass(static::class);
        $name = $qtyType === null ? '' : (' ' . strtolower($qtyType->name));
        $err = "The provided string '$value' does not represent a valid$name quantity.";

        // Reject empty input.
        $value = trim($value);
        if ($value === '') {
            throw new FormatException($err);
        }

        // Look for <num><unit>. Whitespace between the number and unit is permitted. Unit is optional, for a
        // dimensionless quantity.
        $rxNum = Numbers::REGEX;
        $rxDerivedUnit = DerivedUnit::regex();
        if (preg_match("/^($rxNum)\s*($rxDerivedUnit)?$/iu", $value, $m)) {
            return self::create((float)$m[1], $m[2] ?? null);
        }

        // Invalid format.
        throw new FormatException($err);
    }

    /**
     * @param float $value
     * @param string $specifier
     * @param int|null $precision
     * @param bool $trimZeros
     * @return string
     * @throws DomainException
     */
    private static function formatValue(
        float $value,
        string $specifier = 'f',
        ?int $precision = null,
        bool $trimZeros = true
    ): string {
        // Validate the specifier.
        if (!in_array($specifier, ['e', 'E', 'f', 'F', 'g', 'G'], true)) {
            throw new DomainException("The specifier must be 'e', 'E', 'f', 'F', 'g', or 'G'.");
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

        // If $trimZeros is true and there's a decimal point in the string, remove trailing zeros and decimal point from
        // the number. If there's an 'E' or 'e' in the string, this only applies to the mantissa.
        if ($trimZeros && str_contains($valueStr, '.')) {
            $ePos = stripos($valueStr, 'E');
            $mantissa = $ePos === false ? $valueStr : substr($valueStr, 0, $ePos);
            $exp = $ePos === false ? '' : substr($valueStr, $ePos);
            $valueStr = rtrim(rtrim($mantissa, '0'), '.') . $exp;
        }

        return $valueStr;
    }

    /**
     * Format the measurement as a string with control over precision and notation.
     *
     * Precision meaning varies by specifier:
     *  - 'f'/'F': Number of decimal places
     *  - 'e'/'E': Number of mantissa digits
     *  - 'g'/'G': Number of significant figures
     *
     * It's usually best to leave $includeSpace with its default value of null, which uses common style rules to
     * determine if a space should be placed between the number and the unit.
     * The rule is: if it's only a single non-letter (e.g. °, %, "), no space will be inserted.
     * Otherwise, one will be inserted, including for units that start with a non-letter such as °C.
     * If you want to force the use or non-use of a space, set $includeSpace to true or false.
     *
     * @param string $specifier Format type: 'f'/'F' (fixed), 'e'/'E' (scientific), 'g'/'G' (shortest).
     * @param ?int $precision Number of digits (meaning depends on specifier).
     * @param bool $trimZeros If true, remove trailing zeros and decimal point.
     * @param ?bool $includeSpace If true, insert space between value and unit.
     * @param bool $ascii If true, use ASCII characters only.
     * @return string The formatted measurement string.
     * @throws DomainException If specifier or precision are invalid.
     *
     * @example
     *   $angle->format(true, 'f', 2)       // "90.00 deg"
     *   $angle->format(false, 'f', 2)      // "90.00°"
     *   $angle->format(true, 'e', 3)       // "1.571e+0 rad"
     *   $angle->format(true, 'f', 0, true, false)  // "90deg"
     */
    public function format(
        string $specifier = 'f',
        ?int $precision = null,
        bool $trimZeros = true,
        ?bool $includeSpace = null,
        bool $ascii = false
    ): string {
        // Format the value.
        $valueStr = self::formatValue($this->value, $specifier, $precision, $trimZeros);

        // Get the unit as a string.
        $unitSymbol = $this->derivedUnit->format($ascii);

        // If $includeSpace is not specified, do not insert a space between the value and unit if the unit is a single
        // non-letter unit symbol (e.g. °, %, "). Otherwise, insert one space.
        if ($includeSpace === null) {
            $includeSpace = !Unit::isValidNonLetterSymbol($unitSymbol);
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

    // region Part-related methods

    /**
     * Get configuration for part-related methods.
     *
     * This method should be overridden in derived classes that want parts-related functionality.
     *
     * @return array{from: ?string, to: list<string>} Configuration with:
     *   - 'from': Default result unit symbol for fromParts(), or null if no default.
     *   - 'to': Ordered list of unit symbols from largest to smallest for toParts()/formatParts().
     */
    public static function getPartsConfig(): array
    {
        return [
            'from' => null,
            'to'   => [],
        ];
    }

    /**
     * Check precision argument is valid.
     *
     * @param ?int $precision The precision to validate.
     * @return void
     * @throws DomainException If precision is negative.
     */
    protected static function validatePrecision(?int $precision): void
    {
        if ($precision !== null && $precision < 0) {
            throw new DomainException(
                "Invalid precision specified; $precision. Must be null or a non-negative integer."
            );
        }
    }

    /**
     * Check the given unit symbol is valid for the calling class.
     *
     * @param string $unitSymbol The unit symbol to validate.
     * @return Unit The validated unit as a Unit object.
     * @throws LogicException If the calling class is not a registered quantity type.
     * @throws DomainException If the symbol is unknown or invalid.
     */
    protected static function validateUnitSymbol(string $unitSymbol): Unit
    {
        // Get the dimension.
        $qtyType = QuantityTypeRegistry::getByClass(static::class);
        if ($qtyType === null) {
            throw new LogicException(
                "Quantity type not found for class " . static::class . ". Ensure the class is registered " .
                "using `QuantityTypeRegistry::add()` or `QuantityTypeRegistry::setClass()`.");
        }

        // Get the unit.
        $unit = UnitRegistry::getBySymbol($unitSymbol);
        if ($unit === null) {
            throw new DomainException("Unknown unit symbol: '$unitSymbol'.");
        }

        // Compare dimensions.
        if ($qtyType->dimension !== $unit->dimension) {
            throw new DomainException("Unit '$unitSymbol' is invalid for $qtyType->name quantities.");
        }

        return $unit;
    }

    /**
     * Validate and transform the 'to' part units array into a list of Units.
     *
     * @return list<Unit> The part units.
     * @throws LogicException If the 'to' array is empty or contains invalid symbols.
     */
    protected static function validatePartUnitSymbols(): array
    {
        // Get the parts config.
        $config = static::getPartsConfig();
        $partUnitSymbols = $config['to'];

        // Ensure we have some part units.
        if (empty($partUnitSymbols)) {
            throw new LogicException(
                "The derived Quantity class must define the 'to' part units by overriding `getPartsConfig()`, " .
                "which must return an array with a non-empty 'to' list of valid unit symbols."
            );
        }

        // Create a new array to contain the list of Unit objects.
        $partUnits = [];

        // Validate each part unit symbol.
        foreach ($partUnitSymbols as $partUnitSymbol) {
            $partUnits[] = static::validateUnitSymbol($partUnitSymbol);
        }

        return $partUnits;
    }

    /**
     * Validate that a unit symbol is valid for this quantity type and is in the 'to' list of part units.
     *
     * @param string $smallestUnitSymbol The unit symbol to validate.
     * @return Unit The validated unit as a Unit object.
     * @throws LogicException If the calling class is not a registered quantity type.
     * @throws DomainException If the symbol is unknown, invalid for this quantity type, or not in the 'to' list.
     */
    protected static function validateSmallestUnitSymbol(string $smallestUnitSymbol): Unit
    {
        $smallestUnit = static::validateUnitSymbol($smallestUnitSymbol);
        $partUnitSymbols = static::getPartsConfig()['to'];
        if (!in_array($smallestUnitSymbol, $partUnitSymbols, true)) {
            throw new DomainException("Smallest unit symbol '$smallestUnitSymbol' is not a valid part unit.");
        }
        return $smallestUnit;
    }

    /**
     * Create a new Quantity object (of the derived type) as a sum of measurements of the same type in different
     * units.
     *
     * All parts must be non-negative.
     *
     * @param array<string, int|float> $parts The parts. This may include an optional 'sign' key to indicate the sign of
     * the sum, which can be 1 (non-negative) or -1 (negative). If omitted, the sign is assumed to be 1.
     * @param ?string $resultUnitSymbol The unit to use for the resulting quantity, or null to use the class default.
     * @return self A new Quantity representing the sum of the parts.
     * @throws InvalidArgumentException If any of the values are not numbers, or if no result unit is specified and
     * the class has no default.
     * @throws DomainException If any of the values are non-finite or negative, or if any unit is invalid.
     * @throws LogicException If the calling class is not a registered quantity type.
     */
    public static function fromParts(array $parts, ?string $resultUnitSymbol = null): self
    {
        // Use class default if not specified.
        if ($resultUnitSymbol === null) {
            $resultUnitSymbol = static::getPartsConfig()['from'];
            if ($resultUnitSymbol === null) {
                throw new InvalidArgumentException(
                    'Result unit symbol is required; no default is defined for ' . static::class . '.'
                );
            }
        }

        // Check the provided result unit is valid for this quantity type.
        $resultUnit = static::validateUnitSymbol($resultUnitSymbol);

        // Initialize the Quantity to 0, with the unit set to the result unit.
        /** @var self $qty */
        $qty = new (static::class)(0, $resultUnit);

        // Add each of the possible units.
        foreach ($parts as $partUnitSymbol => $partValue) {
            // Skip sign.
            if ($partUnitSymbol === 'sign') {
                continue;
            }

            // Add the part. It will be converted to the result unit automatically.
            // If the value or unit is invalid, this will throw an exception.
            $qty = $qty->add($partValue, $partUnitSymbol);
        }

        // Validate the sign.
        if (isset($parts['sign'])) {
            $sign = $parts['sign'];
            if ($sign !== -1 && $sign !== 1) {
                throw new DomainException("Invalid sign: $sign. Must be -1 or 1.");
            }

            // Make negative if necessary.
            if ($sign === -1) {
                $qty = $qty->neg();
            }
        }

        return $qty;
    }

    /**
     * Convert Quantity to component parts.
     *
     * Returns an array with components from the largest to the smallest unit.
     * A sign part is also included with a value of 1 for positive or zero, or -1 for negative.
     * All the unit values will be floats; the sign will be an integer.
     * Only the last component may have a fractional part; the others will not.
     *
     * @param ?string $smallestUnitSymbol The smallest unit to include in the result.
     * @param ?int $precision The number of decimal places for rounding the smallest unit, or null for no rounding.
     * @return array<string, int|float> Array of parts, plus the optional sign (1 or -1).
     * @throws DomainException If any arguments are invalid.
     * @throws LogicException If getPartsConfig() has not been overridden properly.
     */
    public function toParts(?string $smallestUnitSymbol = null, ?int $precision = null): array
    {
        // Get and validate the part units.
        $partUnits = static::validatePartUnitSymbols();
        $partUnitSymbols = static::getPartsConfig()['to'];

        // Default the smallest unit to the last part unit if not specified.
        if ($smallestUnitSymbol === null) {
            $smallestUnitSymbol = Arrays::last($partUnitSymbols);
            $smallestUnit = Arrays::last($partUnits);
        }
        else {
            // Validate the provided smallest unit.
            $smallestUnit = static::validateSmallestUnitSymbol($smallestUnitSymbol);
        }

        // Validate precision.
        static::validatePrecision($precision);

        // Prep.
        $parts = [
            'sign' => Numbers::sign($this->value, false),
        ];
        $smallestUnitIndex = (int)array_search($smallestUnitSymbol, $partUnitSymbols, true);

        // Initialize the remainder to the source value converted to the smallest unit.
        $rem = abs($this->to($smallestUnit)->value);

        // Get the integer parts.
        foreach ($partUnitSymbols as $partUnitSymbol) {
            // If we've reached the smallest unit, stop.
            if ($partUnitSymbol === $smallestUnitSymbol) {
                break;
            }

            // Get the number of current units in the smallest unit.
            $factor = static::convert(1, $partUnitSymbol, $smallestUnit);
            $wholeNumCurUnit = floor($rem / $factor);
            $parts[$partUnitSymbol] = $wholeNumCurUnit;
            $rem = $rem - $wholeNumCurUnit * $factor;
        }

        // If the precision is specified, round off the smallest unit.
        $parts[$smallestUnitSymbol] = $precision === null ? $rem : round($rem, $precision);

        // Carry in reverse order.
        if ($precision !== null) {
            for ($i = $smallestUnitIndex; $i >= 1; $i--) {
                $curUnit = $partUnitSymbols[$i];
                $prevUnit = $partUnitSymbols[$i - 1];
                $conversionFactor = static::convert(1, $prevUnit, $curUnit);
                if ($parts[$curUnit] >= $conversionFactor) {
                    $parts[$curUnit] -= $conversionFactor;
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
     * @param ?string $smallestUnitSymbol The smallest unit to include (null for default).
     * @param ?int $precision The number of decimal places for rounding the smallest unit, or null for no rounding.
     * @param bool $showZeros If true, show all components (largest to smallest) including zeros; if false, skip
     * zero-value components.
     * @param bool $ascii If true, use ASCII characters only.
     * @return string The formatted string.
     * @throws DomainException If any arguments are invalid.
     * @throws LogicException If the 'to' array is empty or contains invalid symbols.
     */
    public function formatParts(
        ?string $smallestUnitSymbol = null,
        ?int $precision = null,
        bool $showZeros = false,
        bool $ascii = false
    ): string
    {
        // Validate precision.
        static::validatePrecision($precision);

        // Get the part unit symbols.
        $partUnits = static::validatePartUnitSymbols();
        $partUnitSymbols = static::getPartsConfig()['to'];

        // If the smallest unit is not specified, use the last part unit.
        if ($smallestUnitSymbol === null) {
            $smallestUnitSymbol = Arrays::last($partUnitSymbols);
        }

        // Get the quantity as parts. This will validate the parts units and the smallest unit.
        $parts = $this->toParts($smallestUnitSymbol, $precision);

        // Get the smallest unit.
        $smallestUnitIndex = (int)array_search($smallestUnitSymbol, $partUnitSymbols, true);
        $smallestUnit = $partUnits[$smallestUnitIndex];

        // Prep.
        $result = [];
        $hasNonZero = false;

        // Generate string as parts.
        for ($i = 0; $i <= $smallestUnitIndex; $i++) {
            $symbol = $partUnitSymbols[$i];
            $unit = $partUnits[$i];
            $value = $parts[$symbol] ?? 0;
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

            // Format the part with no space between the value and unit.
            $valueStr = static::formatValue($value, 'f', $i === $smallestUnitIndex ? $precision : 0);
            $result[] = $valueStr . $unit->format($ascii);
        }

        // If the value is zero, just show '0' with the smallest unit.
        if (empty($result)) {
            $result[] = '0' . $smallestUnit->format($ascii);
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
     * @throws IncomparableTypesException If the other value is not a Quantity.
     * @throws InvalidArgumentException If the two Quantities have different dimensions.
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
            throw new InvalidArgumentException(
                "Cannot compare quantities with different dimensions, got '$dim1' and '$dim2'."
            );
        }

        // Get the other Quantity in the same unit as this one.
        /** @var Quantity $other */
        return $this->derivedUnit->equal($other->derivedUnit) ? $other->value : $other->to($this->derivedUnit)->value;
    }

    // endregion
}
