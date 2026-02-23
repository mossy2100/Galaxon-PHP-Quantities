<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

/**
 * Systems of units.
 */
enum UnitSystem
{
    // region Values

    case Si;

    case SiAccepted;

    case Common;

    case Imperial;

    case UsCustomary;

    case Scientific;

    case Nautical;

    case Css;

    case Financial;

    case Custom;

    // endregion

    // region Constants

    /**
     * The default unit systems that will be lazy-loaded on first use of the UnitService.
     */
    public const array DEFAULTS = [self::Si, self::SiAccepted, self::Common];

    // endregion
}
