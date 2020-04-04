<?php

namespace App\Services\External\Google;


use App\Utils\HandleErrors\ErrorMessage;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class GoogleAuthenticateService implements GoogleCheckServiceInterface
{

    public function requestHasNameEmailAndAccessTokenOrFail(array $fields)
    {
        $error = [];

        if (empty($fields["access_token"])) {
            $error["access_token"] = "Access Token is required!";
        }

        if (empty($fields["name"])) {
            $error["name"] = "Name from user is required!";
        }

        if (empty($fields["email"])) {
            $error["email"] = "Email from user is required!";
        }

        if (empty($error)) {
            return true;
        }

        $msg = ErrorMessage::getArrayMessageToJson($error);

        throw new UnprocessableEntityHttpException($msg);
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function isValidGoogleAccessTokenOrFail(array $data)
    {
        $client = HttpClient::create();

        $response = $client->request('POST', 'https://www.googleapis.com/oauth2/v3/tokeninfo?access_token='.$data["access_token"]);

        if ($response->getStatusCode()!==Response::HTTP_OK) {
            throw new BadCredentialsException('Token Not Allowed');
        }

        $content = $response->toArray();

        if ($content['email_verified']!=="true") {
            throw new BadCredentialsException('Email not confirmed');
        }

        if ($content['email']!==$data["email"]) {
            throw new BadCredentialsException('Email Invalid');
        }
    }
}