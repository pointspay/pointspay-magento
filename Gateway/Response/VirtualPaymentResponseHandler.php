<?php

namespace Pointspay\Pointspay\Gateway\Response;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Pointspay\Pointspay\Model\Ui\PointspayVirtualConfigProvider;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;

class VirtualPaymentResponseHandler implements HandlerInterface
{
    /**
     * @param array $handlingSubject
     * @param array $response
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDataObject = SubjectReader::readPayment($handlingSubject);
        $stateObject = SubjectReader::readStateObject($handlingSubject);

        $stateObject->setState(Order::STATE_PENDING_PAYMENT);
        $stateObject->setStatus(Order::STATE_PENDING_PAYMENT);
        $stateObject->setData('is_notified', false);

        $payment = $paymentDataObject->getPayment();
        $payment->setIsTransactionPending(true);


        if (!empty($response['body'][PointspayVirtualConfigProvider::HREF])) {
            $payment->setAdditionalInformation(PointspayVirtualConfigProvider::HREF, $response['body'][PointspayVirtualConfigProvider::HREF]);
        }

        if (!empty($response['body'][PointspayVirtualConfigProvider::CREATED_AT])) {
            $payment->setAdditionalInformation(PointspayVirtualConfigProvider::CREATED_AT, $response['body'][PointspayVirtualConfigProvider::CREATED_AT]);
        }

        if (!empty($response['body'][PointspayVirtualConfigProvider::ORDER_ID])) {
            $payment->setAdditionalInformation(PointspayVirtualConfigProvider::ORDER_ID, $response['body'][PointspayVirtualConfigProvider::ORDER_ID]);
        }

        if (!empty($response['body'][PointspayVirtualConfigProvider::PAYMENT_ID])) {
            $payment->setAdditionalInformation(PointspayVirtualConfigProvider::PAYMENT_ID, $response['body'][PointspayVirtualConfigProvider::PAYMENT_ID]);
            if ($payment instanceof Payment) {
                $payment->setTransactionId($response['body'][PointspayVirtualConfigProvider::PAYMENT_ID]);
                $payment->setLastTransId($response['body'][PointspayVirtualConfigProvider::PAYMENT_ID]);
                $transaction = $payment->addTransaction(TransactionInterface::TYPE_PAYMENT);
                $transaction->setIsClosed(0);
                foreach ($response as $key => $value) {
                    $transaction->setAdditionalInformation($key, $value);
                }
                $payment->addTransactionCommentsToOrder($transaction, 'Payment ID: ' . $response['body'][PointspayVirtualConfigProvider::PAYMENT_ID]);
            }

        }

        if (!empty($response['body'][PointspayVirtualConfigProvider::STATUS])) {
            $payment->setAdditionalInformation(PointspayVirtualConfigProvider::STATUS, $response['body'][PointspayVirtualConfigProvider::STATUS]);
        }

        if (!empty($response['body'][PointspayVirtualConfigProvider::STATUS_MESSAGE])) {
            $payment->setAdditionalInformation(PointspayVirtualConfigProvider::STATUS_MESSAGE, $response['body'][PointspayVirtualConfigProvider::STATUS_MESSAGE]);
        }

        if (!empty($response['body'][PointspayVirtualConfigProvider::STATUS_CODE])) {
            $payment->setAdditionalInformation(PointspayVirtualConfigProvider::STATUS_CODE, $response['body'][PointspayVirtualConfigProvider::STATUS_CODE]);
        }

        if (!empty($response['body'][PointspayVirtualConfigProvider::MESSAGE])) {
            $payment->setAdditionalInformation(PointspayVirtualConfigProvider::MESSAGE, $response['body'][PointspayVirtualConfigProvider::MESSAGE]);
        }

        if (!empty($response['body'][PointspayVirtualConfigProvider::KEY])) {
            $payment->setAdditionalInformation(PointspayVirtualConfigProvider::KEY, $response['body'][PointspayVirtualConfigProvider::KEY]);
        }

        // do not close transaction so you can do a cancel() and void
        $payment->setIsTransactionClosed(false);
        $payment->setShouldCloseParentTransaction(false);
    }
}
