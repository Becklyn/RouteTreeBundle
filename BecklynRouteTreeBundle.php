<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle;

use Becklyn\RouteTreeBundle\DependencyInjection\BecklynRouteTreeExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;


/**
 *
 */
class BecklynRouteTreeBundle extends Bundle
{
    /**
     * @inheritDoc
     */
    protected function getContainerExtensionClass ()
    {
        return BecklynRouteTreeExtension::class;
    }
}
