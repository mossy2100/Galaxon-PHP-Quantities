<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use DateInterval;
use DomainException;
use Galaxon\Core\Numbers;
use Galaxon\Quantities\Helpers\PrefixUtils;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\System;
use Override;

class Time extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for time.
     *
     * @return array<string, array{
     *     asciiSymbol: string,
     *     unicodeSymbol?: string,
     *     prefixGroup?: int,
     *     systems: list<System>,
     *     expansionUnitSymbol?: string,
     *     expansionValue?: float
     * }>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'second' => [
                'asciiSymbol' => 's',
                'prefixGroup' => PrefixUtils::GROUP_CODE_METRIC,
                'systems'     => [System::SI],
            ],
            'minute' => [
                'asciiSymbol' => 'min',
                'systems'     => [System::SIAccepted],
            ],
            'hour'   => [
                'asciiSymbol' => 'h',
                'systems'     => [System::SIAccepted],
            ],
            'day'    => [
                'asciiSymbol' => 'd',
                'systems'     => [System::SIAccepted],
            ],
            'week'   => [
                'asciiSymbol' => 'w',
                'systems'     => [System::Common],
            ],
            'month'  => [
                'asciiSymbol' => 'mo',
                'systems'     => [System::Common],
            ],
            'year'   => [
                'asciiSymbol' => 'y',
                'systems'     => [System::Common],
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
        ];
    }

    /**
     * Configuration for parts-related methods.
     *
     * @return array{from: ?string, to: list<string>}
     */
    #[Override]
    public static function getPartsConfig(): array
    {
        return [
            'from' => 's',
            'to'   => ['y', 'mo', 'w', 'd', 'h', 'min', 's'],
        ];
    }

    // endregion

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
     * @return parent A new Time instance.
     */
    public static function fromDateInterval(DateInterval $interval): parent
    {
        // Convert all the parts of the DateInterval to seconds and sum.
        $seconds = self::convert($interval->y, 'y', 's') +
                   self::convert($interval->m, 'mo', 's') +
                   self::convert($interval->d, 'd', 's') +
                   self::convert($interval->h, 'h', 's') +
                   self::convert($interval->i, 'min', 's') +
                   $interval->s + $interval->f;

        // Handle negative intervals.
        if ($interval->invert === 1) {
            $seconds = -$seconds;
        }

        return self::create($seconds, 's');
    }

    // endregion

    // region Conversion methods

    /**
     * Convert time to a DateInterval specification string.
     *
     * Format: P[y]Y[m]M[w]W[d]DT[h]H[i]M[s]S
     *
     * @param string $largestUnitSymbol The largest unit to include (default 'y').
     * @param string $smallestUnitSymbol The smallest unit to include (default 's').
     * @return string A DateInterval specification string.
     * @throws DomainException If the largest or smallest unit argument is invalid.
     */
    public function toDateIntervalSpecifier(string $largestUnitSymbol = 'y', string $smallestUnitSymbol = 's'): string
    {
        // Validate the part unit symbols.
        $partUnitSymbols = static::getPartsConfig()['to'];
        static::validatePartUnitSymbols($partUnitSymbols);

        // Get the default or validate the largest and smallest unit symbols.
        [$largestUnitSymbol, $largestUnitIndex, $smallestUnitSymbol, $smallestUnitIndex] =
            static::validateLargestAndSmallest($partUnitSymbols, $largestUnitSymbol, $smallestUnitSymbol);

        // Get the time parts. Set precision to 0 because DateInterval requires integer parts.
        $parts = $this->toParts($largestUnitSymbol, $smallestUnitSymbol, 0);

        // Prep
        $partUnitSymbols = static::getPartsConfig()['to'];
        $spec = 'P';
        $timeSeparatorAdded = false;

        // Build the specification string.
        for ($i = $largestUnitIndex; $i <= $smallestUnitIndex; $i++) {
            $symbol = $partUnitSymbols[$i];
            $value = $parts[$symbol] ?? 0;

            // Add time separator before any time parts.
            if (in_array($symbol, ['h', 'min', 's'], true) && !$timeSeparatorAdded) {
                $spec .= 'T';
                $timeSeparatorAdded = true;
            }

            // Add the specifier part if it isn't 0.
            if (!Numbers::equal($parts[$symbol], 0.0)) {
                $spec .= (int)$value . strtoupper($symbol[0]);
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
     * @param string $largestUnitSymbol The largest unit to include (default 'y').
     * @param string $smallestUnitSymbol The smallest unit to include (default 's').
     * @return DateInterval A new DateInterval object.
     * @throws DomainException If the largest or smallest unit argument is invalid.
     */
    public function toDateInterval(string $largestUnitSymbol = 'y', string $smallestUnitSymbol = 's'): DateInterval
    {
        // Get the specifier string.
        $spec = $this->toDateIntervalSpecifier($largestUnitSymbol, $smallestUnitSymbol);

        // Construct the DateInterval.
        $dateInterval = new DateInterval($spec);

        // If the time value is negative, invert the DateInterval.
        if ($this->value < 0) {
            $dateInterval->invert = 1;
        }

        return $dateInterval;
    }

    // endregion
}
