<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Tree\Processing\PostProcessing;

use Becklyn\RouteTreeBundle\Node\Node;


/**
 *
 */
class MissingParametersProcessor
{
    const DEFAULT_PARAMETER_VALUE = 1;

    /**
     * Processes the given node
     *
     * @param array $requestAttributes
     * @param Node  $node
     */
    public function process (array $requestAttributes, Node $node)
    {
        $parameters = $node->getParameters();
        $mergedParameters = $node->getMergedParameters();

        foreach ($parameters as $key => $value)
        {
            if ($value === null)
            {
                if (isset($requestAttributes[$key]))
                {
                    $parameters[$key] = $requestAttributes[$key];
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
