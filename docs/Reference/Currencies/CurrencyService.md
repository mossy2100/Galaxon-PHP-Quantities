# CurrencyService

Static service for managing currency units and exchange rate conversions.

**Namespace:** `Galaxon\Quantities\Currencies`

---

## Overview

The `CurrencyService` manages currency data for the Quantities package. It provides:

- Currency unit definitions loaded from the official ISO 4217 XML (published by SIX Group)
- Exchange rate conversion data loaded via a pluggable `ExchangeRateServiceInterface`
- File-based caching of both datasets as PHP files with configurable TTLs
- Locale detection for currency formatting
- A single `init()` entry point that configures the service and triggers data loading

Currency units are registered under the `UnitSystem::Financial` unit system.

---

## Constants

### DEFAULT_DATA_DIR

```php
public const string DEFAULT_DATA_DIR = __DIR__ . '/data';
```

The default directory where generated currency data files are stored.

---

## Configuration

### init()

```php
public static function init(
    ExchangeRateServiceInterface $exchangeRateService,
    ?string $locale = null,
    int $ratesTtl = 3600,
    int $currenciesTtl = 2592000
): void
```

Initialize the currency service. This is the primary entry point for setting up currency support. Sets all service properties and then calls `refresh()` to load or update data as needed.

**Parameters:**
- `$exchangeRateService` (ExchangeRateServiceInterface) - The exchange rate service to use.
- `$locale` (?string) - The locale for currency formatting. `null` for auto-detection.
- `$ratesTtl` (int) - Cache lifetime for exchange rates in seconds. Default: 3600 (1 hour).
- `$currenciesTtl` (int) - Cache lifetime for currency unit data in seconds. Default: 2592000 (30 days).

**Throws:**
- `FormatException` - If the locale string is invalid.
- `DomainException` - If either TTL argument is negative.

### getExchangeRateService()

```php
public static function getExchangeRateService(): ?ExchangeRateServiceInterface
```

Get the configured exchange rate service.

**Returns:**
- `?ExchangeRateServiceInterface` - The exchange rate service, or `null` if not configured.

### setExchangeRateService()

```php
public static function setExchangeRateService(
    ?ExchangeRateServiceInterface $exchangeRateService
): void
```

Set the exchange rate service used to fetch conversion data. Must be set before calling `refresh()` or `refreshConversions()`. Typically configured via `init()`.

### getLocale()

```php
public static function getLocale(): ?string
```

Get the locale for currency formatting. Returns the explicitly set locale, or auto-detects from the HTTP `Accept-Language` header, falling back to PHP's default locale. Once determined, the result is cached for subsequent calls.

**Returns:**
- `?string` - The locale string, or `null` if none could be determined.

### setLocale()

```php
public static function setLocale(?string $locale): void
```

Set the locale used for currency formatting. Pass `null` to clear an explicitly set locale and revert to auto-detection.

**Throws:**
- `FormatException` - If the locale string is invalid.

### getCurrenciesTtl()

```php
public static function getCurrenciesTtl(): int
```

Get the cache lifetime for currency unit data.

**Returns:**
- `int` - The TTL in seconds.

### setCurrenciesTtl()

```php
public static function setCurrenciesTtl(int $currenciesTtl): void
```

Set the cache lifetime for currency unit data.

**Throws:**
- `DomainException` - If the value is negative.

### getRatesTtl()

```php
public static function getRatesTtl(): int
```

Get the cache lifetime for exchange rate data.

**Returns:**
- `int` - The TTL in seconds.

### setRatesTtl()

```php
public static function setRatesTtl(int $ratesTtl): void
```

Set the cache lifetime for exchange rate data.

**Throws:**
- `DomainException` - If the value is negative.

### getDataDir()

```php
public static function getDataDir(): string
```

Get the current data directory path.

**Returns:**
- `string` - The directory path.

### setDataDir()

```php
public static function setDataDir(string $dataDir): void
```

Set the directory where currency data files are stored. Creates the directory if it does not exist. Trailing slashes are trimmed.

