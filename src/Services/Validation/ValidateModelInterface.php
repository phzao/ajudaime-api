<?php

namespace App\Services\Validation;

/**
 * @package App\Services\Validation
 */
interface ValidateModelInterface
{
    public function entityIsValidOrFail($model);
}