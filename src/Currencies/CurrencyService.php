<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Currencies;

use DateTime;
use Galaxon\Core\Stringify;
use Galaxon\Quantities\Currencies\ExchangeRateServices\ExchangeRateServiceInterface;
use Galaxon\Quantities\QuantityType\Money;
use Galaxon\Quantities\Services\ConversionService;
use Galaxon\Quantities\Services\QuantityTypeService;
use Galaxon\Quantities\Services\UnitService;
use Galaxon\Quantities\UnitSystem;
use Locale;
use LogicException;
use ParseError;
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
     *
     * Note: "currrency" with three r's is SIX Group's actual URL, not a typo in our code.
     */
    private const string ISO_4217_URL =
        'https://www.six-group.com/dam/download/financial-information/data-center/iso-currrency/lists/list-one.xml';

    /**
     * The default data directory path.
     */
    public const string DEFAULT_DATA_DIR = __DIR__ . '/data';

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
     * The directory where currency data files are stored.
     */
    private static string $dataDir = self::DEFAULT_DATA_DIR;

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
     * @return ?array{
     *     whenFetched: string,
     *     definitions: array<string, array{asciiSymbol: string, systems: list<UnitSystem>}>
     * }
     */
    public static function loadUnitData(): ?array
    {
        $path = self::getUnitsFilePath();
        if (!file_exists($path)) {
            return null;
        }

        return require $path;
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
        $path = self::getConversionsFilePath();
        if (!file_exists($path)) {
            return null;
        }

        return require $path;
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
        try {
            $unitData = self::loadUnitData();
            // @codeCoverageIgnoreStart
        } catch (ParseError) {
            // File is corrupted.
            $unitData = null;
            // @codeCoverageIgnoreEnd
        }

        // Get the current unit definitions.
        $unitDefinitions = $unitData['definitions'] ?? null;

        // Get the timestamp when the data was last fetched.
        $whenFetched = isset($unitData['whenFetched']) ? strtotime($unitData['whenFetched']) : false;
        $expired = !$whenFetched || time() > $whenFetched + self::$currenciesTtl;

        // See if we can skip the download.
        if (!$bypassCache && !empty($unitDefinitions) && !$expired) {
            return false;
        }

        // Fetch the official ISO 4217 XML.
        $xmlContent = @file_get_contents(self::ISO_4217_URL);
        if ($xmlContent === false) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Failed to fetch ISO 4217 XML from ' . self::ISO_4217_URL);
            // @codeCoverageIgnoreEnd
        }

        // Save the XML for reference, it's useful for debugging.
        file_put_contents(self::getXmlFilePath(), $xmlContent);

        // Convert to SimpleXML.
        $xml = @simplexml_load_string($xmlContent);
        if (!$xml instanceof SimpleXMLElement) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Failed to parse ISO 4217 XML.');
            // @codeCoverageIgnoreEnd
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
        $output .= "\n\nreturn " . Stringify::stringify($unitData, true) . ";\n";

        // Save it.
        file_put_contents(self::getUnitsFilePath(), $output);

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
        try {
            $conversionData = self::loadConversionData();
            // @codeCoverageIgnoreStart
        } catch (ParseError) {
            // File is corrupted.
            $conversionData = null;
            // @codeCoverageIgnoreEnd
        }

        // Get the current conversion definitions.
        $conversionDefinitions = $conversionData['definitions'] ?? null;

        // Get the timestamp when the data was last fetched.
        $whenFetched = isset($conversionData['whenFetched']) ? strtotime($conversionData['whenFetched']) : false;
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
        $output .= "\n\nreturn " . Stringify::stringify($conversionData, true) . ";\n";

        // Save it.
        file_put_contents(self::getConversionsFilePath(), $output);

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
            UnitService::unloadBySystem(UnitSystem::Financial);
        }

        // Load currency units from the (possibly fresh) definitions.
        UnitService::loadBySystem(UnitSystem::Financial);

        if (self::refreshCurrencyConversions()) {
            ConversionService::unloadBySystem(UnitSystem::Financial);
        }

        // Load currency conversions from the (possibly fresh) definitions.
        ConversionService::loadDefinitions();
    }

    // endregion

    // region Configuration

    /**
     * Set the data directory for currency data files.
     *
     * @param string $dataDir The directory path.
     */
    public static function setDataDir(string $dataDir): void
    {
        self::$dataDir = rtrim($dataDir, '/');
    }

    /**
     * Get the current data directory path.
     *
     * @return string The directory path.
     */
    public static function getDataDir(): string
    {
        return self::$dataDir;
    }

    /**
     * Get the path to the currency units data file.
     *
     * @return string The file path.
     */
    public static function getUnitsFilePath(): string
    {
        return self::$dataDir . '/CurrencyUnits.php';
    }

    /**
     * Get the path to the currency conversions data file.
     *
     * @return string The file path.
     */
    public static function getConversionsFilePath(): string
    {
        return self::$dataDir . '/CurrencyConversions.php';
    }

    /**
     * Get the path to the downloaded currencies XML file.
     *
     * @return string The file path.
     */
    private static function getXmlFilePath(): string
    {
        return self::$dataDir . '/CurrencyData.xml';
    }

    /**
     * Ensure that the exchange rate service is configured.
     *
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
            QuantityTypeService::add('currency', 'C', Money::class);
        }

        // Refresh and load units and conversions as needed.
        self::refresh();
    }

    /**
     * Get the locale for currency formatting.
     *
     * Returns the explicitly set locale, or auto-detects from the HTTP Accept-Language header, falling back to PHP's
     * default locale.
     *
     * If a locale is successfully determined, it is cached in the static $locale property for subsequent calls.
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
        // @codeCoverageIgnoreStart
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $detected = Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
            if ($detected !== false) {
                self::$locale = $detected;
                return self::$locale;
            }
        }
        // @codeCoverageIgnoreEnd

        // Fall back to PHP's default locale.
        $default = Locale::getDefault();
        if (!empty($default)) {
            self::$locale = $default;
        }

        return self::$locale;
    }

    // endregion
}
