<?php declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Tests\KnpMenu;

use Becklyn\RouteTreeBundle\Builder\NodeCollectionBuilder;
use Becklyn\RouteTreeBundle\Cache\TreeCache;
use Becklyn\RouteTreeBundle\KnpMenu\MenuBuilder;
use Becklyn\RouteTreeBundle\PostProcessing\PostProcessor;
use Becklyn\RouteTreeBundle\PostProcessing\Processor\SecurityProcessor;
use Becklyn\RouteTreeBundle\Tree\RouteTree;
use Knp\Menu\Integration\Symfony\RoutingExtension;
use Knp\Menu\MenuFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Tests\Becklyn\RouteTreeBundle\RouteTestTrait;


class MenuBuilderTest extends TestCase
{
    use RouteTestTrait;


    /**
     * @param array $routes
     * @return RouteTree
     */
    private function createRouteTree (array $routes) : RouteTree
    {
        $nodeCollectionBuilder = $this->getMockBuilder(NodeCollectionBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cache = $this->getMockBuilder(TreeCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $nodes = $this->buildAndGetNodes($routes);

        $cache
            ->method("get")
            ->willReturn($nodes);

        $securityProcessor = $this->getMockBuilder(SecurityProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $postProcessing = new PostProcessor($securityProcessor);

        return new RouteTree($nodeCollectionBuilder, $cache, $postProcessing);
    }


    /**
     * Builds the menu builder
     *
     * @param array $routes
     * @param array $requestDefaults
     * @return MenuBuilder
     */
    private function createMenuBuilder (array $routes, array $requestDefaults = []) : MenuBuilder
    {
        $requestStack = new RequestStack();
        $requestStack->push(new Request([], [], [
            "_route_params" => $requestDefaults,
        ]));

        $factory = new MenuFactory();

        $urlGenerator = new class implements UrlGeneratorInterface
        {
            public function generate ($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH)
            {
                return \json_encode([$name, $parameters]);
            }

            public function setContext (RequestContext $context) {}
            public function getContext () {}
        };

        $factory->addExtension(new RoutingExtension($urlGenerator));

        $routeTree = $this->createRouteTree($routes);
        return new MenuBuilder($factory, $routeTree, $requestStack);
    }


    /**
     * Test a simple menu
     */
    public function testMenuBuild ()
    {
        $builder = $this->createMenuBuilder([
            "a" => $this->createRoute("/a"),
            "b" => $this->createRoute("/b", "a"),
        ]);

        $menu = $builder->buildMenu("a");

        self::assertSame("root", $menu->getName());
        self::assertCount(1, $menu->getChildren());
        self::assertNotNull($menu->getChild("b"));
    }


    // region Parameter

    public function provideRuntimeParameterInheritance () : array
    {
        return [
            [
                [],
                [],
                [],
                [],
                [],
                ["b", ["p" => 1]],
                "use global default",
            ],
            [
                ["p" => 2],
                [],
                [],
                [],
                [],
                ["b", ["p" => 2]],
                "inherit parent default",
            ],
            [
                ["p" => 2],
                ["p" => 3],
                [],
                [],
                [],
                ["b", ["p" => 3]],
                "route default",
            ],
            [
                [],
                ["p" => 2],
                ["p" => 3],
                ["p" => 4],
                [],
                ["b", ["p" => 4]],
                "request route parameter",
            ],
            [
                [],
                ["p" => 2],
                ["p" => 3],
                ["p" => 4],
                [],
                ["b", ["p" => 4]],
                "passed default parameter",
            ],
            [
                [],
                ["p" => 2],
                ["p" => 3],
                ["p" => 4],
                [
                    "b" => [
                        "p" => 5,
                    ]
                ],
                ["b", ["p" => 5]],
                "passed route-bound default parameter",
            ],
            [
                [],
                ["p" => 2],
                ["p" => 3],
                ["p" => 4],
                [
                    "a" => [
                        "p" => 5,
                    ]
                ],
                ["b", ["p" => 4]],
                "route-bound default parameter for wrong route, so fallback",
            ],
            [
                [],
                ["p" => 2],
                ["p" => 3],
                ["p" => 4],
                [
                    "b" => [
                        "p" => null,
                    ]
                ],
                ["b", ["p" => null]],
                "null is possible as default value",
            ],
        ];
    }


    /**
     * @dataProvider provideRuntimeParameterInheritance
     *
     * @param array  $parentRouteDefaults
     * @param array  $routeDefaults
     * @param array  $requestDefaults
     * @param array  $callDefaults
     * @param array  $callRouteDefaults
     * @param array  $expected
     * @param string $comment
     */
    public function testRuntimeParameterInheritance (
        array $parentRouteDefaults,
        array $routeDefaults,
        array $requestDefaults,
        array $callDefaults,
        array $callRouteDefaults,
        array $expected,
        string $comment
    )
    {
        $builder = $this->createMenuBuilder([
            "a" => $this->createRoute("/a", [
                "parameters" => $parentRouteDefaults,
            ]),
            "b" => $this->createRoute("/b/{p}", [
                "parent" => "a",
                "parameters" => $routeDefaults,
            ]),
        ], $requestDefaults);

        $menu = $builder->buildMenu("a", $callDefaults, $callRouteDefaults);
        $b = $menu->getChild("b");
        self::assertSame(\json_encode($expected), $b->getUri(), $comment);
    }


    /**
     * Test building a breadcrumb + correct ordering
     */
    public function testBuildBreadcrumb ()
    {
        $builder = $this->createMenuBuilder([
            "d" => $this->createRoute("/d", "c"),
            "c" => $this->createRoute("/c", "b"),
            "b" => $this->createRoute("/b", "a"),
            "a" => $this->createRoute("/a"),
        ]);

        $breadcrumb = $builder->buildBreadcrumb("d");
        self::assertSame("root", $breadcrumb->getName());
        self::assertEquals(["a", "b", "c", "d"], \array_keys($breadcrumb->getChildren()));
    }
}
