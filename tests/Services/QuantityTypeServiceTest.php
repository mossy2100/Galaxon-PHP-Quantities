<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Services;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Quantities\Internal\QuantityType;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType\Area;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\QuantityType\Time;
use Galaxon\Quantities\QuantityType\Velocity;
use Galaxon\Quantities\Services\QuantityTypeService;
use Galaxon\Quantities\Tests\Fixtures\TestQuantity;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Tests for QuantityTypeService class.
 */
#[CoversClass(QuantityTypeService::class)]
final class QuantityTypeServiceTest extends TestCase
{
    // region getAll() tests

    /**
     * Test getAll() returns an array.
     */
    public function testGetAllReturnsArray(): void
    {
        $result = QuantityTypeService::getAll();

        $this->assertIsArray($result); // @phpstan-ignore method.alreadyNarrowedType
    }

    /**
     * Test getAll() returns QuantityType objects.
     */
    public function testGetAllReturnsQuantityTypeObjects(): void
    {
        $result = QuantityTypeService::getAll();

        foreach ($result as $name => $qtyType) {
            $this->assertIsString($name);
            $this->assertInstanceOf(QuantityType::class, $qtyType);
        }
    }

    /**
     * Test getAll() contains SI base dimensions.
     */
    public function testGetAllContainsSiBaseDimensions(): void
    {
        $result = QuantityTypeService::getAll();

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
        $result = QuantityTypeService::getAll();

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
        $result = QuantityTypeService::getByDimension('L');

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
        $result = QuantityTypeService::getByDimension('M');

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
        $result = QuantityTypeService::getByDimension('L2');

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
        // 'T-1L' is the non-canonical form; it should be normalized to 'LT-1' for lookup.
        $result = QuantityTypeService::getByDimension('T-1L');

        $this->assertInstanceOf(QuantityType::class, $result);
        $this->assertSame('velocity', $result->name);
        $this->assertSame('LT-1', $result->dimension);
    }

    /**
     * Test getByDimension() throws for invalid dimension code.
     */
    public function testGetByDimensionThrowsForInvalidDimension(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage("Invalid dimension code 'X9Y9Z9'");

        QuantityTypeService::getByDimension('X9Y9Z9');
    }

    // endregion

    // region getByName() tests

    /**
     * Test getByName() returns QuantityType for valid name.
     */
    public function testGetByNameReturnsQuantityType(): void
    {
        $result = QuantityTypeService::getByName('length');

        $this->assertInstanceOf(QuantityType::class, $result);
        $this->assertSame('length', $result->name);
        $this->assertSame('L', $result->dimension);
    }

    /**
     * Test getByName() is case insensitive.
     */
    public function testGetByNameIsCaseInsensitive(): void
    {
        $lower = QuantityTypeService::getByName('length');
        $upper = QuantityTypeService::getByName('LENGTH');
        $mixed = QuantityTypeService::getByName('Length');

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
        $result = QuantityTypeService::getByName('electric current');

        $this->assertInstanceOf(QuantityType::class, $result);
        $this->assertSame('I', $result->dimension);
    }

    /**
     * Test getByName() returns null for unknown name.
     */
    public function testGetByNameReturnsNullForUnknown(): void
    {
        $result = QuantityTypeService::getByName('nonexistent');

        $this->assertNull($result);
    }

    // endregion

    // region getByClass() tests

    /**
     * Test getByClass() returns QuantityType for valid class.
     */
    public function testGetByClassReturnsQuantityType(): void
    {
        $result = QuantityTypeService::getByClass(Length::class);

        $this->assertInstanceOf(QuantityType::class, $result);
        $this->assertSame(Length::class, $result->class);
        $this->assertSame('L', $result->dimension);
    }

