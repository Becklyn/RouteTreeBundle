<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Twig;

use Becklyn\Menu\Renderer\MenuRenderer;
use Becklyn\RouteTreeBundle\Menu\MenuBuilder;
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
     * @param MenuBuilder  $menuBuilder
     * @param MenuRenderer $menuRenderer
     */
    public function __construct (MenuBuilder $menuBuilder, MenuRenderer $menuRenderer)
    {
        $this->menuBuilder = $menuBuilder;
        $this->menuRenderer = $menuRenderer;
    }


    /**
     * @param string $fromRoute
     * @param array  $options
     *
     * @return string
     */
    public function renderRouteTree (string $fromRoute, array $options)
    {
        $root = $this->menuBuilder->build($fromRoute);
        $root->addChildListClass("menu-main");

        return $this->menuRenderer->render(
            $root,
            $options
        );
    }


    /**
     * {@inheritdoc}
     */
    public function getFunctions ()
    {
        return [
            new TwigFunction("route_tree_breadcrumb", [$this->menuBuilder, "buildBreadcrumb"]),
            new TwigFunction("route_tree_menu", [$this->menuBuilder, "buildMenu"]),
            new TwigFunction("route_tree_render", [$this, "renderRouteTree"], ["is_safe" => ["html"]]),
        ];
    }
}
