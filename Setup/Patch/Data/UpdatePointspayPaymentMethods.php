<?php

namespace Pointspay\Pointspay\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Pointspay\Pointspay\Service\PaymentMethodsUpdater;

class UpdatePointspayPaymentMethods implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var PaymentMethodsUpdater
     */
    private PaymentMethodsUpdater $paymentMethodsUpdater;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param PaymentMethodsUpdater $paymentMethodsUpdater
     */
    public function __construct(ModuleDataSetupInterface $moduleDataSetup, PaymentMethodsUpdater $paymentMethodsUpdater)
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->paymentMethodsUpdater = $paymentMethodsUpdater;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $salesOrderPaymentTable = $this->moduleDataSetup->getTable('sales_order_payment');

        $select = $this->moduleDataSetup->getConnection()->select()
            ->from($salesOrderPaymentTable, ['entity_id', 'method', 'additional_information'])
            ->where('method LIKE ?', '%_required_settings%');

        $payments = $this->moduleDataSetup->getConnection()->fetchAll($select);

        foreach ($payments as $payment) {
            $methodParts = explode('_', $payment['method']);
            $flavor = $methodParts[0]; // "fbp" or other prefix

            $additionalInfo = json_decode($payment['additional_information'], true) ?: [];
            $additionalInfo['pointspay_flavor'] = $flavor;

            $this->moduleDataSetup->getConnection()->update(
                $salesOrderPaymentTable,
                ['additional_information' => json_encode($additionalInfo)],
                ['entity_id = ?' => $payment['entity_id']]
            );
        }

        $this->moduleDataSetup->getConnection()->update(
            $salesOrderPaymentTable,
            ['method' => 'pointspay_required_settings'],
            ['method LIKE ?' => '%_required_settings%']
        );

        $this->moduleDataSetup->getConnection()->update(
            $this->moduleDataSetup->getTable('sales_order_grid'),
            ['payment_method' => 'pointspay_required_settings'],
            ['payment_method LIKE ?' => '%_required_settings%']
        );

        $this->paymentMethodsUpdater->execute();

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
