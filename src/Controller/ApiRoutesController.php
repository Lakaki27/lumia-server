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

#[Route("/api/v1")]
class ApiRoutesController extends AbstractController
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

    #[Route('/verify-token', name: 'api_verify_token', methods: ['POST'])]
    public function verifyTokenValidity(Request $request)
    {
        return new JsonResponse(['isValid' => true], JsonResponse::HTTP_OK);
    }

    //TODO: tester ça
    #[Route('/products/sell', name: 'api_products_sell', methods: ['POST'])]
    public function declareSoldProducts(Request $request, EntityManagerInterface $entityManager)
    {
        $products = $request->get("products");

        foreach ($products as $productData) {
            $product = $this->productRepo->findOneBy(['barcode' => $productData['barcode']]);

            if ($product) {
                $productLog = new ProductLog();

                $productLog->setAmount($productData['amount']);

                $productLog->setProduct($product);

                $productLog->setIsSold(1);

                $entityManager->persist($productLog);
            } else {
                return new JsonResponse(['message' => 'Code-barre non répertorié !'], JsonResponse::HTTP_NOT_FOUND);
                // break;
            }
        }

        $entityManager->flush();

        //TODO: vérifier si je renvoie le contenu du ticket côté serveur ou le génère côté client
        return new JsonResponse(["success" => true]);
    }

    #[Route('/products/acquire', name: 'api_products_acquire', methods: ['POST'])]
    public function declareAcquiredProducts(Request $request, EntityManagerInterface $entityManager)
    {
        $products = $request->get("products");

        foreach ($products as $productData) {
            $product = $this->productRepo->findOneBy(['barcode' => $productData['barcode']]);

            if ($product) {
                $productLog = new ProductLog();

                $productLog->setAmount($productData['amount']);

                $productLog->setProduct($product);

                $productLog->setIsSold(0);

                $entityManager->persist($productLog);
            } else {
                return new JsonResponse(['message' => 'Code-barre non répertorié !'], JsonResponse::HTTP_NOT_FOUND);
                // break;
            }
        }

        $entityManager->flush();

        //TODO: vérifier si je renvoie le contenu du ticket côté serveur ou le génère côté client
        return new JsonResponse(["success" => true]);
    }

    #[Route("/products/{barcode}", name: 'api_products_id', methods: ['GET'])]
    public function getProduct(Request $request, int $barcode)
    {
        if (!is_int($barcode)) {
            return $this->json(["error" => true, "message" => "Code-barre invalide !"], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (strlen($barcode) === 13) {
            $barcode = substr($barcode, 7, 5);
        }

        $product = $this->productRepo->findBy(["barcode" => $barcode]);

        if (!$product) {
            return new JsonResponse(['message' => 'Code-barre non répertorié !'], JsonResponse::HTTP_NOT_FOUND);
        }

        dd($product);

        return new JsonResponse(['product' => $product]);
    }
}
