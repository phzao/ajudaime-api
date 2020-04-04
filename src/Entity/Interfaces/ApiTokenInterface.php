<?php

namespace App\Entity\Interfaces;

use App\Utils\Generators\TokenGeneratorInterface;

/**
 * @package App\Entity\Interfaces
 */
interface ApiTokenInterface extends ModelInterface
{
    public function setUser($user);

    public function invalidateToken(): void;

    public function getDetailsToken(): array;

    public function getId(): ?string;

    public function generateToken(TokenGeneratorInterface $tokenGenerator): void;
}