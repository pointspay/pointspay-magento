<?php

namespace Pointspay\Pointspay\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order\Payment;
use Pointspay\Pointspay\Gateway\Http\Client\TransactionVirtualPayment\Response;

class PaymentCommentHistoryRefundHandler implements HandlerInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * TransactionIdHandler constructor.
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }
    /**
     * @param array $handlingSubject
     * @param array $response
     * @return $this
     */
    public function handle(array $handlingSubject, array $response)
    {
        $readPayment = $this->subjectReader->readPayment($handlingSubject);

        $payment = $readPayment->getPayment();

        if ($payment instanceof Payment && $response['body']['status'] == Response::ACCEPTED_REFUND_STATUS) {

            $transaction = $response['body']['payment_id'];

            /** @var Payment $orderPayment */
            $orderPayment = $payment;
            $this->setTransactionId(
                $orderPayment,
                $transaction
            );
        }

        return $this;
    }

    /**
     * Set transaction Id
     *
     * @param Payment $orderPayment
     * @param $transactionId
     * @return void
     */
    protected function setTransactionId(Payment $orderPayment, $transactionId)
    {
        $orderPayment->setTransactionId($transactionId);
    }
}
