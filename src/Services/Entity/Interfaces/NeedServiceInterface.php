<?php

namespace App\Services\Entity\Interfaces;

interface NeedServiceInterface
{
    public function getNeedsListUnblockedByUser(string $user_id, $result_quantity = 3): array;

    public function thisNeedGoesBeyondTheOpensLimitOfOrFail(int $allowed_number, string $user_id);

    public function register(array $data): ?array;

    public function update(array $data, array $user): void;

    public function getNeedByIdOrFail(string $need_id): array;

    public function getOneByIdAndUserOrFail(string $need_id, string $user_id): array;

    public function remove(string $need_id, string $user_id): void;

    public function disableNeedById(string $need_id): void;

    public function setNeedDone(string $need_id, array $donation): void;

    public function loadNeedToDisable(string $need_id, $newData = []): array;

    public function getNeedsListByUser(string $user_id): array;

    public function updateAnyway(array $need);

    public function getOneByIdAndEnableOrFail(string $need_id): array;

    public function getAllNeedsNotCanceled(): array;

    public function removeDonationCanceled(string $need_id);

    public function getDonationId(): ?string;
}