<?php

namespace App\Services\Log;

use App\Services\Log\Interfaces\LoggerServiceInterface;
use Psr\Log\LoggerInterface;

/**
 * @package App\Services\Log
 */
final class LoggerService implements LoggerServiceInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function error(string $msg): void
    {
        if ($_ENV["APP_ENV"]!=="prod") {
            return;
        }

        $this->logger->error($msg);
    }
}