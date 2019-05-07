<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Tree;

use Becklyn\RouteTreeBundle\Builder\NodeCollectionBuilder;
use Becklyn\RouteTreeBundle\Cache\TreeCache;
use Becklyn\RouteTreeBundle\Exception\InvalidRouteTreeException;
use Becklyn\RouteTreeBundle\Node\Node;
use Becklyn\RouteTreeBundle\PostProcessing\PostProcessor;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 *
 */
class RouteTree implements CacheClearerInterface, CacheWarmerInterface
{
    /**
     * @var Node[]
     */
    private $nodes;


    /**
     * @var NodeCollectionBuilder
     */
    private $nodeCollectionBuilder;


    /**
     * @var TreeCache
     */
    private $cache;


    /**
     * @var PostProcessor
     */
    private $postProcessing;


    /**
     * @param NodeCollectionBuilder $nodeCollectionBuilder
     * @param TreeCache             $cache
     * @param PostProcessor         $postProcessing
     */
    public function __construct (NodeCollectionBuilder $nodeCollectionBuilder, TreeCache $cache, PostProcessor $postProcessing)
    {
        $this->nodeCollectionBuilder = $nodeCollectionBuilder;
        $this->cache = $cache;
        $this->postProcessing = $postProcessing;
    }


    /**
     * Builds the tree.
     *
     * @return Node[]
     */
    private function generateNodes () : array
    {
        $nodes = $this->cache->get();

        if (null === $nodes)
        {
            $nodeCollection = $this->nodeCollectionBuilder->build();
            $nodes = $nodeCollection->getNodes();
            $this->cache->set($nodes);
        }

        return $this->postProcessing->postProcessTree($nodes);
    }


    /**
     * Fetches a node from the tree.
     *
     * @param string $route
     *
     * @throws InvalidRouteTreeException
     *
     * @return Node|null
     */
    public function getNode (string $route) : ?Node
    {
        if (null === $this->nodes)
        {
            $this->nodes = $this->generateNodes();
        }

        return $this->nodes[$route] ?? null;
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
        $this->generateNodes();
    }
    //endregion
}
