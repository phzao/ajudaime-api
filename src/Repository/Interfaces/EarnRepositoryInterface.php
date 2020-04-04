<?php

namespace App\Repository\Interfaces;

use App\Entity\Earn;

interface EarnRepositoryInterface extends BaseRepositoryInterface
{
    public function getOneByUserAndID(string $user_id, string $id): ?Earn;

    public function getAllByUserOrderedBy(string $user_id, $orderBy = "ORDER BY e.created_at ASC"): ?array;
}