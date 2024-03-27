<?php

namespace Pointspay\Pointspay\Gateway\Request;

use InvalidArgumentException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order\Payment;
use Psr\Log\LoggerInterface;


class RefundDataBuilder implements BuilderInterface
{
    use Formatter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param SubjectReader $subjectReader
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function build(array $buildSubject)
    {
        $paymentDataObject = SubjectReader::readPayment($buildSubject);
        /** @var Payment $payment */
        $payment = $paymentDataObject->getPayment();

        $amount = null;

        try {
            $amount = $this->formatPrice( SubjectReader::readAmount($buildSubject));
        } catch (InvalidArgumentException $e) {
            $this->logger->critical($e->getMessage());
        }

        /**
         * we should remember that Payment sets Capture txn id of current Invoice into ParentTransactionId Field
         * and cut off '-capture' postfix from transaction ID to support backward compatibility
         */
        $txnId = str_replace(
            '-' . TransactionInterface::TYPE_CAPTURE,
            '',
            $payment->getParentTransactionId()
        );

        return [
            'transaction_id' => $txnId,
            'amount' => $amount
        ];
    }
}
