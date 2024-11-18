<?php

namespace Pointspay\Pointspay\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Pointspay\Pointspay\Block\System\Config\DownloadCertificate;
use Psr\Log\LoggerInterface;

/**
 * Class CustomFieldset
 *
 * This class represents a custom fieldset renderer for the Pointspay settings
 * in the Magento admin panel. It dynamically creates configuration fields for each
 * payment method, including Basic and Access Settings fieldsets with fields like
 * Enabled, Environment, and Consumer Key.
 */
class CustomFieldset extends Fieldset
{
    // Constants representing scope levels
    public const SCOPE_DEFAULT = 'default';

    public const SCOPE_WEBSITES = 'websites';

    public const SCOPE_STORES = 'stores';

    /**
     * Field renderer for fieldsets
     *
     * @var Fieldset
     */
    protected $_fieldsetRenderer;

    /**
     * Field renderer for merchant certificate
     *
     * @var DownloadCertificate
     */
    protected $_merchantCertificateRenderer;

    /**
     * Field renderer for individual fields
     *
     * @var Field
     */
    protected $_fieldRenderer;

    /**
     * Dummy element
     *
     * @var DataObject
     */
    protected $_dummyElement;

    /**
     * Logger instance for error tracking.
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Configuration scope manager for retrieving values by scope.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Serializer for encoding and decoding data.
     *
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * Store manager for retrieving store-related information.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    private $configData;

    /**
     * CustomFieldset constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param LoggerInterface $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context                     $context,
        \Magento\Backend\Model\Auth\Session                $authSession,
        \Magento\Framework\View\Helper\Js                  $jsHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Serialize\SerializerInterface   $serializer,
        \Magento\Store\Model\StoreManagerInterface         $storeManager,
        array                                              $data = []
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
        $this->storeManager = $storeManager;
        parent::__construct($context, $authSession, $jsHelper, $data);
    }

    /**
     * Retrieve a configuration value with fallback across scope levels.
     *
     * @param string $path The configuration path.
     * @param string $defaultFieldKey The key for the default value in case of absence.
     * @return mixed|null
     */
    public function getConfigValueWithFallback($path, $defaultFieldKey)
    {
        $scope = $this->getScope();
        $scopeCode = $this->getScopeCode();
        $data = null;

        $defaultClass = new SystemConfigDefaults();

        switch ($scope) {
            case self::SCOPE_STORES:
                // Attempt to get value from Store scope
                $data = $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $scopeCode);
                break;

            case self::SCOPE_WEBSITES:
                // Attempt to get value from Website scope
                $data = $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_WEBSITE, $scopeCode);
                break;

