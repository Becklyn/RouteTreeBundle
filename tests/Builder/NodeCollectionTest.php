<?php declare(strict_types=1);

namespace Tests\Becklyn\RouteTreeBundle\Builder;

use Becklyn\RouteTreeBundle\Builder\ItemCollection;
use Becklyn\RouteTreeBundle\Exception\InvalidRouteTreeException;
use Becklyn\RouteTreeBundle\Exception\RouteTreeException;
use Becklyn\RouteTreeBundle\Node\ItemFactory;
use Becklyn\RouteTreeBundle\Node\Security\SecurityInferHelper;
use PHPUnit\Framework\TestCase;
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

    private ItemFactory $nodeFactory;


    /**
     * @inheritdoc
     */
    protected function setUp () : void
    {
        $securityInferHelper = $this->getMockBuilder(SecurityInferHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->nodeFactory = new ItemFactory($securityInferHelper);
    }


    /**
     * Tests that routes are automatically correctly linked
     */
    public function testLinkParent () : void
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
     * Asserts that routes which are not participated with the tree are ignored
     */
    public function testIgnoreRoute () : void
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
    public function testParentTypes () : void
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


    public function provideInvalidScalarValues () : array
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
     */
    public function testInvalidScalarValue ($treeData) : void
    {
        $this->expectException(RouteTreeException::class);

        $route = new Route("/route", [], [], [
            "tree" => $treeData,
        ]);
        $this->buildAndGetNodes([
            "route" => $route
        ]);
    }

    /**
     * Tests that a missing parent correctly throws
     */
    public function testMissingParent () : void
    {
        $this->expectException(InvalidRouteTreeException::class);

        $this->buildAndGetNodes([
            "b" => $this->createRoute("/b", "a"),
        ]);
    }


    /**
     * Tests that building from a `RouteCollection` is supported as well
     */
    public function testBuildFromRouteCollection () : void
    {
        $routeCollection = new RouteCollection();
        $routeCollection->add("child", $this->createRoute("/child/{param}", ["parent" => "parent"]));
        $routeCollection->add("parent", $this->createRoute("/parent", ["parent" => "grandparent"]));
        $routeCollection->add("grandparent", $this->createRoute("/grandparent", [
            "parameters" => ["param" => "inherited"],
        ]));

        $collection = new ItemCollection($this->nodeFactory, $routeCollection);
        $nodes = $collection->getItems();

        $this->assertArrayHasKey("grandparent", $nodes);
        $this->assertArrayHasKey("parent", $nodes);
        $this->assertArrayHasKey("child", $nodes);
    }

}
