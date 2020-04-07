<?php

namespace App\Tests;

/**
 * @package App\Tests
 */
trait Authenticate
{
    protected $loginRoute = "/public/login/teste";
    protected $email = "me@me.com";

    public function getTokenAuthenticate($email = "me@me.com")
    {
        $emailToRegister = empty($email)?$this->email: $email;

        $this->client->request('POST', '/public/login/teste', [
            "email" => $emailToRegister
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $res = $this->client->getResponse()->getContent();
        $user = json_decode($res, true);
        sleep(1);

        return ["HTTP_Authorization" => $user["data"]["token"]];
    }
}