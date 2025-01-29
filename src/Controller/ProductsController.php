<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/products')]
final class ProductsController extends AbstractController
{
    private ProductRepository $productRepository;

    public function __construct(UserRepository $userRepository, ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    #[Route("", name: 'products_all')]
    public function allProducts(): Response
    {
        //get all products

        return $this->render('products/products.html.twig', [
            'TODO' => 'Display all products',
        ]);
    }

    #[Route("/{id}", name: 'product_details')]
    public function productDetails(int $id): Response
    {
        //get product from id

        return $this->render('products/details.html.twig', [
            'TODO' => "Display product for id $id",
        ]);
    }

    #[Route("/add", name: 'product_add')]
    public function addProduct(): Response
    {
        //add product (ADD FORM TOO)

        return $this->render('products/add.html.twig', [
            'TODO' => "Display add product page",
        ]);
    }
}
