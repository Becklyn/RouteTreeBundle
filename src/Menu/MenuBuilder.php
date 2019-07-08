<?php declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Menu;

use Becklyn\Menu\Item\MenuItem;
use Becklyn\RouteTreeBundle\Exception\RouteTreeException;
use Becklyn\RouteTreeBundle\Tree\RouteTree;
use Psr\Log\LoggerInterface;

class MenuBuilder
{
    /**
     * @var RouteTree
     */
    private $routeTree;


    /**
     * @var LoggerInterface
     */
    private $logger;


    /**
     * @param RouteTree       $routeTree
     * @param LoggerInterface $logger
     */
    public function __construct (RouteTree $routeTree, LoggerInterface $logger)
    {
        $this->routeTree = $routeTree;
        $this->logger = $logger;
    }


    /**
     * @param string $fromRoute
     *
     * @return MenuItem
     */
    public function build (string $fromRoute) : MenuItem
    {
        $root = new MenuItem();

        try
        {
            $rootNode = $this->routeTree->getByRoute($fromRoute);

            if (null !== $rootNode)
            {
                $this->appendNodes($root, $rootNode->getChildren());
            }

            return $root;
        }
        catch (RouteTreeException $exception)
        {
            $this->logger->error("Route tree building failed from route '{from_route}' due to an exception.", [
                "from_route" => $fromRoute,
                "exception" => $exception,
            ]);

            return $root;
        }
    }


    /**
     * @param MenuItem   $item
     * @param MenuItem[] $items
     */
    private function appendNodes (MenuItem $item, array $items) : void
    {
        foreach ($items as $node)
        {
            $child = $item->addChild($node->getDisplayTitle(), [
                "route" => $node->getRoute(),
                "routeParameters" => [],
                "visible" => !$node->isHidden(),
                "extras" => $node->getExtras(),
            ]);

            $this->appendNodes($child, $node->getChildren());
        }
    }
}
