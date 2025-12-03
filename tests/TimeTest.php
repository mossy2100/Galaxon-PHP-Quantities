<?php

declare(strict_types=1);

namespace Galaxon\Units\Tests;

use DateInterval;
use Galaxon\Units\MeasurementTypes\Time;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ValueError;

#[CoversClass(Time::class)]
final class TimeTest extends TestCase
{
    /**
     * Test toParts() with seconds as smallest unit.
     */
    public function testToPartsSeconds(): void
    {
        $time = new Time(3661.5, 's');
        $parts = $time->toParts('s');

        $this->assertEquals(1, $parts['sign']);
        $this->assertEquals(0, $parts['y']);
        $this->assertEquals(0, $parts['mo']);
        $this->assertEquals(0, $parts['d']);
        $this->assertEquals(0, $parts['w']);
        $this->assertEquals(1, $parts['h']);
        $this->assertEquals(1, $parts['min']);
        $this->assertEquals(1.5, $parts['s']);
    }

    /**
     * Test toParts() with minutes as smallest unit.
     */
    public function testToPartsMinutes(): void
    {
        $time = new Time(3661, 's');
        $parts = $time->toParts('min');

        $this->assertEquals(1, $parts['sign']);
        $this->assertEquals(0, $parts['y']);
        $this->assertEquals(0, $parts['mo']);
        $this->assertEquals(0, $parts['d']);
        $this->assertEquals(0, $parts['w']);
        $this->assertEquals(1, $parts['h']);
        $this->assertEqualsWithDelta(1.0167, $parts['min'], 0.001);
    }

    /**
     * Test toParts() with hours as smallest unit.
     */
    public function testToPartsHours(): void
    {
        $time = new Time(90000, 's');  // 25 hours
        $parts = $time->toParts('h');

        $this->assertEquals(1, $parts['sign']);
        $this->assertEquals(0, $parts['y']);
        $this->assertEquals(0, $parts['mo']);
        $this->assertEquals(1, $parts['d']);
        $this->assertEquals(0, $parts['w']);
        $this->assertEquals(1, $parts['h']);
    }

    /**
     * Test toParts() with days as smallest unit.
     */
    public function testToPartsDays(): void
    {
        $time = new Time(100000, 's');  // ~1.157 days
        $parts = $time->toParts('d');

        $this->assertEquals(1, $parts['sign']);
        $this->assertEquals(0, $parts['y']);
        $this->assertEquals(0, $parts['mo']);
        $this->assertEquals(0, $parts['w']);
        $this->assertEqualsWithDelta(1.157, $parts['d'], 0.001);
    }

    /**
     * Test toParts() with weeks as smallest unit.
     */
    public function testToPartsWeeks(): void
    {
        $time = new Time(1209600, 's');  // 14 days = 2 weeks
        $parts = $time->toParts('w');

        $this->assertEquals(1, $parts['sign']);
        $this->assertEquals(0, $parts['y']);
        $this->assertEquals(0, $parts['mo']);
        $this->assertEquals(2, $parts['w']);
    }

    /**
     * Test toParts() with months as smallest unit.
     */
    public function testToPartsMonths(): void
    {
        $time = new Time(5259487.5, 's');  // ~2 months
        $parts = $time->toParts('mo');

        $this->assertEquals(1, $parts['sign']);
        $this->assertEquals(0, $parts['y']);
        $this->assertEqualsWithDelta(2, $parts['mo'], 0.01);
    }

    /**
     * Test toParts() with years as smallest unit.
     */
    public function testToPartsYears(): void
    {
        $time = new Time(63113904, 's');  // ~2 years
        $parts = $time->toParts('y');

        $this->assertEquals(1, $parts['sign']);
        $this->assertEqualsWithDelta(2, $parts['y'], 0.01);
    }

    /**
     * Test toParts() with negative time.
     */
    public function testToPartsNegative(): void
    {
        $time = new Time(-3661, 's');
        $parts = $time->toParts('s');

        $this->assertEquals(-1, $parts['sign']);
        $this->assertEquals(1, $parts['h']);
        $this->assertEquals(1, $parts['min']);
        $this->assertEquals(1, $parts['s']);
    }

