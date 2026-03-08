<?php

/**
 * Unit definitions for currencies, based off ISO 4217 currency data.
 *
 * Fetched from
 * https://www.six-group.com/dam/download/financial-information/data-center/iso-currrency/lists/list-one.xml
 * at Thursday, 05-Mar-2026 06:15:41 UTC.
 *
 * Auto-generated from the official ISO 4217 XML published by SIX Group.
 *
 * To regenerate, call Galaxon\Quantities\Currencies\CurrencyService::refreshCurrencyUnits().
 *
 * @return array{
 *     whenFetched: string,
 *     definitions: array<string, array{
 *         asciiSymbol: string,
 *         unicodeSymbol?: string,
 *         prefixGroup?: int,
 *         alternateSymbol?: string,
 *         systems: list<UnitSystem>
 *     }>
 * }
 */

declare(strict_types=1);

use Galaxon\Quantities\UnitSystem;

return [
    'whenFetched' => '2026-03-05 06:15:41 UTC',
    'definitions' => [
        'ADB Unit of Account'         => [
            'asciiSymbol' => 'XUA',
            'systems'     => [UnitSystem::Currency],
        ],
        'Afghani'                     => [
            'asciiSymbol' => 'AFN',
            'systems'     => [UnitSystem::Currency],
        ],
        'Algerian Dinar'              => [
            'asciiSymbol' => 'DZD',
            'systems'     => [UnitSystem::Currency],
        ],
        'Arab Accounting Dinar'       => [
            'asciiSymbol' => 'XAD',
            'systems'     => [UnitSystem::Currency],
        ],
        'Argentine Peso'              => [
            'asciiSymbol' => 'ARS',
            'systems'     => [UnitSystem::Currency],
        ],
        'Armenian Dram'               => [
            'asciiSymbol' => 'AMD',
            'systems'     => [UnitSystem::Currency],
        ],
        'Aruban Florin'               => [
            'asciiSymbol' => 'AWG',
            'systems'     => [UnitSystem::Currency],
        ],
        'Australian Dollar'           => [
            'asciiSymbol' => 'AUD',
            'systems'     => [UnitSystem::Currency],
        ],
        'Azerbaijan Manat'            => [
            'asciiSymbol' => 'AZN',
            'systems'     => [UnitSystem::Currency],
        ],
        'Bahamian Dollar'             => [
            'asciiSymbol' => 'BSD',
            'systems'     => [UnitSystem::Currency],
        ],
        'Bahraini Dinar'              => [
            'asciiSymbol' => 'BHD',
            'systems'     => [UnitSystem::Currency],
        ],
        'Baht'                        => [
            'asciiSymbol' => 'THB',
            'systems'     => [UnitSystem::Currency],
        ],
        'Balboa'                      => [
            'asciiSymbol' => 'PAB',
            'systems'     => [UnitSystem::Currency],
        ],
        'Barbados Dollar'             => [
            'asciiSymbol' => 'BBD',
            'systems'     => [UnitSystem::Currency],
        ],
        'Belarusian Ruble'            => [
            'asciiSymbol' => 'BYN',
            'systems'     => [UnitSystem::Currency],
        ],
        'Belize Dollar'               => [
            'asciiSymbol' => 'BZD',
            'systems'     => [UnitSystem::Currency],
        ],
        'Bermudian Dollar'            => [
            'asciiSymbol' => 'BMD',
            'systems'     => [UnitSystem::Currency],
        ],
        'Boliviano'                   => [
            'asciiSymbol' => 'BOB',
            'systems'     => [UnitSystem::Currency],
        ],
        'Bolívar Soberano'            => [
            'asciiSymbol' => 'VES',
            'systems'     => [UnitSystem::Currency],
        ],
        'Brazilian Real'              => [
            'asciiSymbol' => 'BRL',
            'systems'     => [UnitSystem::Currency],
        ],
        'Brunei Dollar'               => [
            'asciiSymbol' => 'BND',
            'systems'     => [UnitSystem::Currency],
        ],
        'Burundi Franc'               => [
            'asciiSymbol' => 'BIF',
            'systems'     => [UnitSystem::Currency],
        ],
        'CFA Franc BCEAO'             => [
            'asciiSymbol' => 'XOF',
            'systems'     => [UnitSystem::Currency],
        ],
        'CFA Franc BEAC'              => [
            'asciiSymbol' => 'XAF',
            'systems'     => [UnitSystem::Currency],
        ],
        'CFP Franc'                   => [
            'asciiSymbol' => 'XPF',
            'systems'     => [UnitSystem::Currency],
        ],
        'Cabo Verde Escudo'           => [
            'asciiSymbol' => 'CVE',
            'systems'     => [UnitSystem::Currency],
        ],
        'Canadian Dollar'             => [
            'asciiSymbol' => 'CAD',
            'systems'     => [UnitSystem::Currency],
        ],
        'Caribbean Guilder'           => [
            'asciiSymbol' => 'XCG',
            'systems'     => [UnitSystem::Currency],
        ],
        'Cayman Islands Dollar'       => [
            'asciiSymbol' => 'KYD',
            'systems'     => [UnitSystem::Currency],
        ],
        'Chilean Peso'                => [
            'asciiSymbol' => 'CLP',
            'systems'     => [UnitSystem::Currency],
        ],
        'Colombian Peso'              => [
            'asciiSymbol' => 'COP',
            'systems'     => [UnitSystem::Currency],
        ],
        'Comorian Franc'              => [
            'asciiSymbol' => 'KMF',
            'systems'     => [UnitSystem::Currency],
        ],
        'Congolese Franc'             => [
            'asciiSymbol' => 'CDF',
            'systems'     => [UnitSystem::Currency],
        ],
        'Convertible Mark'            => [
            'asciiSymbol' => 'BAM',
            'systems'     => [UnitSystem::Currency],
        ],
        'Cordoba Oro'                 => [
            'asciiSymbol' => 'NIO',
            'systems'     => [UnitSystem::Currency],
        ],
        'Costa Rican Colon'           => [
            'asciiSymbol' => 'CRC',
            'systems'     => [UnitSystem::Currency],
        ],
        'Cuban Peso'                  => [
            'asciiSymbol' => 'CUP',
            'systems'     => [UnitSystem::Currency],
        ],
        'Czech Koruna'                => [
            'asciiSymbol' => 'CZK',
            'systems'     => [UnitSystem::Currency],
        ],
        'Dalasi'                      => [
            'asciiSymbol' => 'GMD',
            'systems'     => [UnitSystem::Currency],
        ],
        'Danish Krone'                => [
            'asciiSymbol' => 'DKK',
            'systems'     => [UnitSystem::Currency],
        ],
        'Denar'                       => [
            'asciiSymbol' => 'MKD',
            'systems'     => [UnitSystem::Currency],
        ],
        'Djibouti Franc'              => [
            'asciiSymbol' => 'DJF',
            'systems'     => [UnitSystem::Currency],
        ],
        'Dobra'                       => [
            'asciiSymbol' => 'STN',
            'systems'     => [UnitSystem::Currency],
        ],
        'Dominican Peso'              => [
            'asciiSymbol' => 'DOP',
            'systems'     => [UnitSystem::Currency],
        ],
        'Dong'                        => [
            'asciiSymbol' => 'VND',
            'systems'     => [UnitSystem::Currency],
        ],
        'East Caribbean Dollar'       => [
            'asciiSymbol' => 'XCD',
            'systems'     => [UnitSystem::Currency],
        ],
        'Egyptian Pound'              => [
            'asciiSymbol' => 'EGP',
            'systems'     => [UnitSystem::Currency],
        ],
        'El Salvador Colon'           => [
            'asciiSymbol' => 'SVC',
            'systems'     => [UnitSystem::Currency],
        ],
        'Ethiopian Birr'              => [
            'asciiSymbol' => 'ETB',
            'systems'     => [UnitSystem::Currency],
        ],
        'Euro'                        => [
            'asciiSymbol' => 'EUR',
            'systems'     => [UnitSystem::Currency],
        ],
        'Falkland Islands Pound'      => [
            'asciiSymbol' => 'FKP',
            'systems'     => [UnitSystem::Currency],
        ],
        'Fiji Dollar'                 => [
            'asciiSymbol' => 'FJD',
            'systems'     => [UnitSystem::Currency],
        ],
        'Forint'                      => [
            'asciiSymbol' => 'HUF',
            'systems'     => [UnitSystem::Currency],
        ],
        'Ghana Cedi'                  => [
            'asciiSymbol' => 'GHS',
            'systems'     => [UnitSystem::Currency],
        ],
        'Gibraltar Pound'             => [
            'asciiSymbol' => 'GIP',
            'systems'     => [UnitSystem::Currency],
        ],
        'Gold'                        => [
            'asciiSymbol' => 'XAU',
            'systems'     => [UnitSystem::Currency],
        ],
        'Gourde'                      => [
            'asciiSymbol' => 'HTG',
            'systems'     => [UnitSystem::Currency],
        ],
        'Guarani'                     => [
            'asciiSymbol' => 'PYG',
            'systems'     => [UnitSystem::Currency],
        ],
        'Guinean Franc'               => [
            'asciiSymbol' => 'GNF',
            'systems'     => [UnitSystem::Currency],
        ],
        'Guyana Dollar'               => [
            'asciiSymbol' => 'GYD',
            'systems'     => [UnitSystem::Currency],
        ],
        'Hong Kong Dollar'            => [
            'asciiSymbol' => 'HKD',
            'systems'     => [UnitSystem::Currency],
        ],
        'Hryvnia'                     => [
            'asciiSymbol' => 'UAH',
            'systems'     => [UnitSystem::Currency],
        ],
        'Iceland Krona'               => [
            'asciiSymbol' => 'ISK',
            'systems'     => [UnitSystem::Currency],
        ],
        'Indian Rupee'                => [
            'asciiSymbol' => 'INR',
            'systems'     => [UnitSystem::Currency],
        ],
        'Iranian Rial'                => [
            'asciiSymbol' => 'IRR',
            'systems'     => [UnitSystem::Currency],
        ],
        'Iraqi Dinar'                 => [
            'asciiSymbol' => 'IQD',
            'systems'     => [UnitSystem::Currency],
        ],
        'Jamaican Dollar'             => [
            'asciiSymbol' => 'JMD',
            'systems'     => [UnitSystem::Currency],
        ],
        'Jordanian Dinar'             => [
            'asciiSymbol' => 'JOD',
            'systems'     => [UnitSystem::Currency],
        ],
        'Kenyan Shilling'             => [
            'asciiSymbol' => 'KES',
            'systems'     => [UnitSystem::Currency],
        ],
        'Kina'                        => [
            'asciiSymbol' => 'PGK',
            'systems'     => [UnitSystem::Currency],
        ],
        'Kuwaiti Dinar'               => [
            'asciiSymbol' => 'KWD',
            'systems'     => [UnitSystem::Currency],
        ],
        'Kwanza'                      => [
            'asciiSymbol' => 'AOA',
            'systems'     => [UnitSystem::Currency],
        ],
        'Kyat'                        => [
            'asciiSymbol' => 'MMK',
            'systems'     => [UnitSystem::Currency],
        ],
        'Lao Kip'                     => [
            'asciiSymbol' => 'LAK',
            'systems'     => [UnitSystem::Currency],
        ],
        'Lari'                        => [
            'asciiSymbol' => 'GEL',
            'systems'     => [UnitSystem::Currency],
        ],
        'Lebanese Pound'              => [
            'asciiSymbol' => 'LBP',
            'systems'     => [UnitSystem::Currency],
        ],
        'Lek'                         => [
            'asciiSymbol' => 'ALL',
            'systems'     => [UnitSystem::Currency],
        ],
        'Lempira'                     => [
            'asciiSymbol' => 'HNL',
            'systems'     => [UnitSystem::Currency],
        ],
        'Leone'                       => [
            'asciiSymbol' => 'SLE',
            'systems'     => [UnitSystem::Currency],
        ],
        'Liberian Dollar'             => [
            'asciiSymbol' => 'LRD',
            'systems'     => [UnitSystem::Currency],
        ],
        'Libyan Dinar'                => [
            'asciiSymbol' => 'LYD',
            'systems'     => [UnitSystem::Currency],
        ],
        'Lilangeni'                   => [
            'asciiSymbol' => 'SZL',
            'systems'     => [UnitSystem::Currency],
        ],
        'Loti'                        => [
            'asciiSymbol' => 'LSL',
            'systems'     => [UnitSystem::Currency],
        ],
        'Malagasy Ariary'             => [
            'asciiSymbol' => 'MGA',
            'systems'     => [UnitSystem::Currency],
        ],
        'Malawi Kwacha'               => [
            'asciiSymbol' => 'MWK',
            'systems'     => [UnitSystem::Currency],
        ],
        'Malaysian Ringgit'           => [
            'asciiSymbol' => 'MYR',
            'systems'     => [UnitSystem::Currency],
        ],
        'Mauritius Rupee'             => [
            'asciiSymbol' => 'MUR',
            'systems'     => [UnitSystem::Currency],
        ],
        'Mexican Peso'                => [
            'asciiSymbol' => 'MXN',
            'systems'     => [UnitSystem::Currency],
        ],
        'Moldovan Leu'                => [
            'asciiSymbol' => 'MDL',
            'systems'     => [UnitSystem::Currency],
        ],
        'Moroccan Dirham'             => [
            'asciiSymbol' => 'MAD',
            'systems'     => [UnitSystem::Currency],
        ],
        'Mozambique Metical'          => [
            'asciiSymbol' => 'MZN',
            'systems'     => [UnitSystem::Currency],
        ],
        'Naira'                       => [
            'asciiSymbol' => 'NGN',
            'systems'     => [UnitSystem::Currency],
        ],
        'Nakfa'                       => [
            'asciiSymbol' => 'ERN',
            'systems'     => [UnitSystem::Currency],
        ],
        'Namibia Dollar'              => [
            'asciiSymbol' => 'NAD',
            'systems'     => [UnitSystem::Currency],
        ],
        'Nepalese Rupee'              => [
            'asciiSymbol' => 'NPR',
            'systems'     => [UnitSystem::Currency],
        ],
        'New Israeli Sheqel'          => [
            'asciiSymbol' => 'ILS',
            'systems'     => [UnitSystem::Currency],
        ],
        'New Taiwan Dollar'           => [
            'asciiSymbol' => 'TWD',
            'systems'     => [UnitSystem::Currency],
        ],
        'New Zealand Dollar'          => [
            'asciiSymbol' => 'NZD',
            'systems'     => [UnitSystem::Currency],
        ],
        'Ngultrum'                    => [
            'asciiSymbol' => 'BTN',
            'systems'     => [UnitSystem::Currency],
        ],
        'North Korean Won'            => [
            'asciiSymbol' => 'KPW',
            'systems'     => [UnitSystem::Currency],
        ],
        'Norwegian Krone'             => [
            'asciiSymbol' => 'NOK',
            'systems'     => [UnitSystem::Currency],
        ],
        'Ouguiya'                     => [
            'asciiSymbol' => 'MRU',
            'systems'     => [UnitSystem::Currency],
        ],
        'Pakistan Rupee'              => [
            'asciiSymbol' => 'PKR',
            'systems'     => [UnitSystem::Currency],
        ],
        'Palladium'                   => [
            'asciiSymbol' => 'XPD',
            'systems'     => [UnitSystem::Currency],
        ],
        'Pataca'                      => [
            'asciiSymbol' => 'MOP',
            'systems'     => [UnitSystem::Currency],
        ],
        'Pa’anga'                     => [
            'asciiSymbol' => 'TOP',
            'systems'     => [UnitSystem::Currency],
        ],
        'Peso Uruguayo'               => [
            'asciiSymbol' => 'UYU',
            'systems'     => [UnitSystem::Currency],
        ],
        'Philippine Peso'             => [
            'asciiSymbol' => 'PHP',
            'systems'     => [UnitSystem::Currency],
        ],
        'Platinum'                    => [
            'asciiSymbol' => 'XPT',
            'systems'     => [UnitSystem::Currency],
        ],
        'Pound Sterling'              => [
            'asciiSymbol' => 'GBP',
            'systems'     => [UnitSystem::Currency],
        ],
        'Pula'                        => [
            'asciiSymbol' => 'BWP',
            'systems'     => [UnitSystem::Currency],
        ],
        'Qatari Rial'                 => [
            'asciiSymbol' => 'QAR',
            'systems'     => [UnitSystem::Currency],
        ],
        'Quetzal'                     => [
            'asciiSymbol' => 'GTQ',
            'systems'     => [UnitSystem::Currency],
        ],
        'Rand'                        => [
            'asciiSymbol' => 'ZAR',
            'systems'     => [UnitSystem::Currency],
        ],
        'Rial Omani'                  => [
            'asciiSymbol' => 'OMR',
            'systems'     => [UnitSystem::Currency],
        ],
        'Riel'                        => [
            'asciiSymbol' => 'KHR',
            'systems'     => [UnitSystem::Currency],
        ],
        'Romanian Leu'                => [
            'asciiSymbol' => 'RON',
            'systems'     => [UnitSystem::Currency],
        ],
        'Rufiyaa'                     => [
            'asciiSymbol' => 'MVR',
            'systems'     => [UnitSystem::Currency],
        ],
        'Rupiah'                      => [
            'asciiSymbol' => 'IDR',
            'systems'     => [UnitSystem::Currency],
        ],
        'Russian Ruble'               => [
            'asciiSymbol' => 'RUB',
            'systems'     => [UnitSystem::Currency],
        ],
        'Rwanda Franc'                => [
            'asciiSymbol' => 'RWF',
            'systems'     => [UnitSystem::Currency],
        ],
        'SDR (Special Drawing Right)' => [
            'asciiSymbol' => 'XDR',
            'systems'     => [UnitSystem::Currency],
        ],
        'Saint Helena Pound'          => [
            'asciiSymbol' => 'SHP',
            'systems'     => [UnitSystem::Currency],
        ],
        'Saudi Riyal'                 => [
            'asciiSymbol' => 'SAR',
            'systems'     => [UnitSystem::Currency],
        ],
        'Serbian Dinar'               => [
            'asciiSymbol' => 'RSD',
            'systems'     => [UnitSystem::Currency],
        ],
        'Seychelles Rupee'            => [
            'asciiSymbol' => 'SCR',
            'systems'     => [UnitSystem::Currency],
        ],
        'Silver'                      => [
            'asciiSymbol' => 'XAG',
            'systems'     => [UnitSystem::Currency],
        ],
        'Singapore Dollar'            => [
            'asciiSymbol' => 'SGD',
            'systems'     => [UnitSystem::Currency],
        ],
        'Sol'                         => [
            'asciiSymbol' => 'PEN',
            'systems'     => [UnitSystem::Currency],
        ],
        'Solomon Islands Dollar'      => [
            'asciiSymbol' => 'SBD',
            'systems'     => [UnitSystem::Currency],
        ],
        'Som'                         => [
            'asciiSymbol' => 'KGS',
            'systems'     => [UnitSystem::Currency],
        ],
        'Somali Shilling'             => [
            'asciiSymbol' => 'SOS',
            'systems'     => [UnitSystem::Currency],
        ],
        'Somoni'                      => [
            'asciiSymbol' => 'TJS',
            'systems'     => [UnitSystem::Currency],
        ],
        'South Sudanese Pound'        => [
            'asciiSymbol' => 'SSP',
            'systems'     => [UnitSystem::Currency],
        ],
        'Sri Lanka Rupee'             => [
            'asciiSymbol' => 'LKR',
            'systems'     => [UnitSystem::Currency],
        ],
        'Sucre'                       => [
            'asciiSymbol' => 'XSU',
            'systems'     => [UnitSystem::Currency],
        ],
        'Sudanese Pound'              => [
            'asciiSymbol' => 'SDG',
            'systems'     => [UnitSystem::Currency],
        ],
        'Surinam Dollar'              => [
            'asciiSymbol' => 'SRD',
            'systems'     => [UnitSystem::Currency],
        ],
        'Swedish Krona'               => [
            'asciiSymbol' => 'SEK',
            'systems'     => [UnitSystem::Currency],
        ],
        'Swiss Franc'                 => [
            'asciiSymbol' => 'CHF',
            'systems'     => [UnitSystem::Currency],
        ],
        'Syrian Pound'                => [
            'asciiSymbol' => 'SYP',
            'systems'     => [UnitSystem::Currency],
        ],
        'Taka'                        => [
            'asciiSymbol' => 'BDT',
            'systems'     => [UnitSystem::Currency],
        ],
        'Tala'                        => [
            'asciiSymbol' => 'WST',
            'systems'     => [UnitSystem::Currency],
        ],
        'Tanzanian Shilling'          => [
            'asciiSymbol' => 'TZS',
            'systems'     => [UnitSystem::Currency],
        ],
        'Tenge'                       => [
            'asciiSymbol' => 'KZT',
            'systems'     => [UnitSystem::Currency],
        ],
        'Trinidad and Tobago Dollar'  => [
            'asciiSymbol' => 'TTD',
            'systems'     => [UnitSystem::Currency],
        ],
        'Tugrik'                      => [
            'asciiSymbol' => 'MNT',
            'systems'     => [UnitSystem::Currency],
        ],
        'Tunisian Dinar'              => [
            'asciiSymbol' => 'TND',
            'systems'     => [UnitSystem::Currency],
        ],
        'Turkish Lira'                => [
            'asciiSymbol' => 'TRY',
            'systems'     => [UnitSystem::Currency],
        ],
        'Turkmenistan New Manat'      => [
            'asciiSymbol' => 'TMT',
            'systems'     => [UnitSystem::Currency],
        ],
        'UAE Dirham'                  => [
            'asciiSymbol' => 'AED',
            'systems'     => [UnitSystem::Currency],
        ],
        'US Dollar'                   => [
            'asciiSymbol' => 'USD',
            'systems'     => [UnitSystem::Currency],
        ],
        'Uganda Shilling'             => [
            'asciiSymbol' => 'UGX',
            'systems'     => [UnitSystem::Currency],
        ],
        'Unidad Previsional'          => [
            'asciiSymbol' => 'UYW',
            'systems'     => [UnitSystem::Currency],
        ],
        'Uzbekistan Sum'              => [
            'asciiSymbol' => 'UZS',
            'systems'     => [UnitSystem::Currency],
        ],
        'Vatu'                        => [
            'asciiSymbol' => 'VUV',
            'systems'     => [UnitSystem::Currency],
        ],
        'Won'                         => [
            'asciiSymbol' => 'KRW',
            'systems'     => [UnitSystem::Currency],
        ],
        'Yemeni Rial'                 => [
            'asciiSymbol' => 'YER',
            'systems'     => [UnitSystem::Currency],
        ],
        'Yen'                         => [
            'asciiSymbol' => 'JPY',
            'systems'     => [UnitSystem::Currency],
        ],
        'Yuan Renminbi'               => [
            'asciiSymbol' => 'CNY',
            'systems'     => [UnitSystem::Currency],
        ],
        'Zambian Kwacha'              => [
            'asciiSymbol' => 'ZMW',
            'systems'     => [UnitSystem::Currency],
        ],
        'Zimbabwe Gold'               => [
            'asciiSymbol' => 'ZWG',
            'systems'     => [UnitSystem::Currency],
        ],
        'Zloty'                       => [
            'asciiSymbol' => 'PLN',
            'systems'     => [UnitSystem::Currency],
        ],
    ],
];
