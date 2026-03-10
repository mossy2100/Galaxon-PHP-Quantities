# Currency Service

The `CurrencyService` manages exchange rate data and currency unit definitions. It must be initialised before using `Money` quantities.

---

## Initialisation

Call `CurrencyService::init()` once at application startup:

```php
use Galaxon\Quantities\Currencies\CurrencyService;
use Galaxon\Quantities\Currencies\ExchangeRateServices\FrankfurterService;

CurrencyService::init(new FrankfurterService());
```

`init()` sets the exchange rate service, then calls `refresh()` internally to fetch currency units and conversion rates (if the cached data has expired). 

### init() parameters

| Parameter              | Default      | Description                                                             |
| ---------------------- | ------------ | ----------------------------------------------------------------------- |
| `$exchangeRateService` | *(required)* | An `ExchangeRateServiceInterface` instance.                             |
| `$locale`              | `null`       | Locale for currency formatting (e.g. `'en_US'`). Auto-detected if null. |
| `$ratesTtl`            | `3600`       | Cache lifetime for exchange rates, in seconds (1 hour).                 |
| `$currenciesTtl`       | `2592000`    | Cache lifetime for currency unit definitions, in seconds (30 days).     |

---

## Exchange rate services

The package includes adapters for several exchange rate APIs, all of which have free tiers:

| Service                    | API key required | Approx. number of currencies | Website                                                   |
| -------------------------- | ---------------- | ---------------------------- | --------------------------------------------------------- |
| `FrankfurterService`       | No               | 30                           | [frankfurter.dev](https://frankfurter.dev/)               |
| `ExchangeRateApiService`   | Yes              | 160                          | [exchangerate-api.com](https://www.exchangerate-api.com/) |
| `OpenExchangeRatesService` | Yes              | 170                          | [openexchangerates.org](https://openexchangerates.org/)   |
| `CurrencyLayerService`     | Yes              | 170                          | [currencylayer.com](https://currencylayer.com/)           |
| `FixerService`             | Yes              | 170                          | [fixer.io](https://fixer.io/)                            |

All services are in the `Galaxon\Quantities\Currencies\ExchangeRateServices` namespace.

For services that require an API key, pass it to the constructor:

```php
use Galaxon\Quantities\Currencies\ExchangeRateServices\ExchangeRateApiService;

CurrencyService::init(new ExchangeRateApiService('your-api-key'));
```

### Choosing a service

- **FrankfurterService** is the simplest option — no API key, no signup. It uses European Central Bank data and covers ~30 major currencies.
- The other services require a free API key but support 160+ currencies, including precious metals (XAU, XAG) and some cryptocurrencies.

---

## Caching

Both currency unit definitions and exchange rates are cached as PHP files in a local data directory. The cache TTLs control how often fresh data is fetched:

- **Currency units** (`$currenciesTtl`, default 30 days) — ISO 4217 currency codes change rarely.
- **Exchange rates** (`$ratesTtl`, default 1 hour) — rates change frequently.

### Refreshing data

`CurrencyService::refresh()` is called automatically when currency conversion is attempted. It checks the cache timestamps and only fetches new data when a cache has expired.

To force a refresh (bypassing cache expiry):

```php
CurrencyService::refresh(bypassCache: true);
```

You can also refresh units and conversions independently:

```php
CurrencyService::refreshUnits(bypassCache: true);
CurrencyService::refreshConversions(bypassCache: true);
```

### Changing TTLs

```php
CurrencyService::setRatesTtl(1800);      // 30 minutes
CurrencyService::setCurrenciesTtl(86400); // 1 day
```

### Custom data directory

By default, cached data files are stored in the package's `src/Currencies/data/` directory. To use a different location:

```php
CurrencyService::setDataDir('/path/to/cache');
```

---

## Switching services

The exchange rate service can be changed after initialisation:

```php
use Galaxon\Quantities\Currencies\ExchangeRateServices\OpenExchangeRatesService;

$openExchange = new OpenExchangeRatesService('your-api-key');
CurrencyService::setExchangeRateService($openExchange);
```

When the service changes, the next `refresh()` call will detect the change and fetch fresh rates automatically.

---

## See Also

- [Money](Money.md) — Creating and using Money quantities.
- [Currency Calculations](CurrencyCalculations.md) — Compound unit expressions with currencies.
- [CurrencyService reference](../Reference/Currencies/CurrencyService.md)
- [ExchangeRateServiceInterface reference](../Reference/Currencies/ExchangeRateServices/ExchangeRateServiceInterface.md)
