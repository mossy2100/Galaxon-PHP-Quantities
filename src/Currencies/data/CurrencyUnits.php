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
            'systems'     => [UnitSystem::Financial],
        ],
        'Afghani'                     => [
            'asciiSymbol' => 'AFN',
            'systems'     => [UnitSystem::Financial],
        ],
        'Algerian Dinar'              => [
            'asciiSymbol' => 'DZD',
            'systems'     => [UnitSystem::Financial],
        ],
        'Arab Accounting Dinar'       => [
            'asciiSymbol' => 'XAD',
            'systems'     => [UnitSystem::Financial],
        ],
        'Argentine Peso'              => [
            'asciiSymbol' => 'ARS',
            'systems'     => [UnitSystem::Financial],
        ],
        'Armenian Dram'               => [
            'asciiSymbol' => 'AMD',
            'systems'     => [UnitSystem::Financial],
        ],
        'Aruban Florin'               => [
            'asciiSymbol' => 'AWG',
            'systems'     => [UnitSystem::Financial],
        ],
        'Australian Dollar'           => [
            'asciiSymbol' => 'AUD',
            'systems'     => [UnitSystem::Financial],
        ],
        'Azerbaijan Manat'            => [
            'asciiSymbol' => 'AZN',
            'systems'     => [UnitSystem::Financial],
        ],
        'Bahamian Dollar'             => [
            'asciiSymbol' => 'BSD',
            'systems'     => [UnitSystem::Financial],
        ],
        'Bahraini Dinar'              => [
            'asciiSymbol' => 'BHD',
            'systems'     => [UnitSystem::Financial],
        ],
        'Baht'                        => [
            'asciiSymbol' => 'THB',
            'systems'     => [UnitSystem::Financial],
        ],
        'Balboa'                      => [
            'asciiSymbol' => 'PAB',
            'systems'     => [UnitSystem::Financial],
        ],
        'Barbados Dollar'             => [
            'asciiSymbol' => 'BBD',
            'systems'     => [UnitSystem::Financial],
        ],
        'Belarusian Ruble'            => [
            'asciiSymbol' => 'BYN',
            'systems'     => [UnitSystem::Financial],
        ],
        'Belize Dollar'               => [
            'asciiSymbol' => 'BZD',
            'systems'     => [UnitSystem::Financial],
        ],
        'Bermudian Dollar'            => [
            'asciiSymbol' => 'BMD',
            'systems'     => [UnitSystem::Financial],
        ],
        'Boliviano'                   => [
            'asciiSymbol' => 'BOB',
            'systems'     => [UnitSystem::Financial],
        ],
        'Bolívar Soberano'            => [
            'asciiSymbol' => 'VES',
            'systems'     => [UnitSystem::Financial],
        ],
        'Brazilian Real'              => [
            'asciiSymbol' => 'BRL',
            'systems'     => [UnitSystem::Financial],
        ],
        'Brunei Dollar'               => [
            'asciiSymbol' => 'BND',
            'systems'     => [UnitSystem::Financial],
        ],
        'Burundi Franc'               => [
            'asciiSymbol' => 'BIF',
            'systems'     => [UnitSystem::Financial],
        ],
        'CFA Franc BCEAO'             => [
            'asciiSymbol' => 'XOF',
            'systems'     => [UnitSystem::Financial],
        ],
        'CFA Franc BEAC'              => [
            'asciiSymbol' => 'XAF',
            'systems'     => [UnitSystem::Financial],
        ],
        'CFP Franc'                   => [
            'asciiSymbol' => 'XPF',
            'systems'     => [UnitSystem::Financial],
        ],
        'Cabo Verde Escudo'           => [
            'asciiSymbol' => 'CVE',
            'systems'     => [UnitSystem::Financial],
        ],
        'Canadian Dollar'             => [
            'asciiSymbol' => 'CAD',
            'systems'     => [UnitSystem::Financial],
        ],
        'Caribbean Guilder'           => [
            'asciiSymbol' => 'XCG',
            'systems'     => [UnitSystem::Financial],
        ],
        'Cayman Islands Dollar'       => [
            'asciiSymbol' => 'KYD',
            'systems'     => [UnitSystem::Financial],
        ],
        'Chilean Peso'                => [
            'asciiSymbol' => 'CLP',
            'systems'     => [UnitSystem::Financial],
        ],
        'Colombian Peso'              => [
            'asciiSymbol' => 'COP',
            'systems'     => [UnitSystem::Financial],
        ],
        'Comorian Franc'              => [
            'asciiSymbol' => 'KMF',
            'systems'     => [UnitSystem::Financial],
        ],
        'Congolese Franc'             => [
            'asciiSymbol' => 'CDF',
            'systems'     => [UnitSystem::Financial],
        ],
        'Convertible Mark'            => [
            'asciiSymbol' => 'BAM',
            'systems'     => [UnitSystem::Financial],
        ],
        'Cordoba Oro'                 => [
            'asciiSymbol' => 'NIO',
            'systems'     => [UnitSystem::Financial],
        ],
        'Costa Rican Colon'           => [
            'asciiSymbol' => 'CRC',
            'systems'     => [UnitSystem::Financial],
        ],
        'Cuban Peso'                  => [
            'asciiSymbol' => 'CUP',
            'systems'     => [UnitSystem::Financial],
        ],
        'Czech Koruna'                => [
            'asciiSymbol' => 'CZK',
            'systems'     => [UnitSystem::Financial],
        ],
        'Dalasi'                      => [
            'asciiSymbol' => 'GMD',
            'systems'     => [UnitSystem::Financial],
        ],
        'Danish Krone'                => [
            'asciiSymbol' => 'DKK',
            'systems'     => [UnitSystem::Financial],
        ],
        'Denar'                       => [
            'asciiSymbol' => 'MKD',
            'systems'     => [UnitSystem::Financial],
        ],
        'Djibouti Franc'              => [
            'asciiSymbol' => 'DJF',
            'systems'     => [UnitSystem::Financial],
        ],
        'Dobra'                       => [
            'asciiSymbol' => 'STN',
            'systems'     => [UnitSystem::Financial],
        ],
        'Dominican Peso'              => [
            'asciiSymbol' => 'DOP',
            'systems'     => [UnitSystem::Financial],
        ],
        'Dong'                        => [
            'asciiSymbol' => 'VND',
            'systems'     => [UnitSystem::Financial],
        ],
        'East Caribbean Dollar'       => [
            'asciiSymbol' => 'XCD',
            'systems'     => [UnitSystem::Financial],
        ],
        'Egyptian Pound'              => [
            'asciiSymbol' => 'EGP',
            'systems'     => [UnitSystem::Financial],
        ],
        'El Salvador Colon'           => [
            'asciiSymbol' => 'SVC',
            'systems'     => [UnitSystem::Financial],
        ],
        'Ethiopian Birr'              => [
            'asciiSymbol' => 'ETB',
            'systems'     => [UnitSystem::Financial],
        ],
        'Euro'                        => [
            'asciiSymbol' => 'EUR',
            'systems'     => [UnitSystem::Financial],
        ],
        'Falkland Islands Pound'      => [
            'asciiSymbol' => 'FKP',
            'systems'     => [UnitSystem::Financial],
        ],
        'Fiji Dollar'                 => [
            'asciiSymbol' => 'FJD',
            'systems'     => [UnitSystem::Financial],
        ],
        'Forint'                      => [
            'asciiSymbol' => 'HUF',
            'systems'     => [UnitSystem::Financial],
        ],
        'Ghana Cedi'                  => [
            'asciiSymbol' => 'GHS',
            'systems'     => [UnitSystem::Financial],
        ],
        'Gibraltar Pound'             => [
            'asciiSymbol' => 'GIP',
            'systems'     => [UnitSystem::Financial],
        ],
        'Gold'                        => [
            'asciiSymbol' => 'XAU',
            'systems'     => [UnitSystem::Financial],
        ],
        'Gourde'                      => [
            'asciiSymbol' => 'HTG',
            'systems'     => [UnitSystem::Financial],
        ],
        'Guarani'                     => [
            'asciiSymbol' => 'PYG',
            'systems'     => [UnitSystem::Financial],
        ],
        'Guinean Franc'               => [
            'asciiSymbol' => 'GNF',
            'systems'     => [UnitSystem::Financial],
        ],
        'Guyana Dollar'               => [
            'asciiSymbol' => 'GYD',
            'systems'     => [UnitSystem::Financial],
        ],
        'Hong Kong Dollar'            => [
            'asciiSymbol' => 'HKD',
            'systems'     => [UnitSystem::Financial],
        ],
        'Hryvnia'                     => [
            'asciiSymbol' => 'UAH',
            'systems'     => [UnitSystem::Financial],
        ],
        'Iceland Krona'               => [
            'asciiSymbol' => 'ISK',
            'systems'     => [UnitSystem::Financial],
        ],
        'Indian Rupee'                => [
            'asciiSymbol' => 'INR',
            'systems'     => [UnitSystem::Financial],
        ],
        'Iranian Rial'                => [
            'asciiSymbol' => 'IRR',
            'systems'     => [UnitSystem::Financial],
        ],
        'Iraqi Dinar'                 => [
            'asciiSymbol' => 'IQD',
            'systems'     => [UnitSystem::Financial],
        ],
        'Jamaican Dollar'             => [
            'asciiSymbol' => 'JMD',
            'systems'     => [UnitSystem::Financial],
        ],
        'Jordanian Dinar'             => [
            'asciiSymbol' => 'JOD',
            'systems'     => [UnitSystem::Financial],
        ],
        'Kenyan Shilling'             => [
            'asciiSymbol' => 'KES',
            'systems'     => [UnitSystem::Financial],
        ],
        'Kina'                        => [
            'asciiSymbol' => 'PGK',
            'systems'     => [UnitSystem::Financial],
        ],
        'Kuwaiti Dinar'               => [
            'asciiSymbol' => 'KWD',
            'systems'     => [UnitSystem::Financial],
        ],
        'Kwanza'                      => [
            'asciiSymbol' => 'AOA',
            'systems'     => [UnitSystem::Financial],
        ],
        'Kyat'                        => [
            'asciiSymbol' => 'MMK',
            'systems'     => [UnitSystem::Financial],
        ],
        'Lao Kip'                     => [
            'asciiSymbol' => 'LAK',
            'systems'     => [UnitSystem::Financial],
        ],
        'Lari'                        => [
            'asciiSymbol' => 'GEL',
            'systems'     => [UnitSystem::Financial],
        ],
        'Lebanese Pound'              => [
            'asciiSymbol' => 'LBP',
            'systems'     => [UnitSystem::Financial],
        ],
        'Lek'                         => [
            'asciiSymbol' => 'ALL',
            'systems'     => [UnitSystem::Financial],
        ],
        'Lempira'                     => [
            'asciiSymbol' => 'HNL',
            'systems'     => [UnitSystem::Financial],
        ],
        'Leone'                       => [
            'asciiSymbol' => 'SLE',
            'systems'     => [UnitSystem::Financial],
        ],
        'Liberian Dollar'             => [
            'asciiSymbol' => 'LRD',
            'systems'     => [UnitSystem::Financial],
        ],
        'Libyan Dinar'                => [
            'asciiSymbol' => 'LYD',
            'systems'     => [UnitSystem::Financial],
        ],
        'Lilangeni'                   => [
            'asciiSymbol' => 'SZL',
            'systems'     => [UnitSystem::Financial],
        ],
        'Loti'                        => [
            'asciiSymbol' => 'LSL',
            'systems'     => [UnitSystem::Financial],
        ],
        'Malagasy Ariary'             => [
            'asciiSymbol' => 'MGA',
            'systems'     => [UnitSystem::Financial],
        ],
        'Malawi Kwacha'               => [
            'asciiSymbol' => 'MWK',
            'systems'     => [UnitSystem::Financial],
        ],
        'Malaysian Ringgit'           => [
            'asciiSymbol' => 'MYR',
            'systems'     => [UnitSystem::Financial],
        ],
        'Mauritius Rupee'             => [
            'asciiSymbol' => 'MUR',
            'systems'     => [UnitSystem::Financial],
        ],
        'Mexican Peso'                => [
            'asciiSymbol' => 'MXN',
            'systems'     => [UnitSystem::Financial],
        ],
        'Moldovan Leu'                => [
            'asciiSymbol' => 'MDL',
            'systems'     => [UnitSystem::Financial],
        ],
        'Moroccan Dirham'             => [
            'asciiSymbol' => 'MAD',
            'systems'     => [UnitSystem::Financial],
        ],
        'Mozambique Metical'          => [
            'asciiSymbol' => 'MZN',
            'systems'     => [UnitSystem::Financial],
        ],
        'Naira'                       => [
            'asciiSymbol' => 'NGN',
            'systems'     => [UnitSystem::Financial],
        ],
        'Nakfa'                       => [
            'asciiSymbol' => 'ERN',
            'systems'     => [UnitSystem::Financial],
        ],
        'Namibia Dollar'              => [
            'asciiSymbol' => 'NAD',
            'systems'     => [UnitSystem::Financial],
        ],
        'Nepalese Rupee'              => [
            'asciiSymbol' => 'NPR',
            'systems'     => [UnitSystem::Financial],
        ],
        'New Israeli Sheqel'          => [
            'asciiSymbol' => 'ILS',
            'systems'     => [UnitSystem::Financial],
        ],
        'New Taiwan Dollar'           => [
            'asciiSymbol' => 'TWD',
            'systems'     => [UnitSystem::Financial],
        ],
        'New Zealand Dollar'          => [
            'asciiSymbol' => 'NZD',
            'systems'     => [UnitSystem::Financial],
        ],
        'Ngultrum'                    => [
            'asciiSymbol' => 'BTN',
            'systems'     => [UnitSystem::Financial],
        ],
        'North Korean Won'            => [
            'asciiSymbol' => 'KPW',
            'systems'     => [UnitSystem::Financial],
        ],
        'Norwegian Krone'             => [
            'asciiSymbol' => 'NOK',
            'systems'     => [UnitSystem::Financial],
        ],
        'Ouguiya'                     => [
            'asciiSymbol' => 'MRU',
            'systems'     => [UnitSystem::Financial],
        ],
        'Pakistan Rupee'              => [
            'asciiSymbol' => 'PKR',
            'systems'     => [UnitSystem::Financial],
        ],
        'Palladium'                   => [
            'asciiSymbol' => 'XPD',
            'systems'     => [UnitSystem::Financial],
        ],
        'Pataca'                      => [
            'asciiSymbol' => 'MOP',
            'systems'     => [UnitSystem::Financial],
        ],
        'Pa’anga'                     => [
            'asciiSymbol' => 'TOP',
            'systems'     => [UnitSystem::Financial],
        ],
        'Peso Uruguayo'               => [
            'asciiSymbol' => 'UYU',
            'systems'     => [UnitSystem::Financial],
        ],
        'Philippine Peso'             => [
            'asciiSymbol' => 'PHP',
            'systems'     => [UnitSystem::Financial],
        ],
        'Platinum'                    => [
            'asciiSymbol' => 'XPT',
            'systems'     => [UnitSystem::Financial],
        ],
        'Pound Sterling'              => [
            'asciiSymbol' => 'GBP',
            'systems'     => [UnitSystem::Financial],
        ],
        'Pula'                        => [
            'asciiSymbol' => 'BWP',
            'systems'     => [UnitSystem::Financial],
        ],
        'Qatari Rial'                 => [
            'asciiSymbol' => 'QAR',
            'systems'     => [UnitSystem::Financial],
        ],
        'Quetzal'                     => [
            'asciiSymbol' => 'GTQ',
            'systems'     => [UnitSystem::Financial],
        ],
        'Rand'                        => [
            'asciiSymbol' => 'ZAR',
            'systems'     => [UnitSystem::Financial],
        ],
        'Rial Omani'                  => [
            'asciiSymbol' => 'OMR',
            'systems'     => [UnitSystem::Financial],
        ],
        'Riel'                        => [
            'asciiSymbol' => 'KHR',
            'systems'     => [UnitSystem::Financial],
        ],
        'Romanian Leu'                => [
            'asciiSymbol' => 'RON',
            'systems'     => [UnitSystem::Financial],
        ],
        'Rufiyaa'                     => [
            'asciiSymbol' => 'MVR',
            'systems'     => [UnitSystem::Financial],
        ],
        'Rupiah'                      => [
            'asciiSymbol' => 'IDR',
            'systems'     => [UnitSystem::Financial],
        ],
        'Russian Ruble'               => [
            'asciiSymbol' => 'RUB',
            'systems'     => [UnitSystem::Financial],
        ],
        'Rwanda Franc'                => [
            'asciiSymbol' => 'RWF',
            'systems'     => [UnitSystem::Financial],
        ],
        'SDR (Special Drawing Right)' => [
            'asciiSymbol' => 'XDR',
            'systems'     => [UnitSystem::Financial],
        ],
        'Saint Helena Pound'          => [
            'asciiSymbol' => 'SHP',
            'systems'     => [UnitSystem::Financial],
        ],
        'Saudi Riyal'                 => [
            'asciiSymbol' => 'SAR',
            'systems'     => [UnitSystem::Financial],
        ],
        'Serbian Dinar'               => [
            'asciiSymbol' => 'RSD',
            'systems'     => [UnitSystem::Financial],
        ],
        'Seychelles Rupee'            => [
            'asciiSymbol' => 'SCR',
            'systems'     => [UnitSystem::Financial],
        ],
        'Silver'                      => [
            'asciiSymbol' => 'XAG',
            'systems'     => [UnitSystem::Financial],
        ],
        'Singapore Dollar'            => [
            'asciiSymbol' => 'SGD',
            'systems'     => [UnitSystem::Financial],
        ],
        'Sol'                         => [
            'asciiSymbol' => 'PEN',
            'systems'     => [UnitSystem::Financial],
        ],
        'Solomon Islands Dollar'      => [
            'asciiSymbol' => 'SBD',
            'systems'     => [UnitSystem::Financial],
        ],
        'Som'                         => [
            'asciiSymbol' => 'KGS',
            'systems'     => [UnitSystem::Financial],
        ],
        'Somali Shilling'             => [
            'asciiSymbol' => 'SOS',
            'systems'     => [UnitSystem::Financial],
        ],
        'Somoni'                      => [
            'asciiSymbol' => 'TJS',
            'systems'     => [UnitSystem::Financial],
        ],
        'South Sudanese Pound'        => [
            'asciiSymbol' => 'SSP',
            'systems'     => [UnitSystem::Financial],
        ],
        'Sri Lanka Rupee'             => [
            'asciiSymbol' => 'LKR',
            'systems'     => [UnitSystem::Financial],
        ],
        'Sucre'                       => [
            'asciiSymbol' => 'XSU',
            'systems'     => [UnitSystem::Financial],
        ],
        'Sudanese Pound'              => [
            'asciiSymbol' => 'SDG',
            'systems'     => [UnitSystem::Financial],
        ],
        'Surinam Dollar'              => [
            'asciiSymbol' => 'SRD',
            'systems'     => [UnitSystem::Financial],
        ],
        'Swedish Krona'               => [
            'asciiSymbol' => 'SEK',
            'systems'     => [UnitSystem::Financial],
        ],
        'Swiss Franc'                 => [
            'asciiSymbol' => 'CHF',
            'systems'     => [UnitSystem::Financial],
        ],
        'Syrian Pound'                => [
            'asciiSymbol' => 'SYP',
            'systems'     => [UnitSystem::Financial],
        ],
        'Taka'                        => [
            'asciiSymbol' => 'BDT',
            'systems'     => [UnitSystem::Financial],
        ],
        'Tala'                        => [
            'asciiSymbol' => 'WST',
            'systems'     => [UnitSystem::Financial],
        ],
        'Tanzanian Shilling'          => [
            'asciiSymbol' => 'TZS',
            'systems'     => [UnitSystem::Financial],
        ],
        'Tenge'                       => [
            'asciiSymbol' => 'KZT',
            'systems'     => [UnitSystem::Financial],
        ],
        'Trinidad and Tobago Dollar'  => [
            'asciiSymbol' => 'TTD',
            'systems'     => [UnitSystem::Financial],
        ],
        'Tugrik'                      => [
            'asciiSymbol' => 'MNT',
            'systems'     => [UnitSystem::Financial],
        ],
        'Tunisian Dinar'              => [
            'asciiSymbol' => 'TND',
            'systems'     => [UnitSystem::Financial],
        ],
        'Turkish Lira'                => [
            'asciiSymbol' => 'TRY',
            'systems'     => [UnitSystem::Financial],
        ],
        'Turkmenistan New Manat'      => [
            'asciiSymbol' => 'TMT',
            'systems'     => [UnitSystem::Financial],
        ],
        'UAE Dirham'                  => [
            'asciiSymbol' => 'AED',
            'systems'     => [UnitSystem::Financial],
        ],
        'US Dollar'                   => [
            'asciiSymbol' => 'USD',
            'systems'     => [UnitSystem::Financial],
        ],
        'Uganda Shilling'             => [
            'asciiSymbol' => 'UGX',
            'systems'     => [UnitSystem::Financial],
        ],
        'Unidad Previsional'          => [
            'asciiSymbol' => 'UYW',
            'systems'     => [UnitSystem::Financial],
        ],
        'Uzbekistan Sum'              => [
            'asciiSymbol' => 'UZS',
            'systems'     => [UnitSystem::Financial],
        ],
        'Vatu'                        => [
            'asciiSymbol' => 'VUV',
            'systems'     => [UnitSystem::Financial],
        ],
        'Won'                         => [
            'asciiSymbol' => 'KRW',
            'systems'     => [UnitSystem::Financial],
        ],
        'Yemeni Rial'                 => [
            'asciiSymbol' => 'YER',
            'systems'     => [UnitSystem::Financial],
        ],
        'Yen'                         => [
            'asciiSymbol' => 'JPY',
            'systems'     => [UnitSystem::Financial],
        ],
        'Yuan Renminbi'               => [
            'asciiSymbol' => 'CNY',
            'systems'     => [UnitSystem::Financial],
        ],
        'Zambian Kwacha'              => [
            'asciiSymbol' => 'ZMW',
            'systems'     => [UnitSystem::Financial],
        ],
        'Zimbabwe Gold'               => [
            'asciiSymbol' => 'ZWG',
            'systems'     => [UnitSystem::Financial],
        ],
        'Zloty'                       => [
            'asciiSymbol' => 'PLN',
            'systems'     => [UnitSystem::Financial],
        ],
    ],
];
