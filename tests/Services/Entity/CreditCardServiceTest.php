<?php

namespace App\Tests\Services\Entity;

use Doctrine\DBAL\Types\ConversionException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @package App\Tests\Services\Entity
 */
class CreditCardServiceTest extends WebTestCase
{
    use LoadCreditCardService;

    public function testGetListAllEmpty()
    {
        $cardService = $this->getCreditCardService();

        $result = $cardService->getListAllByUser("491f6278-828e-4f5f-8a48-e11ea346902a");
        $this->assertEmpty($result);
    }

    public function testGetCreditCardByInvalidUUIDShouldFail()
    {
        $cardService = $this->getCreditCardService();
        $this->expectException(ConversionException::class);
        $cardService->getCreditCardFromUserByIdOrFail('asdasd', 'sadasasda');
    }

    public function testGetCreditCardByUserInvalidUUIDShouldFail()
    {
        $cardService = $this->getCreditCardService();
        $this->expectException(ConversionException::class);
        $cardService->getCreditCardFromUserByIdOrFail('asdasd', "491f6278-828e-4f5f-8a48-e11ea346902a");
    }

    public function testGetCreditCardByIdAndUserThatNotExistShouldFail()
    {
        $cardService = $this->getCreditCardService();
        $this->expectException(NotFoundHttpException::class);
        $cardService->getCreditCardFromUserByIdOrFail("491f6278-828e-4f5f-8a48-e11ea346902a", "491f6278-828e-4f5f-8a48-e11ea346902a");
    }
}