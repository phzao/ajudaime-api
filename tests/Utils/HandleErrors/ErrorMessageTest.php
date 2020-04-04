<?php

namespace App\Tests\Utils\HandleErrors;

use App\Utils\HandleErrors\ErrorMessage;
use PHPUnit\Framework\TestCase;

/**
 * Class ErrorMessageTest
 * @package App\Tests\Utils\HandleErrors
 */
class ErrorMessageTest extends TestCase
{
    public function testMessages()
    {
        $msg = ErrorMessage::getErrorMessage('');
        $this->assertJson($msg);
        $this->assertArrayHasKey("status", $this->convertJsonToArray($msg));
        $this->assertArrayHasKey("message", $this->convertJsonToArray($msg));
        $this->assertCount(2, $this->convertJsonToArray($msg));
        $this->assertEmpty($this->convertJsonToArray($msg)["message"]);
        $this->assertEquals('error', $this->convertJsonToArray($msg)["status"]);

        $msg = ErrorMessage::getArrayMessageToJson(["message"=>"An error occurred!"]);
        $this->assertJson($msg);
        $this->assertEquals('{"message":"An error occurred!"}', $msg);
        $this->assertArrayHasKey("message", $this->convertJsonToArray($msg));
        $this->assertEquals("An error occurred!", $this->convertJsonToArray($msg)["message"]);
        $this->assertCount(1, $this->convertJsonToArray($msg));

        $msg = ErrorMessage::getArrayMessageToJson(["key_from_error"=>"error message"]);
        $this->assertJson($msg);
        $this->assertCount(1,  $this->convertJsonToArray($msg));
        $this->assertArrayNotHasKey("status", $this->convertJsonToArray($msg));
        $this->assertArrayNotHasKey("data", $this->convertJsonToArray($msg));
        $this->assertArrayHasKey("key_from_error", $this->convertJsonToArray($msg));
        $this->assertEquals(["key_from_error"=>"error message"], $this->convertJsonToArray($msg));

    }

    /**
     * @param $json
     *
     * @return array
     */
    public function convertJsonToArray($json): array
    {
        return json_decode($json, true);
    }
}