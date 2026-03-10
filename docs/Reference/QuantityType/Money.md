# Money

Represents money quantities with currency conversion and locale-aware formatting.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Money` class handles monetary values with support for all ISO 4217 currencies. Unlike other quantity types, currency units and conversion rates are loaded dynamically via [`CurrencyService`](../Currencies/CurrencyService.md) rather than being hard-coded.

All currency units belong to the `Financial` unit system. The base unit is XAU (gold troy ounces).

Before using `Money`, you must initialize the `CurrencyService`. See [CurrencyService](../Currencies/CurrencyService.md) for setup details.

---

## Currency Formatting

`Money` overrides `__toString()` to produce locale-specific currency formatting using PHP's `NumberFormatter`:

```php
use Galaxon\Quantities\QuantityType\Money;
use Galaxon\Quantities\Currencies\CurrencyService;

CurrencyService::setLocale('en_US');
$price = new Money(42.50, 'USD');
echo $price;  // $42.50

CurrencyService::setLocale('de_DE');
$price = new Money(42.50, 'EUR');
echo $price;  // 42,50 €
```

If no locale can be determined, `Money` falls back to the standard `format()` method inherited from `Quantity`.

---

## Usage Examples

```php
use Galaxon\Quantities\QuantityType\Money;
use Galaxon\Quantities\Currencies\CurrencyService;

// Create money values.
$usd = new Money(100, 'USD');
$eur = new Money(50, 'EUR');

// Convert between currencies.
$inEur = $usd->to('EUR');
$inGbp = $eur->to('GBP');

// String output uses locale-aware formatting.
echo $usd;  // $100.00

// Use format() for the standard Quantity format.
echo $usd->format();  // 100 USD
```

---

## See Also

- **[CurrencyService](../Currencies/CurrencyService.md)** - Currency data management, setup, and locale configuration
- **[ExchangeRateServiceInterface](../Currencies/ExchangeRateServices/ExchangeRateServiceInterface.md)** - Contract for exchange rate providers
- **[Quantity](../Quantity.md)** - Base class documentation
- **[UnitSystem](../UnitSystem.md)** - Unit system classification (Financial system)
