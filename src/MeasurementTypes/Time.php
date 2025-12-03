<?php

declare(strict_types=1);

namespace Galaxon\Units\MeasurementTypes;

use DateInterval;
use Galaxon\Core\Arrays;
use Galaxon\Core\Numbers;
use Galaxon\Units\Measurement;
use Override;
use ValueError;

class Time extends Measurement
{
    private const array PARTS_UNITS = ['y', 'mo', 'w', 'd', 'h', 'min', 's'];

    // region Factory methods

    /**
     * Create a Time from a PHP DateInterval object.
     *
     * Uses naive conversion based on average values:
     * - 1 year = 365.2425 days
     * - 1 month = 30.436875 days (365.2425 / 12)
     * - 1 week = 7 days
     *
     * @param DateInterval $interval The DateInterval to convert.
     * @return self A new Time instance.
     */
    public static function fromDateInterval(DateInterval $interval): self
    {
        // Convert all components to seconds using our conversion system.
        $converter = static::getUnitConverter();

        $seconds = 0.0;

        // Years to seconds.
        if ($interval->y > 0) {
            $seconds += $converter->convert($interval->y, 'y', 's');
        }

        // Months to seconds.
        if ($interval->m > 0) {
            $seconds += $converter->convert($interval->m, 'mo', 's');
        }

        // Days to seconds (DateInterval stores total days, not weeks).
        if ($interval->d > 0) {
            $seconds += $converter->convert($interval->d, 'd', 's');
        }

        // Hours to seconds.
        if ($interval->h > 0) {
            $seconds += $converter->convert($interval->h, 'h', 's');
        }

        // Minutes to seconds.
        if ($interval->i > 0) {
            $seconds += $converter->convert($interval->i, 'min', 's');
        }

        // Seconds and microseconds (f is a float from 0-1 representing fraction of a second).
        $seconds += $interval->s + $interval->f;

        // Handle negative intervals.
        if ($interval->invert === 1) {
            $seconds = -$seconds;
        }

        return new self($seconds, 's');
    }

    // endregion

    // region Instance methods

    /**
     * Check smallest unit argument is valid.
     *
     * @param string $smallestUnit
     * @return void
     * @throws ValueError
     */
    private static function validateSmallestUnit(string $smallestUnit): void
    {
        if (!in_array($smallestUnit, self::PARTS_UNITS, true)) {
            throw new ValueError('Invalid smallest unit specified. Must be one of: ' .
                                 implode(', ', Arrays::quoteValues(self::PARTS_UNITS)));
        }
    }

    private static function validatePrecision(?int $precision): void
    {
        if ($precision !== null && $precision < 0) {
            throw new ValueError('Invalid precision specified. Must be null or a non-negative integer.');
        }
    }

    /**
     * Convert time to component parts.
     *
     * Returns an array with components from years down to the smallest unit.
     * Only the last component may have a fractional part; others are integers.
     *
     * Uses naive conversion, which assumes years and months have a constant, average length:
     * - 1 year = 365.2425 days
     * - 1 month = 30.436875 days
     * - 1 week = 7 days
     *
     * @param string $smallestUnit The smallest unit to include (default 's').
     * @param ?int $precision The number of decimal places for rounding the smallest unit, or null for no rounding.
     * @return array<int|float> Array of time components.
     * @throws ValueError If either argument is invalid.
     */
    public function toParts(string $smallestUnit = 's', ?int $precision = null): array
    {
        // Validate arguments.
        self::validateSmallestUnit($smallestUnit);
        self::validatePrecision($precision);

        // Prep.
        $converter = static::getUnitConverter();
        $sign = Numbers::sign($this->value);
        $parts = ['sign' => $sign];
        $smallestUnitIndex = (int)array_search($smallestUnit, self::PARTS_UNITS, true);

        // Get initial remainder in the smallest unit.
        $rem = abs($this->to($smallestUnit)->value);

        // Get the parts.
        for ($i = 0; $i <= $smallestUnitIndex; $i++) {
            $curUnit = self::PARTS_UNITS[$i];

            // Have we reached the smallest unit?
            if ($curUnit === $smallestUnit) {
                if ($precision === null) {
                    // No rounding.
                    $parts[$curUnit] = $rem;
                } elseif ($precision === 0) {
                    // Return an integer.
                    $parts[$curUnit] = (int)round($rem, $precision);
                } else {
                    // Round off.
                    $parts[$curUnit] = round($rem, $precision);
                }
                break;
            }

            // Get the number of the current units.
            $factor = $converter->convert(1, $curUnit, $smallestUnit);
            $wholeNumCurUnit = floor($rem / $factor);
            $parts[$curUnit] = (int)$wholeNumCurUnit;
            $rem = $rem - $wholeNumCurUnit * $factor;
        }

        // Carry in reverse order.
        if ($precision !== null) {
            for ($i = $smallestUnitIndex; $i >= 1; $i--) {
                $curUnit = self::PARTS_UNITS[$i];
                $prevUnit = self::PARTS_UNITS[$i - 1];
                if ($parts[$curUnit] >= $converter->convert(1, $prevUnit, $curUnit)) {
                    $parts[$curUnit] = 0;
                    $parts[$prevUnit]++;
                }
            }
        }

        return $parts;
    }

