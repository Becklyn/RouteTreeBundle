<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Twig;

use Becklyn\RouteTreeBundle\KnpMenu\MenuBuilder;
use Knp\Menu\ItemInterface;
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
     * @param string $root
     *
     * @return ItemInterface
     */
    public function getRouteTreeMenu ($root)
    {
        return $this->menuBuilder->buildMenu($root);
    }


    /**
     * {@inheritdoc}
     */
    public function getFunctions ()
    {
        return [
            new TwigFunction("getRouteTreeMenu", [$this, "getRouteTreeMenu"]),
        ];
    }
}
