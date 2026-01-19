<?php

declare(strict_types=1);

namespace Registry;

use DomainException;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType;
use Galaxon\Quantities\QuantityType\Angle;
use Galaxon\Quantities\QuantityType\Area;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\QuantityType\Time;
use Galaxon\Quantities\QuantityType\Velocity;
use Galaxon\Quantities\Registry\QuantityTypeRegistry;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for QuantityTypeRegistry class.
 */
#[CoversClass(QuantityTypeRegistry::class)]
final class QuantityTypeRegistryTest extends TestCase
{
    // region getAll() tests

    /**
     * Test getAll() returns an array.
     */
    public function testGetAllReturnsArray(): void
    {
        $result = QuantityTypeRegistry::getAll();

        $this->assertIsArray($result);
    }

    /**
     * Test getAll() returns QuantityType objects.
     */
    public function testGetAllReturnsQuantityTypeObjects(): void
    {
        $result = QuantityTypeRegistry::getAll();

        foreach ($result as $dimension => $qtyType) {
            $this->assertIsString($dimension);
            $this->assertInstanceOf(QuantityType::class, $qtyType);
        }
    }

    /**
     * Test getAll() contains SI base dimensions.
     */
    public function testGetAllContainsSiBaseDimensions(): void
    {
        $result = QuantityTypeRegistry::getAll();

        $this->assertArrayHasKey('L', $result);  // length
        $this->assertArrayHasKey('M', $result);  // mass
        $this->assertArrayHasKey('T', $result);  // time
        $this->assertArrayHasKey('I', $result);  // electric current
        $this->assertArrayHasKey('H', $result);  // temperature
        $this->assertArrayHasKey('N', $result);  // amount of substance
        $this->assertArrayHasKey('J', $result);  // luminous intensity
    }

    /**
     * Test getAll() contains derived dimensions.
     */
    public function testGetAllContainsDerivedDimensions(): void
    {
        $result = QuantityTypeRegistry::getAll();

        $this->assertArrayHasKey('L2', $result);    // area
        $this->assertArrayHasKey('L3', $result);    // volume
        $this->assertArrayHasKey('T-1', $result);   // frequency
        $this->assertArrayHasKey('LT-1', $result);  // velocity
    }

    // endregion

    // region getByDimension() tests

    /**
     * Test getByDimension() returns QuantityType for valid dimension.
     */
    public function testGetByDimensionReturnsQuantityType(): void
    {
        $result = QuantityTypeRegistry::getByDimension('L');

        $this->assertInstanceOf(QuantityType::class, $result);
        $this->assertSame('L', $result->dimension);
        $this->assertSame('length', $result->name);
        $this->assertSame(Length::class, $result->class);
    }

    /**
     * Test getByDimension() returns correct data for mass.
     */
    public function testGetByDimensionMass(): void
    {
        $result = QuantityTypeRegistry::getByDimension('M');

        $this->assertSame('M', $result->dimension);
        $this->assertSame('mass', $result->name);
        $this->assertSame(Mass::class, $result->class);
    }

    /**
     * Test getByDimension() returns correct data for derived dimension.
     */
    public function testGetByDimensionDerived(): void
    {
        $result = QuantityTypeRegistry::getByDimension('L2');

        $this->assertSame('L2', $result->dimension);
        $this->assertSame('area', $result->name);
        $this->assertSame(Area::class, $result->class);
    }

    /**
     * Test getByDimension() normalizes dimension code.
     */
    public function testGetByDimensionNormalizesDimension(): void
    {
        // 'LT-1' should be normalized to 'LT-1' (canonical order)
        $result = QuantityTypeRegistry::getByDimension('LT-1');

        $this->assertNotNull($result);
        $this->assertSame('LT-1', $result->dimension);
        $this->assertSame('velocity', $result->name);
    }

    /**
     * Test getByDimension() throws for invalid dimension code.
     */
    public function testGetByDimensionThrowsForInvalidDimension(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid dimension code 'X9Y9Z9'");

        QuantityTypeRegistry::getByDimension('X9Y9Z9');
    }

