<?php

namespace Becklyn\PageTreeBundle\Model;

use Becklyn\PageTreeBundle\Entity\PageTreeNode;
use Becklyn\PageTreeBundle\Model\PageTree\InvalidNodeException;
use Becklyn\PageTreeBundle\Model\PageTree\InvalidPageTreeException;
use Becklyn\PageTreeBundle\Service\PlaceholderParameterGenerator;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

class PageTreeModel
{
    /**
     * The page tree elements
     *
     * @var PageTreeNode[]
     */
    private $pageTree;


    /**
     * The direct access to the nodes in the page tree
     *
     * @var PageTreeNode[]
     */
    private $directAccess;


    /**
     * @var PlaceholderParameterGenerator
     */
    private $placeholderParameterGenerator;



    /**
     * @param RouterInterface $router
     * @param \Becklyn\PageTreeBundle\Service\PlaceholderParameterGenerator $placeholderParameterGenerator
     */
    public function __construct (RouterInterface $router, PlaceholderParameterGenerator $placeholderParameterGenerator)
    {
        $this->placeholderParameterGenerator = $placeholderParameterGenerator;

        $this->buildPageTree($router);
    }



    /**
     * Builds the pagetree
     *
     * @param RouterInterface $router
     *
     * @throws PageTree\InvalidPageTreeException
     */
    private function buildPageTree (RouterInterface $router)
    {
        $this->pageTree = [];
        $this->directAccess = [];

        // collect all routes, which are configured to be in the page tree
        foreach ($router->getRouteCollection() as $routeName => $route)
        {
            /** @var Route $route */
            $node = $this->transformRouteToNode($routeName, $route);

            if (!is_null($node))
            {
                $this->directAccess[ $routeName ] = $node;
            }
        }

        // apply correct nesting of routes
        foreach ($this->directAccess as $node)
        {
            if ($node->isRootNode())
            {
                $this->pageTree[] = $node;
            }
            else if (array_key_exists($node->getParent(), $this->directAccess))
            {
                $this->directAccess[ $node->getParent() ]->addChild($node);
            }
            else
            {
                throw new InvalidPageTreeException("Invalid pagetree at route „{$node->getRoute()}“: parent '{$node->getParent()}' requested, but route was not found. Did you forget to define the route „{$node->getRoute()}“ as root?");
            }
        }
    }



    /**
     * Transforms a route to a node.
     * Returns null, if the route should not be in the page tree
     *
     * @param string $routeName
     * @param Route $route
     *
     * @return null|PageTreeNode
     * @throws PageTree\InvalidNodeException
     */
    private function transformRouteToNode ($routeName, Route $route)
    {
        try {
            return $this->createNodeFromRoute($routeName, $route);
        }
        catch (\InvalidArgumentException $e)
        {
            throw new InvalidNodeException($e->getMessage(), 0, $e);
        }
    }



    /**
     * Creates a pagetree node from a given route
     *
     * @param string $routeName
     * @param Route $route
     *
     * @throws InvalidNodeException
     * @return PageTreeNode|null
     */
    private function createNodeFromRoute ($routeName, Route $route)
    {
        $routePageTreeData = $route->getOption("page_tree");

        // if there is no pagetree data
        if (!is_array($routePageTreeData))
        {
            return null;
        }

        if (isset($routePageTreeData["is_root"]) && $routePageTreeData["is_root"])
        {
            $parent = null;
        }
        else if (isset($routePageTreeData["parent"]))
        {
            $parent = $routePageTreeData["parent"];
        }
        else
        {
            throw new InvalidNodeException("Node {$routeName} needs to either have a parent or be a root node.");
        }

        $title               = isset($routePageTreeData["title"])      ? (string) $routePageTreeData["title"]     : null;
        $isHidden            = isset($routePageTreeData["hidden"])     ? (bool) $routePageTreeData["hidden"]      : false;
        $separator           = isset($routePageTreeData["separator"])  ? (string) $routePageTreeData["separator"] : null;
        $fakeParameterValues = isset($routePageTreeData["parameters"]) ? (array) $routePageTreeData["parameters"] : array();
        $fakeParameters      = $this->placeholderParameterGenerator->prepareFakeParameters($route->compile()->getPathVariables(), $fakeParameterValues);
        $sortOrder           = isset($routePageTreeData["sortOrder"])  ? (integer) $routePageTreeData["sortOrder"] : null;

        return new PageTreeNode($routeName, $fakeParameters, $parent, $title, $isHidden, $separator, $sortOrder);
    }




    /**
     * Returns the page tree
     *
     * @param null|string $fromRoute
     *
     * @return Pagetree\PageTreeNode[]
     */
    public function getPageTree ($fromRoute = null)
    {
        if (is_null($fromRoute))
        {
            return $this->pageTree;
        }
        else if (array_key_exists($fromRoute, $this->directAccess))
        {
            return $this->directAccess[$fromRoute]->getChildren();
        }

        return [];
    }
}
