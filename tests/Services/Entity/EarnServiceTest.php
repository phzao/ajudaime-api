<?php

namespace App\Tests\Services\Entity;

use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @package App\Tests\Services\Entity
 */
class EarnServiceTest extends WebTestCase
{
    use LoadEarnService;

    public function testGetListAllEmpty()
    {
        $earnService = $this->getEarnService();

        $result = $earnService->getListAllByUser("491f6278-828e-4f5f-8a48-e11ea346902a");
        $this->assertEmpty($result);
    }

    public function testGetEarnByInvalidUUIDShouldFail()
    {
        $earnService = $this->getEarnService();
        $this->expectException(ConversionException::class);
        $earnService->getEarnFromUserByIdOrFail('asdasd', 'sadasasda');
    }

    public function testGetEarnByUserInvalidUUIDShouldFail()
    {
        $earnService = $this->getEarnService();
        $this->expectException(ConversionException::class);
        $earnService->getEarnFromUserByIdOrFail('asdasd', "491f6278-828e-4f5f-8a48-e11ea346902a");
    }

    public function testGetEarnByIdAndUserThatNotExistShouldFail()
    {
        $earnService = $this->getEarnService();
        $this->expectException(NotFoundHttpException::class);
        $earnService->getEarnFromUserByIdOrFail("491f6278-828e-4f5f-8a48-e11ea346902a", "491f6278-828e-4f5f-8a48-e11ea346902a");
    }
}