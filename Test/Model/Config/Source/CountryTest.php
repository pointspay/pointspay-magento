<?php

namespace Pointspay\Pointspay\Test\Model\Config\Source;

use Magento\Directory\Model\ResourceModel\Country\Collection;
use Magento\Framework\Locale\TranslatedLists;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Helper\Config;
use Pointspay\Pointspay\Model\Config\Source\Country;
use Pointspay\Pointspay\Service\PaymentsReader;

class CountryTest extends TestCase
{
    private $countryCollection;

    private $configHelper;

    /**
     * @var Country
     */
    private $country;

    public function testProcessLikeRegularSourceModelReturnsExpectedResult()
    {
        $foregroundCountries = ['US', 'CA'];
        $this->countryCollection->expects($this->once())
            ->method('loadData')
            ->willReturnSelf();
        $this->countryCollection->expects($this->once())
            ->method('setForegroundCountries')
            ->with($foregroundCountries)
            ->willReturnSelf();
        $this->countryCollection->expects($this->once())
            ->method('toOptionArray')
            ->with(false)
            ->willReturn(['US' => 'United States', 'CA' => 'Canada']);

        $this->assertEquals(['US' => 'United States', 'CA' => 'Canada'], $this->country->toOptionArray(true, $foregroundCountries));
    }

    public function testProcessLikePointspaySourceModelReturnsExpectedResult()
    {
        $foregroundCountries = ['US', 'CA'];
        $applicableCountries = [['code' => 'US'], ['code' => 'CA']];

        $ppMethods = ['pointspay' => [
            'applicableCountries' => $applicableCountries
        ]];
        $paymentReaderMock = $this->createMock(PaymentsReader::class);
        $paymentReaderMock->expects($this->once())
            ->method('getAvailablePointspayMethods')
            ->willReturn($ppMethods);

        $this->configHelper->expects($this->once())
            ->method('getPaymentsReader')
            ->willReturn($paymentReaderMock);

        $objectManager = new ObjectManager($this);
        $localeList = $this->createMock(TranslatedLists::class);
        $localeList->expects($this->any())
            ->method('getCountryTranslation')
            ->willReturnMap([
                ['US', null, 'United States'],
                ['CA', null, 'Canada']
            ]);
        $this->country = $objectManager->getObject(
            Country::class,
            [
                'countryCollection' => $this->countryCollection,
                'configHelper' => $this->configHelper,
                'localeLists' => $localeList
            ]
        );
        $this->country->setPath('pointspay_required_settings/specificcountry');
        $result = $this->country->toOptionArray(true, $foregroundCountries);
        $this->assertEquals([['label' => 'United States', 'value' => 'US', 'is_region_visible' => false], ['label' => 'Canada', 'value' => 'CA', 'is_region_visible' => false]], $result);
    }

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->countryCollection = $this->createMock(Collection::class);
        $this->configHelper = $this->createMock(Config::class);

        $this->country = $objectManager->getObject(
            Country::class,
            [
                'countryCollection' => $this->countryCollection,
                'configHelper' => $this->configHelper
            ]
        );
    }
}
