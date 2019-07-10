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
    public function renderTree (string $fromRoute, array $options = [])
    {
        $root = $this->menuBuilder->build($fromRoute);

        if (isset($options["rootClass"]))
        {
            $root->addChildListClass($options["rootClass"]);
            unset($options["rootClass"]);
        }

        return $this->menuRenderer->render($root, $options);
    }


    /**
     * @param string $fromRoute
     * @param array  $options
     *
     * @return string
     */
    public function renderBreadcrumb (string $fromRoute, array $options = [])
    {
        $root = $this->menuBuilder->buildBreadcrumb($fromRoute);

        if (isset($options["rootClass"]))
        {
            $root->addChildListClass($options["rootClass"]);
            unset($options["rootClass"]);
        }

        return $this->menuRenderer->render($root, $options);
    }


    /**
     * {@inheritdoc}
     */
    public function getFunctions ()
    {
        $safe = ["is_safe" => ["html"]];

        return [
            new TwigFunction("route_tree_breadcrumb", [$this, "renderBreadcrumb"], $safe),
            new TwigFunction("route_tree_render", [$this, "renderTree"], $safe),
        ];
    }
}
