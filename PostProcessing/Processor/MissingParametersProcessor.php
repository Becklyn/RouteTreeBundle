<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\PostProcessing\Processor;

use Becklyn\RouteTreeBundle\Node\Node;
use Symfony\Component\HttpFoundation\RequestStack;


/**
 *
 */
class MissingParametersProcessor
{
    const DEFAULT_PARAMETER_VALUE = 1;


    /**
     * @var RequestStack
     */
    private $requestStack;


    /**
     * @param RequestStack $requestStack
     */
    public function __construct (RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * Processes the given node
     *
     * @param array $requestAttributes
     * @param Node  $node
     */
    public function process (Node $node)
    {
        $request = $this->requestStack->getMasterRequest();

        if (null === $request)
        {
            return $request;
        }

        $attributes = $request->attributes->get("_route_params", []);
        $parameters = $node->getParameters();
        $mergedParameters = $node->getMergedParameters();

        foreach ($parameters as $key => $value)
        {
            if ($value === null)
            {
                $parameters[$key] = $attributes[$key]
                    ?? $mergedParameters[$key]
                    ?? self::DEFAULT_PARAMETER_VALUE;
            }
        }

        $node->setParameters($parameters);
    }
}
