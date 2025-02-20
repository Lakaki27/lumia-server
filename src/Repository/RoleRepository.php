<?php

namespace App\Repository;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

    /**
     * Find roles based on a custom query or criteria, if needed.
     * Example of using custom queries.
     * 
     * @param string $criteria
     * @return Role
     */
    public function findRoleById(int $id): ?Role
    {
        // Example: find roles based on a more complex query
        return $this->createQueryBuilder('r')
            ->where('r.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get all Users linked to a given Role by role_id.
     *
     * @param int $roleId The ID of the role (EntityA)
     * @return User[] An array of User entities
     */
    public function findRoleUsers(int $roleId): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select('u')
            ->from(User::class, 'u')
            ->join('u.roles', 'r')
            ->where('r.id = :roleId')
            ->setParameter('roleId', $roleId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get all Users linked to a given Role by role_id.
     *
     * @param string $roleName The ID of the role (EntityA)
     * @return User[] An array of User entities
     */
    public function findByName(string $roleName): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select('u')
            ->from(User::class, 'u')
            ->join('u.roles', 'r')
            ->where('r.name = :roleName')
            ->setParameter('roleName', $roleName)
            ->getQuery()
            ->getResult();
    }
}
