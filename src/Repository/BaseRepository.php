<?php

namespace App\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

/**
 * @package App\Repository
 */
class BaseRepository
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var ObjectRepository
     */
    protected $objectRepository;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $conn;

    public function save($entity)
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function getOneBy(array $params)
    {
        return $this->objectRepository->findOneBy($params);
    }

    public function getAllBy(array $data): ?array
    {
        $result = $this->objectRepository->findBy($data);

        return $result ?? [];
    }

    public function getAllByAndOrderedBy(array $data, $orderBy = ['created_at' => 'ASC']): ?array
    {
        $result = $this->objectRepository->findBy($data, $orderBy);

        return $result ?? [];
    }

    public function remove($entity)
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }
}