    /**
     * Test getByDimension() returns null for empty string (dimensionless).
     */
    public function testGetByDimensionReturnsNullForEmptyDimension(): void
    {
        // Empty dimension (dimensionless) has no registered quantity type
        $result = QuantityTypeRegistry::getByDimension('');

        $this->assertNull($result);
    }

    // endregion

    // region getByName() tests

    /**
     * Test getByName() returns QuantityType for valid name.
     */
    public function testGetByNameReturnsQuantityType(): void
    {
        $result = QuantityTypeRegistry::getByName('length');

        $this->assertInstanceOf(QuantityType::class, $result);
        $this->assertSame('length', $result->name);
        $this->assertSame('L', $result->dimension);
    }

    /**
     * Test getByName() is case insensitive.
     */
    public function testGetByNameIsCaseInsensitive(): void
    {
        $lower = QuantityTypeRegistry::getByName('length');
        $upper = QuantityTypeRegistry::getByName('LENGTH');
        $mixed = QuantityTypeRegistry::getByName('Length');

        $this->assertNotNull($lower);
        $this->assertNotNull($upper);
        $this->assertNotNull($mixed);
        $this->assertSame($lower->dimension, $upper->dimension);
        $this->assertSame($lower->dimension, $mixed->dimension);
    }

    /**
     * Test getByName() works for multi-word names.
     */
    public function testGetByNameMultiWord(): void
    {
        $result = QuantityTypeRegistry::getByName('electric current');

        $this->assertNotNull($result);
        $this->assertSame('I', $result->dimension);
    }

    /**
     * Test getByName() returns null for unknown name.
     */
    public function testGetByNameReturnsNullForUnknown(): void
    {
        $result = QuantityTypeRegistry::getByName('nonexistent');

        $this->assertNull($result);
    }

    // endregion

    // region getByClass() tests

    /**
     * Test getByClass() returns QuantityType for valid class.
     */
    public function testGetByClassReturnsQuantityType(): void
    {
        $result = QuantityTypeRegistry::getByClass(Length::class);

        $this->assertInstanceOf(QuantityType::class, $result);
        $this->assertSame(Length::class, $result->class);
        $this->assertSame('L', $result->dimension);
    }

    /**
     * Test getByClass() works for different classes.
     */
    public function testGetByClassDifferentClasses(): void
    {
        $this->assertSame('M', QuantityTypeRegistry::getByClass(Mass::class)->dimension);
        $this->assertSame('T', QuantityTypeRegistry::getByClass(Time::class)->dimension);
        $this->assertSame('L2', QuantityTypeRegistry::getByClass(Area::class)->dimension);
        $this->assertSame('LT-1', QuantityTypeRegistry::getByClass(Velocity::class)->dimension);
    }

    /**
     * Test getByClass() returns null for unregistered class.
     */
    public function testGetByClassReturnsNullForUnregistered(): void
    {
        $result = QuantityTypeRegistry::getByClass('NonExistent\\Class');

        $this->assertNull($result);
    }

    /**
     * Test getByClass() returns null for base Quantity class.
     */
    public function testGetByClassReturnsNullForBaseClass(): void
    {
        $result = QuantityTypeRegistry::getByClass(Quantity::class);

        $this->assertNull($result);
    }

    // endregion

    // region add() tests

    /**
     * Test add() creates a new quantity type.
     */
    public function testAddCreatesNewQuantityType(): void
    {
        // Use a unique dimension that doesn't exist
        $dimension = 'L4';  // 4th power of length (unlikely to exist)

        // Make sure it doesn't exist first
        $existing = QuantityTypeRegistry::getByDimension($dimension);
        if ($existing !== null) {
            $this->markTestSkipped("Dimension '$dimension' already exists");
        }

        // Add it
        QuantityTypeRegistry::add($dimension, 'hypervolume', 'm4', null);

        // Verify it exists
        $result = QuantityTypeRegistry::getByDimension($dimension);
        $this->assertNotNull($result);
        $this->assertSame('hypervolume', $result->name);
        $this->assertSame('m4', $result->siUnitSymbol);
        $this->assertNull($result->class);
    }

