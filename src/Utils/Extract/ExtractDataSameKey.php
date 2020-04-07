<?php

namespace App\Utils\Extract;

class ExtractDataSameKey implements ExtractDataSameKeyInterface
{
    public function getDataOnTheSameKeys(array $source, array $keySource): array
    {
        if (empty($source) || empty($keySource)) {
            return [];
        }

        $list = [];

        foreach ($keySource as $key)
        {
            if (empty($source[$key])) {
                continue;
            }

            $list[$key] = $source[$key];
        }

        return $list;
    }
}