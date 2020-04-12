<?php

namespace App\Services\Entity\Interfaces;

use App\Entity\Interfaces\TalkInterface;

interface TalkServiceInterface
{
    public function thisTalkGoesBeyondTheUnreadLimitOfOrFail(int $allowed_number, string $user_id, string $transaction_id);

    public function register(array $data): ?array;

    public function getTalkEntity():?TalkInterface;
}