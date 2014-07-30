<?php

namespace Becklyn\PageTreeBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Becklyn\PageTreeBundle\Entity\PageTreeNode;
use Becklyn\PageTreeBundle\Model\PageTreeModel;

class PageTreeMenuBuilder
{
    /**
     * @var \Knp\Menu\FactoryInterface
     */
    private $factory;


    /**
     * @var PageTreeModel
     */
    private $pageTreeModel;



    /**
     * @param FactoryInterface $factory
     * @param PageTreeModel $pageTreeModel
     */
    public function __construct(FactoryInterface $factory, PageTreeModel $pageTreeModel)
    {
        $this->factory       = $factory;
        $this->pageTreeModel = $pageTreeModel;
    }



    /**
     * Builds the menu from a given route
     *
     * @param null|string $fromRoute
     *
     * @return ItemInterface
     */
    public function buildMenu ($fromRoute = null)
    {
        $root = $this->factory->createItem("root");

        // prepare menu for bootstrap
        $this->appendNodes($root, $this->pageTreeModel->getPageTree($fromRoute));

        return $root;
    }



    /**
     * Appends the node tree to the given parent
     *
     * @param ItemInterface $parent
     * @param PageTreeNode[] $nodes
     */
    private function appendNodes (ItemInterface $parent, array $nodes)
    {
        foreach ($nodes as $node)
        {
            $child = $parent->addChild($node->getDisplayTitle(), [
                "route" => $node->getRoute(),
                "routeParameters" => $node->getFakeParameters()
            ]);

            if ($node->isHidden())
            {
                $child->setAttribute("style", "display:none");
            }

            $child->setExtra("pageTree:hidden", $node->isHidden());

            $this->appendNodes($child, $node->getChildren());
        }
    }
}
