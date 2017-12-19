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

        $this->assertSame("title", $node->getTitle());
        $this->assertSame("title", $node->getDisplayTitle());
    }


    public function testMissingTitle ()
    {
        $node = new Node("route");

        $this->assertNull($node->getTitle());
        $this->assertSame("route", $node->getDisplayTitle());
    }


    public function testAutoHiding ()
    {
        $node = new Node("route");

        self::assertTrue($node->isHidden());

        // not hidden anymore as soon as a title is set
        $node->setTitle("Some title");
        self::assertFalse($node->isHidden());
    }


    public function testExplicitHiding ()
    {
        $node = new Node("route");
        $node->setHidden(true);

        self::assertTrue($node->isHidden());

        // still hidden
        $node->setTitle("Some title");
        self::assertTrue($node->isHidden());
    }



    public function testChildLinks ()
    {
        $child = new Node("child");
        $parent = new Node("parent");

        $this->assertNull($child->getParent());
        $this->assertEmpty($parent->getChildren());

        $parent->addChild($child);
        $child->setParent($parent);

        $this->assertSame($parent, $child->getParent());
        $this->assertEquals([$child], $parent->getChildren());
    }
}
