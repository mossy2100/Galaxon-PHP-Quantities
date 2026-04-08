<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Services;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Core\Exceptions\NullArgumentException;
use Galaxon\Core\Floats;
use Galaxon\Core\Numbers;
use Galaxon\Quantities\Exceptions\DimensionMismatchException;
use Galaxon\Quantities\Exceptions\UnknownUnitException;
use Galaxon\Quantities\Internal\QuantityType;
use Galaxon\Quantities\Internal\Unit;
use Galaxon\Quantities\Quantity;
use InvalidArgumentException;
use LogicException;
use UnexpectedValueException;

/**
 * Service for decomposing quantities into parts and reassembling them.
 *
 * Handles operations like converting 1.5 hours into "1h 30min 0s" and vice versa. Built-in
 * defaults are baked in for length, time, angle, and mass; callers can override per call by
 * passing $partUnitSymbols / $resultUnitSymbol arguments.
 */
class QuantityPartsService
{
    // region Private constants

    /**
     * Default parts configurations keyed by quantity type name.
     *
     * @var array<string, array{
     *     partUnitSymbols?: list<string>,
     *     resultUnitSymbol?: string
     * }>
     */
    private const array DEFAULT_PARTS_CONFIGS = [
        'length' => [
            'partUnitSymbols'  => ['mi', 'yd', 'ft', 'in'],
            'resultUnitSymbol' => 'ft',
        ],
        'time'   => [
            'partUnitSymbols'  => ['y', 'mo', 'w', 'd', 'h', 'min', 's'],
            'resultUnitSymbol' => 's',
        ],
        'angle'  => [
            'partUnitSymbols'  => ['deg', 'arcmin', 'arcsec'],
            'resultUnitSymbol' => 'deg',
        ],
        'mass'   => [
            'resultUnitSymbol' => 'lb',
        ],
    ];

    // endregion

    // region Parts methods

    /**
     * Create a new Quantity object as a sum of measurements of different units.
     *
     * If the class is registered as a quantity type, the input and result units must be compatible.
     *
     * The $parts array may include an optional 'sign' key to indicate the sign of the sum, which can be 1
     * (non-negative) or -1 (negative). If omitted, the sign is assumed to be 1.
     *
     * @param ?QuantityType $quantityType The quantity type.
     * @param array<string, int|float> $parts The parts.
     * @param ?string $resultUnitSymbol The result unit symbol, or null to use the default for this quantity type.
     * @return Quantity A new Quantity representing the sum of the parts.
     * @throws NullArgumentException If the quantity type is null.
     * @throws DomainException If the sign is not -1 or 1.
     * @throws LogicException If $resultUnitSymbol is null and no default exists for the quantity type.
     * @throws UnknownUnitException If $resultUnitSymbol or any of the part unit symbols are not recognized units.
     * @throws DimensionMismatchException If $resultUnitSymbol is incompatible with the quantity type.
     * @internal Call the Quantity method with the same name, not this one.
     */
    public static function fromParts(
        ?QuantityType $quantityType,
        array $parts,
        ?string $resultUnitSymbol = null
    ): Quantity {
        $quantityType = self::validateQuantityType($quantityType);

        // Validate the result unit symbol and get the Unit.
        $resultUnit = self::validateResultUnit($quantityType, $resultUnitSymbol);

        // Validate the sign.
        $sign = 1;
        if (isset($parts['sign'])) {
            $sign = $parts['sign'];
            if ($sign !== -1 && $sign !== 1) {
                throw new DomainException("Invalid sign: $sign. Must be -1 or 1.");
            }
        }

        // Initialize the Quantity to 0, with the unit set to the result unit.
        $qty = Quantity::create(0, $resultUnit);

        // Add each of the possible units.
        foreach ($parts as $partUnitSymbol => $partValue) {
            // Skip sign.
            if ($partUnitSymbol === 'sign') {
                continue;
            }

            // Add the part. It will be converted to the result unit automatically.
            // If the value or unit is invalid, this will throw an exception.
            $qty = $qty->add(Quantity::create($partValue, $partUnitSymbol));
        }

        // Make negative if necessary.
        if ($sign === -1) {
            $qty = $qty->neg();
        }

        return $qty;
    }

