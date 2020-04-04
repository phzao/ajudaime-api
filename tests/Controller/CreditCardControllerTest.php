<?php

namespace App\Tests\Controller;

use App\Tests\Authenticate;
use App\Tests\Entity\RegisterCreditCard;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @package App\Tests\Controller
 */
class CreditCardControllerTest extends WebTestCase
{
    use Authenticate, RegisterCreditCard;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    private $client;

    const CREDIT_CARD_ROUTE = '/api/v1/credit-cards';

    public function setUp()
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    public function testShowWithUserNotLoggedShouldFail()
    {
        $this->client->request('POST', self::CREDIT_CARD_ROUTE);
        $this->assertResponseStatusCodeSame(401);
    }

    public function testRegisterWithoutDataShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::CREDIT_CARD_ROUTE, [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "fail",
                                                                  "data": {
                                                                    "last_digits": "Last Digits is required!",
                                                                    "card_banner": "Card Banner is required!"
                                                                  }
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testRegisterWithLastDigitAsStringShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::CREDIT_CARD_ROUTE, ["last_digits"=>"asdsa"],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "fail",
                                                                  "data": {
                                                                    "last_digits": "Last Digits must be an integer!",
                                                                    "card_banner": "Card Banner is required!"
                                                                  }
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testRegisterWithLastDigitWithNegativeShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::CREDIT_CARD_ROUTE, ["last_digits"=>-999],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "fail",
                                                                  "data": {
                                                                    "last_digits": "This value should be between 0 and 9999.",
                                                                    "card_banner": "Card Banner is required!"
                                                                  }
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testRegisterWithCardBannerGreaterThan20ShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('POST', self::CREDIT_CARD_ROUTE, ["card_banner"=> "sadfjsadkl sjdfaslkdj flsadk flaskdjf laskjdf laksdjf"],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "fail",
                                                                  "data": {
                                                                    "last_digits": "Last Digits is required!",
                                                                    "card_banner": "Card Banner type is invalid!."
                                                                  }
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testRegisterWithDueDateAsStringShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $data = [
            "card_banner"=> "visa",
            "last_digits" => 9999,
            "due_date" => "assda"
        ];

        $this->client->request('POST', self::CREDIT_CARD_ROUTE, $data,[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "fail",
                                                                  "data": {
                                                                    "due_date": "The Due Date must be an day of month!"
                                                                  }
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testRegisterWithDueDateGreaterThan30ShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $data = [
            "card_banner"=> "visa",
            "last_digits" => 9999,
            "due_date" => 31
        ];

        $this->client->request('POST', self::CREDIT_CARD_ROUTE, $data,[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "fail",
                                                                  "data": {
                                                                    "due_date": "This value should be between 1 and 30."
                                                                  }
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testRegisterWithDueDateNegativeShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $data = [
            "card_banner"=> "visa",
            "last_digits" => 9999,
            "due_date" => -1
        ];

        $this->client->request('POST', self::CREDIT_CARD_ROUTE, $data,[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "fail",
                                                                  "data": {
                                                                    "due_date": "This value should be between 1 and 30."
                                                                  }
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testRegisterWithLimitValueNegativeShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $data = [
            "card_banner"=> "visa",
            "last_digits" => 9999,
            "limit_value" => -10
        ];

        $this->client->request('POST', self::CREDIT_CARD_ROUTE, $data,[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "fail",
                                                                  "data": {
                                                                    "limit_value": "Limit Value must be a decimal equal or greater than zero!"
                                                                  }
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testRegisterWithLimitValueAsStringShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $data = [
            "card_banner"=> "visa",
            "last_digits" => 9999,
            "limit_value" => "asdsad"
        ];

        $this->client->request('POST', self::CREDIT_CARD_ROUTE, $data,[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "fail",
                                                                  "data": {
                                                                    "limit_value": "limit Value must be a decimal equal or greater than zero!"
                                                                  }
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testRegisterWithLimitValidityAsStringShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $data = [
            "card_banner"=> "visa",
            "last_digits" => 9999,
            "validity" => "asdsad"
        ];

        $this->client->request('POST', self::CREDIT_CARD_ROUTE, $data,[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "fail",
                                                                  "data": {
                                                                     "validity": "Validity is invalid, should be in format Y-m-d"
                                                                  }
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testRegisterWithLimitValidityAsIntegerShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $data = [
            "card_banner"=> "visa",
            "last_digits" => 9999,
            "validity" => 9323
        ];

        $this->client->request('POST', self::CREDIT_CARD_ROUTE, $data,[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "fail",
                                                                  "data": {
                                                                     "validity": "Validity is invalid, should be in format Y-m-d"
                                                                  }
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testRegisterWithDescriptionGreaterThan50ShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $data = [
            "card_banner"=> "visa",
            "last_digits" => 9999,
            "description" => "31839482349 14382394 asdfasldf jasld flaskjdflaskdj flasdkjflasdf"
        ];

        $this->client->request('POST', self::CREDIT_CARD_ROUTE, $data,[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "fail",
                                                                  "data": {
                                                                     "description": "Description cannot be longer than 50 characters"
                                                                  }
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testRegisterWithTheMinimumDataShouldSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $data = [
            "card_banner"=> "visa",
            "last_digits" => 9999
        ];

        $this->client->request('POST', self::CREDIT_CARD_ROUTE, $data,[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);
        $this->assertJson($this->client->getResponse()->getContent());

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(15, $res["data"]);
        $this->assertEmpty($res["data"]["updated_at"]);
        $this->assertIsString($res["data"]["created_at"]);
        $this->assertIsString($res["data"]["user_id"]);
        $this->assertIsNumeric($res["data"]["due_date"]);
        $this->assertEquals(1, $res["data"]["due_date"]);
        $this->assertNull($res["data"]["limit_value"]);
        $this->assertEmpty($res["data"]["validity"]);
        $this->assertEquals("enable", $res["data"]["status"]);
        $this->assertEquals("visa", $res["data"]["card_banner"]);
        $this->assertEquals(9999, $res["data"]["last_digits"]);
        $this->assertFalse($res["data"]["is_default"]);
    }

    public function testRegisterWithAllDataFilledShouldSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $data = [
            "card_banner"=> "visa",
            "last_digits" => 9999,
            "description" => "My Personal Card",
            "validity" => "2020-10-01",
            "limit_value" => 1000,
            "due_date" => 1
        ];

        $this->client->request('POST', self::CREDIT_CARD_ROUTE, $data,[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(201);
        $this->assertJson($this->client->getResponse()->getContent());
        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(15, $res["data"]);
        $this->assertEmpty($res["data"]["updated_at"]);
        $this->assertIsString($res["data"]["created_at"]);
        $this->assertIsString($res["data"]["user_id"]);
        $this->assertEquals("My Personal Card", $res["data"]["description"]);
        $this->assertEquals("2020-10-01", $res["data"]["validity"]);
        $this->assertEquals(1000, $res["data"]["limit_value"]);
        $this->assertEquals(1, $res["data"]["due_date"]);
        $this->assertEquals("enable", $res["data"]["status"]);
        $this->assertEquals("visa", $res["data"]["card_banner"]);
        $this->assertEquals(9999, $res["data"]["last_digits"]);
        $this->assertFalse($res["data"]["is_default"]);
    }

    public function testUpdateWithInvalidIDShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('PUT', self::CREDIT_CARD_ROUTE."/5fe2e400-29fe-4a2e-aca1-f3850a2ea04a", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(404);
        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "error",
                                                                  "message": "There is no credit card with this 5fe2e400-29fe-4a2e-aca1-f3850a2ea04a"
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testUpdateWithWrongUUIDShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('PUT', self::CREDIT_CARD_ROUTE."/asdfasdf", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "fail",
                                                                  "message": "Could not convert database value \"asdfasdf\" to Doctrine Type uuid"
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testUpdateWithDescriptionGreaterThan50ShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $creditCard = $this->registerCredit(self::CREDIT_CARD_ROUTE, ["card_banner" => "visa", "last_digits"=>9020], $result["token"]);

        $this->client->request('PUT', self::CREDIT_CARD_ROUTE."/".$creditCard["id"],
                               ["description"=>"asldfjksald lfkjasdl kfjasldkj lkasjdflaksjdflkasjdflajfsdla ksdjf lkasdjflsad"],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "fail",
                                                                  "data": {
                                                                    "description": "Description cannot be longer than 50 characters"
                                                                  }
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testUpdateWithDescriptionEmptyShouldSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $creditCard = $this->registerCredit(self::CREDIT_CARD_ROUTE, ["card_banner" => "visa", "last_digits"=>9020, "description"=>"my Data"], $result["token"]);

        $this->assertEquals("my Data", $creditCard["description"]);

        $this->client->request('PUT', self::CREDIT_CARD_ROUTE."/".$creditCard["id"], ["description"=>""],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('GET', self::CREDIT_CARD_ROUTE."/".$creditCard["id"], [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEmpty($res["data"]["description"]);
    }

    public function testUpdateLimitValueToStringShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $creditCard = $this->registerCredit(self::CREDIT_CARD_ROUTE, ["card_banner" => "visa", "last_digits"=>9020], $result["token"]);

        $this->client->request('PUT', self::CREDIT_CARD_ROUTE."/".$creditCard["id"], ["limit_value"=>"asdd"],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "fail",
                                                                  "data": {
                                                                    "limit_value": "limit Value must be a decimal equal or greater than zero!"
                                                                  }
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testUpdateLimitValueToNegativeShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $creditCard = $this->registerCredit(self::CREDIT_CARD_ROUTE, ["card_banner" => "visa", "last_digits"=>9020, "limit_value"=>1000], $result["token"]);

        $this->client->request('PUT', self::CREDIT_CARD_ROUTE."/".$creditCard["id"],
                               ["limit_value"=>-10],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "fail",
                                                                  "data": {
                                                                    "limit_value": "Limit Value must be a decimal equal or greater than zero!"
                                                                  }
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testUpdateLimitValueToNullShouldSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $creditCard = $this->registerCredit(self::CREDIT_CARD_ROUTE, ["card_banner" => "visa", "last_digits"=>9020, "limit_value"=>1000], $result["token"]);

        $this->client->request('PUT', self::CREDIT_CARD_ROUTE."/".$creditCard["id"], ["limit_value"=>null],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('GET', self::CREDIT_CARD_ROUTE."/".$creditCard["id"], [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEmpty($res["data"]["limit_value"]);
    }

    public function testUpdateDueDateGreaterThan30ShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $creditCard = $this->registerCredit(self::CREDIT_CARD_ROUTE, ["card_banner" => "visa", "last_digits"=>9020, "due_date"=>5], $result["token"]);

        $this->client->request('PUT', self::CREDIT_CARD_ROUTE."/".$creditCard["id"], ["due_date"=>50],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "fail",
                                                                  "data": {
                                                                    "due_date": "This value should be between 1 and 30."
                                                                  }
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testUpdateDueDateToStringShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $creditCard = $this->registerCredit(self::CREDIT_CARD_ROUTE, ["card_banner" => "visa", "last_digits"=>9020, "due_date"=>10], $result["token"]);

        $this->client->request('PUT', self::CREDIT_CARD_ROUTE."/".$creditCard["id"],
                               ["due_date"=>"asas"],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "fail",
                                                                  "data": {
                                                                      "due_date": "The Due Date must be an day of month!"
                                                                  }
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testUpdateDueDateWithAValidDayShouldSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $creditCard = $this->registerCredit(self::CREDIT_CARD_ROUTE, ["card_banner" => "visa", "last_digits"=>9020], $result["token"]);

        $this->client->request('PUT', self::CREDIT_CARD_ROUTE."/".$creditCard["id"], ["due_date"=>10],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('GET', self::CREDIT_CARD_ROUTE."/".$creditCard["id"], [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(10, $res["data"]["due_date"]);
    }

    public function testUpdateValidityToStringShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $creditCard = $this->registerCredit(self::CREDIT_CARD_ROUTE, ["card_banner" => "visa", "last_digits"=>9020], $result["token"]);

        $this->client->request('PUT', self::CREDIT_CARD_ROUTE."/".$creditCard["id"], ["validity"=>"as as"],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonStringEqualsJsonString('{
                                                                "status": "fail",
                                                                "data": {
                                                                    "validity": "Validity is invalid, should be in format Y-m-d"
                                                                }
                                                            }', $this->client->getResponse()->getContent());
    }

    public function testUpdateValidityToNullShouldSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $creditCard = $this->registerCredit(self::CREDIT_CARD_ROUTE, ["card_banner" => "visa", "last_digits"=>9020, "validity"=>"2010-01-01"], $result["token"]);

        $this->client->request('PUT', self::CREDIT_CARD_ROUTE."/".$creditCard["id"], ["validity"=>null],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('GET', self::CREDIT_CARD_ROUTE."/".$creditCard["id"], [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEmpty($res["data"]["validity"]);
    }

    public function testUpdateValidityToEmptyShouldSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $creditCard = $this->registerCredit(self::CREDIT_CARD_ROUTE, ["card_banner" => "visa", "last_digits"=>9020], $result["token"]);

        $this->client->request('PUT', self::CREDIT_CARD_ROUTE."/".$creditCard["id"], ["validity"=>""],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);
    }

    public function testUpdateCardBannerToEmptyShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $creditCard = $this->registerCredit(self::CREDIT_CARD_ROUTE, ["card_banner" => "visa", "last_digits"=>9020], $result["token"]);

        $this->client->request('PUT', self::CREDIT_CARD_ROUTE."/".$creditCard["id"], ["card_banner"=>''],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "fail",
                                                                  "data": {
                                                                    "card_banner": "Card Banner type is invalid!."
                                                                  }
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testUpdateCardBannerToMastercardShouldSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $creditCard = $this->registerCredit(self::CREDIT_CARD_ROUTE, ["card_banner" => "visa", "last_digits"=>9020], $result["token"]);

        $this->client->request('PUT', self::CREDIT_CARD_ROUTE."/".$creditCard["id"], ["card_banner"=>'mastercard'],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('GET', self::CREDIT_CARD_ROUTE."/".$creditCard["id"], [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals("mastercard", $res["data"]["card_banner"]);
    }

    public function testUpdateLastDigitsToEmptyShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $creditCard = $this->registerCredit(self::CREDIT_CARD_ROUTE, ["card_banner" => "visa", "last_digits"=>9020], $result["token"]);

        $this->client->request('PUT', self::CREDIT_CARD_ROUTE."/".$creditCard["id"], ["last_digits"=>''],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "fail",
                                                                  "data": {
                                                                     "last_digits": "Last Digits must be an integer!"
                                                                  }
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testUpdateLastDigitsToAnotherValidShouldSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $creditCard = $this->registerCredit(self::CREDIT_CARD_ROUTE, ["card_banner" => "visa", "last_digits"=>9020], $result["token"]);

        $this->client->request('PUT', self::CREDIT_CARD_ROUTE."/".$creditCard["id"], ["last_digits"=>1111],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('GET', self::CREDIT_CARD_ROUTE."/".$creditCard["id"], [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(1111, $res["data"]["last_digits"]);
    }

    public function testUpdateStatusToInvalidStatusShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $creditCard = $this->registerCredit(self::CREDIT_CARD_ROUTE, ["card_banner" => "visa", "last_digits"=>9020], $result["token"]);

        $this->client->request('PUT', self::CREDIT_CARD_ROUTE."/".$creditCard["id"]."/status/ast", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "fail",
                                                                  "data": {
                                                                     "status": "This status ast is invalid!"
                                                                  }
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testUpdateStatusToDisableShouldSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $creditCard = $this->registerCredit(self::CREDIT_CARD_ROUTE, ["card_banner" => "visa", "last_digits"=>9020], $result["token"]);

        $this->client->request('PUT', self::CREDIT_CARD_ROUTE."/".$creditCard["id"]."/status/disable", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('GET', self::CREDIT_CARD_ROUTE."/".$creditCard["id"], [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals("disable", $res["data"]["status"]);
    }

    public function testUpdateStatusWithInvalidIDShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('PUT', self::CREDIT_CARD_ROUTE."/9e025b4f-8451-4a2a-9d0f-c7a4647b9739/status/disable", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(404);
        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "error",
                                                                  "message": "There is no credit card with this 9e025b4f-8451-4a2a-9d0f-c7a4647b9739"
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testUpdateStatusWithUUIDInvalidShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('PUT', self::CREDIT_CARD_ROUTE."/asdsadasd/status/disable", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "fail",
                                                                  "message": "Could not convert database value \"asdsadasd\" to Doctrine Type uuid"
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testListShouldSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('GET', self::CREDIT_CARD_ROUTE, [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(0, $res["data"]);

        $creditCard = $this->registerCredit(self::CREDIT_CARD_ROUTE, ["card_banner" => "visa", "last_digits"=>9020], $result["token"]);

        $this->client->request('GET', self::CREDIT_CARD_ROUTE, [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(1, $res["data"]);
        $this->assertEquals($creditCard["card_banner"], $res["data"][0]["card_banner"]);
        $this->assertEquals($creditCard["last_digits"], $res["data"][0]["last_digits"]);
    }


    public function testShowWithUUIDInvalidShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('GET', self::CREDIT_CARD_ROUTE."/asdasda", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "fail",
                                                                  "message": "Could not convert database value \"asdasda\" to Doctrine Type uuid"
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testShowWithInvalidIdShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('GET', self::CREDIT_CARD_ROUTE."/9e021b4f-8451-4a1a-9d0f-c7a4647b9739", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(404);
        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "error",
                                                                  "message": "There is no credit card with this 9e021b4f-8451-4a1a-9d0f-c7a4647b9739"
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testShowValidIdShouldSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $creditCard = $this->registerCredit(self::CREDIT_CARD_ROUTE, ["card_banner" => "visa", "last_digits"=>9020], $result["token"]);

        $this->client->request('GET', self::CREDIT_CARD_ROUTE."/".$creditCard["id"], [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($creditCard["id"], $res["data"]["id"]);
    }

    public function testTryShowCreditCardFromOtherUserShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $creditCard = $this->registerCredit(self::CREDIT_CARD_ROUTE, ["card_banner" => "visa", "last_digits"=>9020], $result["token"]);

        $this->client->request('GET', self::CREDIT_CARD_ROUTE."/".$creditCard["id"], [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($creditCard["id"], $res["data"]["id"]);

        $anotherUser = $result = $this->getTokenAuthenticate("my@mail.com", "123456");

        $this->client->request('GET', self::CREDIT_CARD_ROUTE."/".$creditCard["id"], [],[], ["HTTP_Authorization" => $anotherUser["token"]]);
        $this->assertResponseStatusCodeSame(404);
        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "error",
                                                                  "message": "There is no credit card with this '.$creditCard["id"].'"
                                                                }', $this->client->getResponse()->getContent());
        
    }

    public function testSetDisableFromEnableShouldSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $creditCard = $this->registerCredit(self::CREDIT_CARD_ROUTE, ["card_banner" => "visa", "last_digits"=>9020], $result["token"]);
        $this->assertEquals("enable", $creditCard["status"]);

        $this->client->request('PUT', self::CREDIT_CARD_ROUTE."/".$creditCard["id"]."/status/disable", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('GET', self::CREDIT_CARD_ROUTE."/".$creditCard["id"], [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals("disable", $res["data"]["status"]);
    }

    public function testSetEnableFromADisableCreditCardShouldSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $creditCard = $this->registerCredit(self::CREDIT_CARD_ROUTE, ["card_banner" => "visa", "last_digits"=>9020], $result["token"]);
        $this->assertEquals("enable", $creditCard["status"]);

        $this->client->request('PUT', self::CREDIT_CARD_ROUTE."/".$creditCard["id"]."/status/disable", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('GET', self::CREDIT_CARD_ROUTE."/".$creditCard["id"], [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals("disable", $res["data"]["status"]);

        $this->client->request('PUT', self::CREDIT_CARD_ROUTE."/".$creditCard["id"]."/status/enable", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('GET', self::CREDIT_CARD_ROUTE."/".$creditCard["id"], [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals("enable", $res["data"]["status"]);
    }

    public function testSetDisableFromACreditCardDefaultShouldChangeNonDefaultShouldSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $creditCard = $this->registerCredit(self::CREDIT_CARD_ROUTE, ["card_banner" => "visa", "last_digits"=>9020], $result["token"]);
        $this->assertEquals("enable", $creditCard["status"]);

        $this->client->request('PUT', self::CREDIT_CARD_ROUTE."/".$creditCard["id"]."/default", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('PUT', self::CREDIT_CARD_ROUTE."/".$creditCard["id"]."/status/disable", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('GET', self::CREDIT_CARD_ROUTE."/".$creditCard["id"], [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals("disable", $res["data"]["status"]);
        $this->assertEquals(false, $res["data"]["is_default"]);
    }

    public function testSetDefaultShouldSetEnableTooSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $creditCard = $this->registerCredit(self::CREDIT_CARD_ROUTE, ["card_banner" => "visa", "last_digits"=>9020], $result["token"]);

        $this->assertEquals("enable", $creditCard["status"]);

        $this->client->request('PUT', self::CREDIT_CARD_ROUTE."/".$creditCard["id"]."/status/disable", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('GET', self::CREDIT_CARD_ROUTE."/".$creditCard["id"], [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals("disable", $res["data"]["status"]);
        $this->assertEquals(false, $res["data"]["is_default"]);

        $this->client->request('PUT', self::CREDIT_CARD_ROUTE."/".$creditCard["id"]."/default", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);


        $this->client->request('GET', self::CREDIT_CARD_ROUTE."/".$creditCard["id"], [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals("enable", $res["data"]["status"]);
        $this->assertEquals(true, $res["data"]["is_default"]);
    }

    public function testRemoveIdInvalidShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('DELETE', self::CREDIT_CARD_ROUTE."/9e021b4f-8451-4a1a-9d0f-c7a4647b973a", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(404);
        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "error",
                                                                  "message": "There is no credit card with this 9e021b4f-8451-4a1a-9d0f-c7a4647b973a"
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testRemoveInvalidUUIDShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('DELETE', self::CREDIT_CARD_ROUTE."/assds", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "fail",
                                                                  "message": "Could not convert database value \"assds\" to Doctrine Type uuid"
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testRemoveWithAValidUUIDShouldSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $creditCard = $this->registerCredit(self::CREDIT_CARD_ROUTE, ["card_banner" => "visa", "last_digits"=>9020], $result["token"]);

        $this->client->request('GET', self::CREDIT_CARD_ROUTE."/".$creditCard["id"], [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEmpty($res["data"]["deleted_at"]);

        $this->client->request('DELETE', self::CREDIT_CARD_ROUTE."/".$creditCard["id"], [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('GET', self::CREDIT_CARD_ROUTE."/".$creditCard["id"], [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(200);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertNotEmpty($res["data"]["deleted_at"]);
    }
}