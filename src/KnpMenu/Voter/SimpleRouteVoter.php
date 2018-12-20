<?php declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\KnpMenu\Voter;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Voter\VoterInterface;
use Symfony\Component\HttpFoundation\RequestStack;


/**
 * Simple route voter, that votes only based on the route name.
 */
class SimpleRouteVoter implements VoterInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;


    /**
     * @param RequestStack $requestStack
     */
    public function __construct (RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }


    /**
     * @inheritDoc
     */
    public function matchItem (ItemInterface $item)
    {
        $request = $this->requestStack->getMasterRequest();

        if (null === $request)
        {
            return null;
        }

        $route = $request->attributes->get("_route");

        if (null === $route)
        {
            return null;
        }

        $definedRoutes = (array) $item->getExtra("routes", []);

        foreach ($definedRoutes as $routeDefinition)
        {
            if ($routeDefinition["route"] === $route)
            {
                return true;
            }
        }

        return null;
    }
}
