<?php

namespace App\Tests\Controller;

use App\Tests\CleanElasticSearch;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @package App\Tests\Controller
 */
class RegisterControllerTest extends WebTestCase
{
    use CleanElasticSearch;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    private $client;

    protected $elasticRepository;

    const REGISTER = "/public/login/teste";

    public function setUp()
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    public function tearDown(): void
    {
        $this->clearIndexes();
    }

    public function testRegisterWithoutDataShouldFail()
    {
        $this->client->request('POST', self::REGISTER, []);
        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonStringEqualsJsonString('{"status":"error","message":"Undefined index: email"}',
                                                $this->client->getResponse()->getContent());
    }

    public function testRegisterShouldSuccess()
    {
        $data = [
            "email" => "phbotelhosss@gmail.com",
            "name" => "MR CODE",
            "localization" => [-27.5817, -48.6575],
        ];

        $this->client->request('POST', self::REGISTER, $data);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(6, $res["data"]);
        $this->assertCount(12, $res["data"]["user"]);
        $userData = ["id", "email", "name", "status", "localization", "isConfirmedLocalization", "phone", "whatsapp", "message", "created_at", "updated_at", "deleted_at"];
        $resultUserKeys = array_keys($res["data"]["user"]);
        $this->assertEquals($userData, $resultUserKeys);
    }
}