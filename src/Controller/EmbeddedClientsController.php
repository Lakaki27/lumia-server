<?php

namespace App\Controller;

use App\Entity\EmbeddedClient;
use App\Form\EmbeddedClientFormType;
use App\Repository\EmbeddedClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/embedded-clients')]
final class EmbeddedClientsController extends AbstractController
{
    private EmbeddedClientRepository $embeddedClientsRepository;

    public function __construct(EmbeddedClientRepository $embeddedClientsRepository)
    {
        $this->embeddedClientsRepository = $embeddedClientsRepository;
    }

    #[Route("", name: 'embedded_clients_all')]
    public function allEmbeddedClients(): Response
    {
        $clients = $this->embeddedClientsRepository->findAll();

        return $this->render('embedded_clients/embedded_clients.html.twig', [
            "clients" => $clients,
        ]);
    }

    #[Route("/add", name: 'embedded_clients_add')]
    public function addEmbeddedClient(Request $request, EntityManagerInterface $entityManager): Response
    {
        $client = new EmbeddedClient();
        $form = $this->createForm(EmbeddedClientFormType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($client);
            $entityManager->flush();

            return $this->redirectToRoute('embedded_clients_all');
        }

        return $this->render('embedded_clients/add.html.twig', [
            'embeddedClientForm' => $form,
        ]);
    }

    #[Route("/delete/{id}", name: 'embedded_clients_delete')]
    public function roleDelete(int $id, EntityManagerInterface $em): Response
    {
        if (!is_int($id)) {
            return $this->redirectToRoute("embedded_clients_all");
        }

        $client = $this->embeddedClientsRepository->findOneById($id);

        if (!$client) {
            return $this->redirectToRoute("embedded_clients_all");
        }

        $em->remove($client);
        $em->flush();

        return $this->json(["success" => true, "message" => "Client supprimé !"]);
    }

    #[Route("/{id}", name: 'embedded_clients_details')]
    public function embeddedClientDetails(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        if (!is_int($id)) {
            return $this->redirectToRoute("embedded_clients_all");
        }

        $client = $this->embeddedClientsRepository->findById($id);

        if (!$client) {
            return $this->redirectToRoute("embedded_clients_all");
        }

        $form = $this->createForm(EmbeddedClientFormType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->persist($client);
                $entityManager->flush();
            } else {
                foreach ($form->getErrors(true) as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
            return $this->redirectToRoute('embedded_clients_details', ['id' => $client->getId()]);
        }

        return $this->render('embedded_clients/details.html.twig', [
            "client" => $client,
            "embeddedClientForm" => $form
        ]);
    }
}
