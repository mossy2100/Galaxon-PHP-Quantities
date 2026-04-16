<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\NonCurrencies\Quantity;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Quantities\Exceptions\DimensionMismatchException;
use Galaxon\Quantities\Exceptions\UnknownUnitException;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType\Angle;
use Galaxon\Quantities\QuantityType\Force;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\QuantityType\Time;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for parts-related methods on Quantity objects.
 */
#[CoversClass(Quantity::class)]
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

        $this->assertSame('1 h 1 min 1 s', $result);
    }

    /**
     * Test formatParts() with negative value.
     */
    public function testFormatPartsNegative(): void
    {
        $time = new Time(-3661, 's');
        $result = $time->formatParts();

        $this->assertSame('-1 h 1 min 1 s', $result);
    }

    /**
     * Test formatParts() with showZeros.
     */
    public function testFormatPartsShowZeros(): void
    {
        $time = new Time(3600, 's');
        $result = $time->formatParts(showZeros: true);

        $this->assertSame('0 y 0 mo 0 w 0 d 1 h 0 min 0 s', $result);
    }

    /**
     * Test formatParts() with zero value.
     */
    public function testFormatPartsZero(): void
    {
        $time = new Time(0, 's');
        $result = $time->formatParts();

        $this->assertSame('0 s', $result);
    }

    /**
     * Test formatParts() with includeSpace: false removes spaces between values and units.
     */
    public function testFormatPartsIncludeSpaceFalse(): void
    {
        $time = new Time(3661, 's');
        $result = $time->formatParts(includeSpace: false);

        $this->assertSame('1h 1min 1s', $result);
    }

    /**
     * Test formatParts() with includeSpace: true forces spaces even for non-letters.
     */
    public function testFormatPartsIncludeSpaceTrue(): void
    {
        $angle = new Angle(45.508333333, 'deg');
        $result = $angle->formatParts(precision: 0, includeSpace: true);

        $this->assertSame('45 ° 30 ′ 30 ″', $result);
    }

    /**
     * Test formatParts() with includeSpace: null (auto) omits space for non-letters.
     */
    public function testFormatPartsIncludeSpaceAutoAngle(): void
    {
        $angle = new Angle(45.508333333, 'deg');
        $result = $angle->formatParts(precision: 0);

        $this->assertSame('45° 30′ 30″', $result);
    }

    /**
     * Test formatParts() with includeSpace: false and negative value.
     */
    public function testFormatPartsIncludeSpaceFalseNegative(): void
    {
        $time = new Time(-3661, 's');
        $result = $time->formatParts(includeSpace: false);

        $this->assertSame('-1h 1min 1s', $result);
    }

    // endregion

    // region fromParts() error tests

    /**
     * Test fromParts() on base Quantity class works (no registered quantity type needed).
     */
    public function testFromPartsOnBaseQuantity(): void
    {
        $qty = Quantity::fromParts([
            'ft' => 5,
            'in' => 6,
        ]);

        $this->assertSame('ft', $qty->compoundUnit->asciiSymbol);
        $this->assertSame(5.5, $qty->value);
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

    // region toParts() error tests

    /**
     * Test toParts() on a quantity type with no part unit symbols configured throws.
     */
    public function testToPartsOnBaseQuantityThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('No default part unit symbols configured for force quantities');

        // The Force quantity type has no default part unit symbols.
        $qty = Quantity::create(100, 'kg*m/s2');
        $qty->toParts();
    }

    // endregion

    // region parse() tests

    /**
     * Test parse() with a single quantity string (no space).
     */
    public function testParseSingleQuantityNoSpace(): void
    {
        $length = Quantity::parse('123.45km');

        $this->assertSame('km', $length->compoundUnit->asciiSymbol);
        $this->assertSame(123.45, $length->value);
    }

    /**
     * Test parse() with a single quantity string (with space).
     */
    public function testParseSingleQuantityWithSpace(): void
    {
        $length = Quantity::parse('123.45 km');

        $this->assertSame('km', $length->compoundUnit->asciiSymbol);
        $this->assertSame(123.45, $length->value);
    }

    /**
     * Test parse() falls back to parseParts() for multi-part strings.
     */
    public function testParseMultiPartFallback(): void
    {
        $time = Time::parse('1h 30min 45s');

        $this->assertInstanceOf(Time::class, $time);
        $this->assertSame('h', $time->compoundUnit->asciiSymbol);
        $this->assertEqualsWithDelta(1.5125, $time->value, 1e-9);
    }

    /**
     * Test parse() with empty input throws exception.
     */
    public function testParseEmptyInputThrowsException(): void
    {
        $this->expectException(FormatException::class);

        Time::parse('');
    }

    /**
     * Test parse() with invalid format throws exception.
     */
    public function testParseInvalidFormatThrowsException(): void
    {
        $this->expectException(FormatException::class);

        Time::parse('not a quantity');
    }

    /**
     * Test parse() with dimension mismatch throws exception.
     */
    public function testParseDimensionMismatchThrowsException(): void
    {
        $this->expectException(DimensionMismatchException::class);

        Length::parse('123 kg');
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
        $this->assertSame('h', $time->compoundUnit->asciiSymbol);
        $this->assertEqualsWithDelta(1.5125, $time->value, 1e-9);
    }

    /**
     * Test parseParts() with a negative time parts string.
     */
    public function testParsePartsNegativeTime(): void
    {
        $time = Time::parseParts('-1h 1min 1s');

        $this->assertInstanceOf(Time::class, $time);
        $this->assertSame('h', $time->compoundUnit->asciiSymbol);
        $this->assertEqualsWithDelta(-1.016944444, $time->value, 1e-6);
    }

    /**
     * Test parseParts() with an angle parts string.
     */
    public function testParsePartsAngle(): void
    {
        $angle = Angle::parseParts('45deg 30arcmin 30arcsec');

        $this->assertInstanceOf(Angle::class, $angle);
        $this->assertSame('deg', $angle->compoundUnit->asciiSymbol);
        $this->assertEqualsWithDelta(45.508333333, $angle->value, 1e-6);
    }

    /**
     * Test parseParts() with a single part.
     */
    public function testParsePartsSinglePart(): void
    {
        $time = Time::parseParts('90min');

        $this->assertInstanceOf(Time::class, $time);
        $this->assertSame('min', $time->compoundUnit->asciiSymbol);
        $this->assertSame(90.0, $time->value);
    }

    /**
     * Test parseParts() with decimal value in smallest unit.
     */
    public function testParsePartsDecimalSmallestUnit(): void
    {
        $time = Time::parseParts('1h 30min 45.5s');

        $this->assertSame('h', $time->compoundUnit->asciiSymbol);
        $this->assertEqualsWithDelta(1.512638889, $time->value, 1e-6);
    }

    /**
     * Test parseParts() with spaces between values and units.
     */
    public function testParsePartsWithSpacesBetweenValueAndUnit(): void
    {
        $time = Time::parseParts('1 h 1 min 1 s');

        $this->assertInstanceOf(Time::class, $time);
        $this->assertSame('h', $time->compoundUnit->asciiSymbol);
        $this->assertEqualsWithDelta(1.016944444, $time->value, 1e-6);
    }

    /**
     * Test parseParts() with multi-word unit symbols and spaces.
     */
    public function testParsePartsWithMultiWordUnits(): void
    {
        $mass = Mass::parseParts('12 LT 3 st 4 lb');

        $this->assertInstanceOf(Mass::class, $mass);
        $this->assertSame('LT', $mass->compoundUnit->asciiSymbol);
        // 12 LT + 3 st + 4 lb = (12 * 2240 + 3 * 14 + 4) / 2240 = 12.020535714... LT
        $this->assertEqualsWithDelta(12.020535714, $mass->value, 1e-6);
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

    // endregion

    // region validator coverage tests

    /**
     * Test fromParts() works with Force (which has no default result unit).
     */
    public function testFromPartsForce(): void
    {
        $force = Force::fromParts([
            'N' => 100,
        ]);

        $this->assertInstanceOf(Force::class, $force);
        $this->assertSame('N', $force->compoundUnit->asciiSymbol);
        $this->assertSame(100.0, $force->value);
    }

    /**
     * Test toParts() throws when an empty array of part unit symbols is passed.
     */
    public function testToPartsEmptyPartUnitSymbolsThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('The array of part unit symbols cannot be empty.');

        $time = new Time(60, 's');
        $time->toParts(partUnitSymbols: []);
    }

    /**
     * Test toParts() throws when a part unit symbol is not a string.
     */
    public function testToPartsNonStringPartUnitSymbolThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The array of part unit symbols must contain only strings.');

        $time = new Time(60, 's');
        // @phpstan-ignore argument.type
        $time->toParts(partUnitSymbols: [1, 'h']);
    }

    /**
     * Test toParts() throws when a part unit symbol is not a recognized unit.
     */
    public function testToPartsUnknownPartUnitSymbolThrowsException(): void
    {
        $this->expectException(UnknownUnitException::class);

        $time = new Time(60, 's');
        $time->toParts(partUnitSymbols: ['xyz']);
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
