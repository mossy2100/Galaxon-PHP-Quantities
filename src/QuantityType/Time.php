<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use DateInterval;
use DateMalformedIntervalStringException;
use Galaxon\Core\Numbers;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Services\PrefixService;
use Galaxon\Quantities\UnitSystem;
use Override;

/**
 * Represents time quantities.
 */
class Time extends Quantity
{
    // region Static properties

    /**
     * Default part unit symbols for output methods.
     *
     * @var list<string>
     */
    protected static array $defaultPartUnitSymbols = ['y', 'mo', 'w', 'd', 'h', 'min', 's'];

    /**
     * Default part unit symbols for input methods.
     *
     * @var string
     */
    protected static string $defaultResultUnitSymbol = 's';

    // endregion

    // region Overridden methods

    /**
     * Unit definitions for time.
     *
     * @return array<string, array{
     *     asciiSymbol: string,
     *     unicodeSymbol?: string,
     *     prefixGroup?: int,
     *     alternateSymbol?: string,
     *     systems: list<UnitSystem>
     * }>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'second'  => [
                'asciiSymbol' => 's',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [
                    UnitSystem::Si,
                ],
            ],
            'minute'  => [
                'asciiSymbol' => 'min',
                'systems'     => [
                    UnitSystem::SiAccepted,
                ],
            ],
            'hour'    => [
                'asciiSymbol' => 'h',
                'systems'     => [
                    UnitSystem::SiAccepted,
                ],
            ],
            'day'     => [
                'asciiSymbol' => 'd',
                'systems'     => [
                    UnitSystem::SiAccepted,
                ],
            ],
            'week'    => [
                'asciiSymbol' => 'w',
                'systems'     => [
                    UnitSystem::Common,
                ],
            ],
            'month'   => [
                'asciiSymbol' => 'mo',
                'systems'     => [
                    UnitSystem::Common,
                ],
            ],
            'year'    => [
                'asciiSymbol' => 'y',
                'systems'     => [
                    UnitSystem::Common,
                ],
            ],
            'century' => [
                'asciiSymbol' => 'c',
                'systems'     => [
                    UnitSystem::Common,
                ],
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

    // endregion

    // region DateInterval-related methods

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
        // Add all the parts of the DateInterval, starting with seconds.
        $result = new self($interval->s + $interval->f, 's')
            ->add($interval->y, 'y')
            ->add($interval->m, 'mo')
            ->add($interval->d, 'd')
            ->add($interval->h, 'h')
            ->add($interval->i, 'min');

        // Handle negative interval.
        if ($interval->invert === 1) {
            $result = $result->neg();
        }

        assert($result instanceof self);
        return $result;
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
            if ($symbol === 'sign' || Numbers::equal($value, 0)) {
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
        // Get the specifier string.
        $spec = $this->toDateIntervalSpecifier();

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
