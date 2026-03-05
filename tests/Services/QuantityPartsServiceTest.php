<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Services;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType\Angle;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Time;
use Galaxon\Quantities\Services\QuantityPartsService;
use Galaxon\Quantities\Services\UnitService;
use Galaxon\Quantities\UnitSystem;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for QuantityPartsService class.
 */
#[CoversClass(QuantityPartsService::class)]
final class QuantityPartsServiceTest extends TestCase
{
    // region Setup

    public static function setUpBeforeClass(): void
    {
        UnitService::loadSystem(UnitSystem::Imperial);
        UnitService::loadSystem(UnitSystem::UsCustomary);
    }

    // endregion

    // region fromParts() tests

    /**
     * Test fromParts() creates a quantity from time parts.
     */
    public function testFromPartsCreatesTimeQuantity(): void
    {
        $qty = QuantityPartsService::fromParts(Time::class, [
            'h'   => 1,
            'min' => 30,
            's'   => 45,
        ]);

        $this->assertInstanceOf(Time::class, $qty);
        $this->assertSame('s', $qty->derivedUnit->asciiSymbol);
        $this->assertEqualsWithDelta(5445.0, $qty->value, 1e-10);
    }

    /**
     * Test fromParts() with explicit result unit symbol.
     */
    public function testFromPartsWithExplicitResultUnit(): void
    {
        $qty = QuantityPartsService::fromParts(Time::class, [
            'h'   => 1,
            'min' => 30,
        ], 'min');

        $this->assertSame('min', $qty->derivedUnit->asciiSymbol);
        $this->assertEqualsWithDelta(90.0, $qty->value, 1e-10);
    }

    /**
     * Test fromParts() with negative sign.
     */
    public function testFromPartsWithNegativeSign(): void
    {
        $qty = QuantityPartsService::fromParts(Time::class, [
            'h'    => 1,
            'min'  => 30,
            'sign' => -1,
        ]);

        $this->assertTrue($qty->value < 0);
        $this->assertEqualsWithDelta(-5400.0, $qty->value, 1e-10);
    }

    /**
     * Test fromParts() with angle parts.
     */
    public function testFromPartsCreatesAngleQuantity(): void
    {
        $qty = QuantityPartsService::fromParts(Angle::class, [
            'deg'    => 45,
            'arcmin' => 30,
            'arcsec' => 0,
        ]);

        $this->assertInstanceOf(Angle::class, $qty);
        $this->assertEqualsWithDelta(45.5, $qty->value, 1e-10);
    }

    /**
     * Test fromParts() with length parts.
     */
    public function testFromPartsCreatesLengthQuantity(): void
    {
        $qty = QuantityPartsService::fromParts(Length::class, [
            'ft' => 5,
            'in' => 11,
        ]);

        $this->assertInstanceOf(Length::class, $qty);
        $this->assertSame('ft', $qty->derivedUnit->asciiSymbol);
        // 5 ft + 11 in = 5 + 11/12 ft = 5.916666... ft.
        $this->assertEqualsWithDelta(5.916666666, $qty->value, 1e-6);
    }

    /**
     * Test fromParts() throws when no result unit and no default.
     */
    public function testFromPartsThrowsWithNoResultUnit(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('No result unit symbol provided and no default set.');

        QuantityPartsService::fromParts(Quantity::class, ['m' => 100]);
    }

    /**
     * Test fromParts() throws for unknown result unit.
     */
    public function testFromPartsThrowsForUnknownResultUnit(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Unknown result unit 'xyz'");

        QuantityPartsService::fromParts(Time::class, ['h' => 1], 'xyz');
    }

    /**
     * Test fromParts() throws for incompatible result unit dimension.
     */
    public function testFromPartsThrowsForIncompatibleResultUnit(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('incompatible with time quantities');

        QuantityPartsService::fromParts(Time::class, ['h' => 1], 'm');
    }

    /**
     * Test fromParts() throws for invalid sign value.
     */
    public function testFromPartsThrowsForInvalidSign(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid sign: 2. Must be -1 or 1.');

        QuantityPartsService::fromParts(Time::class, ['h' => 1, 'sign' => 2]);
    }

    // endregion

    // region toParts() tests

