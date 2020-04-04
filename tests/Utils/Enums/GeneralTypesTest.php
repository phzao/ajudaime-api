<?php

namespace App\Tests\Utils\Enums;

use App\Utils\Enums\GeneralTypes;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class GeneralTypesTest
 * @package App\Tests\Utils\Enums
 */
class GeneralTypesTest extends TestCase
{
    public function testStatusTypes()
    {
        $list = GeneralTypes::getStatusList();

        $this->assertEquals(["enable", "disable", "blocked"], $list);

        $list = GeneralTypes::getStatusDescriptionList();
        $descriptions = [
            "enable"  => "ativo",
            "disable" => "inativo"
        ];
        $this->assertEquals($descriptions, $list);

        $this->assertEquals('enable', GeneralTypes::STATUS_ENABLE);
        $this->assertEquals('disable', GeneralTypes::STATUS_DISABLE);
        $this->assertEquals('blocked', GeneralTypes::STATUS_BLOCKED);

        $this->expectException(UnprocessableEntityHttpException::class);

        GeneralTypes::isValidDefaultStatusOrFail("ulala");
    }
}