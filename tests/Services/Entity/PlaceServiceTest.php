<?php

namespace App\Tests\Services\Entity;

use Doctrine\DBAL\Types\ConversionException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @package App\Tests\Services\Entity
 */
class PlaceServiceTest extends WebTestCase
{
    use LoadPlaceService;

    public function testGetListAllEmpty()
    {
        $placeService = $this->getPlaceService();

        $result = $placeService->getListAllByUser("491f6278-828e-4f5f-8a48-e11ea346902a");
        $this->assertEmpty($result);
    }

    public function testGetCategoryByInvalidUUIDShouldFail()
    {
        $placeService = $this->getPlaceService();
        $this->expectException(ConversionException::class);
        $placeService->getPlaceFromUserByIdOrFail('asdasd', 'sadasasda');
    }

    public function testGetCategoryByUserInvalidUUIDShouldFail()
    {
        $placeService = $this->getPlaceService();
        $this->expectException(ConversionException::class);
        $placeService->getPlaceFromUserByIdOrFail('asdasd', "491f6278-828e-4f5f-8a48-e11ea346902a");
    }

    public function testGetCategoryByIdAndUserThatNotExistShouldFail()
    {
        $placeService = $this->getPlaceService();
        $this->expectException(NotFoundHttpException::class);
        $placeService->getPlaceFromUserByIdOrFail("491f6278-828e-4f5f-8a48-e11ea346902a", "491f6278-828e-4f5f-8a48-e11ea346902a");
    }

    public function testGetCategoryByIdFail()
    {
        $placeService = $this->getPlaceService();
        $data["place"] ="491f6278-828e-4f5f-8a48-e11ea346902a";
        $this->expectException(NotFoundHttpException::class);
        $placeService->getPlaceIfWasPassedOrFail($data, "491f6278-828e-4f5f-8a48-e11ea346902a");
    }
}