    /**
     * Test toParts() decomposes a time quantity into parts.
     */
    public function testToPartsDecomposesTime(): void
    {
        $qty = new Time(5445, 's');
        $parts = QuantityPartsService::toParts($qty, ['h', 'min', 's']);

        $this->assertSame(1, $parts['sign']);
        $this->assertSame(1, $parts['h']);
        $this->assertSame(30, $parts['min']);
        $this->assertSame(45.0, $parts['s']);
    }

    /**
     * Test toParts() with negative value.
     */
    public function testToPartsWithNegativeValue(): void
    {
        $qty = new Time(-3661, 's');
        $parts = QuantityPartsService::toParts($qty, ['h', 'min', 's']);

        $this->assertSame(-1, $parts['sign']);
        $this->assertSame(1, $parts['h']);
        $this->assertSame(1, $parts['min']);
        $this->assertSame(1.0, $parts['s']);
    }

    /**
     * Test toParts() with precision rounding.
     */
    public function testToPartsWithPrecisionRoundsUp(): void
    {
        // 3661.567s = 1h 1min 1.567s. Rounding to 1dp gives 1.6s (rounds up, triggers rebuild).
        $qty = new Time(3661.567, 's');
        $parts = QuantityPartsService::toParts($qty, ['h', 'min', 's'], 1);

        $this->assertSame(1, $parts['h']);
        $this->assertSame(1, $parts['min']);
        $this->assertSame(1.6, $parts['s']);
    }

    /**
     * Test toParts() with precision that rounds down the smallest unit.
     */
    public function testToPartsWithPrecisionRoundsDown(): void
    {
        // 3661.3s = 1h 1min 1.3s. Rounding to 0dp gives 1.0s (rounds down, no rebuild needed).
        $qty = new Time(3661.3, 's');
        $parts = QuantityPartsService::toParts($qty, ['h', 'min', 's'], 0);

        $this->assertSame(1, $parts['h']);
        $this->assertSame(1, $parts['min']);
        $this->assertSame(1.0, $parts['s']);
    }

    /**
     * Test toParts() uses class defaults when no symbols provided.
     */
    public function testToPartsUsesClassDefaults(): void
    {
        $qty = new Angle(45.508333333, 'deg');
        $parts = QuantityPartsService::toParts($qty, precision: 0);

        $this->assertSame(45, $parts['deg']);
        $this->assertSame(30, $parts['arcmin']);
        $this->assertSame(30.0, $parts['arcsec']);
    }

    /**
     * Test toParts() with zero value.
     */
    public function testToPartsWithZeroValue(): void
    {
        $qty = new Time(0, 's');
        $parts = QuantityPartsService::toParts($qty, ['h', 'min', 's']);

        $this->assertSame(1, $parts['sign']);
        $this->assertSame(0, $parts['h']);
        $this->assertSame(0, $parts['min']);
        $this->assertSame(0.0, $parts['s']);
    }

    /**
     * Test toParts() rounding carries over to larger units.
     */
    public function testToPartsRoundingCarriesOver(): void
    {
        // 59 min 59.9 s → rounds to 1h 0min 0s at precision 0.
        $qty = new Time(3599.9, 's');
        $parts = QuantityPartsService::toParts($qty, ['h', 'min', 's'], 0);

        $this->assertSame(1, $parts['h']);
        $this->assertSame(0, $parts['min']);
        $this->assertSame(0.0, $parts['s']);
    }

    /**
     * Test toParts() throws for empty part unit symbols.
     */
    public function testToPartsThrowsForEmptySymbols(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('must not be empty');

        $qty = new Time(3661, 's');
        QuantityPartsService::toParts($qty, []);
    }

    /**
     * Test toParts() throws for negative precision.
     */
    public function testToPartsThrowsForNegativePrecision(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid precision');

        $qty = new Time(3661, 's');
        QuantityPartsService::toParts($qty, ['h', 'min', 's'], -1);
    }

    // endregion

    // region parseParts() tests

    /**
     * Test parseParts() parses a time parts string.
     */
    public function testParsePartsTime(): void
    {
        $qty = QuantityPartsService::parseParts(Time::class, '1h 30min 45s');

        $this->assertInstanceOf(Time::class, $qty);
        $this->assertEqualsWithDelta(5445.0, $qty->value, 1e-10);
    }

