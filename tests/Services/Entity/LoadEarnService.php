<?php

namespace App\Tests\Services\Entity;

use App\Services\Entity\Interfaces\EarnServiceInterface;

/**
 * @package App\Tests\Services\Entity
 */
trait LoadEarnService
{
    /**
     * @return EarnServiceInterface
     */
    public function getEarnService():EarnServiceInterface
    {
        self::bootKernel();

        $container = self::$kernel->getContainer();
        $container = self::$container;

        return self::$container->get(EarnServiceInterface::class);
    }
}