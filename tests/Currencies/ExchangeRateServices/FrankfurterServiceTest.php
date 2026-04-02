<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Currencies\ExchangeRateServices;

use Galaxon\Quantities\Currencies\ExchangeRateServices\FrankfurterService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for FrankfurterService.
 */
#[CoversClass(FrankfurterService::class)]
class FrankfurterServiceTest extends TestCase
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
            $service = new FrankfurterService();
            self::$definitions = $service->getConversionDefinitions();
        }

        return self::$definitions;
    }

    // region getName

    public function testGetName(): void
    {
        $service = new FrankfurterService();
        self::assertSame('Frankfurter (ECB)', $service->getName());
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
            // Base currency, target currency, rate.
            self::assertIsString($definition[0]);
            self::assertIsString($definition[1]);
            self::assertIsNumeric($definition[2]);
            // Currency codes are 3 characters.
            self::assertSame(3, strlen($definition[0]));
            self::assertSame(3, strlen($definition[1]));
            // Rate must be positive.
            self::assertGreaterThan(0, $definition[2]);
        }
    }

    public function testGetConversionDefinitionsBaseCurrencyIsEur(): void
    {
        $definitions = $this->getDefinitions();

        // All definitions should have EUR as the base currency.
        foreach ($definitions as $definition) {
            self::assertSame('EUR', $definition[0]);
        }
    }

    public function testGetConversionDefinitionsContainsCommonCurrencies(): void
    {
        $definitions = $this->getDefinitions();

        $targetCurrencies = array_map(static fn (array $def) => $def[1], $definitions);

        self::assertContains('USD', $targetCurrencies);
        self::assertContains('GBP', $targetCurrencies);
        self::assertContains('JPY', $targetCurrencies);
        self::assertContains('AUD', $targetCurrencies);
    }

    // endregion
}
