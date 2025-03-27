<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductLog;
use App\Entity\User;
use App\Repository\EmbeddedClientRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Routes\ApiRoutes;
use App\Service\AlertMailer;
use App\Service\BarcodeService;
use DateTimeImmutable;
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
    private $barcodeService;
    private $embeddedClientRepository;

    public function __construct(
        UserPasswordHasherInterface $passwordEncoder,
        JWTTokenManagerInterface $JWTManager,
        UserRepository $userRepo,
        ProductRepository $productRepo,
        BarcodeService $barcodeService,
        EmbeddedClientRepository $embeddedClientRepository
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->userRepo = $userRepo;
        $this->productRepo = $productRepo;
        $this->JWTManager = $JWTManager;
        $this->barcodeService = $barcodeService;
        $this->embeddedClientRepository = $embeddedClientRepository;
    }

    #[Route('/verify-token', name: 'api_verify_token', methods: ['POST'])]
    public function verifyTokenValidity(Request $request)
    {
        $serial = $request->headers->get('Client-Serial');

        if (!$serial) {
            return new JsonResponse(['isValid' => false], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $serialExists = $this->embeddedClientRepository->findOneBy(["serial" => $serial]);

        if (!$serialExists) {
            return new JsonResponse(['isValid' => false], JsonResponse::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse(['isValid' => true], JsonResponse::HTTP_OK);
    }

    //TODO: tester ça
    #[Route('/products/sell', name: 'api_products_sell', methods: ['POST'])]
    public function declareSoldProducts(Request $request, EntityManagerInterface $entityManager, AlertMailer $alertMailer)
    {
        $serial = $request->headers->get('Client-Serial');

        if (!$serial) {
            return new JsonResponse(['message' => 'Erreur de connexion au serveur: appareil non autorisé !'], JsonResponse::HTTP_NOT_FOUND);
        }

        $serialExists = $this->embeddedClientRepository->findOneBy(["serial" => $serial]);

        if (!$serialExists) {
            return new JsonResponse(['message' => 'Erreur de connexion au serveur: appareil non autorisé !'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $logs = [];
        $productsToUpdate = [];

        if (!isset($data["products"])) {
            return new JsonResponse(['message' => 'La requête est incorrectement formatée.'], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $products = $data["products"];

        foreach ($products as $productData) {
            $barcode = $this->barcodeService->extractProductDigits($productData["barcode"]);

            if (!$barcode) {
                return new JsonResponse(['message' => 'Code-barre invalide !'], JsonResponse::HTTP_NOT_FOUND);
            }

            $product = $this->productRepo->findOneBy(['barcode' => $barcode]);

            if ($product) {
                $productLog = new ProductLog();

                $productLog->setAmount($productData['amount']);

                $productLog->setProduct($product);

                $productLog->setCreatedAt(new \DateTimeImmutable());

                $productLog->setIsSold(1);

                $logs[] = $productLog;

                $product->setAmount($product->getAmount() - $productData["amount"]);

                $productsToUpdate[] = $product;
            } else {
                return new JsonResponse(['message' => 'Code-barre non répertorié !'], JsonResponse::HTTP_NOT_FOUND);
                break;
            }
        }

        foreach ($logs as $log) {
            $entityManager->persist($log);
        }

        foreach ($productsToUpdate as $p) {
            if ($p->getAmount() <= 10) {
                $alertMailer->alertProductStockIsTooLow($p);
            }

            $entityManager->persist($p);
        }

        $entityManager->flush();

        //TODO: vérifier si je renvoie le contenu du ticket côté serveur ou le génère côté client
        return new JsonResponse(["success" => true]);
    }

    #[Route('/products/acquire', name: 'api_products_acquire', methods: ['POST'])]
    public function declareAcquiredProducts(Request $request, EntityManagerInterface $entityManager)
    {
        $serial = $request->headers->get('Client-Serial');

        if (!$serial) {
            return new JsonResponse(['message' => 'Erreur de connexion au serveur: appareil non autorisé !'], JsonResponse::HTTP_NOT_FOUND);
        }

        $serialExists = $this->embeddedClientRepository->findOneBy(["serial" => $serial]);

        if (!$serialExists) {
            return new JsonResponse(['message' => 'Erreur de connexion au serveur: appareil non autorisé !'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $logs = [];
        $productsToUpdate = [];

        if (!isset($data["products"])) {
            return new JsonResponse(['message' => 'La requête est incorrectement formatée.'], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $products = $data["products"];

        foreach ($products as $productData) {
            $barcode = $this->barcodeService->extractProductDigits($productData["barcode"]);

            if (!$barcode) {
                return new JsonResponse(['message' => 'Code-barre invalide !'], JsonResponse::HTTP_NOT_FOUND);
            }

            $product = $this->productRepo->findOneBy(['barcode' => $barcode]);

            if ($product) {
                $productLog = new ProductLog();

                $productLog->setAmount($productData['amount']);

                $productLog->setProduct($product);

                $productLog->setCreatedAt(new \DateTimeImmutable());

                $productLog->setIsSold(0);

                $logs[] = $productLog;

                $product->setAmount($product->getAmount() + $productData["amount"]);

                $productsToUpdate[] = $product;
            } else {
                return new JsonResponse(['message' => 'Code-barre non répertorié !'], JsonResponse::HTTP_NOT_FOUND);
                break;
            }
        }

        foreach ($logs as $log) {
            $entityManager->persist($log);
        }

        foreach ($productsToUpdate as $p) {
            $entityManager->persist($p);
        }

        $entityManager->flush();

        //TODO: vérifier si je renvoie le contenu du ticket côté serveur ou le génère côté client
        return new JsonResponse(["success" => true]);
    }

    #[Route("/products/{barcode}", name: 'api_products_id', methods: ['GET'])]
    public function getProduct(Request $request, int $barcode)
    {
        $serial = $request->headers->get('Client-Serial');

        if (!$serial) {
            return new JsonResponse(['message' => 'Erreur de connexion au serveur: appareil non autorisé !'], JsonResponse::HTTP_NOT_FOUND);
        }

        $serialExists = $this->embeddedClientRepository->findOneBy(["serial" => $serial]);

        if (!$serialExists) {
            return new JsonResponse(['message' => 'Erreur de connexion au serveur: appareil non autorisé !'], JsonResponse::HTTP_NOT_FOUND);
        }
        
        $barcode = $this->barcodeService->extractProductDigits($barcode);

        if (!$barcode) {
            return new JsonResponse(['message' => 'Code-barre invalide !'], JsonResponse::HTTP_NOT_FOUND);
        }

        $product = $this->productRepo->findOneBy(["barcode" => $barcode]);

        if (!$product) {
            return new JsonResponse(['message' => 'Code-barre non répertorié !'], JsonResponse::HTTP_NOT_FOUND);
        }

        $outputBarcode = $this->barcodeService->addCheckDigit($this->getParameter("app.barcodePrefix") . $product->getBarcode());

        return new JsonResponse(['name' => $product->getName(), "barcode" => $outputBarcode, "price" => $product->getPrice() / 100]);
    }
}
