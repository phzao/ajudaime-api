<?php

namespace App\Utils\Extract;

interface ExtractDataSameKeyInterface
{
    public function getDataOnTheSameKeys(array $source, array $keySource): array;
}