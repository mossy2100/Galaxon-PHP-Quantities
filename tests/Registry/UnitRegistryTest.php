<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Registry;

use DomainException;
use Galaxon\Quantities\Internal\Unit;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for UnitRegistry class.
 */
#[CoversClass(UnitRegistry::class)]
final class UnitRegistryTest extends TestCase
{
    // region getAll() tests

    /**
     * Test getAll() returns an array.
     */
    public function testGetAllReturnsArray(): void
    {
        $result = UnitRegistry::getAll();

        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertIsArray($result);
    }

    /**
     * Test getAll() returns Unit objects.
     */
    public function testGetAllReturnsUnitObjects(): void
    {
        $result = UnitRegistry::getAll();

        foreach ($result as $name => $unit) {
            $this->assertIsString($name);
            $this->assertInstanceOf(Unit::class, $unit);
        }
    }

    /**
     * Test getAll() contains SI base units.
     */
    public function testGetAllContainsSiBaseUnits(): void
    {
        $result = UnitRegistry::getAll();

        $this->assertArrayHasKey('meter', $result);
        $this->assertArrayHasKey('gram', $result);  // SI uses kilogram, but gram is the base unit
        $this->assertArrayHasKey('second', $result);
        $this->assertArrayHasKey('ampere', $result);
        $this->assertArrayHasKey('kelvin', $result);
        $this->assertArrayHasKey('mole', $result);
        $this->assertArrayHasKey('candela', $result);
    }

    /**
     * Test getAll() units have correct symbols.
     */
    public function testGetAllUnitsHaveCorrectSymbols(): void
    {
        $result = UnitRegistry::getAll();

        $this->assertSame('m', $result['meter']->asciiSymbol);
        $this->assertSame('g', $result['gram']->asciiSymbol);
        $this->assertSame('s', $result['second']->asciiSymbol);
    }

    // endregion

    // region getBySymbol() tests

    /**
     * Test getBySymbol() returns Unit for valid ASCII symbol.
     */
    public function testGetBySymbolReturnsUnitForAsciiSymbol(): void
    {
        $result = UnitRegistry::getBySymbol('m');

        $this->assertInstanceOf(Unit::class, $result);
        $this->assertSame('meter', $result->name);
        $this->assertSame('m', $result->asciiSymbol);
    }

    /**
     * Test getBySymbol() returns Unit for Unicode symbol.
     */
    public function testGetBySymbolReturnsUnitForUnicodeSymbol(): void
    {
        // Ohm has Unicode symbol Ω
        $result = UnitRegistry::getBySymbol('Ω');

        $this->assertInstanceOf(Unit::class, $result);
        $this->assertSame('ohm', $result->name);
    }

    /**
     * Test getBySymbol() returns null for unknown symbol.
     */
    public function testGetBySymbolReturnsNullForUnknown(): void
    {
        $result = UnitRegistry::getBySymbol('xyz');

        $this->assertNull($result);
    }

    /**
     * Test getBySymbol() is case sensitive.
     */
    public function testGetBySymbolIsCaseSensitive(): void
    {
        // 'm' is meter, 'M' is mega prefix (not a unit by itself)
        $meter = UnitRegistry::getBySymbol('m');
        $this->assertInstanceOf(Unit::class, $meter);
        $this->assertSame('meter', $meter->name);

        // 'M' alone is not a unit
        $upper = UnitRegistry::getBySymbol('M');
        $this->assertNull($upper);
    }

    // endregion

    // region getAllValidSymbols() tests

    /**
     * Test getAllValidSymbols() returns array of strings.
     */
    public function testGetAllValidSymbolsReturnsArrayOfStrings(): void
    {
        $result = UnitRegistry::getAllSymbols();

        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertIsArray($result);

        foreach ($result as $symbol) {
            $this->assertIsString($symbol);
        }
    }

    /**
     * Test getAllValidSymbols() includes base unit symbols.
     */
    public function testGetAllValidSymbolsIncludesBaseSymbols(): void
    {
        $result = UnitRegistry::getAllSymbols();

        $this->assertContains('m', $result);
        $this->assertContains('kg', $result);
        $this->assertContains('s', $result);
    }

