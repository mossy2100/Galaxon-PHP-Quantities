# ExchangeRateApiService

Exchange rate service using the ExchangeRate-API.

**Namespace:** `Galaxon\Quantities\Currencies\ExchangeRateServices`
**Implements:** [`ExchangeRateServiceInterface`](ExchangeRateServiceInterface.md)

---

## Overview

Fetches live exchange rates from the [ExchangeRate-API](https://www.exchangerate-api.com/). The free tier supports any base currency, with 1,500 requests per month. An API key is required, which you can obtain by creating an ExchangeRate-API account and finding it in your dashboard.

The service provides rates for approximately 160 currencies. Rates are fetched relative to USD by default.

---

## Constructor

### \_\_construct()

```php
public function __construct(string $apiKey)
```

Create a new ExchangeRate-API service.

**Parameters:**

| Name      | Type     | Description                        |
|-----------|----------|------------------------------------|
| `$apiKey` | `string` | The API key for ExchangeRate-API.  |

**Throws:**

- `DomainException` - If the API key is empty.

---

## Methods

### getName()

```php
public function getName(): string
```

Returns the service name: `'ExchangeRate-API'`.

**Returns:** `string`

---

### getConversionDefinitions()

```php
public function getConversionDefinitions(): array
```

Fetches fresh exchange rates from the ExchangeRate-API and returns conversion definitions. Rates
are relative to USD by default.

**Returns:** `list<array{string, string, float}>` - Currency conversion definitions as
`[base, target, rate]` tuples.

**Throws:**

- `RuntimeException` - If the API request fails, returns invalid JSON, returns an API error, or
  is missing the expected conversion rates data.

---

## See Also

- **[ExchangeRateServiceInterface](ExchangeRateServiceInterface.md)** — The interface this service implements.
- **[CurrencyService](../CurrencyService.md)** — Exchange rate configuration and caching.
- **[Money](../../QuantityType/Money.md)** — The quantity type for currency values.
