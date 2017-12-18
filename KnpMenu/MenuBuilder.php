<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\KnpMenu;

use Becklyn\RouteTreeBundle\Node\Node;
use Becklyn\RouteTreeBundle\Tree\RouteTree;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;


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
    public function buildMenu (string $fromRoute) : ItemInterface
    {
        $menuRoot = $this->factory->createItem("root");
        $rootNode = $this->routeTree->getNode($fromRoute);

        if (null !== $rootNode)
        {
            $this->appendNodes($menuRoot, $rootNode->getChildren());
        }

        return $menuRoot;
    }


    /**
     * Appends the node tree to the given parent
     *
     * @param ItemInterface $parent
     * @param Node[]        $nodes
     */
    private function appendNodes (ItemInterface $parent, array $nodes) : void
    {
        foreach ($nodes as $node)
        {
            $child = $parent->addChild($node->getDisplayTitle(), [
                "route" => $node->getRoute(),
                "routeParameters" => $node->getParameters(),
            ]);

            if ($node->isHidden())
            {
                $child->setDisplay(false);
            }

            $child->setExtras($node->getExtra());
            $this->appendNodes($child, $node->getChildren());
        }
    }
}
