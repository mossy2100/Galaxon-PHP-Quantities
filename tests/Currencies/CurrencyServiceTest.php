<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Currencies;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Quantities\Currencies\CurrencyService;
use Galaxon\Quantities\Currencies\ExchangeRateServices\ExchangeRateServiceInterface;
use Galaxon\Quantities\Currencies\ExchangeRateServices\FrankfurterService;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

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

        // Configure an exchange rate service by default. fetchUnits() triggers Converter::loadConversions()
        // which eventually asks Money for its conversion definitions, and that needs a service. Tests that
        // specifically require no service configured can override this with setExchangeRateService(null).
        CurrencyService::setExchangeRateService(new FrankfurterService());
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
        $this->assertSame('/tmp/test-currencies', CurrencyService::getDataDir());
    }

    public function testSetDataDirStripsTrailingSlash(): void
    {
        CurrencyService::setDataDir('/tmp/test-currencies/');
        $this->assertSame('/tmp/test-currencies', CurrencyService::getDataDir());
    }

    public function testGetUnitsFilePathReflectsDataDir(): void
    {
        CurrencyService::setDataDir('/tmp/test-currencies');
        $this->assertSame('/tmp/test-currencies/CurrencyUnits.php', CurrencyService::getUnitsFilePath());
    }

    public function testGetConversionsFilePathReflectsDataDir(): void
    {
        CurrencyService::setDataDir('/tmp/test-currencies');
        $this->assertSame('/tmp/test-currencies/CurrencyConversions.php', CurrencyService::getConversionsFilePath());
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
        $this->assertDirectoryDoesNotExist($tempDir);

        CurrencyService::setDataDir($tempDir);

        $this->assertDirectoryExists($tempDir);
        $this->assertSame($tempDir, CurrencyService::getDataDir());

        // Clean up.
        rmdir($tempDir);
    }

    // endregion

    // region getLocale / setLocale

    public function testGetLocaleReturnsExplicitlySetLocale(): void
    {
        CurrencyService::setLocale('en_AU');
        $this->assertSame('en_AU', CurrencyService::getLocale());
    }

    public function testGetLocaleReturnsSomethingWhenNotSet(): void
    {
        CurrencyService::setLocale(null);
        $locale = CurrencyService::getLocale();

        // Should fall back to PHP's default locale.
        $this->assertNotNull($locale);
        $this->assertNotEmpty($locale);
    }

    public function testSetLocaleAcceptsNull(): void
    {
        CurrencyService::setLocale('en_US');
        CurrencyService::setLocale(null);

        // After clearing, getLocale() falls back to auto-detection rather than returning the old value.
        // We can't assert null because getLocale() auto-detects, but we verify no exception is thrown.
        $this->assertNotSame('en_US', CurrencyService::getLocale());
    }

    public function testSetLocaleThrowsForInvalidLocale(): void
    {
        $this->expectException(FormatException::class);
        CurrencyService::setLocale('!!invalid');
    }

    public function testSetLocaleAcceptsVariousValidFormats(): void
    {
        CurrencyService::setLocale('en');
        $this->assertSame('en', CurrencyService::getLocale());

        CurrencyService::setLocale(null);
        CurrencyService::setLocale('en_US');
        $this->assertSame('en_US', CurrencyService::getLocale());

        CurrencyService::setLocale(null);
        CurrencyService::setLocale('zh-Hant-TW');
        $this->assertSame('zh-Hant-TW', CurrencyService::getLocale());
    }

    // endregion

    // region TTL getters / setters

    public function testGetRatesTtlReturnsDefault(): void
    {
        $this->assertSame(3600, CurrencyService::getRatesTtl());
    }

    public function testSetRatesTtlChangesValue(): void
    {
        CurrencyService::setRatesTtl(1800);
        $this->assertSame(1800, CurrencyService::getRatesTtl());
    }

    public function testSetRatesTtlAcceptsZero(): void
    {
        CurrencyService::setRatesTtl(0);
        $this->assertSame(0, CurrencyService::getRatesTtl());
    }

    public function testSetRatesTtlThrowsForNegativeValue(): void
    {
        $this->expectException(DomainException::class);
        CurrencyService::setRatesTtl(-1);
    }

    public function testGetCurrenciesTtlReturnsDefault(): void
    {
        $this->assertSame(2592000, CurrencyService::getCurrenciesTtl());
    }

    public function testSetCurrenciesTtlChangesValue(): void
    {
        CurrencyService::setCurrenciesTtl(86400);
        $this->assertSame(86400, CurrencyService::getCurrenciesTtl());
    }

    public function testSetCurrenciesTtlAcceptsZero(): void
    {
        CurrencyService::setCurrenciesTtl(0);
        $this->assertSame(0, CurrencyService::getCurrenciesTtl());
    }

    public function testSetCurrenciesTtlThrowsForNegativeValue(): void
    {
        $this->expectException(DomainException::class);
        CurrencyService::setCurrenciesTtl(-1);
    }

    // endregion

    // region loadUnits (private — accessed via reflection)

    public function testLoadUnitsReturnsNullWhenNoFile(): void
    {
        $originalDir = CurrencyService::getDataDir();
        CurrencyService::setDataDir(self::TEST_DATA_DIR . '/empty');

        try {
            $this->assertNull(self::invoke('loadUnits'));
        } finally {
            CurrencyService::deleteUnits();
            CurrencyService::deleteConversions();
            CurrencyService::setDataDir($originalDir);
        }
    }

    public function testLoadUnitsReturnsValidArray(): void
    {
        // Ensure data exists by refreshing.
        CurrencyService::getUnits(true);
        $data = self::invoke('loadUnits');

        $this->assertIsArray($data);
        $this->assertArrayHasKey('whenFetched', $data);
        $this->assertArrayHasKey('currencies', $data);
        $this->assertIsArray($data['currencies']);
        $this->assertNotEmpty($data['currencies']);
    }

    public function testLoadUnitsReturnsNullOnParseError(): void
    {
        $dir = self::TEST_DATA_DIR . '/corrupt-units';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($dir . '/CurrencyUnits.php', '<?php this is not valid php;');
        CurrencyService::setDataDir($dir);

        $this->assertNull(self::invoke('loadUnits'));

        @unlink($dir . '/CurrencyUnits.php');
        @rmdir($dir);
    }

    public function testLoadUnitsReturnsNullWhenFileReturnsNonArray(): void
    {
        $dir = self::TEST_DATA_DIR . '/non-array-units';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($dir . '/CurrencyUnits.php', '<?php return "not an array";');
        CurrencyService::setDataDir($dir);

        $this->assertNull(self::invoke('loadUnits'));

        @unlink($dir . '/CurrencyUnits.php');
        @rmdir($dir);
    }

    public function testLoadUnitsContainsCommonCurrencies(): void
    {
        CurrencyService::getUnits(true);
        $data = self::invoke('loadUnits');
        $this->assertIsArray($data);

        $this->assertIsArray($data['currencies']);
        $symbols = array_values($data['currencies']);

        $this->assertContains('USD', $symbols);
        $this->assertContains('EUR', $symbols);
        $this->assertContains('GBP', $symbols);
        $this->assertContains('AUD', $symbols);
        $this->assertContains('JPY', $symbols);
    }

    // endregion

    // region loadConversions (private — accessed via reflection)

    public function testLoadConversionsReturnsNullWhenNoFile(): void
    {
        $originalDir = CurrencyService::getDataDir();
        CurrencyService::setDataDir(self::TEST_DATA_DIR . '/empty');

        try {
            $this->assertNull(self::invoke('loadConversions'));
        } finally {
            CurrencyService::deleteUnits();
            CurrencyService::deleteConversions();
            CurrencyService::setDataDir($originalDir);
        }
    }

    public function testLoadConversionsReturnsValidArray(): void
    {
        CurrencyService::setExchangeRateService(new FrankfurterService());
        CurrencyService::getConversions(true);
        $data = self::invoke('loadConversions');

        $this->assertIsArray($data);
        $this->assertArrayHasKey('whenFetched', $data);
        $this->assertArrayHasKey('serviceName', $data);
        $this->assertArrayHasKey('definitions', $data);
        $this->assertIsArray($data['definitions']);
        $this->assertNotEmpty($data['definitions']);
    }

    public function testLoadConversionsReturnsNullOnParseError(): void
    {
        $dir = self::TEST_DATA_DIR . '/corrupt-conversions';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($dir . '/CurrencyConversions.php', '<?php this is not valid php;');
        CurrencyService::setDataDir($dir);

        $this->assertNull(self::invoke('loadConversions'));

        @unlink($dir . '/CurrencyConversions.php');
        @rmdir($dir);
    }

    public function testLoadConversionsReturnsNullWhenFileReturnsNonArray(): void
    {
        $dir = self::TEST_DATA_DIR . '/non-array-conversions';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($dir . '/CurrencyConversions.php', '<?php return 42;');
        CurrencyService::setDataDir($dir);

        $this->assertNull(self::invoke('loadConversions'));

        @unlink($dir . '/CurrencyConversions.php');
        @rmdir($dir);
    }

    // endregion

    // region getUnits

    public function testGetUnitsWithBypassCacheReturnsDataAndWritesFiles(): void
    {
        $result = CurrencyService::getUnits(true);

        $this->assertArrayHasKey('whenFetched', $result);
        $this->assertArrayHasKey('currencies', $result);
        $this->assertNotEmpty($result['currencies']);
        $this->assertFileExists(self::TEST_DATA_DIR . '/CurrencyUnits.php');
        $this->assertFileExists(self::TEST_DATA_DIR . '/CurrencyUnits.xml');
    }

    public function testGetUnitsReturnsCachedDataWhenFresh(): void
    {
        // First call populates the cache.
        $first = CurrencyService::getUnits(true);

        // Second call without bypass should return the same cached data (identical timestamp).
        $second = CurrencyService::getUnits(false);

        $this->assertSame($first['whenFetched'], $second['whenFetched']);
    }

    public function testGetUnitsWritesValidData(): void
    {
        CurrencyService::getUnits(true);

        $data = self::invoke('loadUnits');
        $this->assertIsArray($data);
        $this->assertArrayHasKey('whenFetched', $data);
        $this->assertArrayHasKey('currencies', $data);
        $this->assertNotEmpty($data['currencies']);
    }

    public function testGetUnitsRenamesSDR(): void
    {
        $data = CurrencyService::getUnits(true);

        // SDR is stored with the cleaned-up name, not the ISO-parenthesised form.
        $this->assertArrayHasKey('Special Drawing Right', $data['currencies']);
        $this->assertSame('XDR', $data['currencies']['Special Drawing Right']);
        $this->assertArrayNotHasKey('SDR (Special Drawing Right)', $data['currencies']);
    }

    // endregion

    // region getConversions

    public function testGetConversionsWithBypassCacheReturnsDataAndWritesFile(): void
    {
        CurrencyService::setExchangeRateService(new FrankfurterService());
        $result = CurrencyService::getConversions(true);

        $this->assertArrayHasKey('whenFetched', $result);
        $this->assertArrayHasKey('serviceName', $result);
        $this->assertArrayHasKey('definitions', $result);
        $this->assertNotEmpty($result['definitions']);
        $this->assertFileExists(self::TEST_DATA_DIR . '/CurrencyConversions.php');
    }

    public function testGetConversionsReturnsCachedDataWhenFresh(): void
    {
        CurrencyService::setExchangeRateService(new FrankfurterService());

        // First call populates the cache.
        $first = CurrencyService::getConversions(true);

        // Second call without bypass should return the same cached data.
        $second = CurrencyService::getConversions(false);

        $this->assertSame($first['whenFetched'], $second['whenFetched']);
        $this->assertSame($first['serviceName'], $second['serviceName']);
    }

    public function testGetConversionsWritesValidData(): void
    {
        CurrencyService::setExchangeRateService(new FrankfurterService());
        CurrencyService::getConversions(true);

        $data = self::invoke('loadConversions');
        $this->assertIsArray($data);
        $this->assertArrayHasKey('whenFetched', $data);
        $this->assertArrayHasKey('serviceName', $data);
        $this->assertArrayHasKey('definitions', $data);
        $this->assertNotEmpty($data['definitions']);
    }

    public function testGetConversionsRefetchesOnServiceChange(): void
    {
        // Ensure currency units are loaded so the conversion definitions' unit codes resolve.
        CurrencyService::getUnits(true);

        CurrencyService::setExchangeRateService(new FrankfurterService());
        $first = CurrencyService::getConversions(true);

        // Swap to a different service. Cache should be invalidated by the service-name change.
        CurrencyService::setExchangeRateService(self::makeFakeService());

        $second = CurrencyService::getConversions(false);

        $this->assertSame('FakeService', $second['serviceName']);
        $this->assertNotSame($first['serviceName'], $second['serviceName']);
    }

    public function testGetConversionsThrowsWithoutService(): void
    {
        CurrencyService::setExchangeRateService(null);
        $this->expectException(LogicException::class);
        CurrencyService::getConversions(true);
    }

    // endregion

    // region deleteUnits

    public function testDeleteUnitsRemovesFiles(): void
    {
        // Ensure the files exist first.
        CurrencyService::getUnits(true);
        $this->assertFileExists(self::TEST_DATA_DIR . '/CurrencyUnits.php');

        CurrencyService::deleteUnits();

        $this->assertFileDoesNotExist(self::TEST_DATA_DIR . '/CurrencyUnits.php');
        $this->assertFileDoesNotExist(self::TEST_DATA_DIR . '/CurrencyUnits.xml');
    }

    public function testDeleteUnitsIsIdempotentWhenFilesAbsent(): void
    {
        // Files may not exist; deleting again should not throw.
        CurrencyService::deleteUnits();
        CurrencyService::deleteUnits();
        $this->assertFileDoesNotExist(self::TEST_DATA_DIR . '/CurrencyUnits.php');
    }

    // endregion

    // region deleteConversions

    public function testDeleteConversionsRemovesFile(): void
    {
        // Ensure the file exists first.
        CurrencyService::getConversions(true);
        $this->assertFileExists(self::TEST_DATA_DIR . '/CurrencyConversions.php');

        CurrencyService::deleteConversions();

        $this->assertFileDoesNotExist(self::TEST_DATA_DIR . '/CurrencyConversions.php');
    }

    public function testDeleteConversionsIsIdempotentWhenFileAbsent(): void
    {
        // File may not exist; deleting again should not throw.
        CurrencyService::deleteConversions();
        CurrencyService::deleteConversions();
        $this->assertFileDoesNotExist(self::TEST_DATA_DIR . '/CurrencyConversions.php');
    }

    // endregion

    // region refresh

    public function testRefreshFromScratchCreatesDataFiles(): void
    {
        // Delete any existing data files so refresh() must fetch fresh data.
        @unlink(self::TEST_DATA_DIR . '/CurrencyUnits.xml');
        @unlink(self::TEST_DATA_DIR . '/CurrencyUnits.php');
        @unlink(self::TEST_DATA_DIR . '/CurrencyConversions.php');

        CurrencyService::setExchangeRateService(new FrankfurterService());
        CurrencyService::refresh();

        $this->assertFileExists(self::TEST_DATA_DIR . '/CurrencyUnits.php');
        $this->assertFileExists(self::TEST_DATA_DIR . '/CurrencyConversions.php');
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

        $this->assertGreaterThan($unitsMtime, $newUnitsMtime);
        $this->assertGreaterThan($conversionsMtime, $newConversionsMtime);
    }

    // endregion

    // region init

    public function testInitConfiguresService(): void
    {
        CurrencyService::init(new FrankfurterService());

        $this->assertInstanceOf(FrankfurterService::class, CurrencyService::getExchangeRateService());
    }

    public function testInitWithCustomLocale(): void
    {
        CurrencyService::init(new FrankfurterService(), 'de_DE');

        $this->assertSame('de_DE', CurrencyService::getLocale());
    }

    public function testInitWritesDataToConfiguredDir(): void
    {
        CurrencyService::init(new FrankfurterService());

        // Data files should be written to the test data directory, not production.
        $this->assertFileExists(self::TEST_DATA_DIR . '/CurrencyUnits.php');
        $this->assertFileExists(self::TEST_DATA_DIR . '/CurrencyConversions.php');
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
            $this->assertSame($unitsMtime, filemtime($unitsPath));
        } else {
            $this->assertFileDoesNotExist($unitsPath);
        }
        if ($conversionsMtime !== null) {
            clearstatcache(true, $conversionsPath);
            $this->assertSame($conversionsMtime, filemtime($conversionsPath));
        } else {
            $this->assertFileDoesNotExist($conversionsPath);
        }
    }

    // endregion

    // region Helpers

    /**
     * Invoke a private static method on CurrencyService by reflection.
     */
    private static function invoke(string $method, mixed ...$args): mixed
    {
        return new ReflectionMethod(CurrencyService::class, $method)->invoke(null, ...$args);
    }

    /**
     * Create a stand-in exchange rate service with a distinct name and a minimal definition set, so tests can
     * exercise the service-change cache invalidation path.
     */
    private static function makeFakeService(): ExchangeRateServiceInterface
    {
        return new class () implements ExchangeRateServiceInterface {
            public function getName(): string
            {
                return 'FakeService';
            }

            /**
             * @return list<list<string|float>>
             */
            public function getConversionDefinitions(): array
            {
                return [
                    ['USD', 'EUR', 0.9],
                ];
            }
        };
    }

    // endregion
}
