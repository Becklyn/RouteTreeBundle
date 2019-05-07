<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\PostProcessing\Processor;

use Becklyn\RouteTreeBundle\Node\Node;
use Becklyn\RouteTreeBundle\PostProcessing\Processor\Security\SecurityChecker;

/**
 * Handles the security checks in tree nodes.
 */
class SecurityProcessor
{
    /**
     * @var SecurityChecker
     */
    private $securityChecker;


    /**
     * @param SecurityChecker $securityChecker
     */
    public function __construct (SecurityChecker $securityChecker)
    {
        $this->securityChecker = $securityChecker;
    }



    /**
     * @param Node $node
     *
     * @return bool
     */
    private function isAllowedToAccessNode (Node $node) : bool
    {
        if (empty($node->getSecurity()))
        {
            // always allowed, if no security is set
            return true;
        }

        return $this->securityChecker->canAccess($node->getSecurity());
    }



    /**
     * Processes the given node.
     *
     * @param Node $node
     */
    public function process (Node $node) : void
    {
        if (!$this->isAllowedToAccessNode($node))
        {
            $node->setHidden(true);
        }
    }
}
