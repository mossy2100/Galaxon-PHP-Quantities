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

    // region loadUnits (private — accessed via reflection)

    public function testLoadUnitsReturnsNullWhenNoFile(): void
    {
        CurrencyService::setDataDir(self::TEST_DATA_DIR . '/empty');
        self::assertNull(self::invoke('loadUnits'));
    }

    public function testLoadUnitsReturnsValidArray(): void
    {
        // Ensure data exists by refreshing.
        CurrencyService::getUnits(true);
        $data = self::invoke('loadUnits');

        self::assertIsArray($data);
        self::assertArrayHasKey('whenFetched', $data);
        self::assertArrayHasKey('currencies', $data);
        self::assertIsArray($data['currencies']);
        self::assertNotEmpty($data['currencies']);
    }

    public function testLoadUnitsReturnsNullOnParseError(): void
    {
        $dir = self::TEST_DATA_DIR . '/corrupt-units';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($dir . '/CurrencyUnits.php', '<?php this is not valid php;');
        CurrencyService::setDataDir($dir);

        self::assertNull(self::invoke('loadUnits'));

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

        self::assertNull(self::invoke('loadUnits'));

        @unlink($dir . '/CurrencyUnits.php');
        @rmdir($dir);
    }

    public function testLoadUnitsContainsCommonCurrencies(): void
    {
        CurrencyService::getUnits(true);
        $data = self::invoke('loadUnits');
        self::assertIsArray($data);

        $symbols = array_values($data['currencies']);

        self::assertContains('USD', $symbols);
        self::assertContains('EUR', $symbols);
        self::assertContains('GBP', $symbols);
        self::assertContains('AUD', $symbols);
        self::assertContains('JPY', $symbols);
    }

    // endregion

    // region loadConversions (private — accessed via reflection)

    public function testLoadConversionsReturnsNullWhenNoFile(): void
    {
        CurrencyService::setDataDir(self::TEST_DATA_DIR . '/empty');
        self::assertNull(self::invoke('loadConversions'));
    }

    public function testLoadConversionsReturnsValidArray(): void
    {
        CurrencyService::setExchangeRateService(new FrankfurterService());
        CurrencyService::getConversions(true);
        $data = self::invoke('loadConversions');

        self::assertIsArray($data);
        self::assertArrayHasKey('whenFetched', $data);
        self::assertArrayHasKey('serviceName', $data);
        self::assertArrayHasKey('definitions', $data);
        self::assertIsArray($data['definitions']);
        self::assertNotEmpty($data['definitions']);
    }

    public function testLoadConversionsReturnsNullOnParseError(): void
    {
        $dir = self::TEST_DATA_DIR . '/corrupt-conversions';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($dir . '/CurrencyConversions.php', '<?php this is not valid php;');
        CurrencyService::setDataDir($dir);

        self::assertNull(self::invoke('loadConversions'));

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

        self::assertNull(self::invoke('loadConversions'));

        @unlink($dir . '/CurrencyConversions.php');
        @rmdir($dir);
    }

    // endregion

    // region getUnits

    public function testGetUnitsWithBypassCacheReturnsDataAndWritesFiles(): void
    {
        $result = CurrencyService::getUnits(true);

        self::assertIsArray($result);
        self::assertArrayHasKey('whenFetched', $result);
        self::assertArrayHasKey('currencies', $result);
        self::assertNotEmpty($result['currencies']);
        self::assertFileExists(self::TEST_DATA_DIR . '/CurrencyUnits.php');
        self::assertFileExists(self::TEST_DATA_DIR . '/CurrencyData.xml');
    }

    public function testGetUnitsReturnsCachedDataWhenFresh(): void
    {
        // First call populates the cache.
        $first = CurrencyService::getUnits(true);

        // Second call without bypass should return the same cached data (identical timestamp).
        $second = CurrencyService::getUnits(false);

        self::assertSame($first['whenFetched'], $second['whenFetched']);
    }

    public function testGetUnitsWritesValidData(): void
    {
        CurrencyService::getUnits(true);

        $data = self::invoke('loadUnits');
        self::assertIsArray($data);
        self::assertArrayHasKey('whenFetched', $data);
        self::assertArrayHasKey('currencies', $data);
        self::assertNotEmpty($data['currencies']);
    }

    public function testGetUnitsRenamesSDR(): void
    {
        $data = CurrencyService::getUnits(true);

        // SDR is stored with the cleaned-up name, not the ISO-parenthesised form.
        self::assertArrayHasKey('Special Drawing Right', $data['currencies']);
        self::assertSame('XDR', $data['currencies']['Special Drawing Right']);
        self::assertArrayNotHasKey('SDR (Special Drawing Right)', $data['currencies']);
    }

    // endregion

    // region getConversions

    public function testGetConversionsWithBypassCacheReturnsDataAndWritesFile(): void
    {
        CurrencyService::setExchangeRateService(new FrankfurterService());
        $result = CurrencyService::getConversions(true);

        self::assertIsArray($result);
        self::assertArrayHasKey('whenFetched', $result);
        self::assertArrayHasKey('serviceName', $result);
        self::assertArrayHasKey('definitions', $result);
        self::assertNotEmpty($result['definitions']);
        self::assertFileExists(self::TEST_DATA_DIR . '/CurrencyConversions.php');
    }

    public function testGetConversionsReturnsCachedDataWhenFresh(): void
    {
        CurrencyService::setExchangeRateService(new FrankfurterService());

        // First call populates the cache.
        $first = CurrencyService::getConversions(true);

        // Second call without bypass should return the same cached data.
        $second = CurrencyService::getConversions(false);

        self::assertSame($first['whenFetched'], $second['whenFetched']);
        self::assertSame($first['serviceName'], $second['serviceName']);
    }

    public function testGetConversionsWritesValidData(): void
    {
        CurrencyService::setExchangeRateService(new FrankfurterService());
        CurrencyService::getConversions(true);

        $data = self::invoke('loadConversions');
        self::assertIsArray($data);
        self::assertArrayHasKey('whenFetched', $data);
        self::assertArrayHasKey('serviceName', $data);
        self::assertArrayHasKey('definitions', $data);
        self::assertNotEmpty($data['definitions']);
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

        self::assertSame('FakeService', $second['serviceName']);
        self::assertNotSame($first['serviceName'], $second['serviceName']);
    }

    public function testGetConversionsThrowsWithoutService(): void
    {
        CurrencyService::setExchangeRateService(null);
        $this->expectException(LogicException::class);
        CurrencyService::getConversions(true);
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
