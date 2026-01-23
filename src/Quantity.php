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
 * Derived classes must implement:
 * - getUnitDefinitions(): Define the base and derived units, and specify the prefixes they accept.
 * (A derived unit is a base unit with an exponent, e.g. 'm3'.)
 * - Optionally override getConversions(): Define conversion factors between units
 *
 * Prefix system:
 * - Units can specify allowed prefixes using bitwise flags (PREFIX_GROUP_METRIC, PREFIX_GROUP_BINARY, etc.)
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

    // phpcs:disable PSR2.Classes.PropertyDeclaration
    // phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact

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
     * @var QuantityType
     */
    public QuantityType $type {
        get => QuantityTypeRegistry::getByDimension($this->dimension);
    }

    // phpcs:enable PSR2.Classes.PropertyDeclaration
    // phpcs:enable Generic.WhiteSpace.ScopeIndent.IncorrectExact

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
        if ($qtyType?->class !== null && $callingClass !== $qtyType->class) {
            throw new LogicException(
                "Cannot instantiate a {$qtyType->name} quantity by calling `new $callingClass()`. Call " .
                "`new {$qtyType->class}()` instead. Otherwise, call `$qtyClass::create()`, which will automatically " .
                'create an object of the correct class.'
            );
        }

        // Set the properties.
        $this->value = Floats::normalizeZero($value);
        $this->derivedUnit = $derivedUnit;
    }

    // endregion

    // region Static methods

    /**
     * Create a Quantity of the appropriate type for the given unit.
     *
     * This works whether called from Quantity or a subclass.
     * Uses the dimension class registry to instantiate the correct subclass.
     * For example, a unit with dimension 'L2' will create an Area object (assuming Area is registered).
     *
     * @param float $value The numeric value.
     * @param null|string|UnitInterface $unit The unit.
     * @return static A Quantity of the appropriate type.
     * @throws DomainException If the value is non-finite (±INF or NAN).
     * @throws FormatException If the unit is provided as a string, and it cannot be parsed.
     * @throws DomainException If the unit is provided as a string, and it contains unknown units.
     * @throws LogicException Never.
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

    /**
     * Parse a string representation into a Quantity object.
     *
     * Accepts formats like "123.45 km", "90deg", "1.5e3 ms".
     * Whitespace between value and unit is optional.
     *
     * @param string $value The string to parse.
     * @return static A new Quantity parsed from the string.
     * @throws FormatException If the string format is invalid.
     * @throws DomainException If the string contains unknown units.
     *
     * @example
     *   Length::parse("123.45 km")  // Length(123.45, 'km')
     *   Angle::parse("90deg")       // Angle(90.0, 'deg')
     *   Time::parse("1.5e3 ms")     // Time(1500.0, 'ms')
     */
    public static function parse(string $value): static
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
        if (preg_match("/^($rxNum)\s*($rxDerivedUnit)?$/u", $value, $m)) {
            return self::create((float)$m[1], $m[2] ?? null);
        }

        // Invalid format.
        throw new FormatException($err);
    }

    private static function compatibleUnitTerms(DerivedUnit $srcUnit, DerivedUnit $destUnit): bool
    {
        if (count($srcUnit->unitTerms) !== count($destUnit->unitTerms)) {
            return false;
        }

        // Get the unit terms without keys.
        $srcUnitTerms = array_values($srcUnit->unitTerms);
        $destUnitTerms = array_values($destUnit->unitTerms);

        foreach ($srcUnitTerms as $i => $srcUnitTerm) {
            if ($srcUnitTerm->dimension !== $destUnitTerms[$i]->dimension) {
                return false;
            }
        }

        return true;
    }

    public static function convert(float $value, string|UnitInterface $srcUnit, string|UnitInterface $destUnit): float
    {
        // Get the units as DerivedUnit objects.
        $srcUnit = DerivedUnit::toDerivedUnit($srcUnit);
        $destUnit = DerivedUnit::toDerivedUnit($destUnit);

        // Check the target unit has compatible dimensions.
        $srcDim = $srcUnit->dimension;
        $destDim = $destUnit->dimension;

        if ($srcDim !== $destDim) {
            throw new DomainException(
                "Cannot convert from '$srcUnit' to '$destUnit' as these units represent different quantity types."
            );
        }

        // Calculate the conversion factor.
        $result = $value;

        // If incompatible, reduce units by combining like terms.
        if (!self::compatibleUnitTerms($srcUnit, $destUnit)) {
            // Reduce source unit term.
            $srcQty = self::create(1, $srcUnit);
            $srcQty = $srcQty->mergeCompatibleUnits();
            $result *= $srcQty->value;
            $srcUnit = $srcQty->derivedUnit;

            // Reduce destination unit term.
            $srcQty = self::create(1, $srcUnit);
            $srcQty = $srcQty->mergeCompatibleUnits();
            $result *= $srcQty->value;
            $srcUnit = $srcQty->derivedUnit;
        }

        // If still incompatible, expand units and reduce again.
        if (!self::compatibleUnitTerms($srcUnit, $destUnit)) {
            // Expand and reduce source unit if possible.
            $srcQty = self::create(1, $srcUnit);
            $srcQty = $srcQty->expandNamedUnits()->mergeCompatibleUnits();
            $result *= $srcQty->value;
            $srcUnit = $srcQty->derivedUnit;

            // Expand and reduce destination unit if possible.
            $destQty = self::create(1, $destUnit);
            $destQty = $destQty->expandNamedUnits()->mergeCompatibleUnits();
            $result /= $destQty->value;
            $destUnit = $destQty->derivedUnit;
        }

        // If still incompatible, conversion can't be completed.
        if (!self::compatibleUnitTerms($srcUnit, $destUnit)) {
            throw new DomainException(
                "Cannot convert from '$srcUnit' to '$destUnit' as these units have different numbers of terms."
            );
        }

        // Now we've validated the units, check for a shortcut. This could be done at the top of the function, but
        // it's probably better to make sure the method is being called with the correct units anyway, which should help
        // catch bugs early.
        if ($value === 0.0) {
            return 0.0;
        }

        $srcUnitTerms = array_values($srcUnit->unitTerms);
        $destUnitTerms = array_values($destUnit->unitTerms);

        foreach ($srcUnitTerms as $i => $srcUnitTerm) {
            // Get the matching destination unit term.
            $destUnitTerm = $destUnitTerms[$i];

            // Try to get the conversion factor between the two unit terms.
            $factor = Converter::getByDimension($srcUnitTerm->dimension)
                ->getConversionFactor($srcUnitTerm, $destUnitTerm);

            if ($factor === null) {
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
        return $this->derivedUnit->isSi();
    }

    public function isDimensionless(): bool
    {
        return $this->derivedUnit->isDimensionless();
    }

    // endregion

    // region Transformation methods

    /**
     * Convert this Quantity to a different unit.
     *
     * Returns a new Quantity object with the equivalent value in the destination unit.
     *
     * @param string|UnitInterface $destUnit The destination unit to convert to.
     * @return static A new Quantity in the specified unit.
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
     * Convert this quantity to SI base units.
     *
     * @return self A new Quantity with the value converted to SI units.
     * @throws DomainException If any dimension codes are invalid or conversion fails.
     * @throws LogicException If any dimension codes do not have an SI base unit defined.
     */
    public function toSi(): self
    {
        $siUnit = $this->derivedUnit->toSi();
        return $this->to($siUnit);
    }

    /**
     * Create a new Quantity with the same unit but a different value.
     *
     * @param float $value
     * @return $this
     */
    public function withValue(float $value): static
    {
        return self::create($value, $this->derivedUnit);
    }

    /**
     * Expand the Quantity by converting named units into their expansions, e.g. N => kg*m/s2
     *
     * @return Quantity
     */
    public function expandNamedUnits(): self
    {
        // Start building new Quantity.
        $newValue = $this->value;
        $newUnit = new DerivedUnit();

        // Expand any named units (i.e. units with expansions).
        foreach ($this->derivedUnit->unitTerms as $unitTerm) {
            $expansionUnit = $unitTerm->unit->expansionUnit;
            $factor = 1;

            if ($expansionUnit === null) {

                // Look for an indirect expansion.
                $converter = Converter::getByDimension($unitTerm->dimension);

                foreach ($converter->units as $converterUnit) {

                    // We're just looking for a named unit by itself.
                    if (count($converterUnit->unitTerms) !== 1) {
                        continue;
                    }

                    // Get the first unit term.
                    $converterUnitTerm = Arrays::first($converterUnit->unitTerms);

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

        return self::create($newValue, $newUnit)->mergeCompatibleUnits();
    }

    /**
     * Merge unit terms with units that have the same dimension, e.g. 'm' and 'ft', or 's' and 'h', or 'lb' and 'kg'.
     *
     * @return $this The quantity to update.
     * @throws DomainException
     * @throws FormatException
     */
    public function mergeCompatibleUnits(): self
    {
        $newValue = $this->value;
        $newUnit = new DerivedUnit();

        foreach ($this->derivedUnit->unitTerms as $symbol => $thisUnitTerm) {
            // See if there is already a unit term with a unit with this dimension.
            $newUnitTerm1 = array_find(
                $newUnit->unitTerms,
                static fn (UnitTerm $ut) => $ut->unit->dimension === $thisUnitTerm->unit->dimension
            );

            // If no unit exists with this dimension, copy the existing one to the result.
            if ($newUnitTerm1 === null) {
                $newUnit->addUnitTerm($thisUnitTerm);
            } else {
                // If there is already a unit with this dimension, convert the second unit term to the same unit as the
                // first.
                $unexponentiatedThisUnitTerm = $thisUnitTerm->removeExponent();
                $converter = Converter::getByDimension($unexponentiatedThisUnitTerm->dimension);
                $factor = $converter->getConversionFactor(
                    $unexponentiatedThisUnitTerm,
                    $newUnitTerm1->unexponentiatedAsciiSymbol
                );
                // Multiply by the conversion factor raised to the exponent of the second unit term.
                $newValue *= $factor ** $thisUnitTerm->exponent;
                // Create a second term with the same unit as the first, but the exponent of the second term.
                $newUnitTerm2 = $newUnitTerm1->withExponent($thisUnitTerm->exponent);
                // Adding the new unit term will combine the first and the second, because they have the same
                // unexponentiated symbol (unit and prefix).
                $newUnit->addUnitTerm($newUnitTerm2);
            }
        }

        return self::create($newValue, $newUnit);
    }

    /**
     * Substitute named units where possible.
     *
     * @return static
     */
    public function substituteNamedUnits(bool $autoPrefix = false): self
    {
        // Handle Hertz separately. We only want to swap 's-1' for 'Hz' if it's the only unit term.
        if (count($this->derivedUnit->unitTerms) === 1) {
            $unitTerm = array_values($this->derivedUnit->unitTerms)[0];
            if ($unitTerm->unit->asciiSymbol === 's' && $unitTerm->exponent === -1) {
                $newUnitTerm = new UnitTerm('Hz', PrefixRegistry::invert($unitTerm->prefix));
                return self::create($this->value, $newUnitTerm);
            }
        }

        // Start constructing result.
        $newValue = $this->value;
        $newUnit = clone $this->derivedUnit;

        // Get all named units.
        $namedUnits = UnitRegistry::getExpandableUnits();

        // Track best match.
        $bestNamedUnit = null;
        $bestScore = 0;

        foreach ($namedUnits as $namedUnit) {
            // See if the $this unit matches.
            $match = true;
            $matchScore = 0;

            foreach ($namedUnit->expansionUnit->unitTerms as $namedUnitTerm) {
                // See if there's a unit term with the same unit. Ignore prefix and exponent.
                $thisUnitTerm = $this->derivedUnit->getUnitTermByUnit($namedUnitTerm->unit);

                // If there's a match on unit and exponent, increase the match score.
                if (
                    $thisUnitTerm !== null &&
                    Numbers::sign($thisUnitTerm->exponent) === Numbers::sign($namedUnitTerm->exponent) &&
                    abs($thisUnitTerm->exponent) >= abs($namedUnitTerm->exponent)
                ) {
                    $matchScore++;
                } else {
                    // No match.
                    $match = false;
                    break;
                }
            }

            // If we found a match, see if it's the best.
            if ($match && $matchScore > 1 && ($bestNamedUnit === null || $matchScore > $bestScore)) {
                $bestNamedUnit = $namedUnit;
                $bestScore = $matchScore;
            }
        }

        // If we found a match, substitute the named unit.
        if ($bestNamedUnit) {
            // Multiply by the inverse of the expansion quantity unit terms.
            foreach ($bestNamedUnit->expansionUnit->unitTerms as $namedUnitTerm) {
                $newUnit->addUnitTerm($namedUnitTerm->inv());
            }

            // Add the named unit.
            $newUnit->addUnitTerm(new UnitTerm($bestNamedUnit));
        }

        // Construct the result.
        $result = self::create($newValue, $newUnit);

        // Apply autoprefixing if necessary.
        return $autoPrefix ? $result->autoPrefix() : $result;
    }

    /**
     * Find the best SI prefix and modify the Quantity accordingly.
     *
     * @return $this
     */
    public function autoPrefix(): self
    {
        // See what prefixes are available for the first unit term.
        /** @var UnitTerm $firstUnitTerm */
        $firstUnitTerm = Arrays::first($this->derivedUnit->unitTerms);
        if ($firstUnitTerm->unit->prefixGroup === 0) {
            // No prefixes are available for the first unit, so we can't add any.
            return $this;
        }

        // Create the new value and derived unit by removing all prefixes.
        $newValue = $this->value;
        $newDerivedUnit = new DerivedUnit();
        foreach ($this->derivedUnit->unitTerms as $unitTerm) {
            // Multiply the value by the multiplier.
            $newValue *= $unitTerm->multiplier;
            // Create a new unit term with no prefix.
            $newUnitTerm = $unitTerm->removePrefix();
            // Add it to the new DerivedUnit.
            $newDerivedUnit->addUnitTerm($newUnitTerm);
        }

        // Choose the prefix that produces the smallest value greater than or equal to 1.
        // Start with the current situation, which is no prefix.
        $absValue = abs($newValue);
        $sign = Numbers::sign($newValue);
        $bestPrefix = null;
        $bestValue = $absValue;

        // Try each allowed prefix to see if it's better. We want the prefix that produces the smallest value greater
        // than or equal to 1.
        foreach ($firstUnitTerm->unit->allowedPrefixes as $prefix => $prefixMultiplier) {
            // We only want to consider engineering prefixes for this. The others (c, d, da, h) are rarely used for most
            // units.
            if (!PrefixRegistry::isPowerOf1000($prefixMultiplier)) {
                continue;
            }

            // Compute the value we'd have if we use this prefix.
            $prefixedValue = $absValue / ($prefixMultiplier ** $firstUnitTerm->exponent);

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
            // Remove first unit term.
            $newDerivedUnit->removeUnitTerm($firstUnitTerm);
            // Create a new one with the prefix applied.
            $newUnitTerm = $firstUnitTerm->withPrefix($bestPrefix);
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
     * @return static A new Quantity with non-negative value in the same unit.
     *
     * @example
     *   $temp = new Temperature(-10, 'C');
     *   $abs = $temp->abs();  // Temperature(10, 'C')
     */
    public function abs(): static
    {
        return self::create(abs($this->value), $this->derivedUnit);
    }

    /**
     * Negate a measurement.
     *
     * @return static A new Quantity containing the negative of this measurement's unit.
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
     * @param null|string|DerivedUnit $otherUnit The other quantity's unit, if a numeric value was provided.
     * @return static A new Quantity containing the sum in this measurement's unit.
     * @throws DomainException If value is non-finite or unit is invalid.
     * @throws LogicException If no conversion path exists between units.
     *
     * @example
     *   $a = new Length(100, 'm');
     *   $b = new Length(2, 'km');
     *   $sum = $a->add($b);           // Length(2100, 'm')
     *   $sum2 = $a->add(50, 'cm');    // Length(100.5, 'm')
     */
    public function add(self|float $otherOrValue, null|string|DerivedUnit $otherUnit = null): self
    {
        // Get the other quantity as an object.
        $other = is_float($otherOrValue) ? self::create($otherOrValue, $otherUnit) : $otherOrValue;

        // Get the other Quantity in the same unit as this one.
        $otherValue = $this->derivedUnit->equal($other->derivedUnit)
            ? $other->value
            : $other->to($this->derivedUnit)->value;

        // Add the two values.
        return $this->withValue($this->value + $otherValue)->substituteNamedUnits();
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
     * @param null|string|DerivedUnit $otherUnit The other quantity's unit, if a numeric value was provided.
     * @return static A new Quantity containing the difference in this measurement's unit.
     * @throws DomainException If value is non-finite or unit is invalid.
     * @throws LogicException If no conversion path exists between units.
     *
     * @example
     *   $a = new Length(100, 'm');
     *   $b = new Length(2, 'km');
     *   $diff = $a->sub($b);          // Length(-1900, 'm')
     */
    public function sub(self|float $otherOrValue, null|string|DerivedUnit $otherUnit = null): self
    {
        // Get the other quantity as an object.
        $other = is_float($otherOrValue) ? self::create($otherOrValue, $otherUnit) : $otherOrValue;

        // Get the other Quantity in the same unit as this one.
        $otherValue = $this->derivedUnit === $other->derivedUnit
            ? $other->value
            : $other->to($this->derivedUnit)->value;

        // Subtract the values.
        return $this->withValue($this->value - $otherValue);
    }

    /**
     * Invert this quantity (1/x).
     *
     * @return Quantity A new Quantity with inverted value and unit.
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
     * @param null|string|DerivedUnit $otherUnit The other quantity's unit, if a numeric value was provided.
     * @return Quantity A new Quantity representing the result of the multiplication.
     * @throws DomainException If the multiplier is a non-finite float (±INF or NAN).
     *
     * @example
     *   $length = new Length(10, 'm');
     *   $doubled = $length->mul(2);  // Length(20, 'm')
     */
    public function mul(float|self $otherOrValue, null|string|DerivedUnit $otherUnit = null): self
    {
        // Get the other quantity as an object.
        $other = is_float($otherOrValue) ? self::create($otherOrValue, $otherUnit) : $otherOrValue;

        // Start by multiplying the values.
        $newValue = $this->value * $other->value;

        // If the other quantity is dimensionless, this is a simple multiplication by a scalar.
        if ($other->isDimensionless()) {
            return $this->withValue($newValue);
        }

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
     * Divide this measurement by a scalar factor.
     *
     * @param float|self $otherOrValue The scalar or Quantity to divide by.
     * @param null|string|DerivedUnit $otherUnit The other quantity's unit, if a numeric value was provided.
     * @return Quantity A new Quantity representing the result of the division.
     * @throws DivisionByZeroError If the divisor is zero.
     * @throws DomainException If the divisor is non-finite (±INF or NAN).
     *
     * @example
     *   $length = new Length(10, 'm');
     *   $half = $length->div(2);  // Length(5, 'm')
     */
    public function div(float|self $otherOrValue, null|string|DerivedUnit $otherUnit = null): self
    {
        // Get the other quantity as an object.
        $other = is_float($otherOrValue) ? self::create($otherOrValue, $otherUnit) : $otherOrValue;

        // Multiply by the inverse.
        return $this->mul($other->inv());
    }

    /**
     * Raise the Quantity to an exponent.
     *
     * @param int $exponent
     * @return Quantity
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
     * @throws DomainException If specifier or precision are invalid.
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
        return $str . ($includeSpace ? ' ' : '') . $this->derivedUnit;
    }

    // endregion

    // region Conversion methods

    /**
     * Convert the measurement to a string using basic formatting.
     *
     * For custom formatting, use format().
     *
     * @return string The measurement as a string (e.g. "1.5707963267949 rad").
     */
    #[Override]
    public function __toString(): string
    {
        return $this->value . ' ' . $this->derivedUnit;
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
     * @throws InvalidArgumentException If any of the values are not numbers.
     * @throws DomainException If any of the values are non-finite or negative.
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
            // Check that the key is valid.
            if (!in_array($key, $validKeys, true)) {
                throw new DomainException('Invalid part name: ' . $key);
            }

            // Check that the value is a number.
            if (!Numbers::isNumber($value)) {
                throw new InvalidArgumentException('All values must be numbers.');
            }

            if ($key === 'sign') {
                // Check that the sign value is valid.
                if ($value !== -1 && $value !== 1) {
                    throw new DomainException('Sign must be -1 or 1.');
                }
            } elseif (!is_finite($value) || $value < 0.0) {
                // Check that the part value is finite and non-negative.
                throw new DomainException('All part values must be finite and non-negative.');
            }
        }

        // Initialize the Quantity to 0, with the unit set to the smallest unit.
        $smallestUnit = Arrays::last($partUnits);
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
     * @throws DomainException If any arguments are invalid.
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
        $parts = [
            'sign' => $sign,
        ];
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
     * @throws DomainException If any arguments are invalid.
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
        return $this->derivedUnit === $other->derivedUnit ? $other->value : $other->to($this->derivedUnit)->value;
    }

    /**
     * Check smallest unit argument is valid.
     *
     * @param string $smallestUnit
     * @return void
     * @throws DomainException
     */
    protected static function validateSmallestUnit(string $smallestUnit): void
    {
        // Validate and get part units.
        $symbols = static::validateAndTransformPartUnits();
        $partUnits = array_keys($symbols);

        // Check the smallest unit is valid.
        if (!in_array($smallestUnit, $partUnits, true)) {
            throw new DomainException('Invalid smallest unit specified. Must be one of: ' .
                                 implode(', ', Arrays::quoteValues($partUnits)));
        }
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
            throw new DomainException('Invalid precision specified. Must be null or a non-negative integer.');
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
                'The derived Quantity class must define the part units by overriding getPartUnits(), so it returns ' .
                'an array of valid units (with optional alternative symbols).'
            );
        }

        // Create a new array to contain the map of units to symbols.
        $symbols = [];

        // Ensure all part units are valid units.
        foreach ($partUnits as $partUnit => $symbol) {
            // If the key is an integer, the unit and the symbol are the same.
            if (is_int($partUnit)) {
                $partUnit = $symbol;
            }

            // Ensure the unit is valid.
            $unit = UnitRegistry::getBySymbol($partUnit);
            if ($unit === null) {
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