    /**
     * Test getAllValidSymbols() includes prefixed symbols.
     */
    public function testGetAllValidSymbolsIncludesPrefixedSymbols(): void
    {
        $result = UnitRegistry::getAllSymbols();

        // Meter should have metric prefixes
        $this->assertContains('km', $result);   // kilo
        $this->assertContains('cm', $result);   // centi
        $this->assertContains('mm', $result);   // milli
        $this->assertContains('μm', $result);   // micro
        $this->assertContains('nm', $result);   // nano
    }

    /**
     * Test getAllValidSymbols() includes Unicode symbols.
     */
    public function testGetAllValidSymbolsIncludesUnicodeSymbols(): void
    {
        $result = UnitRegistry::getAllSymbols();

        // Ohm has Unicode symbol
        $this->assertContains('Ω', $result);
    }

    // endregion

    // region add() tests

    /**
     * Test add() creates a new unit.
     */
    public function testAddCreatesNewUnit(): void
    {
        // Use a unique name that doesn't exist
        $name = 'testunitadd';
        $symbol = 'tua';

        UnitRegistry::add(new Unit(
            name: $name,
            asciiSymbol: $symbol,
            unicodeSymbol: 'τ',  // Greek letter (valid Unicode character)
            dimension: 'L',
            systems: [System::Common]
        ));

        $all = UnitRegistry::getAll();
        $this->assertArrayHasKey($name, $all);
        $this->assertSame('L', $all[$name]->dimension);

        // Clean up
        UnitRegistry::remove($name);
    }

    /**
     * Test add() throws when Unicode symbol conflicts with existing Unicode symbol.
     */
    public function testAddThrowsForDuplicateUnicodeSymbol(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("The symbol 'Ω' for newunitohm is already being used by another unit");

        UnitRegistry::add(new Unit(
            name: 'newunitohm',
            asciiSymbol: 'nuo',
            unicodeSymbol: 'Ω',  // Conflicts with ohm's Unicode symbol
            dimension: 'T-3L2MI-2',
            systems: [System::Common]
        ));
    }

    /**
     * Test add() with all parameters.
     */
    public function testAddWithAllParameters(): void
    {
        $name = 'fullunitparams';
        $symbol = 'fup';

        UnitRegistry::add(new Unit(
            name: $name,
            asciiSymbol: $symbol,
            unicodeSymbol: 'φ',  // Greek letter (valid Unicode letter)
            dimension: 'L',
            prefixGroup: PrefixRegistry::GROUP_METRIC,
            systems: [System::Common]
        ));

        $unit = UnitRegistry::getAll()[$name];
        $this->assertSame($symbol, $unit->asciiSymbol);
        $this->assertContains(System::Common, $unit->systems);
        $this->assertSame(PrefixRegistry::GROUP_METRIC, $unit->prefixGroup);

        // Clean up
        UnitRegistry::remove($name);
    }

    public function testAddAfterReset(): void
    {
        UnitRegistry::clear();

        $name = 'fullunitparams';
        $symbol = 'fup';

        UnitRegistry::add(new Unit(
            name: $name,
            asciiSymbol: $symbol,
            unicodeSymbol: 'φ',
            dimension: 'L',
            prefixGroup: PrefixRegistry::GROUP_METRIC,
            systems: [System::Common]
        ));

        $units = UnitRegistry::getAll();
        $this->assertEquals(1, count($units));
    }

    /**
     * Test add() auto-initializes the registry when it is null.
     */
    public function testAddAutoInitializesNullRegistry(): void
    {
        // Reset sets $units to null, triggering init() on next add().
        UnitRegistry::reset();

        $name = 'autoinitunit';
        $symbol = 'aiu';

        UnitRegistry::add(new Unit(
            name: $name,
            asciiSymbol: $symbol,
            dimension: 'L',
            systems: [System::Common]
        ));

        // The registry should have been initialized with default units plus our new one.
        $this->assertTrue(UnitRegistry::has('meter'));
        $this->assertTrue(UnitRegistry::has($name));

        // Clean up.
        UnitRegistry::remove($name);
    }

    /**
     * Test add() throws an existing unit with the same name.
     */
    public function testAddThrowsExistingUnitWithSameName(): void
    {
        $name = 'replacetest';

        // Add a unit.
        UnitRegistry::add(new Unit(
            name: $name,
            asciiSymbol: 'rpt',
            dimension: 'L',
            systems: [System::Common]
        ));
        $this->assertSame('rpt', UnitRegistry::getAll()[$name]->asciiSymbol);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Unit '$name' already exists in the registry");

        try {
            // Add again with the same name but different symbol.
            // Specify to throw an exception if a unit with the same name already exists.
            UnitRegistry::add(new Unit(
                name: $name,
                asciiSymbol: 'rpx',
                dimension: 'L',
                systems: [System::Common]
            ), UnitRegistry::ON_DUPLICATE_THROW);
        }
        finally {
            // Clean up.
            UnitRegistry::remove($name);
        }
    }

