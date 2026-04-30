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
        $products = $productRepository->findAllWithCategoryAndImages();

        return $this->render('item/index.html.twig', [
            'products'=> $products,
        ]);
    }
}
