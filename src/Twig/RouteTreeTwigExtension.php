<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Twig;

use Becklyn\RouteTreeBundle\KnpMenu\MenuBuilder;
use Becklyn\RouteTreeBundle\KnpMenu\Renderer\SimpleTwigRenderer;
use Knp\Menu\Twig\Helper;
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
     * @var Helper
     */
    private $knpHelper;


    /**
     * @param MenuBuilder $menuBuilder
     * @param Helper      $knpHelper
     */
    public function __construct (MenuBuilder $menuBuilder, Helper $knpHelper)
    {
        $this->menuBuilder = $menuBuilder;
        $this->knpHelper = $knpHelper;
    }


    /**
     * @param string $fromRoute
     * @param array  $options
     *
     * @return string
     */
    public function renderRouteTree (string $fromRoute, array $options)
    {
        $menu = $this->menuBuilder->buildMenu($fromRoute);
        return $this->knpHelper->render($menu, $options, SimpleTwigRenderer::ALIAS);
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
