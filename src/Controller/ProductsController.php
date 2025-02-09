<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductFormType;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Service\BarcodeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/products')]
final class ProductsController extends AbstractController
{
    private ProductRepository $productRepository;
    private BarcodeService $barcodeService;

    public function __construct(ProductRepository $productRepository, BarcodeService $barcodeService)
    {
        $this->productRepository = $productRepository;
        $this->barcodeService = $barcodeService;
    }

    #[Route("", name: 'products_all')]
    public function allProducts(Request $request): Response
    {
        $search = $request->query->get("search");

        $products = [];

        if ($search) {
            $products = $this->productRepository->findByMatchingName($search);
        } else {
            $products = $this->productRepository->findLastAdded(10);
        }

        foreach ($products as $product) {
            $product->setBarcode($this->barcodeService->addCheckDigit("{$this->getParameter("app.barcodePrefix")}{$product->getBarcode()}"));
        }

        return $this->render('products/products.html.twig', [
            "products" => $products,
        ]);
    }

    #[Route("/add", name: 'products_add')]
    public function addProduct(Request $request, EntityManagerInterface $entityManager): Response
    {
        // if (!$this->isGranted("ROLE_MANAGER")) {
        //     return $this->redirectToRoute('users_all');
        // }

        $user = new Product();
        $form = $this->createForm(ProductFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $now = new \DateTimeImmutable();

            $user->setCreatedAt($now);
            $user->setUpdatedAt($now);

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('products_all');
        }

        return $this->render('products/add.html.twig', [
            'productForm' => $form,
        ]);
    }

    #[Route("/{id}", name: 'products_details')]
    public function productDetails(int $id): Response
    {
        if (!is_int($id)) {
            return $this->redirectToRoute("products_all");
        }

        $product = $this->productRepository->findOneById($id);

        if (!$product) {
            return $this->redirectToRoute("products_all");
        }

        return $this->render('products/details.html.twig', [
            "product" => $product,
        ]);
    }
}
