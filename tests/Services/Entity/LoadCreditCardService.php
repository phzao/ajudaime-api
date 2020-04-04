<?php

namespace App\Tests\Services\Entity;

use App\Services\Entity\Interfaces\CreditCardServiceInterface;

/**
 * @package App\Tests\Services\Entity
 */
trait LoadCreditCardService
{
    /**
     * @return CreditCardServiceInterface
     */
    public function getCreditCardService():CreditCardServiceInterface
    {
        self::bootKernel();

        $container = self::$kernel->getContainer();
        $container = self::$container;

        return self::$container->get(CreditCardServiceInterface::class);
    }
}