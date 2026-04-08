<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Currencies;

use Galaxon\Quantities\Currencies\CurrencyService;
use Galaxon\Quantities\Currencies\ExchangeRateServices\FrankfurterService;
use Galaxon\Quantities\Internal\Conversion;
use Galaxon\Quantities\Internal\Converter;
use Galaxon\Quantities\QuantityType\Money;
use Galaxon\Quantities\Services\ConversionService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests that exercise live currency conversion through an exchange rate service.
 *
 * These tests are slow and consume API quota. They use the Frankfurter service (free,
 * no API key) and a dedicated test data directory so production data is not touched.
 */
#[CoversClass(Converter::class)]
#[CoversClass(Money::class)]
final class CurrencyConversionTest extends TestCase
{
    /**
     * The test data directory path.
     */
    private const string TEST_DATA_DIR = __DIR__ . '/data';

    public static function setUpBeforeClass(): void
    {
        // Point data files to the test directory so we don't overwrite production data.
        CurrencyService::setDataDir(self::TEST_DATA_DIR);
        CurrencyService::init(new FrankfurterService());
    }

    public static function tearDownAfterClass(): void
    {
        // Reset static state.
        CurrencyService::setExchangeRateService(null);
        CurrencyService::setLocale(null);
        CurrencyService::setDataDir(CurrencyService::DEFAULT_DATA_DIR);
        Converter::removeAllInstances();
    }

    /**
     * Test that findConversion refreshes currency data when dimension contains 'C'.
     */
    public function testFindConversionRefreshesCurrencyData(): void
    {
        Converter::removeAllInstances();

        $conversion = ConversionService::find('AUD', 'USD');

        $this->assertInstanceOf(Conversion::class, $conversion);
        $this->assertGreaterThan(0.0, $conversion->factor->value);
    }

    /**
     * Test converting to the same currency returns the same value.
     */
    public function testConvertToSameCurrency(): void
    {
        $money = new Money(42.50, 'USD');
        $same = $money->to('USD');

        $this->assertSame(42.50, $same->value);
    }

    /**
     * Test converting USD to EUR.
     */
    public function testConvertUsdToEur(): void
    {
        $usd = new Money(100, 'USD');
        $eur = $usd->to('EUR');

        $this->assertInstanceOf(Money::class, $eur);
        $this->assertSame('EUR', $eur->compoundUnit->asciiSymbol);
        $this->assertGreaterThan(0.0, $eur->value);
    }

    /**
     * Test converting EUR to USD.
     */
    public function testConvertEurToUsd(): void
    {
        $eur = new Money(100, 'EUR');
        $usd = $eur->to('USD');

        $this->assertInstanceOf(Money::class, $usd);
        $this->assertSame('USD', $usd->compoundUnit->asciiSymbol);
        $this->assertGreaterThan(0.0, $usd->value);
    }

    /**
     * Test converting zero value.
     */
    public function testConvertZeroValue(): void
    {
        $money = new Money(0, 'USD');
        $eur = $money->to('EUR');

        $this->assertSame(0.0, $eur->value);
    }

    /**
     * Test round-trip conversion preserves approximate value.
     */
    public function testRoundTripConversion(): void
    {
        $usd = new Money(100, 'USD');
        $eur = $usd->to('EUR');
        $backToUsd = $eur->to('USD');

        // Round-trip should be approximately the original value.
        $this->assertEqualsWithDelta(100.0, $backToUsd->value, 1.0);
    }

    /**
     * Test the static Money::convert() method.
     */
    public function testStaticConvertMethod(): void
    {
        $value = Money::convert(100, 'USD', 'EUR');

        $this->assertGreaterThan(0.0, $value);
    }

    /**
     * Test adding money in different currencies.
     */
    public function testAddDifferentCurrencies(): void
    {
        $usd = new Money(100, 'USD');
        $eur = new Money(100, 'EUR');
        $result = $usd->add($eur);

        // Result should be in USD (the first operand's currency).
        $this->assertInstanceOf(Money::class, $result);
        $this->assertSame('USD', $result->compoundUnit->asciiSymbol);
        $this->assertGreaterThan(100.0, $result->value);
    }
}
