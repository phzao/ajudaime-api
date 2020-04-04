<?php

namespace App\Repository\Interfaces;

use App\Entity\User;

/**
 * @package App\Repository\Interfaces
 */
interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function getOneByID(string $id);

    public function getOneUserByEmailAndStatusEnable(string $email): ?User;

    public function getOneUserByEmail(string $email): ?User;

    public function getListOfUsersByStatusEnableAndASC(): array;
}