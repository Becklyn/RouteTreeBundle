<?php declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\KnpMenu\Renderer;

use Becklyn\RouteTreeBundle\KnpMenu\Voter\SimpleRouteVoter;
use Knp\Menu\Matcher\Matcher;
use Knp\Menu\Renderer\TwigRenderer;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;


class SimpleTwigRenderer extends TwigRenderer
{
    const ALIAS = "route_tree";

    /**
     * @param Environment  $twig
     * @param RequestStack $requestStack
     */
    public function __construct (Environment $twig, RequestStack $requestStack)
    {
        parent::__construct(
            $twig,
            "@BecklynRouteTree/menu-template.html.twig",
            new Matcher([
                new SimpleRouteVoter($requestStack),
            ]),
            [
                "currentClass" => "is-active is-current",
                "ancestorClass" => "is-active is-ancestor",
            ]
        );
    }
}
