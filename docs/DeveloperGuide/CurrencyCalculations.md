# Currency Calculations

Because currencies are regular units in the Quantities system, they participate naturally in compound unit expressions and arithmetic. This enables real-world financial calculations that combine money with physical quantities.

---

## Prerequisites

All examples on this page assume the currency service has been initialised:

```php
use Galaxon\Quantities\Currencies\CurrencyService;
use Galaxon\Quantities\Currencies\ExchangeRateServices\FrankfurterService;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType\Money;
use Galaxon\Quantities\Services\UnitService;
use Galaxon\Quantities\UnitSystem;

CurrencyService::init(new FrankfurterService());
```

---

## Salary conversion

Divide an annual salary by a work capacity to get an hourly rate, then convert currencies:

```php
$salary = Quantity::create(120000, 'USD/y');
$capacity = Quantity::create(40, 'h/w');

$rate = $salary->div($capacity)->to('AUD/h');
echo $rate->format(precision: 2);  // e.g. 95.23 AUD/h
```

The library handles the time unit conversions (years to weeks to hours) and the currency conversion (USD to AUD) in a single chain.

---

## Precious metals

Convert a metal price from one mass unit and currency to another. For example, convert the price of silver from troy ounces (the standard trading unit) to price per kilogram in USD.

(NB: This example won't work with the Frankfurter service, which doesn't provide rates for precious metals.)

```php
CurrencyService::init(new OpenExchangeRatesService('your-api-key'));
UnitService::loadSystem(UnitSystem::Imperial);

$silverPerOzT = Quantity::create(1, 'XAG/oz t');
$silverPerKg = $silverPerOzT->to('USD/kg');
echo $silverPerKg->format(precision: 2);  // e.g. 1044.97 USD/kg
```

---

## Unit pricing

Compare prices of products sold in different quantities:

```php
UnitService::loadSystem(UnitSystem::UsCustomary);

// Olive oil: $8.99 for 500 mL vs $15.49 for 1 litre.
$priceA = Quantity::create(8.99 / 500, 'USD/mL');
$priceB = Quantity::create(15.49 / 1000, 'USD/mL');

echo $priceA->to('USD/L')->format(precision: 2);  // 17.98 USD/L
echo $priceB->to('USD/L')->format(precision: 2);  // 15.49 USD/L

// Or compare in a different unit system.
echo $priceA->to('USD/US gal')->format(precision: 2);  // 68.06 USD/US gal
```

---

## Cost per nutrient

Compare the cost-effectiveness of two protein powder products by computing the price per gram of protein.

```php
use Galaxon\Quantities\QuantityType\Mass;

// Product A: $126.95 for 2.5 kg, 30.1 g protein per 44 g serve.
$priceA = new Money(126.95, 'AUD');
$productMassA = new Mass(2.5, 'kg');
$servingSizeA = new Mass(44, 'g');
$proteinPerServeA = new Mass(30.1, 'g');

$costPerGramPowderA = $priceA->div($productMassA); // AUD/kg
$proteinRatioA = $proteinPerServeA->div($servingSizeA); // g/g (dimensionless)
$proteinCostA = $costPerGramPowderA->div($proteinRatioA); // AUD/kg of protein
echo $proteinCostA->to('AUD/g')->format(precision: 4); // 0.0742 AUD/g

// Product B: $69.95 for 900 g, 22.7 g protein per 30 g serve.
$priceB = new Money(69.95, 'AUD');
$productMassB = new Mass(900, 'g');
$servingSizeB = new Mass(30, 'g');
$proteinPerServeB = new Mass(22.7, 'g');

$costPerGramPowderB = $priceB->div($productMassB);
$proteinRatioB = $proteinPerServeB->div($servingSizeB);
$proteinCostB = $costPerGramPowderB->div($proteinRatioB);
echo $proteinCostB->to('AUD/g')->format(precision: 4); // 0.1027 AUD/g

// Product A is ~28% cheaper per gram of protein.
```

---

## Energy cost calculation

Calculate the cost of running an appliance:

```php
$power = Quantity::create(2.4, 'kW');
$duration = Quantity::create(3, 'h');
$energyRate = Quantity::create(0.30, 'AUD/kW*h');

$energy = $power->mul($duration);            // 7.2 kW*h
$cost = $energy->mul($energyRate)->to('AUD'); // 2.16 AUD
echo $cost->format(precision: 2);             // 2.16 AUD
```

---

## Currency arithmetic

Standard Quantity arithmetic works across currencies. Values are converted automatically:

```php
$usd = new Money(1000, 'USD');
$eur = new Money(500, 'EUR');

// Adding different currencies converts to the left operand's unit.
$total = $usd->add($eur);
echo $total;  // e.g. $1,541.23

// Subtraction works the same way.
$difference = $usd->sub($eur);
echo $difference;  // e.g. $458.77
```

---

## See Also

- [Money](Money.md) — Creating and using Money quantities.
- [Currency Service](CurrencyService.md) — Exchange rate services, caching, and configuration.
- [Arithmetic Operations](ArithmeticOperations.md) — Add, subtract, multiply, and divide quantities.
- [Unit Conversion](UnitConversion.md) — Converting between units.
