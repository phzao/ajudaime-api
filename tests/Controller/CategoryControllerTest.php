<?php

namespace App\Tests\Controller;

use App\Tests\Authenticate;
use App\Tests\Entity\RegisterCategory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @package App\Tests\Controller
 */
class CategoryControllerTest extends WebTestCase
{
    use Authenticate, RegisterCategory;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    private $client;

    const CATEGORY_ROUTE = '/api/v1/categories';

    public function setUp()
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    public function testShowWithUserNotLoggedShouldFail()
    {

        $this->client->request('POST', self::CATEGORY_ROUTE);
        $this->assertResponseStatusCodeSame(401);
    }

    public function testRegisterWithoutDataShouldSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::CATEGORY_ROUTE, [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $res = $this->client->getResponse()->getContent();
        $category = json_decode($res, true);
        $this->assertArrayHasKey('status', $category);
        $this->assertArrayHasKey('data', $category);
        $this->assertNotEmpty($category["data"]);
        $this->assertNotEmpty($category["data"]["created_at"]);
        $this->assertNotEmpty($category["data"]["user_id"]);
        $this->assertNotEmpty($category["data"]["id"]);
        $this->assertCount(10, $category["data"]);
        $this->assertEquals('general', $category["data"]["description"]);
        $this->assertEquals('enable', $category["data"]["status"]);
        $this->assertEquals('ativo', $category["data"]["status_description"]);
        $this->assertEquals('Não padrão', $category["data"]["is_default_description"]);
        $this->assertEquals(false, $category["data"]["is_default"]);
        $this->assertEmpty($category["data"]["updated_at"]);
    }

