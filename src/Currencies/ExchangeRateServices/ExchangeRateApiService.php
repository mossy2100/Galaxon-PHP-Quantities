<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Currencies\ExchangeRateServices;

use DomainException;
use Override;
use RuntimeException;

/**
 * Exchange rate service using the ExchangeRate-API.
 *
 * The free tier supports any base currency with 1,500 requests per month.
 *
 * @see https://www.exchangerate-api.com/
 */
class ExchangeRateApiService implements ExchangeRateServiceInterface
{
    // region Constants

    /**
     * The API endpoint for latest exchange rates.
     *
     * The API key and base currency are appended as path segments.
     */
    private const string API_URL = 'https://v6.exchangerate-api.com/v6';

    // endregion

    // region Properties

    /**
     * The API key for ExchangeRate-API.
     */
    private(set) string $apiKey;

    // endregion

    // region Constructor

    /**
     * Create a new ExchangeRate-API service.
     *
     * After you create an account on ExchangeRate-API, you can find your API key in your dashboard.
     *
     * @param string $apiKey The API key for ExchangeRate-API.
     * @throws DomainException If the API key is empty.
     */
    public function __construct(string $apiKey)
    {
        if (empty($apiKey)) {
            throw new DomainException('API key cannot be empty.');
        }

        $this->apiKey = $apiKey;
    }

    // endregion

    // region Overrides

    #[Override]
    public function getName(): string
    {
        return 'ExchangeRate-API';
    }

    /**
     * Fetch fresh exchange rates from the ExchangeRate-API and generate the conversion definitions.
     *
     * Rates are relative to USD by default.
     *
     * @return list<array{string, string, float}> Currency conversion definitions.
     * @throws RuntimeException If the API request fails or returns invalid data.
     */
    #[Override]
    public function getConversionDefinitions(): array
    {
        $url = self::API_URL . '/' . urlencode($this->apiKey) . '/latest/USD';

        $response = @file_get_contents($url);
        if ($response === false) {
            throw new RuntimeException('Failed to fetch exchange rates from ExchangeRate-API.');
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            throw new RuntimeException('Invalid JSON response from ExchangeRate-API.');
        }

        // Check for API error response.
        if (isset($data['result']) && $data['result'] === 'error') {
            $message = $data['error-type'] ?? 'Unknown error';
            throw new RuntimeException("ExchangeRate-API error: $message");
        }

        if (!isset($data['conversion_rates']) || !is_array($data['conversion_rates'])) {
            throw new RuntimeException('Missing or invalid rates in ExchangeRate-API response.');
        }

        $base = $data['base_code'] ?? 'USD';

        /** @var array<string, float> $rates */
        $rates = $data['conversion_rates'];

        // Convert to conversion definitions.
        $conversionDefinitions = [];
        foreach ($rates as $currencyCode => $rate) {
            $conversionDefinitions[] = [$base, $currencyCode, $rate];
        }
        return $conversionDefinitions;
    }

    // endregion
}
