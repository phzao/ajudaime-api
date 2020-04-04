<?php

namespace App\Tests\Entity;

use App\Utils\Generators\GenerateDataToSaveInterface;
use App\Utils\Generators\GenerateUserDemo;
use PHPUnit\Framework\TestCase;

/**
 * @package App\Tests\Entity
 */
class GenerateUserDemoTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testGenerate()
    {
        $generate = new GenerateUserDemo();

        $this->assertInstanceOf(GenerateDataToSaveInterface::class, $generate);

        $this->assertIsArray($generate->getEmailNameAndPassword());
    }

    public function testReturnGetEmailNameAndPassword()
    {
        $generate = new GenerateUserDemo();

        $this->assertIsArray($generate->getEmailNameAndPassword());
        $this->assertCount(3, $generate->getEmailNameAndPassword());
    }

    public function testContentReturnGetEmailNameAndPassword()
    {
        $generate = new GenerateUserDemo();
        $data = $generate->getEmailNameAndPassword();

        $this->assertNotEmpty($data["name"]);
        $this->assertNotEmpty($data["email"]);
        $this->assertNotEmpty($data["password"]);
        $this->assertIsString($data["name"]);
        $this->assertIsString($data["email"]);
        $this->assertIsInt($data["password"]);
    }
}