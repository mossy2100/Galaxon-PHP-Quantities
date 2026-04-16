<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\NonCurrencies\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType\Resistance;
use Galaxon\Quantities\Tests\NonCurrencies\Traits\ArrayShapeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Resistance quantity type.
 */
#[CoversClass(Resistance::class)]
final class ResistanceTest extends TestCase
{
    use ArrayShapeTrait;

    // region Overridden methods

    /**
     * Test getUnitDefinitions() returns valid unit definitions.
     */
    public function testGetUnitDefinitionsReturnsValidArray(): void
    {
        $units = Resistance::getUnitDefinitions();

        $this->assertValidUnitDefinitionsShape($units);
    }

    /**
     * Test getConversionDefinitions() returns valid conversion definitions.
     */
    public function testGetConversionDefinitionsReturnsValidArray(): void
    {
        $conversions = Resistance::getConversionDefinitions();

        $this->assertValidConversionDefinitionsShape($conversions);
    }

    // endregion

    // region Symbol parsing tests

    /**
     * Data provider for ohm symbol variants.
     *
     * Each entry: [symbol to parse, expected value in ohms].
     * Uses Unicode escapes so the two visually identical Ω characters are unambiguous:
     * - U+03A9 Greek capital letter omega (the declared unicodeSymbol)
     * - U+2126 ohm sign (the declared alternateSymbol)
     *
     * @return array<string, array{string, float}>
     */
    public static function ohmSymbolProvider(): array
    {
        return [
            'ASCII'              => ['ohm', 1.0],
            'Unicode (U+03A9)'   => ["\u{03A9}", 1.0],
            'alternate (U+2126)' => ["\u{2126}", 1.0],
            'prefixed ASCII'     => ['kohm', 1000.0],
            'prefixed Unicode'   => ["k\u{03A9}", 1000.0],
            'prefixed alternate' => ["k\u{2126}", 1000.0],
        ];
    }

    /**
     * Test that each declared ohm symbol (ASCII, Unicode, alternate), with and without a prefix, parses into a
     * Resistance with the expected value.
     */
    #[DataProvider('ohmSymbolProvider')]
    public function testParsesOhmSymbol(string $symbol, float $expectedOhms): void
    {
        $resistance = Quantity::create(1, $symbol);

        $this->assertInstanceOf(Resistance::class, $resistance);
        $this->assertSame($expectedOhms, $resistance->to('ohm')->value);
    }

    // endregion
}
