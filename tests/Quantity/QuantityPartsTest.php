<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Quantity;

use DomainException;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType\Angle;
use Galaxon\Quantities\QuantityType\Time;
use Galaxon\Quantities\Registry\QuantityTypeRegistry;
use Galaxon\Quantities\Tests\Fixtures\BadUnitPartsQuantity;
use Galaxon\Quantities\Tests\Fixtures\UnregisteredQuantity;
use Galaxon\Quantities\Tests\Fixtures\WrongDimensionPartsQuantity;
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
     * Test formatParts() with Time quantity.
     */
    public function testFormatPartsTime(): void
    {
        $time = new Time(3661, 's');
        $result = $time->formatParts();

        $this->assertSame('1h 1min 1s', $result);
    }

    /**
     * Test formatParts() with negative value.
     */
    public function testFormatPartsNegative(): void
    {
        $time = new Time(-3661, 's');
        $result = $time->formatParts();

        $this->assertSame('-1h 1min 1s', $result);
    }

    /**
     * Test formatParts() with showZeros.
     */
    public function testFormatPartsShowZeros(): void
    {
        $time = new Time(3600, 's');
        $result = $time->formatParts('h', 's', showZeros: true);

        $this->assertSame('1h 0min 0s', $result);
    }

    /**
     * Test formatParts() with zero value.
     */
    public function testFormatPartsZero(): void
    {
        $time = new Time(0, 's');
        $result = $time->formatParts();

        $this->assertSame('0s', $result);
    }

    // endregion

    // region fromParts() error tests

    /**
     * Test fromParts() without result unit and no default throws exception.
     */
    public function testFromPartsWithoutResultUnitThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Result unit symbol is required');

        // Quantity has no default 'from' in getPartsConfig()
        Quantity::fromParts([
            'm' => 100,
        ]);
    }

    /**
     * Test fromParts() with unknown result unit throws exception.
     */
    public function testFromPartsUnknownResultUnitThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Unknown unit symbol: 'xyz'.");

        Quantity::fromParts([
            'm' => 100,
        ], 'xyz');
    }

    /**
     * Test fromParts() with invalid sign throws exception.
     */
    public function testFromPartsInvalidSignThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid sign: 0. Must be -1 or 1.');

        Quantity::fromParts([
            'm'    => 100,
            'sign' => 0,
        ], 'm');
    }

    // endregion

    // region validatePrecision() tests

    /**
     * Test toParts() with negative precision throws exception.
     */
    public function testToPartsNegativePrecisionThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid precision specified; -1. Must be null or a non-negative integer.');

        $time = new Time(3661, 's');
        $time->toParts(precision: -1);
    }

    /**
     * Test formatParts() with negative precision throws exception.
     */
    public function testFormatPartsNegativePrecisionThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid precision specified');

        $time = new Time(3661, 's');
        $time->formatParts(precision: -1);
    }

    // endregion

    // region validatePartUnitSymbols() tests

    /**
     * Test toParts() on base Quantity class without parts config throws exception.
     */
    public function testToPartsOnBaseQuantityThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("The derived Quantity class must define the 'to' part units");

        // Base Quantity class doesn't define getPartsConfig() with 'to' units
        $qty = Quantity::create(100, 'kg*m/s2');
        $qty->toParts();
    }

    // endregion

    // region validateUnitSymbol() tests

    /**
     * Test fromParts() with unknown part unit throws exception.
     */
    public function testFromPartsUnknownPartUnitThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Unknown or unsupported unit 'xyz'.");

        // Trying to add a part with an unknown unit
        Quantity::fromParts([
            'xyz' => 100,
        ], 'm');
    }

    // endregion

    // region validateLargestAndSmallest() tests

    /**
     * Test toParts() with invalid largest unit throws exception.
     */
    public function testToPartsInvalidLargestUnitThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("The unit 'yr' is not a valid part unit.");

        $time = new Time(3661, 's');
        $time->toParts('yr');  // 'yr' is not in Time's default part units
    }

    /**
     * Test toParts() with invalid smallest unit throws exception.
     */
    public function testToPartsInvalidSmallestUnitThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("The unit 'ns' is not a valid part unit.");

        $time = new Time(3661, 's');
        $time->toParts(smallestUnitSymbol: 'ns');  // 'ns' is not in Time's default part units
    }

    /**
     * Test formatParts() with invalid largest unit throws exception.
     */
    public function testFormatPartsInvalidLargestUnitThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("The unit 'yr' is not a valid part unit.");

        $time = new Time(3661, 's');
        $time->formatParts('yr');
    }

    /**
     * Test formatParts() with invalid smallest unit throws exception.
     */
    public function testFormatPartsInvalidSmallestUnitThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("The unit 'ns' is not a valid part unit.");

        $time = new Time(3661, 's');
        $time->formatParts(smallestUnitSymbol: 'ns');
    }

    // endregion

    // region toParts() rounding tests

    /**
     * Test toParts() rounding up smallest unit.
     */
    public function testToPartsRoundingUp(): void
    {
        // 59 minutes 59.9 seconds → should round to 1h 0min 0s when precision=0
        $time = new Time(3599.9, 's');
        $parts = $time->toParts(precision: 0);

        $this->assertSame(1, $parts['h']);
        $this->assertSame(0, $parts['min']);
        $this->assertSame(0.0, $parts['s']);
    }

    // endregion

    // region validatePartUnitSymbols() exception tests

    /**
     * Test toParts() from unregistered class throws exception.
     */
    public function testToPartsFromUnregisteredClassThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Quantity type not found for class');

        // UnregisteredQuantity uses L*M dimension which has no registered class
        $qty = new UnregisteredQuantity(100, 'kg*m');
        $qty->toParts();
    }

    /**
     * Test toParts() with unknown unit in parts config throws exception.
     */
    public function testToPartsWithUnknownUnitInConfigThrowsException(): void
    {
        // Temporarily register the fixture class for time
        QuantityTypeRegistry::setClass('time', BadUnitPartsQuantity::class);

        try {
            $this->expectException(DomainException::class);
            $this->expectExceptionMessage("Unknown unit symbol: 'xyz'.");

            // BadUnitPartsQuantity has 'xyz' in its parts config
            $qty = new BadUnitPartsQuantity(100, 's');
            $qty->toParts();
        } finally {
            // Restore the original Time class
            QuantityTypeRegistry::setClass('time', Time::class);
        }
    }

    /**
     * Test toParts() with wrong dimension unit in parts config throws exception.
     */
    public function testToPartsWithWrongDimensionUnitInConfigThrowsException(): void
    {
        // Temporarily register the fixture class for time
        QuantityTypeRegistry::setClass('time', WrongDimensionPartsQuantity::class);

        try {
            $this->expectException(DomainException::class);
            $this->expectExceptionMessage("Unit 'm' (L) is invalid for time quantities (T).");

            // WrongDimensionPartsQuantity has 'm' (length) in its parts config
            $qty = new WrongDimensionPartsQuantity(100, 's');
            $qty->toParts();
        } finally {
            // Restore the original Time class
            QuantityTypeRegistry::setClass('time', Time::class);
        }
    }

    // endregion
}
