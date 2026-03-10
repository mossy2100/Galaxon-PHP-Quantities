# CurrencyLayerService

Exchange rate service using the CurrencyLayer API.

**Namespace:** `Galaxon\Quantities\Currencies\ExchangeRateServices`
**Implements:** [`ExchangeRateServiceInterface`](ExchangeRateServiceInterface.md)

---

## Overview

Fetches live exchange rates from the [CurrencyLayer API](https://currencylayer.com/). The free
tier provides USD-based rates only, with 1,000 requests per month. An access key is required,
which you can obtain by creating a CurrencyLayer account.

The service provides rates for approximately 170 currencies. CurrencyLayer returns quote keys as
concatenated currency code pairs (e.g. `USDEUR`, `USDGBP`). The service automatically extracts the
target currency code from these keys.

---

## Constructor

### \_\_construct()

```php
public function __construct(string $accessKey)
```

Create a new CurrencyLayer service.

**Parameters:**

| Name         | Type     | Description                       |
|--------------|----------|-----------------------------------|
| `$accessKey` | `string` | The access key for CurrencyLayer. |

**Throws:**

- `DomainException` - If the access key is empty.

---

## Methods

### getName()

```php
public function getName(): string
```

Returns the service name: `'CurrencyLayer'`.

**Returns:** `string`

---

### getConversionDefinitions()

```php
public function getConversionDefinitions(): array
```

Fetches fresh exchange rates from the CurrencyLayer API and returns conversion definitions. Rates
are relative to USD on the free tier.

**Returns:** `list<array{string, string, float}>` - Currency conversion definitions as
`[base, target, rate]` tuples.

**Throws:**

- `RuntimeException` - If the API request fails, returns invalid JSON, returns an API error, or
  is missing the expected quotes data.

---

## See Also

- [ExchangeRateServiceInterface](ExchangeRateServiceInterface.md)
- [CurrencyService](../CurrencyService.md)
- [Money](../../QuantityType/Money.md)
