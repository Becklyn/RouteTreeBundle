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
