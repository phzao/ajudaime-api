<?php

namespace App\Tests\Services\Entity;

use App\Services\Entity\Interfaces\PlaceServiceInterface;

/**
 * @package App\Tests\Services\Entity
 */
trait LoadPlaceService
{
    /**
     * @return PlaceServiceInterface
     */
    public function getPlaceService():PlaceServiceInterface
    {
        self::bootKernel();

        $container = self::$kernel->getContainer();
        $container = self::$container;

        return self::$container->get(PlaceServiceInterface::class);
    }
}