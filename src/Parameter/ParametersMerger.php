<?php declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Parameter;

use Becklyn\Menu\Item\MenuItem;
use Becklyn\Menu\Target\LazyRoute;
use Becklyn\RouteTreeBundle\Exception\InvalidParameterValueException;
use Symfony\Component\HttpFoundation\RequestStack;

class ParametersMerger
{
    public const VARIABLES_EXTRA_KEY = "_route_tree.path_vars";


    /**
     * @var RequestStack
     */
    private $requestStack;


    /**
     * @param RequestStack $requestStack
     */
    public function __construct (RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }


    /**
     * Merges the parameters in the sub tree.
     *
     * @param MenuItem $item
     * @param array    $parameters
     * @param array    $routeSpecificParameters
     *
     * @throws InvalidParameterValueException
     */
    public function mergeParameters (MenuItem $item, array $parameters, array $routeSpecificParameters = []) : void
    {
        $request = $this->requestStack->getMasterRequest();
        $requestParameters = null !== $request
            ? $request->attributes->all()
            : [];

        $this->traverse($item, $routeSpecificParameters, $parameters, $requestParameters);
    }


    /**
     * @param MenuItem $item
     * @param array    $routeSpecificParameters
     * @param array    $parameters
     * @param array    $requestParameters
     *
     * @throws InvalidParameterValueException
     */
    private function traverse (
        MenuItem $item,
        array $routeSpecificParameters,
        array $parameters,
        array $requestParameters
    ) : void
    {
        $target = $item->getTarget();
        $pathVariables = $item->getExtra(self::VARIABLES_EXTRA_KEY, []);

        if ($target instanceof LazyRoute && \is_array($pathVariables) && !empty($pathVariables))
        {
            $newParameters = [];
            $itemParameters = $target->getParameters();

            foreach ($pathVariables as $variable)
            {
                $sources = [
                    $itemParameters,
                    $routeSpecificParameters[$target->getRoute()] ?? [],
                    $parameters,
                    $requestParameters,
                ];

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
            $this->traverse($child, $routeSpecificParameters, $parameters, $requestParameters);
        }
    }


    /**
     * Transforms the value to a route-compatible one.
     *
     * @param string $routeName
     * @param string $parameterName
     * @param mixed  $value
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
