<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Tree;

use Becklyn\Menu\Item\MenuItem;
use Becklyn\RouteTreeBundle\Builder\ItemCollection;
use Becklyn\RouteTreeBundle\Cache\TreeCache;
use Becklyn\RouteTreeBundle\Exception\InvalidRouteTreeException;
use Becklyn\RouteTreeBundle\Node\ItemFactory;
use Symfony\Component\Config\ConfigCacheFactory;
use Symfony\Component\Config\ConfigCacheFactoryInterface;
use Symfony\Component\Config\ConfigCacheInterface;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 *
 */
class RouteTree implements CacheClearerInterface, CacheWarmerInterface
{
    private const CONFIG_CACHE_PATH = "becklyn/route_tree/tree.serialized";

    /** @var MenuItem[] */
    private $items;

    /** @var TreeCache */
    private $treeCache;

    /** @var ConfigCacheFactoryInterface */
    private $configCacheFactory;

    /** @var string */
    private $cacheDir;

    /** @var bool */
    private $isDebug;

    /** @var ItemFactory */
    private $itemFactory;

    /** @var RouterInterface */
    private $router;


    /**
     */
    public function __construct (
        RouterInterface $router,
        ItemFactory $itemFactory,
        TreeCache $treeCache,
        string $cacheDir,
        bool $isDebug
    )
    {
        $this->treeCache = $treeCache;
        $this->cacheDir = $cacheDir;
        $this->isDebug = $isDebug;
        $this->itemFactory = $itemFactory;
        $this->router = $router;
    }


    /**
     * Builds the tree.
     *
     * @return MenuItem[]
     */
    private function generateItems () : array
    {
        $nodes = $this->treeCache->get();

        if (null === $nodes)
        {
            $configCache = $this->getConfigCacheFactory()->cache(
                "{$this->cacheDir}/" . self::CONFIG_CACHE_PATH,
                function (ConfigCacheInterface $cache) : void
                {
                    $routeCollection = $this->router->getRouteCollection();

                    $collection = new ItemCollection($this->itemFactory, $routeCollection);
                    $items = $collection->getItems();

                    $cache->write(
                        \sprintf(
                            '<?php return \\unserialize(%s);',
                            \var_export(\serialize($items), true)
                        ),
                        $routeCollection->getResources()
                    );
                }
            );

            $nodes = include $configCache->getPath();
            $this->treeCache->set($nodes);
        }

        return $nodes;
    }


    /**
     * Fetches a node from the tree.
     *
     * @throws InvalidRouteTreeException
     */
    public function getByRoute (string $route) : ?MenuItem
    {
        if (null === $this->items)
        {
            $this->items = $this->generateItems();
        }

        return $this->items[$route] ?? null;
    }



    //region Cache clearer implementation
    /**
     * @inheritDoc
     *
     * @internal
     */
    public function clear ($cacheDir) : void
    {
        $this->treeCache->clear();
    }
    //endregion



    //region Cache warmer implementation
    /**
     * @inheritDoc
     *
     * @internal
     */
    public function isOptional () : bool
    {
        return true;
    }



    /**
     * @inheritDoc
     *
     * @internal
     */
    public function warmUp ($cacheDir) : array
    {
        $this->treeCache->clear();
        $this->generateItems();

        return [];
    }
    //endregion


    /**
     * Creates and returns a new config cache
     */
    private function getConfigCacheFactory () : ConfigCacheFactoryInterface
    {
        if (null === $this->configCacheFactory)
        {
            $this->configCacheFactory = new ConfigCacheFactory($this->isDebug);
        }

        return $this->configCacheFactory;
    }


    /**
     */
    public function setConfigCacheFactory (ConfigCacheFactoryInterface $factory) : void
    {
        $this->configCacheFactory = $factory;
    }
}
