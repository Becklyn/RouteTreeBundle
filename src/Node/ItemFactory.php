<?php declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Node;

use Becklyn\Menu\Item\MenuItem;
use Becklyn\RouteTreeBundle\Node\Security\SecurityInferHelper;
use Becklyn\RouteTreeBundle\Parameter\ParametersMerger;

class ItemFactory
{
    /**
     * @var SecurityInferHelper
     */
    private $securityInferHelper;


    /**
     */
    public function __construct (SecurityInferHelper $securityInferHelper)
    {
        $this->securityInferHelper = $securityInferHelper;
    }


    /**
     * Generates a node from the given route.
     */
    public function create (string $routeName, array $config, array $pathVariables, ?string $controller) : MenuItem
    {
        $item = new MenuItem(null, [
            "key" => $routeName,
            "route" => $routeName,
            "sort" => true,
            "extras" => [
                ParametersMerger::VARIABLES_EXTRA_KEY => $pathVariables,
            ],
        ]);

        foreach ($config as $key => $value)
        {
            switch ($key)
            {
                case "title":
                    $item->setLabel($value);
                    break;

                case "priority":
                    $item->setPriority($value);
                    break;

                case "security":
                    $item->setSecurity($value);
                    break;

                case "sort":
                    $item->setSort($value);
                    break;

                case "parameters":
                    $item->setExtra(ParametersMerger::VARIABLES_DEFAULT_PARAMETERS, $value);
                    break;

                // all unknown parameters are automatically extras
                default:
                    $item->setExtra($key, $value);
                    break;
            }
        }

        // infer security only if it is not explicitly set on the node
        if (!isset($config["security"]) && null !== $controller)
        {
            $this->inferSecurity($item, $controller);
        }

        return $item;
    }


    /**
     * Infers the security from the linked controller.
     */
    private function inferSecurity (MenuItem $item, string $controller) : void
    {
        $security = $this->securityInferHelper->inferSecurity($controller);

        if (null !== $security)
        {
            $item->setSecurity($security);
        }
    }
}
