<?php


namespace Pointspay\Pointspay\Model\Comment;

use Magento\Config\Model\Config\CommentInterface;

class AccessFieldsEnding implements CommentInterface
{
    /**
     * Method magically called by Magento. This returns the last 10 digits in the consumer key.
     *
     * @param string $elementValue The value of the field with this commented. In this case, an encrypted API key.
     * @return string Some HTML markup to be displayed in the admin panel.
     */
    public function getCommentText($elementValue)
    {
        if (is_null($elementValue)) {
            return '';
        }

        $elementValue = str_replace('-----END CERTIFICATE-----', '', $elementValue);
        $apiKeyEnding = substr(trim($elementValue), -10);
        return "Your key ends with <strong>$apiKeyEnding</strong><br/>";
    }
}
