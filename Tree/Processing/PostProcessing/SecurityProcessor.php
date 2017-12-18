<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Tree\Processing\PostProcessing;

use Becklyn\RouteTreeBundle\Tree\Node;
use Sensio\Bundle\FrameworkExtraBundle\Security\ExpressionLanguage;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;


/**
 * Handles the security checks in tree nodes.
 *
 * Should be in sync with the security annotation listener from the framework extra bundle
 *
 * @see \Sensio\Bundle\FrameworkExtraBundle\EventListener\SecurityListener
 * @link http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/security.html
 */
class SecurityProcessor
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;


    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;


    /**
     * @var ExpressionLanguage
     */
    private $language;


    /**
     * @var AuthenticationTrustResolverInterface
     */
    private $trustResolver;


    /**
     * @var RoleHierarchyInterface
     */
    private $roleHierarchy;


    /**
     * @var RequestStack
     */
    private $requestStack;



    /**
     * @param TokenStorageInterface                $tokenStorage
     * @param AuthorizationCheckerInterface        $authorizationChecker
     * @param ExpressionLanguage                   $language
     * @param AuthenticationTrustResolverInterface $trustResolver
     * @param RoleHierarchyInterface               $roleHierarchy
     * @param RequestStack                         $requestStack
     */
    public function __construct (TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authorizationChecker, ExpressionLanguage $language, AuthenticationTrustResolverInterface $trustResolver, RoleHierarchyInterface $roleHierarchy, RequestStack $requestStack)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->language = $language;
        $this->trustResolver = $trustResolver;
        $this->roleHierarchy = $roleHierarchy;
        $this->requestStack = $requestStack;
    }



    /**
     * @param Node $node
     *
     * @return bool
     */
    private function isAllowedToAccessNode (Node $node)
    {
        if (empty($node->getSecurity()))
        {
            // always allowed, if no security is set
            return true;
        }

        // if the user is not behind a firewall but a security string is set, prevent access
        if (null === $this->tokenStorage->getToken())
        {
            return false;
        }

        return $this->language->evaluate($node->getSecurity(), $this->getVariables());
    }



    /**
     * Returns the variables for the expression evaluation
     *
     * code should be sync with Symfony\Component\Security\Core\Authorization\Voter\ExpressionVoter
     *
     * @return array
     */
    private function getVariables ()
    {
        $request = $this->requestStack->getMasterRequest();
        $requestVariables = null !== $request
            ? $request->attributes->all()
            : [];
        $token = $this->tokenStorage->getToken();

        /** @var Role[] $roles */
        $roles = null !== $this->roleHierarchy
            ? $this->roleHierarchy->getReachableRoles($token->getRoles())
            : $token->getRoles();

        $variables = [
            'token' => $token,
            'user' => $token->getUser(),
            'request' => $request,
            'roles' => array_map(function (Role $role) { return $role->getRole(); }, $roles),
            'trust_resolver' => $this->trustResolver,
            // needed for the is_granted expression function
            'auth_checker' => $this->authorizationChecker,
        ];

        // controller variables should also be accessible
        return array_merge($requestVariables, $variables);
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
