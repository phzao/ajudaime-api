<?php

namespace App\Utils\Generators;

class Bin2HexGenerate implements TokenGeneratorInterface
{
    public function generate(int $length = 125): string
    {
        return bin2hex(openssl_random_pseudo_bytes($length));
    }
}