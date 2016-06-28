<?php

namespace Becklyn\RouteTreeBundle\tests\Processing\PostProcessing;

use Becklyn\RouteTreeBundle\Tree\Node;
use Becklyn\RouteTreeBundle\Tree\Processing\PostProcessing\MissingParametersProcessor;


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
        $node = new Node("test");
        $node->setParameters(["shouldBeMissing" => null]);
        $this->missingParametersProcessor->process([], $node);

        // sets the default value
        $this->assertSame(1, $node->getParameters()["shouldBeMissing"]);
    }


    public function testExistingParameter ()
    {
        $attributes = [
            "shouldNotBeMissing" => "exists",
        ];
        $node = new Node("test");
        $node->setParameters(["shouldNotBeMissing" => null]);
        $this->missingParametersProcessor->process($attributes, $node);

        $this->assertSame("exists", $node->getParameters()["shouldNotBeMissing"]);
    }
}
