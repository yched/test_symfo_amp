<?php
/**
 * @file
 * Contains \AppBundle\ControllerListener.
 */

namespace AppBundle;

use Amp\Coroutine;
use Amp\Promise;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Allows controllers to return generators, and execute them as Amphp coroutines.
 */
class CoroutineViewListener implements EventSubscriberInterface
{

    public function onRespond(GetResponseForControllerResultEvent $event)
    {
        $result = $event->getControllerResult();

        if ($result instanceof \Generator) {
            $result = Promise\wait(new Coroutine($result));
            $event->setResponse($result);
        }
        else if ($result instanceof Promise) {
            $result = Promise\wait($result);
            $event->setResponse($result);
        }
    }

    public static function getSubscribedEvents()
    {
        $events[KernelEvents::VIEW] = ['onRespond', -10];

        return $events;
    }
}
