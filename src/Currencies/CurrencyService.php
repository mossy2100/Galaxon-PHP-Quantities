<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Currencies;

use DateTimeInterface;
use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Core\Stringify;
use Galaxon\Quantities\Currencies\ExchangeRateServices\ExchangeRateServiceInterface;
use Galaxon\Quantities\Internal\Converter;
use Galaxon\Quantities\Services\ConversionService;
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
    // region Public constants

    /**
     * The default data directory path.
     */
    public const string DEFAULT_DATA_DIR = __DIR__ . '/data';

    /**
     * The date format used for timestamps in generated data files.
     */
    private const string DATETIME_FORMAT = 'Y-m-d H:i:s T';

    // endregion

    // region Private constants

    /**
     * The URL for the official ISO 4217 XML published by SIX Group.
     *
     * Note: "currrency" with three r's is SIX Group's actual URL, not a typo in our code.
     */
    private const string ISO_4217_URL =
        'https://www.six-group.com/dam/download/financial-information/data-center/iso-currrency/lists/list-one.xml';

    /**
     * The regular expression for matching a locale string.
     */
    private const string RX_LOCALE = '[a-z]{2,4}([-_][A-Z][a-z]{3})?([-_]([A-Z]{2}|[0-9]{3}))?';

    // endregion

    // region Private static properties

    /**
     * The exchange rate service.
     */
    private static ?ExchangeRateServiceInterface $exchangeRateService = null;

    /**
     * The locale used for currency formatting.
     */
    private static ?string $locale = null;

    /**
     * The TTL for currencies, in seconds. Defaults to 30 days.
     */
    private static int $currenciesTtl = 2592000;

    /**
     * The TTL for exchange rates, in seconds. Defaults to 1 hour.
     */
    private static int $ratesTtl = 3600;

    /**
     * The directory where currency data files are stored.
     */
    private static string $dataDir = self::DEFAULT_DATA_DIR;

    // endregion

    // region Currency data

    /**
     * Load the currency data from the generated PHP file.
     *
     * @return ?array{
     *     whenFetched: string,
     *     currencies: array<string, string>
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
    public static function refreshUnits(bool $bypassCache = false): bool
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
        $currencies = $unitData['currencies'] ?? null;

        // Get the timestamp when the data was last fetched.
        $whenFetched = isset($unitData['whenFetched']) ? strtotime($unitData['whenFetched']) : false;
        $expired = !$whenFetched || time() > $whenFetched + self::$currenciesTtl;

        // See if we can skip the download.
        if (!$bypassCache && !empty($currencies) && !$expired) {
            return false;
        }

        // Fetch the official ISO 4217 XML.
        $xmlContent = @file_get_contents(self::ISO_4217_URL);
        if ($xmlContent === false) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Failed to fetch ISO 4217 XML from ' . self::ISO_4217_URL);
            // @codeCoverageIgnoreEnd
        }

        // Ensure the data directory exists.
        self::ensureDirExists(self::$dataDir);

        // Save the XML for reference, it's useful for debugging.
        file_put_contents(self::getXmlFilePath(), $xmlContent);

        // Convert to SimpleXML.
        $xml = @simplexml_load_string($xmlContent);
        if (!$xml instanceof SimpleXMLElement) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Failed to parse ISO 4217 XML.');
            // @codeCoverageIgnoreEnd
        }

        $currencies = [];

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
            if (isset($currencies[$name])) {
                continue;
            }

            $currencies[$name] = $code;
        }

        // Construct the data array.
        ksort($currencies);
        $unitData = [
            'whenFetched' => date(self::DATETIME_FORMAT),
            'currencies'  => $currencies,
        ];

        // Build the PHP file content.
        $url = self::ISO_4217_URL;
        $datetime = date(DateTimeInterface::COOKIE);
        $className = self::class;
        $methodName = __FUNCTION__;
        $output = <<<PHP
            <?php

            /**
             * Currencies, based off ISO 4217 currency data.
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
             *     currencies: array<string, string>
             * }
             */

            declare(strict_types=1);
            PHP;
        $output .= "\n\nreturn " . Stringify::stringify($unitData, true) . ";\n";

        // Save it.
        file_put_contents(self::getUnitsFilePath(), $output);

        // Refresh the currency units.
        UnitService::loadSystem(UnitSystem::Financial, true);

        // Loading any missing conversions. If new currency codes have been added, some conversion definitions not
        // loaded before, due to unknown units, may now be loadable.
        Converter::getInstance('C')->loadConversions();

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
    public static function refreshConversions(bool $bypassCache = false): bool
    {
        // Check if the exchange rate service is configured.
        self::ensureExchangeRateServiceConfigured();
        assert(self::$exchangeRateService !== null);

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
        $datetime = date(DateTimeInterface::COOKIE);
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

        // Ensure the data directory exists.
        self::ensureDirExists(self::$dataDir);

        // Save it.
        file_put_contents(self::getConversionsFilePath(), $output);

        // Remove all conversions involving currencies. We can't just overwrite the conversions between currencies
        // because conversions involving currencies may have been created for dimensions other than 'C'.
        ConversionService::removeBySystem(UnitSystem::Financial);

        // Reload currency conversions.
        Converter::getInstance('C')->loadConversions(true);

        return true;
    }

    /**
     * Ensure we have fresh data.
     *
     * @param bool $bypassCache If true, skip checking the cache expiry.
     * @throws RuntimeException If the XML or API request fails.
     * @throws LogicException If the exchange rate service is not configured.
     */
    public static function refresh(bool $bypassCache = false): void
    {
        self::refreshUnits($bypassCache);
        self::refreshConversions($bypassCache);
    }

    // endregion

    // region Configuration

    /**
     * Get the exchange rate service.
     *
     * @return ?ExchangeRateServiceInterface The exchange rate service, or null if not configured.
     */
    public static function getExchangeRateService(): ?ExchangeRateServiceInterface
    {
        return self::$exchangeRateService;
    }

    /**
     * Set the exchange rate service.
     *
     * @param ?ExchangeRateServiceInterface $exchangeRateService The exchange rate service, or null to clear.
     */
    public static function setExchangeRateService(?ExchangeRateServiceInterface $exchangeRateService): void
    {
        self::$exchangeRateService = $exchangeRateService;
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
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && is_string($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
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

    /**
     * Set the locale used for currency formatting.
     *
     * Pass null to clear an explicitly set locale and revert to auto-detection.
     *
     * @param ?string $locale A BCP 47 / ICU locale string (e.g. 'en_US', 'de_DE'), or null.
     * @throws FormatException If the locale string is invalid.
     */
    public static function setLocale(?string $locale): void
    {
        if ($locale !== null && !preg_match('/^' . self::RX_LOCALE . '$/', $locale)) {
            throw new FormatException("Invalid locale string: '$locale'.");
        }
        self::$locale = $locale;
    }

    /**
     * Get the cache lifetime for currency unit data.
     *
     * @return int The TTL in seconds.
     */
    public static function getCurrenciesTtl(): int
    {
        return self::$currenciesTtl;
    }

    /**
     * Set the cache lifetime for currency unit data.
     *
     * @param int $currenciesTtl The TTL in seconds. Must be non-negative.
     * @throws DomainException If the value is negative.
     */
    public static function setCurrenciesTtl(int $currenciesTtl): void
    {
        if ($currenciesTtl < 0) {
            throw new DomainException("Currencies TTL must be non-negative, got $currenciesTtl.");
        }
        self::$currenciesTtl = $currenciesTtl;
    }

    /**
     * Get the cache lifetime for exchange rate data.
     *
     * @return int The TTL in seconds.
     */
    public static function getRatesTtl(): int
    {
        return self::$ratesTtl;
    }

    /**
     * Set the cache lifetime for exchange rate data.
     *
     * @param int $ratesTtl The TTL in seconds. Must be non-negative.
     * @throws DomainException If the value is negative.
     */
    public static function setRatesTtl(int $ratesTtl): void
    {
        if ($ratesTtl < 0) {
            throw new DomainException("Rates TTL must be non-negative, got $ratesTtl.");
        }
        self::$ratesTtl = $ratesTtl;
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
     * Set the data directory for currency data files.
     *
     * @param string $dataDir The directory path.
     * @throws DomainException If the path is empty.
     * @throws RuntimeException If the directory cannot be created.
     */
    public static function setDataDir(string $dataDir): void
    {
        $dataDir = rtrim($dataDir, '/');
        if ($dataDir === '') {
            throw new DomainException('Data directory path cannot be empty.');
        }
        self::ensureDirExists($dataDir);
        self::$dataDir = $dataDir;
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
     * @internal
     */
    public static function getXmlFilePath(): string
    {
        return self::$dataDir . '/CurrencyData.xml';
    }

    /**
     * Initialize the currency service.
     *
     * @param ExchangeRateServiceInterface $exchangeRateService The exchange rate service.
     * @param string|null $locale The locale used for currency formatting.
     * @param int $ratesTtl The rates cache period in seconds.
     * @param int $currenciesTtl The currencies cache period in seconds.
     * @throws FormatException If the locale string is invalid.
     * @throws DomainException If either TTL argument is negative.
     */
    public static function init(
        ExchangeRateServiceInterface $exchangeRateService,
        ?string $locale = null,
        int $ratesTtl = 3600,
        int $currenciesTtl = 2592000
    ): void {
        // Set the service properties.
        self::setExchangeRateService($exchangeRateService);
        self::setLocale($locale);
        self::setRatesTtl($ratesTtl);
        self::setCurrenciesTtl($currenciesTtl);

        // Refresh units and conversions as needed.
        self::refresh();
    }

    // endregion

    // region Private configuration methods

    /**
     * Ensure that the exchange rate service is configured.
     *
     * @throws LogicException If the exchange rate service is not configured.
     */
    private static function ensureExchangeRateServiceConfigured(): void
    {
        if (self::$exchangeRateService === null) {
            throw new LogicException(
                'The exchange rate service is not configured. Call `CurrencyService::setExchangeRateService()` ' .
                'or `CurrencyService::init()` first.'
            );
        }
    }

    /**
     * Ensure that the data directory exists, creating it if necessary.
     *
     * @param string $dirPath The directory path.
     * @throws RuntimeException If the directory cannot be created.
     */
    private static function ensureDirExists(string $dirPath): void
    {
        if (!is_dir($dirPath) && !mkdir($dirPath, 0755, true)) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException("Failed to create directory: '$dirPath'.");
            // @codeCoverageIgnoreEnd
        }
    }

    // endregion
}
