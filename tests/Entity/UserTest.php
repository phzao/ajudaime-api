<?php

namespace App\Tests\Entity;

use App\Entity\Interfaces\ModelInterface;
use App\Entity\Interfaces\SimpleTimeInterface;
use App\Entity\Interfaces\UsuarioInterface;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @package App\Tests\Entity
 */
class UserTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testInitiateUser()
    {
        $user = new User();

        $this->assertInstanceOf(UsuarioInterface::class, $user);
        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertInstanceOf(ModelInterface::class, $user);
        $this->assertInstanceOf(SimpleTimeInterface::class, $user);


        $this->assertIsArray($user->getRoles());
        $this->assertIsArray($user->getLoginData());
        $this->assertEmpty($user->getName());
        $this->assertEmpty($user->getId());
        $this->assertIsObject($user->getUser());
        $this->assertIsArray($user->getFullData());
        $this->assertIsArray($user->getOriginalData());
        $this->assertIsArray($user->getAllAttributesDateAndFormat());
        $this->assertInstanceOf(ArrayCollection::class, $user->getPlaces());
        $this->assertInstanceOf(ArrayCollection::class, $user->getCategories());
        $this->assertNull($user->getDeletedAt());
        $this->assertIsArray($user->getNameAndId());
        $this->assertIsArray($user->getRoles());
        $this->assertEquals('enable', $user->getStatus());
        $this->assertInstanceOf(ArrayCollection::class, $user->getApiTokens());
        $this->assertIsString($user->getDateTimeStringFrom(''));

        $this->assertEquals([
                                "id",
                                "email",
                                "name",
                                "status",
                                "created_at",
                                "updated_at",
                                "deleted_at"
                            ], array_keys($user->getOriginalData()));

        $this->assertEquals([
                                "id",
                                "email",
                                "name",
                                "status",
                                "status_description",
                                "created_at",
                                "updated_at"
                            ], array_keys($user->getFullData()));
    }

    public function testSettingData()
    {
        $user = new User();

        $data = [
            "name" => "Jacob",
            "email" => "eu@tu.com",
            "password" => "12345"
        ];

        $user->setAttributes($data);

        $userData = $user->getFullData();

        $this->assertEquals($data["name"], $userData["name"]);
        $this->assertEquals($data["email"], $userData["email"]);
    }

}