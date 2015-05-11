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
     * sortorder of element
     *
     * @var integer
     */
    private $sortOrder;



    /**
     * @param string       $route
     * @param string[]     $fakeParameters
     * @param string|null  $parent
     * @param string|null  $title
     * @param bool         $hidden
     * @param string|null  $separator
     * @param integer|null $sortOrder
     */
    public function __construct ($route, array $fakeParameters = array(), $parent, $title, $hidden, $separator, $sortOrder)
    {
        $this->route          = $route;
        $this->fakeParameters = $fakeParameters;
        $this->parent         = $parent;
        $this->title          = $title;
        $this->hidden         = $hidden;
        $this->separator      = $separator;
        $this->sortOrder      = $sortOrder;
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
     * @return int
     */
    public function getSortOrder ()
    {
        return $this->sortOrder;
    }



    /**
     * sorts the PageTreeNode objects by the sortOrder
     *
     * @param $nodeA
     * @param $nodeB
     *
     * @return integer
     */
    static function sortNodes($nodeA, $nodeB)
    {
        $sortOrderA = strtolower($nodeA->sortOrder);
        $sortOrderB = strtolower($nodeB->sortOrder);

        if ($sortOrderA == $sortOrderB) {
            return 0;
        }
        return ($sortOrderA > $sortOrderB) ? +1 : -1;
    }
}