    /**
     * Test add() replaces an existing unit with the same name.
     */
    public function testAddReplacesExistingUnitWithSameName(): void
    {
        $name = 'replacetest';

        // Add a unit.
        UnitRegistry::add(new Unit(
            name: $name,
            asciiSymbol: 'rpt',
            dimension: 'L',
            systems: [System::Common]
        ));
        $this->assertSame('rpt', UnitRegistry::getAll()[$name]->asciiSymbol);

        // Add again with the same name but different symbol.
        // Specify to replace any existing unit with the same name.
        UnitRegistry::add(new Unit(
            name: $name,
            asciiSymbol: 'rpx',
            dimension: 'L',
            systems: [System::Common]
        ), UnitRegistry::ON_DUPLICATE_REPLACE);

        // Should have the new symbol.
        $this->assertSame('rpx', UnitRegistry::getAll()[$name]->asciiSymbol);

        // Clean up.
        UnitRegistry::remove($name);
    }

    /**
     * Test add() skips an existing unit with the same name.
     */
    public function testAddSkipsExistingUnitWithSameName(): void
    {
        $name = 'replacetest';

        // Add a unit.
        UnitRegistry::add(new Unit(
            name: $name,
            asciiSymbol: 'rpt',
            dimension: 'L',
            systems: [System::Common]
        ));
        $this->assertSame('rpt', UnitRegistry::getAll()[$name]->asciiSymbol);

        // Add again with the same name but different symbol.
        // Specify to skip the operation if an existing unit is found with the same name.
        UnitRegistry::add(new Unit(
            name: $name,
            asciiSymbol: 'rpx',
            dimension: 'L',
            systems: [System::Common]
        ), UnitRegistry::ON_DUPLICATE_SKIP);

        // Should have the old symbol.
        $this->assertSame('rpt', UnitRegistry::getAll()[$name]->asciiSymbol);

        // Clean up.
        UnitRegistry::remove($name);
    }

    // endregion

    // region remove() tests

    /**
     * Test remove() removes a unit.
     */
    public function testRemoveRemovesUnit(): void
    {
        // First add a unit
        $name = 'removetest';
        $symbol = 'rmt';
        UnitRegistry::add(new Unit(
            name: $name,
            asciiSymbol: $symbol,
            unicodeSymbol: 'ρ',  // Greek letter
            dimension: 'L',
            systems: [System::Common]
        ));

        // Verify it exists
        $this->assertArrayHasKey($name, UnitRegistry::getAll());

        // Remove it
        UnitRegistry::remove($name);

        // Verify it's gone
        $this->assertArrayNotHasKey($name, UnitRegistry::getAll());
    }

    /**
     * Test remove() does nothing for non-existent unit.
     */
    public function testRemoveDoesNothingForNonExistent(): void
    {
        $countBefore = count(UnitRegistry::getAll());

        UnitRegistry::remove('nonexistent_unit_xyz');

        $countAfter = count(UnitRegistry::getAll());
        $this->assertSame($countBefore, $countAfter);
    }

    /**
     * Test remove() handles uninitialized registry gracefully.
     */
    public function testRemoveHandlesUninitializedRegistry(): void
    {
        // Reset the registry to null state.
        UnitRegistry::reset();

        // This should not throw, just return early.
        UnitRegistry::remove('meter');

        // Re-initialize by accessing the registry.
        $result = UnitRegistry::getBySymbol('m');
        $this->assertInstanceOf(Unit::class, $result);
    }

    // endregion

    // region has() tests

    /**
     * Test has() returns true for existing unit.
     */
    public function testHasReturnsTrueForExistingUnit(): void
    {
        $result = UnitRegistry::has('meter');

        $this->assertTrue($result);
    }

    /**
     * Test has() returns true for other SI base units.
     */
    public function testHasReturnsTrueForOtherSiUnits(): void
    {
        $this->assertTrue(UnitRegistry::has('gram'));
        $this->assertTrue(UnitRegistry::has('second'));
        $this->assertTrue(UnitRegistry::has('ampere'));
        $this->assertTrue(UnitRegistry::has('kelvin'));
    }

