<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Services;

use DomainException;
use Galaxon\Quantities\Internal\Unit;
use Galaxon\Quantities\Services\PrefixService;
use Galaxon\Quantities\Services\UnitService;
use Galaxon\Quantities\UnitSystem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for UnitService class.
 */
#[CoversClass(UnitService::class)]
final class UnitServiceTest extends TestCase
{
    // region getAll() tests

    /**
     * Test getAll() returns an array.
     */
    public function testGetAllReturnsArray(): void
    {
        $result = UnitService::getAll();

        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertIsArray($result);
    }

    /**
     * Test getAll() returns Unit objects.
     */
    public function testGetAllReturnsUnitObjects(): void
    {
        $result = UnitService::getAll();

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
        $result = UnitService::getAll();

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
        $result = UnitService::getAll();

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
        $result = UnitService::getBySymbol('m');

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
        $result = UnitService::getBySymbol('Ω');

        $this->assertInstanceOf(Unit::class, $result);
        $this->assertSame('ohm', $result->name);
    }

    /**
     * Test getBySymbol() returns null for unknown symbol.
     */
    public function testGetBySymbolReturnsNullForUnknown(): void
    {
        $result = UnitService::getBySymbol('xyz');

        $this->assertNull($result);
    }

    /**
     * Test getBySymbol() is case sensitive.
     */
    public function testGetBySymbolIsCaseSensitive(): void
    {
        // 'm' is meter, 'M' is mega prefix (not a unit by itself)
        $meter = UnitService::getBySymbol('m');
        $this->assertInstanceOf(Unit::class, $meter);
        $this->assertSame('meter', $meter->name);

        // 'M' alone is not a unit
        $upper = UnitService::getBySymbol('M');
        $this->assertNull($upper);
    }

    // endregion

    // region getBySystem() tests

    /**
     * Test getBySystem() returns an array of Unit objects.
     */
    public function testGetBySystemReturnsUnitObjects(): void
    {
        $result = UnitService::getBySystem(UnitSystem::Si);

        $this->assertNotEmpty($result);
        foreach ($result as $unit) {
            $this->assertInstanceOf(Unit::class, $unit);
        }
    }

    /**
     * Test getBySystem() returns only units belonging to the specified system.
     */
    public function testGetBySystemReturnsOnlyMatchingUnits(): void
    {
        $result = UnitService::getBySystem(UnitSystem::Si);

        foreach ($result as $unit) {
            $this->assertTrue(
                $unit->belongsToSystem(UnitSystem::Si),
                "Unit '$unit->name' does not belong to SI system."
            );
        }
    }

    /**
     * Test getBySystem() includes SI base units for SI system.
     */
    public function testGetBySystemIncludesSiBaseUnits(): void
    {
        $result = UnitService::getBySystem(UnitSystem::Si);
        $symbols = array_map(static fn (Unit $u) => $u->asciiSymbol, $result);

        $this->assertContains('m', $symbols);
        $this->assertContains('g', $symbols);
        $this->assertContains('s', $symbols);
        $this->assertContains('A', $symbols);
        $this->assertContains('K', $symbols);
        $this->assertContains('mol', $symbols);
        $this->assertContains('cd', $symbols);
    }

    /**
     * Test getBySystem() includes named derived SI units.
     */
    public function testGetBySystemIncludesNamedDerivedSiUnits(): void
    {
        $result = UnitService::getBySystem(UnitSystem::Si);
        $symbols = array_map(static fn (Unit $u) => $u->asciiSymbol, $result);

        $this->assertContains('N', $symbols);
        $this->assertContains('J', $symbols);
        $this->assertContains('W', $symbols);
        $this->assertContains('Pa', $symbols);
        $this->assertContains('Hz', $symbols);
    }

    /**
     * Test getBySystem() does not include Imperial units in SI results.
     */
    public function testGetBySystemExcludesOtherSystems(): void
    {
        $result = UnitService::getBySystem(UnitSystem::Si);
        $symbols = array_map(static fn (Unit $u) => $u->asciiSymbol, $result);

        $this->assertNotContains('ft', $symbols);
        $this->assertNotContains('lb', $symbols);
        $this->assertNotContains('mi', $symbols);
    }

