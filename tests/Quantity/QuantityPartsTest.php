<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Quantity;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Quantities\Exceptions\DimensionMismatchException;
use Galaxon\Quantities\Exceptions\UnknownUnitException;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType\Angle;
use Galaxon\Quantities\QuantityType\Time;
use Galaxon\Quantities\Services\QuantityPartsService;
use LengthException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for parts-related methods on Quantity objects.
 */
#[CoversClass(Quantity::class)]
#[CoversClass(QuantityPartsService::class)]
final class QuantityPartsTest extends TestCase
{
    // region toParts() tests

    /**
     * Test toParts() with Time quantity.
     */
    public function testToPartsTime(): void
    {
        // 1 hour, 30 minutes, 45 seconds = 5445 seconds
        $time = new Time(5445, 's');
        $parts = $time->toParts();

        $this->assertSame(1, $parts['sign']);
        $this->assertSame(1, $parts['h']);
        $this->assertSame(30, $parts['min']);
        $this->assertSame(45.0, $parts['s']);
    }

    /**
     * Test toParts() with negative value.
     */
    public function testToPartsNegative(): void
    {
        $time = new Time(-3661, 's');
        $parts = $time->toParts();

        $this->assertSame(-1, $parts['sign']);
        $this->assertSame(1, $parts['h']);
        $this->assertSame(1, $parts['min']);
        $this->assertSame(1.0, $parts['s']);
    }

    /**
     * Test toParts() with precision.
     */
    public function testToPartsWithPrecision(): void
    {
        $time = new Time(3661.5, 's');
        $parts = $time->toParts(precision: 1);

        $this->assertSame(1, $parts['h']);
        $this->assertSame(1, $parts['min']);
        $this->assertSame(1.5, $parts['s']);
    }

    /**
     * Test toParts() with Angle quantity.
     */
    public function testToPartsAngle(): void
    {
        // 45° 30' 30" = 45.508333...°
        $angle = new Angle(45.508333333333, 'deg');
        $parts = $angle->toParts(precision: 0);

        $this->assertSame(1, $parts['sign']);
        $this->assertSame(45, $parts['deg']);
        $this->assertSame(30, $parts['arcmin']);
        $this->assertSame(30.0, $parts['arcsec']);
    }

    // endregion

    // region formatParts() tests

    /**
     * Test formatParts() with Time quantity.
     */
    public function testFormatPartsTime(): void
    {
        $time = new Time(3661, 's');
        $result = $time->formatParts();

        $this->assertSame('1h 1min 1s', $result);
    }

    /**
     * Test formatParts() with negative value.
     */
    public function testFormatPartsNegative(): void
    {
        $time = new Time(-3661, 's');
        $result = $time->formatParts();

        $this->assertSame('-1h 1min 1s', $result);
    }

    /**
     * Test formatParts() with showZeros.
     */
    public function testFormatPartsShowZeros(): void
    {
        $time = new Time(3600, 's');
        $result = $time->formatParts(showZeros: true);

        $this->assertSame('0y 0mo 0w 0d 1h 0min 0s', $result);
    }

    /**
     * Test formatParts() with zero value.
     */
    public function testFormatPartsZero(): void
    {
        $time = new Time(0, 's');
        $result = $time->formatParts();

        $this->assertSame('0s', $result);
    }

    // endregion

    // region fromParts() error tests

    /**
     * Test fromParts() on unregistered quantity type throws exception.
     */
    public function testFromPartsUnregisteredQuantityTypeThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('unregistered');

