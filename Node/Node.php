<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Node;


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
     * The names of the parameters, that are required (as defined in the route)
     *
     * @var array
     */
    private $requiredParameters = [];

    /**
     * The defined parameter values, found anywhere in the (static) config
     *
     * $parameters = [
     *     "name" => "value",
     * ]
     *
     * @var string[]
     */
    private $parameterValues = [];


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
    private $extras = [];


    /**
     * @var int
     */
    private $priority = 0;
    //endregion


    /**
     * @param string $route
     * @param array  $requiredParameters
     */
    public function __construct (string $route, array $requiredParameters)
    {
        $this->route = $route;
        $this->requiredParameters = $requiredParameters;
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
        // the node is automatically hidden if no title is set
        return $this->hidden || null === $this->title;
    }



    /**
     * @param boolean $hidden
     */
    public function setHidden (bool $hidden) : void
    {
        $this->hidden = $hidden;
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
     * @return array
     */
    public function getParameterValues () : array
    {
        return $this->parameterValues;
    }


    /**
     * Updates the parameters and takes all parameters, that are in the required parameters
     *
     * @param array $parameterValues
     */
    public function updateParameterValues (array $parameterValues, bool $overwriteExisting = false) : void
    {
        foreach ($this->requiredParameters as $parameterName)
        {
            // there is an existing parameter and we should not overwrite existing ones
            if (!$overwriteExisting && \array_key_exists($parameterName, $this->parameterValues))
            {
                continue;
            }

            if (\array_key_exists($parameterName, $parameterValues))
            {
                $this->parameterValues[$parameterName] = $parameterValues[$parameterName];
            }
        }
    }


    /**
     * @return array
     */
    public function getExtras () : array
    {
        return $this->extras;
    }


    /**
     * @param string $key
     * @param        $value
     */
    public function setExtra (string $key, $value) : void
    {
        $this->extras[$key] = $value;
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
