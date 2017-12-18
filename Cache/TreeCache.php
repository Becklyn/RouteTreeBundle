<?php

namespace Becklyn\RouteTreeBundle\Cache;

use Becklyn\RouteTreeBundle\Tree\Node;
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
     * Returns the cached tree
     *
     * @return null|Node[]
     */
    public function getTree () : ?array
    {
        if (!$this->isDebug && !empty($this->nodes))
        {
            return $this->nodes;
        }

        return null;
    }



    /**
     * Updates the cached tree
     *
     * @param Node[] $nodes
     */
    public function setTree (array $nodes)
    {
        $this->nodes = $nodes;
        $this->cacheItem->set($this->nodes);
        $this->cachePool->save($this->cacheItem);
    }



    /**
     * Clears the cache
     */
    public function clear ()
    {
        $this->setTree([]);
    }
}
