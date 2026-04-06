<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Internal;

/**
 * Systems of units.
 */
enum UnitSystem
{
    case Si;

    case SiAccepted;

    case Common;

    case Metric;

    case Imperial;

    case UsCustomary;

    case Scientific;

    case Nautical;

    case Css;

    case Financial;

    case Custom;
}
