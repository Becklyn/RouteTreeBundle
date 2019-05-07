<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\PostProcessing;

use Becklyn\RouteTreeBundle\Node\Node;
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
     * @param SecurityProcessor $securityProcessor
     */
    public function __construct (SecurityProcessor $securityProcessor)
    {
        $this->securityProcessor = $securityProcessor;
    }


    /**
     * Post processes the tree.
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
        }

        return $nodes;
    }
}
