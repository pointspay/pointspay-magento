<?php

namespace Pointspay\Pointspay\Block\Adminhtml\System\Config\Form\Field;

class File extends \Magento\Config\Block\System\Config\Form\Field\File
{
    /**
     * Get element html
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = '';
        $htmlId = $this->getHtmlId();

        $beforeElementHtml = $this->getBeforeElementHtml();
        if ($beforeElementHtml) {
            $html .= '<label class="addbefore" for="' . $htmlId . '">' . $beforeElementHtml . '</label>';
        }

        if (is_array($this->getValue())) {
            foreach ($this->getValue() as $value) {
                $html .= $this->getHtmlForInputByValue($this->_escape($value));
            }
        } else {
            $html .= $this->getHtmlForInputByValue($this->getEscapedValue());
        }

        $afterElementJs = $this->getAfterElementJs();
        if ($afterElementJs) {
            $html .= $afterElementJs;
        }

        $afterElementHtml = $this->getAfterElementHtml();
        if ($afterElementHtml) {
            $html .= '<label class="addafter" for="' . $htmlId . '">' . $afterElementHtml . '</label>';
        }

        return $html;
    }

    private function getHtmlForInputByValue($value)
    {
        return '<input id="' . $this->getHtmlId() . '" name="' . $this->getName() . '" ' . $this->_getUiId()
            . ' value="' . $value . '" ' . $this->serialize($this->getHtmlAttributes()) . '/>';
    }
    /**
     * Get the name.
     *
     * @return mixed
     */
    public function getName()
    {
        $name = $this->_escaper->escapeHtml($this->getData('name'));

        if ($suffix = $this->getForm()->getFieldNameSuffix()) {
            $name = $this->getForm()->addSuffixToName($name, $suffix);
        }
        return $name;
    }
}
