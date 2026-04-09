<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Currencies\ExchangeRateServices;

use DomainException;
use Override;
use RuntimeException;

/**
 * Exchange rate service using the Fixer.io API.
 *
 * The free tier provides EUR-based rates only, with 10,000 requests per month.
 *
 * @see https://fixer.io/
 */
class FixerService implements ExchangeRateServiceInterface
{
    // region Private constants

    /**
     * The API endpoint for latest exchange rates.
     */
    private const string API_URL = 'https://data.fixer.io/api/latest';

    // endregion

    // region Public properties

    /**
     * The access key for Fixer.io.
     */
    private(set) string $accessKey;

    // endregion

    // region Constructor

    /**
     * Create a new Fixer.io service.
     *
     * @param string $accessKey The access key for Fixer.io.
     * @throws DomainException If the access key is empty.
     */
    public function __construct(string $accessKey)
    {
        if (empty($accessKey)) {
            throw new DomainException('Access key cannot be empty.');
        }

        $this->accessKey = $accessKey;
    }

    // endregion

    // region Overrides

    /**
     * Human-readable name of this exchange rate service.
     */
    #[Override]
    public function getName(): string
    {
        return 'Fixer.io';
    }

    /**
     * Fetch fresh exchange rates from the Fixer.io API and generate the conversion definitions.
     *
     * Rates are relative to EUR on the free tier.
     *
     * @return list<array{string, string, float}> Currency conversion definitions.
     * @throws RuntimeException If the API request fails or returns invalid data.
     */
    #[Override]
    public function getConversionDefinitions(): array
    {
        $url = self::API_URL . '?access_key=' . urlencode($this->accessKey);

        $response = @file_get_contents($url);
        if ($response === false) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Failed to fetch exchange rates from Fixer.io API.');
            // @codeCoverageIgnoreEnd
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Invalid JSON response from Fixer.io API.');
            // @codeCoverageIgnoreEnd
        }

        // Check for API error response.
        if (isset($data['success']) && $data['success'] === false) {
            // @codeCoverageIgnoreStart
            $message = is_array($data['error']) && isset($data['error']['info']) && is_string($data['error']['info'])
                ? $data['error']['info']
                : 'Unknown error';
            throw new RuntimeException("Fixer.io API error: $message");
            // @codeCoverageIgnoreEnd
        }

        if (!isset($data['rates']) || !is_array($data['rates'])) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Missing or invalid rates in Fixer.io API response.');
            // @codeCoverageIgnoreEnd
        }

        $base = isset($data['base']) && is_string($data['base']) ? $data['base'] : 'EUR';

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
