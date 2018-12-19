<?php

namespace Tests\Becklyn\RouteTreeBundle\Tree;

use Becklyn\RouteTreeBundle\Node\Node;
use PHPUnit\Framework\TestCase;


/**
 *
 */
class NodeTest extends TestCase
{
    public function testWithTitle ()
    {
        $node = new Node("route");
        $node->setTitle("title");

        self::assertSame("title", $node->getTitle());
        self::assertSame("title", $node->getDisplayTitle());
    }


    public function testMissingTitle ()
    {
        $node = new Node("route");

        self::assertNull($node->getTitle());
        self::assertSame("route", $node->getDisplayTitle());
    }


    public function dataProviderTestAutoHiding ()
    {
        return [
            // not previously hidden -> not hidden as soon as title is set
            [false, false],
            // previously hidden -> still hidden as soon as title is set
            [true, true],
        ];
    }


    /**
     * @dataProvider dataProviderTestAutoHiding
     *
     * @param bool $previouslyHidden
     * @param bool $hiddenAfterwards
     */
    public function testAutoHiding (bool $previouslyHidden, bool $hiddenAfterwards)
    {
        $node = new Node("route");
        $node->setHidden($previouslyHidden);

        self::assertTrue($node->isHidden());
        $node->setTitle("Some title");
        self::assertSame($hiddenAfterwards, $node->isHidden());
    }


    public function testChildLinks ()
    {
        $child = new Node("child");
        $parent = new Node("parent");

        self::assertNull($child->getParent());
        self::assertEmpty($parent->getChildren());

        $parent->addChild($child);
        $child->setParent($parent);

        self::assertSame($parent, $child->getParent());
        self::assertEquals([$child], $parent->getChildren());
    }
}
