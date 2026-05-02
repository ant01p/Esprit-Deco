<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ItemController extends AbstractController
{
    #[Route('/', name: 'app_item_index')]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAllWithPrincipalImage();

        return $this->render('item/index.html.twig', [
            'products'=> $products,
        ]);
    }

    #[Route('/produit/{id}', name: 'product_show')]
    public function show(int $id, ProductRepository $productRepository): Response
    {
        $product = $productRepository->findOneWithCategoryAndImages($id);

        return $this->render('item/show.html.twig', [
            'product' => $product,
        ]);
    }
}
