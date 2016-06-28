<?php

namespace Becklyn\RouteTreeBundle\Tree\Processing;

use Becklyn\RouteTreeBundle\Tree\Node;
use Becklyn\RouteTreeBundle\Tree\RouteTree;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;


/**
 *
 */
class PostProcessing
{
    /**
     * @var TranslatorInterface
     */
    private $translator;


    /**
     * @var SecurityProcessor
     */
    private $securityProcessor;


    /**
     * @var RequestStack
     */
    private $requestStack;



    /**
     * @param TranslatorInterface $translator
     * @param SecurityProcessor   $securityProcessor
     * @param RequestStack        $requestStack
     */
    public function __construct (TranslatorInterface $translator, SecurityProcessor $securityProcessor, RequestStack $requestStack)
    {
        $this->translator = $translator;
        $this->securityProcessor = $securityProcessor;
        $this->requestStack = $requestStack;
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
            $this->translateTitle($node);
            $this->applySecurity($node);
        }

        if (null !== $this->requestStack->getCurrentRequest())
        {
            $requestAttributes = $this->requestStack->getCurrentRequest()->attributes;

            foreach ($nodes as $node)
            {
                $this->generateMissingParameters($requestAttributes, $node);
            }
        }

        return $nodes;
    }



    /**
     * Translates the title of the given node
     *
     * @param Node $node
     */
    private function translateTitle (Node $node)
    {
        if (null === $node->getTitle())
        {
            return;
        }

        $node->setTitle(
            $this->translator->trans($node->getTitle(), [], RouteTree::TREE_TRANSLATION_DOMAIN)
        );
    }



    /**
     * Applies the security check for the given node
     *
     * @param Node $node
     */
    private function applySecurity (Node $node)
    {
        if (!$this->securityProcessor->isAllowedToAccessNode($node))
        {
            $node->setHidden(true);
        }
    }



    /**
     * @param ParameterBag $requestAttributes
     * @param Node         $node
     *
     * @return string[]
     */
    private function generateMissingParameters (ParameterBag $requestAttributes, Node $node)
    {
        $parameters = $node->getParameters();

        foreach ($parameters as $key => $value)
        {
            if ($value === null)
            {
                $parameters[$key] = $requestAttributes->get($key, 1);
            }
        }

        $node->setParameters($parameters);
    }
}
