<?php

namespace Tests\Becklyn\RouteTreeBundle\Builder;

use Becklyn\RouteTreeBundle\Builder\ParametersGenerator;
use Becklyn\RouteTreeBundle\Builder\TreeBuilder;
use Becklyn\RouteTreeBundle\tests\RouteTestTrait;
use Becklyn\RouteTreeBundle\Tree\Node;
use Becklyn\RouteTreeBundle\Tree\Processing\PostProcessing\MissingParametersProcessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Routing\RouteCollection;


/**
 *
 */
class TreeBuilderTest extends TestCase
{
    use RouteTestTrait;

    /**
     * @var TreeBuilder
     */
    private $builder;


    /**
     * @var MissingParametersProcessor
     */
    private $missingParametersProcessor;



    public function setUp ()
    {
        $this->builder = new TreeBuilder(new ParametersGenerator());
        $this->missingParametersProcessor = new MissingParametersProcessor();
    }



    public function testIgnoreRoute ()
    {
        $tree = $this->builder->buildTree($routes = [
            "my_route" => $this->generateRoute("/my-route"),
        ]);

        $this->assertEmpty($tree);
    }



    public function testIncludeParent ()
    {
        $tree = $this->builder->buildTree([
            "child" => $this->generateRoute("/child", ["parent" => "parent"]),
            "parent" => $this->generateRoute("/parent"),
        ]);

        $this->assertArrayHasKey("child", $tree);
        $this->assertArrayHasKey("parent", $tree);
    }



    /**
     * @expectedException \Becklyn\RouteTreeBundle\Exception\InvalidRouteTreeException
     */
    public function testMissingParent ()
    {
        $this->builder->buildTree([
            "child" => $this->generateRoute("/child", ["parent" => "parent"]),
        ]);
    }



    /**
     * @expectedException \Becklyn\RouteTreeBundle\Exception\InvalidNodeDataException
     */
    public function testInvalidSeparator ()
    {
        $this->builder->buildTree([
            "child" => $this->generateRoute("/child", ["separator" => "idontexist"]),
        ]);
    }



    public function testLinking ()
    {
        $tree = $this->builder->buildTree([
            "child" => $this->generateRoute("/child", ["parent" => "parent"]),
            "parent" => $this->generateRoute("/parent"),
        ]);

        /** @var Node $child */
        $child = $tree["child"];
        /** @var Node $parent */
        $parent = $tree["parent"];

        $this->assertSame($parent, $child->getParent());
        $this->assertContains($child, $parent->getChildren());
        $this->assertCount(1, $parent->getChildren());
    }



    public function assertMissingParameter ()
    {
        $tree = $this->builder->buildTree([
            "route" => $this->generateRoute("/route/{first}"),
        ]);

        $this->assertArrayHasKey("first", $tree["route"]->getParameters());
        $this->assertNull($tree["route"]->getParameters()["first"]);
    }



    public function testDefinedParameter ()
    {
        $tree = $this->builder->buildTree([
            "route" => $this->generateRoute("/route/{first}", [
                "parameters" => ["first" => "directly-set"],
            ]),
        ]);

        $this->assertArrayHasKey("first", $tree["route"]->getParameters());
        $this->assertSame("directly-set", $tree["route"]->getParameters()["first"]);
    }



    public function testInheritedParameter ()
    {
        $tree = $this->builder->buildTree([
            "child" => $this->generateRoute("/child/{param}", ["parent" => "parent"]),
            "parent" => $this->generateRoute("/parent", ["parent" => "grandparent"]),
            "grandparent" => $this->generateRoute("/grandparent", [
                "parameters" => ["param" => "inherited"],
            ]),
        ]);

        $route = $tree["child"];

        $this->assertArrayHasKey("param", $route->getMergedParameters());
        $this->assertSame("inherited", $route->getMergedParameters()["param"]);
    }



    public function testBuildFromRouteCollection ()
    {
        $routeCollection = new RouteCollection();
        $routeCollection->add("child", $this->generateRoute("/child/{param}", ["parent" => "parent"]));
        $routeCollection->add("parent", $this->generateRoute("/parent", ["parent" => "grandparent"]));
        $routeCollection->add("grandparent", $this->generateRoute("/grandparent", [
            "parameters" => ["param" => "inherited"],
        ]));

        $tree = $this->builder->buildTree($routeCollection);

        $this->assertArrayHasKey("grandparent", $tree);
        $this->assertArrayHasKey("parent", $tree);
        $this->assertArrayHasKey("child", $tree);
    }

    public function testPriorityOfRequestAttributesOverInherited ()
    {
        $tree = $this->builder->buildTree([
            "child" => $this->generateRoute("/child", [
                "parent" => "parent",
                "parameters" => [
                    "from_attributes" => null,
                    "from_parent" => null,
                ]
            ]),
            "parent" => $this->generateRoute("/parent", [
                "parameters" => [
                    "from_attributes" => "no",
                    "from_parent" => "yes",
                ]
            ]),
        ]);

        $attributes = [
            "from_attributes" => "yes",
        ];

        foreach ($tree as $node)
        {
            $this->missingParametersProcessor->process($attributes, $node);
        }

        $child = $tree["child"];

        // the inherited parameter should only be used if there is no parameter in the attributes
        $this->assertSame("yes", $child->getParameters()["from_attributes"], "from attributes");
        $this->assertSame("yes", $child->getParameters()["from_parent"], "from parent");
    }
}
