<?php

namespace App\Services\Entity\Interfaces;

interface NeedServiceInterface
{
    public function getNeedsListUnblockedByUser(string $user_id, $result_quantity = 3): array;

    public function thisNeedGoesBeyondTheOpensLimitOfOrFail(int $allowed_number, string $user_id);

    public function ifThisNeedListExistAndIsOpenMustFail(string $user_id, array $data);

    public function register(array $data): ?array;

    public function getNeedByIdOrFail(array $data): array;
}