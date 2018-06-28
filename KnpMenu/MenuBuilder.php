<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\KnpMenu;

use Becklyn\RouteTreeBundle\Exception\RouteTreeException;
use Becklyn\RouteTreeBundle\Node\Node;
use Becklyn\RouteTreeBundle\Tree\RouteTree;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\HttpFoundation\RequestStack;


/**
 *
 */
class MenuBuilder
{
    /**
     * @var FactoryInterface
     */
    private $factory;


    /**
     * @var RouteTree
     */
    private $routeTree;


    /**
     * @var RequestStack
     */
    private $requestStack;


    /**
     * @param FactoryInterface $factory
     * @param RouteTree        $routeTree
     * @param RequestStack     $requestStack
     */
    public function __construct (FactoryInterface $factory, RouteTree $routeTree, RequestStack $requestStack)
    {
        $this->factory = $factory;
        $this->routeTree = $routeTree;
        $this->requestStack = $requestStack;
    }


    /**
     * Builds the menu from a given route
     *
     * @param string $fromRoute
     * @param array  $parameters
     * @param array  $routeParameters
     * @return ItemInterface
     */
    public function buildMenu (string $fromRoute, array $parameters = [], array $routeParameters = []) : ItemInterface
    {
        $menuRoot = $this->factory->createItem("root");
        $requestParameters = [];

        $request = $this->requestStack->getMasterRequest();

        if (null !== $request)
        {
            $requestParameters = $request->attributes->get("_route_params");
        }

        try
        {
            $rootNode = $this->routeTree->getNode($fromRoute);

            if (null !== $rootNode)
            {
                $this->appendNodes($menuRoot, $rootNode->getChildren(), $requestParameters, $parameters, $routeParameters);
            }

            return $menuRoot;
        }
        catch (RouteTreeException $e)
        {
            return $menuRoot;
        }
    }


    /**
     * Appends the node tree to the given parent
     *
     * @param ItemInterface $parent
     * @param Node[]        $nodes
     */
    private function appendNodes (ItemInterface $parent, array $nodes, array $requestParameters, array $parameters, array $routeParameters) : void
    {
        foreach ($nodes as $node)
        {
            $child = $parent->addChild($node->getDisplayTitle(), [
                "route" => $node->getRoute(),
                "routeParameters" => $this->getRouteParameters($node, $requestParameters, $parameters, $routeParameters),
            ]);

            $child->setDisplay(!$node->isHidden());
            // we need to preserve the original extras
            $child->setExtras(\array_replace(
                $node->getExtras(),
                $child->getExtras()
            ));

            $this->appendNodes($child, $node->getChildren(), $requestParameters, $parameters, $routeParameters);
        }
    }


    /**
     * Gets the parameters for the route to the given node
     *
     * @param Node  $node
     * @param array $requestParameters
     * @param array $parameters
     * @param array $routeParameters
     * @return array
     */
    private function getRouteParameters (Node $node, array $requestParameters, array $parameters, array $routeParameters) : array
    {
        $result = [];
        $nodeParameters = $node->getParameterValues();

        foreach ($node->getRequiredParameters() as $name)
        {
            if (isset($routeParameters[$node->getRoute()]) && \array_key_exists($name, $routeParameters[$node->getRoute()]))
            {
                // first check if a route-specific parameter is given
                $value = $routeParameters[$node->getRoute()][$name];
            }
            else if (\array_key_exists($name, $parameters))
            {
                // then check if a default parameter is given
                $value = $parameters[$name];
            }
            else if (\array_key_exists($name, $requestParameters))
            {
                // then check if we can read a parameter from the request
                $value = $requestParameters[$name];
            }
            else if (\array_key_exists($name, $nodeParameters))
            {
                // then check if a default parameter was defined
                $value = $nodeParameters[$name];
            }
            else
            {
                // fall back to "1" if nothing else is set
                $value = 1;
            }

            $result[$name] = $value;
        }

        return $result;
    }
}
