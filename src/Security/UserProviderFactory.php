<?php

namespace App\Security;

use App\Security\CustomUserProvider;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Contracts\Service\ServiceProviderInterface;

class UserProviderFactory implements ServiceProviderInterface
{
    public function create(): UserProviderInterface
    {
        return new CustomUserProvider($this->getEntityManager());
    }

    private function getEntityManager(): \Doctrine\ORM\EntityManagerInterface
    {
        return $this->container->get('doctrine')->getManager();
    }

    public function get($id): mixed
    {
        // If the $id is for a user provider, return an instance of CustomUserProvider
        if ($id === 'app.user_provider') {
            return new CustomUserProvider($this->getEntityManager());
        }

        // You can also throw an exception if the service isn't found
        throw new \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException($id);
    }


    public function has($id): bool
    {
        // Check if the service with the given ID exists
        return $id === 'app.user_provider';
    }


    public function getProvidedServices(): array
    {
        // Return a list of services that this provider handles
        return [
            'app.user_provider' => CustomUserProvider::class,
        ];
    }
}
