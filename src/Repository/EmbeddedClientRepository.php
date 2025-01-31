<?php

namespace App\Repository;

use App\Entity\EmbeddedClient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmbeddedClient>
 */
class EmbeddedClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmbeddedClient::class);
    }

    /**
     * @return EmbeddedClient[]
     */
    public function getAll(): array
    {
        return $this->findAll();
    }

    /**
     * @return EmbeddedClient|null
     */
    public function findById(int $id): ?EmbeddedClient
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return EmbeddedClient[]
     */
    public function findByMatchingSerial(string $serial): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.serial LIKE %:serial%')
            ->setParameter('serial', $serial)
            ->getQuery()
            ->setMaxResults(5)
            ->getResult();
    }
}
