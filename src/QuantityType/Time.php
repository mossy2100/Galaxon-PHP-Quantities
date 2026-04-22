<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use DateInterval;
use DateMalformedIntervalStringException;
use Galaxon\Core\Floats;
use Galaxon\Core\Numbers;
use Galaxon\Quantities\Internal\UnitSystem;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Services\PrefixService;
use Override;

/**
 * Represents time quantities.
 */
class Time extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for time.
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'second'  => [
                'asciiSymbol' => 's',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [UnitSystem::Si],
            ],
            'minute'  => [
                'asciiSymbol' => 'min',
                'systems'     => [UnitSystem::SiAccepted],
            ],
            'hour'    => [
                'asciiSymbol' => 'h',
                'systems'     => [UnitSystem::SiAccepted],
            ],
            'day'     => [
                'asciiSymbol' => 'd',
                'systems'     => [UnitSystem::SiAccepted],
            ],
            'week'    => [
                'asciiSymbol' => 'w',
                'systems'     => [UnitSystem::Common],
            ],
            'month'   => [
                'asciiSymbol' => 'mo',
                'systems'     => [UnitSystem::Common],
            ],
            'year'    => [
                'asciiSymbol' => 'y',
                'systems'     => [UnitSystem::Common],
            ],
            'century' => [
                'asciiSymbol' => 'c',
                'systems'     => [UnitSystem::Common],
            ],
        ];
    }

    /**
     * Conversion factors for time units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            ['min', 's', 60],
            ['h', 'min', 60],
            ['d', 'h', 24],
            ['w', 'd', 7],
            ['y', 'mo', 12],
            ['y', 'd', 365.2425],
            ['c', 'y', 100],
        ];
    }

    /**
     * Default part units for time decomposition: years, months, weeks, days, hours, minutes, seconds.
     */
    #[Override]
    public static function getPartUnitSymbols(): ?array
    {
        return ['y', 'mo', 'w', 'd', 'h', 'min', 's'];
    }

    // endregion

    // region DateInterval methods

    /**
     * Create a Time from a PHP DateInterval object.
     *
     * Uses naive conversion based on average values:
     * - 1 year = 12 months = 365.2425 days
     * - 1 week = 7 days
     *
     * @param DateInterval $interval The DateInterval to convert.
     * @return self A new Time instance.
     */
    public static function fromDateInterval(DateInterval $interval): self
    {
        // Convert the DateInterval to an array of parts and create a Time instance.
        return self::fromParts([
            'sign' => $interval->invert ? -1 : 1,
            'y'    => $interval->y,
            'mo'   => $interval->m,
            'd'    => $interval->d,
            'h'    => $interval->h,
            'min'  => $interval->i,
            's'    => $interval->s + $interval->f,
        ]);
    }

    /**
     * Convert time to a DateInterval specification string.
     *
     * The string will represent the absolute value of the time, rounded off to the nearest second.
     *
     * Format: P[y]Y[m]M[w]W[d]DT[h]H[i]M[s]S
     *
     * @return string A DateInterval specification string.
     * @see https://www.php.net/manual/en/dateinterval.construct.php
     */
    public function toDateIntervalSpecifier(): string
    {
        // Get the time parts. Set precision to 0 because DateInterval requires integer parts.
        // All the unit parts will be non-negative.
        // If the value is negative, there will be a sign key with value -1, which will be ignored for this method.
        // Note, however, the sign is included by the toDateInterval() method.
        $parts = $this->toParts(precision: 0);

        // Prep
        $spec = 'P';
        $timeSeparatorAdded = false;

        // Build the specification string.
        foreach ($parts as $symbol => $value) {
            // Skip sign and 0 values.
            if ($symbol === 'sign' || Numbers::isZero($value)) {
                continue;
            }

            // Add the time separator 'T' before any time parts.
            if (in_array($symbol, ['h', 'min', 's'], true) && !$timeSeparatorAdded) {
                $spec .= 'T';
                $timeSeparatorAdded = true;
            }

            $spec .= (int)$value . strtoupper($symbol[0]);
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
     * @return DateInterval A new DateInterval object.
     * @throws DateMalformedIntervalStringException If the DateInterval specification string is invalid.
     */
    public function toDateInterval(): DateInterval
    {
        // Get the absolute value to avoid sign issues.
        $abs = $this->abs();

        // Get the specifier string. Use floor() to omit microseconds, which must be added separately.
        $spec = $abs->to('s')->floor()->toDateIntervalSpecifier();

        // Construct the DateInterval.
        $dateInterval = new DateInterval($spec);

        // Add microseconds from the fractional part of seconds.
        $parts = $abs->toParts();
        $seconds = $parts['s'] ?? 0;
        $frac = Floats::frac($seconds);
        if ($frac > 0) {
            $dateInterval->f = $frac;
        }

        // If the time value is negative, invert the DateInterval.
        if ($this->value < 0) {
            $dateInterval->invert = 1;
        }

        return $dateInterval;
    }

    // endregion
}
