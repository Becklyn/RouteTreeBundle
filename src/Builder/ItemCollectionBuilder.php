<?php declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Builder;

use Becklyn\RouteTreeBundle\Node\ItemFactory;
use Symfony\Component\Routing\RouterInterface;

class ItemCollectionBuilder
{
    /**
     * @var ItemFactory
     */
    private $itemFactory;


    /**
     * @var RouterInterface
     */
    private $router;


    /**
     */
    public function __construct (ItemFactory $itemFactory, RouterInterface $router)
    {
        $this->itemFactory = $itemFactory;
        $this->router = $router;
    }


    /**
     * Builds a new node collection from the current router's routes.
     */
    public function build () : ItemCollection
    {
        return new ItemCollection($this->itemFactory, $this->router->getRouteCollection());
    }
}
