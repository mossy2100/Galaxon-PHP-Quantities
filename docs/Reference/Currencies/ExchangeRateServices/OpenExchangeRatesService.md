# OpenExchangeRatesService

Exchange rate service using the Open Exchange Rates API.

**Namespace:** `Galaxon\Quantities\Currencies\ExchangeRateServices`
**Implements:** [`ExchangeRateServiceInterface`](ExchangeRateServiceInterface.md)

---

## Overview

Fetches live exchange rates from the [Open Exchange Rates API](https://openexchangerates.org/).
The free tier provides USD-based rates only. An App ID is required, which you can obtain by
creating an account and visiting your
[App IDs page](https://openexchangerates.org/account/app-ids).

The service provides rates for approximately 170 currencies.

---

## Constructor

### \_\_construct()

```php
public function __construct(string $appId)
```

Create a new Open Exchange Rates service.

**Parameters:**

| Name     | Type     | Description                            |
|----------|----------|----------------------------------------|
| `$appId` | `string` | The App ID for Open Exchange Rates.    |

**Throws:**

- `DomainException` - If the App ID is empty.

---

## Methods

### getName()

```php
public function getName(): string
```

Returns the service name: `'Open Exchange Rates'`.

**Returns:** `string`

### getConversionDefinitions()

```php
public function getConversionDefinitions(): array
```

Fetches fresh exchange rates from the Open Exchange Rates API and returns conversion definitions.
Rates are always relative to USD.

**Returns:** `list<array{string, string, float}>` - Currency conversion definitions as
`[base, target, rate]` tuples.

**Throws:**

- `RuntimeException` - If the API request fails, returns invalid JSON, returns an API error, or
  is missing the expected rates data.

---

## See Also

- **[ExchangeRateServiceInterface](ExchangeRateServiceInterface.md)** — The interface this service implements.
- **[CurrencyService](../CurrencyService.md)** — Exchange rate configuration and caching.
- **[Money](../../QuantityType/Money.md)** — The quantity type for currency values.
