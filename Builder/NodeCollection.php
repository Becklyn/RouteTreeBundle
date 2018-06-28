<?php declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Builder;


use Becklyn\RouteTreeBundle\Builder\BuildProcessor\ParameterProcessor;
use Becklyn\RouteTreeBundle\Builder\BuildProcessor\PriorityProcessor;
use Becklyn\RouteTreeBundle\Exception\InvalidRouteTreeException;
use Becklyn\RouteTreeBundle\Node\Node;
use Becklyn\RouteTreeBundle\Node\NodeFactory;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;


class NodeCollection
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;


    /**
     * The mapping of route name to the route config
     *
     * @var array<string,array>
     */
    private $routes = [];


    /**
     * Mapping route name node -> parent route name
     *
     * @var array<string,string>
     */
    private $parents = [];


    /**
     * @var array
     */
    private $nodes = [];


    /**
     * @param NodeFactory             $nodeFactory
     * @param RouteCollection|Route[] $routeCollection
     * @throws InvalidRouteTreeException
     */
    public function __construct (NodeFactory $nodeFactory, iterable $routeCollection)
    {
        $this->nodeFactory = $nodeFactory;

        // first: fetch all route config of all relevant routes
        $this->routes = $this->buildRouteIndex($routeCollection);

        // second: create all nodes for all relevant routes
        $this->nodes = $this->buildNodeIndex($routeCollection);

        // then: link hierarchy
        $this->linkHierarchy($this->parents, $this->nodes);

        // sort node children recursively
        $priorityProcessor = new PriorityProcessor();
        $this->nodes = $priorityProcessor->sortNodes($this->nodes);

        // fetch missing parameters from the hierarchy
        $parameterProcessor = new ParameterProcessor($this->routes);
        $this->nodes = $parameterProcessor->calculateAllParameters($this->nodes);
    }


    // region Route Index
    /**
     * @param RouteCollection|Route[] $routeCollection
     * @returns array
     * @throws InvalidRouteTreeException
     */
    private function buildRouteIndex (iterable $routeCollection) : array
    {
        // generate a list of all route names and map it to whether the route is relevant
        $routeIndex = [];

        foreach ($routeCollection as $name => $route)
        {
            $routeIndex[$name] = false;
        }

        $treeConfig = [];

        // mark all relevant routes
        foreach ($routeCollection as $name => $route)
        {
            $config = $this->getRouteConfig($route);

            // no tree data found -> skip
            if ([] === $config)
            {
                continue;
            }

            // fetch parent and unset from config
            $parent = $config["parent"] ?? null;
            unset($config["parent"]);

            // has tree data -> is relevant
            // add it directly to the relevant parents, as we already have the config
            $treeConfig[$name] = $config;
            $routeIndex[$name] = true;

            if (null !== $parent)
            {
                if (!isset($routeIndex[$parent]))
                {
                    throw new InvalidRouteTreeException(sprintf(
                        "Route '%s' references a parent '%s', but the parent route could not be found.",
                        $name,
                        $parent
                    ));
                }

                $this->parents[$name] = $parent;
                $routeIndex[$parent] = true;
            }
        }

        // fetch config for all relevant parents, that aren't yet loaded
        foreach ($routeCollection as $name => $route)
        {
            if (!$routeIndex[$name] || isset($treeConfig[$name]))
            {
                continue;
            }

            $treeConfig[$name] = $this->getRouteConfig($route);
        }

        return $treeConfig;
    }


    /**
     * Returns the route config for the given node
     *
     * @param Route $route
     * @return array
     */
    private function getRouteConfig (Route $route) : array
    {
        $option = $route->getOption("tree");

        // a string-only config should default to just setting the parent
        if (\is_string($option))
        {
            return ["parent" => $option];
        }

        return \is_array($option) ? $option : [];
    }
    // endregion


    // region Node Generation
    /**
     * @param RouteCollection|Route[] $routeCollection
     */
    private function buildNodeIndex (iterable $routeCollection) : array
    {
        $index = [];

        foreach ($routeCollection as $name => $route)
        {
            if (!isset($this->routes[$name]))
            {
                continue;
            }

            $config = $this->routes[$name];
            $config["parameters"] = \array_replace(
                $route->getDefaults(),
                $config["parameters"] ?? []
            );

            $index[$name] = $this->nodeFactory->createNode(
                $name,
                $config,
                $route->compile()->getVariables(),
                $route->getDefault("_controller")
            );
        }

        return $index;
    }
    // endregion


    // region Hierarchy Linking
    /**
     * Links the node hierarchy
     *
     * @param array<string,string> $mapping
     * @param Node[] $nodes
     */
    public function linkHierarchy (array $mapping, array $nodes) : void
    {
        foreach ($mapping as $childRoute => $parentRoute)
        {
            $childNode = $nodes[$childRoute];
            $parentNode = $nodes[$parentRoute];

            $childNode->setParent($parentNode);
            $parentNode->addChild($childNode);
        }
    }
    // endregion


    /**
     * Returns all nodes
     *
     * @return Node[]
     */
    public function getNodes () : array
    {
        return $this->nodes;
    }
}
