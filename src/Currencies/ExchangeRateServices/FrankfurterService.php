<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Currencies\ExchangeRateServices;

use Override;
use RuntimeException;

/**
 * Exchange rate service using the Frankfurter API, backed by European Central Bank data.
 *
 * Completely free with no API key required. Rates are updated daily around 16:00 CET.
 *
 * @see https://frankfurter.dev/
 */
class FrankfurterService implements ExchangeRateServiceInterface
{
    // region Constants

    /**
     * The API endpoint for latest exchange rates.
     */
    private const string API_URL = 'https://api.frankfurter.dev/v1/latest';

    // endregion

    // region Overrides

    #[Override]
    public function getName(): string
    {
        return 'Frankfurter (ECB)';
    }

    /**
     * Fetch fresh exchange rates from the Frankfurter API and generate the conversion definitions.
     *
     * Rates are relative to EUR (the ECB's base currency).
     *
     * @return list<array{string, string, float}> Currency conversion definitions.
     * @throws RuntimeException If the API request fails or returns invalid data.
     */
    #[Override]
    public function getConversionDefinitions(): array
    {
        $response = @file_get_contents(self::API_URL);
        if ($response === false) {
            throw new RuntimeException('Failed to fetch exchange rates from Frankfurter API.');
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            throw new RuntimeException('Invalid JSON response from Frankfurter API.');
        }

        if (!isset($data['rates']) || !is_array($data['rates'])) {
            throw new RuntimeException('Missing or invalid rates in Frankfurter API response.');
        }

        $base = $data['base'] ?? 'EUR';

        /** @var array<string, float> $rates */
        $rates = $data['rates'];

        // Convert to conversion definitions.
        $conversionDefinitions = [];
        foreach ($rates as $currencyCode => $rate) {
            $conversionDefinitions[] = [$base, $currencyCode, $rate];
        }
        return $conversionDefinitions;
    }

    // endregion
}
