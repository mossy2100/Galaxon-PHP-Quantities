<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Currencies\CurrencyService;
use Galaxon\Quantities\Internal\UnitSystem;
use Galaxon\Quantities\Quantity;
use LogicException;
use NumberFormatter;
use Override;
use RuntimeException;

/**
 * Represents money quantities.
 */
class Money extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for currencies.
     *
     * @throws RuntimeException
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        $unitData = CurrencyService::getUnits();
        $currencies = $unitData['currencies'] ?? [];
        return array_map(static fn ($code) => [
            'asciiSymbol' => $code,
            'systems'     => [UnitSystem::Financial],
        ], $currencies);
    }

    /**
     * Conversion factors for currency units.
     *
     * @return list<array{string, string, float}>
     * @throws LogicException If the exchange rate service is not configured.
     * @throws RuntimeException If a fetch is required but the exchange rate service fails or the data directory
     * cannot be created.
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        $conversionData = CurrencyService::getConversions();
        return $conversionData['definitions'] ?? [];
    }

    /**
     * Convert the currency value to a string.
     *
     * For additional formatting options,
     * @see Quantity::format()
     *
     * @return string
     */
    #[Override]
    public function __toString(): string
    {
        // By default, use the locale-specific currency formatter.
        $locale = CurrencyService::getLocale();
        if ($locale !== null) {
            $fmt = new NumberFormatter($locale, NumberFormatter::CURRENCY);
            $result = $fmt->formatCurrency($this->value, (string)$this->compoundUnit);
            if ($result !== false) {
                return $result;
            }
        }

        // Fall back to format().
        return $this->format(); // @codeCoverageIgnore
    }

    // endregion
}
