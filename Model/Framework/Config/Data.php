<?php

namespace Pointspay\Pointspay\Model\Framework\Config;

use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Zend_Cache;

class Data extends \Magento\Framework\Config\Data
{
    /**
     * @var \Magento\Framework\Config\CacheInterface
     */
    private $cache;

    private $cacheId;

    public function __construct(
        ReaderInterface $reader,
        CacheInterface $cache,
        $cacheId,
        SerializerInterface $serializer = null,
        $cacheTags = []
    ) {
        parent::__construct($reader, $cache, $cacheId, $serializer);
        $this->cacheTags = $cacheTags;
        $this->cache = $cache;
        $this->cacheId = $cacheId;
    }

    /**
     * Clear cache data
     *
     * @return void
     */
    public function reset()
    {
        $this->cache->remove($this->cacheId);
        if (empty($this->cacheTags)) {
            return;
        }
        $this->cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $this->cacheTags);
        $this->initData();
    }

}
