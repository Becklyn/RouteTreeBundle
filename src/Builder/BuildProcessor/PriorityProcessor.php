<?php declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Builder\BuildProcessor;

use Becklyn\RouteTreeBundle\Node\Node;

class PriorityProcessor
{
    /**
     * @param Node[] $nodes
     *
     * @return Node[]
     */
    public function sortNodes (array $nodes) : array
    {
        foreach ($nodes as $node)
        {
            // only sort top level and descend recursively
            if (null === $node->getParent())
            {
                $this->sortChildren($node);
            }
        }

        return $nodes;
    }


    /**
     * Recursively sort all children.
     *
     * @param Node $node
     */
    private function sortChildren (Node $node) : void
    {
        $children = $node->getChildren();

        \usort(
            $children,
            function (Node $left, Node $right)
            {
                return $right->getPriority() <=> $left->getPriority();
            }
        );

        $node->setChildren($children);

        foreach ($children as $child)
        {
            $this->sortChildren($child);
        }
    }

}
