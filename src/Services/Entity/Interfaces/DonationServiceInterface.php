<?php

namespace App\Services\Entity\Interfaces;

interface DonationServiceInterface
{
    public function getDonationsListProcessingByUser(string $user_id, $result_quantity = 3): array;

    public function thisDonationGoesBeyondTheProcessingLimitOfOrFail(int $allowed_number, string $user_id);

    public function ifExistADonationWithThisNeedMustFail(array $data, string $user_id);

    public function register(array $data): ?array;

    public function getDonationOnProcessingByIdOrFail(string $donation_id, array $fields): array;

    public function getDonationIdOrFail(string $donation_id): string;

    public function cancelDonation(string $user_id, string $donation_id): array;

    public function doneDonation(string $user_id, string $donation_id): array;

    public function getDonationsByUser(string $user_id): array;

    public function getDonationsByStatus(string $status): array;
}