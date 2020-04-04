<?php

namespace App\Services\External\Google;

/**
 * @package App\Services\External\Google
 */
interface GoogleCheckServiceInterface
{
    public function requestHasNameEmailAndAccessTokenOrFail(array $fields);

    public function isValidGoogleAccessTokenOrFail(array $data);
}