    /**
     * Test toParts() with zero time.
     */
    public function testToPartsZero(): void
    {
        $time = new Time(0, 's');
        $parts = $time->toParts('s');

        $this->assertEquals(0, $parts['sign']);
        $this->assertEquals(0, $parts['y']);
        $this->assertEquals(0, $parts['mo']);
        $this->assertEquals(0, $parts['d']);
        $this->assertEquals(0, $parts['w']);
        $this->assertEquals(0, $parts['h']);
        $this->assertEquals(0, $parts['min']);
        $this->assertEquals(0, $parts['s']);
    }

    /**
     * Test toParts() with precision parameter.
     */
    public function testToPartsWithPrecision(): void
    {
        $time = new Time(3661.56789, 's');
        $parts = $time->toParts('s', 2);

        $this->assertEquals(1, $parts['h']);
        $this->assertEquals(1, $parts['min']);
        $this->assertEquals(1.57, $parts['s']);
    }

    /**
     * Test toParts() with invalid smallest unit throws ValueError.
     */
    public function testToPartsInvalidUnit(): void
    {
        $time = new Time(100, 's');
        $this->expectException(ValueError::class);
        $time->toParts('invalid');
    }

    /**
     * Test toParts() with complex time value.
     */
    public function testToPartsComplex(): void
    {
        // 1 year + 2 months + 3 days + 4 hours + 5 minutes + 6.789 seconds
        $seconds = 31557600 + (2 * 2629743.75) + (3 * 86400) + (4 * 3600) + (5 * 60) + 6.789;
        $time = new Time($seconds, 's');
        $parts = $time->toParts('s', 3);

        $this->assertEquals(1, $parts['sign']);
        $this->assertEquals(1, $parts['y']);
        $this->assertEquals(2, $parts['mo']);
        $this->assertGreaterThan(0, $parts['d']);
        $this->assertGreaterThan(0, $parts['h']);
        $this->assertGreaterThan(0, $parts['min']);
        $this->assertGreaterThan(0, $parts['s']);
    }

    /**
     * Test toParts() carry from seconds to minutes.
     *
     * When precision rounds seconds to 60, it should carry to minutes.
     */
    public function testToPartsCarrySecondsToMinutes(): void
    {
        // 59.999 seconds with precision 0 should round to 60, then carry to 1 minute
        $time = new Time(59.999, 's');
        $parts = $time->toParts('s', 0);

        $this->assertEquals(1, $parts['sign']);
        $this->assertEquals(0, $parts['y']);
        $this->assertEquals(0, $parts['mo']);
        $this->assertEquals(0, $parts['d']);
        $this->assertEquals(0, $parts['w']);
        $this->assertEquals(0, $parts['h']);
        $this->assertEquals(1, $parts['min']);
        $this->assertEquals(0, $parts['s']);
    }

    /**
     * Test toParts() carry from minutes to hours.
     *
     * When precision rounds minutes to 60, it should carry to hours.
     */
    public function testToPartsCarryMinutesToHours(): void
    {
        // 59 minutes 59.999 seconds with precision on minutes
        $time = new Time(3599.999, 's');
        $parts = $time->toParts('min', 0);

        $this->assertEquals(1, $parts['sign']);
        $this->assertEquals(0, $parts['y']);
        $this->assertEquals(0, $parts['mo']);
        $this->assertEquals(0, $parts['d']);
        $this->assertEquals(0, $parts['w']);
        $this->assertEquals(1, $parts['h']);
        $this->assertEquals(0, $parts['min']);
    }

    /**
     * Test toParts() carry from hours to days.
     *
     * When precision rounds hours to 24, it should carry to days.
     */
    public function testToPartsCarryHoursToDays(): void
    {
        // 23 hours 59 minutes 59.999 seconds with precision on hours
        $time = new Time(86399.999, 's');
        $parts = $time->toParts('h', 0);

        $this->assertEquals(1, $parts['sign']);
        $this->assertEquals(0, $parts['y']);
        $this->assertEquals(0, $parts['mo']);
        $this->assertEquals(0, $parts['w']);
        $this->assertEquals(1, $parts['d']);
        $this->assertEquals(0, $parts['h']);
    }

    /**
     * Test toParts() carry cascades through multiple units.
     *
     * When rounding causes a carry, it should cascade all the way up.
     */
    public function testToPartsCarryCascade(): void
    {
        // 1 hour, 59 minutes, 59.999 seconds with precision 0 on seconds
        // Should cascade: 60s -> 1min, then 60min -> 1hour
        $time = new Time(7199.999, 's');
        $parts = $time->toParts('s', 0);

        $this->assertEquals(1, $parts['sign']);
        $this->assertEquals(0, $parts['y']);
        $this->assertEquals(0, $parts['mo']);
        $this->assertEquals(0, $parts['d']);
        $this->assertEquals(0, $parts['w']);
        $this->assertEquals(2, $parts['h']);
        $this->assertEquals(0, $parts['min']);
        $this->assertEquals(0, $parts['s']);
    }

