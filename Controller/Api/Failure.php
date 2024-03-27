<?php

namespace Pointspay\Pointspay\Controller\Api;

use Magento\Framework\Controller\ResultFactory;

class Failure extends AbstractApi
{

    /**
     * @return void
     */
    public function execute()
    {
        $content = $this->getRequest()->getContent();
        $this->service->logPostData($content);
        if($postData = $this->service->restorePostData($content)) {
            $this->_restoreData->cancelOrder($postData,'fails');
            $message = __('The payment was not processed.');
        }else{
            $message = __('The payment was not processed.  The payment was not processed. Invalid ID order.');
        }
        return $this->_redirectToCartPageWithError($message, $postData, 1);
    }
}
