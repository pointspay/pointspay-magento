<?php

namespace Pointspay\Pointspay\Test\Service;

use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Service\Uuid;

class UuidTest extends TestCase
{
    public function testUuidGenerationWithValidV4(): void
    {
        $uuid = Uuid::generateV4();

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $uuid
        );
    }
}
