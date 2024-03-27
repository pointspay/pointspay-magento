<?php

namespace Pointspay\Pointspay\Api\Data;

interface StructureDataUpdaterInterface
{
    /**
     * @return array
     */
    public function execute($config);
}
