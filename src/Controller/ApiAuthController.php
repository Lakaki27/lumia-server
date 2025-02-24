<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductLog;
use App\Entity\User;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Routes\ApiRoutes;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route("api/v1/auth")]
class ApiAuthController extends AbstractController
{
    private $passwordEncoder;
    private $userRepo;
    private $productRepo;
    private $JWTManager;

    public function __construct(
        UserPasswordHasherInterface $passwordEncoder,
        JWTTokenManagerInterface $JWTManager,
        UserRepository $userRepo,
        ProductRepository $productRepo
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->userRepo = $userRepo;
        $this->productRepo = $productRepo;
        $this->JWTManager = $JWTManager;
    }

    #[Route("/login", name: 'api_login', methods: ['POST'])]
    public function login(Request $request)
    {
        $email = $request->get('email');
        $password = $request->get('password');

        $user = $this->userRepo->findOneByEmail($email);

        if (!$user || !$this->passwordEncoder->isPasswordValid($user, $password)) {
            return new JsonResponse(['message' => 'Invalid credentials'], JsonResponse::HTTP_UNAUTHORIZED);
        }
        // Generate the JWT token for the authenticated user
        $token = $this->JWTManager->create($user);

        return new JsonResponse(['token' => $token]);
    }
}
