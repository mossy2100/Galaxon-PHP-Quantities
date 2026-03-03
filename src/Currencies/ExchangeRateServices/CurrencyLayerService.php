<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Currencies\ExchangeRateServices;

use DomainException;
use Override;
use RuntimeException;

/**
 * Exchange rate service using the CurrencyLayer API.
 *
 * The free tier provides USD-based rates only, with 1,000 requests per month.
 *
 * @see https://currencylayer.com/
 */
class CurrencyLayerService implements ExchangeRateServiceInterface
{
    // region Constants

    /**
     * The API endpoint for latest exchange rates.
     */
    private const string API_URL = 'https://api.currencylayer.com/live';

    /**
     * The length of the source currency code prefix in quote keys.
     *
     * CurrencyLayer returns quote keys like "USDEUR", "USDGBP", etc.
     * The target currency code starts at this offset.
     */
    private const int SOURCE_CODE_LENGTH = 3;

    // endregion

    // region Properties

    /**
     * The access key for CurrencyLayer.
     */
    private(set) string $accessKey;

    // endregion

    // region Constructor

    /**
     * Create a new CurrencyLayer service.
     *
     * @param string $accessKey The access key for CurrencyLayer.
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

    #[Override]
    public function getName(): string
    {
        return 'CurrencyLayer';
    }

    /**
     * Fetch fresh exchange rates from the CurrencyLayer API and generate the conversion definitions.
     *
     * Rates are relative to USD on the free tier. Quote keys use concatenated currency codes
     * (e.g. "USDEUR"), so the target code is extracted by stripping the 3-character source prefix.
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
            throw new RuntimeException('Failed to fetch exchange rates from CurrencyLayer API.');
            // @codeCoverageIgnoreEnd
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Invalid JSON response from CurrencyLayer API.');
            // @codeCoverageIgnoreEnd
        }

        // Check for API error response.
        if (isset($data['success']) && $data['success'] === false) {
            // @codeCoverageIgnoreStart
            $message = $data['error']['info'] ?? 'Unknown error';
            throw new RuntimeException("CurrencyLayer API error: $message");
            // @codeCoverageIgnoreEnd
        }

        if (!isset($data['quotes']) || !is_array($data['quotes'])) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Missing or invalid quotes in CurrencyLayer API response.');
            // @codeCoverageIgnoreEnd
        }

        $base = $data['source'] ?? 'USD';

        /** @var array<string, float> $quotes */
        $quotes = $data['quotes'];

        // Convert to conversion definitions.
        // Quote keys are concatenated like "USDEUR", so strip the source code to get the target.
        $conversionDefinitions = [];
        foreach ($quotes as $quoteKey => $rate) {
            $targetCode = substr($quoteKey, self::SOURCE_CODE_LENGTH);
            $conversionDefinitions[] = [$base, $targetCode, $rate];
        }
        return $conversionDefinitions;
    }

    // endregion
}
