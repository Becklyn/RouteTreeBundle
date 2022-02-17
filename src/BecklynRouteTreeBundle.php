<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 *
 */
class BecklynRouteTreeBundle extends Bundle
{
    /**
     * @inheritDoc
     */
    public function getContainerExtension () : ?ExtensionInterface
    {
        return new class() extends Extension {
            /**
             * @inheritDoc
             */
            public function load(array $configs, ContainerBuilder $container) : void
            {
                $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/Resources/config'));
                $loader->load('services.yaml');
            }

            /**
             * @inheritDoc
             */
            public function getAlias () : string
            {
                return "becklyn_route_tree";
            }
        };
    }
}
