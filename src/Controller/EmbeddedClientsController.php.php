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
    public function allEmbeddedClient(): Response
    {
        //get all embedded clients

        return $this->render('embedded_clients/embedded_clients.html.twig', [
            'TODO' => 'Display all embedded clients',
        ]);
    }

    #[Route("/{id}", name: 'embedded_client_details')]
    public function usersEmbeddedClient(int $id): Response
    {
        //get embedded client from id

        return $this->render('embedded_clients/details.html.twig', [
            'TODO' => "Display embedded clients for id $id",
        ]);
    }

    #[Route("/add", name: 'embedded_client_add')]
    public function addEmbeddedClient(): Response
    {
        //add embedded client (ADD FORM TOO)

        return $this->render('embedded_clients/add.html.twig', [
            'TODO' => "Display add embedded client page",
        ]);
    }
}
