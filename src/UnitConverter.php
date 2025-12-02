<?php

declare(strict_types=1);

namespace Galaxon\Units;

use Galaxon\Core\Numbers;
use Galaxon\Core\Types;
use LogicException;
use ValueError;

class UnitConverter
{
    // region Instance properties

    /**
     * The base units for this converter.
     *
     * @var array<string, bool>
     */
    private array $baseUnits;

    /**
     * The prefixes for this converter.
     *
     * @var array<string, int|float>
     */
    private array $prefixes;

    /**
     * The prefixed units.
     *
     * @var array<string, string[]>
     */
    private array $prefixedUnits;

    /**
     * Conversions between units.
     *
     * @var array<string, array<string, Conversion>>
     */
    private array $conversions = [];

    /**
     * The original conversion definitions, for re-importing as needed.
     *
     * @var array<int, array>
     */
    private array $conversionDefinitions = [];

    // endregion

    // region Constructor

    /**
     * Constructor.
     *
     * Validates the Measurement-derived class setup and initializes the unit conversion system.
     *
     * @param array<string, bool> $baseUnits The units with prefix capability flags.
     * @param array<string, int|float> $prefixes The available prefixes and their multipliers.
     * @param array<int, array> $conversionDefinitions The conversion definitions.
     * @throws LogicException If the class is not properly set up.
     */
    public function __construct(array $baseUnits, array $prefixes, array $conversionDefinitions)
    {
        // Validate the units.
        if (empty($baseUnits)) {
            throw new LogicException('Base units must be a non-empty array.');
        }

        // Make sure the units have string keys and boolean values.
        foreach ($baseUnits as $unit => $canHavePrefix) {
            if (!is_string($unit)) {
                throw new LogicException('All units must be strings.');
            }
            if (!is_bool($canHavePrefix)) {
                throw new LogicException('All units must have a boolean value indicating whether they can have a prefix.');
            }
        }

        // Make sure the prefixes have string keys and number values.
        foreach ($prefixes as $prefix => $multiplier) {
            if (!is_string($prefix)) {
                throw new LogicException('All prefixes must be strings.');
            }
            if (!Types::isNumber($multiplier)) {
                throw new LogicException('All prefix multipliers must be numbers (int or float).');
            }
        }

        // Store the units and prefixes first, before generating prefixed units.
        $this->baseUnits = $baseUnits;
        $this->prefixes = $prefixes;

        // Generate the units with prefixes.
        $this->resetUnitsWithPrefixes();

        // Check all conversions have the right structure.
        foreach ($conversionDefinitions as $conversion) {
            // Validate the number of items in the conversion array.
            $nItems = count($conversion);
            if ($nItems < 3 || $nItems > 4) {
                throw new LogicException('Each conversion must have 3 or 4 elements.');
            }

            // Validate the initial unit.
            if (!is_string($conversion[0])) {
                throw new LogicException('Initial unit in conversion must be a string.');
            }
            if (!array_key_exists($conversion[0], $baseUnits)) {
                throw new LogicException("Initial unit '{$conversion[0]}' in conversion is not a base unit. Conversions must only reference base units (without prefixes).");
            }

            // Validate the final unit.
            if (!is_string($conversion[1])) {
                throw new LogicException('Final unit in conversion must be a string.');
            }
            if (!array_key_exists($conversion[1], $baseUnits)) {
                throw new LogicException("Final unit '{$conversion[1]}' in conversion is not a base unit. Conversions must only reference base units (without prefixes).");
            }

            // Validate the multiplier (which must be positive).
            if (!Types::isNumber($conversion[2])) {
                throw new LogicException('Multiplier in conversion must be a number (int or float).');
            }
            if (Numbers::equal($conversion[2], 0)) {
                throw new LogicException('Multiplier in conversion cannot be zero.');
            }

            // Validate the optional offset (which can be negative).
            if ($nItems === 4 && !Types::isNumber($conversion[3])) {
                throw new LogicException('Offset in conversion must be omitted, or a number (int or float).');
            }
        }

        // Store the conversion definitions.
        $this->conversionDefinitions = $conversionDefinitions;

        // Import the conversions.
        $this->resetConversions();
    }

    // endregion

    // region Reset methods

