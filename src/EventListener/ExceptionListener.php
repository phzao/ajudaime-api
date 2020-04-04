<?php

namespace App\EventListener;

use App\Services\External\Slack\SlackServiceInterface;
use App\Services\External\Slack\SlackTemplateInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @package App\EventListener
 */
class ExceptionListener
{
    private $user;

    private $slackTemplate;

    private $slackService;

    private $requestData;

    public function __construct(?UserInterface $user,
                                RequestStack $request,
                                SlackTemplateInterface $slackTemplate,
                                SlackServiceInterface $slackService)
    {
        $this->user = $user;
        $this->slackTemplate = $slackTemplate;
        $this->slackService = $slackService;
        $this->requestData = $request;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        /* @var $exception */
        $exception = $event->getThrowable();

        if ($_ENV["APP_ENV"]==="dev") {
            return;
        }

        $this->slackTemplate->setContent($exception, $this->user, $this->requestData);
        $this->slackService->setBackendErrorChannel();
        $this->slackService->sendMessage($this->slackTemplate);
    }
}