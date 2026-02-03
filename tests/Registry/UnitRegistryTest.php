<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Registry;

use DomainException;
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;
use Galaxon\Quantities\Unit;
use Galaxon\Quantities\Utility\PrefixUtility;
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

        $this->assertArrayHasKey('metre', $result);
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

        $this->assertSame('m', $result['metre']->asciiSymbol);
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
        $this->assertSame('metre', $result->name);
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
        // 'm' is metre, 'M' is mega prefix (not a unit by itself)
        $metre = UnitRegistry::getBySymbol('m');
        $this->assertNotNull($metre);
        $this->assertSame('metre', $metre->name);

        // 'M' alone is not a unit
        $upper = UnitRegistry::getBySymbol('M');
        $this->assertNull($upper);
    }

    // endregion

    // region getByDimension() tests

    /**
     * Test getByDimension() returns units for valid dimension.
     */
    public function testGetByDimensionReturnsUnits(): void
    {
        $result = UnitRegistry::getByDimension('L');

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        foreach ($result as $unit) {
            $this->assertSame('L', $unit->dimension);
        }
    }

    /**
     * Test getByDimension() includes expected length units.
     */
    public function testGetByDimensionIncludesExpectedUnits(): void
    {
        $result = UnitRegistry::getByDimension('L');

        $this->assertArrayHasKey('metre', $result);
    }

    /**
     * Test getByDimension() returns empty array for unknown dimension.
     */
    public function testGetByDimensionReturnsEmptyForUnknown(): void
    {
        $result = UnitRegistry::getByDimension('X9');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    // endregion

    // region getAllValidSymbols() tests

    /**
     * Test getAllValidSymbols() returns array of strings.
     */
    public function testGetAllValidSymbolsReturnsArrayOfStrings(): void
    {
        $result = UnitRegistry::getAllSymbols();

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

        // Metre should have metric prefixes
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

    // region getExpandableUnits() tests

    /**
     * Test getExpandableUnits() returns array.
     */
    public function testGetExpandableUnitsReturnsArray(): void
    {
        $result = UnitRegistry::getExpandable();

        $this->assertIsArray($result);
    }

    /**
     * Test getExpandableUnits() contains units with expansions.
     */
    public function testGetExpandableUnitsContainsExpansions(): void
    {
        $result = UnitRegistry::getExpandable();

        // All items should be Unit objects with non-null expansion
        foreach ($result as $unit) {
            $this->assertInstanceOf(Unit::class, $unit);
            $this->assertNotNull($unit->expansionUnitSymbol);
            $this->assertNotNull($unit->expansionUnit);
        }
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

        UnitRegistry::add(
            name: $name,
            asciiSymbol: $symbol,
            unicodeSymbol: 'τυα',  // Greek letters (valid Unicode word)
            quantityType: 'length',
            dimension: 'L',
            systems: [System::Common]
        );

        $all = UnitRegistry::getAll();
        $this->assertArrayHasKey($name, $all);
        $this->assertSame('length', $all[$name]->quantityType);
        $this->assertSame('L', $all[$name]->dimension);

        // Clean up
        UnitRegistry::remove($name);
    }

    /**
     * Test add() throws for duplicate name.
     */
    public function testAddThrowsForDuplicateName(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("The unit name 'metre' is being used");

        UnitRegistry::add(
            name: 'metre',
            asciiSymbol: 'xx',
            unicodeSymbol: 'ξξ',  // Greek letters (valid Unicode word)
            quantityType: 'length',
            dimension: 'L',
            systems: [System::Common]
        );
    }

    /**
     * Test add() throws for duplicate ASCII symbol.
     */
    public function testAddThrowsForDuplicateAsciiSymbol(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("The symbol 'm' for newunitdup is already being used by another unit");

        UnitRegistry::add(
            name: 'newunitdup',
            asciiSymbol: 'm',
            unicodeSymbol: 'δυπ',  // Greek letters (valid Unicode word)
            quantityType: 'length',
            dimension: 'L',
            systems: [System::Common]
        );
    }

    /**
     * Test add() throws when Unicode symbol conflicts with existing Unicode symbol.
     */
    public function testAddThrowsForDuplicateUnicodeSymbol(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("The symbol 'Ω' for newunitohm is already being used by another unit");

        UnitRegistry::add(
            name: 'newunitohm',
            asciiSymbol: 'nuo',
            unicodeSymbol: 'Ω',  // Conflicts with ohm's Unicode symbol
            quantityType: 'resistance',
            dimension: 'T-3L2MI-2',
            systems: [System::Common]
        );
    }

    /**
     * Test add() with all parameters.
     */
    public function testAddWithAllParameters(): void
    {
        $name = 'fullunitparams';
        $symbol = 'fup';

        UnitRegistry::add(
            name: $name,
            asciiSymbol: $symbol,
            unicodeSymbol: 'φυπ',  // Greek letters (valid Unicode word)
            quantityType: 'length',
            dimension: 'L',
            prefixGroup: PrefixUtility::GROUP_CODE_METRIC,
            expansionUnitSymbol: 'm',
            systems: [System::Common]
        );

        $unit = UnitRegistry::getAll()[$name];
        $this->assertSame($symbol, $unit->asciiSymbol);
        $this->assertContains(System::Common, $unit->systems);
        $this->assertSame(PrefixUtility::GROUP_CODE_METRIC, $unit->prefixGroup);

        // Clean up
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
        UnitRegistry::add(
            name: $name,
            asciiSymbol: $symbol,
            unicodeSymbol: 'ρμτ',  // Greek letters (valid Unicode word)
            quantityType: 'length',
            dimension: 'L',
            systems: [System::Common]
        );

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
            'metre'   => 'L',
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