    /**
     * Update the prefixed units, which is an array mapping the prefixed unit to an array with the prefix and the unit.
     *
     * @return void
     */
    public function resetUnitsWithPrefixes(): void
    {
        $this->prefixedUnits = [];
        foreach ($this->baseUnits as $baseUnit => $canHavePrefix) {
            if ($canHavePrefix) {
                foreach ($this->prefixes as $prefix => $factor) {
                    $this->prefixedUnits[$prefix . $baseUnit] = [$prefix, $baseUnit];
                }
            }
        }
    }

    /**
     * Reset the conversion matrix and regenerate it from the current conversion definitions.
     *
     * This is called automatically whenever units, prefixes or conversions are modified.
     *
     * @return void
     */
    private function resetConversions(): void
    {
        // Clear the conversion matrix.
        $this->conversions = [];

        // Initialize the conversion matrix from the supplied conversion definition arrays.
        // Note: Conversion definitions only contain base units (validated in constructor).
        foreach ($this->conversionDefinitions as $conversionDefinition) {
            // Deconstruct the conversion into local variables.
            [$initUnit, $finUnit, $multiplier] = $conversionDefinition;
            // The offset is optional, defaults to 0.
            $offset = $conversionDefinition[3] ?? 0;

            // Create and store the conversion.
            $this->conversions[$initUnit][$finUnit] = new Conversion($initUnit, $finUnit, $multiplier, $offset);
        }
    }

    // endregion

    // region Methods for working with units

    /**
     * Get all valid units including base units and prefixed units.
     *
     * @return string[] List of all valid units.
     */
    public function getValidUnits(): array
    {
        return array_merge(array_keys($this->baseUnits), array_keys($this->prefixedUnits));
    }

    /**
     * Break down a unit into a prefix (if present) and a base unit.
     *
     * @param string $unit The unit to break down into components.
     * @return array<?string> The parts of the unit.
     */
    public function getUnitComponents(string $unit): array
    {
        return $this->prefixedUnits[$unit] ?? [null, $unit];
    }

    /**
     * Validate that a unit is valid, throwing an exception if not.
     *
     * @param string $unit The unit to validate.
     * @return void
     * @throws ValueError If the unit is not valid.
     */
    public function checkUnitIsValid(string $unit): void
    {
        if (!in_array($unit, $this->getValidUnits(), true)) {
            // Generate a useful message.
            $baseUnits = $this->baseUnits;
            $unitsPrefixNotOk = [];
            $unitsPrefixOk = [];
            foreach ($baseUnits as $baseUnit => $canHavePrefix) {
                if ($canHavePrefix === true) {
                    $unitsPrefixOk[] = $baseUnit;
                } else {
                    $unitsPrefixNotOk[] = $baseUnit;
                }
            }

            $wrapQuotes = static fn($unit) => "'$unit'";
            $strUnitsNoPrefix = implode(', ', array_map($wrapQuotes, $unitsPrefixNotOk));
            $strUnitsPrefixOk = implode(', ', array_map($wrapQuotes, $unitsPrefixOk));
            throw new ValueError("Invalid unit '$unit'. Valid units that may have a metric or other " .
                                 "multiplier prefix: $strUnitsPrefixOk. Valid units that may not have a prefix: " .
                                 "$strUnitsNoPrefix.");
        }
    }

    // endregion

    // region Methods for finding and doing conversions

    private static function testNewConversion(
        string $initSearch,
        string $finSearch,
        string $initUnit,
        string $finUnit,
        ?string $commonUnit,
        Conversion $conversion1,
        ?Conversion $conversion2,
        Conversion $newConversion,
        string $operation,
        int &$minErrScore,
        ?array &$best
    ): bool
    {
        // If either unit in the new conversion matches either the initial or final unit we're searching for, reduce
        // the error score by 1 to favour it.
        $newConversionError = $newConversion->error;
        if ($newConversion->initialUnit === $initSearch || $newConversion->initialUnit === $finSearch ||
            $newConversion->finalUnit === $initSearch || $newConversion->finalUnit === $finSearch) {
            --$newConversionError;
        }

        // Let's see if we have a new best.
        if ($newConversionError < $minErrScore) {
            $minErrScore = $newConversion->error;
            $best = [
                'initialUnit'   => $initUnit,
                'finalUnit'     => $finUnit,
                'commonUnit'    => $commonUnit,
                'conversion1'   => $conversion1,
                'conversion2'   => $conversion2,
                'newConversion' => $newConversion,
                'operation'     => $operation,
            ];
            return true;
        }
        return false;
    }

