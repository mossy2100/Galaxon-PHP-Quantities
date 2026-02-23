<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Services;

use DateTime;
use Galaxon\Quantities\Currencies\ExchangeRateServices\ExchangeRateServiceInterface;
use Galaxon\Quantities\QuantityType\Currency;
use Galaxon\Quantities\UnitSystem;
use Locale;
use LogicException;
use RuntimeException;
use SimpleXMLElement;

/**
 * Service for managing currency units and exchange rate conversions.
 */
class CurrencyService
{
    // region Constants

    /**
     * The URL for the official ISO 4217 XML published by SIX Group.
     */
    private const string ISO_4217_URL =
        'https://www.six-group.com/dam/download/financial-information/data-center/iso-currrency/lists/list-one.xml';

    /**
     * The path to the downloaded currencies XML data file.
     */
    private const string CURRENCIES_XML_FILE = __DIR__ . '/../Currencies/Data/CurrencyData.xml';

    /**
     * The path to the generated Currency unit definitions file.
     */
    public const string CURRENCY_UNITS_FILE = __DIR__ . '/../Currencies/Data/CurrencyUnits.php';

    /**
     * The path to the generated Currency conversion definitions file.
     */
    public const string CURRENCY_CONVERSIONS_FILE = __DIR__ . '/../Currencies/Data/CurrencyConversions.php';

    /**
     * The date format used for timestamps in generated data files.
     */
    private const string DATETIME_FORMAT = 'Y-m-d H:i:s T';

    // endregion

    // region Public static properties

    /**
     * The exchange rate service.
     */
    public static ?ExchangeRateServiceInterface $exchangeRateService = null;

    /**
     * The locale used for currency formatting.
     *
     * Set explicitly to override auto-detection, or leave null to detect
     * from the HTTP Accept-Language header or PHP's default locale.
     */
    public static ?string $locale = null;

    // endregion

    // region Private static properties

    /**
     * The TTL for currencies, in seconds. Defaults to 30 days.
     */
    private static int $currenciesTtl = 2592000;

    /**
     * The TTL for exchange rates, in seconds. Defaults to 1 hour.
     */
    private static int $ratesTtl = 3600;

    // endregion

    // region Currency data

    /**
     * Load the currency data from the generated PHP file.
     *
     * @return ?array{whenFetched: string, definitions: array<string, array{asciiSymbol: string, systems: list<UnitSystem>}>}
     */
    public static function loadUnitData(): ?array
    {
        if (!file_exists(self::CURRENCY_UNITS_FILE)) {
            return null;
        }

        return require self::CURRENCY_UNITS_FILE;
    }

    /**
     * Load the exchange rates data from the generated PHP file.
     *
     * @return ?array{
     *     whenFetched: string,
     *     serviceName: string,
     *     definitions: list<array{string, string, float}>
     * }
     */
    public static function loadConversionData(): ?array
    {
        if (!file_exists(self::CURRENCY_CONVERSIONS_FILE)) {
            return null;
        }

        return require self::CURRENCY_CONVERSIONS_FILE;
    }

