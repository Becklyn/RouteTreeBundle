<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Tree;

use Becklyn\RouteTreeBundle\Builder\TreeBuilder;
use Becklyn\RouteTreeBundle\Cache\TreeCache;
use Becklyn\RouteTreeBundle\Exception\InvalidRouteTreeException;
use Becklyn\RouteTreeBundle\Node\Node;
use Becklyn\RouteTreeBundle\Tree\Processing\PostProcessing;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\Routing\RouterInterface;


/**
 *
 */
class RouteTree implements CacheClearerInterface, CacheWarmerInterface
{
    const TREE_TRANSLATION_DOMAIN = "route_tree";

    /**
     * @var Node[]
     */
    private $tree = null;


    /**
     * @var TreeBuilder
     */
    private $builder;


    /**
     * @var TreeCache
     */
    private $cache;


    /**
     * @var PostProcessing
     */
    private $postProcessing;


    /**
     * @var RouterInterface
     */
    private $router;



    /**
     * @param TreeBuilder     $builder
     * @param TreeCache       $cache
     * @param PostProcessing  $postProcessing
     * @param RouterInterface $router
     */
    public function __construct (TreeBuilder $builder, TreeCache $cache, PostProcessing $postProcessing, RouterInterface $router)
    {
        $this->builder = $builder;
        $this->cache = $cache;
        $this->postProcessing = $postProcessing;
        $this->router = $router;
    }


    /**
     * Builds the tree
     *
     * @return Node[]
     * @throws InvalidRouteTreeException
     */
    private function buildTree ()
    {
        $tree = $this->cache->getTree();

        if (null === $tree)
        {
            $tree = $this->builder->buildTree($this->router->getRouteCollection());
            $this->cache->setTree($tree);
        }

        return $this->postProcessing->postProcessTree($tree);
    }


    /**
     * Fetches a node from the tree
     *
     * @param string $route
     *
     * @return Node|null
     * @throws InvalidRouteTreeException
     */
    public function getNode ($route)
    {
        if (null === $this->tree)
        {
            $this->tree = $this->buildTree();
        }

        return isset($this->tree[$route])
            ? $this->tree[$route]
            : null;
    }



    //region Cache clearer implementation
    /**
     * @inheritDoc
     */
    public function clear ($cacheDir)
    {
        $this->cache->clear();
    }
    //endregion



    //region Cache warmer implementation
    /**
     * @inheritDoc
     */
    public function isOptional ()
    {
        return true;
    }



    /**
     * @inheritDoc
     */
    public function warmUp ($cacheDir)
    {
        $this->cache->clear();
        $this->buildTree();
    }
    //endregion
}
