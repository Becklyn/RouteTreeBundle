<?php declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\PostProcessing\Processor\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * This implementation should be kept in-sync
 * with {@see Sensio\Bundle\FrameworkExtraBundle\EventListener\SecurityListener}.
 */
class SecurityChecker
{
    /**
     * @var ExtendedExpressionLanguage
     */
    private $language;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;


    /**
     * @var RoleHierarchyInterface
     */
    private $roleHierarchy;


    /**
     * @var AuthenticationTrustResolverInterface
     */
    private $trustResolver;


    /**
     * @var AuthorizationCheckerInterface
     */
    private $authChecker;


    /**
     * @var RequestStack
     */
    private $requestStack;


    /**
     * @param ExtendedExpressionLanguage           $language
     * @param TokenStorageInterface                $tokenStorage
     * @param RoleHierarchyInterface               $roleHierarchy
     * @param AuthenticationTrustResolverInterface $trustResolver
     * @param AuthorizationCheckerInterface        $authChecker
     * @param RequestStack                         $requestStack
     */
    public function __construct (
        ExtendedExpressionLanguage $language,
        TokenStorageInterface $tokenStorage,
        RoleHierarchyInterface $roleHierarchy,
        AuthenticationTrustResolverInterface $trustResolver,
        AuthorizationCheckerInterface $authChecker,
        RequestStack $requestStack
    )
    {
        $this->language = $language;
        $this->tokenStorage = $tokenStorage;
        $this->roleHierarchy = $roleHierarchy;
        $this->trustResolver = $trustResolver;
        $this->authChecker = $authChecker;
        $this->requestStack = $requestStack;
    }


    /**
     * Returns whether the current user can access with the given expression.
     *
     * @param string $expression
     *
     * @return bool
     */
    public function canAccess (string $expression) : bool
    {
        $request = $this->requestStack->getMasterRequest();

        if (null === $request)
        {
            // we have a security expression but no request -> be safe and deny access
            return false;
        }

        return (bool) $this->language->evaluate($expression, $this->getVariables($request));
    }


    /**
     * Returns the required variables for the expression language.
     *
     * @param Request $request
     *
     * @return array
     */
    private function getVariables (Request $request) : array
    {
        $token = $this->tokenStorage->getToken();

        $roles = (null !== $this->roleHierarchy)
            ? $this->roleHierarchy->getReachableRoles($token->getRoles())
            : $token->getRoles();

        // we only need the main variables, as we have no controller here
        return [
            'token' => $token,
            'user' => $token->getUser(),
            'object' => $request,
            'subject' => $request,
            'request' => $request,
            'roles' => \array_map(function (Role $role) { return $role->getRole(); }, $roles),
            'trust_resolver' => $this->trustResolver,
            // needed for the is_granted expression function
            'auth_checker' => $this->authChecker,
        ];
    }
}