    /**
     * Test has() returns false for non-existing unit.
     */
    public function testHasReturnsFalseForNonExistingUnit(): void
    {
        $result = UnitRegistry::has('nonexistent_unit_xyz');

        $this->assertFalse($result);
    }

    /**
     * Test has() is case sensitive.
     */
    public function testHasIsCaseSensitive(): void
    {
        // 'meter' exists, 'Meter' and 'METER' should not
        $this->assertTrue(UnitRegistry::has('meter'));
        $this->assertFalse(UnitRegistry::has('Meter'));
        $this->assertFalse(UnitRegistry::has('METER'));
    }

    // endregion

    // region loadSystem() tests

    /**
     * Test loadSystem() skips loading when the system is already loaded.
     */
    public function testLoadSystemSkipsAlreadyLoadedSystem(): void
    {
        // SI is auto-loaded during init(). Loading it again should be a no-op.
        $countBefore = count(UnitRegistry::getAll());

        UnitRegistry::loadSystem(System::Si);

        $countAfter = count(UnitRegistry::getAll());
        $this->assertSame($countBefore, $countAfter);
    }

    // endregion

    // region getLoadedSystems() tests

    /**
     * Test getLoadedSystems() returns default systems after initialization.
     */
    public function testGetLoadedSystemsReturnsDefaultSystems(): void
    {
        $systems = UnitRegistry::getLoadedSystems();

        $this->assertContains(System::Si, $systems);
        $this->assertContains(System::SiAccepted, $systems);
        $this->assertContains(System::Common, $systems);
    }

    /**
     * Test getLoadedSystems() includes manually loaded system.
     */
    public function testGetLoadedSystemsIncludesManuallyLoadedSystem(): void
    {
        UnitRegistry::loadSystem(System::Imperial);

        $systems = UnitRegistry::getLoadedSystems();

        $this->assertContains(System::Imperial, $systems);
    }

    /**
     * Test getLoadedSystems() is empty after clear().
     */
    public function testGetLoadedSystemsEmptyAfterClear(): void
    {
        UnitRegistry::clear();

        $systems = UnitRegistry::getLoadedSystems();

        $this->assertEmpty($systems);

        // Reset the registry to null so it automatically re-initializes on next access.
        UnitRegistry::reset();
    }

    // endregion

    // region Data integrity tests

    /**
     * Test all units have required properties.
     */
    public function testAllUnitsHaveRequiredProperties(): void
    {
        $all = UnitRegistry::getAll();

        foreach ($all as $name => $unit) {
            $this->assertNotEmpty($unit->name, "Unit '$name' has empty name");
            $this->assertNotEmpty($unit->dimension, "Unit '$name' has empty dimension");
            $this->assertNotEmpty($unit->systems, "Unit '$name' has empty system");
        }
    }

    /**
     * Test SI base units have correct dimensions.
     */
    public function testSiBaseUnitsHaveCorrectDimensions(): void
    {
        $expected = [
            'meter'   => 'L',
            'gram'    => 'M',    // SI uses kilogram, but gram is the base unit in registry
            'second'  => 'T',
            'ampere'  => 'I',
            'kelvin'  => 'H',
            'mole'    => 'N',
            'candela' => 'J',
        ];

        $all = UnitRegistry::getAll();

        foreach ($expected as $name => $dimension) {
            $this->assertArrayHasKey($name, $all, "SI base unit '$name' not found");
            $this->assertSame($dimension, $all[$name]->dimension, "Unit '$name' has wrong dimension");
        }
    }

    /**
     * Test unit names are unique.
     */
    public function testUnitNamesAreUnique(): void
    {
        $all = UnitRegistry::getAll();
        $names = array_keys($all);
        $uniqueNames = array_unique($names);

        $this->assertCount(count($names), $uniqueNames, 'Duplicate unit names found');
    }

    /**
     * Test ASCII symbols are unique.
     */
    public function testAsciiSymbolsAreUnique(): void
    {
        $all = UnitRegistry::getAll();
        $symbols = array_map(static fn ($unit) => $unit->asciiSymbol, $all);
        $uniqueSymbols = array_unique($symbols);

        $this->assertCount(count($symbols), $uniqueSymbols, 'Duplicate ASCII symbols found');
    }

    // endregion
}
