<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Builder;

use Becklyn\RouteTreeBundle\Exception\InvalidRouteTreeException;
use Becklyn\RouteTreeBundle\Tree\Node;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;


/**
 * Builds a tree from a list of routes
 */
class TreeBuilder
{
    const PARENT_CONFIGURATION_OPTION = "parent";
    const ROUTE_OPTIONS_KEY = "tree";

    /**
     * All configuration options.
     *
     * Mapping of routing configuration to the properties of the Node
     * ["config-option" => "propertyPath"]
     *
     * @var string[]
     */
    private static $configurationOptions = [
        "title" => "title",
        "hidden" => "hidden",
        "separator" => "separator",
        "parameters" => "parameters",
        "security" => "security",
        "extra" => "extra",
        self::PARENT_CONFIGURATION_OPTION => "parentRoute",
    ];


    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;


    /**
     * @var ParametersGenerator
     */
    private $parametersGenerator;



    /**
     * @param ParametersGenerator $parametersGenerator
     */
    public function __construct (ParametersGenerator $parametersGenerator)
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->parametersGenerator = $parametersGenerator;
    }



    /**
     * Builds the route tree
     *
     * @param Route[]|RouteCollection $routeCollection
     *
     * @return Node[] the array is indexed by route name
     */
    public function buildTree ($routeCollection)
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
    private function calculateRelevantRoutes ($routeCollection)
    {
        $routeIndex = [];

        foreach ($routeCollection as $routeName => $route)
        {
            $routeIndex[$routeName] = false;
        }

        foreach ($routeCollection as $routeName => $route)
        {
            $routeData = $route->getOption(self::ROUTE_OPTIONS_KEY);

            // no route data found -> skip
            if (null === $routeData)
            {
                continue;
            }

            // route data found -> mark as relevant
            $routeIndex[$routeName] = true;

            // mark parent route as relevant
            if (isset($routeData[self::PARENT_CONFIGURATION_OPTION]) && !empty($routeData[self::PARENT_CONFIGURATION_OPTION]))
            {
                $parentRoute = $routeData[self::PARENT_CONFIGURATION_OPTION];

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
    private function generateNodesFromRoutes ($routeCollection, array $relevantRoutes)
    {
        $nodes = [];

        foreach ($routeCollection as $routeName => $route)
        {
            // skip not relevant routes
            if (!isset($relevantRoutes[$routeName]) || !$relevantRoutes[$routeName])
            {
                continue;
            }

            $nodes[$routeName] = $this->createNodeFromRoute($routeName, $route);
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
    private function linkNodeHierarchy (array $nodes)
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
     * Creates a node from a route
     *
     * @param string $routeName
     * @param Route  $route
     *
     * @return Node
     */
    private function createNodeFromRoute ($routeName, Route $route)
    {
        $routeData = $route->getOption(self::ROUTE_OPTIONS_KEY);
        $node = new Node($routeName);

        // if there is no tree data
        if (is_array($routeData))
        {
            // set basic data automatically
            foreach (self::$configurationOptions as $configOption => $propertyPath)
            {
                if (isset($routeData[$configOption]))
                {
                    $this->propertyAccessor->setValue($node, $propertyPath, $routeData[$configOption]);
                }
            }

            // set all required parameters at least as "null"
            $node->setParameters(
                array_replace(
                    array_fill_keys($route->compile()->getVariables(), null),
                    $node->getParameters()
                )
            );
        }

        return $node;
    }



    /**
     * Calculates all parameters
     *
     * @param Node[] $nodes
     *
     * @return Node[]
     */
    private function calculateAllParameters (array $nodes)
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
