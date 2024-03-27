<?php

namespace Pointspay\Pointspay\Service\PaymentMethodsUpdater;

class ExecutionChain
{
    /**
     * @var array
     */
    private $chain;

    public function __construct(
        array $chain = []
    ) {
        $this->chain = $chain;
    }

    public function execute()
    {
        /** @var \Pointspay\Pointspay\Api\Data\PaymentMethodsUpdaterInterface $link */
        foreach ($this->chain as $link) {
            if($link instanceof \Pointspay\Pointspay\Api\Data\PaymentMethodsUpdaterInterface){
                $link->execute();
            }else{
                throw new \Exception('Invalid link in the chain');
            }
        }
    }

}
