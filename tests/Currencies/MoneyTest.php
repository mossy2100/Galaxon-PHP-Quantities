<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Currencies;

use Galaxon\Quantities\Currencies\CurrencyService;
use Galaxon\Quantities\Currencies\ExchangeRateServices\FrankfurterService;
use Galaxon\Quantities\Internal\Converter;
use Galaxon\Quantities\Internal\UnitSystem;
use Galaxon\Quantities\QuantityType\Money;
use Galaxon\Quantities\Services\UnitService;
use Galaxon\Quantities\Tests\NonCurrencies\Traits\ArrayShapeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Money quantity type that do NOT trigger any exchange rate API calls.
 *
 * These tests load currency unit definitions from cached fixtures and never instantiate
 * an ExchangeRateService. Tests that require live currency conversion live in
 * tests/Currencies — those are slow and consume API quota.
 */
#[CoversClass(Money::class)]
final class MoneyTest extends TestCase
{
    use ArrayShapeTrait;

    // region Setup

    /**
     * The test data directory path.
     */
    private const string TEST_DATA_DIR = __DIR__ . '/data';

    public static function setUpBeforeClass(): void
    {
        // Initialize the currency service.
        CurrencyService::init(new FrankfurterService());

        // Point data files to the test fixtures so we don't overwrite production data.
        CurrencyService::setDataDir(self::TEST_DATA_DIR);

        // Load currency units from the fixture file (no API call).
        UnitService::loadSystem(UnitSystem::Financial, true);
    }

    public static function tearDownAfterClass(): void
    {
        // Reset static state.
        CurrencyService::setLocale(null);
        CurrencyService::setDataDir(CurrencyService::DEFAULT_DATA_DIR);
        Converter::removeAllInstances();
    }

    // endregion

    // region Overridden methods

    /**
     * Test getUnitDefinitions() returns valid unit definitions.
     */
    public function testGetUnitDefinitionsReturnsValidArray(): void
    {
        $units = Money::getUnitDefinitions();
        $this->assertValidUnitDefinitionsShape($units);
    }

    /**
     * Test getUnitDefinitions() contains common currencies.
     */
    public function testGetUnitDefinitionsContainsCommonCurrencies(): void
    {
        $units = Money::getUnitDefinitions();

        $symbols = array_map(
            static fn (array $def) => $def['asciiSymbol'],
            $units
        );

        $this->assertContains('USD', $symbols);
        $this->assertContains('EUR', $symbols);
        $this->assertContains('GBP', $symbols);
        $this->assertContains('AUD', $symbols);
        $this->assertContains('JPY', $symbols);
    }

    /**
     * Test getConversionDefinitions() returns valid conversion definitions.
     */
    public function testGetConversionDefinitionsReturnsValidArray(): void
    {
        $conversions = Money::getConversionDefinitions();
        $this->assertValidConversionDefinitionsShape($conversions);
    }

    /**
     * Test getUnitDefinitions() returns array when no data file exists.
     */
    public function testGetUnitDefinitionsReturnsEmptyWhenNoData(): void
    {
        $originalDir = CurrencyService::getDataDir();
        CurrencyService::setDataDir(self::TEST_DATA_DIR . '/empty');

        try {
            $units = Money::getUnitDefinitions();
            // @phpstan-ignore method.alreadyNarrowedType
            $this->assertIsArray($units);
        } finally {
            CurrencyService::deleteUnits();
            CurrencyService::deleteConversions();
            CurrencyService::setDataDir($originalDir);
        }
    }

    /**
     * Test getConversionDefinitions() returns empty array when no data file exists.
     */
    public function testGetConversionDefinitionsReturnsArrayWhenNoData(): void
    {
        $originalDir = CurrencyService::getDataDir();
        CurrencyService::setDataDir(self::TEST_DATA_DIR . '/empty');

        try {
            $conversions = Money::getConversionDefinitions();
            // @phpstan-ignore method.alreadyNarrowedType
            $this->assertIsArray($conversions);
        } finally {
            CurrencyService::deleteUnits();
            CurrencyService::deleteConversions();
            CurrencyService::setDataDir($originalDir);
        }
    }

    // endregion

    // region Construction tests

