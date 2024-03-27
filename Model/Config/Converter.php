<?php


namespace Pointspay\Pointspay\Model\Config;

use Pointspay\Pointspay\Api\Data\PointspayGeneralPaymentInterface;

class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        $xpath = new \DOMXPath($source);
        return [
            'pointspay_methods' => $this->convertPaymentMethods($xpath),
        ];
    }

    /**
     * Convert credit cards xml tree to array
     *
     * @param \DOMXPath $xpath
     * @return array
     */
    protected function convertPaymentMethods(\DOMXPath $xpath)
    {
        $pointspayMethods = [];
        /** @var \DOMNode $type */
        foreach ($xpath->query('/payment/pointspay_methods/type') as $type) {
            $typeArray = [];

            /** @var $typeSubNode \DOMNode */
            foreach ($type->childNodes as $typeSubNode) {
                switch ($typeSubNode->nodeName) {
                    case 'label':
                        $typeArray['name'] = $typeSubNode->nodeValue;
                        break;
                    case 'pointspay_code':
                        $typeArray['pointspay_code'] = strtoupper($typeSubNode->nodeValue) == 'PP' ? PointspayGeneralPaymentInterface::POINTSPAY_REQUIRED_SETTINGS : $typeSubNode->nodeValue;
                        break;
                    case 'sandbox':
                    case 'live':
                    case 'applicableCountries':
                        $typeArray[$typeSubNode->nodeName] = $this->processRegularNode($typeSubNode);
                        break;
                    default:
                        break;
                }
            }

            $typeAttributes = $type->attributes;
            $typeArray['order'] = $typeAttributes->getNamedItem('order')->nodeValue;
            $ccId = $typeAttributes->getNamedItem('id')->nodeValue;
            $pointspayMethods[$ccId] = $typeArray;
        }
        uasort($pointspayMethods, [$this, 'sortPointspayMethods']);
        $config = [];
        foreach ($pointspayMethods as $code => $data) {
            $config[$code] = $data;
        }
        return $config;
    }

    private function processRegularNode($SubNode)
    {
        $result = [];
        if (empty($SubNode->childNodes)) {
            return $SubNode->nodeValue;
        }
        foreach ($SubNode->childNodes as $typeSubNode) {
            if (empty(trim($typeSubNode->nodeValue)) && !is_numeric(trim($typeSubNode->nodeValue))) {
                continue;
            }
            if (!empty($typeSubNode) && !empty($typeSubNode->childNodes) && $typeSubNode->childNodes->length == 0) {
                if ($typeSubNode->parentNode->parentNode->nodeName != 'country') {
                    $result = $typeSubNode->nodeValue;
                } elseif ( $typeSubNode->parentNode->parentNode->nodeName == 'country') {
                    $result = $typeSubNode->nodeValue;
                }
            } else {
                if ($typeSubNode->nodeName == 'country') {
                    $result[] = $this->processRegularNode($typeSubNode);
                } elseif (!empty($typeSubNode->parentNode->nodeName) && empty($typeSubNode->childNodes)) {
                    $result = $typeSubNode->nodeValue;
                } else {
                    $result[$typeSubNode->nodeName] = $this->processRegularNode($typeSubNode);
                }
            }
        }
        return $result;
    }
    /**
     * @param $left
     * @param $right
     * @return mixed
     */
    private function sortPointspayMethods($left, $right)
    {
        return $left['order'] - $right['order'];
    }

}
