<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Cache;

use Becklyn\Menu\Item\MenuItem;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * This is a super fast top-level cache with imprecise cache invalidation.
 *
 * Fun fact: using the pool + item directly are around 10x faster than using the CacheInterface from
 * symfony/contracts.
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
     * @var MenuItem[]
     */
    private $items;


    /**
     */
    public function __construct (CacheItemPoolInterface $cachePool, bool $isDebug)
    {
        $this->isDebug = $isDebug;
        $this->cachePool = $cachePool;
        $this->cacheItem = $this->cachePool->getItem(self::CACHE_ITEM_KEY);
        $this->items = $this->cacheItem->isHit()
            ? $this->cacheItem->get()
            : [];
    }



    /**
     * Returns the cached tree.
     *
     * @return MenuItem[]|null
     */
    public function get () : ?array
    {
        return !$this->isDebug && !empty($this->items)
            ? $this->items
            : null;
    }



    /**
     * Updates the cached tree.
     *
     * @param MenuItem[] $nodes
     */
    public function set (array $nodes) : void
    {
        $this->items = $nodes;
        $this->cacheItem->set($this->items);
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
