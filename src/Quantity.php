<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use DivisionByZeroError;
use DomainException;
use Galaxon\Core\Arrays;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Core\Exceptions\IncomparableTypesException;
use Galaxon\Core\Floats;
use Galaxon\Core\Integers;
use Galaxon\Core\Numbers;
use Galaxon\Core\Traits\ApproxComparable;
use Galaxon\Quantities\Exceptions\DimensionMismatchException;
use Galaxon\Quantities\Internal\DerivedUnit;
use Galaxon\Quantities\Internal\QuantityType;
use Galaxon\Quantities\Internal\UnitInterface;
use Galaxon\Quantities\Internal\UnitTerm;
use Galaxon\Quantities\Services\ConversionService;
use Galaxon\Quantities\Services\DimensionService;
use Galaxon\Quantities\Services\PrefixService;
use Galaxon\Quantities\Services\QuantityPartsService;
use Galaxon\Quantities\Services\QuantityTypeService;
use Galaxon\Quantities\Services\RegexService;
use Galaxon\Quantities\Services\UnitService;
use InvalidArgumentException;
use LogicException;
use Override;
use RoundingMode;
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

    // region Constructor

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
            throw new DomainException('Cannot create a quantity with a non-finite value.');
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

    // endregion

    // region Factory method

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
            throw new DomainException('Cannot create a quantity with a non-finite value.');
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

    /**
     * Parse a string representation into a Quantity object.
     *
     * Accepts formats like "123.45 km", "90deg", "1.5e3 ms".
     * Whitespace between value and unit is optional.
     *
     * @param string $input The string to parse.
     * @return self A new Quantity parsed from the string.
     * @throws FormatException If the input string format is invalid.
     * @throws DomainException If the string contains unknown units, or the string contains multiple parts and either
     * the quantity type is unregistered or the result unit symbol is invalid.
     * @throws DimensionMismatchException If called on a subclass and the parsed unit's dimension doesn't match.
     * @throws UnexpectedValueException If there's an unexpected error during parsing.
     * @throws InvalidArgumentException If the string contains multiple parts, and any of the part unit symbols are not
     * strings.
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

        // Try to parse as <num><unit>. Whitespace between the number and unit is permitted. The unit is optional, as
        // for a dimensionless quantity.
        if (RegexService::isValidQuantity($input, $m)) {
            assert(isset($m[1]));
            $result = self::create((float)$m[1], $m[2] ?? null);
        } else {
            // Try to parse the string as multiple parts, as if output from formatParts().
            $result = static::parseParts($input);
        }

        // If this method is not called from Quantity, check the result class is the same as the calling class.
        if (self::class !== static::class && $result::class !== static::class) {
            throw new DimensionMismatchException(static::getDimension(), $result->dimension);
        }

        return $result;
    }

    // endregion

    // region Static methods

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

    // region Unit conversion methods

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
        $value = static::convert($this->value, $this->derivedUnit, $destUnit);

        // Create new object.
        return self::create($value, $destUnit);
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
        return $this->to($this->derivedUnit->toSiBase());
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
        return $this->toSiBase()->simplify();
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
        return $this->to($this->derivedUnit->toEnglishBase());
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
        return $this->toEnglishBase()->simplify();
    }

    /**
     * Convert this Quantity to base units.
     *
     * This method will convert the quantity to SI or English units, depending on what's a better fit with the
     * existing derived unit. For example, units like lbf, mi, ac, US gal, etc. will be converted to lb, ft, and s.
     * But units like km, mg, N, Hz, etc. will be converted to kg, m, and s.
     *
     * @return static A new Quantity expressed in SI or English base units.
     */
    public function toBase(): static
    {
        return $this->derivedUnit->siPreferred() ? $this->toSiBase() : $this->toEnglishBase();
    }

    // endregion

    // region Transformation methods

    /**
     * Merge units that have the same dimension, e.g. 'm' and 'ft', or 's' and 'h', or 'lb' and 'kg'.
     *
     * The first unit encountered of a given dimension will be the one any others are converted to.
     *
     * @return static A new Quantity with compatible units merged.
     */
    public function merge(): static
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
     * @return static A new Quantity with the best SI prefix applied.
     */
    public function autoPrefix(): static
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
     * To auto-prefix the result, chain with autoPrefix():
     *   $q->simplify()->autoPrefix()
     *
     * @return static A new Quantity with expandable units substituted for base units.
     */
    public function simplify(): static
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
                return self::create($qty->value, $newUnitTerm);
            }
        }

        // Check if we should simplify to SI or English base units.
        $si = $qty->derivedUnit->siPreferred();

        // Get the units to check.
        $units = $si
            ? UnitService::getBySystem(UnitSystem::Si)
            : UnitService::getBySystem(UnitSystem::Imperial);

        // Initialize tracking variables.
        $maxUnitsReplaced = 0;
        $bestExpandableUnit = null;

        // Loop through all the units and try to find an expandable unit that matches the quantity.
        foreach ($units as $unit) {
            // Skip base (non-expandable) units.
            if ($unit->isBase()) {
                continue;
            }

            // If English preferred, check the unit is also US customary.
            if (!$si && !$unit->belongsToSystem(UnitSystem::UsCustomary)) {
                continue;
            }

            // Expand the unit to SI or English base units.
            $expansionUnit = DimensionService::getBaseDerivedUnit($unit->dimension, $si);

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
                $bestExpandableUnit = $unit;
            }
        }

        // If we found a match, substitute the necessary unit terms for the expandable unit.
        if ($bestExpandableUnit !== null) {
            // Get the remaining base units not replaced by the expandable unit.
            $rem = DimensionService::sub($qty->dimension, $bestExpandableUnit->dimension);
            $remUnit = DimensionService::getBaseDerivedUnit($rem, $si);
            $newUnit = new DerivedUnit($bestExpandableUnit)->mul($remUnit);
            return $qty->to($newUnit);
        }

        // No replacements found; return a copy.
        return clone $qty;
    }

    /**
     * Create a new Quantity with the same unit but a different value.
     *
     * @param float $value The new numeric value.
     * @return static A new Quantity with the given value in the same unit.
     */
    public function withValue(float $value): static
    {
        return self::create($value, $this->derivedUnit);
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
     * @return self A new Quantity containing the negative of this Quantity's unit.
     * @example
     *   $length = new Length(10, 'm');
     *   $negated = $length->neg();  // Length(-10, 'm')
     */
    public function neg(): self
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
        return self::create(1.0 / $this->value, $this->derivedUnit->inv());
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
     *   $sum = $a->add($b);         // Length(2100, 'm')
     *   $sum2 = $a->add(50, 'cm');  // Length(100.5, 'm')
     */
    public function add(self $other): self
    {
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
        $otherValue = $this->derivedUnit->equal($other->derivedUnit)
            ? $other->value
            : $other->to($this->derivedUnit)->value;

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
     * @throws DomainException If a value is non-finite or the unit is unknown.
     * @throws FormatException If a unit string cannot be parsed.
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
            $other = self::create(1, DerivedUnit::toDerivedUnit($other));
        }

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
     *
     * @param self|float|string|UnitInterface $other The Quantity, number, or unit to divide.
     * @return self A new Quantity representing the result of the division.
     * @throws DivisionByZeroError If the divisor is zero.
     * @throws DomainException If a value is non-finite or the unit is unknown.
     * @throws FormatException If a unit string cannot be parsed.
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
            $other = self::create(1, DerivedUnit::toDerivedUnit($other));
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
     *   $squared = $length->sqr();  // Length(100, 'm^2')
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

    /**
     * Square this Quantity.
     *
     * Equivalent to pow(2), but more efficient and readable.
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

    // region Conversion methods

    /**
     * Format a numeric value as a string.
     *
     * Format specifiers:
     *   - 'e': Scientific notation with lowercase 'e'.
     *   - 'E': Scientific notation with uppercase 'E'.
     *   - 'f': Fixed-point notation (locale-aware).
     *   - 'F': Fixed-point notation (non-locale-aware, always uses '.' as decimal separator).
     *   - 'g': Shortest of 'e' or 'f' (lower-case 'e'/locale-aware). [default]
     *   - 'G': Shortest of 'E' or 'f' (upper-case 'E'/locale-aware).
     *   - 'h': Shortest of 'e' or 'F' (lower-case 'e'/non-locale-aware).
     *   - 'H': Shortest of 'E' or 'F' (upper-case 'E'/non-locale-aware).
     * For more information, see https://www.php.net/manual/en/function.sprintf.php
     *
     * The meaning of the precision argument depends on the format specifier.
     *   - For e/E/f/F, precision means the number of digits after the decimal point.
     *   - For g/G/h/H, precision means the number of significant digits.
     *
     * If $trimZeros is true, trailing zeros (and if necessary, a trailing decimal point) are automatically
     * removed. For a value string with an exponent, this applies only to the mantissa (the part before the 'e').
     * If $trimZeros is false, all digits are preserved.
     * If $trimZeros is null (default), the behavior will depend on whether precision was specified or not.
     * If $precision is null, zeros will be trimmed; if the $precision is specified, zeros will not be trimmed.
     *
     * When $ascii is false and scientific notation is used, the exponent is rendered as ×10 with
     * superscript digits (e.g. 1.50×10³) instead of e+3.
     *
     * @param float $value The numeric value to format.
     * @param string $specifier The format specifier (default 'f').
     * @param ?int $precision Number of decimal places for e/f, or significant digits for g/h (default null = 6).
     * @param ?bool $trimZeros If trailing zeros should be trimmed (default null for auto).
     * @param bool $ascii If true, use ASCII e notation. If false (default), use ×10 with superscript exponents.
     * @return string The formatted value string.
     * @throws DomainException If the specifier or precision is invalid.
     */
    public static function formatValue(
        float $value,
        string $specifier = 'g',
        ?int $precision = null,
        ?bool $trimZeros = null,
        bool $ascii = false
    ): string {
        // Validate the specifier.
        $validFormats = ['e', 'E', 'f', 'F', 'g', 'G', 'h', 'H'];
        if (!in_array($specifier, $validFormats, true)) {
            $formatsString = Arrays::toSerialList(Arrays::quoteValues($validFormats), 'or');
            throw new DomainException("Invalid format specifier: '$specifier'. Must be $formatsString.");
        }

        // Validate the precision.
        if ($precision !== null && ($precision < 0 || $precision > 17)) {
            throw new DomainException("Invalid precision: $precision. Must be between 0 and 17.");
        }

        // Set $trimZeros if not set.
        if ($trimZeros === null) {
            $trimZeros = $precision === null;
        }

        // Canonicalize -0.0 to 0.0.
        $value = Floats::normalizeZero($value);

        // Format with the desired precision and specifier.
        // If the precision is null, omit it from the format string to use the sprintf default (usually 6).
        $formatString = $precision === null ? "%$specifier" : "%.$precision$specifier";
        $valueStr = sprintf($formatString, $value);

        // Look for an 'e' or 'E'.
        $ePos = stripos($valueStr, 'e');

        // Check for fixed point format.
        if ($ePos === false) {
            // Trim zeros if requested.
            if ($trimZeros && str_contains($valueStr, '.')) {
                $valueStr = rtrim(rtrim($valueStr, '0'), '.');
            }

            return $valueStr;
        }

        // Disassemble the value string.
        $mantissa = substr($valueStr, 0, $ePos);
        $expSeparator = $valueStr[$ePos];
        $exp = substr($valueStr, $ePos + 1);

        // Trim zeros from the mantissa if requested.
        if ($trimZeros && str_contains($mantissa, '.')) {
            $mantissa = rtrim(rtrim($mantissa, '0'), '.');
        }

        // If we want Unicode format and there's an exponent, replace it with the Unicode version.
        if (!$ascii) {
            $expSeparator = '×10';
            $exp = Integers::toSuperscript((int)$exp);
        }

        // Reassemble the value string.
        return $mantissa . $expSeparator . $exp;
    }

    /**
     * Format the measurement as a string with control over precision and notation.
     *
     * See formatValue() for details on the $specifier, $precision, and $trimZeros parameters.
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
        $valueStr = self::formatValue($this->value, $specifier, $precision, $trimZeros, $ascii);

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
     * @return static A new Quantity representing the sum of the parts.
     * @throws InvalidArgumentException If any of the unit symbols are not strings, or any of the values are not
     * numbers.
     * @throws DomainException If the quantity type is unregistered, or the result unit symbol or sign is invalid.
     * @see QuantityPartsService::fromParts()
     */
    public static function fromParts(array $parts): static
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
     * @return static A new Quantity representing the sum of the parts.
     * @throws FormatException If the input string is invalid.
     * @throws UnexpectedValueException If there is an unexpected error during parsing.
     * @throws DomainException If the quantity type is unregistered or the result unit symbol is invalid.
     * @throws InvalidArgumentException If any of the part unit symbols are not strings.
     * @see QuantityPartsService::parseParts()
     */
    public static function parseParts(string $input): static
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
     * @throws DimensionMismatchException If the two Quantities have different dimensions.
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
            throw new DimensionMismatchException($dim1, $dim2);
        }

        // Get the other Quantity in the same unit as this one.
        return $this->derivedUnit->equal($other->derivedUnit) ? $other->value : $other->to($this->derivedUnit)->value;
    }

    // endregion
}
