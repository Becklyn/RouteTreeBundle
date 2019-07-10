<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Tree;

use Becklyn\Menu\Item\MenuItem;
use Becklyn\RouteTreeBundle\Builder\ItemCollectionBuilder;
use Becklyn\RouteTreeBundle\Cache\TreeCache;
use Becklyn\RouteTreeBundle\Exception\InvalidRouteTreeException;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 *
 */
class RouteTree implements CacheClearerInterface, CacheWarmerInterface
{
    /**
     * @var MenuItem[]
     */
    private $items;


    /**
     * @var ItemCollectionBuilder
     */
    private $collectionBuilder;


    /**
     * @var TreeCache
     */
    private $cache;


    /**
     * @param ItemCollectionBuilder $collectionBuilder
     * @param TreeCache             $cache
     */
    public function __construct (ItemCollectionBuilder $collectionBuilder, TreeCache $cache)
    {
        $this->collectionBuilder = $collectionBuilder;
        $this->cache = $cache;
    }


    /**
     * Builds the tree.
     *
     * @return MenuItem[]
     */
    private function generateItems () : array
    {
        $nodes = $this->cache->get();

        if (null === $nodes)
        {
            $collection = $this->collectionBuilder->build();
            $nodes = $collection->getItems();
            $this->cache->set($nodes);
        }

        return $nodes;
    }


    /**
     * Fetches a node from the tree.
     *
     * @param string $route
     *
     * @throws InvalidRouteTreeException
     *
     * @return MenuItem|null
     */
    public function getByRoute (string $route) : ?MenuItem
    {
        if (null === $this->items)
        {
            $this->items = $this->generateItems();
        }

        return $this->items[$route] ?? null;
    }



    //region Cache clearer implementation
    /**
     * @inheritDoc
     *
     * @internal
     */
    public function clear ($cacheDir) : void
    {
        $this->cache->clear();
    }
    //endregion



    //region Cache warmer implementation
    /**
     * @inheritDoc
     *
     * @internal
     */
    public function isOptional ()
    {
        return true;
    }



    /**
     * @inheritDoc
     *
     * @internal
     */
    public function warmUp ($cacheDir) : void
    {
        $this->cache->clear();
        $this->generateItems();
    }
    //endregion
}
