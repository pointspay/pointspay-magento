<?php

namespace Pointspay\Pointspay\Test\Model\Method\FakeBuilder;

use Magento\Framework\Exception\LocalizedException;

class ConsumerKeyDataBuilder extends \Pointspay\Pointspay\Gateway\Request\ConsumerKeyDataBuilder
{

    /**
     * @param array $buildSubject
     * @return array
     * @throws LocalizedException
     */
    public function build(array $buildSubject): array
    {
        $request['clientConfig']['oauth']['consumer_key'] = 'vzzr7zS1xqj9D8DUNxGKMJtByIEn4iXQ';
        return $request;
    }
}
