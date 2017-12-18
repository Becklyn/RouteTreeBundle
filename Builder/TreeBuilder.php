<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Builder;

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
    /**
     * @var NodeFactory
     */
    private $nodeFactory;


    /**
     * @var ParametersGenerator
     */
    private $parametersGenerator;


    /**
     * @param NodeFactory         $nodeFactory
     * @param ParametersGenerator $parametersGenerator
     */
    public function __construct (NodeFactory $nodeFactory, ParametersGenerator $parametersGenerator)
    {
        $this->nodeFactory = $nodeFactory;
        $this->parametersGenerator = $parametersGenerator;
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
        $nodes = $this->linkNodeHierarchy($nodes);

        // needs to be after the hierarchy has been set up
        return $this->calculateAllParameters($nodes);
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
            $routeData = $route->getOption(NodeFactory::CONFIG_OPTIONS_KEY);

            // no route data found -> skip
            if (null === $routeData)
            {
                continue;
            }

            // route data found -> mark as relevant
            $routeIndex[$routeName] = true;

            // mark parent route as relevant
            $parentRoute = $routeData[NodeFactory::CONFIG_OPTIONS_KEY] ?? null;

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
            if ($relevantRoutes[$routeName] ?? false)
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
     * @param Node[] $nodes
     *
     * @return Node[]
     */
    private function linkNodeHierarchy (array $nodes) : array
    {
        foreach ($nodes as $node)
        {
            $parentRoute = $node->getParentRoute();

            if (null !== $parentRoute)
            {
                $nodes[$parentRoute]->addChild($node);
            }
        }

        return $nodes;
    }



    /**
     * Calculates all parameters
     *
     * @param Node[] $nodes
     *
     * @return Node[]
     */
    private function calculateAllParameters (array $nodes) : array
    {
        foreach ($nodes as $node)
        {
            // only loop through the top level nodes as the parameters generator itself traverses the tree
            if (null === $node->getParent())
            {
                $this->parametersGenerator->generateParametersForNode($node);
            }
        }

        return $nodes;
    }
}