    /**
     * Convert a quantity to parts.
     *
     * Returns an array with components from the largest to the smallest unit.
     * All the part values will be integers except for the smallest unit value.
     * A sign key is also included with an integer value of 1 for positive or zero, or -1 for negative.
     *
     * @param Quantity $quantity The quantity to decompose.
     * @param ?int $precision The number of decimal places for rounding the smallest unit, or null for no rounding.
     * @param ?list<string> $partUnitSymbols The part unit symbols, or null to use the default for this quantity type.
     * @return array<string, int|float> Array of parts, plus the sign (1 or -1).
     * @throws NullArgumentException If the quantity type is null.
     * @throws DomainException If precision is negative, or if $partUnitSymbols is an empty array.
     * @throws LogicException If $partUnitSymbols is null and no default exists for the quantity type.
     * @throws InvalidArgumentException If any of the unit symbols are not strings.
     * @throws UnknownUnitException If a part unit symbol is not recognized.
     * @throws DimensionMismatchException If a part unit is incompatible with the quantity's dimension.
     * @internal Call the Quantity method with the same name, not this one.
     */
    public static function toParts(Quantity $quantity, ?int $precision = null, ?array $partUnitSymbols = null): array
    {
        $quantityType = self::validateQuantityType($quantity->quantityType);

        // Validate the precision.
        self::validatePrecision($precision);

        // Validate the part unit symbols and load the units.
        $partUnits = self::validatePartUnits($quantityType, $partUnitSymbols);

        // Initialize the result array.
        $parts = [
            'sign' => Numbers::sign($quantity->value, false),
        ];

        // Initialize the remainder to the source value converted to the smallest unit.
        $nUnits = count($partUnits);
        $smallestUnit = $partUnits[$nUnits - 1];
        $smallestUnitSymbol = $smallestUnit->asciiSymbol;
        $rem = abs($quantity->to($smallestUnit)->value);

        // Get the integer parts.
        for ($i = 0; $i < $nUnits - 1; $i++) {
            // Get the number of current units in the smallest unit.
            $partUnit = $partUnits[$i];
            $factor = Quantity::convert(1, $partUnits[$i], $smallestUnit);
            $wholeNumCurUnit = (int)floor($rem / $factor);
            $parts[$partUnit->asciiSymbol] = $wholeNumCurUnit;
            $rem = $rem - $wholeNumCurUnit * $factor;
        }

        // If the precision is unspecified, we're done.
        if ($precision === null) {
            $parts[$smallestUnitSymbol] = $rem;
            return $parts;
        }

        // Round off the remainder to the requested precision.
        $rem2 = round($rem, $precision);
        $parts[$smallestUnitSymbol] = $rem2;

        // If the rounding doesn't increase the remainder, we're done.
        if ($rem2 <= $rem) {
            return $parts;
        }

        // If the rounding does increase the remainder, then rounding up one or more larger parts may be necessary.
        // To account for non-integer conversion factors, rebuild the parts array.
        // We call toParts() with $precision = null to avoid infinite recursion.
        $rebuilt = self::fromParts($quantityType, $parts);
        $rebuiltParts = self::toParts($rebuilt, null, $partUnitSymbols);
        $rebuiltParts[$smallestUnitSymbol] = round($rebuiltParts[$smallestUnitSymbol], $precision);
        return $rebuiltParts;
    }

