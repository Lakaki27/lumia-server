<?php

namespace App\Security;

use App\Entity\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Contracts\Service\ServiceProviderInterface;

class CustomRoleHierarchy extends RoleHierarchy implements RoleHierarchyInterface
{
    private $roleRepository;
    private $entityManager;
    private $hierarchy;

    public function __construct(RoleHierarchy $roleHierarchy, EntityManagerInterface $entityManager)
    {
        parent::__construct($roleHierarchy);
        $this->hierarchy = $roleHierarchy;
        $this->entityManager = $entityManager;
        $this->roleRepository = $entityManager->getRepository(Role::class);
    }

    protected function buildRoleMap(): void
    {
        $this->map = [];
        $this->roleRepository = $this->entityManager->getRepository(Role::class);

        foreach ($this->hierarchy as $main => $roles) {
            $this->map[$main] = $roles;
            $visited = [];
            $additionalRoles = $roles;
            while ($role = array_shift($additionalRoles)) {
                if (!isset($this->hierarchy[$role])) {
                    continue;
                }

                $visited[] = $role;

                foreach ($this->roleRepository->findBy(['name' => $role]) as $roleEntity) {
                    $this->map[$main][] = $roleEntity->getName();
                }

                foreach (array_diff($this->hierarchy[$role], $visited) as $additionalRole) {
                    $additionalRoles[] = $additionalRole;
                }
            }

            $this->map[$main] = array_unique($this->map[$main]);
        }
    }
}
