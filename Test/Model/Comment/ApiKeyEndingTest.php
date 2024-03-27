<?php
namespace Pointspay\Pointspay\Test\Model\Comment;

use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Model\Comment\ApiKeyEnding;

class ApiKeyEndingTest extends TestCase {

    private $apikeyFieldsEnding;

    protected function setUp(): void
    {
        $this->apikeyFieldsEnding = new ApiKeyEnding();
    }
    public function testCommentTextReturnsEmptyStringWhenElementValueIsNull()
    {
        $result = $this->apikeyFieldsEnding->getCommentText(null);
        $this->assertEquals('', $result);
    }

    public function testCommentTextReturnsCorrectEndingWhenElementValueIsProvided()
    {
        $elementValue = '12345678901234567890';
        $result = $this->apikeyFieldsEnding->getCommentText($elementValue);
        $this->assertEquals('Your stored key ends with <strong>678901234567890</strong>', $result);
    }

    public function testCommentTextReturnsCorrectEndingWhenElementValueHasExtraSpaces()
    {
        $elementValue = '12345678901234567890';
        $result = $this->apikeyFieldsEnding->getCommentText($elementValue);
        $this->assertEquals('Your stored key ends with <strong>678901234567890</strong>', $result);
    }
}
