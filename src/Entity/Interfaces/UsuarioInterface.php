<?php

namespace App\Entity\Interfaces;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @package App\Entity\Interfaces
 */
interface UsuarioInterface extends UserInterface
{
    public function getStatus(): string;

    public function setDisable();

    public function delete(): void;

    public function getName(): string;

    public function getNameAndId(): array;

    public function canAuthenticate(): bool;

    public function getDataResume(): array;

    public function getFieldsAllowedUpdate(): array;
}