    /**
     * Regenerate the currency data file from the official ISO 4217 XML.
     *
     * Fetches the latest currency list from SIX Group and writes a PHP array file
     * to the Currency directory. Fund currencies and entries without
     * currency codes are excluded.
     *
     * @param bool $bypassCache If true, skip checking the cache expiry.
     * @return bool True if the data was updated.
     * @throws RuntimeException If the XML cannot be fetched or parsed.
     */
    public static function refreshCurrencyUnits(bool $bypassCache = false): bool
    {
        // Try to load the unit data.
        $unitData = self::loadUnitData();

        // Get the current unit definitions.
        $unitDefinitions = $unitData['definitions'] ?? null;

        // Get the timestamp when the data was last fetched.
        $whenFetched = $unitData['whenFetched'] ?? false;
        if ($whenFetched) {
            $whenFetched = strtotime($whenFetched);
        }
        $expired = !$whenFetched || time() > $whenFetched + self::$currenciesTtl;

        // See if we can skip the download.
        if (!$bypassCache && !empty($unitDefinitions) && !$expired) {
            return false;
        }

        // Fetch the official ISO 4217 XML.
        $xmlContent = @file_get_contents(self::ISO_4217_URL);
        if ($xmlContent === false) {
            throw new RuntimeException('Failed to fetch ISO 4217 XML from ' . self::ISO_4217_URL);
        }

        // Save the XML for reference, it's useful for debugging.
        file_put_contents(self::CURRENCIES_XML_FILE, $xmlContent);

        // Convert to SimpleXML.
        $xml = @simplexml_load_string($xmlContent);
        if (!$xml instanceof SimpleXMLElement) {
            throw new RuntimeException('Failed to parse ISO 4217 XML.');
        }

        $unitDefinitions = [];

        // Parse currencies from the XML.
        // Silence PHPCS temporarily here because of the properties that aren't in lowerCamelCase.
        // phpcs:disable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
        foreach ($xml->CcyTbl->CcyNtry as $entry) {
            $code = (string)$entry->Ccy;
            $name = trim((string)$entry->CcyNm);
            $isFund = isset($entry->CcyNm['IsFund']) && (string)$entry->CcyNm['IsFund'] === 'true';
            // phpcs:enable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

            // Skip currencies we don't need.
            if ($isFund || str_starts_with($code, 'XB') || $code === '' || $code === 'XTS' || $code === 'XXX') {
                continue;
            }

            // Skip if already added (multiple countries can share a currency).
            if (isset($unitDefinitions[$name])) {
                continue;
            }

            $unitDefinitions[$name] = [
                'asciiSymbol' => $code,
                'systems'     => [UnitSystem::Financial],
            ];
        }

        // Construct the data array.
        ksort($unitDefinitions);
        $unitData = [
            'whenFetched' => date(self::DATETIME_FORMAT),
            'definitions' => $unitDefinitions,
        ];

        // Build the PHP file content.
        $url = self::ISO_4217_URL;
        $datetime = date(DateTime::COOKIE);
        $className = self::class;
        $methodName = __FUNCTION__;
        $output = <<<PHP
            <?php

            /**
             * Unit definitions for currencies, based off ISO 4217 currency data.
             * 
             * Fetched from
             * $url
             * at $datetime.
             * 
             * Auto-generated from the official ISO 4217 XML published by SIX Group.
             *
             * To regenerate, call $className::$methodName().
             *
             * @return array{
             *     whenFetched: string,
             *     definitions: array<string, array{
             *         asciiSymbol: string,
             *         unicodeSymbol?: string,
             *         prefixGroup?: int,
             *         alternateSymbol?: string,
             *         systems: list<UnitSystem>
             *     }>
             * }
             */

            declare(strict_types=1);
            PHP;
        $output .= "\n\nreturn " . var_export($unitData, true) . ";\n";

        // Save it.
        file_put_contents(self::CURRENCY_UNITS_FILE, $output);

        return true;
    }

    /**
     * Update all currency conversions in the conversion registry, if we need to.
     *
     * @param bool $bypassCache If true, skip checking the cache expiry.
     * @return bool True if the data was updated.
     * @throws LogicException If the exchange rate service is not configured.
     * @throws RuntimeException If the API request fails or returns invalid data.
     */
    public static function refreshCurrencyConversions(bool $bypassCache = false): bool
    {
        // Check if the exchange rate service is configured.
        self::ensureExchangeRateServiceConfigured();

        // Try to load the conversion data.
        $conversionData = self::loadConversionData();

        // Get the current conversion definitions.
        $conversionDefinitions = $conversionData['definitions'] ?? null;

        // Get the timestamp when the data was last fetched.
        $whenFetched = $conversionData['whenFetched'] ?? false;
        if ($whenFetched) {
            $whenFetched = strtotime($whenFetched);
        }
        $expired = !$whenFetched || time() > $whenFetched + self::$ratesTtl;

        // Get the service name, see if it changed.
        $serviceName = $conversionData['serviceName'] ?? null;
        $curServiceName = self::$exchangeRateService->getName();
        $serviceChanged = $serviceName !== $curServiceName;

        // See if we can skip the download.
        if (!$bypassCache && !empty($conversionDefinitions) && !$expired && !$serviceChanged) {
            return false;
        }

        // Get the latest exchange rates.
        $conversionDefinitions = self::$exchangeRateService->getConversionDefinitions();

        // Construct the data array.
        $conversionData = [
            'whenFetched' => date(self::DATETIME_FORMAT),
            'serviceName' => $curServiceName,
            'definitions' => $conversionDefinitions,
        ];

        // Build the PHP file content.
        $datetime = date(DateTime::COOKIE);
        $className = self::class;
        $methodName = __FUNCTION__;
        $output = <<<PHP
            <?php

            /**
             * Conversion definitions for currencies.
             * 
             * Fetched from $curServiceName at $datetime.
             *
             * To regenerate, call $className::$methodName().
             *
             * @return array{
             *     whenFetched: string,             
             *     serviceName: string,       
             *     definitions: list<array{string, string, float}>
             * }
             */

            declare(strict_types=1);
            PHP;
        $output .= "\n\nreturn " . var_export($conversionData, true) . ";\n";

        // Save it.
        file_put_contents(self::CURRENCY_CONVERSIONS_FILE, $output);

        return true;
    }