    /**
     * Test toParts() no carry when precision is null.
     *
     * Without precision, no carry should occur even with values like 59.999.
     */
    public function testToPartsNoCarryWithoutPrecision(): void
    {
        $time = new Time(59.999, 's');
        $parts = $time->toParts('s', null);

        $this->assertEquals(1, $parts['sign']);
        $this->assertEquals(0, $parts['min']);
        $this->assertEquals(59.999, $parts['s']);
    }

    /**
     * Test toParts() carry with larger units.
     *
     * Test that carry works with days to weeks conversion.
     */
    public function testToPartsCarryDaysToWeeks(): void
    {
        // 6 days, 23 hours, 59 minutes, 59.999 seconds with precision on days
        // Should round to 7 days and carry to 1 week
        $time = new Time(604799.999, 's');
        $parts = $time->toParts('d', 0);

        $this->assertEquals(1, $parts['sign']);
        $this->assertEquals(0, $parts['y']);
        $this->assertEquals(0, $parts['mo']);
        $this->assertEquals(1, $parts['w']);
        $this->assertEquals(0, $parts['d']);
    }

    /**
     * Test toDateIntervalSpecifier() with seconds.
     */
    public function testToDateIntervalSpecifierSeconds(): void
    {
        $time = new Time(3661, 's');
        $spec = $time->toDateIntervalSpecifier('s');

        $this->assertEquals('PT1H1M1S', $spec);
    }

    /**
     * Test toDateIntervalSpecifier() with minutes.
     */
    public function testToDateIntervalSpecifierMinutes(): void
    {
        $time = new Time(3660, 's');
        $spec = $time->toDateIntervalSpecifier('min');

        $this->assertEquals('PT1H1M', $spec);
    }

    /**
     * Test toDateIntervalSpecifier() with hours.
     */
    public function testToDateIntervalSpecifierHours(): void
    {
        $time = new Time(90000, 's');  // 25 hours
        $spec = $time->toDateIntervalSpecifier('h');

        $this->assertEquals('P1DT1H', $spec);
    }

    /**
     * Test toDateIntervalSpecifier() with days.
     */
    public function testToDateIntervalSpecifierDays(): void
    {
        $time = new Time(172800, 's');  // 2 days
        $spec = $time->toDateIntervalSpecifier('d');

        $this->assertEquals('P2D', $spec);
    }

    /**
     * Test toDateIntervalSpecifier() with weeks.
     */
    public function testToDateIntervalSpecifierWeeks(): void
    {
        $time = new Time(1209600, 's');  // 2 weeks
        $spec = $time->toDateIntervalSpecifier('w');

        $this->assertEquals('P2W', $spec);
    }

    /**
     * Test toDateIntervalSpecifier() with complex value.
     */
    public function testToDateIntervalSpecifierComplex(): void
    {
        // 1 year + 2 months + 1 week + 3 days + 4 hours + 5 minutes + 6 seconds
        $seconds = 31556952 + (2 * 2629746) + (21 * 86400) + (4 * 86400) + (5 * 3600) + (6 * 60) + 7;
        $time = new Time($seconds, 's');
        $spec = $time->toDateIntervalSpecifier('s');

        // Should have all components
        $this->assertStringContainsString('1Y', $spec);
        $this->assertStringContainsString('2M', $spec);
        $this->assertStringContainsString('3W', $spec);
        $this->assertStringContainsString('4D', $spec);
        $this->assertStringContainsString('T', $spec);  // Time separator
        $this->assertStringContainsString('5H', $spec);
        $this->assertStringContainsString('6M', $spec);
        $this->assertStringContainsString('7S', $spec);
    }

    /**
     * Test toDateIntervalSpecifier() with zero time.
     */
    public function testToDateIntervalSpecifierZero(): void
    {
        $time = new Time(0, 's');
        $spec = $time->toDateIntervalSpecifier('s');

        $this->assertEquals('P0D', $spec);
    }

    /**
     * Test formatParts() with default parameters.
     */
    public function testFormatPartsDefault(): void
    {
        $time = new Time(3661.5, 's');
        $formatted = $time->formatParts();

        $this->assertEquals('1h 1min 1.5s', $formatted);
    }

