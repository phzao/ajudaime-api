<?php

namespace App\Services\External\Slack;

/**
 * @package App\Services\External\Slack
 */
interface SlackServiceInterface
{
    public function setBackendErrorChannel(): void;

    public function sendMessage(SlackTemplateInterface $slackTemplate);
}