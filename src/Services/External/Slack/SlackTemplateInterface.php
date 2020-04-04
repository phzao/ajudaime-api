<?php

namespace App\Services\External\Slack;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @package App\Services\External\Slack
 */
interface SlackTemplateInterface
{
    public function getTemplate(): array;

    public function setTime(string $time);

    public function setContent($exception, $user, RequestStack $request): void;
}