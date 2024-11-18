<?php

namespace Pointspay\Pointspay\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Pointspay\Pointspay\Model\FlavourKeysFactory as FlavourKeysFactory;
use Pointspay\Pointspay\Model\ResourceModel\FlavourKeys;
use Pointspay\Pointspay\Model\ResourceModel\FlavourKeys\CollectionFactory;

class CertificateHandler
{
    /**
     * @var \Pointspay\Pointspay\Model\ResourceModel\FlavourKeys
     */
    private $flavourKeysResourceModel;

    /**
     * @var \Pointspay\Pointspay\Model\ResourceModel\FlavourKeys\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Pointspay\Pointspay\Model\FlavourKeysFactory
     */
    private $flavourKeysFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param \Pointspay\Pointspay\Model\ResourceModel\FlavourKeys $flavourKeysResourceModel
     * @param \Pointspay\Pointspay\Model\ResourceModel\FlavourKeys\CollectionFactory $collectionFactory
     * @param \Pointspay\Pointspay\Model\FlavourKeysFactory $flavourKeysFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        FlavourKeys $flavourKeysResourceModel,
        CollectionFactory $collectionFactory,
        FlavourKeysFactory $flavourKeysFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->flavourKeysResourceModel = $flavourKeysResourceModel;
        $this->collectionFactory = $collectionFactory;
        $this->flavourKeysFactory = $flavourKeysFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * To get the certificate and private key by specific code and website id
     * please use code without <code>_required_settings suffix, just code
     * for example:
     * for general payment use code "pointspay"
     * for sub payment method like Flying Blue+ use code "fbp" and so on
     *
     * @param $code
     * @param null $websiteId
     * @return \Magento\Framework\DataObject|\Pointspay\Pointspay\Model\FlavourKeys
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function get($code, $websiteId = null)
    {
        $code .= '_required_settings';
        $collection = $this->collectionFactory->create();
        $collection->setOrder('website_id', 'DESC');
        $collection->addFieldToFilter('payment_code', $code);
        if ($websiteId !== null) {
            $collection->addFieldToFilter(
                ['website_id', 'website_id'],
                [
                   [ 'eq' => 0],
                   [ 'eq' => $websiteId]
                ]
            );
        }
        $result = $collection->getFirstItem();
        if (!$result->getId()) {
            $result = $this->create($code, $websiteId);
        }
        return $result;
    }

    /**
     * @param $code
     * @param $websiteId
     * @return mixed
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function create($code, $websiteId = null)
    {
        $result = $this->generate($code, $websiteId);
        $newCertModel = $this->flavourKeysFactory->create();
        $newCertModel->setData([
            'payment_code' => $code,
            'website_id' => $websiteId ?: 0
        ]);
        $newCertModel->setPrivateKey($result['private_key']);
        $newCertModel->setCertificate($result['certificate']);
        $this->flavourKeysResourceModel->save($newCertModel);
        return $newCertModel;
    }

    /**
     * @param $code
     * @param $websiteId
     * @return array
     */
    public function generate($code, $websiteId)
    {
        $domain = $this->scopeConfig->getValue('web/secure/base_url', 'website', $websiteId);
        $domainHost = parse_url($domain, PHP_URL_HOST);
        $websiteCountry = $this->scopeConfig->getValue('general/country/default', 'website', $websiteId) ?: "US";
        $generalEmail = $this->scopeConfig->getValue('trans_email/ident_general/email', 'website', $websiteId) ?: 'info@' . $domainHost;
        $details = [
            "commonName" => $domainHost,
            "countryName" => $websiteCountry,
            "stateOrProvinceName" => $websiteCountry,
            "localityName" => $websiteCountry,
            "organizationName" => $domainHost,
            "organizationalUnitName" => "Payment code: " . $code,
            "emailAddress" => $generalEmail
        ];
        $privateKey = openssl_pkey_new([
            "private_key_bits" => 8192,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ]);

        $csr = openssl_csr_new($details, $privateKey);
        $x509 = openssl_csr_sign($csr, null, $privateKey, 1095, ['digest_alg' => 'sha256WithRSAEncryption']);

        openssl_pkey_export($privateKey, $privateKeyString);
        openssl_x509_export($x509, $certificate);
        return [
            'certificate' => $certificate,
            'private_key' => $privateKeyString,
        ];
    }
}
