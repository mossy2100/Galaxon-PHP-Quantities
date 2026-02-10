<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\QuantityType;

use Galaxon\Quantities\QuantityType\MagneticFlux;
use Galaxon\Quantities\Tests\Traits\ArrayShapeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the MagneticFlux quantity type.
 */
#[CoversClass(MagneticFlux::class)]
final class MagneticFluxTest extends TestCase
{
    use ArrayShapeTrait;

    // region Overridden methods

    /**
     * Test getUnitDefinitions() returns valid unit definitions.
     */
    public function testGetUnitDefinitionsReturnsValidArray(): void
    {
        $units = MagneticFlux::getUnitDefinitions();

        $this->assertValidUnitDefinitionsShape($units);
    }

    /**
     * Test getConversionDefinitions() returns empty array.
     */
    public function testGetConversionDefinitionsReturnsEmptyArray(): void
    {
        $conversions = MagneticFlux::getConversionDefinitions();

        $this->assertEmpty($conversions);
    }

    // endregion
}
