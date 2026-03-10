# ExchangeRateServiceInterface

Interface for exchange rate web services.

**Namespace:** `Galaxon\Quantities\Currencies\ExchangeRateServices`

---

## Overview

`ExchangeRateServiceInterface` defines the contract that exchange rate service adapters must follow to provide currency conversion data from external APIs. Each implementation wraps a specific web service (e.g., Open Exchange Rates, Fixer.io, Frankfurter) and returns conversion definitions in a standard format compatible with `Quantity::getConversionDefinitions()`.

The interface defines:
- `getName()` - Get the name of the exchange rate service.
- `getConversionDefinitions()` - Fetch exchange rates and return them as conversion definitions.

---

## Methods

### getName()

```php
public function getName(): string
```

Get the name of the exchange rate service.

**Returns:**
- `string` - The human-readable name of the service.

### getConversionDefinitions()

```php
public function getConversionDefinitions(): array
```

Refresh the exchange rates from the web service and return them as conversion definitions.

**Returns:**
- `list<array{string, string, float}>` - An array of conversion definitions, each containing a source unit symbol, target unit symbol, and conversion factor. This matches the format used by `Quantity::getConversionDefinitions()`.

**Contract:**
- Must fetch current exchange rate data from the external API.
- Must return the data as an array of conversion definition tuples.
- Must throw `RuntimeException` if the API request fails or returns invalid data.

---

## Implementing Classes

- [CurrencyLayerService](CurrencyLayerService.md) - Exchange rate service using the CurrencyLayer API.
- [ExchangeRateApiService](ExchangeRateApiService.md) - Exchange rate service using the ExchangeRate-API.
- [FixerService](FixerService.md) - Exchange rate service using the Fixer.io API.
- [FrankfurterService](FrankfurterService.md) - Exchange rate service using the Frankfurter API, backed by European Central Bank data.
- [OpenExchangeRatesService](OpenExchangeRatesService.md) - Exchange rate service using the Open Exchange Rates API.

---

## See Also

- **[Money](../../QuantityType/Money.md)** - The quantity type for currency values.
- **[Quantity](../../Quantity.md)** - The base quantity class that uses conversion definitions.
