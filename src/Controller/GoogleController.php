<?php

namespace App\Controller;

use App\Services\Login\LoginService;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @package App\Controller
 */
class GoogleController extends APIController
{
    /**
     * Link to this controller to start the "connect" process
     *
     * @Route("/oauth-authenticate", name="connect_google")
     * @param ClientRegistry $clientRegistry
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function connectAction(ClientRegistry $clientRegistry)
    {
        return $clientRegistry->getClient('google')->redirect();
    }
    /**
     * Facebook redirects to back here afterwards
     *
     * @Route("/connect/google/check", name="connect_google_check")
     * @param Request      $request
     * @param LoginService $loginService
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function connectCheckAction(Request $request, LoginService $loginService)
    {
        if (!$this->getUser()) {
            return new JsonResponse(['status'  => false, 'message' => "User not found!"]);
        }

        $user      = $this->getUser();
        $loginData = $loginService->getTokenCreateIfNotExist($user);

        return $this->respond($loginData->getDetailsToken());
    }
}