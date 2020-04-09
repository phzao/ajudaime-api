<?php

namespace App\Entity\Interfaces;

interface DonationInterface extends ModelInterface
{
    public function done(): void;

    public function cancel(): void;

    public function confirm(): void;

    public function getResumeToNeed(): array;

    public function getStatusUpdateToIndex(): array;
}