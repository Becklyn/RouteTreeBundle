<?php

namespace Becklyn\RouteTreeBundle\Twig;

use Becklyn\RouteTreeBundle\KnpMenu\MenuBuilder;
use Knp\Menu\ItemInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Knp\Menu\Renderer\RendererProviderInterface;
use Knp\Menu\Twig\Helper;


/**
 * Defines all twig extensions, used in this bundle
 *
 * @package Becklyn\RouteTreeBundle\Service
 */
class RouteTreeTwigExtension extends \Twig_Extension
{
    /**
     * @var MenuProviderInterface
     */
    private $menuProvider;


    /**
     * @var RendererProviderInterface
     */
    private $rendererProvider;


    /**
     * @var MenuBuilder
     */
    private $menuBuilder;



    /**
     * @param MenuProviderInterface     $menuProvider
     * @param RendererProviderInterface $rendererProvider
     * @param MenuBuilder               $menuBuilder
     */
    public function __construct (MenuProviderInterface $menuProvider, RendererProviderInterface $rendererProvider, MenuBuilder $menuBuilder)
    {
        $this->menuProvider = $menuProvider;
        $this->rendererProvider = $rendererProvider;
        $this->menuBuilder = $menuBuilder;
    }



    /**
     * Renders a bootstrap conform tree menu
     *
     * @param ItemInterface $menu
     * @param array $options
     *
     * @return string
     */
    public function routeTreeBootstrapMenu ($menu, array $options = [])
    {
        // Set default values
        $options = array_merge([
            "template"      => "@BecklynRouteTree/Menu/bootstrap.html.twig",
            "currentClass"  => "active",
            "ancestorClass" => "active",
            "hoverDropdown" => true,
            "listClass"     => "navbar-nav"
        ], $options);

        // force twig renderer, because we only provide a twig template
        $helper = new Helper($this->rendererProvider, $this->menuProvider);
        return $helper->render($menu, $options, "twig");
    }



    /**
     * Specifies, whether the children of an item are displayed when rendering the menu
     *
     * @param ItemInterface $item
     *
     * @return bool
     */
    public function hasRouteTreeChildren (ItemInterface $item)
    {
        if (!$item->isDisplayed())
        {
            return false;
        }

        foreach ($item->getChildren() as $child)
        {
            if ($child->isDisplayed())
            {
                return true;
            }
        }

        return false;
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
        return array(
            new \Twig_SimpleFunction("routeTreeBootstrapMenu", [$this, "routeTreeBootstrapMenu"], ["is_safe" => ["html"]]),
            new \Twig_SimpleFunction("hasRouteTreeChildren",  [$this, "hasRouteTreeChildren"]),
            new \Twig_SimpleFunction("getRouteTreeMenu", [$this, "getRouteTreeMenu"]),
        );
    }



    /**
     * {@inheritdoc}
     */
    public function getName ()
    {
        return __CLASS__;
    }
}
