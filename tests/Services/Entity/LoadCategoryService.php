<?php

namespace App\Tests\Services\Entity;

use App\Services\Entity\Interfaces\CategoryServiceInterface;

/**
 * @package App\Tests\Services\Entity
 */
trait LoadCategoryService
{
    /**
     * @return CategoryServiceInterface
     */
    public function getCategoryService():CategoryServiceInterface
    {
        self::bootKernel();

        $container = self::$kernel->getContainer();
        $container = self::$container;

        return self::$container->get(CategoryServiceInterface::class);
    }
}