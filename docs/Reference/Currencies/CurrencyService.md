# CurrencyService

Static service for managing currency units and exchange rate conversions.

**Namespace:** `Galaxon\Quantities\Currencies`

---

## Overview

The `CurrencyService` manages currency data for the Quantities package. It must be initialized before using [`Money`](../QuantityType/Money.md) quantities.

- Currency unit definitions loaded from the official ISO 4217 XML (published by SIX Group)
- Exchange rate conversion data loaded via a pluggable `ExchangeRateServiceInterface`
- File-based caching of both datasets as PHP files with configurable TTLs
- Locale detection for currency formatting
- A single `init()` entry point that configures the service and triggers data loading

Currency units are registered under the `UnitSystem::Financial` unit system.

### Exchange rate services

The package includes adapters for several exchange rate APIs, all of which have free tiers:

| Service                    | API key required | Approx. currencies | Website                                                   |
| -------------------------- | ---------------- | ------------------ | --------------------------------------------------------- |
| `FrankfurterService`       | No               | 30                 | [frankfurter.dev](https://frankfurter.dev/)               |
| `ExchangeRateApiService`   | Yes              | 160                | [exchangerate-api.com](https://www.exchangerate-api.com/) |
| `OpenExchangeRatesService` | Yes              | 170                | [openexchangerates.org](https://openexchangerates.org/)   |
| `CurrencyLayerService`     | Yes              | 170                | [currencylayer.com](https://currencylayer.com/)           |
| `FixerService`             | Yes              | 170                | [fixer.io](https://fixer.io/)                            |

All services are in the `Galaxon\Quantities\Currencies\ExchangeRateServices` namespace.

`FrankfurterService` is the simplest option — no API key, no signup. It uses European Central Bank data and covers ~30 major currencies. The other services require a free API key but support 160+ currencies, including precious metals (XAU, XAG) and some cryptocurrencies.

### Caching

Both currency unit definitions and exchange rates are cached as PHP files in a local data directory. The cache TTLs control how often fresh data is fetched:

- **Currency units** (`$currenciesTtl`, default 30 days) — ISO 4217 currency codes change rarely.
- **Exchange rates** (`$ratesTtl`, default 1 hour) — rates change frequently.

`refresh()` is called automatically when currency conversion is attempted. It checks the cache timestamps and only fetches new data when a cache has expired.

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
- [`FormatException`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Exceptions/FormatException.md) - If the locale string is invalid.
- `DomainException` - If either TTL argument is negative.
- `RuntimeException` - If the ISO 4217 XML or exchange rate API request fails, or if the data directory cannot be created.

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

Set the exchange rate service used to fetch conversion data. Must be set before calling `refresh()` or `getConversions()`. Typically configured via `init()`.

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
- [`FormatException`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Exceptions/FormatException.md) - If the locale string is invalid.

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

## Currency data

### getUnits()

```php
public static function getUnits(bool $bypassCache = false): array
```

Ensure the currency unit data is up to date. Returns the cached data if it exists and has not expired. Otherwise fetches a fresh copy from the official ISO 4217 XML, regenerates the data file, and returns the new data.

**Parameters:**
- `$bypassCache` (bool) - If `true`, always fetch fresh data regardless of cache expiry. Default: `false`.

**Returns:**
- `array` - The current currency unit data, containing:
  - `whenFetched` (string) - Timestamp of when the data was fetched.
  - `currencies` (array<string, string>) - Currency names mapped to their ISO 4217 codes.

**Throws:**
- `RuntimeException` - If a fetch is required but the ISO 4217 XML cannot be fetched or parsed, or if the data directory cannot be created.

### getConversions()

```php
public static function getConversions(bool $bypassCache = false): array
```

Ensure the currency conversion data is up to date. Returns the cached data if it exists, has not expired, and was produced by the currently configured exchange rate service. Otherwise fetches fresh data, regenerates the data file, and returns it.

**Parameters:**
- `$bypassCache` (bool) - If `true`, always fetch fresh data regardless of cache state. Default: `false`.

**Returns:**
- `array` - The current currency conversion data, containing:
  - `whenFetched` (string) - Timestamp of when the data was fetched.
  - `serviceName` (string) - The name of the exchange rate service used.
  - `definitions` (list<array{string, string, float}>) - Conversion definition tuples.

**Throws:**
- `LogicException` - If the exchange rate service is not configured.
- `RuntimeException` - If a fetch is required but the exchange rate service fails or the data directory cannot be created.

### refresh()

```php
public static function refresh(bool $bypassCache = false): void
```

Ensure all currency data is fresh. Refreshes currency units and exchange rate conversions if their caches have expired, then loads the data into the unit and conversion registries.

**Parameters:**
- `$bypassCache` (bool) - If `true`, skip checking the cache expiry. Default: `false`.

**Throws:**
- `RuntimeException` - If the ISO 4217 XML or exchange rate API request fails, or if the data directory cannot be created.
- `LogicException` - If the exchange rate service is not configured.

---

## Usage examples

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
CurrencyService::getConversions(bypassCache: true);

// Use a custom data directory.
CurrencyService::setDataDir('/tmp/currency-cache');

// Check what locale is active.
$locale = CurrencyService::getLocale();
```

---

## See also

- **[ExchangeRateServiceInterface](ExchangeRateServices/ExchangeRateServiceInterface.md)** — Interface for exchange rate providers.
- **[Money](../QuantityType/Money.md)** — Money quantity type.
- **[UnitService](../Services/UnitService.md)** — Unit registry.
- **[ConversionService](../Services/ConversionService.md)** — Conversion registry.
- **[UnitSystem](../Internal/UnitSystem.md)** — Measurement system enum.
