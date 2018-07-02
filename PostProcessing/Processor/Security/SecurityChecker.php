<?php declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\PostProcessing\Processor\Security;

use Sensio\Bundle\FrameworkExtraBundle\EventListener\SecurityListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FilterControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


class SecurityChecker
{
    /**
     * @var SecurityListener
     */
    private $securityListener;


    /**
     * @var KernelInterface
     */
    private $kernel;


    /**
     *
     * @param SecurityListener $securityListener
     * @param KernelInterface  $kernel
     */
    public function __construct (SecurityListener $securityListener, KernelInterface $kernel)
    {
        $this->securityListener = $securityListener;
        $this->kernel = $kernel;
    }


    /**
     * Returns whether the current user can access with the given expression
     *
     * @param string $expression
     * @return bool
     */
    public function canAccess (string $expression) : bool
    {
        try
        {
            // create a fake request and a fake event, as we only need to fill the values,
            // the SecurityListener from the FrameworkExtraBundle actually uses.
            $request = new Request([], [], [
                "_security" => [$expression],
            ]);

            // create a fake event -> we only care about the request arguments
            $fakeEvent = new FilterControllerArgumentsEvent(
                $this->kernel,
                function () {},
                [],
                $request,
                null
            );

            $this->securityListener->onKernelControllerArguments($fakeEvent);

            // no exception was thrown, so allow access
            return true;
        }
        catch (HttpException | AccessDeniedException $exception)
        {
            // an exception was thrown, so access was denied
            return false;
        }
    }
}
