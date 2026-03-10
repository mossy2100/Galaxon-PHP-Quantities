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

Currency units are registered under the `UnitSystem::Financial` measurement system.

---

## Constants

### `DEFAULT_DATA_DIR`

```php
public const string DEFAULT_DATA_DIR = __DIR__ . '/data';
```

The default directory where generated currency data files are stored.

---

## Methods

### Initialization

#### `static init(ExchangeRateServiceInterface $exchangeRateService, ?string $locale = null, int $ratesTtl = 3600, int $currenciesTtl = 2592000): void`

Initialize the currency service. This is the primary entry point for setting up currency support.

```php
use Galaxon\Quantities\Currencies\CurrencyService;
use Galaxon\Quantities\Currencies\ExchangeRateServices\OpenExchangeRatesService;

CurrencyService::init(
    exchangeRateService: new OpenExchangeRatesService('your-api-key'),
    locale: 'en_US',
    ratesTtl: 3600,         // 1 hour (default)
    currenciesTtl: 2592000   // 30 days (default)
);
```

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$exchangeRateService` | `ExchangeRateServiceInterface` | The exchange rate service to use. |
| `$locale` | `?string` | The locale for currency formatting. `null` for auto-detection. |
| `$ratesTtl` | `int` | Cache lifetime for exchange rates in seconds. Default: 3600 (1 hour). |
| `$currenciesTtl` | `int` | Cache lifetime for currency unit data in seconds. Default: 2592000 (30 days). |

Calling `init()` sets the service properties and then calls `refresh()` to load or update data as needed.

---

### Exchange Rate Service

#### `static getExchangeRateService(): ?ExchangeRateServiceInterface`

Get the configured exchange rate service.

**Returns:** `?ExchangeRateServiceInterface` - The exchange rate service, or `null` if not configured.

#### `static setExchangeRateService(?ExchangeRateServiceInterface $exchangeRateService): void`

Set the exchange rate service used to fetch conversion data. Must be set before calling `refresh()` or `refreshCurrencyConversions()`. Typically configured via `init()`.

```php
use Galaxon\Quantities\Currencies\ExchangeRateServices\FrankfurterService;

CurrencyService::setExchangeRateService(new FrankfurterService());
```

---

### Locale

#### `static getLocale(): ?string`

Get the locale for currency formatting. Returns the explicitly set locale, or auto-detects from the HTTP `Accept-Language` header, falling back to PHP's default locale. Once determined, the result is cached for subsequent calls.

```php
$locale = CurrencyService::getLocale();
// e.g. 'en_US'
```

**Returns:** `?string` - The locale string, or `null` if none could be determined.

#### `static setLocale(?string $locale): void`

Set the locale used for currency formatting. Pass `null` to clear an explicitly set locale and revert to auto-detection.

```php
CurrencyService::setLocale('de_DE');
CurrencyService::setLocale(null);  // revert to auto-detection
```

**Throws:** `FormatException` - If the locale string is invalid.

---

### Cache TTLs

#### `static getCurrenciesTtl(): int`

Get the cache lifetime for currency unit data.

**Returns:** `int` - The TTL in seconds.

#### `static setCurrenciesTtl(int $currenciesTtl): void`

Set the cache lifetime for currency unit data.

```php
CurrencyService::setCurrenciesTtl(86400); // 1 day
```

**Throws:** `DomainException` - If the value is negative.

#### `static getRatesTtl(): int`

Get the cache lifetime for exchange rate data.

**Returns:** `int` - The TTL in seconds.

#### `static setRatesTtl(int $ratesTtl): void`

Set the cache lifetime for exchange rate data.

```php
CurrencyService::setRatesTtl(1800); // 30 minutes
```

**Throws:** `DomainException` - If the value is negative.

---

### Refreshing Data

#### `static refresh(bool $bypassCache = false): void`

Ensure all currency data is fresh. Refreshes currency units and exchange rate conversions if their caches have expired, then loads the data into the unit and conversion registries.

```php
CurrencyService::refresh();
CurrencyService::refresh(bypassCache: true); // force re-fetch
```

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$bypassCache` | `bool` | If `true`, skip checking the cache expiry. Default: `false`. |

**Throws:**

- `RuntimeException` - If the ISO 4217 XML or exchange rate API request fails.
- `LogicException` - If the exchange rate service is not configured.

#### `static refreshCurrencyUnits(bool $bypassCache = false): bool`

Regenerate the currency unit data file from the official ISO 4217 XML. Fund currencies and entries without currency codes are excluded.

```php
$updated = CurrencyService::refreshCurrencyUnits();
$updated = CurrencyService::refreshCurrencyUnits(bypassCache: true);
```

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$bypassCache` | `bool` | If `true`, skip checking the cache expiry. Default: `false`. |

**Returns:** `bool` - `true` if the data was updated.

**Throws:** `RuntimeException` - If the XML cannot be fetched or parsed.

#### `static refreshCurrencyConversions(bool $bypassCache = false): bool`

Update all currency conversion data using the configured exchange rate service.

```php
$updated = CurrencyService::refreshCurrencyConversions();
$updated = CurrencyService::refreshCurrencyConversions(bypassCache: true);
```

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$bypassCache` | `bool` | If `true`, skip checking the cache expiry. Default: `false`. |

**Returns:** `bool` - `true` if the data was updated.

**Throws:**

- `LogicException` - If the exchange rate service is not configured.
- `RuntimeException` - If the API request fails or returns invalid data.

---

### Loading Cached Data

#### `static loadUnitData(): ?array`

Load the currency unit data from the generated PHP cache file.

```php
$data = CurrencyService::loadUnitData();
// Returns: ['whenFetched' => '...', 'definitions' => [...]]
```

**Returns:** `?array` - The cached data array, or `null` if the file does not exist. The array contains:

- `whenFetched` (string) - Timestamp of when the data was fetched.
- `definitions` (array) - Currency definitions keyed by name, each with `asciiSymbol` and `systems`.

#### `static loadConversionData(): ?array`

Load the exchange rate conversion data from the generated PHP cache file.

```php
$data = CurrencyService::loadConversionData();
// Returns: ['whenFetched' => '...', 'serviceName' => '...', 'definitions' => [...]]
```

**Returns:** `?array` - The cached data array, or `null` if the file does not exist. The array contains:

- `whenFetched` (string) - Timestamp of when the data was fetched.
- `serviceName` (string) - Name of the exchange rate service that provided the data.
- `definitions` (list) - Conversion triples as `[sourceSymbol, destSymbol, factor]`.

---

### Data Directory

#### `static setDataDir(string $dataDir): void`

Set the directory where currency data files are stored.

```php
CurrencyService::setDataDir('/path/to/custom/data');
```

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$dataDir` | `string` | The directory path. Trailing slashes are trimmed. |

#### `static getDataDir(): string`

Get the current data directory path.

**Returns:** `string` - The directory path.

#### `static getUnitsFilePath(): string`

Get the path to the currency units data file.

**Returns:** `string` - The file path.

#### `static getConversionsFilePath(): string`

Get the path to the currency conversions data file.

**Returns:** `string` - The file path.

---

### Validation

#### `static ensureExchangeRateServiceConfigured(): void`

Ensure that the exchange rate service has been configured. Throws if it has not.

**Throws:** `LogicException` - If no exchange rate service is set.

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
CurrencyService::refreshCurrencyConversions(bypassCache: true);

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
