<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Services;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Core\Numbers;
use Galaxon\Quantities\Exceptions\UnknownUnitException;
use Galaxon\Quantities\Internal\QuantityType;
use Galaxon\Quantities\Internal\Unit;
use Galaxon\Quantities\Quantity;
use InvalidArgumentException;
use UnexpectedValueException;

/**
 * Service for decomposing quantities into parts and reassembling them.
 *
 * Handles operations like converting 1.5 hours into "1h 30min 0s" and vice versa.
 * Also stores the default part unit symbols and result unit symbols for each quantity type.
 */
class QuantityPartsService
{
    // region Constants

    /**
     * Default parts configurations keyed by quantity type name.
     *
     * @var array<string, array{
     *     partUnitSymbols?: list<string>,
     *     resultUnitSymbol?: string}
     * >
     */
    private const array PARTS_CONFIGS = [
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

    // region Static properties

    /**
     * Mutable parts configurations, initialized from the constant.
     *
     * @var ?array<string, array{
     *     partUnitSymbols?: list<string>,
     *     resultUnitSymbol?: string}
     * >
     */
    private static ?array $partsConfigs = null;

    // endregion

    // region Configuration methods

    /**
     * Reset the parts configurations to their defaults.
     *
     * Primarily intended for test isolation.
     */
    public static function reset(): void
    {
        self::$partsConfigs = null;
    }

    /**
     * Get the default part unit symbols for a quantity type.
     *
     * @param ?QuantityType $quantityType The quantity type.
     * @return ?list<string> The part unit symbols, or null if none configured.
     * @throws DomainException If the quantity type is unregistered.
     */
    public static function getPartUnitSymbols(?QuantityType $quantityType): ?array
    {
        $quantityType = self::validateQuantityType($quantityType);

        self::init();
        assert(self::$partsConfigs !== null);

        return self::$partsConfigs[$quantityType->name]['partUnitSymbols'] ?? null;
    }

    /**
     * Set the default part unit symbols for a quantity type.
     *
     * @param ?QuantityType $quantityType The quantity type.
     * @param ?list<string> $partUnitSymbols The part unit symbols, or null to clear.
     * @throws DomainException If the quantity type is unregistered or the array is empty.
     * @throws InvalidArgumentException If the array contains non-string values.
     */
    public static function setPartUnitSymbols(?QuantityType $quantityType, ?array $partUnitSymbols): void
    {
        $quantityType = self::validateQuantityType($quantityType);

        self::init();
        assert(self::$partsConfigs !== null);

        if ($partUnitSymbols !== null) {
            // Check value is not empty.
            if (empty($partUnitSymbols)) {
                throw new DomainException('The array of part unit symbols must not be empty.');
            }

            // Check all the symbols are strings.
            foreach ($partUnitSymbols as $symbol) {
                if (!is_string($symbol)) {
                    throw new InvalidArgumentException('The array of part unit symbols must contain only strings.');
                }
            }

            $partUnitSymbols = array_values(array_unique($partUnitSymbols));
        }

        $name = $quantityType->name;
        if ($partUnitSymbols === null) {
            unset(self::$partsConfigs[$name]['partUnitSymbols']);
        } else {
            self::$partsConfigs[$name]['partUnitSymbols'] = $partUnitSymbols;
        }
    }

    /**
     * Get the default result unit symbol for a quantity type.
     *
     * @param ?QuantityType $quantityType The quantity type.
     * @return ?string The result unit symbol, or null if none configured.
     * @throws DomainException If the quantity type is unregistered.
     */
    public static function getResultUnitSymbol(?QuantityType $quantityType): ?string
    {
        $quantityType = self::validateQuantityType($quantityType);

        self::init();
        assert(self::$partsConfigs !== null);

        return self::$partsConfigs[$quantityType->name]['resultUnitSymbol'] ?? null;
    }

    /**
     * Set the default result unit symbol for a quantity type.
     *
     * @param ?QuantityType $quantityType The quantity type.
     * @param ?string $resultUnitSymbol The result unit symbol, or null to clear.
     * @throws DomainException If the quantity type is unregistered or the value is an empty string.
     */
    public static function setResultUnitSymbol(?QuantityType $quantityType, ?string $resultUnitSymbol): void
    {
        $quantityType = self::validateQuantityType($quantityType);

        self::init();
        assert(self::$partsConfigs !== null);

        if ($resultUnitSymbol === '') {
            throw new DomainException('The result unit symbol must be null or a unit symbol.');
        }

        $name = $quantityType->name;
        if ($resultUnitSymbol === null) {
            unset(self::$partsConfigs[$name]['resultUnitSymbol']);
        } else {
            self::$partsConfigs[$name]['resultUnitSymbol'] = $resultUnitSymbol;
        }
    }

    // endregion

    // region Part operations

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
     * @return Quantity A new Quantity representing the sum of the parts.
     * @throws InvalidArgumentException If any of the unit symbols are not strings, or any of the values are not
     * numbers.
     * @throws DomainException If the quantity type is unregistered, or the result unit symbol or sign is invalid.
     */
    public static function fromParts(?QuantityType $quantityType, array $parts): Quantity
    {
        $quantityType = self::validateQuantityType($quantityType);

        // Get the default result unit symbol.
        $resultUnitSymbol = self::getResultUnitSymbol($quantityType);

        // Validate the result unit.
        if (empty($resultUnitSymbol)) {
            throw new DomainException('No result unit symbol configured for this quantity type.');
        }
        $resultUnit = UnitService::getBySymbol($resultUnitSymbol);
        if ($resultUnit === null) {
            throw new DomainException(
                "Unknown result unit '$resultUnitSymbol'. Ensure you have loaded the necessary system of " .
                'units using `UnitService::loadBySystem()`.'
            );
        }
        if ($quantityType->dimension !== $resultUnit->dimension) {
            throw new DomainException(
                "Result unit '$resultUnitSymbol' is incompatible with " . $quantityType->name . ' quantities.'
            );
        }

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
     * @return array<string, int|float> Array of parts, plus the sign (1 or -1).
     * @throws DomainException If any arguments are invalid.
     * @throws InvalidArgumentException If any of the unit symbols are not strings.
     */
    public static function toParts(Quantity $quantity, ?int $precision = null): array
    {
        $quantityType = self::validateQuantityType($quantity->quantityType);

        // Get the part unit symbols for this quantity type.
        $partUnitSymbols = self::getPartUnitSymbols($quantityType);

        // Validate args.
        $partUnits = self::validatePartUnitSymbols($partUnitSymbols);
        self::validatePrecision($precision);

        // Initialize the result array.
        $parts = [
            'sign' => Numbers::sign($quantity->value, false),
        ];

        // Initialize the remainder to the source value converted to the smallest unit.
        $nUnits = count($partUnitSymbols);
        $smallestUnitSymbol = $partUnitSymbols[$nUnits - 1];
        $rem = abs($quantity->to($smallestUnitSymbol)->value);

        // Get the integer parts.
        for ($i = 0; $i < $nUnits - 1; $i++) {
            // Get the number of current units in the smallest unit.
            $partUnitSymbol = $partUnitSymbols[$i];
            $factor = Quantity::convert(1, $partUnits[$i], $smallestUnitSymbol);
            $wholeNumCurUnit = (int)floor($rem / $factor);
            $parts[$partUnitSymbol] = $wholeNumCurUnit;
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
        $rebuiltParts = self::toParts($rebuilt);
        $rebuiltParts[$smallestUnitSymbol] = round($rebuiltParts[$smallestUnitSymbol], $precision);
        return $rebuiltParts;
    }

    /**
     * Parse a string of quantity parts.
     *
     * Examples:
     *    - "4y 5mo 6d 12h 34min 56.789s"
     *    - "12° 34′ 56.789″"
     *
     * @param ?QuantityType $quantityType The quantity type.
     * @param string $input The string to parse.
     * @return Quantity A new Quantity representing the sum of the parts.
     * @throws DomainException If the quantity type is unregistered.
     * @throws FormatException If the input string is invalid.
     * @throws UnexpectedValueException If there is an unexpected error during parsing.
     * @throws InvalidArgumentException If any of the unit symbols are not strings.
     */
    public static function parseParts(?QuantityType $quantityType, string $input): Quantity
    {
        $quantityType = self::validateQuantityType($quantityType);

        // Ensure the input string is not empty.
        $input = trim($input);
        if ($input === '') {
            throw new FormatException('The input string is empty.');
        }

        // Get the default result unit symbol.
        $resultUnitSymbol = self::getResultUnitSymbol($quantityType);

        // Validate the result unit.
        if (empty($resultUnitSymbol)) {
            throw new DomainException('No result unit symbol configured for this quantity type.');
        }
        $name = ' ' . $quantityType->name;
        $err = "The provided string '$input' does not represent a valid$name quantity.";

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
            if (!RegexService::isValidQuantity($stringPart, $m) || empty($m[2])) {
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
        return self::fromParts($quantityType, $parts);
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
     * @return string The formatted string.
     * @throws DomainException If the quantity type is unregistered.
     * @throws InvalidArgumentException If any of the unit symbols are not strings.
     */
    public static function formatParts(
        Quantity $quantity,
        ?int $precision = null,
        bool $showZeros = false,
        bool $ascii = false
    ): string {

        $quantityType = self::validateQuantityType($quantity->quantityType);

        // Get the quantity as parts. This will validate the arguments.
        $parts = self::toParts($quantity, $precision);

        // Get the default part unit symbols.
        $partUnitSymbols = self::getPartUnitSymbols($quantityType);
        assert(is_array($partUnitSymbols));
        $nUnits = count($partUnitSymbols);

        // Prep.
        $result = [];

        // Generate string as parts for all but the smallest unit.
        for ($i = 0; $i < $nUnits - 1; $i++) {
            $symbol = $partUnitSymbols[$i];
            $unit = UnitService::getBySymbol($symbol);
            assert($unit instanceof Unit);
            $value = $parts[$symbol] ?? 0;

            // Skip zeros if requested.
            if (Numbers::equal($value, 0) && !$showZeros) {
                continue;
            }

            // Format the part with no space between the value and unit.
            $result[] = $value . $unit->format($ascii);
        }

        // Add the smallest unit.
        $symbol = $partUnitSymbols[$nUnits - 1];
        $unit = UnitService::getBySymbol($symbol);
        assert($unit instanceof Unit);
        $value = $parts[$symbol] ?? 0;
        $roundedValue = $precision === null ? $value : round($value, $precision);

        // Skip unless we're showing zeros or the value is non-zero.
        if ($showZeros || $roundedValue !== 0.0 || empty($result)) {
            $valueStr = Quantity::formatValue($value, 'f', $precision, null, $ascii);
            $result[] = $valueStr . $unit->format($ascii);
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
     * @throws DomainException If the quantity type is null.
     */
    private static function validateQuantityType(?QuantityType $quantityType): QuantityType
    {
        if ($quantityType === null) {
            throw new DomainException('Cannot call parts methods on an unregistered quantity type.');
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
            throw new DomainException(
                "Invalid precision specified; $precision. Must be null or a non-negative integer."
            );
        }
    }

    /**
     * Validate and transform the part units array into a list of Units.
     *
     * @param ?list<string> $symbols The part unit symbols to validate and transform.
     * @param-out list<string> $symbols The validated and transformed part unit symbols.
     * @return list<Unit> The part units.
     * @throws InvalidArgumentException If any of the unit symbols are not strings.
     * @throws UnknownUnitException If a unit symbol is not recognized.
     * @throws DomainException If the array is empty.
     */
    private static function validatePartUnitSymbols(?array &$symbols): array
    {
        // Ensure we have some part units.
        if (empty($symbols)) {
            throw new DomainException('The array of part unit symbols must not be empty.');
        }

        // Ignore keys and duplicates.
        $symbols = array_values(array_unique($symbols));

        // Create a new array to contain the list of Unit objects.
        $partUnits = [];

        // Validate each part unit symbol.
        foreach ($symbols as $partUnitSymbol) {
            // Check the type.
            if (!is_string($partUnitSymbol)) {
                // @codeCoverageIgnoreStart
                throw new InvalidArgumentException('The array of part unit symbols must contain only strings.');
                // @codeCoverageIgnoreEnd
            }

            // Get the unit.
            $partUnit = UnitService::getBySymbol($partUnitSymbol);
            if ($partUnit === null) {
                throw new UnknownUnitException(
                    $partUnitSymbol,
                    "Unknown unit: '$partUnitSymbol'. Ensure you have loaded the necessary system " .
                    'of units using `UnitService::loadBySystem()`.'
                );
            }

            $partUnits[] = $partUnit;
        }

        return $partUnits;
    }

    // endregion

    // region Private methods

    /**
     * Initialize the parts configurations from the constant.
     *
     * This is called lazily on first access.
     */
    private static function init(): void
    {
        if (self::$partsConfigs === null) {
            self::$partsConfigs = self::PARTS_CONFIGS;
        }
    }

    // endregion
}