    /**
     * Test getByClass() works for different classes.
     */
    public function testGetByClassDifferentClasses(): void
    {
        $this->assertSame('M', QuantityTypeService::getByClass(Mass::class)?->dimension);
        $this->assertSame('T', QuantityTypeService::getByClass(Time::class)?->dimension);
        $this->assertSame('L2', QuantityTypeService::getByClass(Area::class)?->dimension);
        $this->assertSame('LT-1', QuantityTypeService::getByClass(Velocity::class)?->dimension);
    }

    /**
     * Test getByClass() returns null for unregistered class.
     */
    public function testGetByClassReturnsNullForUnregistered(): void
    {
        $result = QuantityTypeService::getByClass('NonExistent\\Class');

        $this->assertNull($result);
    }

    /**
     * Test getByClass() returns null for base Quantity class.
     */
    public function testGetByClassReturnsNullForBaseClass(): void
    {
        $result = QuantityTypeService::getByClass(Quantity::class);

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
        $existing = QuantityTypeService::getByDimension($dimension);
        if ($existing !== null) {
            $this->markTestSkipped("Dimension '$dimension' already exists");
        }

        // Add it
        QuantityTypeService::add('hypervolume', $dimension, TestQuantity::class);

        // Verify it exists
        $result = QuantityTypeService::getByDimension($dimension);
        $this->assertInstanceOf(QuantityType::class, $result);
        $this->assertSame('hypervolume', $result->name);
        $this->assertSame(TestQuantity::class, $result->class);

        // Tidy up.
        QuantityTypeService::remove('hypervolume');
    }

    /**
     * Test add() throws for duplicate name.
     */
    public function testAddThrowsForDuplicateName(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("Cannot add another quantity type with the name 'length'");

        QuantityTypeService::add('length', 'L9', TestQuantity::class);
    }

    /**
     * Test add() throws for duplicate dimension.
     */
    public function testAddThrowsForDuplicateDimension(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("Cannot add another quantity type with the dimension 'L'");

        QuantityTypeService::add('another length', 'L', TestQuantity::class);
    }

    /**
     * Test add() throws for duplicate class.
     */
    public function testAddThrowsForDuplicateClass(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot add another quantity type with the class');

        QuantityTypeService::add('another', 'L8', Length::class);
    }

    // endregion

    // region remove() tests

    /**
     * Test remove() removes quantity type.
     */
    public function testRemoveRemovesQuantityType(): void
    {
        QuantityTypeService::add('coolness', 'L5', TestQuantity::class);

        $result = QuantityTypeService::getByName('coolness');
        $this->assertNotNull($result);

        QuantityTypeService::remove('coolness');

        $result = QuantityTypeService::getByName('coolness');
        $this->assertNull($result);
    }

    /**
     * Test remove() does not throw for non-existent quantity type.
     */
    public function testRemoveDoesNotThrowForNonExistentQuantityType(): void
    {
        // Ensure the registry is initialized.
        QuantityTypeService::getAll();

        // Get the current count of quantity types.
        $refClass = new ReflectionClass(QuantityTypeService::class);
        $qtyTypes = $refClass->getStaticPropertyValue('quantityTypes');
        assert(is_array($qtyTypes));
        $n = count($qtyTypes);

        // Attempt to remove a non-existent quantity type.
        QuantityTypeService::remove('nonexistent');

        // Check count is the same.
        $qtyTypes = $refClass->getStaticPropertyValue('quantityTypes');
        assert(is_array($qtyTypes));
        $n2 = count($qtyTypes);
        $this->assertEquals($n, $n2);
    }

    /**
     * Test remove() does nothing if the registry is not initialized (i.e. quantityTypes is null).
     */
    public function testRemoveDoesNothingIfQuantityTypesNotInitialized(): void
    {
        // Uninitialize the registry.
        QuantityTypeService::reset();

        // The relevant property is private, so let's use reflection to access it.
        $refClass = new ReflectionClass(QuantityTypeService::class);
        $this->assertNull($refClass->getStaticPropertyValue('quantityTypes'));

        // Call remove() and verify the internal array is still null, and no exception was thrown.
        QuantityTypeService::remove('coolness');
        $this->assertNull($refClass->getStaticPropertyValue('quantityTypes'));
    }

