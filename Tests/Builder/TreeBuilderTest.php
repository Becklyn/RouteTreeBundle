<?php

namespace Tests\Becklyn\RouteTreeBundle\Builder;

use Becklyn\RouteTreeBundle\Builder\BuildProcessor\Parameter\ParametersGenerator;
use Becklyn\RouteTreeBundle\Builder\BuildProcessor\ParameterProcessor;
use Becklyn\RouteTreeBundle\Builder\BuildProcessor\PriorityProcessor;
use Becklyn\RouteTreeBundle\Builder\TreeBuilder;
use Becklyn\RouteTreeBundle\Node\Node;
use Becklyn\RouteTreeBundle\Node\NodeFactory;
use Becklyn\RouteTreeBundle\Node\Security\SecurityInferHelper;
use Becklyn\RouteTreeBundle\PostProcessing\Processor\MissingParametersProcessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouteCollection;
use Tests\Becklyn\RouteTreeBundle\RouteTestTrait;


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
        $securityInferHelper = $this->getMockBuilder(SecurityInferHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $requestStack = $this->getMockBuilder(RequestStack::class)
            ->disableOriginalConstructor()
            ->getMock();

        $requestStack
            ->method("getMasterRequest")
            ->willReturn(new Request([], [], [
                "_route_params" => [
                    "from_attributes" => "yes",
                ],
            ]));

        $this->builder = new TreeBuilder(
            new NodeFactory($securityInferHelper),
            new PriorityProcessor(),
            new ParameterProcessor(new ParametersGenerator())
        );

        $this->missingParametersProcessor = new MissingParametersProcessor($requestStack);
    }



    public function testIgnoreRoute ()
    {
        $tree = $this->builder->buildTree([
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

        foreach ($tree as $node)
        {
            $this->missingParametersProcessor->process($node);
        }

        $child = $tree["child"];

        // the inherited parameter should only be used if there is no parameter in the attributes
        $this->assertSame("yes", $child->getParameters()["from_attributes"], "from attributes");
        $this->assertSame("yes", $child->getParameters()["from_parent"], "from parent");
    }
}
