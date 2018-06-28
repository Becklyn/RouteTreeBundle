<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Node;

use Becklyn\RouteTreeBundle\Builder\TreeBuilder;
use Becklyn\RouteTreeBundle\Node\Security\SecurityInferHelper;
use Becklyn\RouteTreeBundle\Routing\RoutingConfigReader;
use Symfony\Component\Routing\Route;


class NodeFactory
{
    /**
     * @var RoutingConfigReader
     */
    private $routingConfigReader;


    /**
     * @var SecurityInferHelper
     */
    private $securityInferHelper;


    /**
     * @param RoutingConfigReader $routingConfigReader
     * @param SecurityInferHelper $securityInferHelper
     */
    public function __construct (RoutingConfigReader $routingConfigReader, SecurityInferHelper $securityInferHelper)
    {
        $this->routingConfigReader = $routingConfigReader;
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
        $routeData = $this->routingConfigReader->getConfig($route);

        // if there is no tree data
        if (is_array($routeData))
        {
            foreach ($routeData as $key => $value)
            {
                switch ($key)
                {
                    case "title":
                        $node->setTitle($value);
                        break;

                    case "priority":
                        $node->setPriority($value);
                        break;

                    case "parameters":
                        $node->setParameters($value);
                        break;

                    case "security":
                        $node->setSecurity($value);
                        break;

                    // all unknown parameters are automatically extras
                    default:
                        $node->setExtra($key, $value);
                        break;
                }
            }

            // infer security only if it is not explicitly set on the node
            if (!isset($routeData["security"]))
            {
                $this->inferSecurity($node, $route);
            }
        }

        // set all required parameters at least as "null"
        $node->setParameters(
            array_replace(
                array_fill_keys($route->compile()->getVariables(), null),
                $node->getParameters()
            )
        );

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

        if (null !== $security)
        {
            $node->setSecurity($security);
        }
    }
}
