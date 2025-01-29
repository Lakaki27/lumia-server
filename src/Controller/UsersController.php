<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/users")]
final class UsersController extends AbstractController
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    #[Route("", name: 'users_all')]
    public function allUsers(): Response
    {
        //get all users

        return $this->render('users/users.html.twig', [
            'TODO' => 'Display all users',
        ]);
    }

    #[Route("/{id}", name: 'users_details')]
    public function usersDetails(int $id): Response
    {
        //get user from id

        return $this->render('users/details.html.twig', [
            'TODO' => "Display user for id $id",
        ]);
    }

    #[Route("/add", name: 'user_add')]
    public function addUser(): Response
    {
        //add user (ADD FORM TOO)

        return $this->render('users/add.html.twig', [
            'TODO' => "Display add user page",
        ]);
    }
}