    /**
     * Parse a string of quantity parts.
     *
     * Parts must be separated by spaces.
     * There cannot be spaces between values and units.
     * Units containing spaces (e.g. 'US gal') are not supported.
     * Dimensionless quantities (e.g. '1000') are not supported.
     *
     * Examples:
     *    - "4y 5mo 6d 12h 34min 56.789s"
     *    - "12° 34′ 56.789″"
     *
     * @param ?QuantityType $quantityType The quantity type.
     * @param string $input The string to parse.
     * @param ?string $resultUnitSymbol The result unit symbol, or null to use the default for this quantity type.
     * @return Quantity A new Quantity representing the sum of the parts.
     * @throws NullArgumentException If the quantity type is null.
     * @throws FormatException If the input string is empty or malformed.
     * @throws UnexpectedValueException If there is an unexpected error during parsing.
     * @throws LogicException If $resultUnitSymbol is null and no default exists for the quantity type.
     * @throws UnknownUnitException If $resultUnitSymbol is not a recognized unit.
     * @throws DimensionMismatchException If $resultUnitSymbol is incompatible with the quantity type.
     * @internal Call the Quantity method with the same name, not this one.
     */
    public static function parseParts(
        ?QuantityType $quantityType,
        string $input,
        ?string $resultUnitSymbol = null
    ): Quantity {
        $quantityType = self::validateQuantityType($quantityType);

        // Ensure the input string is not empty.
        $input = trim($input);
        if ($input === '') {
            throw new FormatException('The input string is empty.');
        }

        // Validate the result unit symbol and get the Unit.
        $resultUnit = self::validateResultUnit($quantityType, $resultUnitSymbol);

        // Prepare error message.
        $err = "The provided string '$input' does not represent a valid $quantityType->name quantity.";

        // Allow for a string with multiple parts, e.g. "12h 34min 56.789s"
        // In this format there can be no spaces between values and units.
        $parts = [];
        $stringParts = preg_split('/\s+/', $input);
        if ($stringParts === false) {
            // @codeCoverageIgnoreStart
            throw new UnexpectedValueException('Error splitting string into parts.');
            // @codeCoverageIgnoreEnd
        }

        // Collect the parts.
        $sign = 1;
        $firstPartSeen = false;
        foreach ($stringParts as $stringPart) {
            // Check the part looks like a quantity.
            if (!Quantity::isValidQuantity($stringPart, $m) || empty($m[2])) {
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
            $partValue = abs($partValue);
            $partSymbol = $m[2];
            $parts[$partSymbol] = $partValue;

            if (!$firstPartSeen) {
                $firstPartSeen = true;
            }
        }

        // Add the sign part.
        $parts['sign'] = $sign;

        // Construct the new Quantity from the extracted parts.
        return self::fromParts($quantityType, $parts, $resultUnit->asciiSymbol);
    }

    /**
     * Format quantity as parts.
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
     * @param Quantity $quantity The quantity to format.
     * @param ?int $precision The number of decimal places for rounding the smallest unit, or null for no rounding.
     * @param bool $showZeros If true, show all parts including zeros; if false, skip zero-value components.
     * @param bool $ascii If true, use ASCII characters only.
     * @param ?list<string> $partUnitSymbols The part unit symbols, or null to use the default for this quantity type.
     * @return string The formatted string.
     * @throws NullArgumentException If the quantity type is null.
     * @throws DomainException If precision is negative, or if $partUnitSymbols is an empty array.
     * @throws LogicException If $partUnitSymbols is null and no default exists for the quantity type.
     * @throws InvalidArgumentException If any of the unit symbols are not strings.
     * @throws UnknownUnitException If a part unit symbol is not recognized.
     * @throws DimensionMismatchException If a part unit is incompatible with the quantity's dimension.
     * @internal Call the Quantity method with the same name, not this one.
     */
    public static function formatParts(
        Quantity $quantity,
        ?int $precision = null,
        bool $showZeros = false,
        bool $ascii = false,
        ?array $partUnitSymbols = null
    ): string {
        $quantityType = self::validateQuantityType($quantity->quantityType);

        // Validate the part unit symbols and load the units.
        $partUnits = self::validatePartUnits($quantityType, $partUnitSymbols);

        // Get the quantity as parts.
        $parts = self::toParts($quantity, $precision, $partUnitSymbols);

        // Prep for loop.
        $result = [];
        $nUnits = count($partUnits);

        // Generate string as parts for all but the smallest unit.
        for ($i = 0; $i < $nUnits - 1; $i++) {
            $partUnit = $partUnits[$i];
            $value = $parts[$partUnit->asciiSymbol] ?? 0;

            // Skip zeros if requested.
            if (Numbers::isZero($value) && !$showZeros) {
                continue;
            }

            // Format the part with no space between the value and unit.
            $result[] = $value . $partUnit->format($ascii);
        }

        // Add the smallest unit.
        $smallestUnit = $partUnits[$nUnits - 1];
        $value = $parts[$smallestUnit->asciiSymbol] ?? 0;
        $roundedValue = $precision === null ? $value : round($value, $precision);

        // Skip unless we're showing zeros or the value is non-zero.
        if ($showZeros || $roundedValue !== 0.0 || empty($result)) {
            $valueStr = Floats::format($value, 'f', $precision, null, $ascii);
            $result[] = $valueStr . $smallestUnit->format($ascii);
        }

        // Return a string of units, separated by spaces. Prepend minus sign if negative.
        return ($parts['sign'] === -1 ? '-' : '') . implode(' ', $result);
    }

    // endregion

    // region Validation methods

    /**
     * Check that the quantity type is registered.
     *
     * @param ?QuantityType $quantityType The quantity type to validate.
     * @return QuantityType The validated quantity type.
     * @throws NullArgumentException If the quantity type is null.
     */
    private static function validateQuantityType(?QuantityType $quantityType): QuantityType
    {
        if ($quantityType === null) {
            throw new NullArgumentException('quantityType');
        }

        return $quantityType;
    }

    /**
     * Check precision argument is valid.
     *
     * @param ?int $precision The precision to validate.
     * @throws DomainException If precision is negative.
     */
    private static function validatePrecision(?int $precision): void
    {
        if ($precision !== null && $precision < 0) {
            throw new DomainException("Invalid precision: $precision. Must be null or a non-negative integer.");
        }
    }

    /**
     * Validate the part unit symbols and resolve them to Unit objects.
     *
     * If $partUnitSymbols is null, falls back to the default in DEFAULT_PARTS_CONFIGS for the
     * quantity type. Duplicates and string keys are dropped from the resolved list.
     *
     * @param QuantityType $quantityType The quantity type the symbols belong to. Used for default lookup and error
     * messages.
     * @param ?list<string> $partUnitSymbols The part unit symbols to validate, or null to use the default.
     * @return list<Unit> The corresponding Unit objects, in input order with duplicates removed.
     * @throws DomainException If $partUnitSymbols is an empty array.
     * @throws LogicException If $partUnitSymbols is null and no default exists for the quantity type.
     * @throws InvalidArgumentException If any of the unit symbols are not strings.
     * @throws UnknownUnitException If a unit symbol is not recognized.
     */
    private static function validatePartUnits(QuantityType $quantityType, ?array $partUnitSymbols): array
    {
        // An empty array is invalid.
        if ($partUnitSymbols === []) {
            throw new DomainException('The array of part unit symbols cannot be empty.');
        }

        // If null, get the default value if there is one.
        $partUnitSymbols ??= self::DEFAULT_PARTS_CONFIGS[$quantityType->name]['partUnitSymbols'] ?? null;

        // Ensure we have some part units.
        if ($partUnitSymbols === null) {
            throw new LogicException("No default part unit symbols configured for $quantityType->name quantities.");
        }

        // Drop string keys and duplicates.
        $normalised = array_values(array_unique($partUnitSymbols));

        // Resolve each symbol to a Unit object.
        $partUnits = [];
        foreach ($normalised as $partUnitSymbol) {
            // Check the item type.
            if (!is_string($partUnitSymbol)) {
                throw new InvalidArgumentException('The array of part unit symbols must contain only strings.');
            }

            // Get the unit.
            $partUnit = UnitService::getBySymbol($partUnitSymbol);
            if ($partUnit === null) {
                throw new UnknownUnitException($partUnitSymbol);
            }

            $partUnits[] = $partUnit;
        }

        return $partUnits;
    }

    /**
     * Validate the result unit symbol and resolve it to a Unit object.
     *
     * If $resultUnitSymbol is null, falls back to the default in DEFAULT_PARTS_CONFIGS for the
     * quantity type.
     *
     * @param QuantityType $quantityType The quantity type the result unit must belong to.
     * @param ?string $resultUnitSymbol The result unit symbol, or null to use the default.
     * @return Unit The resolved Unit object.
     * @throws LogicException If $resultUnitSymbol is null and no default exists for the quantity type.
     * @throws UnknownUnitException If the symbol is not a recognized unit.
     * @throws DimensionMismatchException If the unit is incompatible with the quantity type.
     */
    private static function validateResultUnit(QuantityType $quantityType, ?string $resultUnitSymbol): Unit
    {
        // If null, get the default value if there is one.
        $resultUnitSymbol ??= self::DEFAULT_PARTS_CONFIGS[$quantityType->name]['resultUnitSymbol'] ?? null;

        // Ensure we have a result unit symbol.
        if ($resultUnitSymbol === null) {
            throw new LogicException("No default result unit symbol configured for $quantityType->name quantities.");
        }

        // Get the unit.
        $resultUnit = UnitService::getBySymbol($resultUnitSymbol);
        if ($resultUnit === null) {
            throw new UnknownUnitException($resultUnitSymbol, "Unknown result unit '$resultUnitSymbol'.");
        }

        // Check the result unit is compatible with the quantity type.
        if ($quantityType->dimension !== $resultUnit->dimension) {
            throw new DimensionMismatchException(
                $quantityType->dimension,
                $resultUnit->dimension,
                "Result unit '$resultUnitSymbol' (dimension '$resultUnit->dimension') is incompatible " .
                    "with $quantityType->name quantities (dimension '$quantityType->dimension')."
            );
        }

        return $resultUnit;
    }

    // endregion
}
