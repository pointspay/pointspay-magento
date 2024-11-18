<?php

namespace Pointspay\Pointspay\Model\Method;

use DomainException;
use Exception;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Payment\Gateway\Command\CommandManagerInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Validator\ValidatorPoolInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Quote\Api\Data\CartInterface;
use Pointspay\Pointspay\Gateway\Config\ConfigFactory;
use Pointspay\Pointspay\Helper\Config;
use Psr\Log\LoggerInterface;

class Adapter extends \Magento\Payment\Model\Method\Adapter
{
    static $paymentCache = [];

    /**
     * @var \Magento\Payment\Gateway\Config\ValueHandlerPoolInterface
     */
    private $valueHandlerPool;

    /**
     * @var \Magento\Payment\Gateway\Data\PaymentDataObjectFactory
     */
    private $paymentDataObjectFactory;

    /**
     * @var \Pointspay\Pointspay\Helper\Config|null
     */
    private $configHelper;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Pointspay\Pointspay\Gateway\Config\ConfigFactory|mixed|null
     */
    private $configFactory;

    /**
     * @var \Magento\Payment\Gateway\Command\CommandManagerInterface|null
     */
    private $commandExecutor;

    /**
     * @var \Magento\Payment\Gateway\Command\CommandPoolInterface|null
     */
    private $commandPool;

    /**
     * @var false
     */
    private $isNeedToSendEmail = false;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Payment\Gateway\Config\ValueHandlerPoolInterface $valueHandlerPool
     * @param \Magento\Payment\Gateway\Data\PaymentDataObjectFactory $paymentDataObjectFactory
     * @param string $code
     * @param string $formBlockType
     * @param string $infoBlockType
     * @param \Magento\Payment\Gateway\Command\CommandPoolInterface|null $commandPool
     * @param \Magento\Payment\Gateway\Validator\ValidatorPoolInterface|null $validatorPool
     * @param \Magento\Payment\Gateway\Command\CommandManagerInterface|null $commandExecutor
     * @param \Psr\Log\LoggerInterface|null $logger
     * @param \Pointspay\Pointspay\Helper\Config|null $configHelper
     * @param \Pointspay\Pointspay\Gateway\Config\ConfigFactory|null $configFactory
     */
    public function __construct(
        ManagerInterface $eventManager,
        ValueHandlerPoolInterface $valueHandlerPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        $code,
        $formBlockType,
        $infoBlockType,
        CommandPoolInterface $commandPool = null,
        ValidatorPoolInterface $validatorPool = null,
        CommandManagerInterface $commandExecutor = null,
        LoggerInterface $logger = null,
        Config $configHelper = null,
        ConfigFactory $configFactory = null
    ) {
        parent::__construct($eventManager, $valueHandlerPool, $paymentDataObjectFactory, $code, $formBlockType, $infoBlockType, $commandPool, $validatorPool, $commandExecutor, $logger);
        $this->valueHandlerPool = $valueHandlerPool;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->configHelper = $configHelper ?: ObjectManager::getInstance()->get(Config::class);
        $this->eventManager = $eventManager;
        //as string due to not being able to process in some step at DI compilation if it is as ::class
        $this->configFactory = $configFactory ?: ObjectManager::getInstance()->get('Pointspay\Pointspay\Gateway\Config\ConfigFactory');
        $this->commandExecutor = $commandExecutor;
        $this->commandPool = $commandPool;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->getConfiguredValue('title');
    }

    /**
     * Unifies configured value handling logic
     *
     * @param string $field
     * @param int|null $storeId
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    private function getConfiguredValue($field, $storeId = null)
    {
        $handler = $this->valueHandlerPool->get($field);
        $subject = [
            'field' => $field,
            'pp_code' => $this->getCode()
        ];

        if ($this->getInfoInstance()) {
            $subject['payment'] = $this->paymentDataObjectFactory->create($this->getInfoInstance());
        }
        $config = $this->configFactory->create(['methodCode' => $this->getCode()]);
        $handler->setConfig($config);

        return $handler->handle($subject, $storeId ?: $this->getStore());
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(CartInterface $quote = null)
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isActive($storeId = null)
    {
        return true;
    }

    public function canUseForCountry($country)
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function canUseCheckout()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function canUseInternal()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function refund(InfoInterface $payment, $amount)
    {
        $this->executeCommand(
            'refund',
            ['payment' => $payment, 'amount' => $amount]
        );

        return $this;
    }

    private function executeCommand($commandCode, array $arguments = [])
    {
        if (!$this->canPerformCommand($commandCode)) {
            return null;
        }

        /** @var InfoInterface|null $payment */
        $payment = null;
        if (isset($arguments['payment']) && $arguments['payment'] instanceof InfoInterface) {
            $payment = $arguments['payment'];
            $arguments['payment'] = $this->paymentDataObjectFactory->create($arguments['payment']);
        }

        if ($this->commandExecutor !== null) {
            return $this->commandExecutor->executeByCode($commandCode, $payment, $arguments);
        }

        if ($this->commandPool === null) {
            throw new DomainException("The command pool isn't configured for use.");
        }

        $command = $this->commandPool->get($commandCode);

        return $command->execute($arguments);
    }

    /**
     * Whether payment command is supported and can be executed
     *
     * @param string $commandCode
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    private function canPerformCommand($commandCode)
    {
        return (bool)$this->getConfiguredValue('can_' . $commandCode);
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function initialize($paymentAction, $stateObject)
    {
        $paymentInstance = $this->getInfoInstance();
        if (isset($paymentInstance) && $paymentInstance instanceof InfoInterface) {
            $order = $paymentInstance->getOrder();
            if ($order) {
                $paymentInstance->getOrder()->setCanSendNewEmailFlag($this->getIsNeedToSendEmail());
            }
        }
        $this->executeCommand(
            'initialize',
            [
                'payment' => $paymentInstance,
                'paymentAction' => $paymentAction,
                'stateObject' => $stateObject
            ]
        );
        return $this;
    }

    /**
     * @return false
     */
    public function getIsNeedToSendEmail(): bool
    {
        return $this->isNeedToSendEmail;
    }

    /**
     * @param false $isNeedToSendEmail
     */
    public function setIsNeedToSendEmail(bool $isNeedToSendEmail): void
    {
        $this->isNeedToSendEmail = $isNeedToSendEmail;
    }

    /**
     * @inheritdoc
     */
    public function getConfigData($field, $storeId = null)
    {
        return $this->getConfiguredValue($field, $storeId);
    }
}
