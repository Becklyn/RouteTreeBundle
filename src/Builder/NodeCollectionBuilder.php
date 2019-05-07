<?php declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Builder;

use Becklyn\RouteTreeBundle\Node\NodeFactory;
use Symfony\Component\Routing\RouterInterface;

class NodeCollectionBuilder
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;


    /**
     * @var RouterInterface
     */
    private $router;


    /**
     * @param NodeFactory     $nodeFactory
     * @param RouterInterface $router
     */
    public function __construct (NodeFactory $nodeFactory, RouterInterface $router)
    {
        $this->nodeFactory = $nodeFactory;
        $this->router = $router;
    }


    /**
     * Builds a new node collection from the current router's routes.
     *
     * @return NodeCollection
     */
    public function build () : NodeCollection
    {
        return new NodeCollection($this->nodeFactory, $this->router->getRouteCollection());
    }
}
