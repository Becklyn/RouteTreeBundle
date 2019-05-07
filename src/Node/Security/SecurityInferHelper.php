<?php

declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\Node\Security;

use Doctrine\Common\Annotations\Reader;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class SecurityInferHelper
{
    /**
     * @var Reader
     */
    private $annotationsReader;


    /**
     * @var ContainerInterface
     */
    private $container;


    /**
     * @param Reader             $annotationsReader
     * @param ContainerInterface $container
     */
    public function __construct (Reader $annotationsReader, ContainerInterface $container)
    {
        $this->annotationsReader = $annotationsReader;
        $this->container = $container;
    }


    /**
     * Infers the security from the given controller.
     *
     * @param mixed $controller
     *
     * @return string|null the security expression for the given controller
     */
    public function inferSecurity ($controller) : ?string
    {
        // bail if no sensio framework extra bundle is installed
        if (!\class_exists(IsGranted::class) || !\class_exists(Security::class))
        {
            return null;
        }

        try
        {
            if (\is_array($controller))
            {
                return $this->getSecurityForAction($controller[0], $controller[1]);
            }

             if (\is_object($controller))
            {
                if (\method_exists($controller, '__invoke'))
                {
                    return $this->getSecurityForAction($controller, "__invoke");
                }
            }
            elseif (\is_string($controller))
            {
                if (false === \strpos($controller, ':'))
                {
                    if (\method_exists($controller, '__invoke'))
                    {
                        return $this->getSecurityForAction($controller, '__invoke');
                    }

                    return null;
                }

                if (false !== \strpos($controller, '::'))
                {
                    [$class, $method] = \explode('::', $controller, 2);
                    return $this->getSecurityForAction($class, $method);
                }

                 if (false !== \strpos($controller, ':'))
                {
                    [$service, $method] = \explode(':', $controller, 2);

                    if ($this->container->has($service))
                    {
                        return $this->getSecurityForAction(
                            $this->container->get($service),
                            $method
                        );
                    }
                }
            }

            return null;
        }
        catch (ContainerExceptionInterface $e)
        {
            return null;
        }
    }


    /**
     * Returns the security expression for the given action.
     *
     * @param string|object $class
     * @param string        $method
     *
     * @return string|null
     */
    private function getSecurityForAction ($class, string $method) : ?string
    {
        try
        {
            $reflectionMethod = new \ReflectionMethod($class, $method);
            $reflectionClass = new \ReflectionClass($class);

            /**
             * @var Security[]
             */
            $securityAnnotations = [
                $this->annotationsReader->getMethodAnnotation($reflectionMethod, Security::class),
                $this->annotationsReader->getClassAnnotation($reflectionClass, Security::class),
            ];

            /** @var IsGranted[] $isGrantedAnnotations */
            $isGrantedAnnotations = [
                $this->annotationsReader->getMethodAnnotation($reflectionMethod, IsGranted::class),
                $this->annotationsReader->getClassAnnotation($reflectionClass, IsGranted::class),
            ];

            $expressions = [];

            // wire @Security() annotations
            foreach ($securityAnnotations as $securityAnnotation)
            {
                if (null !== $securityAnnotation)
                {
                    $expressions[] = $securityAnnotation->getExpression();
                }
            }

            // wire @IsGranted() annotations
            foreach ($isGrantedAnnotations as $isGrantedAnnotation)
            {
                if (null !== $isGrantedAnnotation)
                {
                    // bail, if a subject is required
                    if (null !== $isGrantedAnnotation->getSubject())
                    {
                        return null;
                    }

                    $expressions[] = "is_granted('{$isGrantedAnnotation->getAttributes()}')";
                }
            }

            if (empty($expressions))
            {
                return null;
            }

            return 1 === \count($expressions)
                ? $expressions[0]
                : "(" . \implode(") and (", $expressions) . ")";
        }
        catch (\ReflectionException $e)
        {
            return null;
        }
    }
}
