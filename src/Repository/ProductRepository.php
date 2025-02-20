<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @return Product[] Returns an array of last added Product objects
     */
    public function findLastAdded(int $limit): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.created_at', 'DESC')
            ->setMaxResults($limit ?: 1)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Product[] Returns an array of Product objects
     */
    public function findByMatchingName(string $inputName): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.name LIKE :inputName')
            ->setParameter('inputName', "%$inputName%")
            ->orderBy("created_at", "DESC")
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function findOneById(int $id): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get all product logs for a given product.
     *
     * @param int $productId The ID of the product.
     * 
     * @return ProductLog[] Returns an array of ProductLog objects
     */
    public function findProductLogsByProductId(int $productId): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.productLogs', 'pl')
            ->where('p.id = :productId')
            ->setParameter('productId', $productId)
            ->orderBy('pl.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
