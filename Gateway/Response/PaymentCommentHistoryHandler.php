<?php

namespace Pointspay\Pointspay\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Pointspay\Pointspay\Model\Ui\PointspayVirtualConfigProvider;
use Psr\Log\LoggerInterface;

class PaymentCommentHistoryHandler implements HandlerInterface
{
    /** @var LoggerInterface $logger */
    private $logger;

    /**
     * PaymentCaptureDetailsHandler constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     * @return $this
     */
    public function handle(array $handlingSubject, array $response)
    {
        $readPayment = \Magento\Payment\Gateway\Helper\SubjectReader::readPayment($handlingSubject);

        $payment = $readPayment->getPayment();

        $statusCode = $this->getStatusCode($response);
        $statusMessage = $this->getStatusMessage($response);
        $hrefLink = $this->getHrefLink($response);
        $this->logger->addResult('Payment ID response', $response);
        $type = 'Result response:';
        $comment = __(
            '%1 <br /> Status: %2 <br /> Status message: %3 <br /> Href link : %4 ',
            $type,
            $statusCode,
            $statusMessage,
            $hrefLink
        );

        if ($statusCode) {
            $payment->getOrder()->setResultEventCode($statusCode);
        }

        $payment->getOrder()->addStatusHistoryComment($comment, $payment->getOrder()->getStatus());
        return $this;
    }

    /**
     * Search the passed response array for the response code
     *
     * @param array $response
     * @return string
     */
    private function getStatusCode($response)
    {
        if (isset($response['body'][PointspayVirtualConfigProvider::STATUS_CODE])) {
            $responseCode = $response['body'][PointspayVirtualConfigProvider::STATUS_CODE];
        } else {
            $responseCode = isset($response['status_code']) ? $response['status_code'] : 'no_code';
        }

        return $responseCode;
    }

    /**
     * @param array $response
     * @return string
     */
    private function getStatusMessage($response)
    {
        if (isset($response['body'][PointspayVirtualConfigProvider::STATUS_MESSAGE])) {
            $statusMessage = $response['body'][PointspayVirtualConfigProvider::STATUS_MESSAGE];
        } else {
            $statusMessage = '';
        }

        return $statusMessage;
    }

    private function getHrefLink(array $response)
    {
        if (isset($response['body'][PointspayVirtualConfigProvider::HREF])) {
            $responseCode = $response['body'][PointspayVirtualConfigProvider::HREF];
        } else {
            $responseCode = isset($response['status_code']) ? $response['status_code'] : 'no_code';
        }

        return $responseCode;
    }
}
