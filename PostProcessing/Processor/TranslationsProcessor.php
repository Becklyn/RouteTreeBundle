<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\PostProcessing\Processor;

use Becklyn\RouteTreeBundle\Node\Node;
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
