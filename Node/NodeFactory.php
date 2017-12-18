<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Node;

use Becklyn\RouteTreeBundle\Builder\TreeBuilder;
use Symfony\Component\Routing\Route;


class NodeFactory
{


    /**
     * Generates a node from the given route
     *
     * @param string $routeName
     * @param Route  $route
     * @return Node
     */
    public function createNode (string $routeName, Route $route) : Node
    {
        $node = new Node($routeName);
        $routeData = $route->getOption(TreeBuilder::CONFIG_OPTIONS_KEY);

        // if there is no tree data
        if (is_array($routeData))
        {
            // set basic data
            if (isset($routeData["title"]))
            {
                $node->setTitle($routeData["title"]);
                $node->setHidden(false);
            }
            else
            {
                $node->setHidden(true);
            }

            if (isset($routeData["parameters"]))
            {
                $node->setParameters($routeData["parameters"]);
            }

            if (isset($routeData["security"]))
            {
                $node->setSecurity($routeData["security"]);
            }

            if (isset($routeData["extra"]))
            {
                $node->setExtra($routeData["extra"]);
            }

            // set all required parameters at least as "null"
            $node->setParameters(
                array_replace(
                    array_fill_keys($route->compile()->getVariables(), null),
                    $node->getParameters()
                )
            );
        }

        return $node;
    }
}
