<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Registry;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Quantities\Conversion;
use Galaxon\Quantities\UnitInterface;

/**
 * Registry for unit conversions.
 *
 * Stores and retrieves conversions between units, organized by dimension.
 * Conversions are loaded lazily from QuantityType classes on first access.
 */
class ConversionRegistry
{
    // region Static properties

    /**
     * Conversion matrix storing known conversions between units.
     *
     * Structure: $conversions[$dimension][$srcSymbol][$destSymbol] = Conversion
     *
     * @var ?array<string, array<string, array<string, Conversion>>>
     */
    private static ?array $conversions = null;

    // endregion

    // region Static methods

    /**
     * Initialize the conversions array from the QuantityType class corresponding to a given dimension.
     *
     * This is called lazily on first access.
     *
     * @param string $dimension
     * @throws DomainException If dimensions don't match or factor is not positive.
     */
    private static function init(string $dimension): void
    {
        if (!isset(self::$conversions[$dimension])) {
            self::$conversions[$dimension] = [];

            // Find the relevant QuantityType.
            $qtyType = QuantityTypeRegistry::getByDimension($dimension);

            // Check we have a class with a getConversions() method to call.
            if ($qtyType->class === null || !method_exists($qtyType->class, 'getConversions')) {
                return;
            }

            // Get conversions from the class and add them.
            /** @var list<array{string, string, float}> $conversionList */
            $conversionList = $qtyType->class::getConversions();
            foreach ($conversionList as [$srcSymbol, $destSymbol, $factor]) {
                self::addConversion($srcSymbol, $destSymbol, $factor);
            }

            // Also include any expansions.
            $units = UnitRegistry::getByDimension($dimension);
            foreach ($units as $unit) {
                if ($unit->hasExpansion()) {
                    self::addConversion($unit, $unit->expansionUnitSymbol, $unit->expansionValue);
                }
            }
        }
    }

    private static function addConversion(
        string|UnitInterface $srcUnit,
        string|UnitInterface $destUnit,
        float $factor
    ): void {
        // Construct the new Conversion.
        $conversion = new Conversion($srcUnit, $destUnit, $factor);

        // Add the Conversion to the registry.
        self::add($conversion);

        // If prefixes are present, also add the unprefixed conversion.
        if ($conversion->srcUnit->hasPrefixes() || $conversion->destUnit->hasPrefixes()) {
            self::add($conversion->removePrefixes());
        }
    }

    /**
     * Get all conversions matching a given dimension.
     *
     * @param string $dimension The dimension code.
     * @return array<string, array<string, Conversion>>
     * @throws FormatException If the dimension code is invalid.
     * @throws DomainException If any conversion definitions are invalid.
     */
    public static function getByDimension(string $dimension): array
    {
        // Check the dimension code is valid.
        if (!DimensionRegistry::isValid($dimension)) {
            throw new FormatException("Invalid dimension code '$dimension'.");
        }

        // Load the conversion for this dimension.
        self::init($dimension);

        // Check if we have it.
        $dimension = DimensionRegistry::normalize($dimension);
        return self::$conversions[$dimension] ?? [];
    }

    /**
     * Check if a conversion exists between two units.
     *
     * @param string $dimension The conversion dimension.
     * @param string $srcUnitSymbol The source unit symbol.
     * @param string $destUnitSymbol The destination unit symbol.
     * @return bool If the conversion exists in the registry.
     */
    public static function has(string $dimension, string $srcUnitSymbol, string $destUnitSymbol): bool
    {
        return isset(self::$conversions[$dimension][$srcUnitSymbol][$destUnitSymbol]);
    }

    /**
     * Get a conversion between two units.
     *
     * @param string $dimension The conversion dimension.
     * @param string $srcUnitSymbol The source unit symbol.
     * @param string $destUnitSymbol The destination unit symbol.
     * @return ?Conversion The conversion, or null if not found.
     */
    public static function get(string $dimension, string $srcUnitSymbol, string $destUnitSymbol): ?Conversion
    {
        return self::$conversions[$dimension][$srcUnitSymbol][$destUnitSymbol] ?? null;
    }

    /**
     * Add a conversion to the registry.
     *
     * @param Conversion $conversion The conversion to add.
     */
    public static function add(Conversion $conversion): void
    {
        $dim = $conversion->dimension;
        $src = $conversion->srcUnit->asciiSymbol;
        $dest = $conversion->destUnit->asciiSymbol;
        self::$conversions[$dim][$src][$dest] = $conversion;
    }

    // endregion
}
