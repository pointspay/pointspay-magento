<?php


namespace Pointspay\Pointspay\Model\Comment;

use Magento\Config\Model\Config\CommentInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class ApiKeyEnding implements CommentInterface
{

    /**
     * Method magically called by Magento. This returns the last 4 digits in the merchant's API key.
     *
     * @param string $elementValue The value of the field with this commented. In this case, an encrypted API key.
     * @return string Some HTML markup to be displayed in the admin panel.
     */
    public function getCommentText($elementValue)
    {
        if (is_null($elementValue)) {
            return '';
        }

        $apiKeyEnding = substr(trim($elementValue), -15);
        return "Your stored key ends with <strong>$apiKeyEnding</strong>";
    }
}
