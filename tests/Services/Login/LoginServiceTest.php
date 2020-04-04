<?php

namespace App\Tests\Services\Entity;

use App\Entity\ApiToken;
use App\Entity\Interfaces\ApiTokenInterface;
use App\Entity\User;
use App\Services\Login\LoginService;
use App\Tests\Services\Login\LoadLoginService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

/**
 * @package App\Tests\Services\Entity
 */
class LoginServiceTest extends WebTestCase
{
    use LoadLoginService, LoadUserService;

    public function testLoginDataWithoutEmailFail()
    {
        $loginService = $this->getLoginService();
        $this->expectException(UnprocessableEntityHttpException::class);
        $loginService->requestShouldHaveEmailAndPasswordOrFail(["password" => "123456"]);
    }

    public function testLoginDataWithoutPasswordFail()
    {
        $loginService = $this->getLoginService();
        $this->expectException(UnprocessableEntityHttpException::class);
        $loginService->requestShouldHaveEmailAndPasswordOrFail(["email" => "me@me.com"]);
    }

    public function testLoginDataWithoutDataFail()
    {
        $loginService = $this->getLoginService();
        $this->expectException(UnprocessableEntityHttpException::class);
        $loginService->requestShouldHaveEmailAndPasswordOrFail([]);
    }

    public function testCredentialsUserNotRegisteredFail()
    {
        $user = new User();
        $loginService = $this->getLoginService();

        $this->expectException(BadCredentialsException::class);
        $loginService->passwordShouldBeRightOrFail($user, '121212');
    }

    public function testCredentialsPasswordInvalidFail()
    {
        $user = new User();
        $encoder = $this->getUserPasswordEncoder();
        $user->setPassword('101010');
        $user->encryptPassword($encoder);
        $loginService = $this->getLoginService();

        $this->expectException(BadCredentialsException::class);
        $loginService->passwordShouldBeRightOrFail($user, '121212');
    }

    public function testCredentialsValidSuccess()
    {
        $user = new User();
        $encoder = $this->getUserPasswordEncoder();
        $this->assertInstanceOf(UserPasswordEncoderInterface::class, $encoder);
        $user->setPassword('101010');
        $this->assertEquals('101010', $user->getPassword());
        $user->encryptPassword($encoder);

        $loginService = $this->getLoginService();
        $this->assertInstanceOf(LoginService::class, $loginService);

        $this->assertTrue($loginService->passwordShouldBeRightOrFail($user, '101010'));
    }
}