    /**
     * Traverse the matrix to find the next best conversion to add to the matrix and add it, if found.
     *
     * @return bool True if a new conversion was added, false otherwise.
     */
    private function generateNextConversion(string $initSearch, string $finSearch): bool
    {
        $minErrScore = PHP_INT_MAX;
        $best = null;
        $baseUnits = array_keys($this->baseUnits);

        foreach ($baseUnits as $initUnit) {
            foreach ($baseUnits as $finUnit) {
                // If this conversion is already known, continue.
                if ($initUnit === $finUnit || isset($this->conversions[$initUnit][$finUnit])) {
                    continue;
                }

                // Look for the inverse conversion.
                if (isset($this->conversions[$finUnit][$initUnit])) {
                    $conversion = $this->conversions[$finUnit][$initUnit];
                    $newConversion = $conversion->invert();
                    self::testNewConversion($initSearch, $finSearch, $initUnit, $finUnit, null, $conversion, null,
                        $newConversion, 'inversion', $minErrScore, $best);
                }

                // Look for a conversion opportunity via a common unit.
                /** @var string $commonUnit */
                foreach ($baseUnits as $commonUnit) {
                    // The common unit must be different from the initial and final units.
                    if ($initUnit === $commonUnit || $finUnit === $commonUnit) {
                        continue;
                    }

                    // Get conversions between the initial, final, and common unit.
                    $initToCommon = $this->conversions[$initUnit][$commonUnit] ?? null;
                    $commonToInit = $this->conversions[$commonUnit][$initUnit] ?? null;
                    $finToCommon = $this->conversions[$finUnit][$commonUnit] ?? null;
                    $commonToFin = $this->conversions[$commonUnit][$finUnit] ?? null;

                    // Combine initial->common with common->final.
                    if ($initToCommon !== null && $commonToFin !== null) {
                        $newConversion = $initToCommon->combine1($commonToFin);
                        self::testNewConversion($initSearch, $finSearch, $initUnit, $finUnit, $commonUnit, $initToCommon, $commonToFin,
                            $newConversion, 'combination (method 1)', $minErrScore, $best);
                    }

                    // Combine initial->common with final->common.
                    if ($initToCommon !== null && $finToCommon !== null) {
                        $newConversion = $initToCommon->combine2($finToCommon);
                        self::testNewConversion($initSearch, $finSearch, $initUnit, $finUnit, $commonUnit, $initToCommon, $finToCommon,
                            $newConversion, 'combination (method 2)', $minErrScore, $best);
                    }

                    // Combine common->initial with common->final
                    if ($commonToInit !== null && $commonToFin !== null) {
                        $newConversion = $commonToInit->combine3($commonToFin);
                        self::testNewConversion($initSearch, $finSearch, $initUnit, $finUnit, $commonUnit, $commonToInit, $commonToFin,
                            $newConversion, 'combination (method 3)', $minErrScore, $best);
                    }

                    // Combine common->initial with final->common
                    if ($commonToInit !== null && $finToCommon !== null) {
                        $newConversion = $commonToInit->combine4($finToCommon);
                        self::testNewConversion($initSearch, $finSearch, $initUnit, $finUnit, $commonUnit, $commonToInit, $finToCommon,
                            $newConversion, 'combination (method 4)', $minErrScore, $best);
                    }
                }
            }
        }

        if ($best !== null) {
            // Store the best conversion we found for this scan.
            $this->conversions[$best['initialUnit']][$best['finalUnit']] = $best['newConversion'];

            // *********************************************************************************************************
            // DEBUGGING
            $description =
                "New conversion for {$best['initialUnit']} to {$best['finalUnit']} found by {$best['operation']}:\n";
            if ($best['operation'] === 'inversion') {
                $description .=
                    "  Original conversion: {$best['conversion1']}\n" .
                    "       New conversion: {$best['newConversion']}\n";
            } else {
                $description .=
                    "         Conversion 1: {$best['conversion1']}\n" .
                    "         Conversion 2: {$best['conversion2']}\n" .
                    "       New conversion: {$best['newConversion']}\n";
            }
            echo $description, PHP_EOL;
            // *********************************************************************************************************

            return true;
        }

        return false;
    }

