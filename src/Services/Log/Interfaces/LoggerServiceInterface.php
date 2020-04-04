<?php

namespace App\Services\Log\Interfaces;

/**
 * @package App\Services\Log\Interfaces
 */
interface LoggerServiceInterface
{
    public function error(string $msg): void;
}