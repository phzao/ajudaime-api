<?php

namespace App\Repository\Interfaces;

use App\Entity\Category;

/**
 * @package App\Repository\Interfaces
 */
interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    public function getOneByID(string $id): ?Category;

    public function getOneByUserAndID(string $user_id, string $id): ?Category;

    public function listAllBy(array $params, $orderBy = "created_at", $order = "ASC"): array;

    public function getAllNotDeletedByUser(string $user_id): ?array;

    public function getOneDefaultOrNotByUser(string $user_id): ?Category;

    public function setAllCategoriesAsNonDefault(string $user_id);

    public function getOneByDescription(string $user_id, string $description): ?Category;
}