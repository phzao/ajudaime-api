<?php

namespace App\Services\Entity\Interfaces;

/**
 * @package App\Services\Entity\Interfaces
 */
interface UserServiceInterface
{
    public function getUserByEmailAnyway(array $data): ?array;

    public function getUserById(string $id): array;
}