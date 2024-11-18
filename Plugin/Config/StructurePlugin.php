<?php

namespace Pointspay\Pointspay\Plugin\Config;

use Magento\Config\Model\Config\ScopeDefiner;
use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\Structure\Element\FlyweightFactory;

/**
 * Class StructurePlugin
 *
 * This plugin customizes the behavior of the configuration structure for the
 * Pointspay module in the Magento admin panel. It modifies elements within the
 * configuration structure based on specific criteria, allowing for dynamic settings
 * customization.
 */
class StructurePlugin
{
    /**
     * @var \Magento\Config\Model\Config\Structure\Element\FlyweightFactory
     */
    private FlyweightFactory $_flyweightFactory;

    /**
     * @var \Magento\Config\Model\Config\ScopeDefiner
     */
    private ScopeDefiner $_scopeDefiner;

    /**
     * StructurePlugin constructor.
     *
     * @param \Magento\Config\Model\Config\Structure\Element\FlyweightFactory $flyweightFactory
     * @param \Magento\Config\Model\Config\ScopeDefiner $scopeDefiner
     */
    public function __construct(
        FlyweightFactory $flyweightFactory,
        ScopeDefiner     $scopeDefiner
    )
    {
        $this->_flyweightFactory = $flyweightFactory;
        $this->_scopeDefiner = $scopeDefiner;
    }

    /**
     * After-plugin for the getElement method.
     *
     * This method customizes the configuration element structure by modifying
     * specific elements based on the provided path. It creates customized group and
     * field elements with specified settings such as `clone_fields` or `backend_model`
     * when the configuration path matches Pointspay criteria.
     *
     * @param \Magento\Config\Model\Config\Structure $subject
     * @param \Magento\Config\Model\Config\Structure\ElementInterface|null $result
     * @param string $path The path to the configuration element.
     * @return \Magento\Config\Model\Config\Structure\ElementInterface|null
     */
    public function afterGetElement(Structure $subject, $result, string $path)
    {
        if (false === strpos($path, '_group_all_in_one')) {
            return $result;
        }

        $pathParts = explode('/', $path);

        if (false !== strpos($path, 'pointspay_method_settings') && count($pathParts) === 3) {
            $newResult = $this->_flyweightFactory->create('group');
            $newResult->setData(
                array_merge(
                    $result->getData(),
                    [
                        'clone_fields' => '1',
                        'clone_model' => 'Pointspay\Pointspay\Block\Adminhtml\System\Config\CustomCloneModel',
                    ]
                ),
                $this->_scopeDefiner->getScope()
            );

            return $newResult;
        }

        if (false === strpos($path, 'pointspay_method_settings')) {
            return $result;
        }

        $paymentMethodCode = str_replace('_group_all_in_one', '', $pathParts[1]);
        $fieldId = $pathParts[3];

        $accessFields = ['consumer_key', 'certificate', 'merchant_certificate'];
        $settingsType = in_array($fieldId, $accessFields) ? 'access_settings' : 'required_settings';

        $backendModel = '';
        if ($fieldId === 'certificate') {
            $backendModel = \Pointspay\Pointspay\Model\Config\Backend\File::class;
        }

        if ($fieldId === 'allowspecific') {
            $backendModel = \Pointspay\Pointspay\Model\Config\Backend\AllowedCountries::class;
        }

        $result = $this->_flyweightFactory->create('field');
        $result->setData([
            'id' => $fieldId,
            'path' => "payment/{$paymentMethodCode}_{$settingsType}",
            '_elementType' => 'field',
            'backend_model' => $backendModel,
        ], $this->_scopeDefiner->getScope());

        return $result;
    }
}
