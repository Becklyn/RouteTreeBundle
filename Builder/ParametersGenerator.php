<?php

namespace Becklyn\RouteTreeBundle\Builder;

use Becklyn\RouteTreeBundle\Tree\Node;


/**
 *
 */
class ParametersGenerator
{
    /**
     * Automatically sets the parameters for all descendant nodes
     *
     * @param Node $node
     */
    public function generateParametersForNode (Node $node)
    {
        $node->setParameters($this->calculateParametersForNode($node));

        foreach ($node->getChildren() as $child)
        {
            $this->generateParametersForNode($child);
        }
    }


    /**
     * Calculates the parameters for a single node
     *
     * @param Node $node
     *
     * @return string[]
     */
    private function calculateParametersForNode (Node $node)
    {
        $parameters = $node->getParameters();

        foreach ($parameters as $name => $value)
        {
            if (null === $value)
            {
                $parameters[$name] = $this->findParameterInTree($name, $node);
            }
        }

        return $parameters;
    }



    /**
     * Finds whether the parameter is defined in tree somewhere
     *
     * @param string     $name
     * @param Node $node
     *
     * @return null|string
     */
    private function findParameterInTree ($name, Node $node)
    {
        $nodeParameters = $node->getParameters();

        if (isset($nodeParameters[$name]) && null !== $nodeParameters[$name])
        {
            return $nodeParameters[$name];
        }

        $parent = $node->getParent();

        return null !== $parent
            ? $this->findParameterInTree($name, $parent)
            : null;
    }
}
