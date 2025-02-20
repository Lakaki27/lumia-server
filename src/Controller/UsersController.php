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
        $users = $this->userRepository->findBy([], ['created_at' => 'DESC']);

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

            if ($rolesIds) {
                $roles = $this->roleRepository->findBy(['id' => $rolesIds]);
                $user->setRoles($roles);
            }
            
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

            $this->addFlash('userAddedMessage', 'Nouvel utilisateur ajouté !');
            $this->addFlash('userAddedIcon', 'success');

            return $this->redirectToRoute('users_all');
        }

        return $this->render('users/add.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route("/me", name: 'users_me')]
    public function userMe(Request $request, EntityManagerInterface $entityManager): Response
    {
        $userIdentifier = $this->getUser()->getUserIdentifier();

        $user = $this->userRepository->findOneByEmail($userIdentifier);
        $logins = $user->getLogins();
        $logins = $logins->slice(-10);
        $logins = array_reverse($logins);

        if (!$user) {
            return $this->redirectToRoute("users_all");
        }

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->persist($user);
                $entityManager->flush();
            } else {
                foreach ($form->getErrors(true) as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
            return $this->redirectToRoute('users_details', ['id' => $user->getId()]);
        }

        return $this->render('users/details.html.twig', [
            "user" => $user,
            "logins" => $logins,
            "userForm" => $form
        ]);
    }

    #[Route("/delete/{id}", name: 'users_delete')]
    public function userDelete(int $id, EntityManagerInterface $em): Response
    {
        if (!is_int($id)) {
            return $this->redirectToRoute("users_all");
        }

        $user = $this->userRepository->findOneById($id);

        if (!$user) {
            return $this->redirectToRoute("users_all");
        }

        $em->remove($user);
        $em->flush();

        return $this->json(["success" => true, "message" => "Utilisateur supprimé !"]);
    }

    #[Route("/{id}", name: 'users_details')]
    public function usersDetails(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        if (!is_int($id)) {
            return $this->redirectToRoute("users_all");
        }

        $user = $this->userRepository->findOneById($id);

        if (!$user) {
            return $this->redirectToRoute("users_all");
        }

        if ($user->getEmail() === $this->getUser()->getUserIdentifier()) {
            return $this->redirectToRoute("users_me");
        }

        $logins = $user->getLogins();
        $logins = $logins->slice(-10);
        $logins = array_reverse($logins);

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->persist($user);
                $entityManager->flush();
            } else {
                foreach ($form->getErrors(true) as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
            return $this->redirectToRoute('users_details', ['id' => $user->getId()]);
        }

        return $this->render('users/details.html.twig', [
            "user" => $user,
            "logins" => $logins,
            "userForm" => $form
        ]);
    }
}
