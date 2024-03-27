<?php

namespace Pointspay\Pointspay\Test\Model\Method\FakeBuilder;

use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Pointspay\Pointspay\Service\Uuid;
use Pointspay\Pointspay\Test\MageObjectManager;

class NonceDataBuilder extends \Pointspay\Pointspay\Gateway\Request\NonceDataBuilder
{

    public function build(array $buildSubject): array
    {
        $OM = new MageObjectManager();
        $uuid = $OM->objectManager->create(Uuid::class);
        $request['clientConfig']['oauth']['nonce'] = $uuid->generateV4();
        return $request;
    }
}
