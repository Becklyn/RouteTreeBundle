<?php

namespace Tests\Becklyn\RouteTreeBundle;

use Symfony\Component\Routing\Route;


/**
 *
 */
trait RouteTestTrait
{
    /**
     * Generates a route
     *
     * @param string       $path
     * @param array|string $treeData
     *
     * @return Route
     */
    protected function generateRoute ($path, $treeData = [])
    {
        $options = !empty($treeData)
            ? ["tree" => $treeData]
            : [];

        return new Route(
            $path,
            [], // $defaults
            [], // $requirements
            $options
        );
    }
}
