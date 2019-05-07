<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Node;

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
     * Generates a node from the given route.
     *
     * @param string      $routeName
     * @param array       $config
     * @param array       $variables
     * @param array       $requirements
     * @param string|null $controller
     *
     * @return Node
     */
    public function createNode (string $routeName, array $config, array $variables, array $requirements, ?string $controller) : Node
    {
        $node = new Node($routeName, $variables, $requirements);

        foreach ($config as $key => $value)
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
                    $node->updateParameterValues($value, true);
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
        if (!isset($config["security"]) && null !== $controller)
        {
            $this->inferSecurity($node, $controller);
        }

        return $node;
    }


    /**
     * Infers the security from the linked controller.
     *
     * @param Node   $node
     * @param string $controller
     */
    private function inferSecurity (Node $node, string $controller) : void
    {
        $security = $this->securityInferHelper->inferSecurity($controller);

        if (null !== $security)
        {
            $node->setSecurity($security);
        }
    }
}
