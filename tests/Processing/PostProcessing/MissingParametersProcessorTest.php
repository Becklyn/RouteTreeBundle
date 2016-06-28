<?php

namespace Becklyn\RouteTreeBundle\tests\Processing\PostProcessing;

use Becklyn\RouteTreeBundle\Tree\Node;
use Becklyn\RouteTreeBundle\Tree\Processing\PostProcessing\MissingParametersProcessor;
use Symfony\Component\HttpFoundation\ParameterBag;


/**
 *
 */
class MissingParametersProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MissingParametersProcessor
     */
    private $missingParametersProcessor;


    public function setUp ()
    {
        $this->missingParametersProcessor = new MissingParametersProcessor();
    }


    public function testMissingParameter ()
    {
        $attributes = new ParameterBag();
        $node = new Node("test");
        $node->setParameters(["shouldBeMissing" => null]);
        $this->missingParametersProcessor->process($attributes, $node);

        // sets the default value
        $this->assertSame(1, $node->getParameters()["shouldBeMissing"]);
    }


    public function testExistingParameter ()
    {
        $attributes = new ParameterBag([
            "shouldNotBeMissing" => "exists",
        ]);
        $node = new Node("test");
        $node->setParameters(["shouldNotBeMissing" => null]);
        $this->missingParametersProcessor->process($attributes, $node);

        $this->assertSame("exists", $node->getParameters()["shouldNotBeMissing"]);
    }
}
