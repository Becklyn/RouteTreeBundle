<?php

namespace Becklyn\PageTreeBundle\Entity;


/**
 * Represents a node in the page tree
 *
 * @package Becklyn\PageTreeBundle\Model\PageTree
 */
class PageTreeNode
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
     * @var PageTreeNode[]
     */
    private $children = [];


    /**
     * The page title to display in the page tree
     *
     * @var string
     */
    private $title;


    /**
     * Identifier where to put a separator.
     * Possible values: "before", "after", null (= no separator)
     *
     * @var string|null
     */
    private $separator = null;


    /**
     * Flag, whether the element should be rendered
     *
     * @var bool
     */
    private $hidden;

    /**
     * Array of roles
     *
     * @var array
     */
    private $roles;



    /**
     * @param string      $route
     * @param string[]    $fakeParameters
     * @param string|null $parent
     * @param string|null $title
     * @param bool        $hidden
     * @param string|null $separator
     */
    public function __construct ($route, array $fakeParameters = array(), $parent, $title, $hidden, $separator, $roles = array())
    {
        $this->route          = $route;
        $this->fakeParameters = $fakeParameters;
        $this->parent         = $parent;
        $this->title          = $title;
        $this->hidden         = $hidden;
        $this->separator      = $separator;
        $this->roles          = $roles;
    }



    public function addChild (PageTreeNode $node)
    {
        $this->children[] = $node;
    }



    /**
     * @return PageTreeNode[]
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



    /**
     * Returns whether the node should be hidden when rendering
     *
     * @return boolean
     */
    public function isHidden ()
    {
        return $this->hidden;
    }



    /**
     * @return null|string
     */
    public function getSeparator ()
    {
        return $this->separator;
    }

    /**
     * @return null|string
     */
    public function getRoles ()
    {
        return $this->roles;
    }
}
