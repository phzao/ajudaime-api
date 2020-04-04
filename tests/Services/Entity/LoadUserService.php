<?php

namespace App\Tests\Services\Entity;

use App\Services\Entity\Interfaces\UserServiceInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Trait LoadUserService
 * @package App\Tests\Services\Entity
 */
trait LoadUserService
{
    /**
     * @return UserServiceInterface
     */
    public function getUserService():UserServiceInterface
    {
        self::bootKernel();

        $container = self::$kernel->getContainer();
        $container = self::$container;

        return self::$container->get(UserServiceInterface::class);
    }

    /**
     * @return UserPasswordEncoderInterface
     */
    public function getUserPasswordEncoder(): UserPasswordEncoderInterface
    {
        self::bootKernel();

        $container = self::$kernel->getContainer();
        $container = self::$container;
        return self::$container->get(UserPasswordEncoderInterface::class);
    }
}