<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\PostProcessing;

use Becklyn\RouteTreeBundle\Node\Node;
use Becklyn\RouteTreeBundle\PostProcessing\Processor\MissingParametersProcessor;
use Becklyn\RouteTreeBundle\PostProcessing\Processor\SecurityProcessor;
use Becklyn\RouteTreeBundle\PostProcessing\Processor\TranslationsProcessor;


/**
 *
 */
class PostProcessor
{
    /**
     * @var TranslationsProcessor
     */
    private $translationsProcessor;


    /**
     * @var SecurityProcessor
     */
    private $securityProcessor;


    /**
     * @var MissingParametersProcessor
     */
    private $missingParametersProcessor;


    /**
     * @param TranslationsProcessor      $translationsProcessor
     * @param SecurityProcessor          $securityProcessor
     * @param MissingParametersProcessor $missingParametersProcessor
     */
    public function __construct (
        TranslationsProcessor $translationsProcessor,
        SecurityProcessor $securityProcessor,
        MissingParametersProcessor $missingParametersProcessor
    )
    {
        $this->translationsProcessor = $translationsProcessor;
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
            $this->translationsProcessor->process($node);
            $this->securityProcessor->process($node);
            $this->missingParametersProcessor->process($node);
        }

        return $nodes;
    }
}
