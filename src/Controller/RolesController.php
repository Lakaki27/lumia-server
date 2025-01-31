<?php

namespace App\Controller;

use App\Entity\Role;
use App\Form\RoleFormType;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('roles')]
final class RolesController extends AbstractController
{
    private RoleRepository $roleRepository;

    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    #[Route('', name: 'roles_all')]
    public function allRoles(): Response
    {
        $roles = $this->roleRepository->findAll();

        return $this->render('roles/roles.html.twig', [
            "roles" => $roles,
        ]);
    }

    #[Route("/add", name: 'roles_add')]
    public function addRole(Request $request, EntityManagerInterface $entityManager): Response
    {
        $role = new Role();
        $form = $this->createForm(RoleFormType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($role);
            $entityManager->flush();

            return $this->redirectToRoute('roles_all');
        }

        return $this->render('roles/add.html.twig', [
            'roleForm' => $form,
        ]);
    }

    #[Route("/{id}", name: 'roles_details')]
    public function roleDetails(int $id): Response
    {
        if (!is_int($id)) {
            return $this->redirectToRoute("roles_details");
        }

        $role = $this->roleRepository->findRoleById($id);

        if (!$role) {
            return $this->redirectToRoute("roles_all");
        }

        $users = $this->roleRepository->findRoleUsers($id);

        return $this->render('roles/details.html.twig', [
            "role" => $role,
            "users" => $users
        ]);
    }
}
