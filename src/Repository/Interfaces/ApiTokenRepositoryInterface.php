<?php

namespace App\Repository\Interfaces;

use App\Entity\ApiToken;

/**
 * @package App\Repository\Interfaces
 */
interface ApiTokenRepositoryInterface extends BaseRepositoryInterface
{
    public function getTheLastTokenNotExpiredByUser(string $user_id): ?ApiToken;

    public function getOneByTokenAndNotExpired(string $token): ?ApiToken;
}