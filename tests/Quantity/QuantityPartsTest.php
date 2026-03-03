<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Quantity;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType\Angle;
use Galaxon\Quantities\QuantityType\Time;
use Galaxon\Quantities\Services\QuantityPartsService;
use Galaxon\Quantities\Services\QuantityTypeService;
use InvalidArgumentException;
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
        $parts = $time->toParts(['h', 'min', 's']);

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
        $parts = $time->toParts(['h', 'min', 's']);

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
        $parts = $time->toParts(['h', 'min', 's'], precision: 1);

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
        $result = $time->formatParts(['h', 'min', 's'], showZeros: true);

        $this->assertSame('1h 0min 0s', $result);
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
     * Test fromParts() without result unit and no default throws exception.
     */
    public function testFromPartsWithoutResultUnitThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('No result unit symbol provided and no default set.');

        // Base Quantity class has no default result unit symbol.
        Quantity::fromParts([
            'm' => 100,
        ]);
    }

    /**
     * Test fromParts() with unknown result unit throws exception.
     */
    public function testFromPartsUnknownResultUnitThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Unknown result unit 'xyz'");

        Quantity::fromParts([
            'm' => 100,
        ], 'xyz');
    }

    /**
     * Test fromParts() with result unit of incompatible dimension throws exception.
     */
    public function testFromPartsIncompatibleResultUnitThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('incompatible with time quantities');

        // 'm' is a length unit, not a time unit.
        Time::fromParts([
            'h'   => 1,
            'min' => 30,
        ], 'm');
    }

    /**
     * Test fromParts() with invalid sign throws exception.
     */
    public function testFromPartsInvalidSignThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid sign: 0. Must be -1 or 1.');

        Quantity::fromParts([
            'm'    => 100,
            'sign' => 0,
        ], 'm');
    }

    // endregion

    // region validatePrecision() tests

    /**
     * Test toParts() with negative precision throws exception.
     */
    public function testToPartsNegativePrecisionThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid precision specified; -1. Must be null or a non-negative integer.');

        $time = new Time(3661, 's');
        $time->toParts(precision: -1);
    }

    /**
     * Test formatParts() with negative precision throws exception.
     */
    public function testFormatPartsNegativePrecisionThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid precision specified');

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
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('The array of part unit symbols must not be empty.');

        // Base Quantity class has no default part unit symbols.
        $qty = Quantity::create(100, 'kg*m/s2');
        $qty->toParts();
    }

    /**
     * Test toParts() with empty array throws exception.
     */
    public function testToPartsEmptyArrayThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('The array of part unit symbols must not be empty.');

        $time = new Time(3661, 's');
        $time->toParts([]);
    }

    /**
     * Test toParts() with unknown unit in part unit symbols throws exception.
     */
    public function testToPartsWithUnknownUnitThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Unknown unit symbol: 'xyz'");

        $time = new Time(3661, 's');
        $time->toParts(['h', 'min', 'xyz']);
    }

    /**
     * Test toParts() with unknown unit in class default throws exception.
     */
    public function testToPartsWithUnknownUnitInDefaultThrowsException(): void
    {
        // Temporarily set bad partUnitSymbols on the time QuantityType.
        $qtyType = QuantityTypeService::getByName('time');
        $this->assertNotNull($qtyType);
        $original = $qtyType->partUnitSymbols;
        $qtyType->partUnitSymbols = ['h', 'min', 'xyz'];

        try {
            $this->expectException(DomainException::class);
            $this->expectExceptionMessage("Unknown unit symbol: 'xyz'");

            $time = new Time(100, 's');
            $time->toParts();
        } finally {
            $qtyType->partUnitSymbols = $original;
        }
    }

    // endregion

    // region getDefaultPartUnitSymbols() / setDefaultPartUnitSymbols() tests

    /**
     * Test getDefaultPartUnitSymbols() returns the class default for Time.
     */
    public function testGetDefaultPartUnitSymbolsTime(): void
    {
        $this->assertSame(
            ['y', 'mo', 'w', 'd', 'h', 'min', 's'],
            QuantityPartsService::getDefaultPartUnitSymbols(Time::class)
        );
    }

    /**
     * Test getDefaultPartUnitSymbols() returns the class default for Angle.
     */
    public function testGetDefaultPartUnitSymbolsAngle(): void
    {
        $this->assertSame(
            ['deg', 'arcmin', 'arcsec'],
            QuantityPartsService::getDefaultPartUnitSymbols(Angle::class)
        );
    }

    /**
     * Test getDefaultPartUnitSymbols() returns null for base Quantity.
     */
    public function testGetDefaultPartUnitSymbolsBaseQuantity(): void
    {
        $this->assertNull(QuantityPartsService::getDefaultPartUnitSymbols(Quantity::class));
    }

    /**
     * Test setDefaultPartUnitSymbols() changes the value and getDefaultPartUnitSymbols() reflects it.
     */
    public function testSetDefaultPartUnitSymbols(): void
    {
        $original = QuantityPartsService::getDefaultPartUnitSymbols(Time::class);
        $this->assertNotNull($original);
        try {
            QuantityPartsService::setDefaultPartUnitSymbols(Time::class, ['h', 'min', 's']);
            $this->assertSame(
                ['h', 'min', 's'],
                QuantityPartsService::getDefaultPartUnitSymbols(Time::class)
            );
        } finally {
            QuantityPartsService::setDefaultPartUnitSymbols(Time::class, $original);
        }
    }

    /**
     * Test setDefaultPartUnitSymbols() deduplicates and re-indexes.
     */
    public function testSetDefaultPartUnitSymbolsDeduplicates(): void
    {
        $original = QuantityPartsService::getDefaultPartUnitSymbols(Time::class);
        $this->assertNotNull($original);
        try {
            QuantityPartsService::setDefaultPartUnitSymbols(Time::class, ['h', 'min', 'h', 's']);
            $this->assertSame(
                ['h', 'min', 's'],
                QuantityPartsService::getDefaultPartUnitSymbols(Time::class)
            );
        } finally {
            QuantityPartsService::setDefaultPartUnitSymbols(Time::class, $original);
        }
    }

    /**
     * Test setDefaultPartUnitSymbols() with empty array throws exception.
     */
    public function testSetDefaultPartUnitSymbolsEmptyThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('The array of part unit symbols must not be empty.');

        QuantityPartsService::setDefaultPartUnitSymbols(Time::class, []);
    }

    /**
     * Test setDefaultPartUnitSymbols() with non-string item throws exception.
     */
    public function testSetDefaultPartUnitSymbolsNonStringThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must contain only strings');

        QuantityPartsService::setDefaultPartUnitSymbols(Time::class, ['h', 42]); // @phpstan-ignore argument.type
    }

    /**
     * Test setDefaultPartUnitSymbols() with unknown unit throws exception.
     */
    public function testSetDefaultPartUnitSymbolsUnknownUnitThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Unknown unit symbol: 'xyz'.");

        QuantityPartsService::setDefaultPartUnitSymbols(Time::class, ['h', 'xyz']);
    }

    // endregion

    // region getDefaultResultUnitSymbol() / setDefaultResultUnitSymbol() tests

    /**
     * Test getDefaultResultUnitSymbol() returns the class default for Time.
     */
    public function testGetDefaultResultUnitSymbolTime(): void
    {
        $this->assertSame('s', QuantityPartsService::getDefaultResultUnitSymbol(Time::class));
    }

    /**
     * Test getDefaultResultUnitSymbol() returns the class default for Angle.
     */
    public function testGetDefaultResultUnitSymbolAngle(): void
    {
        $this->assertSame('deg', QuantityPartsService::getDefaultResultUnitSymbol(Angle::class));
    }

    /**
     * Test getDefaultResultUnitSymbol() returns null for base Quantity.
     */
    public function testGetDefaultResultUnitSymbolBaseQuantity(): void
    {
        $this->assertNull(QuantityPartsService::getDefaultResultUnitSymbol(Quantity::class));
    }

    /**
     * Test setDefaultResultUnitSymbol() changes the value and getDefaultResultUnitSymbol() reflects it.
     */
    public function testSetDefaultResultUnitSymbol(): void
    {
        $original = QuantityPartsService::getDefaultResultUnitSymbol(Time::class);
        $this->assertNotNull($original);
        try {
            QuantityPartsService::setDefaultResultUnitSymbol(Time::class, 'min');
            $this->assertSame('min', QuantityPartsService::getDefaultResultUnitSymbol(Time::class));
        } finally {
            QuantityPartsService::setDefaultResultUnitSymbol(Time::class, $original);
        }
    }

    /**
     * Test setDefaultResultUnitSymbol() with unknown unit throws exception.
     */
    public function testSetDefaultResultUnitSymbolUnknownUnitThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Unknown unit symbol: 'xyz'.");

        QuantityPartsService::setDefaultResultUnitSymbol(Time::class, 'xyz');
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
     * Test parseParts() with explicit result unit symbol.
     */
    public function testParsePartsWithResultUnit(): void
    {
        $time = Time::parseParts('1h 30min', 'min');

        $this->assertInstanceOf(Time::class, $time);
        $this->assertSame('min', $time->derivedUnit->format());
        $this->assertSame(90.0, $time->value);
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
     * Test parseParts() on base Quantity without default result unit throws exception.
     */
    public function testParsePartsNoDefaultResultUnitThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('No result unit symbol provided and no default set.');

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
        $parts = $time->toParts(['h', 'min', 's'], precision: 0);

        $this->assertSame(1, $parts['h']);
        $this->assertSame(0, $parts['min']);
        $this->assertSame(0.0, $parts['s']);
    }

    // endregion
}
