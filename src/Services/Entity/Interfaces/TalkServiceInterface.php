<?php

namespace App\Services\Entity\Interfaces;

interface TalkServiceInterface
{
    public function thisTalkGoesBeyondTheUnreadLimitOfOrFail(int $allowed_number,
                                                             string $user_id,
                                                             string $transaction_id);

    public function register(array $data): ?array;
}