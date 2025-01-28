<?php

namespace App\Security;

use App\Entity\User;
use App\Entity\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class CustomUserProvider implements UserProviderInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function loadUserByIdentifier($email): UserInterface
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            throw new \InvalidArgumentException(sprintf('User with email "%s" not found.', $email));
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        $userRepository = $this->entityManager->getRepository(User::class);
        $refreshedUser = $userRepository->findOneBy(['email' => $user->getUserIdentifier()]);

        if (!$refreshedUser) {
            throw new UserNotFoundException(sprintf('User with email "%s" not found.', $user->getUserIdentifier()));
        }

        return $refreshedUser;
    }


    public function supportsClass($class): bool
    {
        return $class === User::class;
    }

    public function getUsernameForAuthId($authUserId)
    {
        return $this->entityManager->getRepository(User::class)->find($authUserId)->getEmail();
    }
}
