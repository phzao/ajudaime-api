<?php

namespace App\Tests\Controller;

use App\Tests\Authenticate;
use App\Tests\CleanElasticSearch;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @package App\Tests\Controller
 */
class UserControllerTest extends WebTestCase
{
    use CleanElasticSearch, Authenticate;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    private $client;

    protected $elasticRepository;

    const USER_ROUTE = "/api/v1/users";

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

    public function testUpdateUserWhatsAppOverLimitShouldFail()
    {
        $token = $this->getTokenAuthenticate();

        $data = [
            "whatsapp" => "112233 234123412 1234123421341",
        ];

        $this->client->request('PUT', self::USER_ROUTE, $data,[], $token);
        $this->assertResponseStatusCodeSame(422);

        $this->assertJsonStringEqualsJsonString($this->client->getResponse()->getContent(),
                                                '{"status":"fail","data":{"whatsapp":"Whatsapp pode ter no m\u00ednimo 20 caracteres! Formato (xx) xxxxx-xxxx"}}');
    }

    public function testUpdateUserPhoneOverLimitShouldFail()
    {
        $token = $this->getTokenAuthenticate();

        $data = [
            "phone" => "112233 234123412 1234123421341",
        ];

        $this->client->request('PUT', self::USER_ROUTE, $data,[], $token);
        $this->assertResponseStatusCodeSame(422);

        $this->assertJsonStringEqualsJsonString($this->client->getResponse()->getContent(),
                                                '{"status":"fail","data":{"phone":"Telefone pode ter no m\u00ednimo 20 caracteres"}}');
    }

    public function testUpdateLocalizationShouldSuccess()
    {
        $token = $this->getTokenAuthenticate();

        $data = [
            -27.60343655507399,
            -48.63020187959159
        ];

        $this->client->request('PUT', self::USER_ROUTE, $data,[], $token);
        $this->assertResponseStatusCodeSame(204);
        $this->client->request('GET', self::USER_ROUTE, [], [], $token);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $userData = $res["data"];

        $this->assertEquals([
                                -27.60343655507399,
                                -48.63020187959159
                            ], $userData["localization"]);
    }

    public function testUpdateUserMessageOverLimitShouldFail()
    {
        $token = $this->getTokenAuthenticate();

        $data = [
            "message" => "lfajsdflajdfkl asldfajsldfjasldfjalks;djf lasjdfl;a sdjfla jsdlfkjaslkdf jaskldfj alksdjf laksdjfklasjdfklajsdlkfja sdlkfjaslkdjfals;df jals jdflasjd
lfajsdflajdfkl asldfajsldfjasldfjalks;djf lasjdfl;a sdjfla jsdlfkjaslkdf jaskldfj alksdjf laksdjfklasjdfklajsdlkfja sdlkfjaslkdjfals;df jals jdflasjd
lfajsdflajdfkl asldfajsldfjasldfjalks;djf lasjdfl;a sdjfla jsdlfkjaslkdf jaskldfj alksdjf laksdjfklasjdfklajsdlkfja sdlkfjaslkdjfals;df jals jdflasjd
lfajsdflajdfkl asldfajsldfjasldfjalks;djf lasjdfl;a sdjfla jsdlfkjaslkdf jaskldfj alksdjf laksdjfklasjdfklajsdlkfja sdlkfjaslkdjfals;df jals jdflasjd",
        ];
        $this->client->request('PUT', self::USER_ROUTE, $data,[], $token);
        $this->assertResponseStatusCodeSame(422);

        $this->assertJsonStringEqualsJsonString($this->client->getResponse()->getContent(),
                                                '{"status":"fail","data":{"message":"Mensagem pode ter no m\u00ednimo 500 caracteres"}}');
    }

    public function testUpdateUserMessageShouldSuccess()
    {
        $token = $this->getTokenAuthenticate();

        $data = [
            "message" => "My message about me"
        ];
        $this->client->request('PUT', self::USER_ROUTE, $data,[], $token);
        $this->assertResponseStatusCodeSame(204);
        $this->client->request('GET', self::USER_ROUTE, [], [], $token);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $userData = $res["data"];

        $this->assertEquals("My message about me", $userData["message"]);
    }

    public function testUpdateUserWhatsappShouldSuccess()
    {
        $token = $this->getTokenAuthenticate();

        $data = [
            "whatsapp" => "12345 1234"
        ];
        $this->client->request('PUT', self::USER_ROUTE, $data,[], $token);
        $this->assertResponseStatusCodeSame(204);
        $this->client->request('GET', self::USER_ROUTE, [], [], $token);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $userData = $res["data"];

        $this->assertEquals("12345 1234", $userData["whatsapp"]);
    }

    public function testUpdateUserPhoneShouldSuccess()
    {
        $token = $this->getTokenAuthenticate();

        $data = [
            "phone" => "12345 1234"
        ];
        $this->client->request('PUT', self::USER_ROUTE, $data,[], $token);
        $this->assertResponseStatusCodeSame(204);
        $this->client->request('GET', self::USER_ROUTE, [], [], $token);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $userData = $res["data"];

        $this->assertEquals("12345 1234", $userData["phone"]);
    }

    public function testUpdateUserNameEmailShouldBeIgnored()
    {
        $token = $this->getTokenAuthenticate();

        $data = [
            "name" => "12345 1234",
            "email" => "my@may.com"
        ];
        $this->client->request('PUT', self::USER_ROUTE, $data,[], $token);
        $this->assertResponseStatusCodeSame(204);
        $this->client->request('GET', self::USER_ROUTE, [], [], $token);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $userData = $res["data"];

        $this->assertNotEquals("12345 1234", $userData["name"]);
        $this->assertNotEquals("my@may.com", $userData["email"]);
    }

    public function testGetDataFromUserLoggedShouldSuccess()
    {
        $token = $this->getTokenAuthenticate();

        $this->client->request('GET', self::USER_ROUTE, [], [], $token);
        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($this->client->getResponse()->getContent());
        $res = json_decode($this->client->getResponse()->getContent(), true);

        $userData = $res["data"];
        $this->assertCount(12, $userData);
        $this->assertEquals(null, $userData["name"]);
        $this->assertEquals("enable", $userData["status"]);
        $this->assertEquals(null, $userData["localization"]);
        $this->assertEquals(false, $userData["isConfirmedLocalization"]);
        $this->assertEquals("NULL", $userData["phone"]);
        $this->assertEquals("NULL", $userData["whatsapp"]);
        $this->assertEquals("NULL", $userData["message"]);
    }
}