<?php

namespace Pointspay\Pointspay\Gateway\Response;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Response\HandlerInterface;
class PaymentRefundDetailsHandler  implements HandlerInterface
{

    /**
     * @param array $handlingSubject
     * @param array $response
     * @throws AlreadyExistsException|LocalizedException
     */
    public function handle(array $handlingSubject, array $response)
    {
        $payment = \Magento\Payment\Gateway\Helper\SubjectReader::readPayment($handlingSubject);


        $payment = $payment->getPayment();

        foreach ($response as $singleResponse) {
            if (isset($singleResponse['error'])) {
                throw new LocalizedException(
                    "The refund failed. Please make sure the amount is not greater than the limit or negative.
                    Otherwise, refer to the logs for details."
                );
            }

            // set pspReference as lastTransId only!
            $payment->setLastTransId($response['body']['payment_id']);
        }

        /**
         * close current transaction because you have refunded the goods
         * but only on full refund close the authorisation
         */
        $payment->setIsTransactionClosed(true);
        $closeParent = !(bool)$payment->getCreditmemo()->getInvoice()->canRefund();
        $payment->setShouldCloseParentTransaction($closeParent);
    }
}
