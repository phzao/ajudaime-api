<?php

namespace App\Entity\Traits;

use App\Entity\User;

/**
 * @package App\Entity\Traits
 */
trait ReadUserData
{
    public function getNameAndIdUser($user): array
    {
        if (!$user instanceof User) {
            return [];
        }

        return $user->getNameAndId();
    }

    public function getIdUser($user): ?string
    {
        if (!$user instanceof User) {
            return '';
        }

        return $user->getId();
    }
}