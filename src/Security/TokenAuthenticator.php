<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\Interfaces\ApiTokenRepositoryInterface;
use App\Services\Entity\Interfaces\ApiTokenServiceInterface;
use App\Services\Entity\Interfaces\UserServiceInterface;
use App\Utils\Datetime\Interfaces\DatetimeCheckServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

/**
 * @package App\Security
 */
class TokenAuthenticator extends AbstractGuardAuthenticator
{
    private $tokenService;

    private $datetimeCheck;

    private $userService;

    private $msg;

    public function __construct(ApiTokenServiceInterface $apiTokenService,
                                UserServiceInterface $userService,
                                DatetimeCheckServiceInterface $datetimeCheckService)
    {
        $this->tokenService = $apiTokenService;
        $this->datetimeCheck = $datetimeCheckService;
        $this->userService = $userService;
        $this->msg = "This token is invalid!";
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning false will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request)
    {
        return $request->headers->has('Authorization');
    }

    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser() as $credentials.
     */
    public function getCredentials(Request $request)
    {
        return [
            'token' => $request->headers->get('Authorization'),
        ];
    }

    private function setMessageError(string $string)
    {
        $this->msg = $string;
    }

    /**
     * @throws \Exception
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $apiToken = $credentials['token'];

        if (null === $apiToken) {
            return;
        }

        $apiToken = $this->tokenService->getTokenNotExpiredBy($apiToken);

        if (empty($apiToken)) {
            return;
        }

        $userData = $this->userService->getUserById($apiToken["user"]);
        $user = new User();

        $user->setAttributes($userData);

        if (!$user->canAuthenticate()) {
            $this->setMessageError("User cannot authenticate!");
            return;
        }

        if ($this->datetimeCheck->stringDatetimeIsGreaterThanNow($apiToken["expired_at"])) {
            return $user;
        }

        $this->tokenService->invalidateToken($apiToken);
        $this->setMessageError("Token expired!");

        return ;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // check credentials - e.g. make sure the password is valid
        // no credential check is needed in this case

        // return true to cause authentication success
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
//        $data = [
////            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
//            // or to translate this message
//            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
//        ];


        return new JsonResponse(["message" => $this->msg],
                                Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed, but it's not sent
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = [
            // you might translate this message
            'message' => 'Authentication Required'
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}