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
        $mergedParameters = $node->getMergedParameters();

        foreach ($parameters as $key => $value)
        {
            if ($value === null)
            {
                if ($requestAttributes->has($key))
                {
                    $parameters[$key] = $requestAttributes->get($key);
                }
                else if (isset($mergedParameters[$key]))
                {
                    $parameters[$key] = $mergedParameters[$key];
                }
                else
                {
                    $parameters[$key] = self::DEFAULT_PARAMETER_VALUE;
                }
            }
        }

        $node->setParameters($parameters);
    }
}