    /**
     * Ensure we have fresh data.
     *
     * @throws RuntimeException If the XML or API request fails.
     * @throws LogicException If the exchange rate service is not configured.
     */
    public static function refresh(): void
    {
        if (self::refreshCurrencyUnits()) {
            UnitService::unloadSystem(UnitSystem::Financial);
            UnitService::loadSystem(UnitSystem::Financial);
        }

        if (self::refreshCurrencyConversions()) {
            ConversionService::unloadSystem(UnitSystem::Financial);
            ConversionService::loadSystem(UnitSystem::Financial);
        }
    }

    // endregion

    // region Configuration

    /**
     * Ensure that the exchange rate service is configured.
     *
     * @return void
     * @throws LogicException If the exchange rate service is not configured.
     */
    public static function ensureExchangeRateServiceConfigured(): void
    {
        if (self::$exchangeRateService === null) {
            throw new LogicException(
                'The exchange rate service is not configured. Set the CurrencyService::$exchangeRateService ' .
                'property or call `CurrencyService::init()` first.'
            );
        }
    }

    /**
     * Initialize the currency service.
     *
     * @param ExchangeRateServiceInterface $exchangeRateService The exchange rate service.
     * @param string|null $locale The locale used for currency formatting.
     * @param int $ratesTtl The rates cache period in seconds.
     * @param int $currenciesTtl The currencies cache period in seconds.
     */
    public static function init(
        ExchangeRateServiceInterface $exchangeRateService,
        ?string $locale = null,
        int $ratesTtl = 3600,
        int $currenciesTtl = 2592000
    ): void {
        // Set the service properties.
        self::$exchangeRateService = $exchangeRateService;
        self::$locale = $locale;
        self::$ratesTtl = $ratesTtl;
        self::$currenciesTtl = $currenciesTtl;

        // Add the currency quantity type to the QuantityTypeService, if not done already.
        $qtyType = QuantityTypeService::getByName('currency');
        if ($qtyType === null) {
            QuantityTypeService::add('currency', 'C', Currency::class);
        }

        // Refresh units and conversions as needed.
        self::refresh();

        // Load all currency units and conversions from the (possibly fresh) definitions.
        UnitService::loadSystem(UnitSystem::Financial, true);
    }

    /**
     * Get the locale for currency formatting.
     *
     * Returns the explicitly set locale, or auto-detects from the HTTP Accept-Language header, falling back to PHP's
     * default locale.
     *
     * The result is cached in the static $locale property.
     *
     * @return ?string The locale string, or null if none could be determined.
     */
    public static function getLocale(): ?string
    {
        // Return if already set.
        if (self::$locale !== null) {
            return self::$locale;
        }

        // Try to detect from the HTTP Accept-Language header.
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $detected = Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
            if ($detected !== false) {
                self::$locale = $detected;
                return self::$locale;
            }
        }

        // Fall back to PHP's default locale.
        $default = Locale::getDefault();
        if (!empty($default)) {
            self::$locale = $default;
        }

        return self::$locale;
    }

    // endregion
}
