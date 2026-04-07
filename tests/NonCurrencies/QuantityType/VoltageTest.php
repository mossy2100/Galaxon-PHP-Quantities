<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\NonCurrencies\QuantityType;

use Galaxon\Quantities\QuantityType\Voltage;
use Galaxon\Quantities\Tests\NonCurrencies\Traits\ArrayShapeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Voltage quantity type.
 */
#[CoversClass(Voltage::class)]
final class VoltageTest extends TestCase
{
    use ArrayShapeTrait;

    // region Overridden methods

    /**
     * Test getUnitDefinitions() returns valid unit definitions.
     */
    public function testGetUnitDefinitionsReturnsValidArray(): void
    {
        $units = Voltage::getUnitDefinitions();

        $this->assertValidUnitDefinitionsShape($units);
    }

    /**
     * Test getConversionDefinitions() returns valid conversion definitions.
     */
    public function testGetConversionDefinitionsReturnsValidArray(): void
    {
        $conversions = Voltage::getConversionDefinitions();

        $this->assertValidConversionDefinitionsShape($conversions);
    }

    // endregion
}
