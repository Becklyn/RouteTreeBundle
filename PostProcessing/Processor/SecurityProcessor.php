<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\PostProcessing\Processor;

use Becklyn\RouteTreeBundle\Node\Node;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\Voter\ExpressionVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;


/**
 * Handles the security checks in tree nodes.
 */
class SecurityProcessor
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;


    /**
     * @var ExpressionVoter
     */
    private $expressionVoter;


    /**
     * @param TokenStorageInterface $tokenStorage
     * @param ExpressionVoter       $expressionVoter
     */
    public function __construct (TokenStorageInterface $tokenStorage, ExpressionVoter $expressionVoter)
    {
        $this->tokenStorage = $tokenStorage;
        $this->expressionVoter = $expressionVoter;
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

        $token = $this->tokenStorage->getToken();

        // if the user is not behind a firewall but a security string is set, prevent access
        if (null === $token)
        {
            return false;
        }

        return VoterInterface::ACCESS_DENIED !== $this->expressionVoter->vote($token, null, [
            new Expression($node->getSecurity()),
        ]);
    }



    /**
     * Processes the given node
     *
     * @param Node $node
     */
    public function process (Node $node)
    {
        if (!$this->isAllowedToAccessNode($node))
        {
            $node->setHidden(true);
        }
    }
}