    /**
     * Test parseParts() parses a negative time parts string.
     */
    public function testParsePartsNegativeTime(): void
    {
        $qty = QuantityPartsService::parseParts(Time::class, '-2h 15min');

        $this->assertTrue($qty->value < 0);
        $this->assertEqualsWithDelta(-8100.0, $qty->value, 1e-10);
    }

    /**
     * Test parseParts() with explicit result unit.
     */
    public function testParsePartsWithResultUnit(): void
    {
        $qty = QuantityPartsService::parseParts(Time::class, '1h 30min', 'min');

        $this->assertSame('min', $qty->derivedUnit->asciiSymbol);
        $this->assertEqualsWithDelta(90.0, $qty->value, 1e-10);
    }

    /**
     * Test parseParts() with angle parts.
     */
    public function testParsePartsAngle(): void
    {
        $qty = QuantityPartsService::parseParts(Angle::class, '45deg 30arcmin');

        $this->assertInstanceOf(Angle::class, $qty);
        $this->assertEqualsWithDelta(45.5, $qty->value, 1e-6);
    }

    /**
     * Test parseParts() trims whitespace.
     */
    public function testParsePartsTrimsWhitespace(): void
    {
        $qty = QuantityPartsService::parseParts(Time::class, '  90min  ');

        $this->assertEqualsWithDelta(5400.0, $qty->value, 1e-10);
    }

    /**
     * Test parseParts() throws for empty input.
     */
    public function testParsePartsThrowsForEmptyInput(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('empty');

        QuantityPartsService::parseParts(Time::class, '');
    }

    /**
     * Test parseParts() throws for whitespace-only input.
     */
    public function testParsePartsThrowsForWhitespaceInput(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('empty');

        QuantityPartsService::parseParts(Time::class, '   ');
    }

    /**
     * Test parseParts() throws for invalid format.
     */
    public function testParsePartsThrowsForInvalidFormat(): void
    {
        $this->expectException(FormatException::class);

        QuantityPartsService::parseParts(Time::class, 'not a quantity');
    }

    /**
     * Test parseParts() throws for negative non-first part.
     */
    public function testParsePartsThrowsForNegativeNonFirstPart(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('only the first may be negative');

        QuantityPartsService::parseParts(Time::class, '1h -30min');
    }

    /**
     * Test parseParts() throws when no result unit and no default.
     */
    public function testParsePartsThrowsWhenNoResultUnit(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('No result unit symbol provided and no default set.');

        QuantityPartsService::parseParts(Quantity::class, '100m');
    }

    // endregion

    // region formatParts() tests

    /**
     * Test formatParts() formats a time quantity.
     */
    public function testFormatPartsTime(): void
    {
        $qty = new Time(5445, 's');
        $result = QuantityPartsService::formatParts($qty, ['h', 'min', 's']);

        $this->assertSame('1h 30min 45s', $result);
    }

    /**
     * Test formatParts() with negative value.
     */
    public function testFormatPartsNegative(): void
    {
        $qty = new Time(-3661, 's');
        $result = QuantityPartsService::formatParts($qty, ['h', 'min', 's']);

        $this->assertSame('-1h 1min 1s', $result);
    }

    /**
     * Test formatParts() with showZeros flag.
     */
    public function testFormatPartsShowZeros(): void
    {
        $qty = new Time(3600, 's');
        $result = QuantityPartsService::formatParts($qty, ['h', 'min', 's'], showZeros: true);

        $this->assertSame('1h 0min 0s', $result);
    }

    /**
     * Test formatParts() without showZeros skips zero parts.
     */
    public function testFormatPartsSkipsZeros(): void
    {
        $qty = new Time(3600, 's');
        $result = QuantityPartsService::formatParts($qty, ['h', 'min', 's'], showZeros: false);

        // All zero parts are skipped, including the smallest unit when the result is non-empty.
        $this->assertSame('1h', $result);
    }

    /**
     * Test formatParts() with zero value shows the smallest unit.
     */
    public function testFormatPartsZeroValue(): void
    {
        $qty = new Time(0, 's');
        $result = QuantityPartsService::formatParts($qty, ['h', 'min', 's']);

        $this->assertSame('0s', $result);
    }

    /**
     * Test formatParts() with precision.
     */
    public function testFormatPartsWithPrecision(): void
    {
        $qty = new Time(3661.567, 's');
        $result = QuantityPartsService::formatParts($qty, ['h', 'min', 's'], precision: 2);

        $this->assertSame('1h 1min 1.57s', $result);
    }