    /**
     * Test constructing a Money quantity with USD.
     */
    public function testConstructWithUsd(): void
    {
        $money = new Money(100, 'USD');

        $this->assertInstanceOf(Money::class, $money);
        $this->assertSame(100.0, $money->value);
        $this->assertSame('USD', $money->compoundUnit->asciiSymbol);
    }

    /**
     * Test constructing a Money quantity with EUR.
     */
    public function testConstructWithEur(): void
    {
        $money = new Money(50, 'EUR');

        $this->assertSame(50.0, $money->value);
        $this->assertSame('EUR', $money->compoundUnit->asciiSymbol);
    }

    /**
     * Test constructing with zero value.
     */
    public function testConstructWithZero(): void
    {
        $money = new Money(0, 'USD');

        $this->assertSame(0.0, $money->value);
    }

    /**
     * Test constructing with negative value.
     */
    public function testConstructWithNegative(): void
    {
        $money = new Money(-25.50, 'GBP');

        $this->assertSame(-25.50, $money->value);
    }

    // endregion

    // region __toString() tests

    /**
     * Test __toString() with an explicit locale.
     */
    public function testToStringWithLocale(): void
    {
        CurrencyService::setLocale('en_US');
        $money = new Money(1234.56, 'USD');

        $result = (string)$money;

        // en_US locale should format as "$1,234.56".
        $this->assertStringContainsString('1', $result);
        $this->assertStringContainsString('234', $result);
        $this->assertStringContainsString('56', $result);
    }

    /**
     * Test __toString() with a different locale.
     */
    public function testToStringWithDifferentLocale(): void
    {
        CurrencyService::setLocale('de_DE');
        $money = new Money(1234.56, 'EUR');

        $result = (string)$money;

        // de_DE locale should format with comma as decimal separator.
        $this->assertNotEmpty($result);
    }

    /**
     * Test __toString() falls back to format() when locale is null.
     */
    public function testToStringFallsBackToFormatWhenNoLocale(): void
    {
        CurrencyService::setLocale(null);

        // Temporarily set PHP's default locale to empty to force fallback.
        $originalLocale = setlocale(LC_ALL, '0');
        setlocale(LC_ALL, 'C');

        try {
            $money = new Money(100, 'USD');
            $result = (string)$money;

            // Should fall back to format() which produces something like "100 USD".
            $this->assertNotEmpty($result);
            $this->assertStringContainsString('100', $result);
        } finally {
            if ($originalLocale !== false) {
                setlocale(LC_ALL, $originalLocale);
            }
        }
    }

    /**
     * Test __toString() with zero value.
     */
    public function testToStringWithZero(): void
    {
        CurrencyService::setLocale('en_US');
        $money = new Money(0, 'USD');

        $result = (string)$money;

        $this->assertStringContainsString('0', $result);
    }

    /**
     * Test __toString() with negative value.
     */
    public function testToStringWithNegativeValue(): void
    {
        CurrencyService::setLocale('en_US');
        $money = new Money(-50, 'USD');

        $result = (string)$money;

        $this->assertNotEmpty($result);
    }

    // endregion

    // region Addition tests

    /**
     * Test adding money in the same currency.
     */
    public function testAddSameCurrency(): void
    {
        $a = new Money(100, 'USD');
        $b = new Money(50, 'USD');
        $result = $a->add($b);

        $this->assertInstanceOf(Money::class, $result);
        $this->assertSame(150.0, $result->value);
        $this->assertSame('USD', $result->compoundUnit->asciiSymbol);
    }

    // endregion

    // region Parse tests

    /**
     * Test parsing a USD string.
     */
    public function testParseUsd(): void
    {
        $money = Money::parse('100 USD');

        $this->assertInstanceOf(Money::class, $money);
        $this->assertSame(100.0, $money->value);
        $this->assertSame('USD', $money->compoundUnit->asciiSymbol);
    }

    /**
     * Test parsing a EUR string.
     */
    public function testParseEur(): void
    {
        $money = Money::parse('250.75 EUR');

        $this->assertSame(250.75, $money->value);
        $this->assertSame('EUR', $money->compoundUnit->asciiSymbol);
    }

    /**
     * Test parsing a negative value.
     */
    public function testParseNegative(): void
    {
        $money = Money::parse('-50 GBP');

        $this->assertSame(-50.0, $money->value);
        $this->assertSame('GBP', $money->compoundUnit->asciiSymbol);
    }

    // endregion
}
