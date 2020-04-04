<?php

namespace App\Entity\Traits;

/**
 * @package App\Entity\Traits
 */
trait ParamControl
{
    public function extractParamsFromRequest(array $attributesList,
                                             array $requestParams): array
    {
        $params = [];

        foreach($attributesList as $param)
        {
            if (empty($requestParams[$param])) {
                continue;
            }

            $params[$param] = $requestParams[$param];
        }

        return $params;
    }
}