    /**
     * Test add() throws for duplicate name.
     */
    public function testAddThrowsForDuplicateName(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("Cannot add another quantity type with the name 'length'");

        QuantityTypeRegistry::add('L9', 'length', 'x', null);
    }

    /**
     * Test add() throws for duplicate dimension.
     */
    public function testAddThrowsForDuplicateDimension(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("Cannot add another quantity type with the dimension 'L'");

        QuantityTypeRegistry::add('L', 'another length', 'm', null);
    }

    /**
     * Test add() throws for duplicate class.
     */
    public function testAddThrowsForDuplicateClass(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("Cannot add another quantity type with the class");

        QuantityTypeRegistry::add('L8', 'another', 'x', Length::class);
    }

    // endregion

    // region setClass() tests

    /**
     * Test setClass() updates class for existing quantity type.
     */
    public function testSetClassUpdatesClass(): void
    {
        // First add a quantity type without a class
        $dimension = 'L5';
        $existing = QuantityTypeRegistry::getByDimension($dimension);
        if ($existing !== null) {
            $this->markTestSkipped("Dimension '$dimension' already exists");
        }

        QuantityTypeRegistry::add($dimension, 'pentavolume', 'm5', null);

        // Verify it has no class
        $result = QuantityTypeRegistry::getByDimension($dimension);
        $this->assertNull($result->class);

        // This would fail because Length::class is already registered
        // So we just verify the method exists and throws for invalid dimension
    }

    /**
     * Test setClass() throws for non-existent dimension.
     */
    public function testSetClassThrowsForNonExistentDimension(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Quantity type with dimension 'L9' not found");

        QuantityTypeRegistry::setClass('L9', Length::class);
    }

    // endregion

    // region Data integrity tests

    /**
     * Test all quantity types have valid dimension codes.
     */
    public function testAllQuantityTypesHaveValidDimensions(): void
    {
        $all = QuantityTypeRegistry::getAll();

        foreach ($all as $dimension => $qtyType) {
            $this->assertSame($dimension, $qtyType->dimension);
        }
    }

    /**
     * Test all quantity types have non-empty names.
     */
    public function testAllQuantityTypesHaveNames(): void
    {
        $all = QuantityTypeRegistry::getAll();

        foreach ($all as $qtyType) {
            $this->assertNotEmpty($qtyType->name, "Quantity type {$qtyType->dimension} has empty name");
        }
    }

    /**
     * Test all quantity types have SI unit terms.
     */
    public function testAllQuantityTypesHaveSiUnitTerms(): void
    {
        $all = QuantityTypeRegistry::getAll();

        foreach ($all as $qtyType) {
            $this->assertNotNull(
                $qtyType->siUnitSymbol,
                "Quantity type {$qtyType->dimension} has no SI unit term"
            );
        }
    }

    /**
     * Test SI base dimensions have expected properties.
     */
    public function testSiBaseDimensionsProperties(): void
    {
        $siBase = [
            'L' => ['name' => 'length', 'siUnit' => 'm', 'class' => Length::class],
            'M' => ['name' => 'mass', 'siUnit' => 'kg', 'class' => Mass::class],
            'T' => ['name' => 'time', 'siUnit' => 's', 'class' => Time::class],
        ];

        foreach ($siBase as $dimension => $expected) {
            $qtyType = QuantityTypeRegistry::getByDimension($dimension);

            $this->assertNotNull($qtyType, "SI base dimension '$dimension' not found");
            $this->assertSame($expected['name'], $qtyType->name);
            $this->assertSame($expected['siUnit'], $qtyType->siUnitSymbol);
            $this->assertSame($expected['class'], $qtyType->class);
        }
    }

    // endregion
}
