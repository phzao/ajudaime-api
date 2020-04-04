<?php
namespace App\EventSubscriber;

use Symfony\Component\HttpKernel\Event\ControllerEvent;
use function json_last_error;
use function json_last_error_msg;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class BeforeActionSubscriber
 * @package App\EventSubscriber
 */
class BeforeActionSubscriber implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => 'convertJsonStringToArray',
        );
    }

    /**
     * @param ControllerEvent $event
     */
    public function convertJsonStringToArray(ControllerEvent $event)
    {
        $request = $event->getRequest();
        if ($request->getContentType() != 'json' || !$request->getContent()) {
            return;
        }
        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new BadRequestHttpException('invalid json body: ' . json_last_error_msg());
        }
        $request->request->replace(is_array($data) ? $data : array());
    }
}