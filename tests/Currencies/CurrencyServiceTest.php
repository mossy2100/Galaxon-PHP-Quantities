<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Currencies;

use Galaxon\Quantities\Currencies\CurrencyService;
use Galaxon\Quantities\Currencies\ExchangeRateServices\FrankfurterService;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for CurrencyService.
 */
#[CoversClass(CurrencyService::class)]
class CurrencyServiceTest extends TestCase
{
    /**
     * The test data directory path.
     */
    private const string TEST_DATA_DIR = __DIR__ . '/data';

    // region Setup

    protected function setUp(): void
    {
        // Point data files to the test directory so we don't overwrite production data.
        CurrencyService::setDataDir(self::TEST_DATA_DIR);
    }

    protected function tearDown(): void
    {
        // Reset static state after each test.
        CurrencyService::$exchangeRateService = null;
        CurrencyService::$locale = null;
        CurrencyService::setDataDir(CurrencyService::DEFAULT_DATA_DIR);
    }

    // endregion

    // region setDataDir / getDataDir

    public function testSetDataDirChangesPath(): void
    {
        CurrencyService::setDataDir('/tmp/test-currencies');
        self::assertSame('/tmp/test-currencies', CurrencyService::getDataDir());
    }

    public function testSetDataDirStripsTrailingSlash(): void
    {
        CurrencyService::setDataDir('/tmp/test-currencies/');
        self::assertSame('/tmp/test-currencies', CurrencyService::getDataDir());
    }

    public function testGetUnitsFilePathReflectsDataDir(): void
    {
        CurrencyService::setDataDir('/tmp/test-currencies');
        self::assertSame('/tmp/test-currencies/CurrencyUnits.php', CurrencyService::getUnitsFilePath());
    }

    public function testGetConversionsFilePathReflectsDataDir(): void
    {
        CurrencyService::setDataDir('/tmp/test-currencies');
        self::assertSame('/tmp/test-currencies/CurrencyConversions.php', CurrencyService::getConversionsFilePath());
    }

    // endregion

    // region ensureExchangeRateServiceConfigured

    public function testEnsureExchangeRateServiceConfiguredThrowsWhenNotSet(): void
    {
        CurrencyService::$exchangeRateService = null;
        $this->expectException(LogicException::class);
        CurrencyService::ensureExchangeRateServiceConfigured();
    }

    public function testEnsureExchangeRateServiceConfiguredPassesWhenSet(): void
    {
        CurrencyService::$exchangeRateService = new FrankfurterService();
        CurrencyService::ensureExchangeRateServiceConfigured();

        // No exception means success.
        self::assertTrue(true); // @phpstan-ignore staticMethod.alreadyNarrowedType
    }

    // endregion

    // region getLocale

    public function testGetLocaleReturnsExplicitlySetLocale(): void
    {
        CurrencyService::$locale = 'en_AU';
        self::assertSame('en_AU', CurrencyService::getLocale());
    }

    public function testGetLocaleReturnsSomethingWhenNotSet(): void
    {
        CurrencyService::$locale = null;
        $locale = CurrencyService::getLocale();

        // Should fall back to PHP's default locale.
        self::assertNotNull($locale);
        self::assertNotEmpty($locale);
    }

    // endregion

    // region loadUnitData

    public function testLoadUnitDataReturnsNullWhenNoFile(): void
    {
        // Test data dir has no CurrencyUnits.php yet.
        CurrencyService::setDataDir(self::TEST_DATA_DIR . '/empty');
        self::assertNull(CurrencyService::loadUnitData());
    }

    public function testLoadUnitDataReturnsArrayFromProductionData(): void
    {
        // Read from the production data directory.
        CurrencyService::setDataDir(CurrencyService::DEFAULT_DATA_DIR);
        $data = CurrencyService::loadUnitData();

        if ($data === null) {
            self::markTestSkipped('CurrencyUnits.php data file does not exist.');
        }

        self::assertArrayHasKey('whenFetched', $data);
        self::assertArrayHasKey('definitions', $data);
        self::assertIsArray($data['definitions']);
        self::assertNotEmpty($data['definitions']);
    }

    public function testLoadUnitDataContainsCommonCurrencies(): void
    {
        CurrencyService::setDataDir(CurrencyService::DEFAULT_DATA_DIR);
        $data = CurrencyService::loadUnitData();

        if ($data === null) {
            self::markTestSkipped('CurrencyUnits.php data file does not exist.');
        }

        // Check that some well-known currencies are present by symbol.
        $symbols = array_map(
            static fn (array $def) => $def['asciiSymbol'],
            $data['definitions']
        );

        self::assertContains('USD', $symbols);
        self::assertContains('EUR', $symbols);
        self::assertContains('GBP', $symbols);
        self::assertContains('AUD', $symbols);
        self::assertContains('JPY', $symbols);
    }

    // endregion

    // region loadConversionData

    public function testLoadConversionDataReturnsNullWhenNoFile(): void
    {
        CurrencyService::setDataDir(self::TEST_DATA_DIR . '/empty');
        self::assertNull(CurrencyService::loadConversionData());
    }

    public function testLoadConversionDataReturnsArrayFromProductionData(): void
    {
        CurrencyService::setDataDir(CurrencyService::DEFAULT_DATA_DIR);
        $data = CurrencyService::loadConversionData();

        if ($data === null) {
            self::markTestSkipped('CurrencyConversions.php data file does not exist.');
        }

        self::assertArrayHasKey('whenFetched', $data);
        self::assertArrayHasKey('serviceName', $data);
        self::assertArrayHasKey('definitions', $data);
        self::assertIsArray($data['definitions']);
        self::assertNotEmpty($data['definitions']);
    }

