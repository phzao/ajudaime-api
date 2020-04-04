<?php

namespace App\Repository\Interfaces;

use App\Entity\Place;

/**
 * @package App\Repository\Interfaces
 */
interface PlaceRepositoryInterface extends BaseRepositoryInterface
{
    public function getOneByID(string $id): ?Place;

    public function getOneDefaultOrNotByUser(string $user_id): ?Place;

    public function setAllPlacesAsNotDefault(string $user_id);
}