        // Base Quantity class has no registered quantity type.
        Quantity::fromParts([
            'm' => 100,
        ]);
    }

    /**
     * Test fromParts() with invalid sign throws exception.
     */
    public function testFromPartsInvalidSignThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid sign: 0.');

        Time::fromParts([
            'h'    => 1,
            'sign' => 0,
        ]);
    }

    // endregion

    // region validatePrecision() tests

    /**
     * Test toParts() with negative precision throws exception.
     */
    public function testToPartsNegativePrecisionThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid precision: -1.');

        $time = new Time(3661, 's');
        $time->toParts(precision: -1);
    }

    /**
     * Test formatParts() with negative precision throws exception.
     */
    public function testFormatPartsNegativePrecisionThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid precision: -1.');

        $time = new Time(3661, 's');
        $time->formatParts(precision: -1);
    }

    // endregion

    // region validatePartUnitSymbols() tests

    /**
     * Test toParts() on base Quantity class without default part unit symbols throws exception.
     */
    public function testToPartsOnBaseQuantityThrowsException(): void
    {
        $this->expectException(LengthException::class);
        $this->expectExceptionMessage('Cannot use an empty array');

        // Base Quantity class has no default part unit symbols.
        $qty = Quantity::create(100, 'kg*m/s2');
        $qty->toParts();
    }

    /**
     * Test toParts() with unknown unit in class default throws exception.
     */
    public function testToPartsWithUnknownUnitInDefaultThrowsException(): void
    {
        // Temporarily set bad partUnitSymbols on the time quantity type.
        $original = Time::getPartUnitSymbols();
        Time::setPartUnitSymbols(['h', 'min', 'xyz']);

        try {
            $this->expectException(UnknownUnitException::class);
            $this->expectExceptionMessage("Unknown unit: 'xyz'");

            $time = new Time(100, 's');
            $time->toParts();
        } finally {
            Time::setPartUnitSymbols($original);
        }
    }

    // endregion

    // region getPartUnitSymbols() / setPartUnitSymbols() tests

    /**
     * Test getPartUnitSymbols() returns null on base Quantity (no registered type).
     */
    public function testGetPartUnitSymbolsThrowsOnBaseQuantity(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('unregistered');

        Quantity::getPartUnitSymbols();
    }

    /**
     * Test setPartUnitSymbols() throws on base Quantity (no registered type).
     */
    public function testSetPartUnitSymbolsThrowsOnBaseQuantity(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('unregistered');

        Quantity::setPartUnitSymbols(['m', 'cm']);
    }

    // endregion

    // region getResultUnitSymbol() / setResultUnitSymbol() tests

    /**
     * Test getResultUnitSymbol() returns null on base Quantity (no registered type).
     */
    public function testGetResultUnitSymbolThrowsOnBaseQuantity(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('unregistered');

        Quantity::getResultUnitSymbol();
    }

    /**
     * Test setResultUnitSymbol() changes the symbol on a registered type.
     */
    public function testSetResultUnitSymbolChangesSymbol(): void
    {
        $original = Time::getResultUnitSymbol();

        try {
            Time::setResultUnitSymbol('min');
            $this->assertSame('min', Time::getResultUnitSymbol());
        } finally {
            Time::setResultUnitSymbol($original);
        }
    }

    /**
     * Test setResultUnitSymbol() throws on base Quantity (no registered type).
     */
    public function testSetResultUnitSymbolThrowsOnBaseQuantity(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('unregistered');

        Quantity::setResultUnitSymbol('m');
    }

    // endregion

    // region parseParts() tests

    /**
     * Test parseParts() with a time parts string.
     */
    public function testParsePartsTime(): void
    {
        $time = Time::parseParts('1h 30min 45s');

        $this->assertInstanceOf(Time::class, $time);
        $this->assertSame('s', $time->derivedUnit->format());
        $this->assertSame(5445.0, $time->value);
    }

    /**
     * Test parseParts() with a negative time parts string.
     */
    public function testParsePartsNegativeTime(): void
    {
        $time = Time::parseParts('-1h 1min 1s');

        $this->assertInstanceOf(Time::class, $time);
        $this->assertSame(-3661.0, $time->value);
    }

    /**
     * Test parseParts() with an angle parts string.
     */
    public function testParsePartsAngle(): void
    {
        $angle = Angle::parseParts('45deg 30arcmin 30arcsec');

        $this->assertInstanceOf(Angle::class, $angle);
        $this->assertEqualsWithDelta(45.508333333, $angle->value, 1e-6);
    }

    /**
     * Test parseParts() with a single part.
     */
    public function testParsePartsSinglePart(): void
    {
        $time = Time::parseParts('90min');

        $this->assertInstanceOf(Time::class, $time);
        $this->assertSame(5400.0, $time->value);
    }

    /**
     * Test parseParts() with decimal value in smallest unit.
     */
    public function testParsePartsDecimalSmallestUnit(): void
    {
        $time = Time::parseParts('1h 30min 45.5s');

        $this->assertSame(5445.5, $time->value);
    }

    /**
     * Test parseParts() with empty input throws exception.
     */
    public function testParsePartsEmptyInputThrowsException(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('The input string is empty.');

        Time::parseParts('');
    }

    /**
     * Test parseParts() with invalid format throws exception.
     */
    public function testParsePartsInvalidFormatThrowsException(): void
    {
        $this->expectException(FormatException::class);

        Time::parseParts('not a quantity');
    }

    /**
     * Test parseParts() with negative part in non-first position throws exception.
     */
    public function testParsePartsNegativeNonFirstPartThrowsException(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('only the first may be negative');

        Time::parseParts('1h -30min 45s');
    }

    /**
     * Test parseParts() throws DimensionMismatchException when a part has the wrong dimension.
     */
    public function testParsePartsMixedDimensionsThrowsException(): void
    {
        $this->expectException(DimensionMismatchException::class);

        Time::parseParts('1h 45mi 34s');
    }

    /**
     * Test parseParts() on unregistered quantity type throws exception.
     */
    public function testParsePartsUnregisteredQuantityTypeThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('unregistered');

        Quantity::parseParts('100m 50cm');
    }

    // endregion

    // region toParts() rounding tests

    /**
     * Test toParts() rounding up smallest unit.
     */
    public function testToPartsRoundingUp(): void
    {
        // 59 minutes 59.9 seconds → should round to 1h 0min 0s when precision=0.
        $time = new Time(3599.9, 's');
        $parts = $time->toParts(precision: 0);

        $this->assertSame(1, $parts['h']);
        $this->assertSame(0, $parts['min']);
        $this->assertSame(0.0, $parts['s']);
    }

    // endregion
}
