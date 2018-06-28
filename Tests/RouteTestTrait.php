<?php

namespace Tests\Becklyn\RouteTreeBundle;

use Becklyn\RouteTreeBundle\Builder\NodeCollection;
use Becklyn\RouteTreeBundle\Node\Node;
use Becklyn\RouteTreeBundle\Node\NodeFactory;
use Becklyn\RouteTreeBundle\Node\Security\SecurityInferHelper;
use Symfony\Component\Routing\Route;


/**
 *
 */
trait RouteTestTrait
{
    /**
     * Creates a route
     *
     * @param string       $path
     * @param array|string $treeData
     *
     * @return Route
     */
    private function createRoute ($path, $treeData = [], array $defaults = [])
    {
        $options = !empty($treeData)
            ? ["tree" => $treeData]
            : [];

        return new Route($path, $defaults, [], $options);
    }





    /**
     * Builds a collection and gets its nodes
     *
     * @param Route[] $routes
     * @return Node[]
     */
    private function buildAndGetNodes (array $routes) : array
    {
        $securityInferHelper = $this->getMockBuilder(SecurityInferHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $nodeFactory = new NodeFactory($securityInferHelper);
        return (new NodeCollection($nodeFactory, $routes))->getNodes();
    }

}
