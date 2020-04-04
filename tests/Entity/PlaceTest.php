<?php

namespace App\Tests\Entity;

use App\Entity\Interfaces\ModelInterface;
use App\Entity\Interfaces\PlaceInterface;
use App\Entity\Interfaces\ReadUserOutsideInterface;
use App\Entity\Interfaces\SimpleTimeInterface;
use App\Entity\Place;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * @package App\Tests\Entity
 */
class PlaceTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testPlaceEmpty()
    {
        $place = new Place();

        $this->assertInstanceOf(ModelInterface::class, $place);
        $this->assertInstanceOf(SimpleTimeInterface::class, $place);
        $this->assertInstanceOf(PlaceInterface::class, $place);
        $this->assertInstanceOf(\JsonSerializable::class, $place);
        $this->assertInstanceOf(ReadUserOutsideInterface::class, $place);

        $original = array_keys($place->getOriginalData());
        $originalShouldHave = [
            "id",
            "user_id",
            "description",
            "is_default",
            "created_at",
            "updated_at",
            "deleted_at",
            "status"
        ];
        $this->assertEquals($original, $originalShouldHave);
        $fullData = array_keys($place->getFullData());
        $fullDataShouldHave = [
            "id",
            "user_id",
            "description",
            "created_at",
            "updated_at",
            "deleted_at",
            "status",
            "is_default",
            "is_default_description",
            "status_description"
        ];

        $this->assertEquals($fullData, $fullDataShouldHave);

        $this->assertIsArray($place->getFullData());
        $this->assertIsArray($place->getOriginalData());
        $this->assertEmpty($place->getId());
        $this->assertEmpty($place->getUser());
        $this->assertEmpty($place->getExpenses());
        $this->assertIsArray($place->getIdAndDescription());
        $this->assertIsArray($place->jsonSerialize());
        $this->assertNotEmpty($place->getDateTimeStringFrom('created_at'));
        $this->assertEmpty($place->getDateTimeStringFrom('updated_at'));
        $this->assertIsArray($place->getNameAndIdUser('user'));
        $this->assertEmpty($place->getFullData()["updated_at"]);
        $this->assertEmpty($place->getDeletedAt());
    }

    /**
     * @throws \Exception
     */
    public function testChangePlace()
    {
        $place = new Place();

        $this->assertEmpty($place->getDateTimeStringFrom('updated_at'));
        $place->updateLastUpdated();

        $this->assertNotEmpty($place->getDateTimeStringFrom('updated_at'));

        $user = new User();
        $place->setUser($user);
        $place->remove();

        $fullData = $place->getFullData();

        $this->assertEmpty($fullData["user_id"]);

        $original = $place->getOriginalData();

        $this->assertNotEmpty($original["deleted_at"]);
    }
}