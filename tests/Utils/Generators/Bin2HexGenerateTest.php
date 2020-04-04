<?php

namespace App\Tests\Entity;

use App\Utils\Generators\Bin2HexGenerate;
use App\Utils\Generators\TokenGeneratorInterface;
use PHPUnit\Framework\TestCase;

/**
 * @package App\Tests\Entity
 */
class Bin2HexGenerateTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testGenerate()
    {
        $generate = new Bin2HexGenerate();

        $this->assertInstanceOf(TokenGeneratorInterface::class, $generate);

        $this->assertNotEmpty($generate->generate());
        $this->assertIsString($generate->generate());

    }

    public function testSetLengthString()
    {
        $generate = new Bin2HexGenerate();

        $this->assertIsString($generate->generate());
        $this->assertEquals(250, strlen($generate->generate(125)));
        $this->assertEquals(300, strlen($generate->generate(150)));
        $this->assertEquals(50, strlen($generate->generate(25)));
    }
}