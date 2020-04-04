<?php

namespace App\Tests;

/**
 * @package App\Tests
 */
trait Authenticate
{
    protected $email = "me@me.com";
    protected $password = "123456";
    protected $registeredData = [];

    public function getTokenAuthenticate($email = null, $password = null)
    {
        $emailToRegister = empty($email)?$this->email: $email;
        $passwordToRegister = empty($password)?$this->password: $password;

        $this->client->request('POST', '/register', [
            "email" => $emailToRegister,
            "password" => $passwordToRegister
        ]);

        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());

        $res = $this->client->getResponse()->getContent();
        $user = json_decode($res, true);

        $this->registeredData = $user["data"];

        $this->client->request('POST', '/authenticate', [
            "email" => $emailToRegister,
            "password" => $passwordToRegister
        ]);

        $res    = $this->client->getResponse()->getContent();
        $result = json_decode($res, true);
        
        return $result["data"];
    }
}