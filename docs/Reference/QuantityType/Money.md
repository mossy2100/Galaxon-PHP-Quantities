# Money

Represents money quantities with currency conversion and locale-aware formatting.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Money` class handles monetary values with support for all [ISO 4217](https://en.wikipedia.org/wiki/ISO_4217) currencies. Unlike other quantity types, currency units and conversion rates are loaded dynamically via [`CurrencyService`](../Currencies/CurrencyService.md) rather than being hard-coded.

All currency units belong to the `Financial` unit system. The base unit is XAU (gold troy ounces).

Before using `Money`, you must initialize the `CurrencyService`:

```php
use Galaxon\Quantities\Currencies\CurrencyService;
use Galaxon\Quantities\Currencies\ExchangeRateServices\FrankfurterService;

CurrencyService::init(new FrankfurterService());
```

See [CurrencyService](../Currencies/CurrencyService.md) for setup details.

---

## Overridden Methods

### \_\_toString()

```php
public function __toString(): string
```

Convert the currency value to a locale-specific formatted string using PHP's `NumberFormatter`. Falls back to `format()` if no locale is available.

The locale can be set via `CurrencyService::init()` or changed later with `CurrencyService::setLocale()`. If no locale is set, it is auto-detected from the HTTP `Accept-Language` header or PHP's default locale.

```php
CurrencyService::setLocale('en_US');
$price = new Money(1234.56, 'USD');
echo $price;  // $1,234.56

CurrencyService::setLocale('de_DE');
$price = new Money(1234.56, 'EUR');
echo $price;  // 1.234,56 €
```

---

## Usage Examples

```php
use Galaxon\Quantities\QuantityType\Money;

// Create money values.
$price = new Money(100, 'USD');
$salary = new Money(75000, 'EUR');

// Convert between currencies.
echo $salary->to('USD');  // e.g. 81000 USD
echo $salary->to('JPY');  // e.g. 11287500 JPY
echo $salary->to('GBP');  // e.g. 63750 GBP

// String output uses locale-aware formatting.
echo $price;  // $100.00

// Use format() for the standard Quantity format.
echo $price->format();  // 100 USD
```

---

## See Also

- **[CurrencyService](../Currencies/CurrencyService.md)** — Currency data management, setup, and locale configuration.
- **[ExchangeRateServiceInterface](../Currencies/ExchangeRateServices/ExchangeRateServiceInterface.md)** — Contract for exchange rate providers.
- **[Quantity](../Quantity.md)** — Base class documentation.
- **[UnitSystem](../UnitSystem.md)** — Unit system classification (Financial system).
