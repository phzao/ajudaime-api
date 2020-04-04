<?php

namespace App\Tests\Services\Login;

use App\Services\Login\LoginService;
use App\Services\Login\LoginServiceInterface;

/**
 * @package App\Tests\Services\Login
 */
trait LoadLoginService
{
    /**
     * @return LoginService
     */
    public function getLoginService(): LoginService
    {
        self::bootKernel();

        $container = self::$kernel->getContainer();
        $container = self::$container;

        return self::$container->get(LoginServiceInterface::class);
    }
}