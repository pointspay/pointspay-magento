<?php

namespace Pointspay\Pointspay\Test\Model\Method;

use Pointspay\Pointspay\Helper\Config;

class FakeConfig extends Config
{
    /**
     * @return mixed
     */
    public function getRequestTimeout()
    {
        return 30;
    }
}
