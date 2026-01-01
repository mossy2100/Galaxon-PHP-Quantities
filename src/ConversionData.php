<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use ValueError;

class ConversionData
{
    // region Static properties

    /**
     * All known/supported conversions including defaults and custom.
     *
     * @var array<string, list<Conversion>>|null
     */
    private static ?array $conversions = null;

    // endregion

    // region Static methods

    /**
     * Initialize the conversions array from registered QuantityType classes.
     *
     * This is called lazily on first access.
     */
    private static function initConversions(): void
    {
        if (self::$conversions === null) {
            self::$conversions = [];

            // Collect conversions from all registered QuantityType classes.
            foreach (QuantityTypes::getAll() as $dimension => $data) {
                $class = $data['class'] ?? null;
                if ($class === null) {
                    continue;
                }

                // Check if the class has a getConversions() method.
                if (!method_exists($class, 'getConversions')) {
                    continue;
                }

                // Get the conversions from the class.
                /** @var list<array{string, string, float}> $conversionList */
                $conversionList = $class::getConversions();

                // Create the conversions array for the dimension.
                if (!array_key_exists($dimension, self::$conversions)) {
                    self::$conversions[$dimension] = [];
                }

                // Add the conversions to the array.
                foreach ($conversionList as $conversion) {
                    [$srcUnitSymbol, $destUnitSymbol, $factor] = $conversion;
                    self::$conversions[$dimension][] =
                        new Conversion($dimension, $srcUnitSymbol, $destUnitSymbol, $factor);
                }
            }
        }
    }

    /**
     * Get the conversions for a given dimension.
     *
     * @return list<Conversion>
     */
    public static function getConversions(string $dimension): array
    {
        self::initConversions();
        return self::$conversions[$dimension];
    }

    private static function updatePrep(string|UnitTerm $srcUnitTerm, string|UnitTerm $destUnitTerm)
    {
        self::initConversions();

        // Convert the source unit term symbol to a UnitTerm object, if necessary.
        if (is_string($srcUnitTerm)) {
            $srcUnitTerm = UnitTerm::parse($srcUnitTerm);
        }

        // Convert the destination unit term symbol to a UnitTerm object, if necessary.
        if (is_string($destUnitTerm)) {
            $destUnitTerm = UnitTerm::parse($destUnitTerm);
        }

        // Check the dimension codes align.
        $srcDim = $srcUnitTerm->dimension;
        $destDim = $destUnitTerm->dimension;
        if ($srcDim !== $destDim) {
            throw new ValueError("No conversion may exist between '$srcUnitTerm' and '$destUnitTerm' because they have different dimensions.");
        }

        return [$srcDim, $srcUnitTerm, $destUnitTerm];
    }

    /**
     * Add or update a conversion definition.
     *
     * @param string|UnitTerm $srcUnitTerm The source unit term symbol or instance.
     * @param string|UnitTerm $destUnitTerm The destination unit term symbol or instance.
     * @param float $factor The scale factor (cannot be 0).
     * @throws ValueError If the factor is zero.
     */
    public static function addConversion(
        string|UnitTerm $srcUnitTerm,
        string|UnitTerm $destUnitTerm,
        float $factor
    ): void
    {
        [$dim, $srcUnitTerm, $destUnitTerm] = self::updatePrep($srcUnitTerm, $destUnitTerm);

        // Ensure multiplier is not zero.
        if ($factor === 0.0) {
            throw new ValueError('Multiplier cannot be zero.');
        }

        // Construct the new Conversion.
        $newConversion = new Conversion($dim, $srcUnitTerm, $destUnitTerm, $factor);

        // Ensure the dimension exists in the conversions array.
        if (!array_key_exists($dim, self::$conversions)) {
            self::$conversions[$dim] = [];
        }

        // Search for the conversion and update if found.
        foreach (self::$conversions[$dim] as $i => $conversion) {
            if ($conversion->srcUnitTerm->equal($srcUnitTerm) && $conversion->destUnitTerm->equal($destUnitTerm)) {
                self::$conversions[$dim][$i] = $newConversion;
                return;
            }
        }

        // Add the new conversion.
        self::$conversions[$dim][] = $newConversion;
    }

    /**
     * Remove a conversion definition.
     *
     * @param string|UnitTerm $srcUnitTerm The source unit term symbol or instance.
     * @param string|UnitTerm $destUnitTerm The destination unit term symbol or instance.
     * @throws ValueError If a unit term is provided as a string and it's invalid.
     */
    public static function removeConversion(string|UnitTerm $srcUnitTerm, string|UnitTerm $destUnitTerm): void
    {
        [$dim, $srcUnitTerm, $destUnitTerm] = self::updatePrep($srcUnitTerm, $destUnitTerm);

        // Search for the conversion and remove if found.
        $conversions = self::getConversions($dim);
        foreach ($conversions as $i => $conversion) {
            if ($conversion->srcUnitTerm->equal($srcUnitTerm) && $conversion->destUnitTerm->equal($destUnitTerm)) {
                // Remove the conversion.
                unset(self::$conversions[$dim][$i]);

                // Renumber the list.
                self::$conversions[$dim] = array_values(self::$conversions[$dim]);

                // We're done.
                return;
            }
        }
    }

    // endregion
}
