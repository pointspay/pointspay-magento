<?php

namespace Pointspay\Pointspay\Service\Signature;

use Magento\Framework\App\RequestInterface;
use Pointspay\Pointspay\Api\IpnInterface;
use Pointspay\Pointspay\Service\Signature\Validator\Parse;
use Pointspay\Pointspay\Model\Ipn;
use Pointspay\Pointspay\Helper\Config;
class IpnValidator
{
    /**
     * @var \Pointspay\Pointspay\Service\Signature\Validator\Parse
     */
    private $headerParser;

    /**
     * @var Ipn
     */
    private $ipnModel;

    /**
     * @var Config
     */
    private $configHelper;

    /**
     * @param Ipn $ipnModel
     * @param Config $configHelper
     * @param Parse $headerParser
     */
    public function __construct(
        Ipn                 $ipnModel,
        Config              $configHelper,
        Parse               $headerParser
    )
    {
        $this->headerParser = $headerParser;
        $this->ipnModel = $ipnModel;
        $this->configHelper = $configHelper;
    }

    /**
     * @param RequestInterface $request
     * @return bool
     */
    public function validate($request)
    {
        if(!$this->validateIpnData($request)) return false;
        $data = $this->prepareData($request);
        $signatureStatus = false;
        if (!empty($data['header']['authorization'])) {
            $headerParams = $this->headerParser->parse($data['header']['authorization']);
            $signature = base64_decode($headerParams->getOauthSignature());
            $bodyToProcess = $data['body'];
            $publicCertificate = $this->getCertificate($bodyToProcess);

            $bodyToVerify = $this->castArrayToString($bodyToProcess);
            $paramsToAppend = sprintf('%s%s%s%s', $headerParams->getOauthConsumerKey(), 'SHA256withRSA', $headerParams->getOauthNonce(), $headerParams->getOauthTimestamp());
            $messageToVerify = mb_convert_encoding(sprintf('%s%s', $bodyToVerify, $paramsToAppend), 'UTF-8');
            $validationResult = openssl_verify($messageToVerify, $signature, $publicCertificate, 'sha256WithRSAEncryption');
            $signatureStatus = $validationResult === 1;
        }
        return $signatureStatus;
    }


    /**
     * @param  $request
     * @return bool
     */
    private function validateIpnData($request)
    {
       if (!$request->isPut()) return false;
        $ipnData = $request->getContent();
        $data = json_decode($ipnData, true);
        if(empty($data)) {
            return false;
        }
        if(!isset($data[IpnInterface::ORDER_ID])
            || !isset($data[IpnInterface::PAYMENT_ID])
            || !isset($data[IpnInterface::STATUS])
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param RequestInterface $request
     * @return array
     */
    private function prepareData($request)
    {
        $data = [];
        $data['header']['authorization'] = $request->getHeader('authorization');
        $ipnData = $request->getContent();
        $data['body'] = json_decode($ipnData, true);
        return $data;
    }

    private function getCertificate($bodyInfo)
    {
        if(empty($bodyInfo['order_id'])) return '';

        try {
            $order = $this->ipnModel->getOrder($bodyInfo);
            $storeId = $order->getStoreId();
            $paymentCode = $order->getPayment()->getAdditionalInformation('pointspay_flavor');
            return $this->getPaymentConfigCertInfo($paymentCode, $storeId);
        } catch (\Exception $e) {
            return '';
        }
    }

    private function getPaymentConfigCertInfo($method, $storeId = 0)
    {
        $certificate = $this->configHelper->getPointspayCertificate($method, $storeId);
        if($storeId !=0 && empty($certificate)){
            $certificate = $this->configHelper->getPointspayCertificate($method);
        }

        return $certificate;
    }

    private function castArrayToString($body)
    {
        $body = array_filter($body);
        $this->recursiveArraySort($body);
        return mb_convert_encoding(json_encode($body,JSON_UNESCAPED_SLASHES), 'UTF-8');
    }

    private function recursiveArraySort(&$array)
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $this->recursiveArraySort($value);
            }
        }
        ksort($array);
    }

}
