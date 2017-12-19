<?php

namespace Tests\Becklyn\RouteTreeBundle\Processing\PostProcessing;

use Becklyn\RouteTreeBundle\Node\Node;
use Becklyn\RouteTreeBundle\PostProcessing\Processor\MissingParametersProcessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;


/**
 *
 */
class MissingParametersProcessorTest extends TestCase
{
    private function buildProcessor (array $routeParameters) : MissingParametersProcessor
    {
        $requestStack = $this->getMockBuilder(RequestStack::class)
            ->disableOriginalConstructor()
            ->getMock();

        $request = new Request([], [], [
            "_route_params" => $routeParameters,
        ]);

        $requestStack
            ->method("getMasterRequest")
            ->willReturn($request);

        return new MissingParametersProcessor($requestStack);
    }


    public function testMissingParameter ()
    {
        $processor = $this->buildProcessor([]);
        $node = new Node("test");
        $node->setParameters(["shouldBeMissing" => null]);
        $processor->process($node);

        // sets the default value
        $this->assertSame(1, $node->getParameters()["shouldBeMissing"]);
    }


    public function testExistingParameter ()
    {
        $processor = $this->buildProcessor([
            "shouldNotBeMissing" => "exists",
        ]);
        $node = new Node("test");
        $node->setParameters(["shouldNotBeMissing" => null]);
        $processor->process($node);

        $this->assertSame("exists", $node->getParameters()["shouldNotBeMissing"]);
    }
}