    /**
     * Get the conversion factor between two units.
     *
     * @param string $initUnit The initial unit.
     * @param string $finUnit The final unit.
     * @return Conversion The conversion factor.
     * @throws LogicException If no conversion between the units could be found.
     */
    public function getConversion(string $initUnit, string $finUnit): Conversion
    {
        // Handle simple case.
        if ($initUnit === $finUnit) {
            return new Conversion($initUnit, $finUnit, 1);
        }

        // See if we already have this one.
        if (isset($this->conversions[$initUnit][$finUnit])) {
            return $this->conversions[$initUnit][$finUnit];
        }

        // Break down the units into prefixes and base units.
        [$initPrefix, $initBase] = $this->getUnitComponents($initUnit);
        [$finPrefix, $finBase] = $this->getUnitComponents($finUnit);

        if ($initBase === $finBase) {
            // Simply converting between two units with the same base unit. Since they are different, they must have
            // different prefixes, or one has a prefix and one doesn't. Start with the unity conversion.
            $conversion = new Conversion($initBase, $finBase, 1);
        } elseif (isset($this->conversions[$initBase][$finBase])) {
            // Check if the conversion between base units is already known.
            $conversion = $this->conversions[$initBase][$finBase];
        } else {
            // Keep generating new conversions until we find the conversion between the base units, or we run
            // out of options.
            do {
                $result = $this->generateNextConversion($initBase, $finBase);
            } while (!isset($this->conversions[$initBase][$finBase]) && $result);

            // If we didn't find the conversion, throw an exception.
            // This indicates either a problem in the setup of the Measurement-derived class, or the programmer has
            // added or removed needed conversions. So, throw a LogicException.
            if (!isset($this->conversions[$initBase][$finBase])) {
                throw new LogicException("No conversion between '$initUnit' and '$finUnit' could be found.");
            }

            $conversion = $this->conversions[$initBase][$finBase];
        }

        // If there are no prefixes, done.
        if ($initPrefix === null && $finPrefix === null) {
            return $conversion;
        }

        // Apply prefixes.
        $initialPrefixMultiplier = $initPrefix !== null ? $this->prefixes[$initPrefix] : 1.0;
        $finalPrefixMultiplier = $finPrefix !== null ? $this->prefixes[$finPrefix] : 1.0;
        $conversion = $conversion->applyPrefixes($initUnit, $finUnit, $initialPrefixMultiplier, $finalPrefixMultiplier);

        // Cache and return the new conversion.
        $this->conversions[$initUnit][$finUnit] = $conversion;
        return $conversion;
    }

    /**
     * Convert a value from one unit to another.
     *
     * @param float $value The value to convert.
     * @param string $initUnit The initial unit.
     * @param string $finUnit The final unit.
     * @return float The converted value.
     * @throws ValueError If either unit is invalid.
     * @throws LogicException If no conversion between the units could be found.
     */
    public function convert(float $value, string $initUnit, string $finUnit): float
    {
        // Check units are valid.
        $this->checkUnitIsValid($initUnit);
        $this->checkUnitIsValid($finUnit);

        // Get the conversion.
        $conversion = $this->getConversion($initUnit, $finUnit);

        // Convert the value. y = mx + k
        return $value * $conversion->multiplier->value + $conversion->offset->value;
    }

    // endregion

    // region Dynamic modification methods

    /**
     * Add or update a unit.
     *
     * @param string $unit The unit symbol.
     * @param bool $canHavePrefix Whether the unit can have a prefix (default false).
     * @return void
     */
    public function addBaseUnit(string $unit, bool $canHavePrefix = false): void
    {
        $this->baseUnits[$unit] = $canHavePrefix;
        $this->resetUnitsWithPrefixes();
        $this->resetConversions();
    }

    /**
     * Remove a unit.
     *
     * @param string $unit The unit symbol to remove.
     * @return void
     */
    public function removeBaseUnit(string $unit): void
    {
        unset($this->baseUnits[$unit]);
        $this->resetUnitsWithPrefixes();
        $this->resetConversions();
    }

    /**
     * Add or update a prefix.
     *
     * @param string $prefix The prefix symbol.
     * @param int|float $multiplier The multiplier for this prefix.
     * @return void
     */
    public function addPrefix(string $prefix, int|float $multiplier): void
    {
        $this->prefixes[$prefix] = $multiplier;
        $this->resetUnitsWithPrefixes();
        $this->resetConversions();
    }

