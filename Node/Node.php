<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Node;

use Becklyn\RouteTreeBundle\Exception\InvalidNodeDataException;


/**
 * Represents a node in the route tree
 */
class Node
{
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
    private $hidden = false;


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
    private $security = null;


    /**
     * The route name of the parent route
     *
     * @var Node|null
     */
    private $parent = null;


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


    /**
     * @var int
     */
    private $priority = 0;
    //endregion



    /**
     * @param string $route
     */
    public function __construct (string $route)
    {
        $this->route = $route;
    }



    //region Accessors
    /**
     * @return string
     */
    public function getRoute () : string
    {
        return $this->route;
    }



    /**
     * @return null|string
     */
    public function getTitle () : ?string
    {
        return $this->title;
    }



    /**
     * @param null|string $title
     */
    public function setTitle (?string $title) : void
    {
        $this->title = $title;
    }



    /**
     * @return boolean
     */
    public function isHidden () : bool
    {
        return $this->hidden;
    }



    /**
     * @param boolean $hidden
     */
    public function setHidden (bool $hidden) : void
    {
        $this->hidden = $hidden;
    }


    /**
     * @return string[]
     */
    public function getParameters () : array
    {
        return $this->parameters;
    }



    /**
     * @param string[] $parameters
     */
    public function setParameters (array $parameters) : void
    {
        $this->parameters = $parameters;

        // refresh merged parameters
        $this->setMergedParameters($parameters);
    }



    /**
     * @return null|string
     */
    public function getSecurity () : ?string
    {
        return $this->security;
    }



    /**
     * @param null|string $security
     */
    public function setSecurity (?string $security) : void
    {
        $this->security = $security;
    }



    /**
     * @return Node|null
     */
    public function getParent () : ?Node
    {
        return $this->parent;
    }



    /**
     * @param Node|null $parent
     */
    public function setParent (?Node $parent) : void
    {
        $this->parent = $parent;
    }



    /**
     * @return Node[]
     */
    public function getChildren () : array
    {
        return $this->children;
    }


    /**
     * @param Node[] $children
     */
    public function setChildren (array $children) : void
    {
        $this->children = $children;
    }


    /**
     * @return string[]
     */
    public function getMergedParameters () : array
    {
        return $this->mergedParameters;
    }



    /**
     * @param string[] $mergedParameters
     */
    public function setMergedParameters (array $mergedParameters) : void
    {
        $this->mergedParameters = $mergedParameters;
    }


    /**
     * @return array
     */
    public function getExtra () : array
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


    /**
     * @return int
     */
    public function getPriority () : int
    {
        return $this->priority;
    }


    /**
     * @param int $priority
     */
    public function setPriority (int $priority) : void
    {
        $this->priority = $priority;
    }
    // endregion


    /**
     * Adds a node as child node
     *
     * @param Node $node
     */
    public function addChild (Node $node) : void
    {
        $this->children[] = $node;
    }



    /**
     * Returns the display title
     *
     * @return string
     */
    public function getDisplayTitle () : string
    {
        return $this->getTitle() ?: $this->getRoute();
    }
}
