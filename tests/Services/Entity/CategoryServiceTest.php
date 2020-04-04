<?php

namespace App\Tests\Services\Entity;

use Doctrine\DBAL\Types\ConversionException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @package App\Tests\Services\Entity
 */
class CategoryServiceTest extends WebTestCase
{
    use LoadCategoryService;

    public function testGetListAllEmpty()
    {
        $categoryService = $this->getCategoryService();

        $result = $categoryService->getListAllByUser("491f6278-828e-4f5f-8a48-e11ea346902a");
        $this->assertEmpty($result);
    }

    public function testGetCategoryByInvalidUUIDShouldFail()
    {
        $categoryService = $this->getCategoryService();
        $this->expectException(ConversionException::class);
        $categoryService->getCategoryFromUserByIdOrFail('asdasd', 'sadasasda');
    }

    public function testGetCategoryByUserInvalidUUIDShouldFail()
    {
        $categoryService = $this->getCategoryService();
        $this->expectException(ConversionException::class);
        $categoryService->getCategoryFromUserByIdOrFail('asdasd', "491f6278-828e-4f5f-8a48-e11ea346902a");
    }

    public function testGetCategoryByIdAndUserThatNotExistShouldFail()
    {
        $categoryService = $this->getCategoryService();
        $this->expectException(NotFoundHttpException::class);
        $categoryService->getCategoryFromUserByIdOrFail("491f6278-828e-4f5f-8a48-e11ea346902a", "491f6278-828e-4f5f-8a48-e11ea346902a");
    }

    public function testGetCategoryByIdFail()
    {
        $categoryService = $this->getCategoryService();
        $this->expectException(NotFoundHttpException::class);
        $categoryService->getCategoryByIdOrFail("491f6278-828e-4f5f-8a48-e11ea346902a");
    }
}