**Throws:**
- `DomainException` - If the path is empty.
- `RuntimeException` - If the directory cannot be created.

### getUnitsFilePath()

```php
public static function getUnitsFilePath(): string
```

Get the path to the currency units data file.

**Returns:**
- `string` - The file path.

### getConversionsFilePath()

```php
public static function getConversionsFilePath(): string
```

Get the path to the currency conversions data file.

**Returns:**
- `string` - The file path.

---

## Currency Data

### loadUnitData()

```php
public static function loadUnitData(): ?array
```

Load the currency unit data from the generated PHP cache file.

**Returns:**
- `?array` - The cached data array, or `null` if the file does not exist. The array contains:
  - `whenFetched` (string) - Timestamp of when the data was fetched.
  - `definitions` (array) - Currency definitions keyed by name, each with `asciiSymbol` and `systems`.

### loadConversionData()

```php
public static function loadConversionData(): ?array
```

Load the exchange rate conversion data from the generated PHP cache file.

**Returns:**
- `?array` - The cached data array, or `null` if the file does not exist. The array contains:
  - `whenFetched` (string) - Timestamp of when the data was fetched.
  - `serviceName` (string) - Name of the exchange rate service that provided the data.
  - `definitions` (list) - Conversion triples as `[sourceSymbol, destSymbol, factor]`.

### refreshUnits()

```php
public static function refreshUnits(bool $bypassCache = false): bool
```

Regenerate the currency unit data file from the official ISO 4217 XML. Fund currencies and entries without currency codes are excluded. Skips the download if the cache has not expired, unless `$bypassCache` is `true`.

**Parameters:**
- `$bypassCache` (bool) - If `true`, skip checking the cache expiry. Default: `false`.

**Returns:**
- `bool` - `true` if the data was updated.

**Throws:**
- `RuntimeException` - If the XML cannot be fetched or parsed.

### refreshConversions()

```php
public static function refreshConversions(bool $bypassCache = false): bool
```

Update all currency conversion data using the configured exchange rate service. Skips the download if the cache has not expired and the service has not changed, unless `$bypassCache` is `true`.

**Parameters:**
- `$bypassCache` (bool) - If `true`, skip checking the cache expiry. Default: `false`.

**Returns:**
- `bool` - `true` if the data was updated.

**Throws:**
- `LogicException` - If the exchange rate service is not configured.
- `RuntimeException` - If the API request fails or returns invalid data.

### refresh()

```php
public static function refresh(bool $bypassCache = false): void
```

Ensure all currency data is fresh. Refreshes currency units and exchange rate conversions if their caches have expired, then loads the data into the unit and conversion registries.

**Parameters:**
- `$bypassCache` (bool) - If `true`, skip checking the cache expiry. Default: `false`.

**Throws:**
- `RuntimeException` - If the ISO 4217 XML or exchange rate API request fails.
- `LogicException` - If the exchange rate service is not configured.

---

## Usage Examples

```php
use Galaxon\Quantities\Currencies\CurrencyService;
use Galaxon\Quantities\Currencies\ExchangeRateServices\OpenExchangeRatesService;

// Initialize with an exchange rate service.
CurrencyService::init(
    new OpenExchangeRatesService('your-api-key')
);

// Currency units and conversions are now loaded and available.
// Subsequent calls to refresh() will only fetch new data when the cache expires.

// Override locale.
CurrencyService::setLocale('de_DE');

// Force a fresh download of exchange rates.
CurrencyService::refreshConversions(bypassCache: true);

// Use a custom data directory.
CurrencyService::setDataDir('/tmp/currency-cache');

// Check what locale is active.
$locale = CurrencyService::getLocale();
```

---

## See Also

- **[ExchangeRateServiceInterface](ExchangeRateServices/ExchangeRateServiceInterface.md)** - Interface for exchange rate providers
- **[Money](../QuantityType/Money.md)** - Money quantity type
- **[UnitService](../Services/UnitService.md)** - Unit registry
- **[ConversionService](../Services/ConversionService.md)** - Conversion registry
- **[UnitSystem](../UnitSystem.md)** - Measurement system enum
