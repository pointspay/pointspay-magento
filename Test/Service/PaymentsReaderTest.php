<?php

namespace Pointspay\Pointspay\Test\Service;

use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Service\PaymentsReader;
use Pointspay\Pointspay\Model\Framework\Config\Data as DataInterface;

class PaymentsReaderTest extends TestCase
{
    private $dataStorage;
    private $paymentsReader;

    private $expectedMethods='[
  {
    "code": "pointspay_required_settings",
    "name": "Pointspay",
    "sandbox": {
      "baseDomain": "https:\/\/uat-secure.pointspay.com",
      "enabled": true
    },
    "live": {
      "baseDomain": "https:\/\/secure.pointspay.com",
      "enabled": true
    },
    "applicableCountries": [
      {
        "code": "AF",
        "name": "Afghanistan"
      },
      {
        "code": "AL",
        "name": "Albania"
      },
      {
        "code": "DZ",
        "name": "Algeria"
      },
      {
        "code": "AS",
        "name": "American Samoa"
      },
      {
        "code": "AD",
        "name": "Andorra"
      },
      {
        "code": "AO",
        "name": "Angola"
      },
      {
        "code": "AG",
        "name": "Antigua and Barbuda"
      },
      {
        "code": "AZ",
        "name": "Azerbaijan"
      },
      {
        "code": "AR",
        "name": "Argentina"
      },
      {
        "code": "AU",
        "name": "Australia"
      },
      {
        "code": "AT",
        "name": "Austria"
      },
      {
        "code": "BS",
        "name": "Bahamas"
      },
      {
        "code": "BH",
        "name": "Bahrain"
      },
      {
        "code": "BD",
        "name": "Bangladesh"
      },
      {
        "code": "AM",
        "name": "Armenia"
      },
      {
        "code": "BB",
        "name": "Barbados"
      },
      {
        "code": "BE",
        "name": "Belgium"
      },
      {
        "code": "BM",
        "name": "Bermuda"
      },
      {
        "code": "BT",
        "name": "Bhutan"
      },
      {
        "code": "BO",
        "name": "Bolivia, Plurinational State of Bolivia"
      },
      {
        "code": "BA",
        "name": "Bosnia and Herzegovina"
      },
      {
        "code": "BW",
        "name": "Botswana"
      },
      {
        "code": "BV",
        "name": "Bouvet Island"
      },
      {
        "code": "BR",
        "name": "Brazil"
      },
      {
        "code": "BZ",
        "name": "Belize"
      },
      {
        "code": "IO",
        "name": "British Indian Ocean Territory"
      },
      {
        "code": "SB",
        "name": "Solomon Islands"
      },
      {
        "code": "VG",
        "name": "Virgin Islands British"
      },
      {
        "code": "BN",
        "name": "Brunei Darussalam"
      },
      {
        "code": "BG",
        "name": "Bulgaria"
      },
      {
        "code": "MM",
        "name": "Myanmar"
      },
      {
        "code": "BI",
        "name": "Burundi"
      },
      {
        "code": "BY",
        "name": "Belarus"
      },
      {
        "code": "KH",
        "name": "Cambodia"
      },
      {
        "code": "CM",
        "name": "Cameroon"
      },
      {
        "code": "CA",
        "name": "Canada"
      },
      {
        "code": "CV",
        "name": "Cape Verde"
      },
      {
        "code": "KY",
        "name": "Cayman Islands"
      },
      {
        "code": "CF",
        "name": "Central African Republic"
      },
      {
        "code": "LK",
        "name": "Sri Lanka"
      },
      {
        "code": "TD",
        "name": "Chad"
      },
      {
        "code": "CL",
        "name": "Chile"
      },
      {
        "code": "CN",
        "name": "China"
      },
      {
        "code": "TW",
        "name": "Taiwan, Province of China"
      },
      {
        "code": "CX",
        "name": "Christmas Island"
      },
      {
        "code": "CC",
        "name": "Cocos (Keeling) Islands"
      },
      {
        "code": "CO",
        "name": "Colombia"
      },
      {
        "code": "KM",
        "name": "Comoros"
      },
      {
        "code": "YT",
        "name": "Mayotte"
      },
      {
        "code": "CG",
        "name": "Congo Brazaville"
      },
      {
        "code": "CD",
        "name": "Congo, The Democratic Republic of The"
      },
      {
        "code": "CK",
        "name": "Cook Islands"
      },
      {
        "code": "CR",
        "name": "Costa Rica"
      },
      {
        "code": "HR",
        "name": "Croatia"
      },
      {
        "code": "CU",
        "name": "Cuba"
      },
      {
        "code": "CY",
        "name": "Cyprus"
      },
      {
        "code": "CZ",
        "name": "Czech Republic"
      },
      {
        "code": "BJ",
        "name": "Benin"
      },
      {
        "code": "DK",
        "name": "Denmark"
      },
      {
        "code": "DM",
        "name": "Dominica"
      },
      {
        "code": "DO",
        "name": "Dominican Republic"
      },
      {
        "code": "EC",
        "name": "Ecuador"
      },
      {
        "code": "SV",
        "name": "El Salvador"
      },
      {
        "code": "GQ",
        "name": "Equatorial Guinea"
      },
      {
        "code": "ET",
        "name": "Ethiopia"
      },
      {
        "code": "ER",
        "name": "Eritrea"
      },
      {
        "code": "EE",
        "name": "Estonia"
      },
      {
        "code": "FO",
        "name": "Faroe Islands"
      },
      {
        "code": "FK",
        "name": "Falkland Islands (Malvinas)"
      },
      {
        "code": "GS",
        "name": "South-Georgia and the South Sandwich Islands"
      },
      {
        "code": "FJ",
        "name": "Fiji"
      },
      {
        "code": "FI",
        "name": "Finland"
      },
      {
        "code": "AX",
        "name": "Aland Islands"
      },
      {
        "code": "FR",
        "name": "France"
      },
      {
        "code": "GF",
        "name": "French Guiana"
      },
      {
        "code": "PF",
        "name": "French Polynesia"
      },
      {
        "code": "TF",
        "name": "French Southern Territories"
      },
      {
        "code": "DJ",
        "name": "Djibouti"
      },
      {
        "code": "GA",
        "name": "Gabon"
      },
      {
        "code": "GE",
        "name": "Georgia"
      },
      {
        "code": "GM",
        "name": "Gambia"
      },
      {
        "code": "PS",
        "name": "Palestine"
      },
      {
        "code": "DE",
        "name": "Germany"
      },
      {
        "code": "GH",
        "name": "Ghana"
      },
      {
        "code": "GI",
        "name": "Gibraltar"
      },
      {
        "code": "KI",
        "name": "Kiribati"
      },
      {
        "code": "GR",
        "name": "Greece"
      },
      {
        "code": "GL",
        "name": "Greenland"
      },
      {
        "code": "GD",
        "name": "Grenada"
      },
      {
        "code": "GP",
        "name": "Guadeloupe"
      },
      {
        "code": "GU",
        "name": "Guam"
      },
      {
        "code": "GT",
        "name": "Guatemala"
      },
      {
        "code": "GN",
        "name": "Guinea"
      },
      {
        "code": "GY",
        "name": "Guyana"
      },
      {
        "code": "HT",
        "name": "Haiti"
      },
      {
        "code": "HM",
        "name": "Heard Island and McDonald Islands"
      },
      {
        "code": "VA",
        "name": "Holy See (Vatican City State)"
      },
      {
        "code": "HN",
        "name": "Honduras"
      },
      {
        "code": "HK",
        "name": "Hong Kong"
      },
      {
        "code": "HU",
        "name": "Hungary"
      },
      {
        "code": "IS",
        "name": "Iceland"
      },
      {
        "code": "IN",
        "name": "India"
      },
      {
        "code": "ID",
        "name": "Indonesia"
      },
      {
        "code": "IR",
        "name": "Iran, Islamic Republic of"
      },
      {
        "code": "IQ",
        "name": "Iraq"
      },
      {
        "code": "IE",
        "name": "Ireland"
      },
      {
        "code": "IL",
        "name": "Israel"
      },
      {
        "code": "IT",
        "name": "Italy"
      },
      {
        "code": "CI",
        "name": "C\u00f4te d\'Ivoire"
      },
      {
        "code": "JM",
        "name": "Jamaica"
      },
      {
        "code": "JP",
        "name": "Japan"
      },
      {
        "code": "KZ",
        "name": "Kazakhstan"
      },
      {
        "code": "JO",
        "name": "Jordan"
      },
      {
        "code": "KE",
        "name": "Kenya"
      },
      {
        "code": "KP",
        "name": "Korea, Democratic People\'s Republic of"
      },
      {
        "code": "KR",
        "name": "Korea, Republic of"
      },
      {
        "code": "KW",
        "name": "Kuwait"
      },
      {
        "code": "KG",
        "name": "Kyrgyzstan"
      },
      {
        "code": "LA",
        "name": "Lao People\'s Democratic Republic"
      },
      {
        "code": "LB",
        "name": "Lebanon"
      },
      {
        "code": "LS",
        "name": "Lesotho"
      },
      {
        "code": "LV",
        "name": "Latvia"
      },
      {
        "code": "LR",
        "name": "Liberia"
      },
      {
        "code": "LY",
        "name": "Libya"
      },
      {
        "code": "LI",
        "name": "Liechtenstein"
      },
      {
        "code": "LT",
        "name": "Lithuania"
      },
      {
        "code": "LU",
        "name": "Luxembourg"
      },
      {
        "code": "MO",
        "name": "Macao"
      },
      {
        "code": "MG",
        "name": "Madagascar"
      },
      {
        "code": "MW",
        "name": "Malawi"
      },
      {
        "code": "MY",
        "name": "Malaysia"
      },
      {
        "code": "MV",
        "name": "Maldives"
      },
      {
        "code": "ML",
        "name": "Mali"
      },
      {
        "code": "MT",
        "name": "Malta"
      },
      {
        "code": "MQ",
        "name": "Martinique"
      },
      {
        "code": "MR",
        "name": "Mauritania"
      },
      {
        "code": "MU",
        "name": "Mauritius"
      },
      {
        "code": "MX",
        "name": "Mexico"
      },
      {
        "code": "MC",
        "name": "Monaco"
      },
      {
        "code": "MN",
        "name": "Mongolia"
      },
      {
        "code": "MD",
        "name": "Moldova, Republic of"
      },
      {
        "code": "ME",
        "name": "Montenegro"
      },
      {
        "code": "MS",
        "name": "Montserrat"
      },
      {
        "code": "MA",
        "name": "Morocco"
      },
      {
        "code": "MZ",
        "name": "Mozambique"
      },
      {
        "code": "OM",
        "name": "Oman"
      },
      {
        "code": "NA",
        "name": "Namibia"
      },
      {
        "code": "NR",
        "name": "Nauru"
      },
      {
        "code": "NP",
        "name": "Nepal"
      },
      {
        "code": "NL",
        "name": "Netherlands"
      },
      {
        "code": "CW",
        "name": "Curacao"
      },
      {
        "code": "AW",
        "name": "Aruba"
      },
      {
        "code": "SX",
        "name": "Sint Maarten (Dutch part)"
      },
      {
        "code": "BQ",
        "name": "Bonaire, Sint Eustatius and Saba"
      },
      {
        "code": "NC",
        "name": "New Caledonia"
      },
      {
        "code": "VU",
        "name": "Vanuatu"
      },
      {
        "code": "NZ",
        "name": "New Zealand"
      },
      {
        "code": "NI",
        "name": "Nicaragua"
      },
      {
        "code": "NE",
        "name": "Niger"
      },
      {
        "code": "NG",
        "name": "Nigeria"
      },
      {
        "code": "NU",
        "name": "Niue"
      },
      {
        "code": "NF",
        "name": "Norfolk Island"
      },
      {
        "code": "NO",
        "name": "Norway"
      },
      {
        "code": "MP",
        "name": "Northern Mariana Islands"
      },
      {
        "code": "UM",
        "name": "United States Minor Outlying Islands"
      },
      {
        "code": "FM",
        "name": "Micronesia, Federated States of"
      },
      {
        "code": "MH",
        "name": "Marshall Islands"
      },
      {
        "code": "PW",
        "name": "Palau"
      },
      {
        "code": "PK",
        "name": "Pakistan"
      },
      {
        "code": "PA",
        "name": "Panama"
      },
      {
        "code": "PG",
        "name": "Papua New Guinea"
      },
      {
        "code": "PY",
        "name": "Paraguay"
      },
      {
        "code": "PE",
        "name": "Peru"
      },
      {
        "code": "PH",
        "name": "Philippines"
      },
      {
        "code": "PN",
        "name": "Pitcairn"
      },
      {
        "code": "PL",
        "name": "Poland"
      },
      {
        "code": "PT",
        "name": "Portugal"
      },
      {
        "code": "GW",
        "name": "Guinea-Bissau"
      },
      {
        "code": "TL",
        "name": "Timor-Leste"
      },
      {
        "code": "PR",
        "name": "Puerto Rico"
      },
      {
        "code": "QA",
        "name": "Qatar"
      },
      {
        "code": "RE",
        "name": "R\u00e9union"
      },
      {
        "code": "RO",
        "name": "Romania"
      },
      {
        "code": "RU",
        "name": "Russian Federation"
      },
      {
        "code": "RW",
        "name": "Rwanda"
      },
      {
        "code": "BL",
        "name": "Saint-Barthelemy"
      },
      {
        "code": "SH",
        "name": "Saint Helena, Ascension and Tristan Da Cunha"
      },
      {
        "code": "KN",
        "name": "Saint Kitts and Nevis"
      },
      {
        "code": "AI",
        "name": "Anguilla"
      },
      {
        "code": "LC",
        "name": "Saint Lucia"
      },
      {
        "code": "MF",
        "name": "Saint Martin (French part)"
      },
      {
        "code": "PM",
        "name": "Saint Pierre and Miquelon"
      },
      {
        "code": "VC",
        "name": "Saint Vincent and The Grenadines"
      },
      {
        "code": "SM",
        "name": "San Marino"
      },
      {
        "code": "ST",
        "name": "Sao Tome and Principe"
      },
      {
        "code": "SA",
        "name": "Saudi Arabia"
      },
      {
        "code": "SN",
        "name": "Senegal"
      },
      {
        "code": "RS",
        "name": "Serbia"
      },
      {
        "code": "SC",
        "name": "Seychelles"
      },
      {
        "code": "SL",
        "name": "Sierra Leone"
      },
      {
        "code": "SG",
        "name": "Singapore"
      },
      {
        "code": "SK",
        "name": "Slovakia"
      },
      {
        "code": "VN",
        "name": "Vietnam"
      },
      {
        "code": "SI",
        "name": "Slovenia"
      },
      {
        "code": "SO",
        "name": "Somalia"
      },
      {
        "code": "ZA",
        "name": "South Africa"
      },
      {
        "code": "ZW",
        "name": "Zimbabwe"
      },
      {
        "code": "ES",
        "name": "Spain"
      },
      {
        "code": "SS",
        "name": "South Sudan"
      },
      {
        "code": "SD",
        "name": "Sudan"
      },
      {
        "code": "EH",
        "name": "Western Sahara"
      },
      {
        "code": "SR",
        "name": "Suriname"
      },
      {
        "code": "SJ",
        "name": "Svalbard and Jan Mayen"
      },
      {
        "code": "SZ",
        "name": "Swaziland"
      },
      {
        "code": "SE",
        "name": "Sweden"
      },
      {
        "code": "CH",
        "name": "Switzerland"
      },
      {
        "code": "SY",
        "name": "Syrian Arab Republic"
      },
      {
        "code": "TJ",
        "name": "Tajikistan"
      },
      {
        "code": "TH",
        "name": "Thailand"
      },
      {
        "code": "TG",
        "name": "Togo"
      },
      {
        "code": "TK",
        "name": "Tokelau"
      },
      {
        "code": "TO",
        "name": "Tonga"
      },
      {
        "code": "TT",
        "name": "Trinidad and Tobago"
      },
      {
        "code": "AE",
        "name": "United Arab Emirates"
      },
      {
        "code": "TN",
        "name": "Tunisia"
      },
      {
        "code": "TR",
        "name": "Turkey"
      },
      {
        "code": "TM",
        "name": "Turkmenistan"
      },
      {
        "code": "TC",
        "name": "Turks and Caicos Islands"
      },
      {
        "code": "TV",
        "name": "Tuvalu"
      },
      {
        "code": "UG",
        "name": "Uganda"
      },
      {
        "code": "UA",
        "name": "Ukraine"
      },
      {
        "code": "MK",
        "name": "Macedonia, The Former Yugoslav Republic of"
      },
      {
        "code": "EG",
        "name": "Egypt"
      },
      {
        "code": "GB",
        "name": "United Kingdom"
      },
      {
        "code": "GG",
        "name": "Guernsey"
      },
      {
        "code": "JE",
        "name": "Jersey"
      },
      {
        "code": "IM",
        "name": "Isle of Man"
      },
      {
        "code": "TZ",
        "name": "Tanzania, United Republic of"
      },
      {
        "code": "US",
        "name": "United States"
      },
      {
        "code": "VI",
        "name": "Virgin Islands, U.S."
      },
      {
        "code": "BF",
        "name": "Burkina Faso"
      },
      {
        "code": "UY",
        "name": "Uruguay"
      },
      {
        "code": "UZ",
        "name": "Uzbekistan"
      },
      {
        "code": "VE",
        "name": "Venezuela, Bolivarian Republic of"
      },
      {
        "code": "WF",
        "name": "Wallis and Futuna"
      },
      {
        "code": "WS",
        "name": "Samoa"
      },
      {
        "code": "YE",
        "name": "Yemen"
      },
      {
        "code": "ZM",
        "name": "Zambia"
      }
    ]
  },
  {
    "code": "FBP",
    "name": "Flying Blue+",
    "sandbox": {
      "baseDomain": "https:\/\/uat-plus-secure.flyingblue.com",
      "enabled": true
    },
    "live": {
      "baseDomain": "https:\/\/plus-secure.flyingblue.com",
      "enabled": true
    },
    "applicableCountries": [
      {
        "code": "FR",
        "name": "France"
      },
      {
        "code": "NL",
        "name": "Netherlands"
      }
    ]
  }
]
';

    protected function setUp(): void
    {
        $this->dataStorage = $this->createMock(DataInterface::class);
        $this->paymentsReader = new PaymentsReader($this->dataStorage);
    }

    public function testAavailablePointspayMethodsReturnsExpectedMethods(): void
    {
        $expectedMethods = json_decode($this->expectedMethods);
        $this->dataStorage->method('get')->willReturn($expectedMethods);

        $this->assertEquals($expectedMethods, $this->paymentsReader->getAvailablePointspayMethods());
    }

    public function testAvailablePointspayMethodsReturnsEmptyArrayWhenNoMethods(): void
    {
        $this->dataStorage->method('get')->willReturn([]);

        $this->assertEquals([], $this->paymentsReader->getAvailablePointspayMethods());
    }

    public function testResetStorageCallsResetOnDataStorage(): void
    {
        $this->dataStorage->expects($this->once())->method('reset');

        $this->paymentsReader->resetStorage();
    }
}
