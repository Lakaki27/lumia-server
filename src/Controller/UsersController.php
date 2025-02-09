<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\LoginRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Service\PasswordGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/users")]
final class UsersController extends AbstractController
{
    private UserRepository $userRepository;
    private RoleRepository $roleRepository;
    private LoginRepository $loginRepository;

    public function __construct(UserRepository $userRepository, LoginRepository $loginRepository, RoleRepository $roleRepository)
    {
        $this->userRepository = $userRepository;
        $this->loginRepository = $loginRepository;
        $this->roleRepository = $roleRepository;
    }

    #[Route("", name: 'users_all')]
    public function allUsers(): Response
    {
        $users = $this->userRepository->findAll();

        return $this->render('users/users.html.twig', [
            "users" => $users,
        ]);
    }

    #[Route("/add", name: 'users_add')]
    public function addUser(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        PasswordGeneratorService $pass
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $pass->generatePassword();
            $now = new \DateTimeImmutable();
            $user->setPassword($userPasswordHasher->hashPassword($user, $password));
            $user->setCreatedAt($now);
            $user->setUpdatedAt($now);
            $user->setIsFirstLogin(true);

            $rolesIds = $form->get('roles')->getData();
            $roles = $this->roleRepository->findBy(['id' => $rolesIds]);

            $user->setRoles($roles);

            $entityManager->persist($user);
            $entityManager->flush();

            $firstName = $user->getFirstName();
            $lastName = $user->getLastName();
            $emailAddress = $user->getEmail();

            $mail = (new TemplatedEmail())
                ->to(new Address($emailAddress))
                ->subject("Accès à l'application Lumia")
                ->htmlTemplate('emails/user_added.html.twig')
                ->context([
                    "name" => "$firstName $lastName",
                    "password" => $password
                ]);

            $mailer->send($mail);

            return $this->redirectToRoute('users_all');
        }

        return $this->render('users/add.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route("/me", name: 'users_me')]
    public function userMe(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute("users_all");
        }

        return $this->render('users/details.html.twig', [
            "user" => $user
        ]);
    }

    #[Route("/{id}", name: 'users_details')]
    public function usersDetails(int $id): Response
    {
        if (!is_int($id)) {
            return $this->redirectToRoute("users_all");
        }

        $user = $this->userRepository->findOneById($id);
        $logins = $user->getLogins();

        if (!$user) {
            return $this->redirectToRoute("users_all");
        }

        return $this->render('users/details.html.twig', [
            "user" => $user,
            "logins" => $logins
        ]);
    }
}
