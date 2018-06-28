<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Twig;

use Becklyn\RouteTreeBundle\KnpMenu\MenuBuilder;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;


/**
 * Defines all twig extensions used in this bundle
 */
class RouteTreeTwigExtension extends AbstractExtension
{
    /**
     * @var MenuBuilder
     */
    private $menuBuilder;


    /**
     * @param MenuBuilder $menuBuilder
     */
    public function __construct (MenuBuilder $menuBuilder)
    {
        $this->menuBuilder = $menuBuilder;
    }


    /**
     * {@inheritdoc}
     */
    public function getFunctions ()
    {
        return [
            new TwigFunction("route_tree_breadcrumb", [$this->menuBuilder, "buildBreadcrumb"]),
            new TwigFunction("route_tree_menu", [$this->menuBuilder, "buildMenu"]),
        ];
    }
}
