<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Services\CurrencyService;
use Galaxon\Quantities\UnitSystem;
use NumberFormatter;
use Override;

/**
 * Represents currency quantities.
 */
class Currency extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for currencies.
     *
     * @return array<string, array{
     *     asciiSymbol: string,
     *     unicodeSymbol?: string,
     *     prefixGroup?: int,
     *     alternateSymbol?: string,
     *     systems: list<UnitSystem>
     * }>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        $unitData = CurrencyService::loadUnitData();
        return $unitData['definitions'] ?? [];
    }

    /**
     * Conversion factors for currency units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        $conversionData = CurrencyService::loadConversionData();
        return $conversionData['definitions'] ?? [];
    }

    /**
     * Convert the currency value to a string.
     *
     * For the normal format options, use format().
     *
     * @return string
     */
    #[Override]
    public function __toString(): string
    {
        $locale = CurrencyService::getLocale();

        if ($locale !== null) {
            $fmt = new NumberFormatter($locale, NumberFormatter::CURRENCY);
            $result = $fmt->formatCurrency($this->value, (string)$this->derivedUnit);
            if ($result !== false) {
                return $result;
            }
        }

        // Fall back to usual format() method.
        return $this->format();
    }

    // endregion
}