    /**
     * Test formatParts() with precision.
     */
    public function testFormatPartsWithPrecision(): void
    {
        $time = new Time(3661.56789, 's');
        $formatted = $time->formatParts('s', 2);

        $this->assertEquals('1h 1min 1.57s', $formatted);
    }

    /**
     * Test formatParts() with zero precision.
     */
    public function testFormatPartsZeroPrecision(): void
    {
        $time = new Time(3661.9, 's');
        $formatted = $time->formatParts('s', 0);

        $this->assertEquals('1h 1min 2s', $formatted);
    }

    /**
     * Test formatParts() with minutes as smallest unit.
     */
    public function testFormatPartsMinutes(): void
    {
        $time = new Time(3660, 's');
        $formatted = $time->formatParts('min');

        $this->assertEquals('1h 1min', $formatted);
    }

    /**
     * Test formatParts() with hours as smallest unit.
     */
    public function testFormatPartsHours(): void
    {
        $time = new Time(7200, 's');
        $formatted = $time->formatParts('h');

        $this->assertEquals('2h', $formatted);
    }

    /**
     * Test formatParts() skips zero components.
     */
    public function testFormatPartsSkipsZeros(): void
    {
        $time = new Time(3600, 's');  // Exactly 1 hour, no minutes or seconds
        $formatted = $time->formatParts('s');

        $this->assertEquals('1h', $formatted);
        $this->assertStringNotContainsString('min', $formatted);
        $this->assertStringNotContainsString('0s', $formatted);
    }

    /**
     * Test formatParts() with negative time.
     */
    public function testFormatPartsNegative(): void
    {
        $time = new Time(-3661, 's');
        $formatted = $time->formatParts('s');

        $this->assertEquals('-(1h 1min 1s)', $formatted);
    }

    /**
     * Test formatParts() with zero time.
     */
    public function testFormatPartsZero(): void
    {
        $time = new Time(0, 's');
        $formatted = $time->formatParts('s');

        $this->assertEquals('0s', $formatted);
    }

    /**
     * Test formatParts() with complex time.
     */
    public function testFormatPartsComplex(): void
    {
        // 2 days, 3 hours, 4 minutes, 5.678 seconds
        $seconds = (2 * 86400) + (3 * 3600) + (4 * 60) + 5.678;
        $time = new Time($seconds, 's');
        $formatted = $time->formatParts('s', 3);

        $this->assertEquals('2d 3h 4min 5.678s', $formatted);
    }

    /**
     * Test formatParts() with only largest components.
     */
    public function testFormatPartsLargeComponents(): void
    {
        $time = new Time(31556952, 's');  // ~1 year
        $formatted = $time->formatParts('d');

        $this->assertStringContainsString('y', $formatted);
        $this->assertStringNotContainsString('h', $formatted);
        $this->assertStringNotContainsString('min', $formatted);
        $this->assertStringNotContainsString('s', $formatted);
    }

    /**
     * Test formatParts() with weeks.
     */
    public function testFormatPartsWithWeeks(): void
    {
        $time = new Time(1209600, 's');  // 2 weeks
        $formatted = $time->formatParts('w');

        $this->assertEquals('2w', $formatted);
    }

    /**
     * Test fromDateInterval() with simple interval.
     */
    public function testFromDateIntervalSimple(): void
    {
        $interval = new DateInterval('PT1H30M45S');
        $time = Time::fromDateInterval($interval);

        // 1 hour + 30 minutes + 45 seconds = 5445 seconds
        $this->assertEqualsWithDelta(5445, $time->value, 0.001);
        $this->assertEquals('s', $time->unit);
    }

    /**
     * Test fromDateInterval() with days.
     */
    public function testFromDateIntervalWithDays(): void
    {
        $interval = new DateInterval('P2DT3H');
        $time = Time::fromDateInterval($interval);

        // 2 days + 3 hours = (2 * 86400) + (3 * 3600) = 183600 seconds
        $this->assertEqualsWithDelta(183600, $time->value, 0.001);
    }

    /**
     * Test fromDateInterval() with weeks.
     */
    public function testFromDateIntervalWithWeeks(): void
    {
        $interval = new DateInterval('P2W');
        $time = Time::fromDateInterval($interval);

        // 2 weeks = 14 days = 1209600 seconds
        $this->assertEqualsWithDelta(1209600, $time->value, 0.001);
    }

