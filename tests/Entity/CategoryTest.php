<?php

namespace App\Tests\Entity;

use App\Entity\Category;
use App\Entity\Interfaces\CategoryInterface;
use App\Entity\Interfaces\ModelInterface;
use App\Entity\Interfaces\ReadUserOutsideInterface;
use App\Entity\Interfaces\SimpleTimeInterface;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * @package App\Tests\Entity
 */
class CategoryTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testEmptyCategory()
    {
        $category = new Category();

        $this->assertInstanceOf(CategoryInterface::class, $category);
        $this->assertInstanceOf(ModelInterface::class, $category);
        $this->assertInstanceOf(SimpleTimeInterface::class, $category);
        $this->assertInstanceOf(ReadUserOutsideInterface::class, $category);
        $this->assertInstanceOf(\JsonSerializable::class, $category);

        $this->assertIsArray($category->getFullData());
        $this->assertCount(10, $category->getFullData());
        $this->assertCount(8, $category->getOriginalData());

        $original = array_keys($category->getOriginalData());
        $originalShouldHave = [
            "id",
            "description",
            "status",
            "user_id",
            "is_default",
            "created_at",
            "updated_at",
            "deleted_at"
        ];
        $this->assertEquals($original, $originalShouldHave);
        $fullData = array_keys($category->getFullData());
        $fullDataShouldHave = [
            "id",
            "description",
            "status",
            "status_description",
            "user_id",
            "is_default",
            "is_default_description",
            "created_at",
            "updated_at",
            "deleted_at"
        ];
        $this->assertEquals($fullData, $fullDataShouldHave);

        $this->assertIsArray($category->getOriginalData());

        $this->assertEmpty($category->getId());
        $this->assertEmpty($category->getUser());
        $this->assertEmpty($category->getIdUser('user'));
        $this->assertEmpty($category->getExpenses());
        $this->assertIsArray($category->jsonSerialize());
        $this->assertNotEmpty($category->getDateTimeStringFrom('created_at'));
        $this->assertEmpty($category->getDateTimeStringFrom('updated_at'));
        $this->assertIsArray($category->getNameAndIdUser('user'));
        $this->assertEmpty($category->getFullData()["updated_at"]);
        $this->assertEmpty($category->getDeletedAt());
    }

    /**
     * @throws \Exception
     */
    public function testChangeCategory()
    {
        $category = new Category();
        $data = [
            "description" => "Car"
        ];
        $category->setAttributes($data);

        $this->assertEmpty($category->getDateTimeStringFrom('updated_at'));
        $category->updateLastUpdated();

        $this->assertNotEmpty($category->getDateTimeStringFrom('updated_at'));

        $user = new User();

        $category->setUser($user);
        $category->remove();

        $fullData = $category->getFullData();

        $this->assertEquals("Car", $fullData["description"]);
        $this->assertEmpty($fullData["user_id"]);

        $original = $category->getOriginalData();

        $this->assertEquals("Car", $original["description"]);
        $this->assertNotEmpty($original["deleted_at"]);
    }
}