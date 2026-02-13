<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\QuantityType;

use Galaxon\Quantities\QuantityType\LuminousFlux;
use Galaxon\Quantities\Tests\Traits\ArrayShapeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the LuminousFlux quantity type.
 */
#[CoversClass(LuminousFlux::class)]
final class LuminousFluxTest extends TestCase
{
    use ArrayShapeTrait;

    // region Overridden methods

    /**
     * Test getUnitDefinitions() returns valid unit definitions.
     */
    public function testGetUnitDefinitionsReturnsValidArray(): void
    {
        $units = LuminousFlux::getUnitDefinitions();

        $this->assertValidUnitDefinitionsShape($units);
    }

    /**
     * Test getConversionDefinitions() returns an empty array.
     */
    public function testGetConversionDefinitionsReturnsEmptyArray(): void
    {
        $this->assertEmpty(LuminousFlux::getConversionDefinitions());
    }

    // endregion
}