    /**
     * Convert time to a DateInterval specification string.
     *
     * Format: P[y]Y[m]M[w]W[d]DT[h]H[i]M[s]S
     *
     * @param string $smallestUnit The smallest unit to include (default 's').
     * @return string A DateInterval specification string.
     * @throws ValueError If the smallest unit argument is invalid.
     */
    public function toDateIntervalSpecifier(string $smallestUnit = 's'): string
    {
        // Validate argument.
        self::validateSmallestUnit($smallestUnit);

        // Prep.
        $smallestUnitIndex = (int)array_search($smallestUnit, self::PARTS_UNITS, true);
        $parts = $this->toParts($smallestUnit, 0);
        $spec = 'P';
        $labels = ['Y', 'M', 'W', 'D', 'H', 'M', 'S'];
        $timeSeparatorAdded = false;

        // Build the specification string.
        for ($i = 0; $i <= $smallestUnitIndex; $i++) {
            $unit = self::PARTS_UNITS[$i];
            $value = $parts[$unit] ?? 0;

            // Add time separator before hours.
            if ($unit === 'h' && !$timeSeparatorAdded) {
                $spec .= 'T';
                $timeSeparatorAdded = true;
            }

            // Add the specifier part if it isn't 0.
            if ($parts[$unit] !== 0) {
                $spec .= $value . $labels[$i];
            }
        }

        // If nothing was added, return P0D.
        if ($spec === 'P' || $spec === 'PT') {
            return 'P0D';
        }

        return $spec;
    }

    /**
     * Convert time to a PHP DateInterval object.
     *
     * @param string $smallestUnit The smallest unit to include (default 's').
     * @return DateInterval A new DateInterval object.
     * @throws ValueError If the smallest unit argument is invalid.
     */
    public function toDateInterval(string $smallestUnit = 's'): DateInterval
    {
        // Validate argument.
        self::validateSmallestUnit($smallestUnit);

        // Get the specifier string.
        $spec = $this->toDateIntervalSpecifier($smallestUnit);

        // Construct the DateInterval.
        $dateInterval = new DateInterval($spec);

        // If the time value is negative, invert the DateInterval.
        if ($this->value < 0) {
            $dateInterval->invert = 1;
        }

        return $dateInterval;
    }

    /**
     * Format time as component parts with units.
     *
     * Returns a string like "1y 3mo 2w 4d 12h 34min 56.789s".
     * Units other than the smallest unit are shown as integers.
     *
     * @param string $smallestUnit The smallest unit to include (default 's').
     * @param ?int $precision The number of decimal places for rounding the smallest unit, or null for no rounding.
     * @return string Formatted time string.
     * @throws ValueError If either argument is invalid.
     */
    public function formatParts(string $smallestUnit = 's', ?int $precision = null): string
    {
        // Validate arguments.
        self::validateSmallestUnit($smallestUnit);
        self::validatePrecision($precision);

        // Get the parts.
        $parts = $this->toParts($smallestUnit, $precision);

        // If the value is zero, just return '0' with the smallest unit.
        if ($parts['sign'] === 0) {
            return '0' . $smallestUnit;
        }

        // Prep.
        $smallestUnitIndex = (int)array_search($smallestUnit, self::PARTS_UNITS, true);
        $result = [];

        // Generate string as parts.
        for ($i = 0; $i <= $smallestUnitIndex; $i++) {
            $unit = self::PARTS_UNITS[$i];
            $value = $parts[$unit] ?? 0;

            // Skip zero components.
            if (Numbers::equal($value, 0)) {
                continue;
            }

            // Format the value.
            $result[] = $value . $unit;
        }

        // Return full string. If sign is negative, wrap units in brackets.
        $neg = $parts['sign'] === -1;
        return ($neg ? '-(' : '') . implode(' ', $result) . ($neg ? ')' : '');
    }

    // endregion

    // region Measurement methods

    /**
     * Get the units for Time measurements.
     *
     * @return array<string, int> Array of units with allowed prefixes flags.
     */
    #[Override]
    public static function getBaseUnits(): array
    {
        return [
            's'   => self::PREFIX_SET_METRIC,  // second
            'min' => 0,  // minute
            'h'   => 0,  // hour
            'd'   => 0,  // day
            'w'   => 0,  // week
            'mo'  => 0,  // month
            'y'   => 0,  // year
            'c'   => 0,  // century
        ];
    }

    /**
     * Get the conversions for Time measurements.
     *
     * @return array<array<string, string, int|float>>
     */
    #[Override]
    public static function getConversions(): array
    {
        return [
            ['min', 's', 60],
            ['h', 'min', 60],
            ['d', 'h', 24],
            ['w', 'd', 7],
            ['y', 'mo', 12],
            ['y', 'd', 365.2425],
            ['c', 'y', 100]
        ];
    }

    // endregion
}
