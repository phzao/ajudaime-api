<?php

namespace App\Utils\Generators;

/**
 * @package App\Utils\Generators
 */
interface TokenGeneratorInterface
{
    public function generate(int $length): string;
}