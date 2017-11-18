<?php
/**
 * @file
 * Contains \AppBundle\ControllerListener.
 */

namespace AppBundle;

use Amp\Coroutine;
use Amp\Loop;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 *
 */
class GeneratorViewListener implements EventSubscriberInterface
{

    public function onRespond(GetResponseForControllerResultEvent $event)
    {
        $result = $event->getControllerResult();

        if ($result instanceof \Generator) {
            \Amp\asyncCall(function () use ($result, &$return) {
                $return = yield (new Coroutine($result));
                Loop::stop();
            });
            Loop::run();
            $event->setResponse($return);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        $events[KernelEvents::VIEW] = ['onRespond', -10];

        return $events;
    }
}
