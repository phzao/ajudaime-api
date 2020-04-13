<?php

namespace App\Services\Entity\Interfaces;

use App\Entity\Interfaces\DonationInterface;

interface DonationServiceInterface
{
    public function getDonationsListProcessingByUser(string $user_id, $result_quantity = 3): array;

    public function thisDonationGoesBeyondTheProcessingLimitOfOrFail(int $allowed_number, string $user_id);

    public function ifExistADonationWithThisNeedMustFail(string $need_id, string $user_id);

    public function register(array $data): ?array;

    public function getDonationOnProcessingByIdOrFail(string $donation_id, array $fields): array;

    public function getDonationIdOrFail(string $donation_id): string;

    public function cancelDonation(string $user_id, $donation_id): string;

    public function cancelDonationById($donation_id): array;

    public function doneDonation(string $user_id, string $donation_id): string;

    public function needConfirmation(string $user_id, string $donation_id): string;

    public function getDonationsByUser(string $user_id): array;

    public function getDonationByIdAndUserOrFail(string $donation_id, string $user_id): array;

    public function getDonationByIdOrFail(string $donation_id): array;

    public function getDonationsByStatus(string $status): array;

    public function getDonationStatusIdCreated(): array;

    public function getOneByIdUserIdProcessingOrFail(string $user_id, string $donation_id): array;

    public function getDonationsToTalk(string $User_id): array;

    public function getDonationEntityByIdOrFail(string $donation_id): ?DonationInterface;

    public function update(array $donation): void;

    public function updateTalk(array $talk): void;
}