    /**
     * Test getBySystem() returns Imperial units when requested.
     */
    public function testGetBySystemReturnsImperialUnits(): void
    {
        // Ensure Imperial is loaded.
        UnitService::loadBySystem(UnitSystem::Imperial);

        $result = UnitService::getBySystem(UnitSystem::Imperial);
        $symbols = array_map(static fn (Unit $u) => $u->asciiSymbol, $result);

        $this->assertContains('ft', $symbols);
        $this->assertContains('lb', $symbols);
        $this->assertContains('mi', $symbols);
    }

    /**
     * Test getBySystem() returns different results for different systems.
     */
    public function testGetBySystemReturnsDifferentResultsPerSystem(): void
    {
        $si = UnitService::getBySystem(UnitSystem::Si);
        $imperial = UnitService::getBySystem(UnitSystem::Imperial);

        $siSymbols = array_map(static fn (Unit $u) => $u->asciiSymbol, $si);
        $imperialSymbols = array_map(static fn (Unit $u) => $u->asciiSymbol, $imperial);

        // The two systems should not be identical.
        $this->assertNotEquals($siSymbols, $imperialSymbols);
    }

    /**
     * Test getBySystem() returns a sequential list (not associative).
     */
    public function testGetBySystemReturnsSequentialList(): void
    {
        $result = UnitService::getBySystem(UnitSystem::Si);

        $this->assertSame(array_values($result), $result);
    }

    // endregion

    // region getAllValidSymbols() tests

    /**
     * Test getAllValidSymbols() returns array of strings.
     */
    public function testGetAllValidSymbolsReturnsArrayOfStrings(): void
    {
        $result = UnitService::getAllSymbols();

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
        $result = UnitService::getAllSymbols();

        $this->assertContains('m', $result);
        $this->assertContains('kg', $result);
        $this->assertContains('s', $result);
    }

    /**
     * Test getAllValidSymbols() includes prefixed symbols.
     */
    public function testGetAllValidSymbolsIncludesPrefixedSymbols(): void
    {
        $result = UnitService::getAllSymbols();

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
        $result = UnitService::getAllSymbols();

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
        $unit = new Unit(
            name: 'testunitadd',
            asciiSymbol: 'tua',
            dimension: 'L',
            systems: [UnitSystem::Common],
            unicodeSymbol: 'τ'  // Greek letter (valid Unicode character)
        );

        UnitService::add($unit);

        $all = UnitService::getAll();
        $this->assertArrayHasKey($unit->name, $all);
        $this->assertSame('L', $all[$unit->name]->dimension);

        // Clean up
        UnitService::remove($unit);
    }

    /**
     * Test add() throws when Unicode symbol conflicts with existing Unicode symbol.
     */
    public function testAddThrowsForDuplicateUnicodeSymbol(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("The symbol 'Ω' for newunitohm is already being used by another unit");

        UnitService::add(new Unit(
            name: 'newunitohm',
            asciiSymbol: 'nuo',
            dimension: 'T-3L2MI-2',
            systems: [UnitSystem::Common],
            unicodeSymbol: 'Ω'  // Conflicts with ohm's Unicode symbol
        ));
    }

    /**
     * Test add() with all parameters.
     */
    public function testAddWithAllParameters(): void
    {
        $unit = new Unit(
            name: 'fullunitparams',
            asciiSymbol: 'fup',
            dimension: 'L',
            systems: [UnitSystem::Common],
            prefixGroup: PrefixService::GROUP_METRIC,
            unicodeSymbol: 'φ'  // Greek letter (valid Unicode letter)
        );

        UnitService::add($unit);

        $retrieved = UnitService::getAll()[$unit->name];
        $this->assertSame('fup', $retrieved->asciiSymbol);
        $this->assertContains(UnitSystem::Common, $retrieved->systems);
        $this->assertSame(PrefixService::GROUP_METRIC, $retrieved->prefixGroup);

        // Clean up
        UnitService::remove($unit);
    }

    public function testAddAfterReset(): void
    {
        UnitService::clear();

        $name = 'fullunitparams';
        $symbol = 'fup';

        UnitService::add(new Unit(
            name: $name,
            asciiSymbol: $symbol,
            dimension: 'L',
            systems: [UnitSystem::Common],
            prefixGroup: PrefixService::GROUP_METRIC,
            unicodeSymbol: 'φ'
        ));

        $units = UnitService::getAll();
        $this->assertEquals(1, count($units));
    }

