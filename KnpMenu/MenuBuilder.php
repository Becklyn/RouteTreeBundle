<?php

namespace Becklyn\RouteTreeBundle\KnpMenu;

use Becklyn\RouteTreeBundle\Tree\Node;
use Becklyn\RouteTreeBundle\Tree\RouteTree;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;


/**
 *
 */
class MenuBuilder
{
    /**
     * @var \Knp\Menu\FactoryInterface
     */
    private $factory;

    /**
     * @var RouteTree
     */
    private $routeTree;



    /**
     * @param FactoryInterface $factory
     * @param RouteTree        $routeTree
     */
    public function __construct (FactoryInterface $factory, RouteTree $routeTree)
    {
        $this->factory = $factory;
        $this->routeTree = $routeTree;
    }



    /**
     * Builds the menu from a given route
     *
     * @param string $fromRoute
     *
     * @return ItemInterface
     */
    public function buildMenu ($fromRoute)
    {
        $menuRoot = $this->factory->createItem("root");
        $rootNode = $this->routeTree->getNode($fromRoute);

        if (null !== $rootNode && !$rootNode->isHidden())
        {
            $this->appendNodes($menuRoot, $rootNode->getChildren());
        }

        return $menuRoot;
    }



    /**
     * Appends the node tree to the given parent
     *
     * @param ItemInterface $parent
     * @param Node[] $nodes
     */
    private function appendNodes (ItemInterface $parent, array $nodes)
    {
        foreach ($nodes as $node)
        {
            if ($node->isHidden())
            {
                return;
            }

            $routeParameters = $node->getParameters();

            $child = $parent->addChild($node->getDisplayTitle(), [
                "route" => $node->getRoute(),
                "routeParameters" => $routeParameters,
            ]);

            $child->setExtra("routeTree:separator", $node->getSeparator());

            $this->appendNodes($child, $node->getChildren());
        }
    }
}