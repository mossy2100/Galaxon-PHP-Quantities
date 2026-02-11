<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\QuantityType;

use Galaxon\Quantities\QuantityType\RadiationDose;
use Galaxon\Quantities\Tests\Traits\ArrayShapeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the RadiationDose quantity type.
 */
#[CoversClass(RadiationDose::class)]
final class RadiationDoseTest extends TestCase
{
    use ArrayShapeTrait;

    // region Overridden methods

    /**
     * Test getUnitDefinitions() returns valid unit definitions.
     */
    public function testGetUnitDefinitionsReturnsValidArray(): void
    {
        $units = RadiationDose::getUnitDefinitions();

        $this->assertValidUnitDefinitionsShape($units);
    }

    /**
     * Test getConversionDefinitions() returns valid conversion definitions.
     */
    public function testGetConversionDefinitionsReturnsValidArray(): void
    {
        $conversions = RadiationDose::getConversionDefinitions();

        $this->assertValidConversionDefinitionsShape($conversions);
        $this->assertCount(2, $conversions);
    }

    // endregion
}
