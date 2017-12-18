<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Builder;

use Becklyn\RouteTreeBundle\Builder\BuildProcessor\ParameterProcessor;
use Becklyn\RouteTreeBundle\Builder\BuildProcessor\PriorityProcessor;
use Becklyn\RouteTreeBundle\Exception\InvalidRouteTreeException;
use Becklyn\RouteTreeBundle\Node\Node;
use Becklyn\RouteTreeBundle\Node\NodeFactory;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;


/**
 * Builds a tree from a list of routes
 */
class TreeBuilder
{
    const CONFIG_OPTIONS_KEY = "tree";
    const CONFIG_PARENT_KEY = "parent";


    /**
     * @var NodeFactory
     */
    private $nodeFactory;


    /**
     * @var PriorityProcessor
     */
    private $priorityProcessor;


    /**
     * @var ParameterProcessor
     */
    private $parameterProcessor;


    /**
     * @param NodeFactory        $nodeFactory
     * @param PriorityProcessor  $priorityProcessor
     * @param ParameterProcessor $parameterProcessor
     */
    public function __construct (NodeFactory $nodeFactory, PriorityProcessor $priorityProcessor, ParameterProcessor $parameterProcessor)
    {
        $this->nodeFactory = $nodeFactory;
        $this->priorityProcessor = $priorityProcessor;
        $this->parameterProcessor = $parameterProcessor;
    }


    /**
     * Builds the route tree
     *
     * @param Route[]|RouteCollection $routeCollection
     *
     * @return Node[] the array is indexed by route name
     * @throws InvalidRouteTreeException
     */
    public function buildTree (iterable $routeCollection)
    {
        $relevantRoutes = $this->calculateRelevantRoutes($routeCollection);
        $nodes = $this->generateNodesFromRoutes($routeCollection, $relevantRoutes);
        $nodes = $this->linkNodeHierarchy($routeCollection, $nodes);

        // needs to be after the hierarchy has been set up
        $nodes = $this->priorityProcessor->sortNodes($nodes);
        return $this->parameterProcessor->calculateAllParameters($nodes);
    }


    /**
     * Calculates a list of which routes should be included in the tree
     *
     * @param Route[]|RouteCollection $routeCollection
     *
     * @return array of format ["route" => (bool) $includeInTree]
     * @throws InvalidRouteTreeException
     */
    private function calculateRelevantRoutes (iterable $routeCollection) : array
    {
        $routeIndex = [];

        foreach ($routeCollection as $routeName => $route)
        {
            $routeIndex[$routeName] = false;
        }

        foreach ($routeCollection as $routeName => $route)
        {
            $routeData = $route->getOption(self::CONFIG_OPTIONS_KEY);

            // no route data found -> skip
            if (null === $routeData)
            {
                continue;
            }

            // route data found -> mark as relevant
            $routeIndex[$routeName] = true;

            // mark parent route as relevant
            $parentRoute = $routeData[self::CONFIG_OPTIONS_KEY] ?? null;

            if (null !== $parentRoute)
            {
                if (!isset($routeIndex[$parentRoute]))
                {
                    throw new InvalidRouteTreeException(sprintf(
                        "Route '%s' references a parent '%s', but the parent route could not be found.",
                        $routeName,
                        $parentRoute
                    ));
                }

                $routeIndex[$parentRoute] = true;
            }
        }

        return $routeIndex;
    }


    /**
     * Generates the nodes for the given routes
     *
     * @param Route[]|RouteCollection $routeCollection
     * @param array                   $relevantRoutes
     *
     * @return Node[]
     */
    private function generateNodesFromRoutes (iterable $routeCollection, array $relevantRoutes) : array
    {
        $nodes = [];

        foreach ($routeCollection as $routeName => $route)
        {
            // skip not relevant routes
            if (!$relevantRoutes[$routeName] ?? false)
            {
                continue;
            }

            $nodes[$routeName] = $this->nodeFactory->createNode($routeName, $route);
        }

        return $nodes;
    }


    /**
     * Links the hierarchy between the nodes
     *
     * @param Route[]|RouteCollection $routeCollection
     * @param Node[]                  $nodes
     *
     * @return Node[]
     */
    private function linkNodeHierarchy (iterable $routeCollection, array $nodes) : array
    {
        foreach ($routeCollection as $routeName => $route)
        {
            $routeData = $route->getOption(self::CONFIG_OPTIONS_KEY);
            // we can silently ignore the null here in the array access, as the item will never exist
            $parentRoute = $routeData[self::CONFIG_PARENT_KEY] ?? null;

            $node = $nodes[$routeName] ?? null;
            $parent = $nodes[$parentRoute] ?? null;

            if (null !== $node && null !== $parent)
            {
                $node->setParent($parent);
                $parent->addChild($node);
            }
        }

        return $nodes;
    }
}
