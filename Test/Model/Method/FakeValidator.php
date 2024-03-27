<?php

namespace Pointspay\Pointspay\Test\Model\Method;

class FakeValidator extends \Magento\Payment\Gateway\Validator\AbstractValidator
{

    public function validate(array $validationSubject)
    {
        return $this->createResult(true);
    }
}
