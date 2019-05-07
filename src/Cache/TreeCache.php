<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Cache;

use Becklyn\RouteTreeBundle\Node\Node;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 *
 */
class TreeCache
{
    const CACHE_ITEM_KEY = "becklyn.route-tree.cache";

    /**
     * @var bool
     */
    private $isDebug;


    /**
     * @var CacheItemPoolInterface
     */
    private $cachePool;


    /**
     * @var CacheItemInterface
     */
    private $cacheItem;


    /**
     * @var Node[]
     */
    private $nodes;


    /**
     * @param CacheItemPoolInterface $cachePool
     * @param bool                   $debug
     */
    public function __construct (CacheItemPoolInterface $cachePool, bool $debug)
    {
        $this->isDebug = $debug;
        $this->cachePool = $cachePool;
        $this->cacheItem = $this->cachePool->getItem(self::CACHE_ITEM_KEY);
        $this->nodes = $this->cacheItem->isHit()
            ? $this->cacheItem->get()
            : [];
    }



    /**
     * Returns the cached tree.
     *
     * @return Node[]|null
     */
    public function get () : ?array
    {
        return !$this->isDebug && !empty($this->nodes)
            ? $this->nodes
            : null;
    }



    /**
     * Updates the cached tree.
     *
     * @param Node[] $nodes
     */
    public function set (array $nodes) : void
    {
        $this->nodes = $nodes;
        $this->cacheItem->set($this->nodes);
        $this->cachePool->save($this->cacheItem);
    }



    /**
     * Clears the cache.
     */
    public function clear () : void
    {
        $this->set([]);
    }
}
