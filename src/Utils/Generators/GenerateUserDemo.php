<?php

namespace App\Utils\Generators;

class GenerateUserDemo implements GenerateDataToSaveInterface
{
    public function getEmailNameAndPassword(): array
    {
        $email = $this->random_str(10)."demo@kontatudo.com.br";
        $name = "Demo user ".$this->random_str(10)."";

        return [
            "email" => $email,
            "name" => $name,
            "password" => rand(100000, 999999)
        ];
    }

    private function random_str(int $length = 64,
                                string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'): string
    {
        if ($length < 1) {
            throw new \RangeException("Length must be a positive integer");
        }
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces []= $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }
}