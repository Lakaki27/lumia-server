<?php

namespace App\Controller;

use App\Repository\EmbeddedClientRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/embedded_clients")]
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

    #[Route("/{id}", name: 'embedded_client_details')]
    public function embeddedClientDetails(int $id): Response
    {
        $client = $this->embeddedClientsRepository->findOneById($id);

        return $this->render('embedded_clients/details.html.twig', [
            "client" => $client,
        ]);
    }

    #[Route("/add", name: 'embedded_client_add')]
    public function addEmbeddedClient(): Response
    {
        //add embedded client (ADD FORM TOO)

        return $this->render('embedded_clients/add.html.twig');
    }
}
