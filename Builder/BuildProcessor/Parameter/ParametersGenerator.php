<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Builder\BuildProcessor\Parameter;

use Becklyn\RouteTreeBundle\Node\Node;


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
        $node->setMergedParameters($this->calculateParametersForNode($node));

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
        $parameters = $node->getMergedParameters();

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
     * @param string $name
     * @param Node   $node
     *
     * @return mixed
     */
    private function findParameterInTree (string $name, Node $node)
    {
        $nodeParameters = $node->getMergedParameters();

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
