<?php

namespace Pointspay\Pointspay\Controller\Api;

use Magento\Framework\Controller\ResultFactory;

class Cancel extends AbstractApi
{

    /**
     * @return void
     */
    public function execute()
    {
        $content = $this->getRequest()->getContent();
        $this->service->logPostData($content);
        if($postData = $this->service->restorePostData($content))
        {
            $this->_restoreData->cancelOrder($postData,'cancel');
            $message = __('Canceling a payment.');
        }else{
            $message = __('Payment cannot be canceled. Incorrect parameters.');
        }
        return $this->_redirectToCartPageWithError($message, $postData);
    }
}
