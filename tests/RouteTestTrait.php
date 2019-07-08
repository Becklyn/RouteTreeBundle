<?php

namespace Tests\Becklyn\RouteTreeBundle;

use Becklyn\Menu\Item\MenuItem;
use Becklyn\RouteTreeBundle\Builder\ItemCollection;
use Becklyn\RouteTreeBundle\Node\ItemFactory;
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
     * @return MenuItem[]
     */
    private function buildAndGetNodes (array $routes) : array
    {
        $securityInferHelper = $this->getMockBuilder(SecurityInferHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $nodeFactory = new ItemFactory($securityInferHelper);
        return (new ItemCollection($nodeFactory, $routes))->getItems();
    }

}
