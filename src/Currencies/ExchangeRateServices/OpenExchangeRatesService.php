<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Currencies\ExchangeRateServices;

use DomainException;
use Override;
use RuntimeException;

/**
 * Exchange rate service using the Open Exchange Rates API.
 *
 * The free tier provides USD-based rates only.
 *
 * @see https://openexchangerates.org/
 */
class OpenExchangeRatesService implements ExchangeRateServiceInterface
{
    // region Constants

    /**
     * The API endpoint for latest exchange rates.
     */
    private const string API_URL = 'https://openexchangerates.org/api/latest.json';

    // endregion

    // region Properties

    /**
     * The App ID for Open Exchange Rates.
     */
    private(set) string $appId;

    // endregion

    // region Constructor

    /**
     * Create a new Open Exchange Rates service.
     *
     * After you create an account on Open Exchange Rates, you can find your App ID here:
     * https://openexchangerates.org/account/app-ids
     *
     * @param string $appId The App ID for Open Exchange Rates.
     * @throws DomainException If the App ID is empty.
     */
    public function __construct(string $appId)
    {
        if (empty($appId)) {
            throw new DomainException('App ID cannot be empty.');
        }

        $this->appId = $appId;
    }

    // endregion

    // region Overrides

    #[Override]
    public function getName(): string
    {
        return 'Open Exchange Rates';
    }

    /**
     * Fetch fresh exchange rates from the API and generate the conversion definitions.
     *
     * @return list<array{string, string, float}> Currency conversion definitions.
     * @throws RuntimeException If the API request fails or returns invalid data.
     */
    #[Override]
    public function getConversionDefinitions(): array
    {
        $url = self::API_URL . '?app_id=' . urlencode($this->appId);

        $response = @file_get_contents($url);
        if ($response === false) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Failed to fetch exchange rates from Open Exchange Rates API.');
            // @codeCoverageIgnoreEnd
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Invalid JSON response from Open Exchange Rates API.');
            // @codeCoverageIgnoreEnd
        }

        // Check for API error response.
        if (isset($data['error']) && $data['error'] === true) {
            // @codeCoverageIgnoreStart
            $message = $data['message'] ?? 'Unknown error';
            throw new RuntimeException("Open Exchange Rates API error: $message");
            // @codeCoverageIgnoreEnd
        }

        if (!isset($data['rates']) || !is_array($data['rates'])) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Missing or invalid rates in Open Exchange Rates API response.');
            // @codeCoverageIgnoreEnd
        }

        /** @var array<string, float> $rates */
        $rates = $data['rates'];

        // Convert to conversion definitions.
        $conversionDefinitions = [];
        foreach ($rates as $currencyCode => $rate) {
            $conversionDefinitions[] = ['USD', $currencyCode, $rate];
        }
        return $conversionDefinitions;
    }

    // endregion
}
