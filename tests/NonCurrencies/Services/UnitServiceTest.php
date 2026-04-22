<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\NonCurrencies\Services;

use DomainException;
use Galaxon\Quantities\Internal\Unit;
use Galaxon\Quantities\Internal\UnitSystem;
use Galaxon\Quantities\Services\PrefixService;
use Galaxon\Quantities\Services\QuantityTypeService;
use Galaxon\Quantities\Services\UnitService;
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

    // region getByName() tests

    /**
     * Test getByName() returns Unit for a valid name.
     */
    public function testGetByNameReturnsUnitForValidName(): void
    {
        $result = UnitService::getByName('meter');

        $this->assertInstanceOf(Unit::class, $result);
        $this->assertSame('meter', $result->name);
        $this->assertSame('m', $result->asciiSymbol);
    }

    /**
     * Test getByName() returns null for unknown name.
     */
    public function testGetByNameReturnsNullForUnknown(): void
    {
        $this->assertNull(UnitService::getByName('nonexistent_xyz'));
    }

    /**
     * Test getByName() is case sensitive.
     */
    public function testGetByNameIsCaseSensitive(): void
    {
        $this->assertInstanceOf(Unit::class, UnitService::getByName('meter'));
        $this->assertNull(UnitService::getByName('Meter'));
        $this->assertNull(UnitService::getByName('METER'));
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
        // Ohm has Unicode symbol Ω.
        $result = UnitService::getBySymbol('Ω');

        $this->assertInstanceOf(Unit::class, $result);
        $this->assertSame('ohm', $result->name);
    }

    /**
     * Test getBySymbol() returns Unit for prefixed symbol.
     */
    public function testGetBySymbolReturnsUnitForPrefixedSymbol(): void
    {
        $result = UnitService::getBySymbol('km');

        $this->assertInstanceOf(Unit::class, $result);
        $this->assertSame('meter', $result->name);
    }

    /**
     * Test getBySymbol() returns null for unknown symbol.
     */
    public function testGetBySymbolReturnsNullForUnknown(): void
    {
        $this->assertNull(UnitService::getBySymbol('xyz'));
    }

    /**
     * Test getBySymbol() is case sensitive.
     */
    public function testGetBySymbolIsCaseSensitive(): void
    {
        // 'm' is meter, 'M' alone is not a unit.
        $meter = UnitService::getBySymbol('m');
        $this->assertInstanceOf(Unit::class, $meter);
        $this->assertSame('meter', $meter->name);

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
     * Test getBySystem() returns Imperial units when loaded.
     */
    public function testGetBySystemReturnsImperialUnits(): void
    {
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

    // region getByQuantityType() tests

    /**
     * Test getByQuantityType() returns units for a known quantity type.
     */
    public function testGetByQuantityTypeReturnsUnits(): void
    {
        $lengthType = QuantityTypeService::getByName('length');
        $this->assertNotNull($lengthType);

        $result = UnitService::getByQuantityType($lengthType);

        $this->assertNotEmpty($result);
        foreach ($result as $unit) {
            $this->assertInstanceOf(Unit::class, $unit);
        }
    }

    /**
     * Test getByQuantityType() returns only units with matching dimension.
     */
    public function testGetByQuantityTypeReturnsOnlyMatchingUnits(): void
    {
        $timeType = QuantityTypeService::getByName('time');
        $this->assertNotNull($timeType);

        $result = UnitService::getByQuantityType($timeType);

        foreach ($result as $unit) {
            $this->assertSame(
                $timeType->dimension,
                $unit->dimension,
                "Unit '$unit->name' has dimension '$unit->dimension', expected '$timeType->dimension'."
            );
        }
    }

    /**
     * Test getByQuantityType() includes expected units for time.
     */
    public function testGetByQuantityTypeIncludesExpectedUnits(): void
    {
        $timeType = QuantityTypeService::getByName('time');
        $this->assertNotNull($timeType);

        $result = UnitService::getByQuantityType($timeType);
        $symbols = array_map(static fn (Unit $u) => $u->asciiSymbol, $result);

        $this->assertContains('s', $symbols);
        $this->assertContains('min', $symbols);
        $this->assertContains('h', $symbols);
    }

    /**
     * Test getByQuantityType() returns a sequential list.
     */
    public function testGetByQuantityTypeReturnsSequentialList(): void
    {
        $lengthType = QuantityTypeService::getByName('length');
        $this->assertNotNull($lengthType);

        $result = UnitService::getByQuantityType($lengthType);

        $this->assertSame(array_values($result), $result);
    }

    // endregion

    // region getAllSymbols() tests

    /**
     * Test getAllSymbols() returns array of strings.
     */
    public function testGetAllSymbolsReturnsArrayOfStrings(): void
    {
        $result = UnitService::getAllSymbols();

        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertIsArray($result);

        foreach ($result as $symbol) {
            $this->assertIsString($symbol);
        }
    }

    /**
     * Test getAllSymbols() includes base unit symbols.
     */
    public function testGetAllSymbolsIncludesBaseSymbols(): void
    {
        $result = UnitService::getAllSymbols();

        $this->assertContains('m', $result);
        $this->assertContains('kg', $result);
        $this->assertContains('s', $result);
    }

    /**
     * Test getAllSymbols() includes prefixed symbols.
     */
    public function testGetAllSymbolsIncludesPrefixedSymbols(): void
    {
        $result = UnitService::getAllSymbols();

        // Meter should have metric prefixes.
        $this->assertContains('km', $result);   // kilo
        $this->assertContains('cm', $result);   // centi
        $this->assertContains('mm', $result);   // milli
        $this->assertContains('μm', $result);   // micro (Unicode)
        $this->assertContains('nm', $result);   // nano
    }

    /**
     * Test getAllSymbols() includes Unicode symbols.
     */
    public function testGetAllSymbolsIncludesUnicodeSymbols(): void
    {
        $result = UnitService::getAllSymbols();

        // Ohm has Unicode symbol.
        $this->assertContains('Ω', $result);
    }

    // endregion

    // region add() tests

    /**
     * Test add() creates a new unit and returns true.
     */
    public function testAddCreatesNewUnit(): void
    {
        $unit = new Unit(
            name: 'testunitadd',
            asciiSymbol: 'tua',
            dimension: 'L',
            systems: [UnitSystem::Custom],
            unicodeSymbol: 'τ'
        );

        try {
            $result = UnitService::add($unit);

            $this->assertTrue($result);
            $this->assertArrayHasKey($unit->name, UnitService::getAll());
            $this->assertSame('L', UnitService::getAll()[$unit->name]->dimension);
        } finally {
            UnitService::remove($unit);
        }
    }

    /**
     * Test add() throws when a symbol conflicts with an existing unit's symbol.
     */
    public function testAddThrowsForDuplicateSymbol(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("The symbol 'Ω' for newunitohm is already being used by another unit");

        UnitService::add(new Unit(
            name: 'newunitohm',
            asciiSymbol: 'nuo',
            dimension: 'T-3L2MI-2',
            systems: [UnitSystem::Custom],
            unicodeSymbol: 'Ω'  // Conflicts with ohm's Unicode symbol.
        ));
    }

    /**
     * Test add() with all optional parameters.
     */
    public function testAddWithAllParameters(): void
    {
        $unit = new Unit(
            name: 'fullunitparams',
            asciiSymbol: 'fup',
            dimension: 'L',
            systems: [UnitSystem::Custom],
            prefixGroup: PrefixService::GROUP_METRIC,
            unicodeSymbol: 'φ'
        );

        try {
            UnitService::add($unit);

            $retrieved = UnitService::getAll()[$unit->name];
            $this->assertSame('fup', $retrieved->asciiSymbol);
            $this->assertContains(UnitSystem::Custom, $retrieved->systems);
            $this->assertSame(PrefixService::GROUP_METRIC, $retrieved->prefixGroup);
        } finally {
            UnitService::remove($unit);
        }
    }

    /**
     * Test add() into an empty registry (after removeAll) does not auto-initialize.
     */
    public function testAddIntoEmptyRegistry(): void
    {
        UnitService::removeAll();

        try {
            $unit = new Unit(
                name: 'onlyunit',
                asciiSymbol: 'onu',
                dimension: 'L',
                systems: [UnitSystem::Custom]
            );

            UnitService::add($unit);

            $this->assertSame(1, UnitService::count());
        } finally {
            UnitService::reset();
        }
    }

    /**
     * Test add() auto-initializes the registry when it is null (after reset).
     */
    public function testAddAutoInitializesNullRegistry(): void
    {
        UnitService::reset();

        $unit = new Unit(
            name: 'autoinitunit',
            asciiSymbol: 'aiu',
            dimension: 'L',
            systems: [UnitSystem::Custom]
        );

        try {
            UnitService::add($unit);

            // The registry should have been initialized with default units plus our new one.
            $this->assertTrue(UnitService::has('meter'));
            $this->assertTrue(UnitService::has($unit->name));
        } finally {
            UnitService::remove($unit);
        }
    }

    /**
     * Test add() replaces an existing unit when replaceExisting is true and returns true.
     */
    public function testAddReplacesExistingUnit(): void
    {
        $name = 'replacetest';

        $unit = new Unit(
            name: $name,
            asciiSymbol: 'rpt',
            dimension: 'L',
            systems: [UnitSystem::Custom]
        );

        try {
            UnitService::add($unit);
            $this->assertSame('rpt', UnitService::getAll()[$name]->asciiSymbol);

            // Replace with different symbol.
            $replacement = new Unit(
                name: $name,
                asciiSymbol: 'rpx',
                dimension: 'L',
                systems: [UnitSystem::Custom]
            );
            $result = UnitService::add($replacement, true);

            $this->assertTrue($result);
            $this->assertSame('rpx', UnitService::getAll()[$name]->asciiSymbol);
        } finally {
            UnitService::remove($name);
        }
    }

    /**
     * Test add() skips an existing unit when replaceExisting is false and returns false.
     */
    public function testAddSkipsExistingUnit(): void
    {
        $name = 'skiptest';

        $unit = new Unit(
            name: $name,
            asciiSymbol: 'skt',
            dimension: 'L',
            systems: [UnitSystem::Custom]
        );

        try {
            UnitService::add($unit);

            $result = UnitService::add(new Unit(
                name: $name,
                asciiSymbol: 'skx',
                dimension: 'L',
                systems: [UnitSystem::Custom]
            ));

            $this->assertFalse($result);
            $this->assertSame('skt', UnitService::getAll()[$name]->asciiSymbol);
        } finally {
            UnitService::remove($name);
        }
    }

    // endregion

    // region addFromDefinition() tests

    /**
     * Test addFromDefinition() creates a unit from a definition array.
     */
    public function testAddFromDefinitionCreatesUnit(): void
    {
        try {
            UnitService::addFromDefinition('testdefunit', [
                'asciiSymbol' => 'tdu',
                'dimension'   => 'L',
                'systems'     => [UnitSystem::Custom],
            ]);

            $unit = UnitService::getByName('testdefunit');
            $this->assertInstanceOf(Unit::class, $unit);
            $this->assertSame('tdu', $unit->asciiSymbol);
            $this->assertSame('L', $unit->dimension);
        } finally {
            UnitService::remove('testdefunit');
        }
    }

    /**
     * Test addFromDefinition() with all optional fields.
     */
    public function testAddFromDefinitionWithAllFields(): void
    {
        try {
            UnitService::addFromDefinition('fulldefunit', [
                'asciiSymbol'     => 'fdu',
                'dimension'       => 'T',
                'systems'         => [UnitSystem::Custom],
                'prefixGroup'     => PrefixService::GROUP_METRIC,
                'unicodeSymbol'   => 'ψ',
                'alternateSymbol' => '#',
            ]);

            $unit = UnitService::getByName('fulldefunit');
            $this->assertInstanceOf(Unit::class, $unit);
            $this->assertSame(PrefixService::GROUP_METRIC, $unit->prefixGroup);

            // Unicode and alternate symbols should be in the symbols map.
            $this->assertIsArray($unit->symbols);
            $this->assertArrayHasKey('ψ', $unit->symbols);
            $this->assertArrayHasKey('#', $unit->symbols);
        } finally {
            UnitService::remove('fulldefunit');
        }
    }

    // endregion

    // region remove() tests

    /**
     * Test remove() removes a unit by Unit object.
     */
    public function testRemoveByUnitObject(): void
    {
        $unit = new Unit(
            name: 'removetest',
            asciiSymbol: 'rmt',
            dimension: 'L',
            systems: [UnitSystem::Custom]
        );
        UnitService::add($unit);
        $this->assertTrue(UnitService::has('removetest'));

        UnitService::remove($unit);

        $this->assertFalse(UnitService::has('removetest'));
    }

    /**
     * Test remove() removes a unit by name string.
     */
    public function testRemoveByNameString(): void
    {
        $unit = new Unit(
            name: 'removestringtest',
            asciiSymbol: 'rst',
            dimension: 'L',
            systems: [UnitSystem::Custom]
        );
        UnitService::add($unit);
        $this->assertTrue(UnitService::has('removestringtest'));

        UnitService::remove('removestringtest');

        $this->assertFalse(UnitService::has('removestringtest'));
    }

    /**
     * Test remove() does nothing for non-existent unit.
     */
    public function testRemoveDoesNothingForNonExistent(): void
    {
        $countBefore = UnitService::count();

        UnitService::remove('nonexistent_unit_xyz');

        $this->assertSame($countBefore, UnitService::count());
    }

    /**
     * Test remove() handles uninitialized (null) registry gracefully.
     */
    public function testRemoveHandlesUninitializedRegistry(): void
    {
        UnitService::reset();

        // remove() should return early without triggering init().
        UnitService::remove('meter');

        // Re-initialize by accessing the registry — meter should still exist.
        $result = UnitService::getByName('meter');
        $this->assertInstanceOf(Unit::class, $result);
    }

    // endregion

    // region removeAll() tests

    /**
     * Test removeAll() empties the registry.
     */
    public function testRemoveAllEmptiesRegistry(): void
    {
        try {
            UnitService::removeAll();

            $this->assertSame(0, UnitService::count());
            $this->assertEmpty(UnitService::getAll());
        } finally {
            UnitService::reset();
        }
    }

    /**
     * Test removeAll() does not trigger re-initialization on next access.
     */
    public function testRemoveAllDoesNotReinitialize(): void
    {
        try {
            UnitService::removeAll();

            // Accessing getAll() should NOT re-initialize — it should stay empty.
            $this->assertEmpty(UnitService::getAll());
        } finally {
            UnitService::reset();
        }
    }

    // endregion

    // region reset() tests

    /**
     * Test reset() followed by access still includes default units.
     */
    public function testResetStillHasDefaultUnits(): void
    {
        UnitService::reset();

        $this->assertTrue(UnitService::has('meter'));
        $this->assertTrue(UnitService::has('second'));
        $this->assertTrue(UnitService::has('gram'));
    }

    // endregion

    // region has() tests

    /**
     * Test has() returns true for existing unit by name string.
     */
    public function testHasReturnsTrueForExistingUnitByName(): void
    {
        $this->assertTrue(UnitService::has('meter'));
    }

    /**
     * Test has() returns true for existing unit by Unit object.
     */
    public function testHasReturnsTrueForExistingUnitByObject(): void
    {
        $unit = UnitService::getByName('meter');
        $this->assertNotNull($unit);

        $this->assertTrue(UnitService::has($unit));
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
        $this->assertFalse(UnitService::has('nonexistent_unit_xyz'));
    }

    /**
     * Test has() is case sensitive.
     */
    public function testHasIsCaseSensitive(): void
    {
        $this->assertTrue(UnitService::has('meter'));
        $this->assertFalse(UnitService::has('Meter'));
        $this->assertFalse(UnitService::has('METER'));
    }

    // endregion

    // region count() tests

    /**
     * Test count() returns a positive integer for the default registry.
     */
    public function testCountReturnsPositiveInteger(): void
    {
        $this->assertGreaterThan(0, UnitService::count());
    }

    /**
     * Test count() increases after adding a unit.
     */
    public function testCountIncreasesAfterAdd(): void
    {
        $countBefore = UnitService::count();

        $unit = new Unit(
            name: 'counttest',
            asciiSymbol: 'cnt',
            dimension: 'L',
            systems: [UnitSystem::Custom]
        );

        try {
            UnitService::add($unit);
            $this->assertSame($countBefore + 1, UnitService::count());
        } finally {
            UnitService::remove($unit);
        }
    }

    /**
     * Test count() decreases after removing a unit.
     */
    public function testCountDecreasesAfterRemove(): void
    {
        $unit = new Unit(
            name: 'countremovetest',
            asciiSymbol: 'crt',
            dimension: 'L',
            systems: [UnitSystem::Custom]
        );
        UnitService::add($unit);
        $countBefore = UnitService::count();

        UnitService::remove($unit);

        $this->assertSame($countBefore - 1, UnitService::count());
    }

    /**
     * Test count() returns zero after removeAll().
     */
    public function testCountReturnsZeroAfterRemoveAll(): void
    {
        try {
            UnitService::removeAll();
            $this->assertSame(0, UnitService::count());
        } finally {
            UnitService::reset();
        }
    }

    // endregion

    // region loadSystem() tests

    /**
     * Test loadSystem() adds units from the specified system.
     */
    public function testLoadSystemAddsUnits(): void
    {
        try {
            UnitService::removeAll();
            UnitService::loadSystem(UnitSystem::Imperial);

            $this->assertTrue(UnitService::has('foot'));
            $this->assertTrue(UnitService::has('mile'));
        } finally {
            UnitService::reset();
        }
    }

    /**
     * Test loadSystem() does not add duplicate units when called twice.
     */
    public function testLoadSystemDoesNotDuplicateUnits(): void
    {
        // SI is auto-loaded during init(). Loading it again should be a no-op.
        $countBefore = UnitService::count();

        UnitService::loadSystem(UnitSystem::Si);

        $this->assertSame($countBefore, UnitService::count());
    }

    /**
     * Test loadSystem() loads units from an unloaded system.
     */
    public function testLoadSystemLoadsUnitsFromSystem(): void
    {
        try {
            UnitService::removeAll();
            UnitService::loadSystem(UnitSystem::Nautical);

            $this->assertTrue(UnitService::has('nautical mile'));
        } finally {
            UnitService::reset();
        }
    }

    // endregion

    // region loadAll() tests

    /**
     * Test loadAll() makes Imperial and US Customary units available.
     */
    public function testLoadAllIncludesNonDefaultUnits(): void
    {
        try {
            UnitService::removeAll();
            UnitService::loadAll();

            $this->assertTrue(UnitService::has('foot'));
            $this->assertTrue(UnitService::has('mile'));
            $this->assertTrue(UnitService::has('meter'));
        } finally {
            UnitService::reset();
        }
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
            $this->assertNotEmpty($unit->name, "Unit '$name' has empty name.");
            $this->assertNotEmpty($unit->systems, "Unit '$name' has empty systems.");
        }
    }

    /**
     * Test SI base units have correct dimensions.
     */
    public function testSiBaseUnitsHaveCorrectDimensions(): void
    {
        $expected = [
            'meter'   => 'L',
            'gram'    => 'M',
            'second'  => 'T',
            'ampere'  => 'I',
            'kelvin'  => 'H',
            'mole'    => 'N',
            'candela' => 'J',
        ];

        $all = UnitService::getAll();

        foreach ($expected as $name => $dimension) {
            $this->assertArrayHasKey($name, $all, "SI base unit '$name' not found.");
            $this->assertSame($dimension, $all[$name]->dimension, "Unit '$name' has wrong dimension.");
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

        $this->assertCount(count($names), $uniqueNames, 'Duplicate unit names found.');
    }

    /**
     * Test ASCII symbols are unique across all units.
     */
    public function testAsciiSymbolsAreUnique(): void
    {
        $all = UnitService::getAll();
        $symbols = array_map(static fn ($unit) => $unit->asciiSymbol, $all);
        $uniqueSymbols = array_unique($symbols);

        $this->assertCount(count($symbols), $uniqueSymbols, 'Duplicate ASCII symbols found.');
    }

    // endregion
}
