<?php declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Parameter;

use Becklyn\Menu\Item\MenuItem;
use Becklyn\Menu\Target\LazyRoute;
use Becklyn\RouteTreeBundle\Exception\InvalidParameterValueException;

class ParametersMerger
{
    public const VARIABLES_EXTRA_KEY = "_route_tree.path_vars";
    public const VARIABLES_DEFAULT_PARAMETERS = "_route_tree.default_parameters";


    /**
     * Merges the parameters in the sub tree.
     *
     * @throws InvalidParameterValueException
     */
    public function mergeParameters (MenuItem $item, array $parameters, array $routeSpecificParameters = []) : void
    {
        $this->traverse($item, $routeSpecificParameters, $parameters);
    }


    /**
     * @throws InvalidParameterValueException
     */
    private function traverse (
        MenuItem $item,
        array $routeSpecificParameters,
        array $parameters
    ) : void
    {
        $target = $item->getTarget();
        $pathVariables = $item->getExtra(self::VARIABLES_EXTRA_KEY, []);

        if ($target instanceof LazyRoute && \is_array($pathVariables) && !empty($pathVariables))
        {
            $newParameters = [];
            $itemParameters = $target->getParameters();

            $sources = [
                $itemParameters,
                $routeSpecificParameters[$target->getRoute()] ?? [],
                $parameters,
                $item->getExtra(self::VARIABLES_DEFAULT_PARAMETERS, []),
            ];

            foreach ($pathVariables as $variable)
            {
                foreach ($sources as $source)
                {
                    if (\array_key_exists($variable, $source))
                    {
                        $newParameters[$variable] = $this->transformValue(
                            $target->getRoute(),
                            $variable,
                            $source[$variable]
                        );

                        continue 2;
                    }
                }

                $newParameters[$variable] = null;
            }

            $item->setTarget(new LazyRoute(
                $target->getRoute(),
                $newParameters,
                $target->getReferenceType()
            ));
        }

        foreach ($item->getChildren() as $child)
        {
            $this->traverse($child, $routeSpecificParameters, $parameters);
        }
    }


    /**
     * Transforms the value to a route-compatible one.
     *
     * @param mixed $value
     *
     * @throws InvalidParameterValueException
     *
     * @return mixed
     */
    private function transformValue (string $routeName, string $parameterName, $value)
    {
        if (\is_object($value) && \method_exists($value, "getId"))
        {
            return $value->getId();
        }

        if (!\is_string($value) && !\is_int($value) && !\is_float($value) && null !== $value)
        {
            throw new InvalidParameterValueException(\sprintf(
                "Invalid parameter type for route parameter '%s' in route '%s': must be object with ->getId() or scalar, but %s given.",
                $parameterName,
                $routeName,
                \gettype($value)
            ));
        }

        return $value;
    }
}
