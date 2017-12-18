<?php

namespace Becklyn\RouteTreeBundle\Tree;

use Becklyn\RouteTreeBundle\Exception\InvalidNodeDataException;


/**
 * Represents a node in the route tree
 */
class Node
{
    /**
     * @var array
     */
    private static $allowedSeparatorValues = ["before", "after", null];


    //region Fields
    /**
     * @var string
     */
    private $route;


    /**
     * The title to display in the tree
     *
     * @var string|null
     */
    private $title = null;


    /**
     * Flag, whether the element should be rendered
     *
     * @var bool
     */
    private $hidden;


    /**
     * Identifier where to put a separator.
     * Possible values: "before", "after", null (= no separator)
     *
     * @var string|null
     */
    private $separator = null;


    /**
     * The parameters which were set directly on the node itself.
     * Array with names as key and parameter values as values
     *
     * $parameters = [
     *     "name" => "value",
     * ]
     *
     * @var string[]
     */
    private $parameters = [];


    /**
     * The parameters with the inherited parameters.
     * Array with names as key and parameter values as values
     *
     * $parameters = [
     *     "name" => "value",
     * ]
     *
     * @var string[]
     */
    private $mergedParameters = [];


    /**
     * Security restrictions.
     * Supports the same values as the security annotation:
     * @link http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/security.html
     *
     * @var string|null
     */
    private $security;


    /**
     * The route name of the parent route
     *
     * @var string|null
     */
    private $parentRoute = null;


    /**
     * The route name of the parent route
     *
     * @var Node|null
     */
    private $parent;


    /**
     * The child nodes
     *
     * @var Node[]
     */
    private $children = [];


    /**
     * The extra data
     *
     * @var array
     */
    private $extra = [];
    //endregion



    /**
     * @param string $route
     */
    public function __construct ($route)
    {
        $this->route = $route;
    }



    //region Accessors
    /**
     * @return string
     */
    public function getRoute ()
    {
        return $this->route;
    }



    /**
     * @return null|string
     */
    public function getTitle ()
    {
        return $this->title;
    }



    /**
     * @param null|string $title
     */
    public function setTitle ($title)
    {
        $this->title = null !== $title
            ? (string) $title
            : null;
    }



    /**
     * @return boolean
     */
    public function isHidden ()
    {
        return $this->hidden;
    }



    /**
     * @param boolean $hidden
     */
    public function setHidden ($hidden)
    {
        $this->hidden = (bool) $hidden;
    }



    /**
     * @return null|string
     */
    public function getSeparator ()
    {
        return $this->separator;
    }



    /**
     * @param null|string $separator
     *
     * @throws InvalidNodeDataException
     */
    public function setSeparator ($separator)
    {
        if (!in_array($separator, self::$allowedSeparatorValues, true))
        {
            throw new InvalidNodeDataException(sprintf(
                "Invalid 'separator' value. Allowed are the values %s, but '%s' was given.",
                var_export(self::$allowedSeparatorValues, true),
                $separator
            ));
        }

        $this->separator = $separator;
    }


    /**
     * @return string[]
     */
    public function getParameters ()
    {
        return $this->parameters;
    }



    /**
     * @param string[] $parameters
     */
    public function setParameters (array $parameters)
    {
        $this->parameters = $parameters;

        // refresh merged parameters
        $this->setMergedParameters($parameters);
    }



    /**
     * @return null|string
     */
    public function getSecurity ()
    {
        return $this->security;
    }



    /**
     * @param null|string $security
     */
    public function setSecurity ($security)
    {
        $this->security = null !== $security
            ? (string) $security
            : null;
    }



    /**
     * @return Node|null
     */
    public function getParent ()
    {
        return $this->parent;
    }



    /**
     * @param Node|null $parent
     */
    public function setParent (Node $parent = null)
    {
        $this->parent = $parent;
    }



    /**
     * @return Node[]
     */
    public function getChildren ()
    {
        return $this->children;
    }



    /**
     * Returns the parent route
     *
     * @return null|string
     */
    public function getParentRoute ()
    {
        $parent = $this->getParent();

        return null !== $parent
            ? $parent->getRoute()
            : $this->parentRoute;
    }



    /**
     * @param null|string $parentRoute
     */
    public function setParentRoute ($parentRoute)
    {
        $this->parentRoute = null !== $parentRoute
            ? (string) $parentRoute
            : null;
    }



    /**
     * @return string[]
     */
    public function getMergedParameters ()
    {
        return $this->mergedParameters;
    }



    /**
     * @param string[] $mergedParameters
     */
    public function setMergedParameters ($mergedParameters)
    {
        $this->mergedParameters = $mergedParameters;
    }
    //endregion


    /**
     * Adds a node as child node
     *
     * @param Node $node
     */
    public function addChild (Node $node)
    {
        $node->setParent($this);
        $this->children[] = $node;
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
     * @return array
     */
    public function getExtra ($key, $defaultValue = null) : array
    {
        return $this->extra[$key] ?? $defaultValue;
    }


    /**
     * @return array
     */
    public function getExtras () : array
    {
        return $this->extra;
    }


    /**
     * @param array $extra
     */
    public function setExtra (array $extra) : void
    {
        $this->extra = $extra;
    }
}
