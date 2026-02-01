<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use DateInterval;
use DomainException;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\System;
use LogicException;
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
                'prefixGroup' => PrefixRegistry::GROUP_CODE_METRIC,
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
     * @param string $smallestUnitSymbol The smallest unit to include (default 's').
     * @return string A DateInterval specification string.
     * @throws DomainException If the smallest unit argument is invalid.
     */
    public function toDateIntervalSpecifier(string $smallestUnitSymbol = 's'): string
    {
        // Validate the provided smallest unit.
        self::validateSmallestUnitSymbol($smallestUnitSymbol);

        // Validate the part units.
        self::validatePartUnitSymbols();

        // Prep.
        $partUnits = static::getPartsConfig()['to'];
        $smallestUnitIndex = (int)array_search($smallestUnitSymbol, $partUnits, true);
        $parts = $this->toParts($smallestUnitSymbol, 0);  // DateInterval requires integer parts.
        $spec = 'P';
        $labels = [
            'y'   => 'Y',
            'mo'  => 'M',
            'w'   => 'W',
            'd'   => 'D',
            'h'   => 'H',
            'min' => 'M',
            's'   => 'S',
        ];
        $timeSeparatorAdded = false;

        // Build the specification string.
        for ($i = 0; $i <= $smallestUnitIndex; $i++) {
            $unit = $partUnits[$i];
            $value = $parts[$unit] ?? 0;

            // Check we have a label.
            if (!isset($labels[$unit])) {
                throw new LogicException("Unit '$unit' is not compatible with DateInterval specifiers.");
            }

            // Add time separator before hours.
            if ($unit === 'h' && !$timeSeparatorAdded) {
                $spec .= 'T';
                $timeSeparatorAdded = true;
            }

            // Add the specifier part if it isn't 0.
            if ($parts[$unit] !== 0.0) {
                $spec .= (int)$value . $labels[$unit];
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
     * @param string $smallestUnitSymbol The smallest unit to include (default 's').
     * @return DateInterval A new DateInterval object.
     * @throws DomainException If the smallest unit argument is invalid.
     */
    public function toDateInterval(string $smallestUnitSymbol = 's'): DateInterval
    {
        // Validate the provided smallest unit.
        self::validateSmallestUnitSymbol($smallestUnitSymbol);

        // Get the specifier string.
        $spec = $this->toDateIntervalSpecifier($smallestUnitSymbol);

        // Construct the DateInterval.
        $dateInterval = new DateInterval($spec);

        // If the time value is negative, invert the DateInterval.
        if ($this->value < 0) {
            $dateInterval->invert = 1;
        }

        return $dateInterval;
    }

    // endregion

    // region Part-related methods

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
}
