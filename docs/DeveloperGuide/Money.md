# Money

The `Money` class extends `Quantity` with currency support and locale-aware formatting.

Before working with Money quantities, you must initialise the `CurrencyService` — see [Currency Service](CurrencyService.md) for setup details.

```php
use Galaxon\Quantities\Currencies\CurrencyService;
use Galaxon\Quantities\Currencies\ExchangeRateServices\FrankfurterService;
use Galaxon\Quantities\QuantityType\Money;

CurrencyService::init(new FrankfurterService());
```

---

## Creating Money quantities

Create a Money object with a value and an ISO 4217 currency code:

```php
$price = new Money(100, 'USD');
$salary = new Money(75000, 'EUR');
$tip = new Money(15.50, 'GBP');
```

Currencies use [ISO 4217](https://en.wikipedia.org/wiki/ISO_4217) three-letter codes as their unit symbols (e.g. `USD`, `EUR`, `GBP`, `JPY`, `AUD`). All currencies belong to the `Financial` unit system.

---

## Currency conversion

Currency conversion works like any other unit conversion:

```php
$salary = new Money(75000, 'USD');
echo $salary->to('EUR');   // e.g. 69375 EUR
echo $salary->to('JPY');   // e.g. 11287500 JPY
echo $salary->to('GBP');   // e.g. 59625 GBP
```

---

## Currency formatting

The `Money` class overrides `__toString()` to use PHP's `NumberFormatter` for locale-aware currency display:

```php
CurrencyService::setLocale('en_US');

$price = new Money(1234.56, 'USD');
echo $price;  // $1,234.56
```

```php
CurrencyService::setLocale('de_DE');

$price = new Money(1234.56, 'EUR');
echo $price;  // 1.234,56 €
```

The locale can be set via `CurrencyService::init()` or changed later with `CurrencyService::setLocale()`. If no locale is set, it is auto-detected from the HTTP `Accept-Language` header or PHP's default locale. If none can be determined, the standard `format()` method is used as a fallback.

---

## See Also

- [Currency Service](CurrencyService.md) — Exchange rate services, caching, and configuration.
- [Currency Calculations](CurrencyCalculations.md) — Compound unit expressions with currencies.
- [Money reference](../Reference/QuantityType/Money.md)
