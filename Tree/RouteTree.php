<?php

namespace Becklyn\RouteTreeBundle\Tree;

use Becklyn\RouteTreeBundle\Builder\TreeBuilder;
use Becklyn\RouteTreeBundle\Cache\TreeCache;
use Becklyn\RouteTreeBundle\Tree\Processing\PostProcessing;


/**
 *
 */
class RouteTree
{
    const TREE_TRANSLATION_DOMAIN = "route_tree";

    /**
     * @var Node[]
     */
    private $tree = [];



    /**
     * @param TreeBuilder    $builder
     * @param TreeCache      $cache
     * @param PostProcessing $postProcessing
     */
    public function __construct (TreeBuilder $builder, TreeCache $cache, PostProcessing $postProcessing)
    {
        $tree = $cache->getTree();

        if (null === $tree)
        {
            $tree = $builder->buildTree();
            $cache->setTree($tree);
        }

        $this->tree = $postProcessing->postProcessTree($tree);
    }



    /**
     * Fetches a node from the tree
     *
     * @param string $route
     *
     * @return Node|null
     */
    public function getNode ($route)
    {
        return isset($this->tree[$route])
            ? $this->tree[$route]
            : null;
    }
}