            default:
                // Attempt to get value from Default scope
                $data = $this->scopeConfig->getValue($path, 'default');
                break;
        }

        if ($data === null) {
            $data = $defaultClass->getDefaultValue($defaultFieldKey);
        }

        return $data;
    }

    /**
     * Get current scope code.
     *
     * @return string
     */
    public function getScopeCode()
    {
        $scopeCode = $this->getData('scope_code');
        if ($scopeCode === null) {
            if ($this->getStoreCode()) {
                $scopeCode = $this->getStoreCode();
            } elseif ($this->getWebsiteCode()) {
                $scopeCode = $this->getWebsiteCode();
            } else {
                $scopeCode = '';
            }
            $this->setScopeCode($scopeCode);
        }

        return $scopeCode;
    }


    /**
     * Get the code of the current website from the request parameters.
     *
     * @return string
     */
    public function getWebsiteCode()
    {
        return $this->getRequest()->getParam('website', '');
    }

    /**
     * Get the code of the current store from the request parameters.
     *
     * @return string
     */
    public function getStoreCode()
    {
        return $this->getRequest()->getParam('store', '');
    }

    /**
     * Retrieve current scope.
     *
     * @return string
     */
    public function getScope()
    {
        $scope = $this->getData('scope');
        if ($scope === null) {
            if ($this->getStoreCode()) {
                $scope = self::SCOPE_STORES;
            } elseif ($this->getWebsiteCode()) {
                $scope = self::SCOPE_WEBSITES;
            } else {
                $scope = self::SCOPE_DEFAULT;
            }
            $this->setScope($scope);
        }

        return $scope;
    }

    /**
     * Render the custom fieldset with dynamically added fields for each payment method.
     *
     * @param AbstractElement $element The form element to render.
     * @return string
     * @throws LocalizedException
     */
    public function render(AbstractElement $element)
    {
        // Get the JSON-encoded methods from the configuration
        $jsonEncodedMethods = $this->scopeConfig->getValue('payment/pointspay_available_methods_list', ScopeInterface::SCOPE_STORE);

        $paymentMethods = [];
        if ($jsonEncodedMethods) {
            try {
                $paymentMethods = $this->serializer->unserialize($jsonEncodedMethods);
            } catch (\Exception $e) {
                $this->logger->error('Error decoding payment methods JSON: ' . $e->getMessage());
            }
        }

        $this->configData = $this->getConfigData();

        if (!empty($paymentMethods)) {
            foreach ($paymentMethods as $method) {
                // Define the fieldset ID for the method
                $methodFieldsetId = 'fieldset_' . $method['code'];

                // Create a main fieldset for each payment method
                $methodFieldset = $element->addFieldset($methodFieldsetId, [
                    'legend' => __($method['name']),
                    'class' => 'custom-fieldset-method',
                    'collapsable' => true,
                    'expanded' =>   true,
                ])->setRenderer($this->getFieldsetRenderer());

                // Create Basic Settings fieldset for the method
                $this->createBasicSettingsFieldset($methodFieldset, $method);

                // Create Access Settings fieldset for the method
                $this->createAccessSettingsFieldset($methodFieldset, $method);
            }
        }

        return parent::render($element);
    }

    /**
     * Get dummy element.
     *
     * @return DataObject
     */
    protected function _getDummyElement()
    {
        if (empty($this->_dummyElement)) {
            $this->_dummyElement = new DataObject(['showInDefault' => 1, 'showInWebsite' => 1]);
        }
        return $this->_dummyElement;
    }

    /**
     * Get fieldset renderer
     *
     * @throws LocalizedException
     */
    protected function getFieldsetRenderer()
    {
        if (empty($this->_fieldsetRenderer)) {
            $this->_fieldsetRenderer = $this->getLayout()->getBlockSingleton(
                Fieldset::class
            );
        }
        return $this->_fieldsetRenderer;
    }

    /**
     * Get field renderer.
     *
     * @throws LocalizedException
     */
    protected function getFieldRenderer()
    {
        if (empty($this->_fieldRenderer)) {
            $this->_fieldRenderer = $this->getLayout()->getBlockSingleton(
                Field::class
            );
        }
        return $this->_fieldRenderer;
    }

    /**
     * Retrieve the renderer for the merchant certificate field.
     *
     * @return DownloadCertificate
     * @throws LocalizedException
     */
    protected function getMerchantCertificateRenderer()
    {
        if (empty($this->_merchantCertificateRenderer)) {
            $this->_merchantCertificateRenderer = $this->getLayout()->createBlock(
                DownloadCertificate::class,
                'certificate block',
                [
                    'actionPath' => 'pointspay/certificate/download',
                    'returnPath' => 'adminhtml/system_config/edit/section/payment',
                    'buttonName' => __('Download Certificate'),
                    'template' => 'Pointspay_Pointspay::system/config/getCertificateButton.phtml'
                ]
            );
        }
        return $this->_merchantCertificateRenderer;
    }

    /**
     * Adds a field to the specified fieldset with various configurations.
     *
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset Fieldset to which the field will be added.
     * @param string $fieldId Unique identifier for the field.
     * @param string $type Type of the form element (e.g., 'text', 'select', 'button').
     * @param string $name Name attribute for the field.
     * @param string $label Label for the field, displayed in the form.
     * @param string $configPath Configuration path for retrieving saved values.
     * @param string $defaultFieldKey Key for retrieving a default value if none exists in the config.
     * @param array $values Array of options for fields like select, multiselect, etc.
     * @param string $class CSS classes to be applied to the field element.
     * @param string $comment Additional comment or description displayed with the field.
     * @param \Magento\Framework\Data\Form\Element\AbstractElement|null $renderer Custom renderer for the field, if needed.
     * @param bool $canRestore Whether the field can restore default values (default: true).
     *
     * @return \Magento\Framework\Data\Form\Element\AbstractElement The created field element.
     */
    protected function addFieldToFieldset(
        $fieldset,
        $fieldId,
        $type,
        $name,
        $label,
        $configPath,
        $defaultFieldKey = '',
        $values = [],
        $class = '',
        $comment = '',
        $renderer = null,
        $canRestore = true
    )
    {
        $inherit = !array_key_exists($configPath, $this->configData);
        $data = isset($this->configData[$configPath]) ? $this->configData[$configPath] : null;

        if ($inherit) {
            $data = $this->getConfigValueWithFallback($configPath, $defaultFieldKey);
        }

        $labelWithScope = $label . '<br/><span style="color: gray; font-size: 0.9em;">' . '[' . $this->getScope() . ']' . '</span>';

        $fieldOptions = [
            'name' => $name,
            'label' => __($labelWithScope),
            'value' => $data,
            'values' => $values,
            'class' => $class,
            'comment' => __($comment),
            'scope' => $this->getScope(),
            'scope_id' => $this->getScopeCode(),
            'can_restore_to_default' => $canRestore,
            'inherit' => $inherit,
            'can_use_default_value' => $this->getForm()->canUseDefaultValue($this->_getDummyElement()),
            'can_use_website_value' => $this->getForm()->canUseWebsiteValue($this->_getDummyElement()),
        ];

        if (strpos($fieldId, 'service_certificate_uploader_') !== false && $this->getScope() === CustomFieldset::SCOPE_DEFAULT) {
            $fieldOptions['can_restore_to_default'] = false;
            $fieldOptions['inherit'] = false;
            $fieldOptions['can_use_default_value'] = false;
            $fieldOptions['can_use_website_value'] = false;
        }

        $field = $fieldset->addField($fieldId, $type, $fieldOptions);

        if ($renderer) {
            $field->setRenderer($renderer);
        }

        return $field;
    }

    /**
     * Create Basic Settings fieldset for a payment method.
     *
     * @param \Magento\Framework\Data\Form\Element\Fieldset $methodFieldset
     * @param array $method
     * @throws LocalizedException
     */
    protected function createBasicSettingsFieldset($methodFieldset, $method, $configData = [])
    {
        // Create a nested fieldset for Basic Settings
        $requiredFieldsetId = $method['code'] . 'required_settings';
        $basicSettingsFieldset = $methodFieldset->addFieldset($requiredFieldsetId, [
            'legend' => __('Basic Settings'),
            'class' => 'basic-settings-fieldset',
            'collapsable' => true,
        ])->setRenderer($this->getFieldsetRenderer());

        // Add Enabled field in Basic Settings group
        $activeFieldId = 'active_' . $method['code'];
        $this->addFieldToFieldset(
            $basicSettingsFieldset,
            $activeFieldId,
            'select',
            "groups[{$method['code']}_group_all_in_one][groups][pointspay_method_settings][fields][active][value]",
            'Enabled',
            "payment/{$method['code']}_required_settings/active",
            'active',
            [
                ['value' => 1, 'label' => __('Yes')],
                ['value' => 0, 'label' => __('No')],
            ],
            '',
            '',
            $this->getFieldRenderer()
        );

        // Add Environment field
        $demoFieldId = 'demo_mode_' . $method['code'];
        $this->addFieldToFieldset(
            $basicSettingsFieldset,
            $demoFieldId,
            'select',
            "groups[{$method['code']}_group_all_in_one][groups][pointspay_method_settings][fields][demo_mode][value]",
            'Environment',
            "payment/{$method['code']}_required_settings/demo_mode",
            'demo_mode',
            [
                ['value' => 1, 'label' => __('Sandbox')],
                ['value' => 0, 'label' => __('Live')],
            ],
            '',
            '',
            $this->getFieldRenderer()
        );

        $shopIdFieldId = 'shop_code_' . $method['code'];
        $this->addFieldToFieldset(
            $basicSettingsFieldset,
            $shopIdFieldId,
            'text',
            "groups[{$method['code']}_group_all_in_one][groups][pointspay_method_settings][fields][shop_code][value]",
            'Shop ID',
            "payment/{$method['code']}_required_settings/shop_code",
            '',
            [],
            'validate-length minimum-length-12 maximum-length-12 validate-no-empty',
            '',
            $this->getFieldRenderer()
        );

        // Add Debug field
        $debugFieldId = 'debug_' . $method['code'];
        $this->addFieldToFieldset(
            $basicSettingsFieldset,
            $debugFieldId,
            'select',
            "groups[{$method['code']}_group_all_in_one][groups][pointspay_method_settings][fields][debug][value]",
            'Debug',
            "payment/{$method['code']}_required_settings/debug",
            '',
            [
                ['value' => 1, 'label' => __('Enable')],
                ['value' => 0, 'label' => __('Disable')],
            ],
            '',
            '',
            $this->getFieldRenderer()
        );

        // Add Allow Specific field
        $allowSpecificFieldId = $method['code'] . '_allowspecific';
        $this->addFieldToFieldset(
            $basicSettingsFieldset,
            $allowSpecificFieldId,
            'Magento\Config\Block\System\Config\Form\Field\Select\Allowspecific',
            "groups[{$method['code']}_group_all_in_one][groups][pointspay_method_settings][fields][allowspecific][value]",
            'Payment from Applicable Countries',
            "payment/{$method['code']}_required_settings/allowspecific",
            'allowspecific',
            [
                ['value' => 1, 'label' => __('Specific Countries')],
                ['value' => 0, 'label' => __('All Allowed Countries')],
            ],
            '',
            '',
            $this->getFieldRenderer()
        );

        // Add Specific Country field
        $specificFieldId = $method['code'] . '_specificcountry';
        $applicableCountries = $method['applicableCountries'] ?? [];
        $countryOptions = [];
        foreach ($applicableCountries as $country) {
            $countryOptions[] = [
                'value' => $country['code'],
                'label' => __($country['name']),
            ];
        }

        $this->addFieldToFieldset(
            $basicSettingsFieldset,
            $specificFieldId,
            'multiselect',
            "groups[{$method['code']}_group_all_in_one][groups][pointspay_method_settings][fields][specificcountry][value]",
            'Payment from Specific Countries',
            "payment/{$method['code']}_required_settings/specificcountry",
            'allowspecific',
            $countryOptions,
            '',
            '',
            $this->getFieldRenderer()
        );

        // Add Sort Order field
        $sortOrderFieldId = 'sort_order_' . $method['code'];
        $this->addFieldToFieldset(
            $basicSettingsFieldset,
            $sortOrderFieldId,
            'text',
            "groups[{$method['code']}_group_all_in_one][groups][pointspay_method_settings][fields][sort_order][value]",
            'Sort Order', // label
            "payment/{$method['code']}_required_settings/sort_order",
            'sort_order',
            [],
            'validate-number validate-length minimum-length-1 validate-not-negative-number validate-no-empty',
            '',
            $this->getFieldRenderer()
        );

        // Add Cancel URL field
        $cancelUrlFieldId = 'cancel_url_' . $method['code'];
        $this->addFieldToFieldset(
            $basicSettingsFieldset,
            $cancelUrlFieldId,
            'text',
            "groups[{$method['code']}_group_all_in_one][groups][pointspay_method_settings][fields][cancel_url][value]",
            'Cancel URL',
            "payment/{$method['code']}_required_settings/cancel_url",
            '',
            [],
            'validate-url',
            '',
            $this->getFieldRenderer()
        );
    }

    /**
     * Create Access Settings fieldset for a payment method.
     *
     * @param \Magento\Framework\Data\Form\Element\Fieldset $methodFieldset
     * @param array $method
     * @throws LocalizedException
     */
    protected function createAccessSettingsFieldset($methodFieldset, $method, $configData = [])
    {
        $accessSettingsFieldsetId = $method['code'] . 'access_settings';

        // Create a nested fieldset for Access Settings
        $accessSettingsFieldset = $methodFieldset->addFieldset($accessSettingsFieldsetId, [
            'legend' => __('Access Settings'),
            'class' => 'access-settings-fieldset',
            'collapsable' => true,
        ])->setRenderer($this->getFieldsetRenderer());

        // Add Consumer Key field
        $consumerKeyFieldId = 'consumer_key_' . $method['code'];

        $this->addFieldToFieldset(
            $accessSettingsFieldset,
            $consumerKeyFieldId,
            'text',
            "groups[{$method['code']}_group_all_in_one][groups][pointspay_method_settings][fields][consumer_key][value]",
            'Consumer Key',
            "payment/{$method['code']}_access_settings/consumer_key",
            '',
            [],
            'validate-length minimum-length-1 validate-no-empty',
            '',
            $this->getFieldRenderer()
        );

        // Add Pointspay Certificate Uploader field
        $certificateFieldId = 'service_certificate_uploader_' . $method['code'];

        $this->addFieldToFieldset(
            $accessSettingsFieldset,
            $certificateFieldId,
            'Pointspay\Pointspay\Block\Adminhtml\System\Config\Form\Field\File',
            "groups[{$method['code']}_group_all_in_one][groups][pointspay_method_settings][fields][certificate][value]",
            'Pointspay Certificate',
            "payment/{$method['code']}_access_settings/certificate",
            '',
            [],
            '',
            'Upload Pointspay Certificate',
            $this->getFieldRenderer(),
            false
        );

        // Add Merchant Certificate Button
        $merchantCertificateFieldId = 'merchant_certificate_' . $method['code'];
        $this->addFieldToFieldset(
            $accessSettingsFieldset,
            $merchantCertificateFieldId,
            'button',
            "groups[{$method['code']}_group_all_in_one][groups][pointspay_method_settings][fields][merchant_certificate][value]",
            'Merchant Certificate',
            '',
            '',
            [],
            '',
            'Please note: you have to push this button <strong>only</strong> on the website scope',
            $this->getMerchantCertificateRenderer(),
            false
        );
    }
}
