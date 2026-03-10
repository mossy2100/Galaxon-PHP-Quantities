# FixerService

Exchange rate service using the Fixer.io API.

**Namespace:** `Galaxon\Quantities\Currencies\ExchangeRateServices`
**Implements:** [`ExchangeRateServiceInterface`](ExchangeRateServiceInterface.md)

---

## Overview

Fetches live exchange rates from the [Fixer.io API](https://fixer.io/). The free tier provides
EUR-based rates only, with 10,000 requests per month. An access key is required, which you can
obtain by creating a Fixer.io account.

---

## Constructor

### __construct()

```php
public function __construct(string $accessKey)
```

Create a new Fixer.io service.

**Parameters:**

| Name         | Type     | Description                    |
|--------------|----------|--------------------------------|
| `$accessKey` | `string` | The access key for Fixer.io.   |

**Throws:**

- `DomainException` - If the access key is empty.

---

## Methods

### getName()

```php
public function getName(): string
```

Returns the service name: `'Fixer.io'`.

**Returns:** `string`

---

### getConversionDefinitions()

```php
public function getConversionDefinitions(): array
```

Fetches fresh exchange rates from the Fixer.io API and returns conversion definitions. Rates are
relative to EUR on the free tier.

**Returns:** `list<array{string, string, float}>` - Currency conversion definitions as
`[base, target, rate]` tuples.

**Throws:**

- `RuntimeException` - If the API request fails, returns invalid JSON, returns an API error, or
  is missing the expected rates data.

---

## See Also

- [ExchangeRateServiceInterface](ExchangeRateServiceInterface.md)
- [CurrencyService](../CurrencyService.md)
- [Money](../../QuantityType/Money.md)
