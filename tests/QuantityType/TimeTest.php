<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\QuantityType;

use DateInterval;
use Galaxon\Core\Traits\FloatAssertions;
use Galaxon\Quantities\QuantityType\Time;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Time quantity type.
 */
#[CoversClass(Time::class)]
final class TimeTest extends TestCase
{
    use FloatAssertions;

    // region Conversion tests

    /**
     * Test converting seconds to minutes.
     */
    public function testConvertSecondsToMinutes(): void
    {
        $time = new Time(120, 's');
        $min = $time->to('min');

        $this->assertInstanceOf(Time::class, $min);
        $this->assertSame(2.0, $min->value);
        $this->assertSame('min', $min->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting minutes to hours.
     */
    public function testConvertMinutesToHours(): void
    {
        $time = new Time(90, 'min');
        $h = $time->to('h');

        $this->assertSame(1.5, $h->value);
    }

    /**
     * Test converting hours to days.
     */
    public function testConvertHoursToDays(): void
    {
        $time = new Time(48, 'h');
        $d = $time->to('d');

        $this->assertSame(2.0, $d->value);
    }

    /**
     * Test converting days to weeks.
     */
    public function testConvertDaysToWeeks(): void
    {
        $time = new Time(14, 'd');
        $w = $time->to('w');

        $this->assertSame(2.0, $w->value);
    }

    /**
     * Test converting years to months.
     */
    public function testConvertYearsToMonths(): void
    {
        $time = new Time(1, 'y');
        $mo = $time->to('mo');

        $this->assertSame(12.0, $mo->value);
    }

    /**
     * Test converting years to days (using Gregorian average).
     */
    public function testConvertYearsToDays(): void
    {
        $time = new Time(1, 'y');
        $d = $time->to('d');

        $this->assertSame(365.2425, $d->value);
    }

    /**
     * Test converting milliseconds to seconds.
     */
    public function testConvertMillisecondsToSeconds(): void
    {
        $time = new Time(1000, 'ms');
        $s = $time->to('s');

        $this->assertSame(1.0, $s->value);
    }

    /**
     * Test converting microseconds to milliseconds.
     */
    public function testConvertMicrosecondsToMilliseconds(): void
    {
        $time = new Time(1000, 'μs');
        $ms = $time->to('ms');

        $this->assertSame(1.0, $ms->value);
    }

    /**
     * Test converting nanoseconds to microseconds.
     */
    public function testConvertNanosecondsToMicroseconds(): void
    {
        $time = new Time(1000, 'ns');
        $us = $time->to('μs');

        $this->assertSame(1.0, $us->value);
    }

    // endregion

    // region DateInterval conversion tests

    /**
     * Test fromDateInterval with simple interval.
     */
    public function testFromDateIntervalSimple(): void
    {
        $interval = new DateInterval('PT1H30M');
        $time = Time::fromDateInterval($interval);

        $this->assertInstanceOf(Time::class, $time);
        // 1 hour + 30 minutes = 5400 seconds
        $this->assertSame(5400.0, $time->value);
        $this->assertSame('s', $time->derivedUnit->asciiSymbol);
    }

    /**
     * Test fromDateInterval with days.
     */
    public function testFromDateIntervalWithDays(): void
    {
        $interval = new DateInterval('P2D');
        $time = Time::fromDateInterval($interval);

        // 2 days = 2 * 24 * 60 * 60 = 172800 seconds
        $this->assertSame(172800.0, $time->value);
    }

    /**
     * Test fromDateInterval with years and months.
     */
    public function testFromDateIntervalWithYearsAndMonths(): void
    {
        $interval = new DateInterval('P1Y');
        $time = Time::fromDateInterval($interval);

        // 1 year = 365.2425 days = 365.2425 * 24 * 60 * 60 seconds
        $expectedSeconds = 365.2425 * 24 * 60 * 60;
        $this->assertApproxEqual($expectedSeconds, $time->value);
    }

    /**
     * Test fromDateInterval with negative interval.
     */
    public function testFromDateIntervalNegative(): void
    {
        $interval = new DateInterval('PT1H');
        $interval->invert = 1;
        $time = Time::fromDateInterval($interval);

        // -1 hour = -3600 seconds
        $this->assertSame(-3600.0, $time->value);
    }

    /**
     * Test fromDateInterval with fractional seconds.
     */
    public function testFromDateIntervalWithFractionalSeconds(): void
    {
        $interval = new DateInterval('PT1S');
        $interval->f = 0.5;
        $time = Time::fromDateInterval($interval);

        // 1.5 seconds
        $this->assertSame(1.5, $time->value);
    }

    /**
     * Test toDateInterval with simple time.
     */
    public function testToDateIntervalSimple(): void
    {
        $time = new Time(5400, 's');  // 1h 30min
        $interval = $time->toDateInterval();

        $this->assertInstanceOf(DateInterval::class, $interval);
        $this->assertSame(1, $interval->h);
        $this->assertSame(30, $interval->i);
        $this->assertSame(0, $interval->s);
    }

    /**
     * Test toDateInterval with days.
     */
    public function testToDateIntervalWithDays(): void
    {
        $time = new Time(2, 'd');
        $interval = $time->toDateInterval('y', 'd');

        $this->assertSame(2, $interval->d);
    }

    /**
     * Test toDateInterval with negative time.
     */
    public function testToDateIntervalNegative(): void
    {
        $time = new Time(-3600, 's');
        $interval = $time->toDateInterval();

        $this->assertSame(1, $interval->invert);
        $this->assertSame(1, $interval->h);
    }

    /**
     * Test toDateIntervalSpecifier with simple time.
     */
    public function testToDateIntervalSpecifierSimple(): void
    {
        $time = new Time(5400, 's');  // 1h 30min
        $spec = $time->toDateIntervalSpecifier();

        $this->assertSame('PT1H30M', $spec);
    }

    /**
     * Test toDateIntervalSpecifier with days.
     */
    public function testToDateIntervalSpecifierWithDays(): void
    {
        $time = new Time(90061, 's');  // 1d 1h 1min 1s
        $spec = $time->toDateIntervalSpecifier();

        $this->assertSame('P1DT1H1M1S', $spec);
    }

    /**
     * Test toDateIntervalSpecifier returns P0D for zero.
     */
    public function testToDateIntervalSpecifierZero(): void
    {
        $time = new Time(0, 's');
        $spec = $time->toDateIntervalSpecifier();

        $this->assertSame('P0D', $spec);
    }

    /**
     * Test toDateIntervalSpecifier with smallest unit.
     */
    public function testToDateIntervalSpecifierSmallestUnit(): void
    {
        $time = new Time(5400, 's');  // 1h 30min
        $spec = $time->toDateIntervalSpecifier('y', 'min');

        $this->assertSame('PT1H30M', $spec);
    }

    // endregion

    // region Parts methods tests

    /**
     * Test getPartsConfig returns correct structure.
     */
    public function testGetPartsConfig(): void
    {
        $config = Time::getPartsConfig();

        $this->assertArrayHasKey('from', $config);
        $this->assertArrayHasKey('to', $config);
        $this->assertSame('s', $config['from']);
        $this->assertSame(['y', 'mo', 'w', 'd', 'h', 'min', 's'], $config['to']);
    }

    /**
     * Test fromParts with hours and minutes.
     */
    public function testFromPartsHoursMinutes(): void
    {
        $time = Time::fromParts([
            'h'   => 1,
            'min' => 30,
        ]);

        // 1 hour + 30 minutes = 5400 seconds
        $this->assertSame(5400.0, $time->value);
        $this->assertSame('s', $time->derivedUnit->asciiSymbol);
    }

    /**
     * Test fromParts with years.
     */
    public function testFromPartsYears(): void
    {
        $time = Time::fromParts([
            'y' => 1,
        ]);

        // 1 year in seconds
        $expectedSeconds = 365.2425 * 24 * 60 * 60;
        $this->assertApproxEqual($expectedSeconds, $time->value);
    }

    /**
     * Test fromParts with weeks.
     */
    public function testFromPartsWeeks(): void
    {
        $time = Time::fromParts([
            'w' => 2,
        ]);

        // 2 weeks = 14 days = 14 * 24 * 60 * 60 seconds
        $this->assertSame(1209600.0, $time->value);
    }

    /**
     * Test fromParts with negative sign.
     */
    public function testFromPartsNegative(): void
    {
        $time = Time::fromParts([
            'h'    => 1,
            'sign' => -1,
        ]);

        // -1 hour = -3600 seconds
        $this->assertSame(-3600.0, $time->value);
    }

    /**
     * Test fromParts with negative value uses sign key instead.
     */
    public function testFromPartsNegativeValueUsesSign(): void
    {
        // Negative values in parts are handled via the 'sign' key
        $time = Time::fromParts([
            'h'    => 1,
            'sign' => -1,
        ]);

        $this->assertSame(-3600.0, $time->value);
    }

    /**
     * Test fromParts with custom result unit.
     */
    public function testFromPartsCustomResultUnit(): void
    {
        $time = Time::fromParts([
            'h'   => 2,
            'min' => 30,
        ], 'min');

        // 2.5 hours = 150 minutes
        $this->assertSame(150.0, $time->value);
        $this->assertSame('min', $time->derivedUnit->asciiSymbol);
    }

    /**
     * Test fromParts uses default result unit from config.
     */
    public function testFromPartsDefaultResultUnit(): void
    {
        $time = Time::fromParts([
            'd' => 1,
        ]);

        // Default result unit for Time is 's'
        $this->assertSame('s', $time->derivedUnit->asciiSymbol);
    }

    /**
     * Test toParts decomposes time correctly.
     */
    public function testToParts(): void
    {
        $time = new Time(90061, 's');  // 1d 1h 1min 1s
        $parts = $time->toParts(null, 's', 0);

        $this->assertSame(1, $parts['sign']);
        $this->assertSame(0, $parts['y']);
        $this->assertSame(0, $parts['mo']);
        $this->assertSame(0, $parts['w']);
        $this->assertSame(1, $parts['d']);
        $this->assertSame(1, $parts['h']);
        $this->assertSame(1, $parts['min']);
        $this->assertSame(1.0, $parts['s']);
    }

    /**
     * Test toParts with negative value.
     */
    public function testToPartsNegative(): void
    {
        $time = new Time(-3661, 's');  // -1h 1min 1s
        $parts = $time->toParts(null, 's', 0);

        $this->assertSame(-1, $parts['sign']);
        $this->assertSame(1, $parts['h']);
        $this->assertSame(1, $parts['min']);
        $this->assertSame(1.0, $parts['s']);
    }

    /**
     * Test toParts with precision causes carry.
     */
    public function testToPartsCarry(): void
    {
        $time = new Time(3599.999, 's');  // Just under 1 hour
        $parts = $time->toParts(null, 's', 0);

        // Should round to 3600s and carry to 1h
        $this->assertSame(1, $parts['h']);
        $this->assertSame(0, $parts['min']);
        $this->assertSame(0.0, $parts['s']);
    }

    /**
     * Test formatParts default.
     */
    public function testFormatPartsDefault(): void
    {
        $time = new Time(5400, 's');  // 1h 30min
        $result = $time->formatParts(null, 's', 0);

        $this->assertSame('1h 30min', $result);
    }

    /**
     * Test formatParts with days.
     */
    public function testFormatPartsWithDays(): void
    {
        $time = new Time(90061, 's');  // 1d 1h 1min 1s
        $result = $time->formatParts(null, 's', 0);

        $this->assertSame('1d 1h 1min 1s', $result);
    }

    /**
     * Test formatParts with precision.
     */
    public function testFormatPartsWithPrecision(): void
    {
        $time = new Time(3661.5, 's');  // 1h 1min 1.5s
        $result = $time->formatParts(null, 's', 1);

        $this->assertSame('1h 1min 1.5s', $result);
    }

    /**
     * Test formatParts for negative time.
     */
    public function testFormatPartsNegative(): void
    {
        $time = new Time(-3661, 's');  // -1h 1min 1s
        $result = $time->formatParts(null, 's', 0);

        $this->assertSame('-1h 1min 1s', $result);
    }

    /**
     * Test formatParts to minutes only.
     */
    public function testFormatPartsToMinutes(): void
    {
        $time = new Time(5400, 's');  // 1h 30min
        $result = $time->formatParts(null, 'min', 0);

        $this->assertSame('1h 30min', $result);
    }

    /**
     * Test formatParts showZeros option.
     *
     * showZeros=true shows all parts including zeros.
     */
    public function testFormatPartsShowZeros(): void
    {
        $time = new Time(3600, 's');  // 1h
        $result = $time->formatParts(null, 's', 0, true);

        // Shows all parts including zeros
        $this->assertSame('0y 0mo 0w 0d 1h 0min 0s', $result);
    }

    // endregion

    // region Parse tests

    /**
     * Test parsing time with seconds unit.
     */
    public function testParseSeconds(): void
    {
        $time = Time::parse('60 s');

        $this->assertInstanceOf(Time::class, $time);
        $this->assertSame(60.0, $time->value);
        $this->assertSame('s', $time->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing time with minutes unit.
     */
    public function testParseMinutes(): void
    {
        $time = Time::parse('30 min');

        $this->assertSame(30.0, $time->value);
        $this->assertSame('min', $time->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing time with prefixed unit.
     */
    public function testParsePrefixedUnit(): void
    {
        $time = Time::parse('500 ms');

        $this->assertSame(500.0, $time->value);
        $this->assertSame('ms', $time->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing time with hours.
     */
    public function testParseHours(): void
    {
        $time = Time::parse('2.5 h');

        $this->assertSame(2.5, $time->value);
        $this->assertSame('h', $time->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Round-trip tests

    /**
     * Test DateInterval round-trip conversion.
     */
    public function testDateIntervalRoundTrip(): void
    {
        $original = new DateInterval('P1DT2H30M45S');
        $time = Time::fromDateInterval($original);
        $result = $time->toDateInterval();

        $this->assertSame(1, $result->d);
        $this->assertSame(2, $result->h);
        $this->assertSame(30, $result->i);
        $this->assertSame(45, $result->s);
    }

    /**
     * Test parts round-trip conversion.
     */
    public function testPartsRoundTrip(): void
    {
        $time = Time::fromParts([
            'd'   => 1,
            'h'   => 2,
            'min' => 30,
            's'   => 45,
        ]);
        $formatted = $time->formatParts(null, 's', 0);

        $this->assertSame('1d 2h 30min 45s', $formatted);
    }

    // endregion
}