    /**
     * Test fromDateInterval() with months.
     */
    public function testFromDateIntervalWithMonths(): void
    {
        $interval = new DateInterval('P2M');
        $time = Time::fromDateInterval($interval);

        // 2 months = 2 * 2629746 seconds (average)
        $this->assertEqualsWithDelta(5259492, $time->value, 1);
    }

    /**
     * Test fromDateInterval() with years.
     */
    public function testFromDateIntervalWithYears(): void
    {
        $interval = new DateInterval('P1Y');
        $time = Time::fromDateInterval($interval);

        // 1 year = 31556952 seconds (365.2425 days)
        $this->assertEqualsWithDelta(31556952, $time->value, 1);
    }

    /**
     * Test fromDateInterval() with complex interval.
     */
    public function testFromDateIntervalComplex(): void
    {
        $interval = new DateInterval('P1Y2M3DT4H5M6S');
        $time = Time::fromDateInterval($interval);

        // Should be positive and greater than 1 year
        $this->assertGreaterThan(31556952, $time->value);
        $this->assertEquals('s', $time->unit);
    }

    /**
     * Test fromDateInterval() with microseconds.
     */
    public function testFromDateIntervalWithMicroseconds(): void
    {
        $interval = DateInterval::createFromDateString('1 second + 500000 microseconds');
        $time = Time::fromDateInterval($interval);

        // 1 second + 0.5 seconds = 1.5 seconds
        $this->assertEqualsWithDelta(1.5, $time->value, 0.001);
    }

    /**
     * Test fromDateInterval() with inverted (negative) interval.
     */
    public function testFromDateIntervalNegative(): void
    {
        $interval = new DateInterval('PT1H');
        $interval->invert = 1;
        $time = Time::fromDateInterval($interval);

        // Should be negative
        $this->assertEquals(-3600, $time->value);
    }

    /**
     * Test fromDateInterval() with zero interval.
     */
    public function testFromDateIntervalZero(): void
    {
        $interval = new DateInterval('PT0S');
        $time = Time::fromDateInterval($interval);

        $this->assertEquals(0, $time->value);
    }

    /**
     * Test toDateInterval() with seconds.
     */
    public function testToDateIntervalSeconds(): void
    {
        $time = new Time(3661, 's');
        $interval = $time->toDateInterval('s');

        $this->assertEquals(1, $interval->h);
        $this->assertEquals(1, $interval->i);
        $this->assertEquals(1, $interval->s);
    }

    /**
     * Test toDateInterval() with minutes.
     */
    public function testToDateIntervalMinutes(): void
    {
        $time = new Time(3660, 's');
        $interval = $time->toDateInterval('min');

        $this->assertEquals(1, $interval->h);
        $this->assertEquals(1, $interval->i);
    }

    /**
     * Test toDateInterval() with hours.
     */
    public function testToDateIntervalHours(): void
    {
        $time = new Time(90000, 's');  // 25 hours
        $interval = $time->toDateInterval('h');

        $this->assertEquals(1, $interval->d);
        $this->assertEquals(1, $interval->h);
    }

    /**
     * Test toDateInterval() with days.
     */
    public function testToDateIntervalDays(): void
    {
        $time = new Time(172800, 's');  // 2 days
        $interval = $time->toDateInterval('d');

        $this->assertEquals(2, $interval->d);
    }

    /**
     * Test toDateInterval() with weeks.
     */
    public function testToDateIntervalWeeks(): void
    {
        $time = new Time(1209600, 's');  // 2 weeks
        $interval = $time->toDateInterval('w');

        // DateInterval doesn't have a weeks property, it converts to days.
        $this->assertEquals(14, $interval->d);
    }

    /**
     * Test toDateInterval() roundtrip conversion.
     */
    public function testToDateIntervalRoundtrip(): void
    {
        $originalTime = new Time(5445, 's');  // 1h 30m 45s
        $interval = $originalTime->toDateInterval('s');
        $convertedTime = Time::fromDateInterval($interval);

        $this->assertEqualsWithDelta($originalTime->value, $convertedTime->value, 1);
    }

    /**
     * Test toDateInterval() with zero time.
     */
    public function testToDateIntervalZero(): void
    {
        $time = new Time(0, 's');
        $interval = $time->toDateInterval('s');

        // Should create a valid DateInterval representing zero time
        $this->assertEquals(0, $interval->y);
        $this->assertEquals(0, $interval->m);
        $this->assertEquals(0, $interval->d);
    }
}
