<?php

namespace Pointspay\Pointspay\Service\Signature;

use Magento\Framework\App\RequestInterface;
use Pointspay\Pointspay\Api\IpnInterface;
use Pointspay\Pointspay\Service\Signature\Validator\Parse;
use Pointspay\Pointspay\Model\Ipn;
use Pointspay\Pointspay\Helper\Config;
use Pointspay\Pointspay\Service\Checkout\Service;
class RedirectValidator
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
     * @var Service
     */
    private $service;

    /**
     * @param Ipn $ipnModel
     * @param Config $configHelper
     * @param Parse $headerParser
     */
    public function __construct(
        Ipn                 $ipnModel,
        Config              $configHelper,
        Parse               $headerParser,
        Service             $service
    )
    {
        $this->headerParser = $headerParser;
        $this->ipnModel = $ipnModel;
        $this->configHelper = $configHelper;
        $this->service = $service;
    }

    /**
     * @param int $success
     * @param RequestInterface $request
     * @return bool
     */
    public function validate($request, $success = 0): bool
    {
        $bodyContent = $request->getContent();
        $postData = $this->service->restorePostData($bodyContent);
        if(!$this->validatePostData($postData, $success)) return false;

        $signature = ($success)?base64_decode($postData[IpnInterface::OAUTH_SIGNATURE]):base64_decode($postData[IpnInterface::OAUTHSIGNATURE]);
        $publicCertificate = $this->getCertificate($postData);
        $messageToVerify = $postData[IpnInterface::ORDER_ID].$postData[IpnInterface::PAYMENT_ID].$postData[IpnInterface::STATUS].$postData[IpnInterface::AUTHORIZATION];
        $validationResult = openssl_verify($messageToVerify, $signature, $publicCertificate, 'sha256WithRSAEncryption');
        return $validationResult === 1;
    }


    /**
     * @param  array $data
     * @param  int $success
     * @return bool
     */
    private function validatePostData($data, $success = 0):bool
    {
        if(empty($data)) {
            return false;
        }
        if(!$success && !isset($data[IpnInterface::OAUTHSIGNATURE])){
            return false;
        }elseif($success && !isset($data[IpnInterface::OAUTH_SIGNATURE])){
            return false;
        }
        if(!isset($data[IpnInterface::ORDER_ID])
            || !isset($data[IpnInterface::PAYMENT_ID])
            || !isset($data[IpnInterface::STATUS])
            || !isset($data[IpnInterface::AUTHORIZATION])
        ) {
            return false;
        }

        return true;
    }


    private function getCertificate($bodyInfo)
    {
        if(empty($bodyInfo['order_id'])) return '';

        try {
            $order = $this->ipnModel->getOrder($bodyInfo);
            $storeId = $order->getStoreId();
            $paymentCode = $order->getPayment()->getMethod();
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

}