    /**
     * Remove a prefix.
     *
     * @param string $prefix The prefix symbol to remove.
     * @return void
     */
    public function removePrefix(string $prefix): void
    {
        unset($this->prefixes[$prefix]);
        $this->resetUnitsWithPrefixes();
        $this->resetConversions();
    }

    /**
     * Add or update a conversion between two units.
     *
     * @param string $initUnit The initial unit.
     * @param string $finUnit The final unit.
     * @param int|float $multiplier The multiplier.
     * @param int|float $offset The offset (default 0).
     * @return void
     */
    public function addConversion(string $initUnit, string $finUnit, int|float $multiplier, int|float $offset = 0): void
    {
        // Find if this conversion already exists.
        /** @var null|string $key */
        $key = array_find_key(
            $this->conversionDefinitions,
            static fn($conversion) => $conversion[0] === $initUnit && $conversion[1] === $finUnit
        );

        if ($key !== null) {
            // Update existing conversion.
            $this->conversionDefinitions[$key][2] = $multiplier;
            $this->conversionDefinitions[$key][3] = $offset;
        } else {
            // Add new conversion.
            $this->conversionDefinitions[] = [$initUnit, $finUnit, $multiplier, $offset];
        }

        $this->resetConversions();
    }

    /**
     * Remove a conversion between two units.
     *
     * @param string $initUnit The initial unit.
     * @param string $finUnit The final unit.
     * @return void
     */
    public function removeConversion(string $initUnit, string $finUnit): void
    {
        $this->conversionDefinitions = array_filter(
            $this->conversionDefinitions,
            static fn($conversion) => !($conversion[0] === $initUnit && $conversion[1] === $finUnit)
        );
        $this->resetConversions();
    }

    // endregion

    // region Matrix-level methods

    /**
     * Traverse the matrix repeatedly using a best-first search until all the positions are filled or we run out of
     * options.
     *
     * NB: This method may be removed. For now, we are exiting the loop in getConversion() as soon as the desired one
     * is found. Generating a complete matrix of conversions could be inefficient for some measurement types and
     * unlikely to be necessary for most use cases.
     *
     * @return void
     */
    public function completeMatrix()
    {
        do {
            $result = $this->generateNextConversion();
        } while ($result);
    }

    /**
     * Check if the conversion matrix is complete (all conversions between base units are known).
     *
     * @return bool True if complete, false otherwise.
     */
    public function isMatrixComplete(): bool
    {
        $baseUnits = array_keys($this->baseUnits);
        foreach ($baseUnits as $initUnit) {
            foreach ($baseUnits as $finUnit) {
                if (!isset($this->conversions[$initUnit][$finUnit])) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Print the conversion matrix for debugging purposes.
     *
     * @return void
     */
    public function printMatrix()
    {
        $colWidth = 20;
        $baseUnits = array_keys($this->baseUnits);

        echo "+------+";
        foreach ($baseUnits as $baseUnit) {
            echo str_repeat('-', $colWidth) . '+';
        }
        echo "\n";

        echo "|      |";
        foreach ($baseUnits as $baseUnit) {
            echo str_pad($baseUnit, $colWidth, ' ', STR_PAD_BOTH) . "|";
        }
        echo "\n";

        echo "+------+";
        foreach ($baseUnits as $baseUnit) {
            echo str_repeat('-', $colWidth) . '+';
        }
        echo "\n";

        foreach ($baseUnits as $initUnit) {
            echo "|" . str_pad($initUnit, 6) . "|";
            foreach ($baseUnits as $finUnit) {
                if (isset($this->conversions[$initUnit][$finUnit])) {
                    $mult = $this->conversions[$initUnit][$finUnit]->multiplier->value;
                    $strMult = sprintf('%.10g', $mult);
                    echo str_pad($strMult, $colWidth);
                } else {
                    echo str_pad('?', $colWidth);
                }
                echo "|";
            }
            echo "\n";
        }

        echo "+------+";
        foreach ($baseUnits as $baseUnit) {
            echo str_repeat('-', $colWidth) . '+';
        }
        echo "\n";
    }

    /**
     * Dump the conversion matrix contents for debugging purposes.
     *
     * @return void
     */
    public function dumpMatrix()
    {
        echo "\n";
        echo "CONVERSION MATRIX\n";
        foreach ($this->conversions as $initBase => $conversions) {
            foreach ($conversions as $finBase => $conversion) {
                echo "$conversion\n";
            }
        }
        echo "\n";
        echo "\n";
    }

    // endregion
}