    // endregion

    // region refreshCurrencyUnits

    public function testRefreshCurrencyUnitsWithBypassCache(): void
    {
        $result = CurrencyService::refreshCurrencyUnits(true);

        self::assertTrue($result);
        self::assertFileExists(self::TEST_DATA_DIR . '/CurrencyUnits.php');
        self::assertFileExists(self::TEST_DATA_DIR . '/CurrencyData.xml');
    }

    public function testRefreshCurrencyUnitsReturnsFalseWhenCached(): void
    {
        // First call populates the cache.
        CurrencyService::refreshCurrencyUnits(true);

        // Second call without bypass should return false (still fresh).
        $result = CurrencyService::refreshCurrencyUnits(false);
        self::assertFalse($result);
    }

    public function testRefreshCurrencyUnitsWritesValidData(): void
    {
        CurrencyService::refreshCurrencyUnits(true);

        $data = CurrencyService::loadUnitData();
        self::assertNotNull($data);
        self::assertArrayHasKey('whenFetched', $data);
        self::assertArrayHasKey('definitions', $data);
        self::assertNotEmpty($data['definitions']);
    }

    // endregion

    // region refreshCurrencyConversions

    public function testRefreshCurrencyConversionsWithBypassCache(): void
    {
        CurrencyService::$exchangeRateService = new FrankfurterService();
        $result = CurrencyService::refreshCurrencyConversions(true);

        self::assertTrue($result);
        self::assertFileExists(self::TEST_DATA_DIR . '/CurrencyConversions.php');
    }

    public function testRefreshCurrencyConversionsReturnsFalseWhenCached(): void
    {
        CurrencyService::$exchangeRateService = new FrankfurterService();

        // First call populates the cache.
        CurrencyService::refreshCurrencyConversions(true);

        // Second call without bypass should return false (still fresh).
        $result = CurrencyService::refreshCurrencyConversions(false);
        self::assertFalse($result);
    }

    public function testRefreshCurrencyConversionsWritesValidData(): void
    {
        CurrencyService::$exchangeRateService = new FrankfurterService();
        CurrencyService::refreshCurrencyConversions(true);

        $data = CurrencyService::loadConversionData();
        self::assertNotNull($data);
        self::assertArrayHasKey('whenFetched', $data);
        self::assertArrayHasKey('serviceName', $data);
        self::assertArrayHasKey('definitions', $data);
        self::assertNotEmpty($data['definitions']);
    }

    public function testRefreshCurrencyConversionsThrowsWithoutService(): void
    {
        CurrencyService::$exchangeRateService = null;
        $this->expectException(LogicException::class);
        CurrencyService::refreshCurrencyConversions(true);
    }

    // endregion

    // region refresh

    public function testRefreshFromScratchCreatesDataFiles(): void
    {
        // Delete any existing data files so refresh() must fetch fresh data.
        @unlink(self::TEST_DATA_DIR . '/CurrencyUnits.php');
        @unlink(self::TEST_DATA_DIR . '/CurrencyConversions.php');
        @unlink(self::TEST_DATA_DIR . '/CurrencyData.xml');

        CurrencyService::$exchangeRateService = new FrankfurterService();
        CurrencyService::refresh();

        self::assertFileExists(self::TEST_DATA_DIR . '/CurrencyUnits.php');
        self::assertFileExists(self::TEST_DATA_DIR . '/CurrencyConversions.php');
    }

    // endregion

    // region init

    public function testInitConfiguresService(): void
    {
        CurrencyService::init(new FrankfurterService());

        self::assertInstanceOf(FrankfurterService::class, CurrencyService::$exchangeRateService);
    }

    public function testInitWithCustomLocale(): void
    {
        CurrencyService::init(new FrankfurterService(), 'de_DE');

        self::assertSame('de_DE', CurrencyService::$locale);
    }

    public function testInitWritesDataToConfiguredDir(): void
    {
        CurrencyService::init(new FrankfurterService());

        // Data files should be written to the test data directory, not production.
        self::assertFileExists(self::TEST_DATA_DIR . '/CurrencyUnits.php');
        self::assertFileExists(self::TEST_DATA_DIR . '/CurrencyConversions.php');
    }

    public function testInitDoesNotWriteToProductionDir(): void
    {
        // setUp() redirects the data dir to the test directory. This test verifies that init() respects
        // that redirection and does not write to the production data directory.
        $unitsPath = CurrencyService::DEFAULT_DATA_DIR . '/CurrencyUnits.php';
        $conversionsPath = CurrencyService::DEFAULT_DATA_DIR . '/CurrencyConversions.php';

        // Snapshot the production files' state before init().
        $unitsMtime = file_exists($unitsPath) ? filemtime($unitsPath) : null;
        $conversionsMtime = file_exists($conversionsPath) ? filemtime($conversionsPath) : null;

        CurrencyService::init(new FrankfurterService());

        // If production files existed before, they should be untouched.
        // If they didn't exist, they should still not exist.
        if ($unitsMtime !== null) {
            clearstatcache(true, $unitsPath);
            self::assertSame($unitsMtime, filemtime($unitsPath));
        } else {
            self::assertFileDoesNotExist($unitsPath);
        }
        if ($conversionsMtime !== null) {
            clearstatcache(true, $conversionsPath);
            self::assertSame($conversionsMtime, filemtime($conversionsPath));
        } else {
            self::assertFileDoesNotExist($conversionsPath);
        }
    }

    // endregion
}
