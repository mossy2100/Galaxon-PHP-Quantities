<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Currencies;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
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
        CurrencyService::setExchangeRateService(null);
        CurrencyService::setLocale(null);
        CurrencyService::setRatesTtl(3600);
        CurrencyService::setCurrenciesTtl(2592000);
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

    public function testSetDataDirThrowsForEmptyPath(): void
    {
        $this->expectException(DomainException::class);
        CurrencyService::setDataDir('');
    }

    public function testSetDataDirThrowsForSlashOnly(): void
    {
        $this->expectException(DomainException::class);
        CurrencyService::setDataDir('/');
    }

    public function testSetDataDirCreatesNonExistentDirectory(): void
    {
        $tempDir = sys_get_temp_dir() . '/currency-test-' . uniqid();
        self::assertDirectoryDoesNotExist($tempDir);

        CurrencyService::setDataDir($tempDir);

        self::assertDirectoryExists($tempDir);
        self::assertSame($tempDir, CurrencyService::getDataDir());

        // Clean up.
        rmdir($tempDir);
    }

    // endregion

    // region getLocale / setLocale

    public function testGetLocaleReturnsExplicitlySetLocale(): void
    {
        CurrencyService::setLocale('en_AU');
        self::assertSame('en_AU', CurrencyService::getLocale());
    }

    public function testGetLocaleReturnsSomethingWhenNotSet(): void
    {
        CurrencyService::setLocale(null);
        $locale = CurrencyService::getLocale();

        // Should fall back to PHP's default locale.
        self::assertNotNull($locale);
        self::assertNotEmpty($locale);
    }

    public function testSetLocaleAcceptsNull(): void
    {
        CurrencyService::setLocale('en_US');
        CurrencyService::setLocale(null);

        // After clearing, getLocale() falls back to auto-detection rather than returning the old value.
        // We can't assert null because getLocale() auto-detects, but we verify no exception is thrown.
        self::assertNotSame('en_US', CurrencyService::getLocale());
    }

    public function testSetLocaleThrowsForInvalidLocale(): void
    {
        $this->expectException(FormatException::class);
        CurrencyService::setLocale('!!invalid');
    }

    public function testSetLocaleAcceptsVariousValidFormats(): void
    {
        CurrencyService::setLocale('en');
        self::assertSame('en', CurrencyService::getLocale());

        CurrencyService::setLocale(null);
        CurrencyService::setLocale('en_US');
        self::assertSame('en_US', CurrencyService::getLocale());

        CurrencyService::setLocale(null);
        CurrencyService::setLocale('zh-Hant-TW');
        self::assertSame('zh-Hant-TW', CurrencyService::getLocale());
    }

    // endregion

    // region TTL getters / setters

    public function testGetRatesTtlReturnsDefault(): void
    {
        self::assertSame(3600, CurrencyService::getRatesTtl());
    }

    public function testSetRatesTtlChangesValue(): void
    {
        CurrencyService::setRatesTtl(1800);
        self::assertSame(1800, CurrencyService::getRatesTtl());
    }

    public function testSetRatesTtlAcceptsZero(): void
    {
        CurrencyService::setRatesTtl(0);
        self::assertSame(0, CurrencyService::getRatesTtl());
    }

    public function testSetRatesTtlThrowsForNegativeValue(): void
    {
        $this->expectException(DomainException::class);
        CurrencyService::setRatesTtl(-1);
    }

    public function testGetCurrenciesTtlReturnsDefault(): void
    {
        self::assertSame(2592000, CurrencyService::getCurrenciesTtl());
    }

    public function testSetCurrenciesTtlChangesValue(): void
    {
        CurrencyService::setCurrenciesTtl(86400);
        self::assertSame(86400, CurrencyService::getCurrenciesTtl());
    }

    public function testSetCurrenciesTtlAcceptsZero(): void
    {
        CurrencyService::setCurrenciesTtl(0);
        self::assertSame(0, CurrencyService::getCurrenciesTtl());
    }

    public function testSetCurrenciesTtlThrowsForNegativeValue(): void
    {
        $this->expectException(DomainException::class);
        CurrencyService::setCurrenciesTtl(-1);
    }

    // endregion

    // region loadUnitData

    public function testLoadUnitDataReturnsNullWhenNoFile(): void
    {
        // Test data dir has no CurrencyUnits.php yet.
        CurrencyService::setDataDir(self::TEST_DATA_DIR . '/empty');
        self::assertNull(CurrencyService::loadUnitData());
    }

    public function testLoadUnitDataReturnsValidArray(): void
    {
        // Ensure data exists by refreshing.
        CurrencyService::refreshUnits(true);
        $data = CurrencyService::loadUnitData();

        self::assertNotNull($data);
        self::assertArrayHasKey('whenFetched', $data);
        self::assertArrayHasKey('definitions', $data);
        self::assertIsArray($data['definitions']);
        self::assertNotEmpty($data['definitions']);
    }

    public function testLoadUnitDataContainsCommonCurrencies(): void
    {
        // Ensure data exists by refreshing.
        CurrencyService::refreshUnits(true);
        $data = CurrencyService::loadUnitData();
        self::assertNotNull($data);

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

    public function testLoadConversionDataReturnsValidArray(): void
    {
        // Ensure data exists by refreshing.
        CurrencyService::setExchangeRateService(new FrankfurterService());
        CurrencyService::refreshConversions(true);
        $data = CurrencyService::loadConversionData();

        self::assertNotNull($data);
        self::assertArrayHasKey('whenFetched', $data);
        self::assertArrayHasKey('serviceName', $data);
        self::assertArrayHasKey('definitions', $data);
        self::assertIsArray($data['definitions']);
        self::assertNotEmpty($data['definitions']);
    }

    // endregion

    // region refreshUnits

    public function testRefreshCurrencyUnitsWithBypassCache(): void
    {
        $result = CurrencyService::refreshUnits(true);

        self::assertTrue($result);
        self::assertFileExists(self::TEST_DATA_DIR . '/CurrencyUnits.php');
        self::assertFileExists(self::TEST_DATA_DIR . '/CurrencyData.xml');
    }

    public function testRefreshCurrencyUnitsReturnsFalseWhenCached(): void
    {
        // First call populates the cache.
        CurrencyService::refreshUnits(true);

        // Second call without bypass should return false (still fresh).
        $result = CurrencyService::refreshUnits(false);
        self::assertFalse($result);
    }

    public function testRefreshCurrencyUnitsWritesValidData(): void
    {
        CurrencyService::refreshUnits(true);

        $data = CurrencyService::loadUnitData();
        self::assertNotNull($data);
        self::assertArrayHasKey('whenFetched', $data);
        self::assertArrayHasKey('definitions', $data);
        self::assertNotEmpty($data['definitions']);
    }

    // endregion

    // region refreshConversions

    public function testRefreshCurrencyConversionsWithBypassCache(): void
    {
        CurrencyService::setExchangeRateService(new FrankfurterService());
        $result = CurrencyService::refreshConversions(true);

        self::assertTrue($result);
        self::assertFileExists(self::TEST_DATA_DIR . '/CurrencyConversions.php');
    }

    public function testRefreshCurrencyConversionsReturnsFalseWhenCached(): void
    {
        CurrencyService::setExchangeRateService(new FrankfurterService());

        // First call populates the cache.
        CurrencyService::refreshConversions(true);

        // Second call without bypass should return false (still fresh).
        $result = CurrencyService::refreshConversions(false);
        self::assertFalse($result);
    }

    public function testRefreshCurrencyConversionsWritesValidData(): void
    {
        CurrencyService::setExchangeRateService(new FrankfurterService());
        CurrencyService::refreshConversions(true);

        $data = CurrencyService::loadConversionData();
        self::assertNotNull($data);
        self::assertArrayHasKey('whenFetched', $data);
        self::assertArrayHasKey('serviceName', $data);
        self::assertArrayHasKey('definitions', $data);
        self::assertNotEmpty($data['definitions']);
    }

    public function testRefreshCurrencyConversionsThrowsWithoutService(): void
    {
        CurrencyService::setExchangeRateService(null);
        $this->expectException(LogicException::class);
        CurrencyService::refreshConversions(true);
    }

    // endregion

    // region refresh

    public function testRefreshFromScratchCreatesDataFiles(): void
    {
        // Delete any existing data files so refresh() must fetch fresh data.
        @unlink(self::TEST_DATA_DIR . '/CurrencyUnits.php');
        @unlink(self::TEST_DATA_DIR . '/CurrencyConversions.php');
        @unlink(self::TEST_DATA_DIR . '/CurrencyData.xml');

        CurrencyService::setExchangeRateService(new FrankfurterService());
        CurrencyService::refresh();

        self::assertFileExists(self::TEST_DATA_DIR . '/CurrencyUnits.php');
        self::assertFileExists(self::TEST_DATA_DIR . '/CurrencyConversions.php');
    }

    public function testRefreshWithBypassCacheRefreshesEvenWhenCached(): void
    {
        // First call populates the cache.
        CurrencyService::setExchangeRateService(new FrankfurterService());
        CurrencyService::refresh();

        // Record the timestamps.
        clearstatcache();
        $unitsMtime = filemtime(self::TEST_DATA_DIR . '/CurrencyUnits.php');
        $conversionsMtime = filemtime(self::TEST_DATA_DIR . '/CurrencyConversions.php');

        // Wait a moment so timestamps differ if files are rewritten.
        sleep(1);

        // Refresh with bypass should re-fetch even though the cache is fresh.
        CurrencyService::refresh(bypassCache: true);

        clearstatcache();
        $newUnitsMtime = filemtime(self::TEST_DATA_DIR . '/CurrencyUnits.php');
        $newConversionsMtime = filemtime(self::TEST_DATA_DIR . '/CurrencyConversions.php');

        self::assertGreaterThan($unitsMtime, $newUnitsMtime);
        self::assertGreaterThan($conversionsMtime, $newConversionsMtime);
    }

    // endregion

    // region init

    public function testInitConfiguresService(): void
    {
        CurrencyService::init(new FrankfurterService());

        self::assertInstanceOf(FrankfurterService::class, CurrencyService::getExchangeRateService());
    }

    public function testInitWithCustomLocale(): void
    {
        CurrencyService::init(new FrankfurterService(), 'de_DE');

        self::assertSame('de_DE', CurrencyService::getLocale());
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
