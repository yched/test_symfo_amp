<?php
/**
 * @file
 * Contains \AppBundle\ControllerListener.
 */

namespace AppBundle\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Allows controllers to return generators or promises, and execute them through the Amphp event loop.
 */
class CoroutineControllerListener implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\HttpKernel\Controller\ControllerResolverInterface
     */
    protected $controllerResolver;

    public function __construct(ControllerResolverInterface $controllerResolver)
    {
        $this->controllerResolver = $controllerResolver;
    }

    public function onController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        $arguments = $this->controllerResolver->getArguments($event->getRequest(), $controller);

        $event->setController(function () use ($controller, $arguments) {
            $promise = \Amp\coroutine($controller)(...$arguments);
            return \Amp\Promise\wait($promise);
        });
    }

    public static function getSubscribedEvents()
    {
        $events[KernelEvents::CONTROLLER] = ['onController'];

        return $events;
    }
}
