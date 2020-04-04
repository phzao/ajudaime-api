<?php

namespace App\Tests\Entity;

/**
 * @package App\Tests\Entity
 */
trait RegisterExpense
{
    public function registerExpense(string $route, array $params, string $token): array
    {
        $this->client->request('POST', $route, $params, [], ["HTTP_Authorization" => $token]);
        $this->assertResponseStatusCodeSame(201);
        $res = json_decode($this->client->getResponse()->getContent(), true);
        $expense = $res["data"];

        return $expense;
    }
}