<?php

namespace App\Tests\Controller;

use App\Tests\Authenticate;
use App\Tests\CleanElasticSearch;
use App\Tests\Entity\RegisterNeed;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @package App\Tests\Controller
 */
class DonationControllerTest extends WebTestCase
{
    use CleanElasticSearch, Authenticate, RegisterNeed;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    private $client;

    const DONATION_ROUTE = "/api/v1/donations";
    const NEED_ROUTE = "/api/v1/needs";

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

    public function testRegisterDonationWithNeedIdWrongShouldFail()
    {
        $token = $this->getTokenAuthenticate();

        $this->client->request('POST', self::DONATION_ROUTE."/2341412", [],[], $token);
        $this->assertResponseStatusCodeSame(404);
        $this->assertJsonStringEqualsJsonString('{"status":"error","message":"Lista n\u00e3o localizada"}',
                                                $this->client->getResponse()->getContent());
    }

    public function testRegisterDonationWithANeedValidShouldSuccess()
    {
        $token = $this->getTokenAuthenticate();

        $data["needsList"] = "my message to";
        $data["message"] = "Food";
        $this->client->request('POST', self::NEED_ROUTE, $data,[], $token);
        $this->assertResponseStatusCodeSame(201);
        sleep(1);

        $res = json_decode($this->client->getResponse()->getContent(), true);

        $need = $res["data"];

        $this->client->request('POST', self::DONATION_ROUTE."/".$need["id"], [],[], $token);
        $this->assertResponseStatusCodeSame(201);
    }

    public function testRegisterMoreThan3DonationsShouldFail()
    {
        $userOne = $this->getNewUserAndNeed("me1@you.com", "Package", "beacause");
        $userTwo = $this->getNewUserAndNeed("me2@you.com", "Package", "beacause");
        $userThree = $this->getNewUserAndNeed("me3@you.com", "Package", "beacause");
        $userFour = $this->getNewUserAndNeed("me4@you.com", "Package", "beacause");

        $token = $this->getTokenAuthenticate();

        $this->client->request('POST', self::DONATION_ROUTE."/".$userOne["need"]["id"], [],[], $token);
        $this->assertResponseStatusCodeSame(201);
        sleep(1);
        $this->client->request('POST', self::DONATION_ROUTE."/".$userThree["need"]["id"], [],[], $token);
        $this->assertResponseStatusCodeSame(201);
        sleep(1);
        $this->client->request('POST', self::DONATION_ROUTE."/".$userTwo["need"]["id"], [],[], $token);
        $this->assertResponseStatusCodeSame(201);
        sleep(1);
        $this->client->request('POST', self::DONATION_ROUTE."/".$userFour["need"]["id"], [],[], $token);
        $this->assertResponseStatusCodeSame(201);
        sleep(1);
        $res = json_decode($this->client->getResponse()->getContent(), true);

        $donation = $res["data"];
        dump($res);exit;
    }
}