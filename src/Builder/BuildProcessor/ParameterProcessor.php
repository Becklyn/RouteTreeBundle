<?php

namespace Becklyn\RouteTreeBundle\Builder\BuildProcessor;

use Becklyn\RouteTreeBundle\Node\Node;


/**
 * Processes all nodes, so that they can access the default parameters of their parents
 */
class ParameterProcessor
{
    private $configs;


    /**
     * @param array $configs
     */
    public function __construct (array $configs)
    {
        $this->configs = $configs;
    }


    /**
     * Calculates all parameters
     *
     * @param Node[] $nodes
     *
     * @return Node[]
     */
    public function calculateAllParameters (array $nodes) : array
    {
        foreach ($nodes as $name => $node)
        {
            // only loop through the top level nodes as the parameters generator itself traverses the tree
            if (null === $node->getParent())
            {
                $this->generateParametersForNode($node, []);
            }
        }

        return $nodes;
    }

    /**
     * Automatically sets the parameters for all descendant nodes
     *
     * @param Node $node
     */
    private function generateParametersForNode (Node $node, array $parentDefaults)
    {
        $defaults = \array_replace(
            $parentDefaults,
            $this->configs[$node->getRoute()]["parameters"] ?? []
        );

        $node->updateParameterValues($defaults, false);

        foreach ($node->getChildren() as $child)
        {
            $this->generateParametersForNode($child, $defaults);
        }
    }
}
