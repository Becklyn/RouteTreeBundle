<?php

namespace Becklyn\RouteTreeBundle\Tree\Processing\PostProcessing;

use Becklyn\RouteTreeBundle\Tree\Node;
use Becklyn\RouteTreeBundle\Tree\RouteTree;
use Symfony\Component\Translation\TranslatorInterface;


/**
 *
 */
class TranslationsProcessor
{
    /**
     * @var TranslatorInterface
     */
    private $translator;



    /**
     * @param TranslatorInterface $translator
     */
    public function __construct (TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }



    /**
     * Processes the given node
     *
     * @param Node $node
     */
    public function process (Node $node)
    {
        if (null === $node->getTitle())
        {
            return;
        }

        $node->setTitle(
            $this->translator->trans($node->getTitle(), [], RouteTree::TREE_TRANSLATION_DOMAIN)
        );
    }
}
