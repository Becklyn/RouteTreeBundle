<?php

namespace Becklyn\PageTreeBundle\Model;

use Becklyn\PageTreeBundle\Model\PageTree\InvalidNodeException;
use Becklyn\PageTreeBundle\Model\PageTree\InvalidPageTreeException;
use Becklyn\PageTreeBundle\Model\PageTree\PageTreeNode;
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
     * @param RouterInterface $router
     */
    public function __construct (RouterInterface $router)
    {
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
                throw new InvalidPageTreeException("Invalid pagetree: parent '{$node->getParent()}' requested, but route was not found.");
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
            return PageTreeNode::createFromRoute($routeName, $route);
        }
        catch (\InvalidArgumentException $e)
        {
            throw new InvalidNodeException($e->getMessage(), 0, $e);
        }
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