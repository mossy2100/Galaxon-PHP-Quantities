<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\QuantityType;

use Galaxon\Quantities\QuantityType\Power;
use Galaxon\Quantities\Tests\Traits\ArrayShapeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Power quantity type.
 */
#[CoversClass(Power::class)]
final class PowerTest extends TestCase
{
    use ArrayShapeTrait;

    // region Overridden methods

    /**
     * Test getUnitDefinitions() returns valid unit definitions.
     */
    public function testGetUnitDefinitionsReturnsValidArray(): void
    {
        $units = Power::getUnitDefinitions();

        $this->assertValidUnitDefinitionsShape($units);
    }

    /**
     * Test getConversionDefinitions() returns valid conversion definitions.
     */
    public function testGetConversionDefinitionsReturnsValidArray(): void
    {
        $conversions = Power::getConversionDefinitions();

        $this->assertValidConversionDefinitionsShape($conversions);
        $this->assertCount(1, $conversions);
    }

    // endregion
}