    /**
     * Test add() auto-initializes the registry when it is null.
     */
    public function testAddAutoInitializesNullService(): void
    {
        // Reset sets $units to null, triggering init() on next add().
        UnitService::reset();

        $unit = new Unit(
            name: 'autoinitunit',
            asciiSymbol: 'aiu',
            dimension: 'L',
            systems: [UnitSystem::Common]
        );

        UnitService::add($unit);

        // The registry should have been initialized with default units plus our new one.
        $this->assertTrue(UnitService::has('meter'));
        $this->assertTrue(UnitService::has($unit->name));

        // Clean up.
        UnitService::remove($unit);
    }

    /**
     * Test add() replaces an existing unit with the same name.
     */
    public function testAddReplacesExistingUnitWithSameName(): void
    {
        $name = 'replacetest';

        // Add a unit.
        $unit = new Unit(
            name: $name,
            asciiSymbol: 'rpt',
            dimension: 'L',
            systems: [UnitSystem::Common]
        );
        UnitService::add($unit);
        $this->assertSame('rpt', UnitService::getAll()[$name]->asciiSymbol);

        // Add again with the same name but different symbol.
        // Specify to replace any existing unit with the same name.
        $replacement = new Unit(
            name: $name,
            asciiSymbol: 'rpx',
            dimension: 'L',
            systems: [UnitSystem::Common]
        );
        UnitService::add($replacement, true);

        // Should have the new symbol.
        $this->assertSame('rpx', UnitService::getAll()[$name]->asciiSymbol);

        // Clean up.
        UnitService::remove($replacement);
    }

    /**
     * Test add() skips an existing unit with the same name.
     */
    public function testAddSkipsExistingUnitWithSameName(): void
    {
        $name = 'replacetest';

        // Add a unit.
        $unit = new Unit(
            name: $name,
            asciiSymbol: 'rpt',
            dimension: 'L',
            systems: [UnitSystem::Common]
        );
        UnitService::add($unit);
        $this->assertSame('rpt', UnitService::getAll()[$name]->asciiSymbol);

        // Add again with the same name but different symbol.
        // Specify to skip the operation if an existing unit is found with the same name.
        UnitService::add(new Unit(
            name: $name,
            asciiSymbol: 'rpx',
            dimension: 'L',
            systems: [UnitSystem::Common]
        ));

        // Should have the old symbol.
        $this->assertSame('rpt', UnitService::getAll()[$name]->asciiSymbol);

        // Clean up.
        UnitService::remove($unit);
    }

    // endregion

    // region remove() tests

    /**
     * Test remove() removes a unit.
     */
    public function testRemoveRemovesUnit(): void
    {
        // First add a unit.
        $unit = new Unit(
            name: 'removetest',
            asciiSymbol: 'rmt',
            dimension: 'L',
            systems: [UnitSystem::Common],
            unicodeSymbol: 'ρ'  // Greek letter
        );
        UnitService::add($unit);

        // Verify it exists.
        $this->assertArrayHasKey($unit->name, UnitService::getAll());

        // Remove it.
        UnitService::remove($unit);

        // Verify it's gone.
        $this->assertArrayNotHasKey($unit->name, UnitService::getAll());
    }

    /**
     * Test remove() does nothing for non-existent unit.
     */
    public function testRemoveDoesNothingForNonExistent(): void
    {
        $countBefore = count(UnitService::getAll());

        // Create a unit that was never added to the registry.
        $unit = new Unit(
            name: 'nonexistent_unit_xyz',
            asciiSymbol: 'nex',
            dimension: 'L',
            systems: [UnitSystem::Common]
        );
        UnitService::remove($unit);

        $countAfter = count(UnitService::getAll());
        $this->assertSame($countBefore, $countAfter);
    }

