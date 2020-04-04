<?php

namespace App\Repository\Interfaces;

use App\Entity\Expense;

/**
 * @package App\Repository\Interfaces
 */
interface ExpenseRepositoryInterface extends BaseRepositoryInterface
{
    public function getByID(string $id): ?Expense;

    public function listAllBy(array $params, $orderBy = "created_at", $order = "ASC"): array;

    public function getTheLastRecordByUser(string $user_id): ?Expense;

    public function setAllExpensesDeletedAtByToken(string $token_group, string $deleted_date);

    public function getOneByRegisteredAndID(string $user_id, string $uuid): ?Expense;

    public function getDataToPieChartByCategoryAndDueDate(array $params): array;

    public function getLast12MonthsStatisticsByCategoryAndDueDate(array $params): array;
}