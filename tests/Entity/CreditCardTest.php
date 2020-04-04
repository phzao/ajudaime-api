<?php

namespace App\Tests\Entity;

use App\Entity\CreditCard;
use App\Entity\Earn;
use App\Entity\Interfaces\CreditCardInterface;
use App\Entity\Interfaces\ModelInterface;
use App\Entity\Interfaces\ReadUserOutsideInterface;
use App\Entity\Interfaces\SimpleTimeInterface;
use App\Entity\Place;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * @package App\Tests\Entity
 */
class CreditCardTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testCreditCardEmpty()
    {
        $earn = new CreditCard();

        $this->assertInstanceOf(ModelInterface::class, $earn);
        $this->assertInstanceOf(SimpleTimeInterface::class, $earn);
        $this->assertInstanceOf(CreditCardInterface::class, $earn);
        $this->assertInstanceOf(\JsonSerializable::class, $earn);
        $this->assertInstanceOf(ReadUserOutsideInterface::class, $earn);

        $original = array_keys($earn->getOriginalData());
        $originalShouldHave = [
            "id",
            "status",
            "description",
            "last_digits",
            "due_date",
            "card_banner",
            "is_default",
            "limit_value",
            "validity",
            "created_at",
            "updated_at",
            "deleted_at",
            "user_id"
        ];
        $this->assertEquals($original, $originalShouldHave);
        $fullData = array_keys($earn->getFullData());
        $fullDataShouldHave = [
            "id",
            "status",
            "status_description",
            "description",
            "last_digits",
            "due_date",
            "card_banner",
            "is_default",
            "is_default_description",
            "limit_value",
            "validity",
            "created_at",
            "updated_at",
            "deleted_at",
            "user_id"
        ];

        $this->assertEquals($fullData, $fullDataShouldHave);

        $this->assertIsArray($earn->getFullData());
        $this->assertIsArray($earn->getOriginalData());
        $this->assertEmpty($earn->getId());
        $this->assertNull($earn->getUser());
        $this->assertIsArray($earn->getAllAttributesDateAndFormat());
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
    public function testSetUser()
    {
        $creditCard = new CreditCard();

        $user = new User();
        $creditCard->setUser($user);

        $this->assertNotEmpty($creditCard->getUser());
    }

    /**
     * @throws \Exception
     */
    public function testChangeUpdate()
    {
        $creditCard = new CreditCard();

        $this->assertEmpty($creditCard->getDateTimeStringFrom('updated_at'));
        $creditCard->updateLastUpdated();

        $this->assertNotEmpty($creditCard->getDateTimeStringFrom('updated_at'));
    }

    /**
     * @throws \Exception
     */
    public function testSetData()
    {
        $creditCard = new CreditCard();

        $data = [
            "limit_value" => 10.0,
            "description"=> "my card",
            "card_banner" => "visa",
            "due_date" => 10,
            "validity" => "2020-10-01",
            "last_digits" => 1000,
        ];

        $creditCard->setAttributes($data);

        $res = $creditCard->getFullData();

        $this->assertEquals("my card", $res["description"]);
        $this->assertEquals(10.0, $res["limit_value"]);
        $this->assertEquals("visa",$res["card_banner"]);
        $this->assertEquals(10,$res["due_date"]);
        $this->assertEquals("2020-10-01",$res["validity"]);
        $this->assertEquals(1000,$res["last_digits"]);

    }

    /**
     * @throws \Exception
     */
    public function testChangeStatusToDisable()
    {
        $creditCard = new CreditCard();
        $res = $creditCard->getFullData();
        $this->assertEquals("enable", $res["status"]);

        $creditCard->changeStatusAndSetNoDefaultIfNecessary('disable');

        $res = $creditCard->getFullData();
        $this->assertEquals("disable", $res["status"]);
    }

    /**
     * @throws \Exception
     */
    public function testChangeStatusToEnable()
    {
        $creditCard = new CreditCard();
        $res = $creditCard->getFullData();
        $this->assertEquals("enable", $res["status"]);

        $creditCard->changeStatusAndSetNoDefaultIfNecessary('disable');

        $res = $creditCard->getFullData();
        $this->assertEquals("disable", $res["status"]);

        $creditCard->changeStatusAndSetNoDefaultIfNecessary('enable');

        $res = $creditCard->getFullData();
        $this->assertEquals("enable", $res["status"]);
    }

    /**
     * @throws \Exception
     */
    public function testIsDefaultToDefault()
    {
        $creditCard = new CreditCard();
        $res = $creditCard->getFullData();
        $this->assertEquals(false, $res["is_default"]);

        $creditCard->setDefaultAndEnableIfIsDisable();

        $res = $creditCard->getFullData();
        $this->assertEquals(true, $res["is_default"]);
    }

    /**
     * @throws \Exception
     */
    public function testIsDefaultToDefaultShouldEnableIfWasDisable()
    {
        $creditCard = new CreditCard();
        $res = $creditCard->getFullData();
        $this->assertEquals(false, $res["is_default"]);
        $this->assertEquals('enable', $res["status"]);

        $creditCard->changeStatusAndSetNoDefaultIfNecessary('disable');

        $res = $creditCard->getFullData();
        $this->assertEquals("disable", $res["status"]);

        $creditCard->setDefaultAndEnableIfIsDisable();

        $res = $creditCard->getFullData();
        $this->assertEquals(true, $res["is_default"]);
        $this->assertEquals('enable', $res["status"]);
    }

    /**
     * @throws \Exception
     */
    public function testSetDisableShouldTurnOnNonDefaultIfWasDefault()
    {
        $creditCard = new CreditCard();
        $res = $creditCard->getFullData();
        $this->assertEquals(false, $res["is_default"]);
        $this->assertEquals('enable', $res["status"]);

        $creditCard->setDefaultAndEnableIfIsDisable();

        $res = $creditCard->getFullData();
        $this->assertEquals(true, $res["is_default"]);
        $this->assertEquals('enable', $res["status"]);

        $creditCard->changeStatusAndSetNoDefaultIfNecessary('disable');

        $res = $creditCard->getFullData();
        $this->assertEquals("disable", $res["status"]);
        $this->assertEquals(false, $res["is_default"]);
    }
}