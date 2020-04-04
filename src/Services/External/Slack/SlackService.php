<?php

namespace App\Services\External\Slack;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

/**
 * @package App\Services\External\Slack
 */
class SlackService implements SlackServiceInterface
{
    private $hook_url;

    /**
     * @var SlackTemplateInterface
     */
    private $slackTemplate;

    /**
     * @param SlackTemplateInterface $slackTemplate
     */
    public function __construct(SlackTemplateInterface $slackTemplate)
    {
        $this->slackTemplate = $slackTemplate;
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function sendMessage(SlackTemplateInterface $slackTemplate)
    {
        try {
            $client = HttpClient::create();

            $response = $client->request('POST', $this->hook_url, ["json"=>$this->slackTemplate->getTemplate()]);

            if ($response->getStatusCode()!==Response::HTTP_OK) {
                throw new BadCredentialsException('Slack Error');
            }

        } catch (\Exception $e) {
            return 'error';
        }
    }

    public function setBackendErrorChannel(): void
    {
        $this->hook_url = $_ENV["SLACK_TOKEN"];
    }
}