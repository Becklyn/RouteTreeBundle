<?php

namespace Becklyn\RouteTreeBundle\Tree\Processing\PostProcessing;

use Becklyn\RouteTreeBundle\Tree\Node;
use Symfony\Component\HttpFoundation\ParameterBag;


/**
 *
 */
class MissingParametersProcessor
{
    const DEFAULT_PARAMETER_VALUE = 1;

    /**
     * Processes the given node
     *
     * @param ParameterBag $requestAttributes
     * @param Node         $node
     */
    public function process (ParameterBag $requestAttributes, Node $node)
    {
        $parameters = $node->getParameters();

        foreach ($parameters as $key => $value)
        {
            if ($value === null)
            {
                $parameters[$key] = $requestAttributes->get($key, self::DEFAULT_PARAMETER_VALUE);
            }
        }

        $node->setParameters($parameters);
    }
}
