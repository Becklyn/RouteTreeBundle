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


    public function dataProviderTestParameter ()
    {
        return [
            // missing parameters are overriden with default values
            ["shouldBeMissing", 1],
            // existing parameter should be taken
            ["shouldNotBeMissing", "exists"],
        ];
    }


    /**
     * @dataProvider dataProviderTestParameter
     *
     * @param string $setParameterName
     * @param        $expectedValue
     */
    public function testParameter (string $setParameterName, $expectedValue)
    {
        $processor = $this->buildProcessor([
            "shouldNotBeMissing" => "exists",
        ]);
        $node = new Node("test");
        $node->setParameters([$setParameterName => null]);
        $processor->process($node);

        $this->assertSame($expectedValue, $node->getParameters()[$setParameterName]);
    }
}
