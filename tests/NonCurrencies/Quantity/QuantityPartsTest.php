<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\NonCurrencies\Quantity;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Quantities\Exceptions\DimensionMismatchException;
use Galaxon\Quantities\Exceptions\UnknownUnitException;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType\Angle;
use Galaxon\Quantities\QuantityType\Dimensionless;
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
     * Test formatParts() with a Time quantity.
     */
    public function testFormatPartsTime(): void
    {
        $time = new Time(3661, 's');
        $result = $time->formatParts();

        $this->assertSame('1h 1min 1s', $result);
    }

    /**
     * Test formatParts() with a negative value.
     */
    public function testFormatPartsNegative(): void
    {
        $time = new Time(-3661, 's');
        $result = $time->formatParts();

        $this->assertSame('-1h 1min 1s', $result);
    }

    /**
     * Test formatParts() with zero value always includes the smallest unit.
     */
    public function testFormatPartsZero(): void
    {
        $time = new Time(0, 's');
        $result = $time->formatParts();

        $this->assertSame('0s', $result);
    }

    /**
     * Test formatParts() with showZeros includes all parts, even zero-value ones.
     */
    public function testFormatPartsShowZeros(): void
    {
        $time = new Time(3600, 's');
        $result = $time->formatParts(showZeros: true);

        $this->assertSame('0y 0mo 0w 0d 1h 0min 0s', $result);
    }

    /**
     * Test formatParts() with precision formats the smallest unit to the given decimal places.
     */
    public function testFormatPartsWithPrecision(): void
    {
        $time = new Time(3661.5, 's');
        $result = $time->formatParts(precision: 1);

        $this->assertSame('1h 1min 1.5s', $result);
    }

    /**
     * Test formatParts() with an Angle quantity uses Unicode symbols by default.
     */
    public function testFormatPartsAngleUnicode(): void
    {
        $angle = new Angle(45.508333333, 'deg');
        $result = $angle->formatParts(precision: 0);

        $this->assertSame('45° 30′ 30″', $result);
    }

    /**
     * Test formatParts() with ascii: true uses ASCII unit symbols.
     */
    public function testFormatPartsAngleAscii(): void
    {
        $angle = new Angle(45.508333333, 'deg');
        $result = $angle->formatParts(precision: 0, ascii: true);

        $this->assertSame('45deg 30arcmin 30arcsec', $result);
    }

    // endregion

    // region fromParts() tests

    /**
     * Test fromParts() with English base result (default).
     */
    public function testFromPartsEnglishDefault(): void
    {
        $length = Length::fromParts([
            'ft' => 5,
            'in' => 6,
        ]);

        $this->assertInstanceOf(Length::class, $length);
        $this->assertSame('ft', $length->compoundUnit->asciiSymbol);
        $this->assertSame(5.5, $length->value);
    }

    /**
     * Test fromParts() with $si = true uses SI base unit for the result.
     */
    public function testFromPartsSiTrue(): void
    {
        $length = Length::fromParts([
            'ft' => 1,
        ], si: true);

        $this->assertInstanceOf(Length::class, $length);
        $this->assertSame('m', $length->compoundUnit->asciiSymbol);
        $this->assertEqualsWithDelta(0.3048, $length->value, 1e-10);
    }

    /**
     * Test fromParts() accepts prefixed unit symbols (e.g. 'km').
     */
    public function testFromPartsPrefixedSymbols(): void
    {
        $length = Length::fromParts([
            'km' => 5,
            'm'  => 300,
        ]);

        $this->assertInstanceOf(Length::class, $length);
        $this->assertEqualsWithDelta(5300.0, $length->to('m')->value, 1e-10);
    }

    /**
     * Test fromParts() with negative sign.
     */
    public function testFromPartsNegativeSign(): void
    {
        $time = Time::fromParts([
            'sign' => -1,
            'h'    => 1,
            'min'  => 30,
        ]);

        $this->assertInstanceOf(Time::class, $time);
        $this->assertEqualsWithDelta(-5400.0, $time->to('s')->value, 1e-10);
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

    /**
     * Test fromParts() with empty parts throws exception.
     */
    public function testFromPartsEmptyThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot create a Quantity from an empty parts array.');

        Time::fromParts([]);
    }

    /**
     * Test fromParts() with only a sign and no actual parts throws exception.
     */
    public function testFromPartsSignOnlyThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot create a Quantity from an empty parts array.');

        Time::fromParts([
            'sign' => 1,
        ]);
    }

    /**
     * Test fromParts() with mismatched dimensions throws exception.
     */
    public function testFromPartsDimensionMismatchThrowsException(): void
    {
        $this->expectException(DimensionMismatchException::class);

        Length::fromParts([
            'ft' => 1,
            's'  => 5,
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
        $this->assertEqualsWithDelta(5445.0, $time->to('s')->value, 1e-9);
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
        $this->assertEqualsWithDelta(5445.0, $time->to('s')->value, 1e-9);
    }

    /**
     * Test parseParts() with a negative time parts string.
     */
    public function testParsePartsNegativeTime(): void
    {
        $time = Time::parseParts('-1h 1min 1s');

        $this->assertInstanceOf(Time::class, $time);
        $this->assertEqualsWithDelta(-3661.0, $time->to('s')->value, 1e-6);
    }

    /**
     * Test parseParts() with an angle parts string.
     */
    public function testParsePartsAngle(): void
    {
        $angle = Angle::parseParts('45deg 30arcmin 30arcsec');

        $this->assertInstanceOf(Angle::class, $angle);
        $this->assertEqualsWithDelta(45.508333333, $angle->to('deg')->value, 1e-6);
    }

    /**
     * Test parseParts() with a single part.
     */
    public function testParsePartsSinglePart(): void
    {
        $time = Time::parseParts('90min');

        $this->assertInstanceOf(Time::class, $time);
        $this->assertEqualsWithDelta(5400.0, $time->to('s')->value, 1e-6);
    }

    /**
     * Test parseParts() with decimal value in smallest unit.
     */
    public function testParsePartsDecimalSmallestUnit(): void
    {
        $time = Time::parseParts('1h 30min 45.5s');

        $this->assertEqualsWithDelta(5445.5, $time->to('s')->value, 1e-6);
    }

    /**
     * Test parseParts() with multi-word unit symbols (no space between value and unit).
     */
    public function testParsePartsWithMultiWordUnits(): void
    {
        $mass = Mass::parseParts('12LT 3st 4lb');

        $this->assertInstanceOf(Mass::class, $mass);
        // 12 LT + 3 st + 4 lb = 12 * 2240 + 3 * 14 + 4 = 26926 lb
        $this->assertEqualsWithDelta(26926.0, $mass->to('lb')->value, 1e-6);
    }

    /**
     * Test parseParts() with a bare number (dimensionless).
     */
    public function testParsePartsBareNumber(): void
    {
        $qty = Dimensionless::parseParts('42');

        $this->assertInstanceOf(Dimensionless::class, $qty);
        $this->assertSame(42.0, $qty->value);
    }

    /**
     * Test parseParts() with mixed bare number and unit parts.
     */
    public function testParsePartsBareNumberWithUnits(): void
    {
        $qty = Dimensionless::parseParts('1000 45% 76ppm');

        $this->assertInstanceOf(Dimensionless::class, $qty);
        $this->assertEqualsWithDelta(1000.450076, $qty->value, 1e-10);
    }

    /**
     * Test parseParts() with duplicate bare numbers throws exception.
     */
    public function testParsePartsDuplicateBareNumberThrowsException(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Duplicate unit symbol');

        Dimensionless::parseParts('1000 2000');
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
     * Test fromParts() works with Force using SI base.
     *
     * Force has no default part unit symbols, but fromParts() still works because we're passing explicit parts.
     * Using $si = true so the result is in SI base units (kg*m/s2) rather than English (lb*ft/s2).
     */
    public function testFromPartsForceSi(): void
    {
        $force = Force::fromParts([
            'N' => 100,
        ], si: true);

        $this->assertInstanceOf(Force::class, $force);
        $this->assertSame('kg*m/s2', $force->compoundUnit->asciiSymbol);
        $this->assertEqualsWithDelta(100.0, $force->to('N')->value, 1e-10);
    }

    /**
     * Test fromParts() works with Force using English base (default).
     */
    public function testFromPartsForceEnglish(): void
    {
        $force = Force::fromParts([
            'N' => 100,
        ]);

        $this->assertInstanceOf(Force::class, $force);
        $this->assertSame('lb*ft/s2', $force->compoundUnit->asciiSymbol);
        $this->assertEqualsWithDelta(100.0, $force->to('N')->value, 1e-10);
    }

    /**
     * Test toParts() with an empty array falls back to the default part unit symbols.
     */
    public function testToPartsEmptyPartUnitSymbolsUsesDefault(): void
    {
        $time = new Time(3661, 's');

        // Empty array should behave the same as null (use Time's built-in defaults).
        $partsDefault = $time->toParts();
        $partsEmpty = $time->toParts(partUnitSymbols: []);

        $this->assertSame($partsDefault, $partsEmpty);
    }

    /**
     * Test toParts() with an empty array throws when no default part unit symbols are configured.
     */
    public function testToPartsEmptyPartUnitSymbolsThrowsWhenNoDefault(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('No default part unit symbols configured');

        $force = Force::create(100, 'N');
        $force->toParts(partUnitSymbols: []);
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

    /**
     * Test toParts() with duplicate part unit symbols throws exception.
     */
    public function testToPartsDuplicatePartUnitSymbolsThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Duplicate part unit');

        $time = new Time(3661, 's');
        $time->toParts(partUnitSymbols: ['h', 'min', 'h']);
    }

    /**
     * Test toParts() with wrong-dimension part unit symbols throws exception.
     */
    public function testToPartsDimensionMismatchThrowsException(): void
    {
        $this->expectException(DimensionMismatchException::class);

        $time = new Time(3661, 's');
        $time->toParts(partUnitSymbols: ['ft', 'in']);
    }

    // endregion

    // region toParts() additional tests

    /**
     * Test toParts() with zero value produces all-zero parts.
     */
    public function testToPartsZero(): void
    {
        $time = new Time(0, 's');
        $parts = $time->toParts();

        $this->assertSame(1, $parts['sign']);
        $this->assertSame(0, $parts['h']);
        $this->assertSame(0, $parts['min']);
        $this->assertSame(0.0, $parts['s']);
    }

    /**
     * Test toParts() with custom part unit symbols.
     */
    public function testToPartsCustomPartUnitSymbols(): void
    {
        $time = new Time(5445, 's');
        $parts = $time->toParts(partUnitSymbols: ['h', 'min', 's']);

        $this->assertSame(1, $parts['sign']);
        $this->assertSame(1, $parts['h']);
        $this->assertSame(30, $parts['min']);
        $this->assertSame(45.0, $parts['s']);
        // Custom list should not include default units like 'y', 'mo', etc.
        $this->assertArrayNotHasKey('y', $parts);
        $this->assertArrayNotHasKey('d', $parts);
    }

    /**
     * Test toParts() with prefixed part unit symbols (e.g. 'km', 'm').
     */
    public function testToPartsPrefixedPartUnitSymbols(): void
    {
        $length = new Length(5300, 'm');
        $parts = $length->toParts(partUnitSymbols: ['km', 'm']);

        $this->assertSame(1, $parts['sign']);
        $this->assertSame(5, $parts['km']);
        $this->assertSame(300.0, $parts['m']);
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
