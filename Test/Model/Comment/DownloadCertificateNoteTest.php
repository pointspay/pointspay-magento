<?php
namespace Pointspay\Pointspay\Test\Model\Comment;

use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Model\Comment\DownloadCertificateNote;

class DownloadCertificateNoteTest extends TestCase
{
    private $downloadCertificateNote;

    protected function setUp(): void
    {
        $this->downloadCertificateNote = new DownloadCertificateNote();
    }

    public function testCommentTextReturnsCorrectNote()
    {
        $result = $this->downloadCertificateNote->getCommentText(null);
        $expected = "Please note: you have to push this button <strong>only</strong> on the website scope<br/>";
        $this->assertEquals($expected, $result);
    }
}
