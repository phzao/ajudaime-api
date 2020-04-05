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

        $this->assertIsArray($user->getFullDataToUpdateIndex());
        $this->assertIsArray($user->getFullData());
        $this->assertIsArray($user->getOriginalData());
        $this->assertIsArray($user->getDataToInsert());
        $this->assertIsArray($user->getDataResume());
        $this->assertIsArray($user->getElasticSearchMapping());
        $this->assertIsArray($user->getElasticIndexName());
        $this->assertIsArray($user->getNameAndId());
    }

    public function testFullDataToUpdateIndex()
    {
        $user = new User();

        $userUpdate = $user->getFullDataToUpdateIndex();

        $this->assertCount(4, $userUpdate);
        $this->assertCount(11, $userUpdate["body"]["doc"]);
    }

    public function testIndexName()
    {
        $user = new User();

        $userIndex = $user->getElasticIndexName();

        $this->assertEquals(["index"=>"users"], $userIndex);
    }
}