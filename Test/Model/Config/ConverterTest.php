<?php

namespace Pointspay\Pointspay\Test\Model\Config;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Model\Config\Converter;
use Pointspay\Pointspay\Api\Data\PointspayGeneralPaymentInterface;

class ConverterTest extends TestCase
{
    private $converter;

    public function testCnvertWithValidData(): void
    {
        $source = new DOMDocument();
        $source->loadXML('<payment><pointspay_methods><type id="1" order="1"><label>Pointspay 1</label><pointspay_code>pp</pointspay_code><sandbox><country>US</country></sandbox></type></pointspay_methods></payment>');

        $expected = [
            'pointspay_methods' => [
                '1' => [
                    'name' => 'Pointspay 1',
                    'pointspay_code' => PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS,
                    'sandbox' => ['US'],
                    'order' => '1'
                ]
            ]
        ];

        $this->assertEquals($expected, $this->converter->convert($source));
    }

    public function testConvertWithMultipleMethods(): void
    {
        $source = new DOMDocument();
        $xml='<payment>
    <pointspay_methods>
        <type id="1" order="1">
            <label>Pointspay 1</label>
            <pointspay_code>pp</pointspay_code>
            <sandbox>
                <enabled>true</enabled>
                <baseDomain>https://api.pointspay.com/</baseDomain>
            </sandbox>
            <live>
                <enabled>true</enabled>
                <baseDomain>https://api.pointspay.com/</baseDomain>
            </live>
            <applicableCountries>
                <country>US</country>
            </applicableCountries>
        </type>
        <type id="2" order="2">
            <label>Pointspay 2</label>
            <pointspay_code>pp2</pointspay_code>
            <sandbox>
                <enabled>true</enabled>
                <baseDomain>https://api.pointspay.com/</baseDomain>
            </sandbox>
            <live>
                <enabled>true</enabled>
                <baseDomain>https://api.pointspay.com/</baseDomain>
            </live>
            <applicableCountries>
                <country>US</country>
            </applicableCountries>
        </type>
    </pointspay_methods>
</payment>';
        $source->loadXML($xml);

        $expected = [
            'pointspay_methods' => [
                '1' => [
                    'name' => 'Pointspay 1',
                    'pointspay_code' => PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS,
                    'sandbox' => ['enabled'=> 'true', 'baseDomain'=> 'https://api.pointspay.com/'],
                    'live' => ['enabled'=> 'true', 'baseDomain'=> 'https://api.pointspay.com/'],
                    'applicableCountries' => ['US'],
                    'order' => '1'
                ],
                '2' => [
                    'name' => 'Pointspay 2',
                    'pointspay_code' => 'pp2',
                    'sandbox' => ['enabled'=> 'true', 'baseDomain'=> 'https://api.pointspay.com/'],
                    'live' => ['enabled'=> 'true', 'baseDomain'=> 'https://api.pointspay.com/'],
                    'applicableCountries' => ['US'],
                    'order' => '2'
                ]
            ]
        ];

        $this->assertEquals($expected, $this->converter->convert($source));
    }

    public function testConvertWithNoMethods(): void
    {
        $source = new DOMDocument();
        $source->loadXML('<payment><pointspay_methods></pointspay_methods></payment>');

        $expected = [
            'pointspay_methods' => []
        ];

        $this->assertEquals($expected, $this->converter->convert($source));
    }

    protected function setUp(): void
    {
        $this->converter = new Converter();
    }
}
