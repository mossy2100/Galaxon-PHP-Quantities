<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\QuantityType;

use Galaxon\Quantities\QuantityType\CatalyticActivity;
use Galaxon\Quantities\Tests\Traits\ArrayShapeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the CatalyticActivity quantity type.
 */
#[CoversClass(CatalyticActivity::class)]
final class CatalyticActivityTest extends TestCase
{
    use ArrayShapeTrait;

    // region Overridden methods

    /**
     * Test getUnitDefinitions() returns valid unit definitions.
     */
    public function testGetUnitDefinitionsReturnsValidArray(): void
    {
        $units = CatalyticActivity::getUnitDefinitions();

        $this->assertValidUnitDefinitionsShape($units);
    }

    /**
     * Test getConversionDefinitions() returns valid conversion definitions.
     */
    public function testGetConversionDefinitionsReturnsValidArray(): void
    {
        $conversions = CatalyticActivity::getConversionDefinitions();

        $this->assertValidConversionDefinitionsShape($conversions);
        $this->assertCount(1, $conversions);
    }

    // endregion
}
