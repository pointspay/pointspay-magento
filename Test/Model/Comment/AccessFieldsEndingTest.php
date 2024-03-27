<?php
namespace Pointspay\Pointspay\Test\Model\Comment;

use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Model\Comment\AccessFieldsEnding;

class AccessFieldsEndingTest extends TestCase
{
    private $accessFieldsEnding;

    protected function setUp(): void
    {
        $this->accessFieldsEnding = new AccessFieldsEnding();
    }

    public function testCommentTextReturnsEmptyStringWhenElementValueIsNull()
    {
        $result = $this->accessFieldsEnding->getCommentText(null);
        $this->assertEquals('', $result);
    }

    public function testCommentTextReturnsCorrectEndingWhenElementValueIsProvided()
    {
        $elementValue = '-----BEGIN CERTIFICATE-----1234567890-----END CERTIFICATE-----';
        $result = $this->accessFieldsEnding->getCommentText($elementValue);
        $this->assertEquals('Your key ends with <strong>1234567890</strong><br/>', $result);
    }

    public function testCommentTextReturnsCorrectEndingWhenElementValueHasExtraSpaces()
    {
        $elementValue = '-----BEGIN CERTIFICATE-----    1234567890    -----END CERTIFICATE-----';
        $result = $this->accessFieldsEnding->getCommentText($elementValue);
        $this->assertEquals('Your key ends with <strong>1234567890</strong><br/>', $result);
    }
}
