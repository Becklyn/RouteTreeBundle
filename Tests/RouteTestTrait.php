<?php

namespace Tests\Becklyn\RouteTreeBundle;

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
    protected function createRoute ($path, $treeData = [], array $defaults = [])
    {
        $options = !empty($treeData)
            ? ["tree" => $treeData]
            : [];

        return new Route($path, $defaults, [], $options);
    }
}
