<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use DomainException;

/**
 * Provides access to physical constants as Quantity objects.
 *
 * Constants are lazily instantiated and cached for efficient repeated access.
 * Lookup is available by symbol (case-sensitive) or by name (case-insensitive).
 */
class PhysicalConstant
{
    // region Static properties

    /**
     * Cache of instantiated constant Quantity objects, keyed by symbol.
     *
     * @var array<string, Quantity>
     */
    private static array $constants = [];

    // endregion

    // region Constant definitions

    /**
     * Get the definitions of default physical constants.
     *
     * @return array<string, array{symbol: string, value: float, unitSymbol: string}>
     */
    private const array CONSTANT_DEFINITIONS = [
        // Universal constants.
        'hyperfine transition frequency of caesium' => [
            'symbol'     => 'deltaNuCs',
            'value'      => 9192631770,
            'unitSymbol' => 'Hz',
        ],
        'speed of light in vacuum'                  => [
            'symbol'     => 'c',
            'value'      => 299792458,
            'unitSymbol' => 'm/s',
        ],
        'gravitational constant'                    => [
            'symbol'     => 'G',
            'value'      => 6.67430e-11,
            'unitSymbol' => 'm3/(kg*s2)',
        ],
        'Planck constant'                           => [
            'symbol'     => 'h',
            'value'      => 6.62607015e-34,
            'unitSymbol' => 'J*s',
        ],
        'reduced Planck constant'                   => [
            'symbol'     => 'hbar',
            'value'      => 1.054571817e-34,
            'unitSymbol' => 'J*s',
        ],

        // Electromagnetic constants.
        'vacuum electric permittivity'              => [
            'symbol'     => 'epsilon0',
            'value'      => 8.8541878128e-12,
            'unitSymbol' => 'F/m',
        ],
        'vacuum magnetic permeability'              => [
            'symbol'     => 'mu0',
            'value'      => 1.25663706212e-6,
            'unitSymbol' => 'H/m',
        ],
        'elementary charge'                         => [
            'symbol'     => 'e',
            'value'      => 1.602176634e-19,
            'unitSymbol' => 'C',
        ],

        // Atomic and nuclear constants.
        'electron mass'                             => [
            'symbol'     => 'me',
            'value'      => 9.1093837015e-31,
            'unitSymbol' => 'kg',
        ],
        'proton mass'                               => [
            'symbol'     => 'mp',
            'value'      => 1.67262192369e-27,
            'unitSymbol' => 'kg',
        ],
        'neutron mass'                              => [
            'symbol'     => 'mn',
            'value'      => 1.67492749804e-27,
            'unitSymbol' => 'kg',
        ],
        'fine-structure constant'                   => [
            'symbol'     => 'alpha',
            'value'      => 7.2973525693e-3,
            'unitSymbol' => '',
        ],
        'Rydberg constant'                          => [
            'symbol'     => 'Rinf',
            'value'      => 10973731.568160,
            'unitSymbol' => 'm-1',
        ],
        'Bohr radius'                               => [
            'symbol'     => 'a0',
            'value'      => 5.29177210903e-11,
            'unitSymbol' => 'm',
        ],

        // Thermodynamic constants.
        'Boltzmann constant'                        => [
            'symbol'     => 'k',
            'value'      => 1.380649e-23,
            'unitSymbol' => 'J/K',
        ],
        'Avogadro constant'                         => [
            'symbol'     => 'NA',
            'value'      => 6.02214076e23,
            'unitSymbol' => 'mol-1',
        ],
        'molar gas constant'                        => [
            'symbol'     => 'R',
            'value'      => 8.314462618,
            'unitSymbol' => 'J/(mol*K)',
        ],
        'Stefan-Boltzmann constant'                 => [
            'symbol'     => 'sigma',
            'value'      => 5.670374419e-8,
            'unitSymbol' => 'W/(m2*K4)',
        ],

        // Photometric constants.
        'luminous efficacy of 540 THz radiation'    => [
            'symbol'     => 'Kcd',
            'value'      => 683,
            'unitSymbol' => 'lm/W',
        ],

        // Other constants.
        'standard acceleration of gravity'          => [
            'symbol'     => 'g',
            'value'      => 9.80665,
            'unitSymbol' => 'm/s2',
        ],
    ];

    // endregion

    // region Public methods

    /**
     * Get a physical constant by its symbol.
     *
     * Symbol lookup is case-sensitive (e.g., 'G' differs from 'g').
     *
     * @param string $symbol The symbol of the constant (e.g., 'G', 'c', 'h').
     * @return Quantity The constant as a Quantity object.
     * @throws DomainException If the symbol is unknown.
     */
    public static function get(string $symbol): Quantity
    {
        // Return cached constant if available.
        if (isset(self::$constants[$symbol])) {
            return self::$constants[$symbol];
        }

        // Find the definition by symbol.
        foreach (self::CONSTANT_DEFINITIONS as $def) {
            if ($def['symbol'] === $symbol) {
                $quantity = Quantity::create($def['value'], $def['unitSymbol'] ?: null);
                self::$constants[$symbol] = $quantity;
                return $quantity;
            }
        }

        throw new DomainException("Unknown constant symbol: '$symbol'.");
    }

    /**
     * Get a physical constant by its name.
     *
     * Name lookup is case-insensitive (e.g., 'Gravitational Constant' matches 'gravitational constant').
     *
     * @param string $name The name of the constant (e.g., 'gravitational constant', 'speed of light in vacuum').
     * @return Quantity The constant as a Quantity object.
     * @throws DomainException If the name is unknown.
     */
    public static function getByName(string $name): Quantity
    {
        $definitions = self::CONSTANT_DEFINITIONS;
        $nameLower = strtolower($name);

        // Find the definition by name (case-insensitive).
        foreach ($definitions as $defName => $def) {
            if (strtolower($defName) === $nameLower) {
                return self::get($def['symbol']);
            }
        }

        throw new DomainException("Unknown constant name: '$name'.");
    }

    /**
     * Search the physical constants for those with a name matching a given search string.
     *
     * @param string $searchString The search string (case-insensitive).
     * @return list<Quantity> The matching Quantities.
     */
    public static function search(string $searchString): array
    {
        $definitions = self::CONSTANT_DEFINITIONS;
        $searchString = strtolower($searchString);
        $matches = [];

        // Find the definition by name (case-insensitive).
        foreach ($definitions as $defName => $def) {
            if (str_contains(strtolower($defName), $searchString)) {
                $matches[] = self::get($def['symbol']);
            }
        }

        return $matches;
    }

    /**
     * Get all physical constants.
     *
     * Returns an array of all constants, keyed by symbol.
     *
     * @return array<string, Quantity> All constants as Quantity objects.
     */
    public static function getAll(): array
    {
        // Ensure all constants are cached.
        foreach (self::CONSTANT_DEFINITIONS as $def) {
            self::get($def['symbol']);
        }

        return self::$constants;
    }

    // endregion
}
