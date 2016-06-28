<?php

namespace Becklyn\RouteTreeBundle\Tree\Processing;

use Becklyn\RouteTreeBundle\Tree\Node;
use Becklyn\RouteTreeBundle\Tree\Processing\PostProcessing\MissingParametersProcessor;
use Becklyn\RouteTreeBundle\Tree\Processing\PostProcessing\SecurityProcessor;
use Becklyn\RouteTreeBundle\Tree\Processing\PostProcessing\TranslationsProcessor;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RequestStack;


/**
 *
 */
class PostProcessing
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
     * @var RequestStack
     */
    private $requestStack;


    /**
     * @var MissingParametersProcessor
     */
    private $missingParametersProcessor;



    /**
     * @param TranslationsProcessor      $translationsProcessor
     * @param SecurityProcessor          $securityProcessor
     * @param MissingParametersProcessor $missingParametersProcessor
     * @param RequestStack               $requestStack
     */
    public function __construct (TranslationsProcessor $translationsProcessor, SecurityProcessor $securityProcessor, MissingParametersProcessor $missingParametersProcessor, RequestStack $requestStack)
    {
        $this->translationsProcessor = $translationsProcessor;
        $this->securityProcessor = $securityProcessor;
        $this->requestStack = $requestStack;
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
        }

        if (null !== $this->requestStack->getCurrentRequest())
        {
            $requestAttributes = $this->requestStack->getCurrentRequest()->attributes;

            foreach ($nodes as $node)
            {
                $this->missingParametersProcessor->process($requestAttributes->get("_route_params", []), $node);
            }
        }

        return $nodes;
    }
}
