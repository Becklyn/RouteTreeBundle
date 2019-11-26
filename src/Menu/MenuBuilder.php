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
     */
    public function __construct (RouteTree $routeTree, LoggerInterface $logger)
    {
        $this->routeTree = $routeTree;
        $this->logger = $logger;
    }


    /**
     *
     */
    public function build (string $fromRoute) : MenuItem
    {
        try
        {
            return $this->routeTree->getByRoute($fromRoute);
        }
        catch (RouteTreeException $exception)
        {
            $this->logger->error("Route tree building failed from route '{from_route}' due to an exception.", [
                "from_route" => $fromRoute,
                "exception" => $exception,
            ]);

            return new MenuItem();
        }
    }


    /**
     *
     */
    public function buildBreadcrumb (string $fromRoute) : MenuItem
    {
        try
        {
            $root = new MenuItem();
            $item = $this->routeTree->getByRoute($fromRoute);

            if (null === $item)
            {
                return $root;
            }

            foreach ($item->getHierarchy() as $node)
            {
                $inHierarchy = clone $node;
                $inHierarchy->clearChildren();
                $root->addChild($inHierarchy);
            }

            return $root;
        }
        catch (RouteTreeException $exception)
        {
            $this->logger->error("Route tree building failed from route '{from_route}' due to an exception.", [
                "from_route" => $fromRoute,
                "exception" => $exception,
            ]);

            return new MenuItem();
        }
    }
}
