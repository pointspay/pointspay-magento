<?php

namespace Pointspay\Pointspay\Controller\Api;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Pointspay\Pointspay\Service\Checkout\Service;
use Pointspay\Pointspay\Service\Signature\RedirectValidator;
use Psr\Log\LoggerInterface;
use Pointspay\Pointspay\Model\Quote\RestoreData;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;


/**
 * Abstract  Checkout Controller
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractApi extends Action  implements CsrfAwareActionInterface, HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * @var RedirectInterface
     */
    protected $_redirect;

    /**
     * @var Service
     */
    protected $service;

    /**
     * @var RedirectValidator
     */
    protected $redirectValidator;

    /**
     * @var RestoreData
     */
    protected $_restoreData;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;


    /**
     * @param Context $context
     * @param RestoreData $restoreData
     * @param LoggerInterface $logger
     * @param Service $service
     * @param Redirect $resultRedirectFactory
     */
    public function __construct(
        Context                 $context,
        RestoreData             $restoreData,
        LoggerInterface         $logger,
        Service                 $service,
        RedirectValidator       $redirectValidator,
        Redirect                $resultRedirectFactory
    ){
        $this->resultFactory        = $context->getResultFactory();
        $this->messageManager       = $context->getMessageManager();
        $this->_restoreData         = $restoreData;
        $this->service              = $service;
        $this->redirectValidator    = $redirectValidator;
        $this->logger               = $logger;
        $this->resultRedirectFactory = $resultRedirectFactory;
        parent::__construct($context);
    }

    /**
     * Redirects to the checkout or cart page with error
     *
     * @param string $error
     * @param array $postData
     * @param int $redirectFlag
     */
    public function _redirectToCartPageWithError(string $error, array $postData = [], $redirectFlag = 0)
    {
        $this->logger->addInfo(__METHOD__ . " error:{$error}");

        $this->messageManager->addErrorMessage($error);

        if($customUrl = $this->service->getCustomCancelUrl($postData)) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setUrl($customUrl);
            return $resultRedirect;
        }
        if ($redirectFlag == 1) {
            $this->_redirect("checkout", [ '_fragment' => 'payment']);
        } else {
            $this->_redirect("checkout/cart");
        }
    }


    /**
     * @param RequestInterface $request
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ? InvalidRequestException
    {
        return null;
    }

    /**
     * @param RequestInterface $request
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    abstract public function execute();

}