    public function testRegisterWithADifferentStatusShouldStayEnableSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::CATEGORY_ROUTE, ["status"=>"disable"],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $res = $this->client->getResponse()->getContent();
        $category = json_decode($res, true);
        $this->assertEquals('enable', $category["data"]["status"]);
        $this->assertEquals('ativo', $category["data"]["status_description"]);
    }

    public function testRegisterWithADifferentStatusSettingShouldStayNotDefaultSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::CATEGORY_ROUTE, ["is_default"=>"default"],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $res = $this->client->getResponse()->getContent();
        $category = json_decode($res, true);
        $this->assertEquals(false, $category["data"]["is_default"]);
    }

    public function testRegisterWithADescriptionShouldSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::CATEGORY_ROUTE, ["description" => "car"], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);
        $this->assertJson($this->client->getResponse()->getContent());

        $res = json_decode($this->client->getResponse()->getContent(), true);
        $category = $res;
        $this->assertArrayHasKey('status', $category);
        $this->assertArrayHasKey('data', $category);
        $this->assertNotEmpty($category["data"]);
        $this->assertNotEmpty($category["data"]["created_at"]);
        $this->assertNotEmpty($category["data"]["user_id"]);
        $this->assertNotEmpty($category["data"]["id"]);
        $this->assertCount(10, $category["data"]);
        $this->assertEquals('car', $category["data"]["description"]);
        $this->assertEquals('enable', $category["data"]["status"]);
        $this->assertEquals('ativo', $category["data"]["status_description"]);
        $this->assertEquals('Não padrão', $category["data"]["is_default_description"]);
        $this->assertEquals(false, $category["data"]["is_default"]);
        $this->assertEmpty($category["data"]["updated_at"]);
    }

    public function testRegisterDuplicateDefaultDescriptionShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::CATEGORY_ROUTE, [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $this->client->request('POST', self::CATEGORY_ROUTE, [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(405);

        $this->assertJsonStringEqualsJsonString('{
                                                      "status": "error",
                                                      "message": "Same description not allowed in more than one category!"
                                                    }', $this->client->getResponse()->getContent());
    }

    public function testRegisterDescriptionGreaterThan255ShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $description = "asdfasdfasjdflasj ljfljsalkfjsaljflsd flasjf lasjdf lasjdlf ajsdlfjasldf jalsdjflsadjfasdfasdfasjdflasj ljfljsalkfjsaljflsd flasjf lasjdf lasjdlf ajsdlfjasldf jalsdjflsadjfasdfasdfasjdflasj ljfljsalkfjsaljflsd flasjf lasjdf lasjdlf ajsdlfjasldf j632323222 ajsdlfjasldf j632323222 ajsdlfjasldf j632323222 ajsdlfjasldf j632323222";

        $this->client->request('POST', self::CATEGORY_ROUTE, [
            "description" => $description
        ],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);

        $this->assertJsonStringEqualsJsonString('{
                                                          "status": "fail",
                                                          "data": {
                                                            "description": "Description cannot be longer than 255 characters"
                                                          }
                                                        }', $this->client->getResponse()->getContent());
    }

    public function testRegisterDescriptionBlankShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::CATEGORY_ROUTE, ["description" => ""],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);

        $this->assertJsonStringEqualsJsonString('{
                                                          "status": "fail",
                                                          "data": {
                                                            "description": "Description is required!"
                                                          }
                                                        }', $this->client->getResponse()->getContent());
    }

    public function testShowShouldSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::CATEGORY_ROUTE, ["description"=>"work"],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $res = $this->client->getResponse()->getContent();
        $data = json_decode($res, true);
        $category = $data["data"];

        $this->client->request('GET', self::CATEGORY_ROUTE."/".$category["id"], [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $resultExpected = [
            "status" => "success",
            "data" => $category
        ];

        $this->assertJsonStringEqualsJsonString(json_encode($resultExpected), $this->client->getResponse()->getContent());
    }

    public function testShowWithIdNotUUIDShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('GET', self::CATEGORY_ROUTE."/sdfasdfasdfasd", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);

        $this->assertJsonStringEqualsJsonString('{
                              "status": "fail",
                              "message": "Could not convert database value \"sdfasdfasdfasd\" to Doctrine Type uuid"
                            }', $this->client->getResponse()->getContent());
    }

    public function testListWithUserNotLoggedShouldFail()
    {
        $this->client->request('GET', self::CATEGORY_ROUTE);
        $this->assertResponseStatusCodeSame(401);

        $this->assertJsonStringEqualsJsonString('{"message": "Authentication Required"}', $this->client->getResponse()->getContent());
    }

    public function testUpdateWithUserNotLoggedShouldFail()
    {
        $this->client->request('PUT', self::CATEGORY_ROUTE."/8f0a9c17-d392-4bc1-9acb-3449c6f24d19");
        $this->assertResponseStatusCodeSame(401);

        $this->assertJsonStringEqualsJsonString('{"message": "Authentication Required"}', $this->client->getResponse()->getContent());
    }

    public function testUpdateStatusWithUserNotLoggedShouldFail()
    {
        $this->client->request('PUT', self::CATEGORY_ROUTE."/8f0a9c17-d392-4bc1-9acb-3449c6f24d19/status/disable");
        $this->assertResponseStatusCodeSame(401);

        $this->assertJsonStringEqualsJsonString('{"message": "Authentication Required"}', $this->client->getResponse()->getContent());
    }

    public function testListCategoriesEmptySuccess()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('GET', self::CATEGORY_ROUTE, [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $this->assertJsonStringEqualsJsonString('{
                                                        "status": "success",
                                                          "data": []
                                                        }', $this->client->getResponse()->getContent());
    }

    public function testListTwoCategoriesBelongToUserSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::CATEGORY_ROUTE, [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);
        $this->client->request('POST', self::CATEGORY_ROUTE, ["description" => "work"], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $this->client->request('GET', self::CATEGORY_ROUTE, [], [], ["HTTP_Authorization" => $result["token"]]);
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

        $this->client->request('POST', self::CATEGORY_ROUTE, [], [], ["HTTP_Authorization" => $userSecond["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $category = json_decode($this->client->getResponse()->getContent(), true);

        $this->client->request('PUT', self::CATEGORY_ROUTE."/".$category["data"]["id"]."/status/disable", ["description"=>"works"],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(404);

        $result_expected = [
            "status" => "error",
            "message" => "There is no category with this ".$category["data"]["id"]
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

        $this->client->request('POST', self::CATEGORY_ROUTE, [], [], ["HTTP_Authorization" => $userSecond["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $category = json_decode($this->client->getResponse()->getContent(), true);

        $this->client->request('PUT', self::CATEGORY_ROUTE."/".$category["data"]["id"], ["description"=>"works"],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(404);

        $result_expected = [
            "status" => "error",
            "message" => "There is no category with this ".$category["data"]["id"]
        ];
        $this->assertJsonStringEqualsJsonString(json_encode($result_expected), $this->client->getResponse()->getContent());
    }

    public function testUpdateUuidInvalidShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('PUT', self::CATEGORY_ROUTE."/12312", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);

        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "fail",
                                                                  "message": "Could not convert database value \"12312\" to Doctrine Type uuid"
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testUpdateUuidNotRegisteredShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('PUT', self::CATEGORY_ROUTE."/8f0a9c17-d392-4bc1-9acb-3449c6f24d10", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(404);

        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "error",
                                                                  "message": "There is no category with this 8f0a9c17-d392-4bc1-9acb-3449c6f24d10"
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testUpdateDescriptionEqualToAnExistentShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::CATEGORY_ROUTE, [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $this->client->request('POST', self::CATEGORY_ROUTE, ["description"=>"work"], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $category = json_decode($this->client->getResponse()->getContent(),true);
        $category_id = $category["data"]["id"];

        $this->client->request('PUT', self::CATEGORY_ROUTE."/".$category_id, ["description"=>"general"],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(405);

        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "error",
                                                                  "message": "Same description not allowed in more than one category!"
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testUpdateDescriptionSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::CATEGORY_ROUTE, ["description"=>"work"], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $category = json_decode($this->client->getResponse()->getContent(),true);
        $category_id = $category["data"]["id"];

        $this->client->request('PUT', self::CATEGORY_ROUTE."/".$category_id, ["description"=>"home"],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);
    }

    public function testUpdateStatusToInvalidShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::CATEGORY_ROUTE, ["description"=>"work"], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $category = json_decode($this->client->getResponse()->getContent(),true);
        $category_id = $category["data"]["id"];

        $this->client->request('PUT', self::CATEGORY_ROUTE."/".$category_id."/status/blocking", [],[], ["HTTP_Authorization" => $result["token"]]);
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

        $this->client->request('POST', self::CATEGORY_ROUTE, [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $category = json_decode($this->client->getResponse()->getContent(),true);
        $category_id = $category["data"]["id"];

        $this->client->request('PUT', self::CATEGORY_ROUTE."/".$category_id."/status/disable", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('PUT', self::CATEGORY_ROUTE."/".$category_id."/status/blocked", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('PUT', self::CATEGORY_ROUTE."/".$category_id."/status/enable", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);
    }

    public function testDeleteUnregisteredCategoryShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('DELETE', self::CATEGORY_ROUTE."/8f0a9c17-d392-4bc1-9acb-3449c6f24d10", [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(404);
    }

    public function testDeleteSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::CATEGORY_ROUTE, [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $category = json_decode($this->client->getResponse()->getContent(),true);
        $category_id = $category["data"]["id"];

        $this->client->request('DELETE', self::CATEGORY_ROUTE."/$category_id", [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);
    }

    public function testListShowDeletedSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::CATEGORY_ROUTE, [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $category = json_decode($this->client->getResponse()->getContent(),true);
        $category_id = $category["data"]["id"];

        $this->client->request('POST', self::CATEGORY_ROUTE, ["description"=>"car"], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $this->client->request('POST', self::CATEGORY_ROUTE, ["description"=>"ultra"], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $this->client->request('GET', self::CATEGORY_ROUTE, [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(3, $res["data"]);

        $this->client->request('DELETE', self::CATEGORY_ROUTE."/".$category_id, [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('GET', self::CATEGORY_ROUTE, [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $item = [];
        foreach($res["data"] as $category)
        {
            if ($category["id"]===$category_id) {
                $item = $category;
            }
        }

        $this->assertCount(3, $res["data"]);
        $this->assertNotEmpty($item["deleted_at"]);
    }

    public function testUpdateStatusSettingToDefaultShouldChangeOthersToNotDefaultSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::CATEGORY_ROUTE, [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $category = json_decode($this->client->getResponse()->getContent(),true);
        $category_id = $category["data"]["id"];
        sleep(1);

        $this->client->request('POST', self::CATEGORY_ROUTE, ["description"=>"tech"], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $category = json_decode($this->client->getResponse()->getContent(),true);
        $category_two_id = $category["data"]["id"];

        $this->client->request('POST', self::CATEGORY_ROUTE, ["description"=>"ultra"], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $this->client->request('GET', self::CATEGORY_ROUTE, [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(3, $res["data"]);
        $this->assertCount(10, $res["data"][0]);
        $this->assertCount(10, $res["data"][1]);
        $this->assertCount(10, $res["data"][2]);
        $this->assertEquals(false, $res["data"][0]["is_default"]);
        $this->assertEquals(false, $res["data"][1]["is_default"]);
        $this->assertEquals(false, $res["data"][2]["is_default"]);

        $this->client->request('PUT', self::CATEGORY_ROUTE."/".$category_id."/default", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('GET', self::CATEGORY_ROUTE, [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(true, $res["data"][0]["is_default"]);
        $this->assertEquals(false, $res["data"][1]["is_default"]);
        $this->assertEquals(false, $res["data"][2]["is_default"]);

        $this->client->request('PUT', self::CATEGORY_ROUTE."/".$category_two_id."/default", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('GET', self::CATEGORY_ROUTE, [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $item = [];
        foreach($res["data"] as $category)
        {
            if ($category["id"]===$category_two_id) {
                $item = $category;
            }
        }

        $this->assertCount(3, $res["data"]);
        $this->assertEquals(true, $item["is_default"]);
    }

    public function testCategoryDisableChangeToEnableIfSetAsDefaultSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::CATEGORY_ROUTE, [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $category = json_decode($this->client->getResponse()->getContent(),true);
        $category_id = $category["data"]["id"];

        $this->client->request('PUT', self::CATEGORY_ROUTE."/".$category_id."/status/disable", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('GET', self::CATEGORY_ROUTE."/".$category_id, [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($res["data"]["status"], "disable");
        $this->assertEquals($res["data"]["is_default"], false);

        $this->client->request('PUT', self::CATEGORY_ROUTE."/".$category_id."/default", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('GET', self::CATEGORY_ROUTE."/".$category_id, [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($res["data"]["status"], "enable");
        $this->assertEquals($res["data"]["is_default"], true);
    }

    public function testCategoryToDisableShouldSetToNotDefaultTooSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::CATEGORY_ROUTE, [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $category = json_decode($this->client->getResponse()->getContent(),true);
        $category_id = $category["data"]["id"];

        $this->client->request('PUT', self::CATEGORY_ROUTE."/".$category_id."/default", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('GET', self::CATEGORY_ROUTE."/".$category_id, [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($res["data"]["status"], "enable");
        $this->assertEquals($res["data"]["is_default"], true);

        $this->client->request('PUT', self::CATEGORY_ROUTE."/".$category_id."/status/disable", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('GET', self::CATEGORY_ROUTE."/".$category_id, [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($res["data"]["status"], "disable");
        $this->assertEquals($res["data"]["is_default"], false);
    }

    public function testRemoveWithIDThatNotExistShouldFail()
    {
        $result = $this->getTokenAuthenticate();
        $this->client->request('DELETE', self::CATEGORY_ROUTE."/0b4084ff-c7a1-4fa6-9029-3c50ee41b838", [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(404);
        $this->assertJson($this->client->getResponse()->getContent());

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $expected = [
            "status"=> "error",
            "message"=> 'There is no category with this 0b4084ff-c7a1-4fa6-9029-3c50ee41b838'
        ];
        $this->assertEquals($expected, $res);
    }

    public function testRemoveWithIDIncorrectShouldFail()
    {
        $result = $this->getTokenAuthenticate();
        $this->client->request('DELETE', self::CATEGORY_ROUTE."/0b4084ff", [], [], ["HTTP_Authorization" => $result["token"]]);
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
        $this->client->request('POST', self::CATEGORY_ROUTE, [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $category = json_decode($this->client->getResponse()->getContent(),true);
        $category_id = $category["data"]["id"];

        $this->client->request('GET', self::CATEGORY_ROUTE, [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(1, $res["data"]);
        $this->assertCount(10, $res["data"][0]);
        $this->assertEmpty($res["data"][0]["deleted_at"]);
        $this->assertEquals($category_id, $res["data"][0]["id"]);


        $this->client->request('DELETE', self::CATEGORY_ROUTE."/".$category_id, [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);
        $this->assertEmpty($this->client->getResponse()->getContent());

        $this->client->request('GET', self::CATEGORY_ROUTE, [], [], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertNotEmpty($res["data"][0]["deleted_at"]);
    }

    public function testTryShowCategoryFromOtherUserShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $category = $this->registerCategory(self::CATEGORY_ROUTE, [], $result["token"]);

        $this->client->request('GET', self::CATEGORY_ROUTE."/".$category["id"], [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($category["id"], $res["data"]["id"]);

        $anotherUser = $result = $this->getTokenAuthenticate("my@mail.com", "123456");

        $this->client->request('GET', self::CATEGORY_ROUTE."/".$category["id"], [],[], ["HTTP_Authorization" => $anotherUser["token"]]);
        $this->assertResponseStatusCodeSame(404);
        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "error",
                                                                  "message": "There is no category with this '.$category["id"].'"
                                                                }', $this->client->getResponse()->getContent());
    }
}