    /**
     * Test formatParts() with ascii flag.
     */
    public function testFormatPartsAscii(): void
    {
        $qty = new Angle(45.508333333, 'deg');
        $resultUnicode = QuantityPartsService::formatParts($qty, precision: 0);
        $resultAscii = QuantityPartsService::formatParts($qty, precision: 0, ascii: true);

        // Unicode uses ° ′ ″ symbols.
        $this->assertStringContainsString('°', $resultUnicode);
        // ASCII uses deg, arcmin, arcsec.
        $this->assertStringContainsString('deg', $resultAscii);
    }

    /**
     * Test formatParts() uses class defaults when no symbols provided.
     */
    public function testFormatPartsUsesClassDefaults(): void
    {
        $qty = new Time(3661, 's');
        $result = QuantityPartsService::formatParts($qty);

        // Should use the Time class defaults (y, mo, w, d, h, min, s).
        $this->assertStringContainsString('1h', $result);
        $this->assertStringContainsString('1min', $result);
        $this->assertStringContainsString('1s', $result);
    }

    // endregion

    // region validatePrecision() tests

    /**
     * Test validatePrecision() accepts null.
     */
    public function testValidatePrecisionAcceptsNull(): void
    {
        // Should not throw.
        QuantityPartsService::validatePrecision(null);
        $this->assertTrue(true);
    }

    /**
     * Test validatePrecision() accepts zero.
     */
    public function testValidatePrecisionAcceptsZero(): void
    {
        QuantityPartsService::validatePrecision(0);
        $this->assertTrue(true);
    }

    /**
     * Test validatePrecision() accepts positive integer.
     */
    public function testValidatePrecisionAcceptsPositive(): void
    {
        QuantityPartsService::validatePrecision(5);
        $this->assertTrue(true);
    }

    /**
     * Test validatePrecision() throws for negative value.
     */
    public function testValidatePrecisionThrowsForNegative(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid precision');

        QuantityPartsService::validatePrecision(-1);
    }

    // endregion

    // region validatePartUnitSymbols() tests

    /**
     * Test validatePartUnitSymbols() returns Unit objects for valid symbols.
     */
    public function testValidatePartUnitSymbolsReturnsUnits(): void
    {
        $symbols = ['h', 'min', 's'];
        $units = QuantityPartsService::validatePartUnitSymbols($symbols);

        $this->assertCount(3, $units);
        $this->assertSame('h', $units[0]->asciiSymbol);
        $this->assertSame('min', $units[1]->asciiSymbol);
        $this->assertSame('s', $units[2]->asciiSymbol);
    }

    /**
     * Test validatePartUnitSymbols() deduplicates and re-indexes.
     */
    public function testValidatePartUnitSymbolsDeduplicates(): void
    {
        $symbols = ['h', 'min', 'h', 's'];
        $units = QuantityPartsService::validatePartUnitSymbols($symbols);

        $this->assertCount(3, $units);
        $this->assertSame(['h', 'min', 's'], $symbols);
    }

    /**
     * Test validatePartUnitSymbols() throws for null.
     */
    public function testValidatePartUnitSymbolsThrowsForNull(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('must not be empty');

        $symbols = null;
        QuantityPartsService::validatePartUnitSymbols($symbols);
    }

    /**
     * Test validatePartUnitSymbols() throws for empty array.
     */
    public function testValidatePartUnitSymbolsThrowsForEmptyArray(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('must not be empty');

        $symbols = [];
        QuantityPartsService::validatePartUnitSymbols($symbols);
    }

    /**
     * Test validatePartUnitSymbols() throws for non-string items.
     */
    public function testValidatePartUnitSymbolsThrowsForNonStringItems(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must contain only strings');

        $symbols = ['h', 42]; // @phpstan-ignore argument.type
        QuantityPartsService::validatePartUnitSymbols($symbols);
    }

    /**
     * Test validatePartUnitSymbols() throws for unknown unit.
     */
    public function testValidatePartUnitSymbolsThrowsForUnknownUnit(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Unknown unit symbol: 'xyz'");

        $symbols = ['h', 'xyz'];
        QuantityPartsService::validatePartUnitSymbols($symbols);
    }

    // endregion
}
