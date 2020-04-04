<?php

namespace App\Utils\Generators;

/**
 * @package App\Utils\Generators
 */
interface GenerateDataToSaveInterface
{
    public function getEmailNameAndPassword(): array;
}