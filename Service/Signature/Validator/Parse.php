<?php

namespace Pointspay\Pointspay\Service\Signature\Validator;

use Magento\Framework\DataObject;

class Parse
{
    /**
     * @param $string
     * @return \Magento\Framework\DataObject
     */
    public function parse($string)
    {
        if(empty($string)) {
            return new DataObject();
        }
        if (strpos(strtolower($string), 'oauth') !== 0) {
            throw new \InvalidArgumentException('Invalid string');
        }
        $string = str_replace('OAuth', '', $string);
        $string = str_replace('Oauth', '', $string);
        $params = explode(', ', $string);
        $oauthParams = [];
        foreach ($params as $param) {
            list($key, $value) = explode('=', $param, 2);
            $oauthParams[trim($key)] = trim($value, '"');
        }
        return new DataObject($oauthParams);
    }

}
