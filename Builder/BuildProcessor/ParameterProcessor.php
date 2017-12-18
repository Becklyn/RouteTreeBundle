<?php

namespace Becklyn\RouteTreeBundle\Builder\BuildProcessor;


use Becklyn\RouteTreeBundle\Builder\BuildProcessor\Parameter\ParametersGenerator;
use Becklyn\RouteTreeBundle\Node\Node;


class ParameterProcessor
{
    /**
     * @var ParametersGenerator
     */
    private $parametersGenerator;


    /**
     * @param ParametersGenerator $parametersGenerator
     */
    public function __construct (ParametersGenerator $parametersGenerator)
    {
        $this->parametersGenerator = $parametersGenerator;
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
        foreach ($nodes as $node)
        {
            // only loop through the top level nodes as the parameters generator itself traverses the tree
            if (null === $node->getParent())
            {
                $this->parametersGenerator->generateParametersForNode($node);
            }
        }

        return $nodes;
    }
}
