<?php

namespace App\Tests\Services\Validation;

use App\Services\Validation\ValidationService;

/**
 * Trait LoadValidationService
 * @package App\Tests\Services\Validation
 */
trait LoadValidationService
{
    /**
     * @return ValidationService
     */
    public function getValidationService(): ValidationService
    {
        self::bootKernel();

        $container = self::$kernel->getContainer();
        $container = self::$container;
        return self::$container->get(ValidationService::class);
    }
}