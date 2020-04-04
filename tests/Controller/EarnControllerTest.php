<?php

namespace App\Tests\Controller;

use App\Tests\Authenticate;
use App\Tests\Entity\RegisterEarn;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @package App\Tests\Controller
 */
class EarnControllerTest extends WebTestCase
{
    use Authenticate, RegisterEarn;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    private $client;

    const EARN_ROUTE = '/api/v1/earns';

    public function setUp()
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    public function testShowWithUserNotLoggedShouldFail()
    {
        $this->client->request('POST', self::EARN_ROUTE);
        $this->assertResponseStatusCodeSame(401);
    }

    public function testRegisterWithoutDataShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::EARN_ROUTE, [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertJsonStringEqualsJsonString('{
                                          "status": "fail",
                                          "data": {
                                            "value": "A Value is required!"
                                          }
                                        }', $this->client->getResponse()->getContent());
    }

    public function testRegisterWithAValueNonDecimalValueShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::EARN_ROUTE, ["value"=>"hi"],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);

        $this->assertJsonStringEqualsJsonString('{
                                          "status": "fail",
                                          "data": {
                                            "value": "Value must be a decimal equal or greater than zero!"
                                          }
                                        }', $this->client->getResponse()->getContent());
    }

    public function testRegisterWithAValueNegativeShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::EARN_ROUTE, ["value"=>-10],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);

        $this->assertJsonStringEqualsJsonString('{
                                          "status": "fail",
                                          "data": {
                                            "value": "Value must be a decimal equal or greater than zero!"
                                          }
                                        }', $this->client->getResponse()->getContent());
    }

    public function testRegisterWithDescriptionGreaterThan50AndValueRightShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::EARN_ROUTE, [
            "value" => 10.00,
            "description" => "sadfjalsdfjla kjl jsaldjf alsdkf asldjf alksdjflkasd flaks;dfjasl dfalsdjf lasdjf lasdkjfalksdf lkjsadfj aslkdj falskj l2kj3 4l2kj3 4l2j3kl4j23lk4j23 42j3k4l2 34l23jk 4l2jk34 l2k3jl"
        ],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);

        $this->assertJsonStringEqualsJsonString('{
                              "status": "fail",
                              "data": {
                                "description": "Description cannot be longer than 50 characters"
                              }
                            }', $this->client->getResponse()->getContent());
    }

    public function testWithEarnAtWrongAndValueRightShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::EARN_ROUTE, [
            "value" => 10.00,
            "earn_at" => "asdfasd"
        ],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);

        $this->assertJsonStringEqualsJsonString('{
                              "status": "fail",
                              "data": {
                                "earn_at": "Earn At is invalid, should be in format Y-m-d"
                              }
                            }', $this->client->getResponse()->getContent());
    }

    public function testRegisterWithDescriptionGreaterThan50AndValueEmptyShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::EARN_ROUTE, [
            "description" => "sadfjalsdfjla kjl jsaldjf alsdkf asldjf alksdjflkasd flaks;dfjasl dfalsdjf lasdjf lasdkjfalksdf lkjsadfj aslkdj falskj l2kj3 4l2kj3 4l2j3kl4j23lk4j23 42j3k4l2 34l23jk 4l2jk34 l2k3jl"
        ],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);

        $this->assertJsonStringEqualsJsonString('{
                              "status": "fail",
                              "data": {
                                "description": "Description cannot be longer than 50 characters",
                                "value": "A Value is required!"
                              }
                            }', $this->client->getResponse()->getContent());
    }

    public function testWithConfirmedAtWrongAndValueRightShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::EARN_ROUTE, [
            "value" => 10.00,
            "confirmed_at" => "asdfasd"
        ],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);

        $this->assertJsonStringEqualsJsonString('{
                              "status": "fail",
                              "data": {
                                "confirmed_at": "Confirmed at is invalid, should be in format Y-m-d H:i:s"
                              }
                            }', $this->client->getResponse()->getContent());
    }

    public function testWithTypeWrongAndValueRightShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::EARN_ROUTE, [
            "value" => 10.00,
            "type" => "asdfasd"
        ],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);

        $this->assertJsonStringEqualsJsonString('{
                              "status": "fail",
                              "data": {
                                "type": "The value you selected is not a valid choice."
                              }
                            }', $this->client->getResponse()->getContent());
    }

    public function testWithPlaceIdWrongAndValueRightShouldSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::EARN_ROUTE, [
            "value" => 10.00,
            "place" => "4da08000-9886-4eb5-b9a7-a585154a7f67"
        ],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $this->assertJson($this->client->getResponse()->getContent());
        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(10, $res["data"]);
        $this->assertEquals($res["data"]["value"], 10);
        $this->assertNotEquals($res["data"]["place"]["id"], "4da08000-9886-4eb5-b9a7-a585154a7f67");
        $this->assertNull($res["data"]["description"]);
        $this->assertEmpty($res["data"]["confirmed_at"]);
        $this->assertEquals("moonlighting",$res["data"]["type"]);
        $this->assertEquals("Ganho Extra",$res["data"]["type_description"]);
    }

    public function testRegisterFillAllShouldSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::EARN_ROUTE, [
            "value" => 10.00,
            "description" => "My extra",
            "earn_at" => "2020-01-01",
            "type" => "monthly_allowance"
        ],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);

        $this->assertJson($this->client->getResponse()->getContent());
        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(10, $res["data"]);
        $this->assertEquals($res["data"]["value"], 10);
        $this->assertNotEquals($res["data"]["place"]["id"], "4da08000-9886-4eb5-b9a7-a585154a7f67");
        $this->assertEquals("My extra", $res["data"]["description"]);
        $this->assertEmpty($res["data"]["confirmed_at"]);
        $this->assertEquals("monthly_allowance",$res["data"]["type"]);
        $this->assertEquals("Mesada",$res["data"]["type_description"]);
    }

    public function testUpdateConfirmedAtWithWrongDataShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $earn = $this->registerEarn(self::EARN_ROUTE, ["value"=>10.0], $result["token"]);

        $this->client->request('PUT', self::EARN_ROUTE."/".$earn["id"], ["confirmed_at" => "asdas"],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);


        $this->assertJsonStringEqualsJsonString('{
                                          "status": "fail",
                                          "data": {
                                            "confirmed_at": "Confirmed at is invalid, should be in format Y-m-d H:i:s"
                                          }
                                        }', $this->client->getResponse()->getContent());
    }

    public function testUpdateEarnAtWithWrongDataShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $earn = $this->registerEarn(self::EARN_ROUTE, ["value"=>10.0], $result["token"]);

        $this->client->request('PUT', self::EARN_ROUTE."/".$earn["id"], ["earn_at" => "asdas"],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);


        $this->assertJsonStringEqualsJsonString('{
                                                  "status": "fail",
                                                  "data": {
                                                    "earn_at": "Earn At is invalid, should be in format Y-m-d"
                                                  }
                                                }', $this->client->getResponse()->getContent());
    }

    public function testUpdateTypeWithWrongDataShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $earn = $this->registerEarn(self::EARN_ROUTE, ["value"=>10.0], $result["token"]);

        $this->client->request('PUT', self::EARN_ROUTE."/".$earn["id"], ["type" => "asdas"],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);


        $this->assertJsonStringEqualsJsonString('{
                                          "status": "fail",
                                          "data": {
                                            "type": "The value you selected is not a valid choice."
                                          }
                                        }', $this->client->getResponse()->getContent());
    }

    public function testUpdateValueWithAStringShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $earn = $this->registerEarn(self::EARN_ROUTE, ["value"=>10.0], $result["token"]);

        $this->client->request('PUT', self::EARN_ROUTE."/".$earn["id"], ["value" => "asdas"],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);


        $this->assertJsonStringEqualsJsonString('{
                                          "status": "fail",
                                          "data": {
                                            "value": "Value must be a decimal equal or greater than zero!"
                                          }
                                        }', $this->client->getResponse()->getContent());
    }

    public function testUpdateValueWithNegativeNumberShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $earn = $this->registerEarn(self::EARN_ROUTE, ["value"=>10.0], $result["token"]);

        $this->client->request('PUT', self::EARN_ROUTE."/".$earn["id"], ["value" => -10],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);


        $this->assertJsonStringEqualsJsonString('{
                                          "status": "fail",
                                          "data": {
                                            "value": "Value must be a decimal equal or greater than zero!"
                                          }
                                        }', $this->client->getResponse()->getContent());
    }

    public function testUpdateTrySetEmptyPlaceShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $earn = $this->registerEarn(self::EARN_ROUTE, ["value"=>10.0], $result["token"]);

        $this->client->request('PUT', self::EARN_ROUTE."/".$earn["id"], ["place" => ""],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);


        $this->assertJsonStringEqualsJsonString('{
                                          "status": "fail",
                                          "data": {
                                            "place": "A Place is required!"
                                          }
                                        }', $this->client->getResponse()->getContent());
    }

    public function testUpdateTrySetInvalidUUIDPlaceShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $earn = $this->registerEarn(self::EARN_ROUTE, ["value"=>10.0], $result["token"]);

        $this->client->request('PUT', self::EARN_ROUTE."/".$earn["id"], ["place" => "12342342342"],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);


        $this->assertJsonStringEqualsJsonString('{
                                          "status": "fail",
                                          "message": "Could not convert database value \"12342342342\" to Doctrine Type uuid"
                                        }', $this->client->getResponse()->getContent());
    }

    public function testUpdateTrySetAnInvalidPlaceShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $earn = $this->registerEarn(self::EARN_ROUTE, ["value"=>10.0], $result["token"]);

        $this->client->request('PUT', self::EARN_ROUTE."/".$earn["id"], ["place" => "4da08000-9826-4eb5-b9a7-a585154a7f67"],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(404);


        $this->assertJsonStringEqualsJsonString('{
                                          "status": "error",
                                          "message": "There is no place with this 4da08000-9826-4eb5-b9a7-a585154a7f67"
                                        }', $this->client->getResponse()->getContent());
    }

    public function testUpdateAllShouldSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $earn = $this->registerEarn(self::EARN_ROUTE, ["value"=>10.0], $result["token"]);

        $this->assertCount(10, $earn);
        $this->assertNull($earn["description"]);
        $this->assertEmpty($earn["updated_at"]);
        $this->assertEmpty($earn["confirmed_at"]);
        $this->assertNotEmpty($earn["created_at"]);
        $this->assertNotEmpty($earn["created_at"]);
        $this->assertEquals($earn["value"], 10.0);
        $this->assertEquals($earn["type"], "moonlighting");

        $this->client->request('PUT', self::EARN_ROUTE."/".$earn["id"],
                               [
                                   "value" => 15.5,
                                   "description" => "New Gain",
                                   "confirmed_at" => "2020-02-08 14:09:36",
                                   "earn_at" => "2020-02-05",
                                   "type" => "salary"
                               ],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('GET', self::EARN_ROUTE."/".$earn["id"], [],[], ["HTTP_Authorization" => $result["token"]]);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($res["data"]["value"], 15.5);
        $this->assertEquals($res["data"]["description"], "New Gain");
        $this->assertEmpty($res["data"]["confirmed_at"]);
        $this->assertEquals($res["data"]["earn_at"], "2020-02-05");
        $this->assertEquals($res["data"]["type"], "salary");
    }

    public function testRemoveInvalidUUIDShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('DELETE', self::EARN_ROUTE."/sfadfsdfsdfsa", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);

        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "fail",
                                                                  "message": "This id sfadfsdfsdfsa is invalid!"
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testRemoveNotFoundIdShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('DELETE', self::EARN_ROUTE."/4da08000-9816-4eb5-b9a7-a585154a7f67", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(404);
        $this->assertJsonStringEqualsJsonString('{
                                                      "status": "error",
                                                      "message": "There is no earn with this 4da08000-9816-4eb5-b9a7-a585154a7f67"
                                                    }', $this->client->getResponse()->getContent());

    }

    public function testRemoveAValidEarnShouldSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $earn = $this->registerEarn(self::EARN_ROUTE, ["value" => 10.0], $result["token"]);

        $this->client->request('GET', self::EARN_ROUTE, [],[], ["HTTP_Authorization" => $result["token"]]);

        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(1, $res["data"]);
        $this->assertEquals($earn["id"], $res["data"][0]["id"]);

        $this->client->request('DELETE', self::EARN_ROUTE."/".$earn["id"], [],[], ["HTTP_Authorization" => $result["token"]]);

        $this->assertResponseStatusCodeSame(204);

        $this->client->request('GET', self::EARN_ROUTE, [],[], ["HTTP_Authorization" => $result["token"]]);

        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(0, $res["data"]);
    }

    public function testShowInvalidUUIDShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('GET', self::EARN_ROUTE."/asdasd", [],[], ["HTTP_Authorization" => $result["token"]]);

        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonStringEqualsJsonString('{
                                                  "status": "error",
                                                  "message": "This id asdasd is invalid!"
                                                }', $this->client->getResponse()->getContent());
    }

    public function testShowInvalidIdShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('GET', self::EARN_ROUTE."/ee224fd9-c17b-4daf-a89b-f3b3a8a08700", [],[], ["HTTP_Authorization" => $result["token"]]);

        $this->assertResponseStatusCodeSame(404);
        $this->assertJsonStringEqualsJsonString('{
                                                  "status": "error",
                                                  "message": "There is no earn with this ee224fd9-c17b-4daf-a89b-f3b3a8a08700"
                                                }', $this->client->getResponse()->getContent());
    }

    public function testShowValidIDShouldSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('GET', self::EARN_ROUTE, [],[], ["HTTP_Authorization" => $result["token"]]);

        $this->assertResponseStatusCodeSame(200);
        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(0, $res["data"]);

        $earn = $this->registerEarn(self::EARN_ROUTE, ["value" => 10.0], $result["token"]);

        $this->client->request('GET', self::EARN_ROUTE, [],[], ["HTTP_Authorization" => $result["token"]]);

        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(1, $res["data"]);
        $this->assertEquals($earn["id"], $res["data"][0]["id"]);
        $this->assertCount(10, $res["data"][0]);
    }

    public function testConfirmWithInvalidUUIDShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('PUT', self::EARN_ROUTE."/asdasd/confirm", [],[], ["HTTP_Authorization" => $result["token"]]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonStringEqualsJsonString('{
                                                  "status": "fail",
                                                  "message": "This id asdasd is invalid!"
                                                }', $this->client->getResponse()->getContent());
    }

    public function testConfirmWithInvalidIdShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('PUT', self::EARN_ROUTE."/ee224fd9-c17b-4daf-a89b-f3b3a8a08700/confirm", [],[], ["HTTP_Authorization" => $result["token"]]);

        $this->assertResponseStatusCodeSame(404);
        $this->assertJsonStringEqualsJsonString('{
                                                  "status": "error",
                                                  "message": "There is no earn with this ee224fd9-c17b-4daf-a89b-f3b3a8a08700"
                                                }', $this->client->getResponse()->getContent());
    }

    public function testConfirmSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $earn = $this->registerEarn(self::EARN_ROUTE, ["value" => 10.0], $result["token"]);

        $this->assertEmpty($earn["confirmed_at"]);

        $this->client->request('PUT', self::EARN_ROUTE."/".$earn["id"]."/confirm", [],[], ["HTTP_Authorization" => $result["token"]]);

        $this->assertResponseStatusCodeSame(204);

        $this->client->request('GET', self::EARN_ROUTE."/".$earn["id"], [],[], ["HTTP_Authorization" => $result["token"]]);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(10, $res["data"]);
        $this->assertIsString($res["data"]["confirmed_at"]);
    }
}