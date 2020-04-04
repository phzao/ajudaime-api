<?php

namespace App\Services\External\Slack;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @package App\Services\External\Slack
 */
class SlackApproval implements SlackTemplateInterface
{
    private $now;
    private $messageData;

    public function __construct()
    {
        $this->now = date("d-m-Y H:i:s");
    }

    private function getHeader(): array
    {
        $string = ":newspaper:* <fakeLink.toEmployeeProfile.com|" . $this->messageData["code"];
        $string .= " - " . $this->messageData["exception"] . ">*";

        return [
            "type" => "section",
            "text" => [
                "type" => "mrkdwn",
                "text" => $string
            ]
        ];
    }

    public function setTime(string $time)
    {
        $this->now = $time;
    }

    private function getTime(): array
    {
        return [
            "type"     => "context",
            "elements" => [
                [
                    "type" => "mrkdwn",
                    "text" => "*" . $this->now . "*  |  line " . $this->messageData["line"]
                ]
            ]
        ];
    }

    private function getDivider(): array
    {
        return [
            "type" => "divider"
        ];
    }

    private function getWhoHeader(): array
    {
        return [
            "type" => "section",
            "text" => [
                "type" => "mrkdwn",
                "text" => ":pushpin: *Who*\n"
            ]
        ];
    }

    private function getMessageHeader(): array
    {
        return [
            "type" => "section",
            "text" => [
                "type" => "mrkdwn",
                "text" => ":pushpin: *Message*\n"
            ]
        ];
    }

    private function getWhereHeader(): array
    {
        return [
            "type" => "section",
            "text" => [
                "type" => "mrkdwn",
                "text" => ":pushpin: *Where*\n"
            ]
        ];
    }

    private function getWho(): array
    {
        $string = "Login: *" . $this->messageData["login"] . "*\n";
        $string .= "Rota: *" . $this->messageData["url_route"] . "*\n";

        return [
            "type" => "section",
            "text" => [
                "type" => "mrkdwn",
                "text" => $string
            ]
        ];
    }

    private function getMessage(): array
    {
        return [
            "type" => "section",
            "text" => [
                "type" => "mrkdwn",
                "text" => "*" . $this->messageData["message"] . "*"
            ]
        ];
    }

    private function getWhere(): array
    {
        return [
            "type" => "section",
            "text" => [
                "type" => "mrkdwn",
                "text" => "*" . $this->messageData["file"] . "*"
            ]
        ];
    }

    public function getTemplate(): array
    {
        $template["blocks"] = [
            $this->getHeader(),
            $this->getTime(),
            $this->getDivider(),
            $this->getWhereHeader(),
            $this->getWhere(),
            $this->getDivider(),
            $this->getMessageHeader(),
            $this->getMessage(),
            $this->getDivider(),
            $this->getWhoHeader(),
            $this->getWho()
        ];

        return $template;
    }

    public function setContent($exception, $user, RequestStack $request): void
    {
        $class = explode("\\",get_class($exception));

        $login = ($user instanceof UserInterface)? $user->getEmail():'not registered';
        $dataRequest = $request->getCurrentRequest();

        $this->messageData = [
            "file" => $exception->getFile(),
            "line" => $exception->getLine(),
            "message" => $exception->getMessage(),
            "code"  => $exception->getCode(),
            "login" => $login,
            "exception" => end($class),
            "url_route" => $dataRequest->getRequestUri()
        ];
    }
}