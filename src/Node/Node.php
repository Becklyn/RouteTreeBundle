<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Node;

/**
 * Represents a node in the route tree.
 */
class Node
{
    //region Fields
    /**
     * @var string
     */
    private $route;


    /**
     * The names of the parameters, that are required (as defined in the route).
     *
     * @var string[]
     */
    private $requiredParameters = [];


    /**
     * The title to display in the tree.
     *
     * @var string|null
     */
    private $title;


    /**
     * Flag, whether the element should be rendered.
     *
     * @var bool
     */
    private $hidden = false;

    /**
     * The defined parameter values, found anywhere in the (static) config.
     *
     * $parameters = [
     *     "name" => "value",
     * ]
     *
     * @var string[]
     */
    private $parameterValues = [];


    /**
     * The requirements as defined in the route.
     *
     * $requirements = [
     *     "parameter" => "\\d*",
     * ]
     *
     * @var string[]
     */
    private $requirements;


    /**
     * Security restrictions.
     * Supports the same values as the security annotation:.
     *
     * @see http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/security.html
     *
     * @var string|null
     */
    private $security;


    /**
     * The route name of the parent route.
     *
     * @var Node|null
     */
    private $parent;


    /**
     * The child nodes.
     *
     * @var Node[]
     */
    private $children = [];


    /**
     * The extra data.
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
     * @param string   $route
     * @param string[] $requiredParameters
     * @param array    $requirements
     */
    public function __construct (string $route, array $requiredParameters = [], array $requirements = [])
    {
        $this->route = $route;
        $this->requiredParameters = $requiredParameters;
        $this->requirements = $requirements;
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
     * @return string[]
     */
    public function getRequiredParameters () : array
    {
        return $this->requiredParameters;
    }


    /**
     * @return string|null
     */
    public function getTitle () : ?string
    {
        return $this->title;
    }



    /**
     * @param string|null $title
     */
    public function setTitle (?string $title) : void
    {
        $this->title = $title;
    }



    /**
     * @return bool
     */
    public function isHidden () : bool
    {
        // the node is automatically hidden if no title is set
        return $this->hidden || null === $this->title;
    }



    /**
     * @param bool $hidden
     */
    public function setHidden (bool $hidden) : void
    {
        $this->hidden = $hidden;
    }



    /**
     * @return string|null
     */
    public function getSecurity () : ?string
    {
        return $this->security;
    }



    /**
     * @param string|null $security
     */
    public function setSecurity (?string $security) : void
    {
        $this->security = $security;
    }



    /**
     * @return Node|null
     */
    public function getParent () : ?self
    {
        return $this->parent;
    }



    /**
     * @param Node|null $parent
     */
    public function setParent (?self $parent) : void
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
     * Updates the parameters and takes all parameters, that are in the required parameters.
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
     * Adds a node as child node.
     *
     * @param Node $node
     */
    public function addChild (self $node) : void
    {
        $this->children[] = $node;
    }



    /**
     * Returns the display title.
     *
     * @return string
     */
    public function getDisplayTitle () : string
    {
        return $this->getTitle() ?: $this->getRoute();
    }


    /**
     * Returns whether the given parameter value is valid for the parameter.
     *
     * @param string     $name
     * @param string|int $value
     *
     * @return bool
     */
    public function isValidParameterValue (string $name, $value) : bool
    {
        $value = (string) $value;
        $requirement = $this->requirements[$name] ?? null;

        // if there is no requirement, all values are ok.
        if (null === $requirement)
        {
            return true;
        }

        // try to match with `~` as separator
        $matched = @\preg_match("~^{$requirement}$~", $value);

        // a RegExp error occured, try again with another delimiter
        if (false === $matched)
        {
            // try another delimiter
            $matched = @\preg_match("%^{$requirement}$%", $value);
        }

        // Here there are several possible return values:
        //  - 1: one of the two regexes matched -> all is fine (= is valid)
        //  - 0: at least one compiled but didn't match (= is NOT valid)
        //  - false: didn't compile (= we can't say for sure, so assume valid and let it maybe fail later)
        return 0 !== $matched;
    }
}
