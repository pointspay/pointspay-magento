<?php

namespace Pointspay\Pointspay\Gateway\Http\Client\TransactionVirtualPayment;


use Magento\Framework\Serialize\SerializerInterface;

class Response
{
    const ACCEPTED_STATUS = 'accepted';
    const ACCEPTED_REFUND_STATUS = 'success';

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param array $request
     * @param \Magento\Framework\HTTP\AsyncClient\HttpResponseDeferredInterface $response
     * @return array
     * @throws \Throwable
     */
    public function process(array $request, $response)
    {
        $responseArr['original_body'] = $response->get()->getBody();
        $responseArr['body'] = $this->serializer->unserialize($response->get()->getBody());
        $responseArr['header'] = $response->get()->getHeaders();
        $responseArr['status_code'] = $this->serializer->unserialize($response->get()->getStatusCode());
        $responseArr['request'] = $request;
        return $responseArr;
    }
}
