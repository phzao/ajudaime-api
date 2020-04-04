<?php

namespace App\Tests\Entity;

use App\Entity\ApiToken;
use App\Entity\Interfaces\ApiTokenInterface;
use PHPUnit\Framework\TestCase;

/**
 * @package App\Tests\Entity
 */
class ApiTokenTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testApiTokenEmpty()
    {
        $apiToken = new ApiToken();
        $this->assertInstanceOf(ApiTokenInterface::class, $apiToken);
        $this->assertCount(6, $apiToken->getDetailsToken());
        $this->assertEmpty($apiToken->getId());
        $this->assertNotEmpty($apiToken->getExpireAt());
        $this->assertNull($apiToken->getUser());
    }

    /**
     * @throws \Exception
     */
    public function testInvalidateToken()
    {
        $apiToken = new ApiToken();

        $data = $apiToken->getDetailsToken();
        $this->assertNotEmpty($data["expire_at"]);
        $this->assertEmpty($data["expired_at"]);
        $apiToken->invalidateToken();

        $data = $apiToken->getDetailsToken();
        $this->assertNotEmpty($data["expired_at"]);
    }
}