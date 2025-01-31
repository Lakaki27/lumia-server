<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/products')]
final class ProductsController extends AbstractController
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
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

        return $this->render('products/products.html.twig', [
            "products" => $products,
        ]);
    }

    #[Route("/add", name: 'products_add')]
    public function addProduct(): Response
    {
        // if (!$this->isGranted("ROLE_MANAGER")) {
        //     return $this->redirectToRoute('users_all');
        // }

        $user = new Product();
        $form = $this->createForm(ProductForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $pass->generatePassword();
            $now = new \DateTimeImmutable();

            $user->setPassword($userPasswordHasher->hashPassword($user, $password));
            $user->setCreatedAt($now);
            $user->setUpdatedAt($now);
            $user->setIsFirstLogin(true);

            $entityManager->persist($user);
            $entityManager->flush();

            $firstName = $user->getFirstName();
            $lastName = $user->getLastName();
            $emailAddress = $user->getEmail();

            $mail = (new TemplatedEmail())
                ->to(new Address($emailAddress))
                ->subject("Accès à l'application Lumia")
                ->htmlTemplate('emails/user_added.html.twig')
                ->context([
                    "name" => "$firstName $lastName",
                    "password" => $password
                ]);

            $mailer->send($mail);

            return $this->redirectToRoute('products_all');
        }

        return $this->render('products/add.html.twig');
    }

    #[Route("/{id}", name: 'products_details')]
    public function productDetails($id): Response
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
