<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\KnpMenu;

use Becklyn\RouteTreeBundle\Exception\RouteTreeException;
use Becklyn\RouteTreeBundle\Node\Node;
use Becklyn\RouteTreeBundle\Tree\RouteTree;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Psr\Log\LoggerInterface;
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
     * @var LoggerInterface|null
     */
    private $logger;


    /**
     * @param FactoryInterface     $factory
     * @param RouteTree            $routeTree
     * @param RequestStack         $requestStack
     * @param LoggerInterface|null $logger
     */
    public function __construct (FactoryInterface $factory, RouteTree $routeTree, RequestStack $requestStack, ?LoggerInterface $logger = null)
    {
        $this->factory = $factory;
        $this->routeTree = $routeTree;
        $this->requestStack = $requestStack;
        $this->logger = $logger;
    }


    /**
     * Builds the menu from a given route.
     *
     * @param string $fromRoute
     * @param array  $parameters
     * @param array  $routeParameters
     *
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
            if (null !== $this->logger)
            {
                $this->logger->error("Route tree building failed from route '{from_route}' due to an exception.", [
                    "from_route" => $fromRoute,
                    "exception" => $e,
                ]);
            }

            return $menuRoot;
        }
    }


    /**
     * Appends the node tree to the given parent.
     *
     * @param ItemInterface $parent
     * @param Node[]        $nodes
     */
    private function appendNodes (ItemInterface $parent, array $nodes, array $requestParameters, array $parameters, array $routeParameters) : void
    {
        foreach ($nodes as $node)
        {
            $child = $this->addChild($parent, $node, $requestParameters, $parameters, $routeParameters);
            $this->appendNodes($child, $node->getChildren(), $requestParameters, $parameters, $routeParameters);
        }
    }


    /**
     * Adds a single node as child.
     *
     * @param ItemInterface $parent
     * @param Node          $node
     * @param array         $requestParameters
     * @param array         $parameters
     * @param array         $routeParameters
     *
     * @return ItemInterface
     */
    private function addChild (ItemInterface $parent, Node $node, array $requestParameters, array $parameters, array $routeParameters) : ItemInterface
    {
        $child = $parent->addChild($node->getRoute(), [
            "route" => $node->getRoute(),
            "routeParameters" => $this->getRouteParameters($node, $requestParameters, $parameters, $routeParameters),
        ]);

        $child->setDisplay(!$node->isHidden());
        // we need to preserve the original extras
        $child->setExtras(\array_replace(
            $node->getExtras(),
            $child->getExtras()
        ));
        $child->setLabel($node->getDisplayTitle());

        return $child;
    }


    /**
     * Gets the parameters for the route to the given node.
     *
     * @param Node  $node
     * @param array $requestParameters
     * @param array $parameters
     * @param array $routeParameters
     *
     * @return array
     */
    private function getRouteParameters (Node $node, array $requestParameters, array $parameters, array $routeParameters) : array
    {
        $result = [];
        $nodeParameters = $node->getParameterValues();

        foreach ($node->getRequiredParameters() as $name)
        {
            if (isset($routeParameters[$node->getRoute()])
                && \array_key_exists($name, $routeParameters[$node->getRoute()])
                && $node->isValidParameterValue($name, $routeParameters[$node->getRoute()][$name])
            )
            {
                // first check if a route-specific parameter is given
                $value = $routeParameters[$node->getRoute()][$name];
            }
            elseif (\array_key_exists($name, $parameters) && $node->isValidParameterValue($name, $parameters[$name]))
            {
                // then check if a default parameter is given
                $value = $parameters[$name];
            }
            elseif (\array_key_exists($name, $requestParameters) && $node->isValidParameterValue($name, $requestParameters[$name]))
            {
                // then check if we can read a parameter from the request
                $value = $requestParameters[$name];
            }
            elseif (\array_key_exists($name, $nodeParameters) && $node->isValidParameterValue($name, $nodeParameters[$name]))
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


    /**
     * Builds the breadcrumb to the given node.
     * The breadcrumb is a one-level deep menu with the breadcrumb nodes as entries.
     * The node itself is the last item in the children list, the top most node is the first one.
     *
     * @param string $fromNode
     * @param array  $parameters
     * @param array  $routeParameters
     *
     * @return ItemInterface
     */
    public function buildBreadcrumb (string $fromNode, array $parameters = [], array $routeParameters = []) : ItemInterface
    {
        $menuRoot = $this->factory->createItem("root");
        $requestParameters = [];

        $request = $this->requestStack->getMasterRequest();

        if (null !== $request)
        {
            $requestParameters = $request->attributes->get("_route_params");
        }

        $hierarchy = $this->getHierarchyToNode($fromNode);
        $menuNode = $menuRoot;

        foreach ($hierarchy as $node)
        {
            $this->addChild($menuNode, $node, $requestParameters, $parameters, $routeParameters);
        }

        return $menuRoot;
    }


    /**
     * Returns the hierarchy to the given node.
     *
     * @param string $targetNode
     *
     * @return Node[]
     */
    private function getHierarchyToNode (string $targetNode) : array
    {
        $node = $this->routeTree->getNode($targetNode);
        $hierarchy = [];

        do
        {
            $hierarchy[] = $node;
            $node = $node->getParent();
        }
        while (null !== $node);

        return \array_reverse($hierarchy);
    }
}
