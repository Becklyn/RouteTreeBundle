<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\PostProcessing;

use Becklyn\RouteTreeBundle\Node\Node;
use Becklyn\RouteTreeBundle\PostProcessing\Processor\MissingParametersProcessor;
use Becklyn\RouteTreeBundle\PostProcessing\Processor\SecurityProcessor;


/**
 *
 */
class PostProcessor
{
    /**
     * @var SecurityProcessor
     */
    private $securityProcessor;


    /**
     * @var MissingParametersProcessor
     */
    private $missingParametersProcessor;


    /**
     * @param SecurityProcessor          $securityProcessor
     * @param MissingParametersProcessor $missingParametersProcessor
     */
    public function __construct (
        SecurityProcessor $securityProcessor,
        MissingParametersProcessor $missingParametersProcessor
    )
    {
        $this->securityProcessor = $securityProcessor;
        $this->missingParametersProcessor = $missingParametersProcessor;
    }


    /**
     * Post processes the tree
     *
     * @param Node[] $nodes
     *
     * @return Node[]
     */
    public function postProcessTree (array $nodes)
    {
        foreach ($nodes as $node)
        {
            $this->securityProcessor->process($node);
            $this->missingParametersProcessor->process($node);
        }

        return $nodes;
    }
}
