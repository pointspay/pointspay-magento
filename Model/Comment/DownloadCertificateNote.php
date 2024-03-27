<?php

namespace Pointspay\Pointspay\Model\Comment;

use Magento\Config\Model\Config\CommentInterface;
use Magento\Framework\App\RequestInterface;

class DownloadCertificateNote implements CommentInterface
{
    /**
     * Method magically called by Magento.
     *
     * @param string $elementValue The value of the field with this commented.
     * @return string Some HTML markup to be displayed in the admin panel.
     */
    public function getCommentText($elementValue)
    {
        return "Please note: you have to push this button <strong>only</strong> on the website scope<br/>";
    }
}