    // endregion

    // region setClass() tests

    /**
     * Test setClass() updates class for existing quantity type.
     */
    public function testSetClassUpdatesClass(): void
    {
        // Add a quantity type with TestQuantity as its class.
        $dimension = 'L5';
        $existing = QuantityTypeService::getByDimension($dimension);
        if ($existing !== null) {
            $this->markTestSkipped("Dimension '$dimension' already exists");
        }

        QuantityTypeService::add('pentavolume', $dimension, TestQuantity::class);

        // Verify initial class.
        $result = QuantityTypeService::getByDimension($dimension);
        $this->assertInstanceOf(QuantityType::class, $result);
        $this->assertSame(TestQuantity::class, $result->class);

        // Update the class to Length using setClass().
        QuantityTypeService::setClass('pentavolume', Length::class);

        // Verify the class was updated.
        $result = QuantityTypeService::getByDimension($dimension);
        $this->assertSame(Length::class, $result?->class);

        // Tidy up.
        QuantityTypeService::remove('pentavolume');
    }

    /**
     * Test setClass() throws for non-existent name.
     */
    public function testSetClassThrowsForNonExistentName(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Unknown quantity type: 'coolness'.");

        QuantityTypeService::setClass('coolness', Length::class);
    }

    // endregion

    // region getClasses() tests

    /**
     * Test getClasses() returns array of registered classes.
     */
    public function testGetClassesReturnsArrayOfClasses(): void
    {
        $classes = QuantityTypeService::getClasses();

        $this->assertIsArray($classes); // @phpstan-ignore method.alreadyNarrowedType
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
        $classes = QuantityTypeService::getClasses();

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
        $classes = QuantityTypeService::getClasses();

        // All quantity types have classes, so null should not be in the list.
        foreach ($classes as $class) {
            $this->assertNotNull($class);
        }
    }

    // endregion

    // region removeAll() and reset() tests

    /**
     * Test removeAll() removes all quantity types.
     */
    public function testRemoveAllRemovesAllQuantityTypes(): void
    {
        // Ensure registry is initialized with default types.
        $before = QuantityTypeService::getAll();
        $this->assertNotEmpty($before);

        // Clear the registry.
        QuantityTypeService::removeAll();

        // Verify the registry is empty.
        $after = QuantityTypeService::getAll();
        $this->assertEmpty($after);

        // Reset to restore defaults for other tests.
        QuantityTypeService::reset();
    }

    /**
     * Test removeAll() does not trigger re-initialization.
     */
    public function testRemoveAllDoesNotReinitialize(): void
    {
        // Clear the registry.
        QuantityTypeService::removeAll();

        // Add a single custom type.
        QuantityTypeService::add('custom', 'L6', TestQuantity::class);

        // Verify only the custom type exists (defaults were not re-loaded).
        $all = QuantityTypeService::getAll();
        $this->assertCount(1, $all);
        $this->assertArrayHasKey('custom', $all);

        // Reset to restore defaults for other tests.
        QuantityTypeService::reset();
    }

    // endregion

    // region Data integrity tests

    /**
     * Test all quantity types have non-empty names.
     */
    public function testAllQuantityTypesHaveNames(): void
    {
        $all = QuantityTypeService::getAll();

        foreach ($all as $qtyType) {
            $this->assertNotEmpty($qtyType->name, "Quantity type $qtyType->dimension has empty name");
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
            $qtyType = QuantityTypeService::getByDimension($dimension);

            $this->assertNotNull($qtyType, "SI base dimension '$dimension' not found");
            $this->assertSame($expected['name'], $qtyType->name);
            $this->assertSame($expected['class'], $qtyType->class);
        }
    }

    // endregion
}
