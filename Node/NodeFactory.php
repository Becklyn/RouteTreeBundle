<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Node;

use Becklyn\RouteTreeBundle\Builder\TreeBuilder;
use Becklyn\RouteTreeBundle\Node\Security\SecurityInferHelper;
use Symfony\Component\Routing\Route;


class NodeFactory
{
    /**
     * @var SecurityInferHelper
     */
    private $securityInferHelper;


    /**
     * @param SecurityInferHelper $securityInferHelper
     */
    public function __construct (SecurityInferHelper $securityInferHelper)
    {
        $this->securityInferHelper = $securityInferHelper;
    }


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

            if (isset($routeData["priority"]))
            {
                $node->setPriority($routeData["priority"]);
            }

            if (isset($routeData["parameters"]))
            {
                $node->setParameters($routeData["parameters"]);
            }

            if (isset($routeData["security"]))
            {
                // prefer explicitly set security settings
                $node->setSecurity($routeData["security"]);
            }
            else
            {
                $this->inferSecurity($node, $route);
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


    /**
     * Infers the security from the linked controller
     *
     * @param Node  $node
     * @param Route $route
     */
    private function inferSecurity (Node $node, Route $route) : void
    {
        $controller = $route->getDefault("_controller");

        if (null === $controller)
        {
            return;
        }

        $security = $this->securityInferHelper->inferSecurity($controller);
        dump($security);

        if (null !== $security)
        {
            $node->setSecurity($security);
        }
    }
}
