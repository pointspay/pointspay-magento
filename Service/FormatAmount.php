<?php

namespace Pointspay\Pointspay\Service;

class FormatAmount
{
    /**
     * @param int|float $amount
     * @param string $currency
     * @return int
     */
    public function formatAmount($amount, $currency)
    {
        return (int)number_format($amount, $this->decimalNumbers($currency), '', '');
    }

    /**
     * @param string $currency
     * @return int
     */
    public function decimalNumbers($currency)
    {
        switch ($currency) {
            case "CVE":
            case "DJF":
            case "GNF":
            case "IDR":
            case "JPY":
            case "KMF":
            case "KRW":
            case "PYG":
            case "RWF":
            case "UGX":
            case "VND":
            case "VUV":
            case "XAF":
            case "XOF":
            case "XPF":
                $format = 0;
                break;
            case "BHD":
            case "IQD":
            case "JOD":
            case "KWD":
            case "LYD":
            case "OMR":
            case "TND":
                $format = 3;
                break;
            case "MRO":
                $format = 2;
                break;
            default:
                $format = 2;
        }
        return $format;
    }

    /**
     * @param int|float $amount
     * @param string $currency
     * @return float
     */
    public function originalAmount($amount, $currency)
    {
        // check the format
        switch ($currency) {
            case "JPY":
            case "IDR":
            case "KRW":
            case "BYR":
            case "VND":
            case "CVE":
            case "DJF":
            case "GNF":
            case "PYG":
            case "RWF":
            case "UGX":
            case "VUV":
            case "XAF":
            case "XOF":
            case "XPF":
            case "GHC":
            case "KMF":
                $format = 1;
                break;
            case "MRO":
                $format = 10;
                break;
            case "BHD":
            case "JOD":
            case "KWD":
            case "OMR":
            case "LYD":
            case "TND":
                $format = 1000;
                break;
            default:
                $format = 100;
                break;
        }

        return ($amount / $format);
    }
}
