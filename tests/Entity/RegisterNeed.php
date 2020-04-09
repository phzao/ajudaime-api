<?php

namespace App\Tests\Entity;


trait RegisterNeed
{
    protected $email = "me@me.com";

    public function getNewNeed(array $token, $needsList = "Package of food", $message="I needy"): array
    {
        $data["needsList"] = $needsList;
        $data["message"] = $message;

        $this->client->request('POST', self::NEED_ROUTE, $data, [], $token);
        $this->assertResponseStatusCodeSame(201);
        $res = json_decode($this->client->getResponse()->getContent(), true);

        $needData = $res["data"];

        return $needData;
    }

    public function getNewUserAndNeed(string $email, string $needList, string $message): array
    {
        $token = $this->getTokenAuthenticate($email);

        return [
            "token" => $token,
            "need"  => $this->getNewNeed($token, $needList, $message)
        ];
    }
}