    /**
     * Test remove() handles uninitialized registry gracefully.
     */
    public function testRemoveHandlesUninitializedService(): void
    {
        // Reset the registry to null state.
        UnitService::reset();

        // Create a unit to pass to remove() - the registry is null so it should return early.
        $unit = new Unit(
            name: 'meter',
            asciiSymbol: 'm',
            dimension: 'L',
            systems: [UnitSystem::Si]
        );
        UnitService::remove($unit);

        // Re-initialize by accessing the registry.
        $result = UnitService::getBySymbol('m');
        $this->assertInstanceOf(Unit::class, $result);
    }

    // endregion

    // region has() tests

    /**
     * Test has() returns true for existing unit.
     */
    public function testHasReturnsTrueForExistingUnit(): void
    {
        $result = UnitService::has('meter');

        $this->assertTrue($result);
    }

    /**
     * Test has() returns true for other SI base units.
     */
    public function testHasReturnsTrueForOtherSiUnits(): void
    {
        $this->assertTrue(UnitService::has('gram'));
        $this->assertTrue(UnitService::has('second'));
        $this->assertTrue(UnitService::has('ampere'));
        $this->assertTrue(UnitService::has('kelvin'));
    }

    /**
     * Test has() returns false for non-existing unit.
     */
    public function testHasReturnsFalseForNonExistingUnit(): void
    {
        $result = UnitService::has('nonexistent_unit_xyz');

        $this->assertFalse($result);
    }

    /**
     * Test has() is case sensitive.
     */
    public function testHasIsCaseSensitive(): void
    {
        // 'meter' exists, 'Meter' and 'METER' should not
        $this->assertTrue(UnitService::has('meter'));
        $this->assertFalse(UnitService::has('Meter'));
        $this->assertFalse(UnitService::has('METER'));
    }

    // endregion

    // region loadSystem() tests

    /**
     * Test loadSystem() skips loading when the system is already loaded.
     */
    public function testLoadSystemSkipsAlreadyLoadedSystem(): void
    {
        // SI is auto-loaded during init(). Loading it again should be a no-op.
        $countBefore = count(UnitService::getAll());

        UnitService::loadBySystem(UnitSystem::Si);

        $countAfter = count(UnitService::getAll());
        $this->assertSame($countBefore, $countAfter);
    }

    // endregion

    // region getLoadedSystems() tests

    /**
     * Test getLoadedSystems() returns default systems after initialization.
     */
    public function testGetLoadedSystemsReturnsDefaultSystems(): void
    {
        $systems = UnitService::getLoadedSystems();

        $this->assertContains(UnitSystem::Si, $systems);
        $this->assertContains(UnitSystem::SiAccepted, $systems);
        $this->assertContains(UnitSystem::Common, $systems);
    }

    /**
     * Test getLoadedSystems() includes manually loaded system.
     */
    public function testGetLoadedSystemsIncludesManuallyLoadedSystem(): void
    {
        UnitService::loadBySystem(UnitSystem::Imperial);

        $systems = UnitService::getLoadedSystems();

        $this->assertContains(UnitSystem::Imperial, $systems);
    }

    /**
     * Test getLoadedSystems() is empty after clear().
     */
    public function testGetLoadedSystemsEmptyAfterClear(): void
    {
        UnitService::clear();

        $systems = UnitService::getLoadedSystems();

        $this->assertEmpty($systems);

        // Reset the registry to null so it automatically re-initializes on next access.
        UnitService::reset();
    }

    // endregion

    // region Data integrity tests

    /**
     * Test all units have required properties.
     */
    public function testAllUnitsHaveRequiredProperties(): void
    {
        $all = UnitService::getAll();

        foreach ($all as $name => $unit) {
            $this->assertNotEmpty($unit->name, "Unit '$name' has empty name");
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

        $all = UnitService::getAll();

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
        $all = UnitService::getAll();
        $names = array_keys($all);
        $uniqueNames = array_unique($names);

        $this->assertCount(count($names), $uniqueNames, 'Duplicate unit names found');
    }

    /**
     * Test ASCII symbols are unique.
     */
    public function testAsciiSymbolsAreUnique(): void
    {
        $all = UnitService::getAll();
        $symbols = array_map(static fn ($unit) => $unit->asciiSymbol, $all);
        $uniqueSymbols = array_unique($symbols);

        $this->assertCount(count($symbols), $uniqueSymbols, 'Duplicate ASCII symbols found');
    }

    // endregion
}
