<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Currencies\ExchangeRateServices;

use RuntimeException;

/**
 * Interface for exchange rate web services such as Open Exchange Rates, etc.
 */
interface ExchangeRateServiceInterface
{
    /**
     * Get the name of the exchange rate service.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Refresh the exchange rates from the web service.
     *
     * This should return an array of conversion definitions, just like Quantity::getConversionDefinitions().
     *
     * @return list<array{string, string, float}>
     * @throws RuntimeException If the API request fails or returns invalid data.
     */
    public function getConversionDefinitions(): array;
}
