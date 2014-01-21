<?php

namespace Becklyn\PageTreeBundle\Service;

use Knp\Menu\ItemInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Knp\Menu\Renderer\RendererProviderInterface;
use Knp\Menu\Twig\Helper;

/**
 * Defines all twig extensions, used in this bundle
 *
 * @package Becklyn\PageTreeBundle\Service
 */
class PageTreeTwigExtension extends \Twig_Extension
{
    /**
     * @var RendererProviderInterface
     */
    private $rendererProvider;


    /**
     * @var MenuProviderInterface
     */
    private $menuProvider;



    /**
     * @param MenuProviderInterface $menuProvider
     * @param RendererProviderInterface $rendererProvider
     */
    public function __construct (MenuProviderInterface $menuProvider, RendererProviderInterface $rendererProvider)
    {
        $this->menuProvider     = $menuProvider;
        $this->rendererProvider = $rendererProvider;
    }



    /**
     * Renders a bootstrap conform page tree menu
     *
     * @param ItemInterface $menu
     * @param array $options
     *
     * @return string
     */
    public function renderBootstrap ($menu, array $options = [])
    {
        // Set default values
        $options = array_merge([
            "template"      => "@BecklynPageTree/Menu/bootstrap.html.twig",
            "currentClass"  => "active",
            "ancestorClass" => "active"
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
    public function hasChildrenHelper (ItemInterface $item)
    {
        if ($item->getLevel() >= 2 || !$item->isDisplayed())
        {
            return false;
        }

        foreach ($item->getChildren() as $child)
        {
            /** @var ItemInterface $child */
            if (!$child->getExtra("pageTree:hidden", false))
            {
                // if we find a child which is not hidden, we need to render the menu
                return true;
            }
        }

        return false;
    }



    /**
     * {@inheritdoc}
     */
    public function getFunctions ()
    {
        return array(
            new \Twig_SimpleFunction("renderPageTreeBootstrapMenu", [$this, "renderBootstrap"], ["is_safe" => ["html"]]),
            new \Twig_SimpleFunction("pageTreeBootstrapMenu_hasChildren", [$this, "hasChildrenHelper"]),
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