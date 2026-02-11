<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Traits;

use Galaxon\Quantities\System;

/**
 * Trait providing assertions for validating unit and conversion definition array shapes.
 */
trait ArrayShapeTrait
{
    /**
     * Assert that the given array matches the expected shape for unit definitions.
     *
     * Expected shape:
     * array<string, array{
     *      asciiSymbol: string,
     *      unicodeSymbol?: string,
     *      prefixGroup?: int,
     *      systems: list<System>,
     *  }>
     *
     * @param array<mixed> $units The array to validate.
     */
    public function assertValidUnitDefinitionsShape(array $units): void
    {
        $this->assertNotEmpty($units);

        foreach ($units as $key => $value) {
            // Check key is a string.
            $this->assertIsString($key, 'Unit definition key must be a string.');

            // Check value is an array.
            $this->assertIsArray($value, "Unit definition for '$key' must be an array.");

            // Check asciiSymbol is provided and is a string.
            $this->assertArrayHasKey('asciiSymbol', $value, "Unit '$key' must have 'asciiSymbol'.");
            $this->assertIsString($value['asciiSymbol'], "Unit '$key' asciiSymbol must be a string.");

            // Check unicodeSymbol is optional and is a string.
            if (array_key_exists('unicodeSymbol', $value)) {
                $this->assertIsString($value['unicodeSymbol'], "Unit '$key' unicodeSymbol must be a string.");
            }

            // Check prefixGroup is optional and is an int.
            if (array_key_exists('prefixGroup', $value)) {
                $this->assertIsInt($value['prefixGroup'], "Unit '$key' prefixGroup must be an int.");
            }

            // Check systems are provided as an array of System objects.
            $this->assertArrayHasKey('systems', $value, "Unit '$key' must have 'systems'.");
            $this->assertIsArray($value['systems'], "Unit '$key' systems must be an array.");
            foreach ($value['systems'] as $system) {
                $this->assertInstanceOf(
                    System::class,
                    $system,
                    "Unit '$key' systems must contain only System instances."
                );
            }
        }
    }

    /**
     * Assert that the given array matches the expected shape for conversion definitions.
     *
     * Expected shape:
     * list<array{string, string, float}>
     *
     * @param array<mixed> $conversions The array to validate.
     */
    public function assertValidConversionDefinitionsShape(array $conversions): void
    {
        $this->assertNotEmpty($conversions);

        foreach ($conversions as $key => $value) {
            // Check key is an int (list format).
            $this->assertIsInt($key, 'Conversion definition key must be an int (list format).');

            // Check value is an array of 3 items.
            $this->assertIsArray($value, "Conversion at index $key must be an array.");
            $this->assertCount(3, $value, "Conversion at index $key must have exactly 3 elements.");

            // Check the types of the array items.
            $this->assertIsString($value[0], "Conversion at index $key: first element must be a string.");
            $this->assertIsString($value[1], "Conversion at index $key: second element must be a string.");
            $this->assertTrue(
                is_float($value[2]) || is_int($value[2]),
                "Conversion at index $key: third element must be a float or int."
            );
        }
    }
}
