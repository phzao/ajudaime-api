<?php

namespace App\Entity\Interfaces;

/**
 * @package App\Entity\Interfaces
 */
interface ModelInterface
{
    public function setAttributes(array $values): void;

    public function setAttribute(string $key, $value): void;

    public function getFullData(): array;

    public function getOriginalData(): array;

    public function getId(): ?string;

    public function updated(): void;

    public function remove(): void;

    public function getDeletedAt(): ?\DateTime;

    public function getElasticSearchMapping(): array;

    public function getDataToInsert(): array;

    public function getElasticIndexName(): array;

    public function getFullDataToUpdateIndex(): array;

    public function getIndexName(): string;
}