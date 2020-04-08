<?php

namespace App\Tests\Controller;

use App\Tests\Authenticate;
use App\Tests\CleanElasticSearch;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @package App\Tests\Controller
 */
class NeedControllerTest extends WebTestCase
{
    use CleanElasticSearch, Authenticate;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    private $client;

    protected $elasticRepository;

    const NEED_ROUTE = "/api/v1/needs";

    const DONATION_ROUTE = "/api/v1/donations";

    public function setUp()
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->clearIndexes();
    }

    public function testRegisterNeedsWithoutDataShouldFail()
    {
        $token = $this->getTokenAuthenticate();

        $this->client->request('POST', self::NEED_ROUTE, [],[], $token);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonStringEqualsJsonString($this->client->getResponse()->getContent(),
                                                '{"status":"fail","data":{"needsList":"Uma lista de necessidades \u00e9 obrigat\u00f3ria!","message":"Uma mensagem para essa ajuda \u00e9 requerida!"}}');
    }

    public function testRegisterNeedsWithOnlyMessageShouldFail()
    {
        $token = $this->getTokenAuthenticate();
        $data["message"] = "my message to";

        $this->client->request('POST', self::NEED_ROUTE, $data,[], $token);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonStringEqualsJsonString($this->client->getResponse()->getContent(),
                                                '{"status":"fail","data":{"needsList":"Uma lista de necessidades \u00e9 obrigat\u00f3ria!"}}');
    }

    public function testRegisterNeedsWithOnlyNeedsListShouldFail()
    {
        $token = $this->getTokenAuthenticate();
        $data["needsList"] = "my message to";

        $this->client->request('POST', self::NEED_ROUTE, $data,[], $token);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonStringEqualsJsonString($this->client->getResponse()->getContent(),
                                                '{"status":"fail","data":{"message":"Uma mensagem para essa ajuda \u00e9 requerida!"}}');
    }

    public function testRegisterNeedsWithMessageRightAndNeedsListOverLimitShouldFail()
    {
        $token = $this->getTokenAuthenticate();
        $data["message"] = "my message to";
        $data["needsList"] = "lsjflajsdflasjdlfjasldf jaslfj lasdjflas jdflas jdfljasd lfjasldjfklasjdfklasjdflkjasdlkfjlasdjf lasadsafalsjflajsdflasjdlfjasldf jaslfj lasdjflas jdflas jdfljasd lfjasldjfklasjdfklasjdflkjasdlkfjlasdjf ladsasdfas";
        $this->client->request('POST', self::NEED_ROUTE, $data,[], $token);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonStringEqualsJsonString($this->client->getResponse()->getContent(),
                                                '{"status":"fail","data":{"needsList":"A lista deve ter no m\u00e1ximo 200 caracteres"}}');
    }

    public function testRegisterNeedsWithNeedsListRightAndMessageListOverLimitShouldFail()
    {
        $token = $this->getTokenAuthenticate();
        $data["needsList"] = "my message to";
        $data["message"] = "lsjflajsdflasjdlfjasldf jaslfj lasdjflas jdflas jdfljasd lfjasldjfklasjdfklasjdflkjasdlkfjlasdjf lasadsafalsjflajsdflasjdlfjasldf jaslfj lasdjflas jdflas jdfljasd lfjasldjfklasjdfklasjdflkjasdlkfjlasdjf ladsasdfas";

        $this->client->request('POST', self::NEED_ROUTE, $data,[], $token);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonStringEqualsJsonString($this->client->getResponse()->getContent(),
                                                '{"status":"fail","data":{"message":"A Mensagem deve ter no m\u00e1ximo 200 caracteres"}}');
    }

    public function testRegisterShouldSuccess()
    {
        $token = $this->getTokenAuthenticate();
        $data["needsList"] = "my message to";
        $data["message"] = "Food";
        $this->client->request('POST', self::NEED_ROUTE, $data,[], $token);
        $this->assertResponseStatusCodeSame(201);
        $res = json_decode($this->client->getResponse()->getContent(), true);

        $needData = $res["data"];
        $this->assertCount(8, $needData);
        $this->assertEquals(null, $needData["donation"]);
        $this->assertEquals("Food", $needData["message"]);
        $this->assertEquals("my message to", $needData["needsList"]);
    }

    public function testTryRegisterTwoNeedsWithoutADonationShouldFail()
    {
        $token = $this->getTokenAuthenticate();

        $data["needsList"] = "my message to";
        $data["message"] = "Food";
        $this->client->request('POST', self::NEED_ROUTE, $data,[], $token);
        $this->assertResponseStatusCodeSame(201);
        sleep(1);

        $data["message"] = "my message to";
        $data["needsList"] = "Food";
        $this->client->request('POST', self::NEED_ROUTE, $data,[], $token);
        $this->assertResponseStatusCodeSame(400);
        sleep(1);

        $this->assertJsonStringEqualsJsonString($this->client->getResponse()->getContent(),
                                                '{"status":"error","message":"Quantidade limite de 1 listas em aberto atingida!"}');
    }

    public function testTryUpdateANeedDontExistShouldFail()
    {
        $token = $this->getTokenAuthenticate();

        $this->client->request('PUT', self::NEED_ROUTE."/fasfa-sdfsafa", [],[], $token);
        $this->assertResponseStatusCodeSame(404);
        $this->assertJsonStringEqualsJsonString($this->client->getResponse()->getContent(),
                                                '{"status": "error","message": "Lista não localizada"}');
    }

    public function testTryUpdateANeedFromOtherUserShouldFail()
    {
        $token = $this->getTokenAuthenticate();

        $data["needsList"] = "my message to";
        $data["message"] = "Food";
        $this->client->request('POST', self::NEED_ROUTE, $data,[], $token);
        $this->assertResponseStatusCodeSame(201);
        sleep(1);
        $res = json_decode($this->client->getResponse()->getContent(), true);

        $needOne = $res["data"];
        $userTwo = $this->getTokenAuthenticate("you@your.mail");

        $this->client->request('PUT', self::NEED_ROUTE."/".$needOne["id"], [],[], $userTwo);
        $this->assertResponseStatusCodeSame(404);
        $this->assertJsonStringEqualsJsonString($this->client->getResponse()->getContent(),
                                                '{"status": "error","message": "Lista não localizada"}');
    }

    public function testTryUpdateMessageToNullShouldFail()
    {
        $token = $this->getTokenAuthenticate();

        $data["needsList"] = "my message to";
        $data["message"] = "Food";
        $this->client->request('POST', self::NEED_ROUTE, $data,[], $token);
        $this->assertResponseStatusCodeSame(201);
        sleep(1);
        $res = json_decode($this->client->getResponse()->getContent(), true);

        $need = $res["data"];

        $newData = ["message"=>null];

        $this->client->request('PUT', self::NEED_ROUTE."/".$need["id"], $newData,[], $token);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonStringEqualsJsonString($this->client->getResponse()->getContent(),
                                                '{"status":"fail","data":{"message":"Uma mensagem para essa ajuda \u00e9 requerida!"}}');
    }

    public function testTryUpdateNeedsListToNullShouldFail()
    {
        $token = $this->getTokenAuthenticate();

        $data["needsList"] = "my message to";
        $data["message"] = "Food";
        $this->client->request('POST', self::NEED_ROUTE, $data,[], $token);
        $this->assertResponseStatusCodeSame(201);
        sleep(1);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $need = $res["data"];

        $newData = ["needsList"=>null];

        $this->client->request('PUT', self::NEED_ROUTE."/".$need["id"], $newData,[], $token);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonStringEqualsJsonString($this->client->getResponse()->getContent(),
                                                '{"status":"fail","data":{"needsList":"Uma lista de necessidades \u00e9 obrigat\u00f3ria!"}}');
    }

    public function testTryRemoveANeedWhoHasDonationSetShouldFail()
    {
        $token = $this->getTokenAuthenticate();

        $data["needsList"] = "my message to";
        $data["message"] = "Food";
        $this->client->request('POST', self::NEED_ROUTE, $data,[], $token);
        $this->assertResponseStatusCodeSame(201);
        sleep(1);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $need = $res["data"];

        $newData = ["needsList"=>null];

        $this->client->request('PUT', self::NEED_ROUTE."/".$need["id"], $newData,[], $token);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonStringEqualsJsonString($this->client->getResponse()->getContent(),
                                                '{"status":"fail","data":{"needsList":"Uma lista de necessidades \u00e9 obrigat\u00f3ria!"}}');
    }

    public function testTryRemoveANeedWithoutADonationShouldSuccess()
    {
        $token = $this->getTokenAuthenticate();

        $data["needsList"] = "my message to";
        $data["message"] = "Food";
        $this->client->request('POST', self::NEED_ROUTE, $data,[], $token);
        $this->assertResponseStatusCodeSame(201);
        sleep(1);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $need = $res["data"];

        $this->client->request('DELETE', self::NEED_ROUTE."/".$need["id"], [],[], $token);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('PUT', self::NEED_ROUTE."/".$need["id"], ["message"=>"change"],[], $token);
        $this->assertResponseStatusCodeSame(404);
    }
}