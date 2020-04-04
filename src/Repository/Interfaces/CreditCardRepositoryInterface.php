<?php

namespace App\Repository\Interfaces;

use App\Entity\CreditCard;

interface CreditCardRepositoryInterface extends BaseRepositoryInterface
{
    public function getOneByUserAndID(string $user_id, string $id): ?CreditCard;

    public function getAllNotDeletedAndOrderedBy(array $parameters): ?array;

    public function setAllCreditCardsAsNonDefault(string $user_id);

    public function getOneById(string $credit_card_id): ?CreditCard;

    public function getOneDefaultOrNotByUser(string $user_id): ?CreditCard;
}