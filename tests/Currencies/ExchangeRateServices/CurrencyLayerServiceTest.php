<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Currencies\ExchangeRateServices;

use DomainException;
use Galaxon\Quantities\Currencies\ExchangeRateServices\CurrencyLayerService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for CurrencyLayerService.
 */
#[CoversClass(CurrencyLayerService::class)]
class CurrencyLayerServiceTest extends TestCase
{
    /**
     * Cached conversion definitions from a single API call.
     *
     * @var ?list<array{string, string, float}>
     */
    private static ?array $definitions = null;

    /**
     * Lazily fetch conversion definitions, caching the result.
     *
     * @return list<array{string, string, float}>
     */
    private function getDefinitions(): array
    {
        if (self::$definitions === null) {
            $apiKey = $this->loadApiKey();
            $service = new CurrencyLayerService($apiKey);
            self::$definitions = $service->getConversionDefinitions();
        }

        return self::$definitions;
    }

    /**
     * Load the API key, skipping the test if unavailable.
     *
     * @return string The API key.
     */
    private function loadApiKey(): string
    {
        $path = __DIR__ . '/../api-keys.json';
        if (!file_exists($path)) {
            self::markTestSkipped('CurrencyLayer access key not configured in tests/api-keys.json.');
        }

        $keysJson = file_get_contents($path);
        if ($keysJson === false) {
            self::markTestSkipped('Failed to read CurrencyLayer access key from tests/api-keys.json.');
        }

        $keys = json_decode($keysJson, true);
        if (!is_array($keys)) {
            self::markTestSkipped('Invalid JSON in tests/api-keys.json.');
        }

        $apiKey = $keys['currencyLayer'] ?? '';
        if ($apiKey === '' || !is_string($apiKey)) {
            self::markTestSkipped('CurrencyLayer access key not configured in tests/api-keys.json.');
        }

        return $apiKey;
    }

    // region Constructor

    public function testConstructorRejectsEmptyAccessKey(): void
    {
        $this->expectException(DomainException::class);
        new CurrencyLayerService('');
    }

    public function testConstructorStoresAccessKey(): void
    {
        $service = new CurrencyLayerService('test-key');
        self::assertSame('test-key', $service->accessKey);
    }

    // endregion

    // region getName

    public function testGetName(): void
    {
        $service = new CurrencyLayerService('test-key');
        self::assertSame('CurrencyLayer', $service->getName());
    }

    // endregion

    // region getConversionDefinitions

    public function testGetConversionDefinitionsReturnsArray(): void
    {
        $definitions = $this->getDefinitions();
        self::assertNotEmpty($definitions);
    }

    public function testGetConversionDefinitionsStructure(): void
    {
        $definitions = $this->getDefinitions();

        foreach ($definitions as $definition) {
            self::assertCount(3, $definition);
            self::assertIsString($definition[0]);
            self::assertIsString($definition[1]);
            self::assertIsNumeric($definition[2]);
            self::assertSame(3, strlen($definition[0]));
            self::assertSame(3, strlen($definition[1]));
            self::assertGreaterThan(0, $definition[2]);
        }
    }

    public function testGetConversionDefinitionsBaseCurrencyIsUsd(): void
    {
        $definitions = $this->getDefinitions();

        foreach ($definitions as $definition) {
            self::assertSame('USD', $definition[0]);
        }
    }

    public function testGetConversionDefinitionsContainsCommonCurrencies(): void
    {
        $definitions = $this->getDefinitions();

        $targetCurrencies = array_map(static fn (array $def) => $def[1], $definitions);

        self::assertContains('EUR', $targetCurrencies);
        self::assertContains('GBP', $targetCurrencies);
        self::assertContains('JPY', $targetCurrencies);
        self::assertContains('AUD', $targetCurrencies);
    }

    // endregion
}
