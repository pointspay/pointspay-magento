<?php

namespace Pointspay\Pointspay\Test\Service\PaymentMethodsUpdater;

use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Api\Data\PaymentMethodsUpdaterInterface;
use Pointspay\Pointspay\Service\PaymentMethodsUpdater\ExecutionChain;

class ExecutionChainTest extends TestCase
{
    private $executionChain;

    protected function setUp(): void
    {
        $this->executionChain = new ExecutionChain();
    }

    public function testExecutionChainExecutesAllLinksInChain()
    {
        $link1 = $this->createMock(PaymentMethodsUpdaterInterface::class);
        $link1->expects($this->once())->method('execute');

        $link2 = $this->createMock(PaymentMethodsUpdaterInterface::class);
        $link2->expects($this->once())->method('execute');
        $this->executionChain = new ExecutionChain(
            [$link1, $link2]
        );
        $this->executionChain->execute();
    }

    public function testExecutionChainThrowsExceptionForInvalidLink()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid link in the chain');
        $invalidLink = new \stdClass();
        $this->executionChain = new ExecutionChain(
            [$invalidLink]
        );

        $this->executionChain->execute();
    }
}
