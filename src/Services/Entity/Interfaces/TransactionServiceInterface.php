<?php

namespace App\Services\Entity\Interfaces;

interface TransactionServiceInterface
{
    public function getTransactionsListProcessingByUser(string $user_id, $result_quantity = 3): array;

    public function thisTransactionGoesBeyondTheProcessingLimitOfOrFail(int $allowed_number, string $user_id);

    public function ifExistATransactionWithThisNeedMustFail(array $data, string $user_id);

    public function register(array $data): ?array;

    public function getTransactionOnProcessingByIdOrFail(string $transaction_id, array $fields): array;

    public function getTransactionIdOrFail(string $transaction_id): string;
}