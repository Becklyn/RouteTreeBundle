<?php declare(strict_types=1);

namespace Tests\Becklyn\RouteTreeBundle\Builder;

use Becklyn\RouteTreeBundle\Builder\NodeCollection;
use Becklyn\RouteTreeBundle\Node\Node;
use Becklyn\RouteTreeBundle\Node\NodeFactory;
use Becklyn\RouteTreeBundle\Node\Security\SecurityInferHelper;
use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Tests\Becklyn\RouteTreeBundle\RouteTestTrait;


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
}
