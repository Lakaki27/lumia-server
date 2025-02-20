<?php

namespace App\Controller;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, EntityManagerInterface $em, UserRepository $userRepo, RoleRepository $roleRepo, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('index');
        }

        $roleDev = $roleRepo->findOneBy(["name" => "ROLE_DEV"]);
        $roleManager = $roleRepo->findOneBy(["name" => "ROLE_MANAGER"]);

        if (!$roleDev) {
            $roleDev = new Role;
            $roleDev->setSlug("Développeur");
            $roleDev->setName("ROLE_DEV");

            $em->persist($roleDev);
            $em->flush();
        }

        if (!$roleManager) {
            $roleManager = new Role;
            $roleManager->setSlug("Manager");
            $roleManager->setName("ROLE_MANAGER");

            $em->persist($roleManager);
            $em->flush();
        }

        if (!$userRepo->findAll()) {
            $user = new User;
            $now = new \DateTimeImmutable();
            $user->setPassword($userPasswordHasher->hashPassword($user, "lumia"));
            $user->setFirstName("Lumia Admin");
            $user->setLastName("Lumia Admin");
            $user->setEmail("lumia@lumia.com");
            $user->setCreatedAt($now);
            $user->setUpdatedAt($now);
            $user->setIsFirstLogin(true);
            $user->setRoles([$roleDev]);

            $em->persist($user);
            $em->flush();
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        // throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
