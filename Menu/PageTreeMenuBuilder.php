<?php

namespace Becklyn\PageTreeBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Becklyn\PageTreeBundle\Entity\PageTreeNode;
use Becklyn\PageTreeBundle\Model\PageTreeModel;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;



    /**
     * @param FactoryInterface $factory
     * @param PageTreeModel $pageTreeModel
     */
    public function __construct(FactoryInterface $factory, PageTreeModel $pageTreeModel, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->factory       = $factory;
        $this->pageTreeModel = $pageTreeModel;
        $this->authorizationChecker = $authorizationChecker;
    }



    /**
     * Builds the menu from a given route
     *
     * @param null|string $fromRoute
     * @param array $options
     *
     * @return ItemInterface
     */
    public function buildMenu ($fromRoute = null, array $options = [])
    {
        $root = $this->factory->createItem("root");

        // prepare menu for bootstrap
        $this->appendNodes($root, $this->pageTreeModel->getPageTree($fromRoute), $options);

        return $root;
    }



    /**
     * Appends the node tree to the given parent
     *
     * @param ItemInterface $parent
     * @param PageTreeNode[] $nodes
     * @param array $options
     */
    private function appendNodes (ItemInterface $parent, array $nodes, array $options = [])
    {
        foreach ($nodes as $node)
        {
            $routeParameters = [];

            foreach ($node->getFakeParameters() as $parameter => $value)
            {
                if (isset($options["routeParameters"][$parameter]))
                {
                    $routeParameters[$parameter] = $options["routeParameters"][$parameter];
                }
                else
                {
                    $routeParameters[$parameter] = $value;
                }
            }

            $child = $parent->addChild($node->getDisplayTitle(), [
                "route" => $node->getRoute(),
                "routeParameters" => $routeParameters,
            ]);

            $nodeHidden = $node->isHidden() || !$this->isAuthorized($node);

            if ($nodeHidden)
            {
                $child->setAttribute("style", "display:none");
            }

            $child->setExtra("pageTree:hidden", $nodeHidden);
            $child->setExtra("pageTree:separator", $node->getSeparator());

            $this->appendNodes($child, $node->getChildren(), $options);
        }
    }

    /**
     * Checks wheter the logged in User is authorized to see the PageTreeNode
     *
     * @param PageTreeNode $node
     * @return bool
     */
    private function isAuthorized(PageTreeNode $node)
    {
        if (empty($node->getRoles()))
        {
            return true;
        }

        foreach ($node->getRoles() as $role) {
            if ($this->authorizationChecker->isGranted($role)) {
                return true;
            }
        }

        return false;
    }
}
