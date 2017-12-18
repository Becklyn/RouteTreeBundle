<?php

namespace Tests\Becklyn\RouteTreeBundle\Processing\PostProcessing;

use Becklyn\RouteTreeBundle\Node\Node;
use Becklyn\RouteTreeBundle\Tree\Processing\PostProcessing\MissingParametersProcessor;
use PHPUnit\Framework\TestCase;


/**
 *
 */
class MissingParametersProcessorTest extends TestCase
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
