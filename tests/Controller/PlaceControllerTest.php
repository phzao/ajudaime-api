<?php

namespace App\Tests\Controller;

use App\Tests\Authenticate;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @package App\Tests\Controller
 */
class PlaceControllerTest extends WebTestCase
{
    use Authenticate;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    private $client;

    const PLACE_ROUTE = '/api/v1/places';

    public function setUp()
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    public function testRegisterWithoutDataShouldSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::PLACE_ROUTE, [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $res = $this->client->getResponse()->getContent();
        $place = json_decode($res, true);
        $this->assertArrayHasKey('status', $place);
        $this->assertArrayHasKey('data', $place);
        $this->assertNotEmpty($place["data"]);
        $this->assertNotEmpty($place["data"]["created_at"]);
        $this->assertNotEmpty($place["data"]["user_id"]);
        $this->assertNotEmpty($place["data"]["id"]);
        $this->assertCount(10, $place["data"]);
        $this->assertEquals('home', $place["data"]["description"]);
        $this->assertEquals('enable', $place["data"]["status"]);
        $this->assertEquals('ativo', $place["data"]["status_description"]);
        $this->assertEquals('Não padrão', $place["data"]["is_default_description"]);
        $this->assertEquals(false, $place["data"]["is_default"]);
        $this->assertEmpty($place["data"]["updated_at"]);
    }

    public function testRegisterWithADescriptionShouldSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::PLACE_ROUTE, ["description"=>"work"],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);
        $this->assertJson($this->client->getResponse()->getContent());


        $res = json_decode($this->client->getResponse()->getContent(), true);
        $place = $res;
        $this->assertArrayHasKey('status', $place);
        $this->assertArrayHasKey('data', $place);
        $this->assertNotEmpty($place["data"]);
        $this->assertNotEmpty($place["data"]["created_at"]);
        $this->assertNotEmpty($place["data"]["user_id"]);
        $this->assertNotEmpty($place["data"]["id"]);
        $this->assertCount(10, $place["data"]);
        $this->assertEquals('work', $place["data"]["description"]);
        $this->assertEquals('enable', $place["data"]["status"]);
        $this->assertEquals('ativo', $place["data"]["status_description"]);
        $this->assertEquals('Não padrão', $place["data"]["is_default_description"]);
        $this->assertEquals(false, $place["data"]["is_default"]);
        $this->assertEmpty($place["data"]["updated_at"]);
    }

    public function testRegisterWithADifferentStatusShouldStayEnableSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::PLACE_ROUTE, ["status"=>"disable"],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $res = $this->client->getResponse()->getContent();
        $place = json_decode($res, true);
        $this->assertEquals('enable', $place["data"]["status"]);
        $this->assertEquals('ativo', $place["data"]["status_description"]);
    }

    public function testRegisterDuplicateDefaultDescriptionShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::PLACE_ROUTE, [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $this->client->request('POST', self::PLACE_ROUTE, [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(405);

        $this->assertJsonStringEqualsJsonString('{
                                                      "status": "error",
                                                      "message": "Same description not allowed in more than one place!"
                                                    }', $this->client->getResponse()->getContent());
    }

    public function testRegisterDescriptionGreaterThan255ShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $description = "asdfasdfasjdflasj ljfljsalkfjsaljflsd flasjf lasjdf lasjdlf ajsdlfjasldf jalsdjflsadjfasdfasdfasjdflasj ljfljsalkfjsaljflsd ";

        $this->client->request('POST', self::PLACE_ROUTE, [
            "description" => $description
        ],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);

        $this->assertJsonStringEqualsJsonString('{
                                                          "status": "fail",
                                                          "data": {
                                                            "description": "Description cannot be longer than 30 characters"
                                                          }
                                                        }', $this->client->getResponse()->getContent());
    }

    public function testRegisterDescriptionBlankShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::PLACE_ROUTE, ["description" => ""],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);

        $this->assertJsonStringEqualsJsonString('{
                                                          "status": "fail",
                                                          "data": {
                                                            "description": "A description is required!"
                                                          }
                                                        }', $this->client->getResponse()->getContent());
    }

    public function testRegisterDuplicateDescriptionShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::PLACE_ROUTE, ["description"=>"work"],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $this->client->request('POST', self::PLACE_ROUTE, ["description"=>"work"],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(405);

        $this->assertJsonStringEqualsJsonString('{
                                                      "status": "error",
                                                      "message": "Same description not allowed in more than one place!"
                                                    }', $this->client->getResponse()->getContent());
    }

    public function testShowWithUUIDInvalidShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('GET', self::PLACE_ROUTE."/12132312", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);

        $this->assertJsonStringEqualsJsonString('{
                                                          "status": "fail",
                                                          "message": "Could not convert database value \"12132312\" to Doctrine Type uuid"
                                                        }', $this->client->getResponse()->getContent());
    }

    public function testShowWithUUIDNotRegisteredShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('GET', self::PLACE_ROUTE."/8f0a9c17-d392-4bc1-9acb-3449c6f24d19", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(404);

        $this->assertJsonStringEqualsJsonString('{
                                                          "status": "error",
                                                          "message": "There is no place with this 8f0a9c17-d392-4bc1-9acb-3449c6f24d19"
                                                        }', $this->client->getResponse()->getContent());
    }

    public function testShowWithUserNotLoggedShouldFail()
    {
        $this->client->request('GET', self::PLACE_ROUTE."/8f0a9c17-d392-4bc1-9acb-3449c6f24d19");
        $this->assertResponseStatusCodeSame(401);

        $this->assertJsonStringEqualsJsonString('{"message": "Authentication Required"}', $this->client->getResponse()->getContent());
    }

    public function testShowShouldSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::PLACE_ROUTE, ["description"=>"work"],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $res = $this->client->getResponse()->getContent();
        $data = json_decode($res, true);
        $place = $data["data"];

        $this->client->request('GET', self::PLACE_ROUTE."/".$place["id"], [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $resultExpected = [
            "status" => "success",
            "data" => $place
        ];

        $this->assertJsonStringEqualsJsonString(json_encode($resultExpected), $this->client->getResponse()->getContent());
    }

    public function testListWithUserNotLoggedShouldFail()
    {
        $this->client->request('GET', self::PLACE_ROUTE);
        $this->assertResponseStatusCodeSame(401);

        $this->assertJsonStringEqualsJsonString('{"message": "Authentication Required"}', $this->client->getResponse()->getContent());
    }

    public function testUpdateWithUserNotLoggedShouldFail()
    {
        $this->client->request('PUT', self::PLACE_ROUTE."/8f0a9c17-d392-4bc1-9acb-3449c6f24d19");
        $this->assertResponseStatusCodeSame(401);

        $this->assertJsonStringEqualsJsonString('{"message": "Authentication Required"}', $this->client->getResponse()->getContent());
    }

    public function testUpdateStatusWithUserNotLoggedShouldFail()
    {
        $this->client->request('PUT', self::PLACE_ROUTE."/8f0a9c17-d392-4bc1-9acb-3449c6f24d19/status/disable");
        $this->assertResponseStatusCodeSame(401);

        $this->assertJsonStringEqualsJsonString('{"message": "Authentication Required"}', $this->client->getResponse()->getContent());
    }

    public function testListPlacesEmptySuccess()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('GET', self::PLACE_ROUTE, [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $this->assertJsonStringEqualsJsonString('{
                                                        "status": "success",
                                                          "data": []
                                                        }', $this->client->getResponse()->getContent());
    }

    public function testListTwoPlacesBelongToUserSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::PLACE_ROUTE, [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);
        $this->client->request('POST', self::PLACE_ROUTE, ["description" => "work"], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $this->client->request('GET', self::PLACE_ROUTE, [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(2, $res["data"]);
        $this->assertCount(10, $res["data"][0]);
        $this->assertCount(10, $res["data"][1]);
        $this->assertEquals($this->registeredData["id"], $res["data"][0]["user_id"]);
        $this->assertEquals($this->registeredData["id"], $res["data"][1]["user_id"]);
    }

    public function testUpdateStatusFromAnotherUserShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', '/register', ["email" => "you@yo.com", "password" => "123456"]);
        $this->assertResponseStatusCodeSame(201);

        $this->client->request('POST', '/authenticate', [
            "email" => "you@yo.com",
            "password" => "123456"
        ]);

        $secondUser = $this->client->getResponse()->getContent();
        $another = json_decode($secondUser, true);
        $userSecond = $another["data"];

        $this->client->request('POST', self::PLACE_ROUTE, [], [], ["HTTP_Authorization" => $userSecond["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $place = json_decode($this->client->getResponse()->getContent(), true);

        $this->client->request('PUT', self::PLACE_ROUTE."/".$place["data"]["id"]."/status/disable", ["description"=>"works"],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(404);

        $result_expected = [
            "status" => "error",
            "message" => "There is no place with this ".$place["data"]["id"]
        ];
        $this->assertJsonStringEqualsJsonString(json_encode($result_expected), $this->client->getResponse()->getContent());
    }

    public function testUpdateFromAnotherUserShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', '/register', ["email" => "you@yo.com", "password" => "123456"]);
        $this->assertResponseStatusCodeSame(201);

        $this->client->request('POST', '/authenticate', [
            "email" => "you@yo.com",
            "password" => "123456"
        ]);

        $secondUser = $this->client->getResponse()->getContent();
        $another = json_decode($secondUser, true);
        $userSecond = $another["data"];

        $this->client->request('POST', self::PLACE_ROUTE, [], [], ["HTTP_Authorization" => $userSecond["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $place = json_decode($this->client->getResponse()->getContent(), true);

        $this->client->request('PUT', self::PLACE_ROUTE."/".$place["data"]["id"], ["description"=>"works"],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(404);

        $result_expected = [
            "status" => "error",
            "message" => "There is no place with this ".$place["data"]["id"]
        ];
        $this->assertJsonStringEqualsJsonString(json_encode($result_expected), $this->client->getResponse()->getContent());
    }

    public function testUpdateUuidInvalidShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('PUT', self::PLACE_ROUTE."/12312", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);

        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "fail",
                                                                  "message": "Could not convert database value \"12312\" to Doctrine Type uuid"
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testUpdateUuidNotRegisteredShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('PUT', self::PLACE_ROUTE."/8f0a9c17-d392-4bc1-9acb-3449c6f24d10", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(404);

        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "error",
                                                                  "message": "There is no place with this 8f0a9c17-d392-4bc1-9acb-3449c6f24d10"
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testUpdateDescriptionEqualToAnExistentShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::PLACE_ROUTE, [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $this->client->request('POST', self::PLACE_ROUTE, ["description"=>"work"], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $place = json_decode($this->client->getResponse()->getContent(),true);
        $place_id = $place["data"]["id"];

        $this->client->request('PUT', self::PLACE_ROUTE."/".$place_id, ["description"=>"home"],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(405);

        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "error",
                                                                  "message": "Same description not allowed in more than one place!"
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testUpdateDescriptionSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::PLACE_ROUTE, ["description"=>"work"], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $place = json_decode($this->client->getResponse()->getContent(),true);
        $place_id = $place["data"]["id"];

        $this->client->request('PUT', self::PLACE_ROUTE."/".$place_id, ["description"=>"home"],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);
    }

    public function testUpdateStatusToInvalidShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::PLACE_ROUTE, ["description"=>"work"], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $place = json_decode($this->client->getResponse()->getContent(),true);
        $place_id = $place["data"]["id"];

        $this->client->request('PUT', self::PLACE_ROUTE."/".$place_id."/status/blocking", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);

        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "fail",
                                                                  "data": {
                                                                    "status": "This status blocking is invalid!"
                                                                  }
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testUpdateStatusToDisableBlockedEnableShouldSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::PLACE_ROUTE, [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $place = json_decode($this->client->getResponse()->getContent(),true);
        $place_id = $place["data"]["id"];

        $this->client->request('PUT', self::PLACE_ROUTE."/".$place_id."/status/disable", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('PUT', self::PLACE_ROUTE."/".$place_id."/status/blocked", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('PUT', self::PLACE_ROUTE."/".$place_id."/status/enable", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);
    }

    public function testUpdateStatusSettingWithoutLoginShouldFail()
    {
        $this->client->request('PUT', self::PLACE_ROUTE."/8f0a9c17-d392-4bc1-9acb-3449c6f24d19/default");
        $this->assertResponseStatusCodeSame(401);
    }

    public function testUpdateStatusSettingToDefaultShouldChangeOthersToNotDefaultSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::PLACE_ROUTE, [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $place = json_decode($this->client->getResponse()->getContent(),true);
        $place_id = $place["data"]["id"];
        sleep(1);

        $this->client->request('POST', self::PLACE_ROUTE, ["description"=>"tech"], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $place = json_decode($this->client->getResponse()->getContent(),true);
        $place_two_id = $place["data"]["id"];

        sleep(1);
        
        $this->client->request('POST', self::PLACE_ROUTE, ["description"=>"ultra"], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $this->client->request('GET', self::PLACE_ROUTE, [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(3, $res["data"]);
        $this->assertCount(10, $res["data"][0]);
        $this->assertCount(10, $res["data"][1]);
        $this->assertCount(10, $res["data"][2]);
        $this->assertEquals(false, $res["data"][0]["is_default"]);
        $this->assertEquals(false, $res["data"][1]["is_default"]);
        $this->assertEquals(false, $res["data"][2]["is_default"]);

        $this->client->request('PUT', self::PLACE_ROUTE."/".$place_id."/default", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('GET', self::PLACE_ROUTE, [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(true, $res["data"][0]["is_default"]);
        $this->assertEquals(false, $res["data"][1]["is_default"]);
        $this->assertEquals(false, $res["data"][2]["is_default"]);

        $this->client->request('PUT', self::PLACE_ROUTE."/".$place_two_id."/default", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('GET', self::PLACE_ROUTE, [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(false, $res["data"][0]["is_default"]);
        $this->assertEquals(true, $res["data"][1]["is_default"]);
        $this->assertEquals(false, $res["data"][2]["is_default"]);
    }

    public function testRemoveWithIDThatNotExistShouldFail()
    {
        $result = $this->getTokenAuthenticate();
        $this->client->request('DELETE', self::PLACE_ROUTE."/0b4084ff-c7a1-4fa6-9029-3c50ee41b838", [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(404);
        $this->assertJson($this->client->getResponse()->getContent());

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $expected = [
            "status"=> "error",
            "message"=> 'There is no place with this 0b4084ff-c7a1-4fa6-9029-3c50ee41b838'
        ];
        $this->assertEquals($expected, $res);
    }

    public function testRemoveWithIDIncorrectShouldFail()
    {
        $result = $this->getTokenAuthenticate();
        $this->client->request('DELETE', self::PLACE_ROUTE."/0b4084ff", [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJson($this->client->getResponse()->getContent());

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $expected = [
            "status"=> "fail",
            "message"=> 'Could not convert database value "0b4084ff" to Doctrine Type uuid'
        ];
        $this->assertEquals($expected, $res);
    }

    public function testRemoveSuccess()
    {
        $result = $this->getTokenAuthenticate();
        $this->client->request('POST', self::PLACE_ROUTE, [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $place = json_decode($this->client->getResponse()->getContent(),true);
        $place_id = $place["data"]["id"];

        $this->client->request('GET', self::PLACE_ROUTE, [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(1, $res["data"]);
        $this->assertCount(10, $res["data"][0]);
        $this->assertEmpty($res["data"][0]["deleted_at"]);
        $this->assertEquals($place_id, $res["data"][0]["id"]);


        $this->client->request('DELETE', self::PLACE_ROUTE."/".$place_id, [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);
        $this->assertEmpty($this->client->getResponse()->getContent());

        $this->client->request('GET', self::PLACE_ROUTE, [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertIsArray($res["data"]);
        $this->assertNotEmpty($res["data"][0]["deleted_at"]);
    }
}