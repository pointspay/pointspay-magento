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
        $this->service->logResponse(
            'Redirect header ',
            ['authorization'=>$this->getRequest()->getHeader('authorization')]
        );
        $postData = $this->service->restorePostData($content);
        if($this->redirectValidator->validate($this->getRequest()))
        {
            $this->_restoreData->cancelOrder($postData,'cancel');
            $message = __('Canceling a payment.');
        }else{
            $message = __('Payment cannot be canceled. Incorrect parameters.');
        }
        return $this->_redirectToCartPageWithError($message, $postData);
    }
}
