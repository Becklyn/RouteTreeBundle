<?php declare(strict_types=1);

namespace Tests\Becklyn\RouteTreeBundle\Builder;

use Becklyn\RouteTreeBundle\Builder\NodeCollection;
use Becklyn\RouteTreeBundle\Node\Node;
use Becklyn\RouteTreeBundle\Node\NodeFactory;
use Becklyn\RouteTreeBundle\Node\Security\SecurityInferHelper;
use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Tests\Becklyn\RouteTreeBundle\RouteTestTrait;


/**
 * This class contains all tests related to the node collection.
 * It acts as a sort-of integration test for nearly all tree-building logic.
 */
class NodeCollectionTest extends TestCase
{
    use RouteTestTrait;


    /**
     * @var NodeFactory
     */
    private $nodeFactory;


    /**
     * @inheritdoc
     */
    protected function setUp ()
    {
        $annotationReader = $this->getMockBuilder(AnnotationReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->nodeFactory = new NodeFactory(new SecurityInferHelper($annotationReader, $container));
    }


    /**
     * Builds a collection and gets its nodes
     *
     * @param array $routes
     * @return Node[]
     */
    private function buildAndGetNodes (array $routes) : array
    {
        return (new NodeCollection($this->nodeFactory, $routes))->getNodes();
    }


    /**
     * Tests that routes are automatically correctly linked
     */
    public function testLinkParent ()
    {
        $nodes = $this->buildAndGetNodes([
            "a" => $this->createRoute("/a"),
            "b" => $this->createRoute("/b", "a"),
            "c" => $this->createRoute("/c"),
        ]);

        self::assertEquals($nodes["a"], $nodes["b"]->getParent());
        self::assertEquals([$nodes["b"]], $nodes["a"]->getChildren());
    }


    /**
     * Tests inherited default parameters
     */
    public function testInheritedDefaults ()
    {
        $nodes = $this->buildAndGetNodes([
            "a" => $this->createRoute("/a", [
                "parameters" => [
                    "e" => 3,
                    "c" => 2,
                ],
            ]),
            "b" => $this->createRoute("/b/{c}/{d}", [
                "parent" => "a",
                "parameters" => [
                    "test" => 1,
                    "c" => 2,
                ],
            ]),
            "c" => $this->createRoute("/b/{e}", "b"),
        ]);

        self::assertEquals([], $nodes["a"]->getParameterValues());
        self::assertEquals(["c" => 2], $nodes["b"]->getParameterValues());
        self::assertEquals(["e" => 3], $nodes["c"]->getParameterValues());
    }


    /**
     * Asserts that routes which are not participated with the tree are ignored
     */
    public function testIgnoreRoute ()
    {
        $nodes = $this->buildAndGetNodes([
            "a" => $this->createRoute("/a"),
            "b" => $this->createRoute("/b"),
            "c" => $this->createRoute("/c"),
        ]);

        $this->assertEmpty($nodes);
    }


    /**
     * Tests the variant syntaxes for defining the parent
     */
    public function testParentTypes ()
    {
        $nodes = $this->buildAndGetNodes([
            "a" => $this->createRoute("/a"),
            "b" => $this->createRoute("/b", "a"),
            "c" => $this->createRoute("/c", [
                "parent" => "a",
            ]),
        ]);

        self::assertCount(2, $nodes["a"]->getChildren());
        self::assertContains($nodes["b"], $nodes["a"]->getChildren());
        self::assertContains($nodes["c"], $nodes["a"]->getChildren());
    }


    public function provideInvalidScalarValues ()
    {
        return [
            [1],
            [1.0],
            [false],
        ];
    }


    /**
     * Tests that invalid values as `tree` option are not allowed
     *
     * @dataProvider provideInvalidScalarValues
     *
     * @param $treeData
     * @expectedException Becklyn\RouteTreeBundle\Exception\RouteTreeException
     */
    public function testInvalidScalarValue ($treeData)
    {
        $route = new Route("/route", [], [], [
            "tree" => $treeData,
        ]);
        $this->buildAndGetNodes([
            "route" => $route
        ]);
    }

    /**
     * Tests that a missing parent correctly throws
     *
     * @expectedException \Becklyn\RouteTreeBundle\Exception\InvalidRouteTreeException
     */
    public function testMissingParent ()
    {
        $this->buildAndGetNodes([
            "b" => $this->createRoute("/b", "a"),
        ]);
    }


    /**
     * Tests that building from a `RouteCollection` is supported as well
     */
    public function testBuildFromRouteCollection ()
    {
        $routeCollection = new RouteCollection();
        $routeCollection->add("child", $this->createRoute("/child/{param}", ["parent" => "parent"]));
        $routeCollection->add("parent", $this->createRoute("/parent", ["parent" => "grandparent"]));
        $routeCollection->add("grandparent", $this->createRoute("/grandparent", [
            "parameters" => ["param" => "inherited"],
        ]));

        $collection = new NodeCollection($this->nodeFactory, $routeCollection);
        $nodes = $collection->getNodes();

        $this->assertArrayHasKey("grandparent", $nodes);
        $this->assertArrayHasKey("parent", $nodes);
        $this->assertArrayHasKey("child", $nodes);
    }


    /**
     * Tests that priority sorting works as expected
     */
    public function testPrioritySorting ()
    {
        $nodes = $this->buildAndGetNodes([
            "a" => $this->createRoute("/a"),
            "b" => $this->createRoute("/b", [
                "parent" => "a",
                "priority" => 10,
            ]),
            "c" => $this->createRoute("/c", [
                "parent" => "a",
                // default is priority 0
            ]),
            "d" => $this->createRoute("/d", [
                "parent" => "a",
                "priority" => 100,
            ]),
            "e" => $this->createRoute("/e", [
                "parent" => "a",
                "priority" => -10,
            ]),
        ]);

        $children = $nodes["a"]->getChildren();
        self::assertSame($nodes["d"], $children[0]);
        self::assertSame($nodes["b"], $children[1]);
        self::assertSame($nodes["c"], $children[2]);
        self::assertSame($nodes["e"], $children[3]);
    }
}
