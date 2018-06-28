<?php declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Routing;

use Symfony\Component\Routing\Route;


/**
 * Class to read (and normalize) the route config data
 */
class RoutingConfigReader
{
    const CONFIG_OPTIONS_KEY = "tree";
    const CONFIG_PARENT_KEY = "parent";


    /**
     * Returns the config of the route
     *
     * @param Route $route
     * @return array|null
     */
    public function getConfig (Route $route)
    {
        $data = $route->getOption(self::CONFIG_OPTIONS_KEY);

        // if a string is given, use it as parent
        if (\is_string($data))
        {
            $data = [
                self::CONFIG_PARENT_KEY => $data,
            ];
        }

        return \is_array($data)
            ? $data
            : null;
    }
}
