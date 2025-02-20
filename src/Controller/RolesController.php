<?php

namespace App\Controller;

use App\Entity\Role;
use App\Form\RoleFormType;
use App\Repository\RoleRepository;
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

    #[Route("/delete/{id}", name: 'roles_delete')]
    public function roleDelete(int $id, EntityManagerInterface $em): Response
    {
        if (!is_int($id)) {
            return $this->redirectToRoute("roles_all");
        }

        $role = $this->roleRepository->findRoleById($id);

        if (in_array($role->getName(), ["ROLE_USER", "ROLE_MANAGER", "ROLE_DEV"])) {
            return $this->json(["error" => true, "message" => "Ce rôle ne peut pas être supprimé !"]);
        }

        if (!$role) {
            return $this->redirectToRoute("roles_all");
        }

        $em->remove($role);
        $em->flush();

        return $this->json(["success" => true, "message" => "Rôle supprimé !"]);
    }

    #[Route("/{id}", name: 'roles_details')]
    public function roleDetails(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        if (!is_int($id)) {
            return $this->redirectToRoute("roles_all");
        }

        $role = $this->roleRepository->findRoleById($id);

        if (!$role) {
            return $this->redirectToRoute("roles_all");
        }

        $users = $this->roleRepository->findRoleUsers($id);

        $form = $this->createForm(RoleFormType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->persist($role);
                $entityManager->flush();
            } else {
                foreach ($form->getErrors(true) as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
            return $this->redirectToRoute('roles_details', ['id' => $role->getId()]);
        }

        return $this->render('roles/details.html.twig', [
            "role" => $role,
            "users" => $users,
            "roleForm" => $form
        ]);
    }
}
