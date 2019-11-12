<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Twig;

use Becklyn\Menu\Renderer\MenuRenderer;
use Becklyn\RouteTreeBundle\Exception\InvalidParameterValueException;
use Becklyn\RouteTreeBundle\Menu\MenuBuilder;
use Becklyn\RouteTreeBundle\Parameter\ParametersMerger;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Defines all twig extensions used in this bundle.
 */
class RouteTreeTwigExtension extends AbstractExtension
{
    /**
     * @var MenuBuilder
     */
    private $menuBuilder;


    /**
     * @var MenuRenderer
     */
    private $menuRenderer;


    /**
     * @var ParametersMerger
     */
    private $parameterMerger;


    /**
     * @param MenuBuilder      $menuBuilder
     * @param MenuRenderer     $menuRenderer
     * @param ParametersMerger $parameterMerger
     */
    public function __construct (MenuBuilder $menuBuilder, MenuRenderer $menuRenderer, ParametersMerger $parameterMerger)
    {
        $this->menuBuilder = $menuBuilder;
        $this->menuRenderer = $menuRenderer;
        $this->parameterMerger = $parameterMerger;
    }


    /**
     * Renders the tree.
     *
     * @param string $fromRoute
     * @param array  $renderOptions
     * @param array  $renderOptions   the options for rendering
     * @param array  $routeParameters the route-specific parameters to use when resolving the parameters
     *
     * @return string
     */
    public function renderTree (string $fromRoute, array $renderOptions = [], array $parameters = [], array $routeParameters = []) : string
    {
        $root = $this->menuBuilder->build($fromRoute);

        if (isset($renderOptions["rootClass"]))
        {
            $root->addChildListClass($renderOptions["rootClass"]);
            unset($renderOptions["rootClass"]);
        }

        $this->parameterMerger->mergeParameters($root, $parameters, $routeParameters);
        return $this->menuRenderer->render($root, $renderOptions);
    }


    /**
     * Builds and renders a breadcrumb.
     *
     * @param string $fromRoute       the route to start the rendering from
     * @param array  $parameters      the global parameters to use when resolving the parameters
     * @param array  $renderOptions   the options for rendering
     * @param array  $routeParameters the route-specific parameters to use when resolving the parameters
     *
     * @throws InvalidParameterValueException
     *
     * @return string
     */
    public function renderBreadcrumb (string $fromRoute, array $renderOptions = [], array $parameters = [], array $routeParameters = []) : string
    {
        $root = $this->menuBuilder->buildBreadcrumb($fromRoute);

        if (isset($renderOptions["rootClass"]))
        {
            $root->addChildListClass($renderOptions["rootClass"]);
            unset($renderOptions["rootClass"]);
        }

        $this->parameterMerger->mergeParameters($root, $parameters, $routeParameters);
        return $this->menuRenderer->render($root, $renderOptions);
    }


    /**
     * {@inheritdoc}
     */
    public function getFunctions () : array
    {
        $safe = ["is_safe" => ["html"]];

        return [
            new TwigFunction("route_tree_breadcrumb", [$this, "renderBreadcrumb"], $safe),
            new TwigFunction("route_tree_render", [$this, "renderTree"], $safe),
        ];
    }
}
