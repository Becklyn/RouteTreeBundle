<?php

namespace Becklyn\RouteTreeBundle\tests\Tree;

use Becklyn\RouteTreeBundle\Tree\Node;


/**
 *
 */
class NodeTest extends \PHPUnit_Framework_TestCase
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

        $this->assertSame($parent, $child->getParent());
        $this->assertEquals([$child], $parent->getChildren());
    }



    /**
     * @expectedException \Becklyn\RouteTreeBundle\Exception\InvalidNodeDataException
     */
    public function testInvalidSeparator ()
    {
        $node = new Node("route");
        $node->setSeparator("idontexist");
    }
}
