<?php

namespace App\Tests\Entity;

use App\Entity\Earn;
use App\Entity\Interfaces\EarnInterface;
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
class EarnTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testEarnEmpty()
    {
        $earn = new Earn();

        $this->assertInstanceOf(ModelInterface::class, $earn);
        $this->assertInstanceOf(SimpleTimeInterface::class, $earn);
        $this->assertInstanceOf(EarnInterface::class, $earn);
        $this->assertInstanceOf(\JsonSerializable::class, $earn);

        $original = array_keys($earn->getOriginalData());
        $originalShouldHave = [
            "id",
            "description",
            "value",
            "confirmed_at",
            "type",
            "created_at",
            "earn_at",
            "place_id",
            "updated_at"
        ];
        $this->assertEquals($original, $originalShouldHave);
        $fullData = array_keys($earn->getFullData());
        $fullDataShouldHave = [
            "id",
            "description",
            "value",
            "confirmed_at",
            "type",
            "type_description",
            "place",
            "created_at",
            "earn_at",
            "updated_at"
        ];

        $this->assertEquals($fullData, $fullDataShouldHave);

        $this->assertIsArray($earn->getFullData());
        $this->assertIsArray($earn->getOriginalData());
        $this->assertEmpty($earn->getId());
        $this->assertNull($earn->getPlace());
        $this->assertEmpty($earn->getPlaceId());
        $this->assertIsArray($earn->getPlaceIDAndDescription());
        $this->assertIsArray($earn->jsonSerialize());
        $this->assertNotEmpty($earn->getDateTimeStringFrom('created_at'));
        $this->assertEmpty($earn->getDateTimeStringFrom('updated_at'));
        $this->assertIsArray($earn->getAllAttributesDateAndFormat());
        $this->assertEmpty($earn->getFullData()["updated_at"]);
        $this->assertEmpty($earn->getDeletedAt());
    }

    /**
     * @throws \Exception
     */
    public function testSetPlace()
    {
        $earn = new Earn();

        $place = new Place();
        $earn->setPlace($place);

        $this->assertNotEmpty($earn->getPlace());
    }

    /**
     * @throws \Exception
     */
    public function testChangeUpdate()
    {
        $earn = new Earn();

        $this->assertEmpty($earn->getDateTimeStringFrom('updated_at'));
        $earn->updateLastUpdated();

        $this->assertNotEmpty($earn->getDateTimeStringFrom('updated_at'));
    }

    /**
     * @throws \Exception
     */
    public function testSetData()
    {
        $earn = new Earn();

        $data = [
            "value" => 10.0,
            "description"=> "extra",
            "type" => "salary",
        ];

        $earn->setAttributes($data);

        $res = $earn->getFullData();

        $this->assertEquals("extra",$res["description"]);
        $this->assertEquals(10.0,$res["value"]);
        $this->assertEquals("salary",$res["type"]);
    }
}