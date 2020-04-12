<?php

namespace App\Entity\Interfaces;

interface DonationInterface extends ModelInterface
{
    public function done(): void;

    public function cancel(): void;

    public function confirm(): void;

    public function getResumeToNeed(): array;

    public function getStatusUpdateToIndex(): array;

    public function getNeedId(): string;

    public function getResume(): array;

    public function addTalk(array $talk): void;
}