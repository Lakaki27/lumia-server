<?php

namespace App\Repository;

use App\Entity\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

    /**
     * Find roles by their name(s).
     * 
     * @param string|array $names
     * @return Role[]
     */
    public function findByName($names)
    {
        // If a single name is passed, convert it to an array
        if (is_string($names)) {
            $names = [$names];
        }

        return $this->findBy(['name' => $names]);
    }

    /**
     * Find a role by its exact name.
     * 
     * @param string $name
     * @return Role|null
     */
    public function findOneByName($name)
    {
        return $this->findOneBy(['name' => $name]);
    }

    /**
     * Retrieve all roles in the database.
     * 
     * @return Role[]
     */
    public function findAllRoles()
    {
        return $this->findAll();
    }

    // /**
    //  * Find roles based on a custom query or criteria, if needed.
    //  * Example of using custom queries.
    //  * 
    //  * @param string $criteria
    //  * @return Role[]
    //  */
    // public function findRolesByCustomCriteria($criteria)
    // {
    //     // Example: find roles based on a more complex query
    //     return $this->createQueryBuilder('r')
    //         ->where('r.name LIKE :criteria')
    //         ->setParameter('criteria', '%' . $criteria . '%')
    //         ->getQuery()
    //         ->getResult();
    // }
}
