<?php

namespace Becklyn\PageTreeBundle\Model\PageTree;

/**
 * Represents a node in the page tree
 *
 * @package Becklyn\PageTreeBundle\Model\PageTree
 */
class Node
{
    /**
     * @var string
     */
    private $route;


    /**
     * The parameter names
     *
     * @var string[]
     */
    private $fakeParameters;


    /**
     * The route name of the parent route
     *
     * @var string
     */
    private $parent;


    /**
     * @var Node[]
     */
    private $children = [];


    /**
     * The page title to display in the page tree
     *
     * @var string
     */
    private $title;



    /**
     * @param $route
     * @param string[] $fakeParameters
     * @param string|null $parent
     * @param string|null $title
     */
    public function __construct ($route, array $fakeParameters = array(), $parent, $title)
    {
        $this->route          = $route;
        $this->fakeParameters = $fakeParameters;
        $this->parent         = $parent;
        $this->title          = $title;
    }



    public function addChild (Node $node)
    {
        $this->children[] = $node;
    }



    /**
     * @return Node[]
     */
    public function getChildren ()
    {
        return $this->children;
    }



    /**
     * @return string[]
     */
    public function getFakeParameters ()
    {
        return $this->fakeParameters;
    }



    /**
     * @return mixed
     */
    public function getRoute ()
    {
        return $this->route;
    }



    /**
     * Returns, whether the node is a root node
     *
     * @return bool
     */
    public function isRootNode ()
    {
        return is_null($this->getParent());
    }



    /**
     * @return string
     */
    public function getParent ()
    {
        return $this->parent;
    }



    /**
     * @return string
     */
    public function getTitle ()
    {
        return $this->title;
    }



    /**
     * Returns the display title
     *
     * @return string
     */
    public function getDisplayTitle ()
    {
        return $this->getTitle() ?: $this->getRoute();
    }
}