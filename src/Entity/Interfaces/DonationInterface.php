<?php

namespace App\Entity\Interfaces;

interface DonationInterface extends ModelInterface
{
    public function done(): void;

    public function cancel(): void;
}