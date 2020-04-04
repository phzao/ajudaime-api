<?php

namespace App\Tests\Services\Entity;

use App\Entity\User;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @package App\Tests\Services\Entity
 */
class UserServiceTest extends WebTestCase
{
    use LoadUserService;

    public function testUserNotExist()
    {
        $userService = $this->getUserService();
        $this->expectException(EntityNotFoundException::class);
        $userService->getUserByIdOrFail("491f6278-828e-4f5f-8a48-e11ea346902a");
    }

    /**
     * @throws \Exception
     */
    public function testUpdateUserWithoutEmailShouldFail()
    {
        $userService = $this->getUserService();
        $user = new User();
        $this->expectException(NotNullConstraintViolationException::class);
        $userService->updateStatus($user, "enable");
    }

    public function testUpdateSuccess()
    {
        $userService = $this->getUserService();
        $data = ["email"=>"me@me.com", "password"=>"123456"];
        $user = $userService->register($data);
        $userService->updateStatus($user, "disable");
        $this->assertIsString('disable', $user->getStatus());
    }

    public function testRegisterFail()
    {
        $userService = $this->getUserService();
        $this->expectException(NotNullConstraintViolationException::class);
        $userService->register([]);
    }

    public function testFail()
    {
        $userService = $this->getUserService();

        $user = $userService->getUserByEmail("eu@eu.com");
        $this->assertNull($user);
        /** success */

        $request = ["email" => "my@my.com"];

        /** @var User */
        $user = $userService->register($request);

        $this->assertInstanceOf(User::class, $user);
        $this->assertIsString($user->getId());
        $this->assertCount(7, $user->getFullData());

        $findByEmail = $userService->getUserByEmail("my@my.com");
        $this->assertInstanceOf(User::class, $findByEmail);
        $this->assertEquals($user->getFullData(), $findByEmail->getFullData());

        $userExist = $userService->getUserByIdOrFail($user->getId());
        $this->assertInstanceOf(User::class, $userExist);
        $this->assertCount(7, $userExist->getFullData());
        $this->assertEquals($user->getFullData(), $userExist->getFullData());
    }

    public function testSuccess()
    {
        $userService = $this->getUserService();

        $user = $userService->getUserByEmail("eu@eu.com");
        $this->assertNull($user);
        /** success */

        $request = ["email" => "my@my.com"];

        /** @var User */
        $user = $userService->register($request);

        $this->assertInstanceOf(User::class, $user);
        $this->assertIsString($user->getId());
        $this->assertCount(7, $user->getFullData());

        $findByEmail = $userService->getUserByEmail("my@my.com");
        $this->assertInstanceOf(User::class, $findByEmail);
        $this->assertEquals($user->getFullData(), $findByEmail->getFullData());

        $userExist = $userService->getUserByIdOrFail($user->getId());
        $this->assertInstanceOf(User::class, $userExist);
        $this->assertCount(7, $userExist->getFullData());
        $this->assertEquals($user->getFullData(), $userExist->getFullData());
    }
}