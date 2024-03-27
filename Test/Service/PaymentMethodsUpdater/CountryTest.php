<?php

namespace Pointspay\Pointspay\Test\Service\PaymentMethodsUpdater;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Service\PaymentMethodsUpdater\Country;
use Pointspay\Pointspay\Service\PaymentsReader;

class CountryTest extends TestCase
{
    private $country;
    private $configWriter;
    private $storeManager;
    private $paymentsReader;

    /**
     * @var \Magento\Framework\App\ResourceConnection|(\Magento\Framework\App\ResourceConnection&\object&\PHPUnit\Framework\MockObject\MockObject)|(\Magento\Framework\App\ResourceConnection&\PHPUnit\Framework\MockObject\MockObject)|(\object&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resourceConncetionMock;

    protected function setUp(): void
    {
        $this->configWriter = $this->createMock(WriterInterface::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $website1 = $this->createMock(\Magento\Store\Model\Website::class);
        $website1->expects($this->any())->method('getId')->willReturn(1);
        $website2 = $this->createMock(\Magento\Store\Model\Website::class);
        $website2->expects($this->any())->method('getId')->willReturn(2);
        $website3 = $this->createMock(\Magento\Store\Model\Website::class);
        $website3->expects($this->any())->method('getId')->willReturn(3);
        $this->storeManager->method('getWebsites')->willReturn([$website1, $website2,$website3]);
        $this->paymentsReader = $this->createMock(PaymentsReader::class);
        $this->resourceConncetionMock = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $connection = $this->createMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class);
        $select = $this->createMock(\Magento\Framework\DB\Select::class);
        $connection->expects($this->any())->method('select')->willReturn($select);
        $currentData = [
            [
                'path' => 'payment/pointspay_required_settings/specificcountry',
                'value' => 'US,UK',
                'scope' => 'websites',
                'scope_id' => '1',
            ],
            [
                'path' => 'payment/pointspay_required_settings/specificcountry',
                'value' => 'CA,AU',
                'scope' => 'default',
                'scope_id' => '0',
            ],
            [
                'path' => 'payment/pointspay_required_settings/specificcountry',
                'value' => 'CA,AU',
                'scope' => 'websites',
                'scope_id' => '0',
            ],

        ];
        $connection->expects($this->any())->method('fetchAll')->willReturn($currentData);
        $this->resourceConncetionMock->expects($this->any())->method('getConnection')
            ->willReturn($connection);
        $this->country = new Country(
            $this->configWriter,
            $this->storeManager,
            $this->paymentsReader,
            $this->resourceConncetionMock
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testCountryUpdatesPaymentMethods()
    {
        $methods = [
            [
                'pointspay_code' => 'method1',
                'applicableCountries' => [
                    ['code' => 'US'],
                    ['code' => 'UK']
                ]
            ],
            [
                'pointspay_code' => 'method2',
                'applicableCountries' => [
                    ['code' => 'CA'],
                    ['code' => 'AU']
                ]
            ]
        ];

        $this->paymentsReader->method('getAvailablePointspayMethods')->willReturn($methods);

        $this->configWriter->expects($this->any())->method('save');

        $this->country->execute();
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testCountryHandlesNoApplicableCountries()
    {
        $methods = [
            [
                'pointspay_code' => 'method1',
            ],
            [
                'pointspay_code' => 'method2',
            ]
        ];

        $this->paymentsReader->method('getAvailablePointspayMethods')->willReturn($methods);

        $this->configWriter->expects($this->any())->method('save');

        $this->country->execute();
    }
}
