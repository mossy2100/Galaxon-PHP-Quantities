# FrankfurterService

Exchange rate service using the Frankfurter API, backed by European Central Bank data.

**Namespace:** `Galaxon\Quantities\Currencies\ExchangeRateServices`
**Implements:** [`ExchangeRateServiceInterface`](ExchangeRateServiceInterface.md)

---

## Overview

Fetches live exchange rates from the [Frankfurter API](https://frankfurter.dev/), which sources
its data from the European Central Bank (ECB). This service is completely free with no API key
required and no request limits. Rates are updated daily around 16:00 CET.

This is the simplest service to use as it requires no authentication or configuration.

---

## Methods

### getName()

```php
public function getName(): string
```

Returns the service name: `'Frankfurter (ECB)'`.

**Returns:** `string`

---

### getConversionDefinitions()

```php
public function getConversionDefinitions(): array
```

Fetches fresh exchange rates from the Frankfurter API and returns conversion definitions. Rates
are relative to EUR (the ECB's base currency).

**Returns:** `list<array{string, string, float}>` - Currency conversion definitions as
`[base, target, rate]` tuples.

**Throws:**

- `RuntimeException` - If the API request fails, returns invalid JSON, or is missing the expected
  rates data.

---

## See Also

- [ExchangeRateServiceInterface](ExchangeRateServiceInterface.md)
- [CurrencyService](../CurrencyService.md)
- [Money](../../QuantityType/Money.md)
