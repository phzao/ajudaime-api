<?php

namespace App\Services\Login;

use App\Entity\User;
use App\Services\Entity\Interfaces\ApiTokenServiceInterface;
use App\Utils\HandleErrors\ErrorMessage;
use Symfony\Component\Config\Definition\Exception\ForbiddenOverwriteException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @package App\Services\Entity
 */
final class LoginService implements LoginServiceInterface
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var ApiTokenServiceInterface
     */
    private $apiTokenService;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder,
                                ApiTokenServiceInterface $apiTokenService)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->apiTokenService = $apiTokenService;
    }

    public function requestShouldHaveEmailAndPasswordOrFail(array $data)
    {
        $error = [];

        if (empty($data["email"])) {
            $error["email"] = "A email is required to login!";
        }

        if (empty($data["password"])) {
            $error["password"] = "A password is required to login!";
        }

        if (empty($error)) {
            return ;
        }

        $msg = ErrorMessage::getArrayMessageToJson($error);

        throw new UnprocessableEntityHttpException($msg);
    }

    public function userShouldCanAuthenticateOrFail(User $user)
    {
        if (!$user->canAuthenticate()) {
            throw new ForbiddenOverwriteException("This user don't have permission to login!");
        }
    }

    public function passwordShouldBeRightOrFail($user, string $password)
    {
        if ($user instanceof UserInterface &&
            $this->passwordEncoder->isPasswordValid($user, $password)) {
            return true;
        }

        $msg = "The password is wrong!";

        if (!$user instanceof UserInterface) {
            $msg = "The email is wrong!";
        }

        throw new BadCredentialsException($msg);
    }

    public function getTokenCreateIfNotExist(array $user, array $data): array
    {
        $token = $this->apiTokenService->getAValidApiTokenToUser($user["id"]);

        if (!empty($token)) {
            return $token;
        }

        if (!empty($data["geolocalization"])) {
            $data = $data["geolocalization"];
        }

        $apiToken = $this->apiTokenService->registerAndGetApiTokenTo($user["id"], $data);

        return $apiToken;
    }

    public function checkIfExistPasswordAndIsItRightOrFail($user, array $data)
    {
       if (!empty($data["password"])) {
           $this->passwordShouldBeRightOrFail($user, $data["password"]);

       } else {

           throw new BadCredentialsException("The password is wrong!");
       }
    }
}