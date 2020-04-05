<?php

namespace App\Services\Entity\Interfaces;

interface NeedServiceInterface
{
    public function getNeedsListUnblockedByUser(string $user_id, $result_quantity = 3): array;

    public function thisNeedGoesBeyondTheOpensLimitOfOrFail(int $allowed_number, string $user_id);

    public function ifThisNeedListExistAndIsOpenMustFail(string $user_id, array $data);

    public function register(array $data): ?array;

    public function update(array $data, array $user): void;

    public function getNeedByIdOrFail(array $data): array;

    public function remove(string $need_id, string $user_id): void;

    public function disableNeedById(string $need_id): void;

    public function getNeedsListByUser(string $user_id): array;
}