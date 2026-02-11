<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Registry;

use DomainException;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType;
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

        // @phpstan-ignore method.alreadyNarrowedType
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

        $this->assertArrayHasKey('length', $result);
        $this->assertArrayHasKey('mass', $result);
        $this->assertArrayHasKey('time', $result);
        $this->assertArrayHasKey('electric current', $result);
        $this->assertArrayHasKey('temperature', $result);
        $this->assertArrayHasKey('amount of substance', $result);
        $this->assertArrayHasKey('luminous intensity', $result);
    }

    /**
     * Test getAll() contains derived dimensions.
     */
    public function testGetAllContainsDerivedDimensions(): void
    {
        $result = QuantityTypeRegistry::getAll();

        $this->assertArrayHasKey('area', $result);
        $this->assertArrayHasKey('volume', $result);
        $this->assertArrayHasKey('frequency', $result);
        $this->assertArrayHasKey('velocity', $result);
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

        $this->assertInstanceOf(QuantityType::class, $result);
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

        $this->assertInstanceOf(QuantityType::class, $result);
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

        $this->assertInstanceOf(QuantityType::class, $result);
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

        $this->assertInstanceOf(QuantityType::class, $lower);
        $this->assertInstanceOf(QuantityType::class, $upper);
        $this->assertInstanceOf(QuantityType::class, $mixed);
        $this->assertSame($lower->dimension, $upper->dimension);
        $this->assertSame($lower->dimension, $mixed->dimension);
    }

    /**
     * Test getByName() works for multi-word names.
     */
    public function testGetByNameMultiWord(): void
    {
        $result = QuantityTypeRegistry::getByName('electric current');

        $this->assertInstanceOf(QuantityType::class, $result);
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
        $this->assertSame('M', QuantityTypeRegistry::getByClass(Mass::class)?->dimension);
        $this->assertSame('T', QuantityTypeRegistry::getByClass(Time::class)?->dimension);
        $this->assertSame('L2', QuantityTypeRegistry::getByClass(Area::class)?->dimension);
        $this->assertSame('LT-1', QuantityTypeRegistry::getByClass(Velocity::class)?->dimension);
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
        QuantityTypeRegistry::add('hypervolume', $dimension, null);

        // Verify it exists
        $result = QuantityTypeRegistry::getByDimension($dimension);
        $this->assertInstanceOf(QuantityType::class, $result);
        $this->assertSame('hypervolume', $result->name);
        $this->assertNull($result->class);
    }

    /**
     * Test add() throws for duplicate name.
     */
    public function testAddThrowsForDuplicateName(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("Cannot add another quantity type with the name 'length'");

        QuantityTypeRegistry::add('length', 'L9', null);
    }

    /**
     * Test add() throws for duplicate dimension.
     */
    public function testAddThrowsForDuplicateDimension(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("Cannot add another quantity type with the dimension 'L'");

        QuantityTypeRegistry::add('another length', 'L', null);
    }

    /**
     * Test add() throws for duplicate class.
     */
    public function testAddThrowsForDuplicateClass(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot add another quantity type with the class');

        QuantityTypeRegistry::add('another', 'L8', Length::class);
    }

    // endregion

    // region setClass() tests

    /**
     * Test setClass() updates class for existing quantity type.
     */
    public function testSetClassUpdatesClass(): void
    {
        // First add a quantity type without a class.
        $dimension = 'L5';
        $existing = QuantityTypeRegistry::getByDimension($dimension);
        if ($existing !== null) {
            $this->markTestSkipped("Dimension '$dimension' already exists");
        }

        QuantityTypeRegistry::add('pentavolume', $dimension, null);

        // Verify it has no class.
        $result = QuantityTypeRegistry::getByDimension($dimension);
        $this->assertInstanceOf(QuantityType::class, $result);
        $this->assertNull($result->class);

        // Now set the class using our test fixture.
        QuantityTypeRegistry::setClass('pentavolume', TestQuantity::class);

        // Verify the class was set.
        $result = QuantityTypeRegistry::getByDimension($dimension);
        $this->assertSame(TestQuantity::class, $result?->class);
    }

    /**
     * Test setClass() throws for non-existent dimension.
     */
    public function testSetClassThrowsForNonExistentDimension(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Quantity type 'coolness' not found");

        QuantityTypeRegistry::setClass('coolness', Length::class);
    }

    // endregion

    // region getClasses() tests

    /**
     * Test getClasses() returns array of registered classes.
     */
    public function testGetClassesReturnsArrayOfClasses(): void
    {
        $classes = QuantityTypeRegistry::getClasses();

        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertIsArray($classes);
        $this->assertNotEmpty($classes);

        // Verify all elements are class strings.
        foreach ($classes as $class) {
            $this->assertIsString($class);
            $this->assertTrue(class_exists($class), "Class $class should exist");
            $this->assertTrue(is_subclass_of($class, Quantity::class), "Class $class should extend Quantity");
        }
    }

    /**
     * Test getClasses() contains expected quantity type classes.
     */
    public function testGetClassesContainsExpectedClasses(): void
    {
        $classes = QuantityTypeRegistry::getClasses();

        // Verify some expected classes are present.
        $this->assertContains(Length::class, $classes);
        $this->assertContains(Mass::class, $classes);
        $this->assertContains(Time::class, $classes);
        $this->assertContains(Area::class, $classes);
        $this->assertContains(Velocity::class, $classes);
    }

    /**
     * Test getClasses() does not contain null entries.
     */
    public function testGetClassesDoesNotContainNull(): void
    {
        $classes = QuantityTypeRegistry::getClasses();

        // Currency quantity type has no class, so null should not be in the list.
        foreach ($classes as $class) {
            $this->assertNotNull($class);
        }
    }

    // endregion

    // region clear() and reset() tests

    /**
     * Test clear() removes all quantity types.
     */
    public function testClearRemovesAllQuantityTypes(): void
    {
        // Ensure registry is initialized with default types.
        $before = QuantityTypeRegistry::getAll();
        $this->assertNotEmpty($before);

        // Clear the registry.
        QuantityTypeRegistry::clear();

        // Verify the registry is empty.
        $after = QuantityTypeRegistry::getAll();
        $this->assertEmpty($after);

        // Reset to restore defaults for other tests.
        QuantityTypeRegistry::reset();
    }

    /**
     * Test clear() does not trigger re-initialization.
     */
    public function testClearDoesNotReinitialize(): void
    {
        // Clear the registry.
        QuantityTypeRegistry::clear();

        // Add a single custom type.
        QuantityTypeRegistry::add('custom', 'L6', null);

        // Verify only the custom type exists (defaults were not re-loaded).
        $all = QuantityTypeRegistry::getAll();
        $this->assertCount(1, $all);
        $this->assertArrayHasKey('custom', $all);

        // Reset to restore defaults for other tests.
        QuantityTypeRegistry::reset();
    }

    // endregion

    // region Data integrity tests

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
     * Test SI base dimensions have expected properties.
     */
    public function testSiBaseDimensionsProperties(): void
    {
        $siBase = [
            'L' => [
                'name'  => 'length',
                'class' => Length::class,
            ],
            'M' => [
                'name'  => 'mass',
                'class' => Mass::class,
            ],
            'T' => [
                'name'  => 'time',
                'class' => Time::class,
            ],
        ];

        foreach ($siBase as $dimension => $expected) {
            $qtyType = QuantityTypeRegistry::getByDimension($dimension);

            $this->assertNotNull($qtyType, "SI base dimension '$dimension' not found");
            $this->assertSame($expected['name'], $qtyType->name);
            $this->assertSame($expected['class'], $qtyType->class);
        }
    }

    // endregion
}

/**
 * Test fixture class for testing QuantityTypeRegistry::setClass().
 *
 * This is a minimal Quantity subclass used only for testing purposes.
 */
class TestQuantity extends Quantity
{
}
