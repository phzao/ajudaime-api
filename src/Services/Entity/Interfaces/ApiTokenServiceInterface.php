<?php

namespace App\Services\Entity\Interfaces;

/**
 * @package App\Services\Entity\Interfaces
 */
interface ApiTokenServiceInterface
{
    public function registerAndGetApiTokenTo(string $user, array $data): array;

    public function getAValidApiTokenToUser(string $user_id): array;

    public function getTokenNotExpiredBy(string $token): array;

    public function invalidateToken(array $apiToken);
}