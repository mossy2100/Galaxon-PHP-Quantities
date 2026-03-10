<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Services;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
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
        // @phpstan-ignore argument.type
        $qty = QuantityPartsService::fromParts(Time::getQuantityType(), [
            'h'   => 1,
            'min' => 30,
            's'   => 45,
        ]);

        $this->assertInstanceOf(Time::class, $qty);
        $this->assertSame('s', $qty->derivedUnit->asciiSymbol);
        $this->assertEqualsWithDelta(5445.0, $qty->value, 1e-10);
    }

    /**
     * Test fromParts() with negative sign.
     */
    public function testFromPartsWithNegativeSign(): void
    {
        // @phpstan-ignore argument.type
        $qty = QuantityPartsService::fromParts(Time::getQuantityType(), [
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
        // @phpstan-ignore argument.type
        $qty = QuantityPartsService::fromParts(Angle::getQuantityType(), [
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
        // @phpstan-ignore argument.type
        $qty = QuantityPartsService::fromParts(Length::getQuantityType(), [
            'ft' => 5,
            'in' => 11,
        ]);

        $this->assertInstanceOf(Length::class, $qty);
        $this->assertSame('ft', $qty->derivedUnit->asciiSymbol);
        // 5 ft + 11 in = 5 + 11/12 ft = 5.916666... ft.
        $this->assertEqualsWithDelta(5.916666666, $qty->value, 1e-6);
    }

    /**
     * Test fromParts() throws for invalid sign value.
     */
    public function testFromPartsThrowsForInvalidSign(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid sign: 2. Must be -1 or 1.');

        // @phpstan-ignore argument.type
        QuantityPartsService::fromParts(Time::getQuantityType(), [
            'h'    => 1,
            'sign' => 2,
        ]);
    }

    // endregion

    // region toParts() tests

    /**
     * Test toParts() decomposes a time quantity into parts.
     */
    public function testToPartsDecomposesTime(): void
    {
        $qty = new Time(5445, 's');
        $parts = QuantityPartsService::toParts($qty);

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
        $parts = QuantityPartsService::toParts($qty);

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
        $parts = QuantityPartsService::toParts($qty, 1);

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
        $parts = QuantityPartsService::toParts($qty, 0);

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
        $parts = QuantityPartsService::toParts($qty);

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
        $parts = QuantityPartsService::toParts($qty, 0);

        $this->assertSame(1, $parts['h']);
        $this->assertSame(0, $parts['min']);
        $this->assertSame(0.0, $parts['s']);
    }

    /**
     * Test toParts() throws for negative precision.
     */
    public function testToPartsThrowsForNegativePrecision(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid precision');

        $qty = new Time(3661, 's');
        QuantityPartsService::toParts($qty, -1);
    }

    // endregion

    // region parseParts() tests

    /**
     * Test parseParts() parses a time parts string.
     */
    public function testParsePartsTime(): void
    {
        // @phpstan-ignore argument.type
        $qty = QuantityPartsService::parseParts(Time::getQuantityType(), '1h 30min 45s');

        $this->assertInstanceOf(Time::class, $qty);
        $this->assertEqualsWithDelta(5445.0, $qty->value, 1e-10);
    }

    /**
     * Test parseParts() parses a negative time parts string.
     */
    public function testParsePartsNegativeTime(): void
    {
        // @phpstan-ignore argument.type
        $qty = QuantityPartsService::parseParts(Time::getQuantityType(), '-2h 15min');

        $this->assertTrue($qty->value < 0);
        $this->assertEqualsWithDelta(-8100.0, $qty->value, 1e-10);
    }

    /**
     * Test parseParts() with angle parts.
     */
    public function testParsePartsAngle(): void
    {
        // @phpstan-ignore argument.type
        $qty = QuantityPartsService::parseParts(Angle::getQuantityType(), '45deg 30arcmin');

        $this->assertInstanceOf(Angle::class, $qty);
        $this->assertEqualsWithDelta(45.5, $qty->value, 1e-6);
    }

    /**
     * Test parseParts() trims whitespace.
     */
    public function testParsePartsTrimsWhitespace(): void
    {
        // @phpstan-ignore argument.type
        $qty = QuantityPartsService::parseParts(Time::getQuantityType(), '  90min  ');

        $this->assertEqualsWithDelta(5400.0, $qty->value, 1e-10);
    }

    /**
     * Test parseParts() throws for empty input.
     */
    public function testParsePartsThrowsForEmptyInput(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('empty');

        // @phpstan-ignore argument.type
        QuantityPartsService::parseParts(Time::getQuantityType(), '');
    }

    /**
     * Test parseParts() throws for whitespace-only input.
     */
    public function testParsePartsThrowsForWhitespaceInput(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('empty');

        // @phpstan-ignore argument.type
        QuantityPartsService::parseParts(Time::getQuantityType(), '   ');
    }

    /**
     * Test parseParts() throws for invalid format.
     */
    public function testParsePartsThrowsForInvalidFormat(): void
    {
        $this->expectException(FormatException::class);

        // @phpstan-ignore argument.type
        QuantityPartsService::parseParts(Time::getQuantityType(), 'not a quantity');
    }

    /**
     * Test parseParts() throws for negative non-first part.
     */
    public function testParsePartsThrowsForNegativeNonFirstPart(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('only the first may be negative');

        // @phpstan-ignore argument.type
        QuantityPartsService::parseParts(Time::getQuantityType(), '1h -30min');
    }

    // endregion

    // region formatParts() tests

    /**
     * Test formatParts() formats a time quantity.
     */
    public function testFormatPartsTime(): void
    {
        $qty = new Time(5445, 's');
        $result = QuantityPartsService::formatParts($qty);

        $this->assertStringContainsString('1h', $result);
        $this->assertStringContainsString('30min', $result);
        $this->assertStringContainsString('45s', $result);
    }

    /**
     * Test formatParts() with negative value.
     */
    public function testFormatPartsNegative(): void
    {
        $qty = new Time(-3661, 's');
        $result = QuantityPartsService::formatParts($qty);

        $this->assertStringStartsWith('-', $result);
        $this->assertStringContainsString('1h', $result);
        $this->assertStringContainsString('1min', $result);
        $this->assertStringContainsString('1s', $result);
    }

    /**
     * Test formatParts() with showZeros flag.
     */
    public function testFormatPartsShowZeros(): void
    {
        $qty = new Time(3600, 's');
        $result = QuantityPartsService::formatParts($qty, showZeros: true);

        $this->assertStringContainsString('1h', $result);
        $this->assertStringContainsString('0min', $result);
        $this->assertStringContainsString('0s', $result);
    }

    /**
     * Test formatParts() without showZeros skips zero parts.
     */
    public function testFormatPartsSkipsZeros(): void
    {
        $qty = new Time(3600, 's');
        $result = QuantityPartsService::formatParts($qty, showZeros: false);

        // Non-zero parts should be present, zero parts should be skipped.
        $this->assertStringContainsString('1h', $result);
        $this->assertStringNotContainsString('0min', $result);
    }

    /**
     * Test formatParts() with zero value shows the smallest unit.
     */
    public function testFormatPartsZeroValue(): void
    {
        $qty = new Time(0, 's');
        $result = QuantityPartsService::formatParts($qty);

        $this->assertSame('0s', $result);
    }

    /**
     * Test formatParts() with precision.
     */
    public function testFormatPartsWithPrecision(): void
    {
        $qty = new Time(3661.567, 's');
        $result = QuantityPartsService::formatParts($qty, precision: 2);

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

    // endregion

    // region validatePrecision() tests

    /**
     * Test validatePrecision() accepts null.
     */
    public function testValidatePrecisionAcceptsNull(): void
    {
        // Should not throw.
        QuantityPartsService::validatePrecision(null);
        $this->assertTrue(true); // @phpstan-ignore method.alreadyNarrowedType
    }

    /**
     * Test validatePrecision() accepts zero.
     */
    public function testValidatePrecisionAcceptsZero(): void
    {
        QuantityPartsService::validatePrecision(0);
        $this->assertTrue(true); // @phpstan-ignore method.alreadyNarrowedType
    }

    /**
     * Test validatePrecision() accepts positive integer.
     */
    public function testValidatePrecisionAcceptsPositive(): void
    {
        QuantityPartsService::validatePrecision(5);
        $this->assertTrue(true); // @phpstan-ignore method.alreadyNarrowedType
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

        $symbols = ['h', 42];
        QuantityPartsService::validatePartUnitSymbols($symbols); // @phpstan-ignore argument.type
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
