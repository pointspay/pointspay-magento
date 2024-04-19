<?php

namespace Pointspay\Pointspay\Service\Api\Success\PaymentProcessor;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction;
use Pointspay\Pointspay\Api\IpnInterface;

class Ipn extends \Pointspay\Pointspay\Service\Api\Success\PaymentProcessor
{
    /**
     * @param \Magento\Sales\Model\Order $order
     * @param array $gatewayData
     * @return false|float|\Magento\Framework\DataObject|\Magento\Sales\Api\Data\OrderPaymentInterface|mixed|null
     * @throws \Exception
     */
    protected function createTransaction(Order $order, array $gatewayData)
    {
        $payment = $order->getPayment();

        $trans_id = $gatewayData[IpnInterface::PAYMENT_ID];
        $payment->setLastTransId($trans_id);
        $payment->setTransactionId($trans_id);

        $formatedPrice = $order->getBaseCurrency()->formatTxt(
            $order->getGrandTotal()
        );

        $message = __('The Captured amount is %1 (by IPN).', $formatedPrice);
        $transaction = $this->transactionBuilder->setPayment($payment)
            ->setOrder($order)
            ->setTransactionId($trans_id)
            ->setAdditionalInformation(
                [Transaction::RAW_DETAILS => (array)$payment->getAdditionalInformation()]
            )
            ->setFailSafe(true)
            //build method creates the transaction and returns the object
            ->build(Transaction::TYPE_CAPTURE);
        $transaction->setParentTxnId($trans_id);
        $transaction->setIsClosed(0);
        $payment->addTransactionCommentsToOrder(
            $transaction,
            $message
        );
        $payment->setParentTransactionId(null);

        $payment->save();
        $transaction->save();

        return $payment;
    }
}
